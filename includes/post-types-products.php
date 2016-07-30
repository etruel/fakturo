<?php


// Exit if accessed directly
if (!defined('ABSPATH'))  {
	exit;
}


if ( ! class_exists('fktrPostTypeProducts') ) :
class fktrPostTypeProducts {
	function __construct() {
		
		add_action( 'init', array('fktrPostTypeProducts', 'setup'), 1 );
		add_action('transition_post_status', array('fktrPostTypeProducts', 'default_fields'), 10, 3);
		
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
			'register_meta_box_cb' => array('fktrPostTypeProducts','productsMetaBoxes'),
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
		add_meta_box('postimagediv', __('Product Image', FAKTURO_TEXT_DOMAIN ), array('fktrPostTypeProducts', 'fktr_post_thumbnail_meta_box'), 'fktr_product', 'side', 'high');
		add_meta_box( 'fakturo-seller-box', __('Assign Seller', FAKTURO_TEXT_DOMAIN ), array('fktrPostTypeProducts', 'fktr_product_seller_box'),'fktr_product','side', 'high' );
		add_meta_box( 'fakturo-data-box', __('Complete Product Data', FAKTURO_TEXT_DOMAIN ), array('fktrPostTypeProducts', 'fktr_product_data_box'),'fktr_product','normal', 'default' );

		add_meta_box( 'fakturo-price-box', __('Price', FAKTURO_TEXT_DOMAIN ), array('fktrPostTypeProducts', 'fktr_product_price_box'),'fktr_product','side', 'default' );
		add_meta_box( 'fakturo-stock-box', __('Stock', FAKTURO_TEXT_DOMAIN ), array('fktrPostTypeProducts', 'fktr_product_stock_box'),'fktr_product','normal', 'default' );
		
	}
	
	public static function fktr_post_thumbnail_meta_box() {
		
		
	}
	public static function fktr_product_seller_box() {
		
		
	}
	public static function fktr_product_data_box() {
		global $post;
		
		$product_data = self::get_product_data($post->ID);
		$echoHtml = '<table class="form-table">
					<tbody>
			<tr class="user-address-wrap">
				<th><label for="cost">'. __('Cost', FAKTURO_TEXT_DOMAIN ). '	</label></th>
				<td><input type="text" name="cost" id="cost" value="'.$product_data['cost'].'" class="regular-text"></td>
			</tr>
			<tr class="user-facebook-wrap">
				<th><label for="provider">'.__('Provider', FAKTURO_TEXT_DOMAIN ).'	</label></th>
				<td></td>
			</tr>
			<tr class="user-facebook-wrap">
				<th><label for="product_type">'.__('Product Type', FAKTURO_TEXT_DOMAIN ).'	</label></th>
				<td></td>
			</tr>
			<tr class="user-facebook-wrap">
				<th><label for="tax">'.__('Tax', FAKTURO_TEXT_DOMAIN ).'	</label></th>
				<td></td>
			</tr>
			<tr class="user-address-wrap">
				<th><label for="currency">'.__('Currency', FAKTURO_TEXT_DOMAIN ).'</label></th>
				<td>
				
				</td>		
			</tr>
			<tr class="user-address-wrap">
				<th><label for="reference">'.__('Reference', FAKTURO_TEXT_DOMAIN ).'</label></th>
				<td><input type="text" name="reference" id="reference" value="'.$product_data['reference'].'" class="regular-text"></td>
			</tr>
			<tr class="user-address-wrap">
				<th><label for="internal">'.__('Internal code', FAKTURO_TEXT_DOMAIN ).'</label></th>
				<td><input type="text" name="internal" id="internal" value="'.$product_data['internal'].'" class="regular-text"></td>
			</tr>
			<tr class="user-address-wrap">
				<th><label for="manufacturers">'.__('Manufacturers code', FAKTURO_TEXT_DOMAIN ).'</label></th>
				<td><input type="text" name="manufacturers" id="manufacturers" value="'.$product_data['manufacturers'].'" class="regular-text"></td>
			</tr>
			<tr class="user-address-wrap">
				<th><label for="description">'.__('Description', FAKTURO_TEXT_DOMAIN ).'</label></th>
				<td>
					<textarea style="width:95%;" rows="4" name="description" id="description">'.$product_data['description'].'</textarea>
				</td>
			</tr>
			<tr class="user-address-wrap">
				<th><label for="short">'.__('Short description', FAKTURO_TEXT_DOMAIN ).'</label></th>
				<td><input type="text" name="short" id="short" value="'.$product_data['short'].'" class="regular-text"></td>
			</tr>
			<tr class="user-address-wrap">
				<th><label for="min">'.__('Minimal stock', FAKTURO_TEXT_DOMAIN ).'</label></th>
				<td><input type="number" name="min" id="min" value="'.$product_data['min'].'" class="regular-text"></td>
			</tr>
			<tr class="user-facebook-wrap">
				<th><label for="min_alert">'.__('Minimal stock alert', FAKTURO_TEXT_DOMAIN ).'</label></th>
				<td><input id="min_alert" type="checkbox" name="min_alert" value="1" '.(($product_data['min_alert'])?'checked="checked"':'').'</td>
			</tr>
			<tr class="user-facebook-wrap">
				<th><label for="tax">'. __('Packaging', FAKTURO_TEXT_DOMAIN). '</label></th>
				<td></td> 
			</tr>
			<tr class="user-address-wrap">
				<th><label for="unit">'.__('Units per package', FAKTURO_TEXT_DOMAIN ).'</label></th>
				<td><input type="text" name="unit" id="unit" value="'.$product_data['unit'].'" class="regular-text"></td>
			</tr>
			<tr class="user-address-wrap">
				<th><label for="note">'.__('Notes', FAKTURO_TEXT_DOMAIN ).'</label></th>
				<td><textarea style="width:95%;" rows="4" name="note" id="note">'.$product_data['note'].'</textarea></td>
			</tr>
			<tr class="user-address-wrap">
				<th><label for="origin">'.__('Origin', FAKTURO_TEXT_DOMAIN ).'</label></th>
				<td></td>
			</tr>
		</tbody>
	</table>';
	
		$echoHtml = apply_filters('fktr_product_data_box', $echoHtml);
		echo $echoHtml;
		do_action('add_fktr_product_data_box', $echoHtml);
		
	}
	public static function fktr_product_price_box() {
		
		
	}
	public static function fktr_product_stock_box() {
		
		
	}
	public static function default_fields($new_status, $old_status, $post ) {
		
		if( $post->post_type == 'fktr_product' && $old_status == 'new'){		
			
			$fields = array();
			$fields['cost'] = '0';
			$fields = apply_filters('fktr_clean_product_fields', $fields);

			foreach ( $fields as $field => $value ) {
				if ( !is_null( $value ) ) {
					
					$new = apply_filters( 'fktr_product_metabox_save_' . $field, $value );  //filtra cada campo antes de grabar
					update_post_meta( $post->ID, $field, $new );
				}
			}
		}
	}
	public static function get_product_data($product_id) {
		$custom_field_keys = get_post_custom($product_id);
		foreach ( $custom_field_keys as $key => $value ) {
			$custom_field_keys[$key] = maybe_unserialize($value[0]);
		}
		$custom_field_keys = apply_filters('fktr_clean_product_fields', $custom_field_keys );
		return $custom_field_keys;
	}
	
	
} 

endif;

$fktrPostTypeProducts = new fktrPostTypeProducts();

?>