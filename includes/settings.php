<?php


// Exit if accessed directly
if (!defined('ABSPATH'))  {
	exit;
}


if ( ! class_exists('fktrSettings') ) :
class fktrSettings {
	function __construct() {
		
		add_action( 'init', array('fktrSettings', 'setup'), 1, 99 );
	
		
	}
	public static function setup() {
		$labels_model = array(
			'name'                       => _x( 'Locations', 'Locations' ),
			'singular_name'              => _x( 'Location', 'Location' ),
			'search_items'               => __( 'Search Locations' ),
			'popular_items'              => __( 'Popular Locations' ),
			'all_items'                  => __( 'All Locations' ),
			'parent_item'                => __( 'Country', FAKTURO_TEXT_DOMAIN ),
			'parent_item_colon'          => __( 'Country:', FAKTURO_TEXT_DOMAIN ),
			'edit_item'                  => __( 'Edit Location' ),
			'update_item'                => __( 'Update Location' ),
			'add_new_item'               => __( 'Add New Location' ),
			'new_item_name'              => __( 'New Location Name' ),
			'separate_items_with_commas' => __( 'Separate location with commas' ),
			'add_or_remove_items'        => __( 'Add or remove locations' ),
			'choose_from_most_used'      => __( 'Choose from the most used locations' ),
			'not_found'                  => __( 'No locations found.' ),
			'menu_name'                  => __( 'Locations' ),
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

	}
	static public function getSelectTwoLocationsParents() {
		
		
	}
	static public function getSelectTwoLocationsChilds($term_parent_id) {
		
		
	}
	
	
} 

endif;

$fktrSettings = new fktrSettings();

?>