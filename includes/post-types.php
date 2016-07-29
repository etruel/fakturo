<?php


// Exit if accessed directly
if (!defined('ABSPATH'))  {
	exit;
}


if ( ! class_exists('fktrPostType') ) :
class fktrPostType {
	function __construct() {
		
		add_action( 'init', array('fktrPostType', 'setup'), 1 );
		
		
	}
	public static function setup() {
		$slug     = defined( 'FAKTURO_PRODUCT_SLUG' ) ? FAKTURO_PRODUCT_SLUG : 'fktr_products';
		$labels = array( 
			'name' => __( 'Products', FAKTURO_TEXT_DOMAIN ),
			'singular_name' => __( 'Product', FAKTURO_TEXT_DOMAIN ),
			'add_new' => __( 'Add New', FAKTURO_TEXT_DOMAIN ),
			'add_new_item' => __( 'Add New Product', FAKTURO_TEXT_DOMAIN ),
			'edit_item' => __( 'Edit Product', FAKTURO_TEXT_DOMAIN ),
			'new_item' => __( 'New Product', FAKTURO_TEXT_DOMAIN ),
			'view_item' => __( 'View Product', FAKTURO_TEXT_DOMAIN ),
			'search_items' => __( 'Search Products', FAKTURO_TEXT_DOMAIN ),
			'not_found' => __( 'No products found', FAKTURO_TEXT_DOMAIN ),
			'not_found_in_trash' => __( 'No products found in Trash', FAKTURO_TEXT_DOMAIN ),
			'parent_item_colon' => __( 'Parent Product:', FAKTURO_TEXT_DOMAIN ),
			'menu_name' => __( 'Products', FAKTURO_TEXT_DOMAIN ),
		);
		$capabilities = array(
			'publish_post' => 'publish_fakturo_product',
			'publish_posts' => 'publish_fakturo_products',
			'read_post' => 'read_fakturo_product',
			'read_private_posts' => 'read_private_fakturo_products',
			'edit_post' => 'edit_fakturo_product',
			'edit_published_posts' => 'edit_published_fakturo_products',
			'edit_private_posts' => 'edit_private_fakturo_products',
			'edit_posts' => 'edit_fakturo_products',
			'edit_others_posts' => 'edit_others_fakturo_products',
			'delete_post' => 'delete_fakturo_product',
			'delete_posts' => 'delete_fakturo_products',
			'delete_published_posts' => 'delete_published_fakturo_products',
			'delete_private_posts' => 'delete_private_fakturo_products',
			'delete_others_posts' => 'delete_others_fakturo_products',
		);

		$args = array( 
			'labels' => $labels,
			'hierarchical' => false,
			'description' => 'Fakturo Products',
			'supports' => array( 'title', 'thumbnail',/* 'custom-fields' */),
			'register_meta_box_cb' => array('fktrPostType','productsMetaBoxes'),
			'public' => true,
			'show_ui' => true,
			'show_in_menu' => 'edit.php?post_type=fktr_product',
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

		register_post_type( 'fktr_product', $args );

		
		
	}
	
	public static function productsMetaBoxes() {
		
		//add_action('wp_ajax_webcam_shot', 'fakturo_ajax_webcam_shot');
		
		// Remove Custom Fields Metabox
		remove_meta_box( 'postimagediv', 'fakturo_product', 'side' );
		add_meta_box('postimagediv', __('Product Image', FAKTURO_TEXT_DOMAIN ), 'fktr_post_thumbnail_meta_box', 'fktr_product', 'side', 'high');
		add_meta_box( 'fakturo-seller-box', __('Assign Seller', FAKTURO_TEXT_DOMAIN ), 'fktr_seller_box','fktr_product','side', 'high' );
		add_meta_box( 'fakturo-data-box', __('Complete Product Data', FAKTURO_TEXT_DOMAIN ), 'fktr_product_data_box','fktr_product','normal', 'default' );

		add_meta_box( 'fakturo-price-box', __('Price', FAKTURO_TEXT_DOMAIN ), 'fktr_product_price_box','fktr_product','side', 'default' );
		add_meta_box( 'fakturo-stock-box', __('Stock', FAKTURO_TEXT_DOMAIN ), 'fktr_product_stock_box','fktr_product','normal', 'default' );
		
	}
	
	
} 

endif;

$fktrPostType = new fktrPostType();

?>