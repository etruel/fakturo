<?php


// Exit if accessed directly
if (!defined('ABSPATH'))  {
	exit;
}


if ( ! class_exists('fktrSettings') ) :
class fktrSettings {
	function __construct() {
		
		add_action( 'init', array('fktrSettings', 'setup'), 1, 99 );
		//add_action( 'in_admin_header', array('fktrSettings', 'probandoarriba'), 1, 0 );
		add_action( 'all_admin_notices', array('fktrSettings', 'probandoarriba'), 1, 0 );
		
	}
	
	public static function probandoarriba() {
		global $screen, $current_screen;
		if($current_screen->id == "edit-fktr_locations" || ($current_screen->id == "edit-fktr_bank_entities") ) {
			echo "Agregar tabs aca<br>AAAAAAAAAAAAAAAAAAAAAAAAA";
		}
	}
		
	public static function setup() {
		$labels_model = array(
			'name'                       => _x( 'Locations', 'Locations', FAKTURO_TEXT_DOMAIN ),
			'singular_name'              => _x( 'Location', 'Location', FAKTURO_TEXT_DOMAIN ),
			'search_items'               => __( 'Search Locations', FAKTURO_TEXT_DOMAIN ),
			'popular_items'              => __( 'Popular Locations', FAKTURO_TEXT_DOMAIN ),
			'all_items'                  => __( 'All Locations', FAKTURO_TEXT_DOMAIN ),
			'parent_item'                => __( 'Country', FAKTURO_TEXT_DOMAIN ),
			'parent_item_colon'          => __( 'Country:', FAKTURO_TEXT_DOMAIN ),
			'edit_item'                  => __( 'Edit Location', FAKTURO_TEXT_DOMAIN ),
			'update_item'                => __( 'Update Location', FAKTURO_TEXT_DOMAIN ),
			'add_new_item'               => __( 'Add New Location', FAKTURO_TEXT_DOMAIN ),
			'new_item_name'              => __( 'New Location Name', FAKTURO_TEXT_DOMAIN ),
			'separate_items_with_commas' => __( 'Separate location with commas', FAKTURO_TEXT_DOMAIN ),
			'add_or_remove_items'        => __( 'Add or remove locations', FAKTURO_TEXT_DOMAIN ),
			'choose_from_most_used'      => __( 'Choose from the most used locations', FAKTURO_TEXT_DOMAIN ),
			'not_found'                  => __( 'No locations found.', FAKTURO_TEXT_DOMAIN ),
			'menu_name'                  => __( 'Locations', FAKTURO_TEXT_DOMAIN ),
		);

		$args_model = array(
			'hierarchical'          => true,
			'labels'                => $labels_model,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'fktr-locations' ),
		);

		register_taxonomy(
			'fktr_locations',
			'fktr_provider',
			$args_model
		);
		
		
		
		$labels_model = array(
			'name'                       => _x( 'Bank Entities', 'Bank Entities', FAKTURO_TEXT_DOMAIN ),
			'singular_name'              => _x( 'Location', 'Location', FAKTURO_TEXT_DOMAIN ),
			'search_items'               => __( 'Search Bank Entities', FAKTURO_TEXT_DOMAIN ),
			'popular_items'              => __( 'Popular Bank Entities', FAKTURO_TEXT_DOMAIN ),
			'all_items'                  => __( 'All Bank Entities', FAKTURO_TEXT_DOMAIN ),
			'parent_item'                => __( 'Bank', FAKTURO_TEXT_DOMAIN ),
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Bank Entity', FAKTURO_TEXT_DOMAIN ),
			'update_item'                => __( 'Update Bank Entity', FAKTURO_TEXT_DOMAIN ),
			'add_new_item'               => __( 'Add New Bank Entity', FAKTURO_TEXT_DOMAIN ),
			'new_item_name'              => __( 'New Bank Entity Name', FAKTURO_TEXT_DOMAIN ),
			'separate_items_with_commas' => __( 'Separate Bank Entity with commas', FAKTURO_TEXT_DOMAIN ),
			'add_or_remove_items'        => __( 'Add or remove Bank Entities', FAKTURO_TEXT_DOMAIN ),
			'choose_from_most_used'      => __( 'Choose from the most used Bank Entities', FAKTURO_TEXT_DOMAIN ),
			'not_found'                  => __( 'No Bank Entities found.', FAKTURO_TEXT_DOMAIN ),
			'menu_name'                  => __( 'Bank Entities', FAKTURO_TEXT_DOMAIN ),
		);

		$args_model = array(
			'hierarchical'          => false,
			'labels'                => $labels_model,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'fktr-bank-entities' ),
		);

		register_taxonomy(
			'fktr_bank_entities',
			'fktr_provider',
			$args_model
		);

	}
	
	
	
} 

endif;

$fktrSettings = new fktrSettings();

?>