<?php


// Exit if accessed directly
if (!defined('ABSPATH'))  {
	exit;
}

if ( ! class_exists('fktr_tax_nvoice_types') ) :
class fktr_tax_nvoice_types {
	
	public static $tax_name = 'fktr_invoice_types';
	function __construct() {
		add_action( 'init', array(__CLASS__, 'init'), 1, 99 );
		add_action( 'fakturo_activation', array(__CLASS__, 'init'), 1 );
		add_action(self::$tax_name.'_edit_form_fields', array(__CLASS__, 'edit_form_fields'));
		add_action(self::$tax_name.'_add_form_fields',  array(__CLASS__, 'add_form_fields'));
		
		add_filter('parent_file',  array( __CLASS__, 'tax_menu_correction'));
		add_filter('submenu_file',  array( __CLASS__, 'tax_submenu_correction'));
		
		add_action('edited_'.self::$tax_name, array(__CLASS__, 'save_fields'), 10, 2);
		add_action('created_'.self::$tax_name, array(__CLASS__,'save_fields'), 10, 2);
		
		add_filter('manage_edit-'.self::$tax_name.'_columns', array(__CLASS__, 'columns'), 10, 3);
		add_filter('manage_'.self::$tax_name.'_custom_column',  array(__CLASS__, 'theme_columns'), 10, 3);
		
		add_action('admin_enqueue_scripts', array(__CLASS__, 'scripts'), 10, 1);

		add_filter('redirect_term_location', array(__CLASS__, 'redirect_term_location'), 0, 2);
	}
	
	static function redirect_term_location($location, $tax ){
		if($tax->name == self::$tax_name){
			$location = admin_url('edit-tags.php?taxonomy='.self::$tax_name);
		}
		return $location;
	}
	
	// highlight the proper top level menu
	static function tax_menu_correction($parent_file) {
		global $current_screen;
		if ($current_screen->id == "edit-fktr_invoice_types") {
			$parent_file = 'fakturo_dashboard';
		}
		return $parent_file;
	}
	
	// highlight the proper sub level menu
	static function tax_submenu_correction($submenu_file) {
		global $current_screen;
		if ($current_screen->id == "edit-fktr_invoice_types") {
			$submenu_file = 'fakturo-settings';
		}
		return $submenu_file;
	}
	
