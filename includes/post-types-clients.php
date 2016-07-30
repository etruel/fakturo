<?php


// Exit if accessed directly
if (!defined('ABSPATH'))  {
	exit;
}


if ( ! class_exists('fktrPostTypeClients') ) :
class fktrPostTypeClients {
	function __construct() {
		
		add_action( 'init', array('fktrPostTypeClients', 'setup'), 1 );
		add_action('transition_post_status', array('fktrPostTypeClients', 'default_fields'), 10, 3);
		
	}
	public static function setup() {
		$labels = array( 
			'name' => __( 'Clients', FAKTURO_TEXT_DOMAIN ),
			'singular_name' => __( 'Client', FAKTURO_TEXT_DOMAIN ),
			'add_new' => __( 'Add New', FAKTURO_TEXT_DOMAIN ),
			'add_new_item' => __( 'Add New Client', FAKTURO_TEXT_DOMAIN ),
			'edit_item' => __( 'Edit Client', FAKTURO_TEXT_DOMAIN ),
			'new_item' => __( 'New Client', FAKTURO_TEXT_DOMAIN ),
			'view_item' => __( 'View Client', FAKTURO_TEXT_DOMAIN ),
			'search_items' => __( 'Search Clients', FAKTURO_TEXT_DOMAIN ),
			'not_found' => __( 'No clients found', FAKTURO_TEXT_DOMAIN ),
			'not_found_in_trash' => __( 'No clients found in Trash', FAKTURO_TEXT_DOMAIN ),
			'parent_item_colon' => __( 'Parent Client:', FAKTURO_TEXT_DOMAIN ),
			'menu_name' => __( 'Clients', FAKTURO_TEXT_DOMAIN ),
		);
		$capabilities = array(
			'publish_post' => 'publish_fakturo_client',
			'publish_posts' => 'publish_fakturo_clients',
			'read_post' => 'read_fakturo_client',
			'read_private_posts' => 'read_private_fakturo_clients',
			'edit_post' => 'edit_fakturo_client',
			'edit_published_posts' => 'edit_published_fakturo_clients',
			'edit_private_posts' => 'edit_private_fakturo_clients',
			'edit_posts' => 'edit_fakturo_clients',
			'edit_others_posts' => 'edit_others_fakturo_clients',
			'delete_post' => 'delete_fakturo_client',
			'delete_posts' => 'delete_fakturo_clients',
			'delete_published_posts' => 'delete_published_fakturo_clients',
			'delete_private_posts' => 'delete_private_fakturo_clients',
			'delete_others_posts' => 'delete_others_fakturo_clients',
			);

		$args = array( 
			'labels' => $labels,
			'hierarchical' => false,
			'description' => 'Fakturo Clients',
			'supports' => array( 'title', 'thumbnail',/* 'custom-fields' */),
			'register_meta_box_cb' => array('fktrPostTypeClients','meta_boxes'),
			'public' => true,
			'show_ui' => true,
			'show_in_menu' => 'admin.php?page=fakturo/view/fakturo_admin.php',
			'menu_position' => 3,
			'menu_icon' => '/images/icon_20.png',
			'show_in_nav_menus' => false,
			'publicly_queryable' => false,
			'exclude_from_search' => false,
			'has_archive' => false,
			'query_var' => true,
			'can_export' => true,
			'rewrite' => true,
		);

		register_post_type( 'fktr_client', $args );

	}
	
	public static function meta_boxes() {
		
		do_action('add_ftkr_client_meta_boxes');
	}
	
	
	public static function default_fields($new_status, $old_status, $post ) {
		
		if( $post->post_type == 'fktr_client' && $old_status == 'new'){		
			
			$fields = array();
			$fields = apply_filters('fktr_clean_client_fields', $fields);

			foreach ( $fields as $field => $value ) {
				if ( !is_null( $value ) ) {
					
					$new = apply_filters( 'fktr_client_metabox_save_' . $field, $value );  //filtra cada campo antes de grabar
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
		$custom_field_keys = apply_filters('fktr_clean_client_fields', $custom_field_keys );
		return $custom_field_keys;
	}
	
	
} 

endif;

$fktrPostTypeClients = new fktrPostTypeClients();

?>