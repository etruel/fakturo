<?php


// Exit if accessed directly
if (!defined('ABSPATH'))  {
	exit;
}

if ( ! class_exists('fktr_tax_currency') ) :
class fktr_tax_currency {
	
	function __construct() {
		add_action( 'init', array('fktr_tax_currency', 'init'), 1, 99 );
		add_action( 'fakturo_activation', array(__CLASS__, 'init'), 1 );
		add_action('fktr_currencies_edit_form_fields', array('fktr_tax_currency', 'edit_form_fields'));
		add_action('fktr_currencies_add_form_fields',  array('fktr_tax_currency', 'add_form_fields'));
		add_action('edited_fktr_currencies', array('fktr_tax_currency', 'save_fields'), 10, 2);
		add_action('created_fktr_currencies', array('fktr_tax_currency','save_fields'), 10, 2);
		
		add_filter('parent_file',  array( __CLASS__, 'tax_menu_correction'));
		add_filter('submenu_file',  array( __CLASS__, 'tax_submenu_correction'));
		
		add_filter('manage_edit-fktr_currencies_columns', array('fktr_tax_currency', 'columns'), 10, 3);
		add_filter('manage_fktr_currencies_custom_column',  array('fktr_tax_currency', 'theme_columns'), 10, 3);
		
		add_filter('before_save_tax_fktr_currencies', array(__CLASS__, 'before_save'), 10, 1);
		add_filter('redirect_term_location', array(__CLASS__, 'redirect_term_location'), 0, 2);

		add_action('admin_enqueue_scripts', array('fktr_tax_currency', 'scripts'), 10, 1);
		add_action('fktr_popup_tax_fktr_currencies_print_scripts', array(__CLASS__, 'scripts'), 10, 1);
	}
	static function redirect_term_location($location, $tax ){
		if($tax->name == 'fktr_currencies'){
			$location = admin_url('edit-tags.php?taxonomy=fktr_currencies');
		}
		return $location;
	}
	public static function init() {
		
		$labels = array(
			'name'                       => __( 'Currencies', 'fakturo' ),
			'singular_name'              => __( 'Currency', 'fakturo' ),
			'search_items'               => __( 'Search Currencies', 'fakturo' ),
			'popular_items'              => __( 'Popular Currencies', 'fakturo' ),
			'all_items'                  => __( 'All Currencies', 'fakturo' ),
			'parent_item'                => __( 'Bank', 'fakturo' ),
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Currency', 'fakturo' ),
			'update_item'                => __( 'Update Currency', 'fakturo' ),
			'add_new_item'               => __( 'Add New Currency', 'fakturo' ),
			'new_item_name'              => __( 'New Currency Name', 'fakturo' ),
			'separate_items_with_commas' => __( 'Separate Currency with commas', 'fakturo' ),
			'add_or_remove_items'        => __( 'Add or remove Currencies', 'fakturo' ),
			'choose_from_most_used'      => __( 'Choose from the most used Currencies', 'fakturo' ),
			'not_found'                  => __( 'No Currencies found.', 'fakturo' ),
			'menu_name'                  => __( 'Currencies', 'fakturo' ),
		);

		$args = array(
			'public'				=> false,
			'hierarchical'          => false,
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'fktr-currencies' ),
			'capabilities' => array(
				'manage_terms' => 'manage_fktr_currencies',
				'edit_terms' => 'edit_fktr_currencies',
				'delete_terms' => 'delete_fktr_currencies',
				'assign_terms' => 'assign_fktr_currencies'
			)
		);
		register_taxonomy(
			'fktr_currencies',
			'',
			$args
		);
		
	}

	// highlight the proper top level menu
	static function tax_menu_correction($parent_file) {
		global $current_screen;
		if ($current_screen->id == "edit-fktr_currencies") {
			$parent_file = 'fakturo_dashboard';
		}
		return $parent_file;
	}
	
	// highlight the proper sub level menu
	static function tax_submenu_correction($submenu_file) {
		global $current_screen;
		if ($current_screen->id == "edit-fktr_currencies") {
			$submenu_file = 'fakturo-settings';
		}
		return $submenu_file;
	}
		
	public static function scripts() {
		$requerimient = array( 'jquery' );
		if (isset($_GET['action'])) {
			if ($_GET['action']=='fktr_popup_taxonomy') {
				$requerimient = array();
			}
		}
		if (isset($_GET['taxonomy']) && $_GET['taxonomy'] == 'fktr_currencies') {
			wp_enqueue_script( 'jquery-mask', FAKTURO_PLUGIN_URL . 'assets/js/jquery.mask.min.js', $requerimient, WPE_FAKTURO_VERSION, true );
			wp_enqueue_script( 'taxonomy-currencies', FAKTURO_PLUGIN_URL . 'assets/js/taxonomy-currencies.js', $requerimient, WPE_FAKTURO_VERSION, true );
			$setting_system = get_option('fakturo_system_options_group', false);
			wp_localize_script('taxonomy-currencies', 'setting_system',
				array(
					'thousand' => $setting_system['thousand'],
					'decimal' => $setting_system['decimal'],
					'decimal_numbers' => $setting_system['decimal_numbers']

				) );
		
		}
		
		
	}
	public static function add_form_fields() {
		$echoHtml = '
		<style type="text/css">.form-field.term-parent-wrap,.form-field.term-slug-wrap, .form-field label[for="parent"], .form-field #parent {display: none;}  .form-field.term-description-wrap { display:none;} .inline.hide-if-no-js{ display:none;} .view{ display:none;}</style>
		<div class="form-field" id="plural_div">
			<label for="term_meta[plural]">'.__( 'Plural', 'fakturo' ).'</label>
			<input type="text" name="term_meta[plural]" id="term_meta[plural]" value="">
			<p class="description">'.__( 'Enter name plural of the currency', 'fakturo' ).'</p>
		</div>
		<div class="form-field" id="symbol_div">
			<label for="term_meta[symbol]">'.__( 'Symbol', 'fakturo' ).'</label>
			<input style="width: 60px;text-align: center; padding-right: 0px; " type="text" name="term_meta[symbol]" id="term_meta_symbol" value="">
			<p class="description">'.__( 'Enter a symbol like $', 'fakturo' ).'</p>
		</div>
		<div class="form-field" id="rate_div">
			<label for="term_meta[rate]">'.__( 'Rate', 'fakturo' ).'</label>
			<input style="width: 60px;text-align: right; padding-right: 0px; " type="text" name="term_meta[rate]" id="term_meta_rate" value="0">
			<p class="description">'.__( 'Enter a rate', 'fakturo' ).'</p>
		</div>
		<div class="form-field" id="reference_div">
			<label for="term_meta[reference]">'.__( 'Reference', 'fakturo' ).'</label>
			<input type="text" name="term_meta[reference]" id="term_meta_reference" value="">
			<p class="description">'.__( 'Enter a reference website to find the conversion rate', 'fakturo' ).'</p>
		</div>
		
		';
		echo $echoHtml;
	}
	public static function edit_form_fields($term) {
	

		$term_meta = get_fakturo_term($term->term_id, 'fktr_currencies');
		$setting_system = get_option('fakturo_system_options_group', false);
		$echoHtml = '<style type="text/css">.form-field.term-parent-wrap, .form-field.term-slug-wrap {display: none;} .form-field.term-description-wrap { display:none;}  </style>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="term_meta[plural]">'.__( 'Plural', 'fakturo' ).'</label>
			</th>
			<td>
				<input type="text" name="term_meta[plural]" id="term_meta[plural]" value="'.$term_meta->plural.'">
				<p class="description">'.__( 'Enter name plural of the currency', 'fakturo' ).'</p>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="term_meta[symbol]">'.__( 'Symbol', 'fakturo' ).'</label>
			</th>
			<td>
				<input type="text" style="width: 60px;text-align: center; padding-right: 0px; " name="term_meta[symbol]" id="term_meta_symbol" value="'.$term_meta->symbol.'">
				<p class="description">'.__( 'Enter a symbol like $', 'fakturo' ).'</p>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="term_meta[rate]">'.__( 'Rate', 'fakturo' ).'</label>
			</th>
			<td>
				<input style="width: 60px;text-align: right; padding-right: 0px; " type="text" name="term_meta[rate]" id="term_meta_rate" value="'.number_format($term_meta->rate, $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']).'">
				<p class="description">'.__( 'Enter a rate', 'fakturo' ).'</p>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="term_meta[reference]">'.__( 'Reference', 'fakturo' ).'</label>
			</th>
			<td>
				<input type="text" name="term_meta[reference]" id="term_meta_reference" value="'.$term_meta->reference.'">
				<p class="description">'.__( 'Enter a reference website to find the conversion rate', 'fakturo' ).'</p>
			</td>
		</tr>
		';
		echo $echoHtml;
		
	}
	public static function columns($columns) {
		$new_columns = array(
			'cb' => '<input type="checkbox" />',
			'name' => __('Name', 'fakturo'),
			'symbol' => __('Symbol', 'fakturo'),
			'rate' => __('Rate', 'fakturo')
		);
		return $new_columns;
	}
	public static function theme_columns($out, $column_name, $term_id) {
		
		
		$term = get_fakturo_term($term_id, 'fktr_currencies');
		$setting_system = get_option('fakturo_system_options_group', false);
		switch ($column_name) {
			case 'symbol': 
				$out = esc_attr( $term->symbol);
				break;

			case 'rate': 
				$out = esc_attr(number_format($term->rate, $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']));
				break;

			default:
				break;
		}
		return $out;    
	}
	public static function before_save($fields)  {
		if (isset($fields['rate'])) {
			$fields['rate'] = fakturo_mask_to_float($fields['rate']);
		}
		return $fields;
	}
	public static function save_fields($term_id, $tt_id) {
		$setting_system = get_option('fakturo_system_options_group', false);
		if (isset( $_POST['term_meta'])) {
			
			$_POST['term_meta'] = apply_filters('before_save_tax_fktr_currencies', $_POST['term_meta']);
			set_fakturo_term($term_id, $tt_id, $_POST['term_meta']);
		}
	}
	
}
endif;

$fktr_tax_currency = new fktr_tax_currency();

?>