	public static function init() {
		$labels = array(
			'name'                       => _x( 'Invoice Types', 'Invoice Types' ),
			'singular_name'              => _x( 'Invoice Type', 'Invoice Type' ),
			'search_items'               => __( 'Search Invoice Types' ),
			'popular_items'              => __( 'Popular Invoice Types' ),
			'all_items'                  => __( 'All Invoice Types' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Invoice Type' ),
			'update_item'                => __( 'Update Invoice Type' ),
			'add_new_item'               => __( 'Add New Invoice Type' ),
			'new_item_name'              => __( 'New Invoice Type Name' ),
			'separate_items_with_commas' => __( 'Separate Invoice Types with commas' ),
			'add_or_remove_items'        => __( 'Add or remove Invoice Types' ),
			'choose_from_most_used'      => __( 'Choose from the most used Invoice Types' ),
			'not_found'                  => __( 'No Invoice Types found.' ),
			'menu_name'                  => __( 'Invoice Types' ),
		);

		$args = array(
			'public'				=> false,
			'hierarchical'          => false,
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'fktr-invoice-types' ),
			'capabilities' => array(
				'manage_terms' => 'manage_'.self::$tax_name,
				'edit_terms' => 'edit_'.self::$tax_name,
				'delete_terms' => 'delete_'.self::$tax_name,
				'assign_terms' => 'assign_'.self::$tax_name
			)
		);

		register_taxonomy(
			self::$tax_name,
			'',
			$args
		);
		
	}
	public static function scripts() {
		if (isset($_GET['taxonomy']) && $_GET['taxonomy'] == self::$tax_name) {
			wp_enqueue_script( 'jquery-mask', FAKTURO_PLUGIN_URL . 'assets/js/jquery.mask.min.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			wp_enqueue_script( 'taxonomy-taxes', FAKTURO_PLUGIN_URL . 'assets/js/taxonomy-invoice-types.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			
		}
		
		
	}
	public static function add_form_fields() {
		$echoHtml = '
		<style type="text/css">.form-field.term-parent-wrap,.form-field.term-slug-wrap, .form-field label[for="parent"], .form-field #parent {display: none;}  .form-field.term-description-wrap { display:none;} .inline.hide-if-no-js{ display:none;} .view{ display:none;}</style>
		<div class="form-field" id="short_name_div">
			<label for="term_meta[short_name]">'.__( 'Short Name', FAKTURO_TEXT_DOMAIN ).'</label>
			<input type="text" name="term_meta[short_name]" id="term_meta[short_name]" value="">
			<p class="description">'.__( 'Enter a short name of the invoice types', FAKTURO_TEXT_DOMAIN ).'</p>
		</div>
		
		
		<div class="form-field" id="Symbol_div">
			<label for="term_meta[symbol]">'.__( 'Symbol', FAKTURO_TEXT_DOMAIN ).'</label>
			<input style="width: 60px;text-align: center; padding-right: 0px; " maxlength="1" type="text" name="term_meta[symbol]" id="term_meta_symbol" value="">
			<p class="description">'.__( 'Enter a symbol', FAKTURO_TEXT_DOMAIN ).'</p>
		</div>
		
		<div class="form-field" id="discriminates_taxes_div">
			<input type="checkbox" class="slidercheck" value="1" name="term_meta_discriminates_taxes" id="term_meta_discriminates_taxes">
			<label for="term_meta_discriminates_taxes"><span class="ui"></span>'.__('Discriminates taxes', FAKTURO_TEXT_DOMAIN ).'	</label>
			
		</div>
		
		<div class="form-field" id="sum_div">
			<input type="checkbox" class="slidercheck" value="1" name="term_meta_sum" id="term_meta_sum">
			<label for="term_meta_sum"><span class="ui"></span>'.__('Sum', FAKTURO_TEXT_DOMAIN ).'	</label>
			
		</div>
		
		
		';
		echo $echoHtml;
	}
	public static function edit_form_fields($term) {
	

		$term_meta = get_fakturo_term($term->term_id, self::$tax_name);
	
		$echoHtml = '<style type="text/css">.form-field.term-parent-wrap, .form-field.term-slug-wrap {display: none;} .form-field.term-description-wrap { display:none;}  </style>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="term_meta[short_name]">'.__( 'Short Name', FAKTURO_TEXT_DOMAIN ).'</label>
			</th>
			<td>
				<input type="text" name="term_meta[short_name]" id="term_meta[short_name]" value="'.$term_meta->short_name.'">
				<p class="description">'.__( 'Enter a short name of the invoice types', FAKTURO_TEXT_DOMAIN ).'</p>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="term_meta[symbol]">'.__( 'Symbol', FAKTURO_TEXT_DOMAIN ).'</label>
			</th>
			<td>
				<input style="width: 60px;text-align: center; padding-right: 0px; " maxlength="1" type="text" name="term_meta[symbol]" id="term_meta_symbol" value="'.$term_meta->symbol.'">
				<p class="description">'.__( 'Enter a symbol', FAKTURO_TEXT_DOMAIN ).'</p>
			</td>
		</tr>
		
		<tr class="form-field">
			<th scope="row" valign="top">
				
			</th>
			<td>
				<input type="checkbox" class="slidercheck" value="1" name="term_meta_discriminates_taxes" id="term_meta_discriminates_taxes" '.(($term_meta->discriminates_taxes)?'checked="checked"':'').'>
				<label for="term_meta_discriminates_taxes"><span class="ui"></span>'.__('Discriminates taxes', FAKTURO_TEXT_DOMAIN ).'	</label>
			</td>
		</tr>
		
		<tr class="form-field">
			<th scope="row" valign="top">
				
			</th>
			<td>
				<input type="checkbox" class="slidercheck" value="1" name="term_meta_sum" id="term_meta_sum" '.(($term_meta->sum)?'checked="checked"':'').'>
				<label for="term_meta_sum"><span class="ui"></span>'.__('Sum', FAKTURO_TEXT_DOMAIN ).'	</label>
			</td>
		</tr>
		
		';
		echo $echoHtml;
		
	}
	public static function columns($columns) {
		$new_columns = array(
			'cb' => '<input type="checkbox" />',
			'name' => __('Name', FAKTURO_TEXT_DOMAIN),
			'short_name' => __('Short Name', FAKTURO_TEXT_DOMAIN),
			'discriminates_taxes' => __('Discriminates taxes', FAKTURO_TEXT_DOMAIN),
			'sum' => __('Sum', FAKTURO_TEXT_DOMAIN),
		);
		return $new_columns;
	}
	public static function theme_columns($out, $column_name, $term_id) {
		
		
		$term = get_fakturo_term($term_id, self::$tax_name);
		
		switch ($column_name) {
			case 'short_name': 
				$out = esc_attr( $term->short_name);
				break;
			case 'discriminates_taxes': 
				$out = esc_attr( $term->discriminates_taxes);
				break;
			case 'sum': 
				$out = esc_attr( $term->sum);
				break;

			default:
				break;
		}
		return $out;    
	}
	public static function save_fields($term_id, $tt_id) {
		if (!isset($_POST['term_meta_discriminates_taxes'])) {
			$_POST['term_meta_discriminates_taxes'] = 0;
		}
		if (!isset($_POST['term_meta_sum'])) {
			$_POST['term_meta_sum'] = 0;
		}
		if (isset( $_POST['term_meta'])) {
			$_POST['term_meta']['discriminates_taxes'] = $_POST['term_meta_discriminates_taxes'];
			$_POST['term_meta']['sum'] = $_POST['term_meta_sum'];
			set_fakturo_term($term_id, $tt_id, $_POST['term_meta']);
		}
	}
	
}
endif;

$fktr_tax_nvoice_types = new fktr_tax_nvoice_types();

?>