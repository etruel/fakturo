<?php


// Exit if accessed directly
if (!defined('ABSPATH'))  {
	exit;
}


if ( ! class_exists('fktrPostTypeProviders') ) :
class fktrPostTypeProviders {
	function __construct() {
		
		add_action( 'init', array('fktrPostTypeProviders', 'setup'), 1 );
		add_action('transition_post_status', array('fktrPostTypeProviders', 'default_fields'), 10, 3);
		
	}
	public static function setup() {
		$labels = array( 
			'name' => __( 'Providers', FAKTURO_TEXT_DOMAIN ),
			'singular_name' => __( 'Provider', FAKTURO_TEXT_DOMAIN ),
			'add_new' => __( 'Add New', FAKTURO_TEXT_DOMAIN ),
			'add_new_item' => __( 'Add New Provider', FAKTURO_TEXT_DOMAIN ),
			'edit_item' => __( 'Edit Provider', FAKTURO_TEXT_DOMAIN ),
			'new_item' => __( 'New Provider', FAKTURO_TEXT_DOMAIN ),
			'view_item' => __( 'View Provider', FAKTURO_TEXT_DOMAIN ),
			'search_items' => __( 'Search Providers', FAKTURO_TEXT_DOMAIN ),
			'not_found' => __( 'No providers found', FAKTURO_TEXT_DOMAIN ),
			'not_found_in_trash' => __( 'No providers found in Trash', FAKTURO_TEXT_DOMAIN ),
			'parent_item_colon' => __( 'Parent Provider:', FAKTURO_TEXT_DOMAIN ),
			'menu_name' => __( 'Providers', FAKTURO_TEXT_DOMAIN ),
		);
		$capabilities = array(
			'publish_post' => 'publish_fakturo_provider',
			'publish_posts' => 'publish_fakturo_providers',
			'read_post' => 'read_fakturo_provider',
			'read_private_posts' => 'read_private_fakturo_providers',
			'edit_post' => 'edit_fakturo_provider',
			'edit_published_posts' => 'edit_published_fakturo_providers',
			'edit_private_posts' => 'edit_private_fakturo_providers',
			'edit_posts' => 'edit_fakturo_providers',
			'edit_others_posts' => 'edit_others_fakturo_providers',
			'delete_post' => 'delete_fakturo_provider',
			'delete_posts' => 'delete_fakturo_providers',
			'delete_published_posts' => 'delete_published_fakturo_providers',
			'delete_private_posts' => 'delete_private_fakturo_providers',
			'delete_others_posts' => 'delete_others_fakturo_providers',
			);

		$args = array( 
			'labels' => $labels,
			'hierarchical' => false,
			'description' => 'Fakturo Providers',
			'supports' => array( 'title', 'thumbnail',/* 'custom-fields' */),
			'register_meta_box_cb' => array('fktrPostTypeProviders', 'meta_boxes'),
			'public' => true,
			'show_ui' => true,
			'show_in_menu' => 'admin.php?page=fakturo/view/fakturo_admin.php',
			'menu_position' => 5,
			'menu_icon' => '/images/icon_20.png',
			'show_in_nav_menus' => false,
			'publicly_queryable' => false,
			'exclude_from_search' => false,
			'has_archive' => false,
			'query_var' => true,
			'can_export' => true,
			'rewrite' => true
		);

		register_post_type( 'fktr_provider', $args );

	}
	
	public static function meta_boxes() {
		
		do_action('add_ftkr_provider_meta_boxes');
	}
	
	
	public static function default_fields($new_status, $old_status, $post ) {
		
		if( $post->post_type == 'fktr_provider' && $old_status == 'new'){		
			
			$fields = array();
			$fields = apply_filters('fktr_clean_provider_fields', $fields);

			foreach ( $fields as $field => $value ) {
				if ( !is_null( $value ) ) {
					
					$new = apply_filters( 'fktr_provider_metabox_save_' . $field, $value );  //filtra cada campo antes de grabar
					update_post_meta( $post->ID, $field, $new );
				}
			}
		}
	}
	public static function get_provider_data($provider_id) {
		$custom_field_keys = get_post_custom($provider_id);
		foreach ( $custom_field_keys as $key => $value ) {
			$custom_field_keys[$key] = maybe_unserialize($value[0]);
		}
		$custom_field_keys = apply_filters('fktr_clean_provider_fields', $custom_field_keys );
		return $custom_field_keys;
	}
	
	
} 

endif;

$fktrPostTypeProviders = new fktrPostTypeProviders();

?>