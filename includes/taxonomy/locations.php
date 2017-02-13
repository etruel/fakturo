<?php


// Exit if accessed directly
if (!defined('ABSPATH'))  {
	exit;
}

if ( ! class_exists('fktr_tax_locations') ) :
class fktr_tax_locations {
	
	public static $tax_name = 'fktr_locations';
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
	public static function init() {
		
	
		$labels = array(
			'name'                       => _x( 'Locations', 'Locations' ),
			'singular_name'              => _x( 'Location', 'Location' ),
			'search_items'               => __( 'Search Locations' ),
			'popular_items'              => __( 'Popular Locations' ),
			'all_items'                  => __( 'All Locations' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Location' ),
			'update_item'                => __( 'Update Location' ),
			'add_new_item'               => __( 'Add New Location' ),
			'new_item_name'              => __( 'New Location Name' ),
			'separate_items_with_commas' => __( 'Separate Locations with commas' ),
			'add_or_remove_items'        => __( 'Add or remove Locations' ),
			'choose_from_most_used'      => __( 'Choose from the most used Locations' ),
			'not_found'                  => __( 'No Locations found.' ),
			'menu_name'                  => __( 'Locations' ),
		);

		$args = array(
			'public'				=> false,
			'hierarchical'          => false,
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'fktr-location' ),
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
		if ($current_screen->id == "edit-fktr_locations") {
			$parent_file = 'fakturo_dashboard';
		}
		return $parent_file;
	}
	
	// highlight the proper sub level menu
	static function tax_submenu_correction($submenu_file) {
		global $current_screen;
		if ($current_screen->id == "edit-fktr_locations") {
			$submenu_file = 'fakturo-settings';
		}
		return $submenu_file;
	}
	
	public static function scripts() {
		if (isset($_GET['taxonomy']) && $_GET['taxonomy'] == self::$tax_name) {
			
		}
	}
	public static function add_form_fields() {
		$echoHtml = '
		<style type="text/css">.form-field.term-parent-wrap,.form-field.term-slug-wrap, .form-field label[for="parent"], .form-field #parent {display: none;}  .form-field.term-description-wrap { display:none;} .inline.hide-if-no-js{ display:none;} .view{ display:none;}</style>

		';
		echo $echoHtml;
	}
	public static function edit_form_fields($term) {
	

		$term_meta = get_fakturo_term($term->term_id, self::$tax_name);
	
		$echoHtml = '<style type="text/css">.form-field.term-parent-wrap, .form-field.term-slug-wrap {display: none;} .form-field.term-description-wrap { display:none;}  </style>
	
		';
		echo $echoHtml;
		
	}
	public static function columns($columns) {
		$new_columns = array(
			'cb' => '<input type="checkbox" />',
			'name' => __('Name', FAKTURO_TEXT_DOMAIN),
		);
		return $new_columns;
	}
	public static function theme_columns($out, $column_name, $term_id) {
		
		
		switch ($column_name) {
			default:
				break;
		}
		return $out;    
	}
	public static function save_fields($term_id, $tt_id) {
		if (isset( $_POST['term_meta'])) {
			set_fakturo_term($term_id, $tt_id, $_POST['term_meta']);
		}
	}
	
}
endif;

$fktr_tax_locations = new fktr_tax_locations();

?>