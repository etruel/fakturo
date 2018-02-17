<?php


// Exit if accessed directly
if (!defined('ABSPATH'))  {
	exit;
}

if ( ! class_exists('fktr_tax_sale_points') ) :
class fktr_tax_sale_points {
	
	public static $tax_name = 'fktr_sale_points';
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
		add_action('fktr_popup_tax_'.self::$tax_name.'_print_scripts', array(__CLASS__, 'scripts'), 10, 1);
		
		add_filter('before_save_tax_'.self::$tax_name, array(__CLASS__, 'before_save'), 10, 1);
		add_filter('redirect_term_location', array(__CLASS__, 'redirect_term_location'), 0, 2);
		
	}
	static function redirect_term_location($location, $tax ){
		if($tax->name == self::$tax_name){
			$location = admin_url('edit-tags.php?taxonomy='.self::$tax_name);
		}
		return $location;
	}
	public static function init() {
		
	
		$labels = array(
			'name'                       => _x( 'Sale Points', 'Sale Points', 'fakturo' ),
			'singular_name'              => _x( 'Sale Point', 'Sale Point', 'fakturo' ),
			'search_items'               => __( 'Search Sale Points', 'fakturo' ),
			'popular_items'              => __( 'Popular Sale Points', 'fakturo' ),
			'all_items'                  => __( 'All Sale Points', 'fakturo' ),
			'parent_item'                => __( 'Bank', 'fakturo' ),
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Sale Point', 'fakturo' ),
			'update_item'                => __( 'Update Sale Point', 'fakturo' ),
			'add_new_item'               => __( 'Add New Sale Point', 'fakturo' ),
			'new_item_name'              => __( 'New Sale Point Name', 'fakturo' ),
			'separate_items_with_commas' => __( 'Separate Sale Point with commas', 'fakturo' ),
			'add_or_remove_items'        => __( 'Add or remove Sale Points', 'fakturo' ),
			'choose_from_most_used'      => __( 'Choose from the most used Sale Points', 'fakturo' ),
			'not_found'                  => __( 'No Sale Points found.', 'fakturo' ),
			'menu_name'                  => __( 'Sale Points', 'fakturo' ),
		);

		$args = array(
			'public'				=> false,
			'hierarchical'          => false,
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'fktr-sale-points' ),
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
	// highlight the proper top level menu
	static function tax_menu_correction($parent_file) {
		global $current_screen;
		if ($current_screen->id == 'edit-fktr_sale_points') {
			$parent_file = 'fakturo_dashboard';
		}
		return $parent_file;
	}
	
	// highlight the proper sub level menu
	static function tax_submenu_correction($submenu_file) {
		global $current_screen;
		if ($current_screen->id == 'edit-fktr_sale_points') {
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
		if (isset($_GET['taxonomy']) && $_GET['taxonomy'] == self::$tax_name) {
			wp_enqueue_script( 'jquery-mask', FAKTURO_PLUGIN_URL . 'assets/js/jquery.mask.min.js', $requerimient, WPE_FAKTURO_VERSION, true );
			wp_enqueue_script( 'taxonomy-price-scales', FAKTURO_PLUGIN_URL . 'assets/js/taxonomy-sale-points.js', $requerimient, WPE_FAKTURO_VERSION, true );
			
		}
		
		
	}
	public static function add_form_fields() {
		$extraHtml = apply_filters('fktr_extra_html_add_form_'.self::$tax_name, '');
		$echoHtml = '
		<style type="text/css">.form-field.term-parent-wrap,.form-field.term-slug-wrap, .form-field label[for="parent"], .form-field #parent {display: none;}  .form-field.term-description-wrap { display:none;} .inline.hide-if-no-js{ display:none;} .view{ display:none;}</style>
		<div class="form-field" id="code_div">
			<label for="term_meta[code]">'.__( 'Code', 'fakturo' ).'</label>
			<input style="width: 60px;text-align: right; padding-right: 0px; " maxlength="4" type="text" name="term_meta[code]" id="term_meta_code" value=""/>
			<p class="description">'.__( 'Enter a code', 'fakturo' ).'</p>
		</div>
		'.$extraHtml.'
		';
		echo $echoHtml;
	}
	public static function edit_form_fields($term) {
	

		$term_meta = get_fakturo_term($term->term_id, self::$tax_name);
		$extraHtml = apply_filters('fktr_extra_html_edit_form_'.self::$tax_name, '');
		$echoHtml = '<style type="text/css">.form-field.term-parent-wrap, .form-field.term-slug-wrap {display: none;} .form-field.term-description-wrap { display:none;}  </style>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="term_meta[code]">'.__( 'Code', 'fakturo' ).'</label>
			</th>
			<td>
				<input style="width: 60px;text-align: right; padding-right: 0px; " maxlength="4" type="text" name="term_meta[code]" id="term_meta_code" value="'.$term_meta->code.'"/>
				<p class="description">'.__( 'Enter a code', 'fakturo' ).'</p>
			</td>
			'.$extraHtml.'
		</tr>
		';
		echo $echoHtml;
		
	}
	public static function columns($columns) {
		$new_columns = array(
			'cb' => '<input type="checkbox" />',
			'name' => __('Name', 'fakturo'),
			'code' => __('Code', 'fakturo'),
		);
		return $new_columns;
	}
	public static function theme_columns($out, $column_name, $term_id) {
		
		$term = get_fakturo_term($term_id, self::$tax_name);
		
		switch ($column_name) {
			case 'code': 
				
				$code = str_pad($term->code, 4, '0', STR_PAD_LEFT);
				$out = esc_attr($code);
				break;

			default:
				break;
		}
		return $out;    
	}
	public static function before_save($fields)  {
		
		return $fields;
	}
	public static function save_fields($term_id, $tt_id) {
		if (isset( $_POST['term_meta'])) {
			$_POST['term_meta'] = apply_filters('before_save_tax_'.self::$tax_name, $_POST['term_meta']);
			set_fakturo_term($term_id, $tt_id, $_POST['term_meta']);
		}
	}
	
}
endif;

$fktr_tax_sale_points = new fktr_tax_sale_points();

?>