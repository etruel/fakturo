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
		add_action('save_post', array('fktrPostTypeProducts', 'save'), 99, 2 );
		
		add_action( 'admin_print_scripts-post-new.php', array('fktrPostTypeProducts','scripts'), 11 );
		add_action( 'admin_print_scripts-post.php', array('fktrPostTypeProducts','scripts'), 11 );
		
		add_action('admin_print_styles-post-new.php', array('fktrPostTypeProducts','styles'));
		add_action('admin_print_styles-post.php', array('fktrPostTypeProducts','styles'));
		
		
		add_filter('fktr_clean_product_fields', array('fktrPostTypeProducts', 'clean_fields'), 10, 1);
		add_filter('fktr_product_before_save', array('fktrPostTypeProducts', 'before_save'), 10, 1);
		
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
			'register_meta_box_cb' => array('fktrPostTypeProducts','meta_boxes'),
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

			// category taxonomy
		$labels_model = array(
			'name'                       => _x( 'Categories', 'Categories' ),
			'singular_name'              => _x( 'Category', 'Category' ),
			'search_items'               => __( 'Search Categories' ),
			'popular_items'              => __( 'Popular Categories' ),
			'all_items'                  => __( 'All Categories' ),
			'parent_item'                => __( 'Parent Category' ),
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Category' ),
			'update_item'                => __( 'Update Category' ),
			'add_new_item'               => __( 'Add New Category' ),
			'new_item_name'              => __( 'New Category Name' ),
			'separate_items_with_commas' => __( 'Separate categories with commas' ),
			'add_or_remove_items'        => __( 'Add or remove categories' ),
			'choose_from_most_used'      => __( 'Choose from the most used categories' ),
			'not_found'                  => __( 'No categories found.' ),
			'menu_name'                  => __( 'Categories' ),
		);

		$args_model = array(
			'hierarchical'          => true,
			'labels'                => $labels_model,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'fktr-category' ),
		);

		register_taxonomy(
			'fktr_category',
			'fktr_product',
			$args_model
		);
		
		// model taxonomy
		$labels_model = array(
			'name'                       => _x( 'Models', 'Models' ),
			'singular_name'              => _x( 'Model', 'Model' ),
			'search_items'               => __( 'Search Models' ),
			'popular_items'              => __( 'Popular Models' ),
			'all_items'                  => __( 'All Models' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Model' ),
			'update_item'                => __( 'Update Model' ),
			'add_new_item'               => __( 'Add New Model' ),
			'new_item_name'              => __( 'New Model Name' ),
			'separate_items_with_commas' => __( 'Separate models with commas' ),
			'add_or_remove_items'        => __( 'Add or remove models' ),
			'choose_from_most_used'      => __( 'Choose from the most used models' ),
			'not_found'                  => __( 'No models found.' ),
			'menu_name'                  => __( 'Models' ),
		);

		$args_model = array(
			'hierarchical'          => false,
			'labels'                => $labels_model,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'fktr-model' ),
		);

		register_taxonomy(
			'fktr_model',
			'fktr_product',
			$args_model
		);
		
		// model taxonomy
		$labels_model = array(
			'name'                       => _x( 'Product Types', 'Product Types' ),
			'singular_name'              => _x( 'Product Type', 'Product Type' ),
			'search_items'               => __( 'Search Product Types' ),
			'popular_items'              => __( 'Popular Product Types' ),
			'all_items'                  => __( 'All Product Types' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Product Type' ),
			'update_item'                => __( 'Update Product Type' ),
			'add_new_item'               => __( 'Add New Product Type' ),
			'new_item_name'              => __( 'New Product Type Name' ),
			'separate_items_with_commas' => __( 'Separate Product Types with commas' ),
			'add_or_remove_items'        => __( 'Add or remove Product Types' ),
			'choose_from_most_used'      => __( 'Choose from the most used Product Types' ),
			'not_found'                  => __( 'No Product Types found.' ),
			'menu_name'                  => __( 'Product Types' ),
		);

		$args_model = array(
			'hierarchical'          => false,
			'labels'                => $labels_model,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'fktr-product-type' ),
			'description' => 'porcent'
		);

		register_taxonomy(
			'fktr_product_type',
			'fktr_product',
			$args_model
		);
		
		
		// model taxonomy
		$labels_model = array(
			'name'                       => _x( 'Taxes', 'Taxes' ),
			'singular_name'              => _x( 'Tax', 'Tax' ),
			'search_items'               => __( 'Search Taxes' ),
			'popular_items'              => __( 'Popular Taxes' ),
			'all_items'                  => __( 'All Taxes' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Tax' ),
			'update_item'                => __( 'Update Tax' ),
			'add_new_item'               => __( 'Add New Tax' ),
			'new_item_name'              => __( 'New Tax Name' ),
			'separate_items_with_commas' => __( 'Separate Taxes with commas' ),
			'add_or_remove_items'        => __( 'Add or remove Taxes' ),
			'choose_from_most_used'      => __( 'Choose from the most used Taxes' ),
			'not_found'                  => __( 'No Taxes found.' ),
			'menu_name'                  => __( 'Taxes' ),
		);

		$args_model = array(
			'hierarchical'          => false,
			'labels'                => $labels_model,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'fktr-tax' ),
			'description' => 'porcent'
		);

		register_taxonomy(
			'fktr_tax',
			'fktr_product',
			$args_model
		);
		
	}
	public static function styles() {
		global $post_type;
		if($post_type == 'fktr_product') {
			wp_enqueue_style('style-select2',FAKTURO_PLUGIN_URL .'assets/css/select2.min.css');	
			wp_enqueue_style('post-type-products',FAKTURO_PLUGIN_URL .'assets/css/post-type-products.css');	
		}
	}
	public static function scripts() {
		global $post_type;
		if($post_type == 'fktr_product') {
			wp_enqueue_script('webcam', FAKTURO_PLUGIN_URL .'assets/js/webcamjs-master/webcam.min.js', array('jquery'), WPE_FAKTURO_VERSION, true);
			wp_enqueue_script( 'jquery-snapshot', FAKTURO_PLUGIN_URL . 'assets/js/snapshot.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			wp_enqueue_script( 'jquery-select2', FAKTURO_PLUGIN_URL . 'assets/js/jquery.select2.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			wp_enqueue_script( 'jquery-vsort', FAKTURO_PLUGIN_URL . 'assets/js/jquery.vSort.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			wp_enqueue_script( 'jquery-mask', FAKTURO_PLUGIN_URL . 'assets/js/jquery.mask.min.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			wp_enqueue_script( 'post-type-products', FAKTURO_PLUGIN_URL . 'assets/js/post-type-products.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			
			$setting_system = get_option('fakturo_system_options_group', false);
			
			
			wp_localize_script('post-type-products', 'products_object',
				array('ajax_url' => admin_url( 'admin-ajax.php' ),
					'thousand' => $setting_system['thousand'],
					'decimal' => $setting_system['decimal']

				) );
			
			
		}
		
	}
	
	public static function meta_boxes() {
		
		//add_action('wp_ajax_webcam_shot', 'fakturo_ajax_webcam_shot');
		
		// Remove Custom Fields Metabox
		add_meta_box('fakturo-prices', __('Prices', FAKTURO_TEXT_DOMAIN ), array('fktrPostTypeProducts', 'prices_box'), 'fktr_product', 'side', 'high');
		remove_meta_box( 'postimagediv', 'fakturo_product', 'side' );
		add_meta_box('postimagediv', __('Product Image', FAKTURO_TEXT_DOMAIN ), array('fktrPostTypeProducts', 'fktr_post_thumbnail_meta_box'), 'fktr_product', 'side', 'high');
		add_meta_box( 'fakturo-seller-box', __('Assign Seller', FAKTURO_TEXT_DOMAIN ), array('fktrPostTypeProducts', 'fktr_product_seller_box'),'fktr_product','side', 'high' );
		add_meta_box( 'fakturo-data-box', __('Complete Product Data', FAKTURO_TEXT_DOMAIN ), array('fktrPostTypeProducts', 'data_box'),'fktr_product','normal', 'default' );

		add_meta_box( 'fakturo-price-box', __('Price', FAKTURO_TEXT_DOMAIN ), array('fktrPostTypeProducts', 'fktr_product_price_box'),'fktr_product','side', 'default' );
		add_meta_box( 'fakturo-stock-box', __('Stock', FAKTURO_TEXT_DOMAIN ), array('fktrPostTypeProducts', 'fktr_product_stock_box'),'fktr_product','normal', 'default' );
		
		do_action('add_ftkr_product_meta_boxes');
	}
	
	public static function fktr_post_thumbnail_meta_box() {
		
		
	}
	public static function fktr_product_seller_box() {
		
		
	}
	public static function prices_box() {
		global $post;
		$product_data = self::get_product_data($post->ID);
		$selectCurrencies = wp_dropdown_categories( array(
			'show_option_all'    => '',
			'show_option_none'   => __('Choose a Currency', FAKTURO_TEXT_DOMAIN ),
			'orderby'            => 'name', 
			'order'              => 'ASC',
			'show_count'         => 0,
			'hide_empty'         => 0, 
			'child_of'           => 0,
			'exclude'            => '',
			'echo'               => 0,
			'selected'           => $product_data['currency'],
			'hierarchical'       => 1, 
			'name'               => 'currency',
			'class'              => 'form-no-clear',
			'depth'              => 1,
			'tab_index'          => 0,
			'taxonomy'           => 'fktr_currencies',
			'hide_if_empty'      => false
		));		
		
		
		$echoHtml = '<table class="form-table">
					<tbody>
			<tr class="user-address-wrap">
				<th><label for="cost">'. __('Cost', FAKTURO_TEXT_DOMAIN ). '	</label></th>
				<td><input type="text" name="cost" id="cost" value="'.$product_data['cost'].'"></td>
			</tr>
			<tr class="user-address-wrap">
				<th><label for="currency">'.__('Currency', FAKTURO_TEXT_DOMAIN ).'</label></th>
				<td>
					'.$selectCurrencies.'
				</td>		
			</tr>
			</tbody>
		</table>';
	
		$echoHtml = apply_filters('fktr_product_prices_box', $echoHtml);
		echo $echoHtml;
		do_action('add_fktr_product_prices_box', $echoHtml);
		
	}

	public static function data_box() {
		global $post;
		
		$product_data = self::get_product_data($post->ID);
		
		$selectProvider = fakturo_get_select_post(array(
											'echo' => 0,
											'post_type' => 'fktr_provider',
											'show_option_none' => __('Choose a Provider', FAKTURO_TEXT_DOMAIN ),
											'name' => 'provider',
											'id' => 'provider',
											'class' => ''
										));
		$selectModel = wp_dropdown_categories( array(
			'show_option_all'    => '',
			'show_option_none'   => __('Choose a Model', FAKTURO_TEXT_DOMAIN ),
			'orderby'            => 'name', 
			'order'              => 'ASC',
			'show_count'         => 0,
			'hide_empty'         => 0, 
			'child_of'           => 0,
			'exclude'            => '',
			'echo'               => 0,
			'selected'           => $product_data['model'],
			'hierarchical'       => 1, 
			'name'               => 'model',
			'class'              => 'form-no-clear',
			'depth'              => 1,
			'tab_index'          => 0,
			'taxonomy'           => 'fktr_model',
			'hide_if_empty'      => false
		));			
		$selectCategory = wp_dropdown_categories( array(
			'show_option_all'    => '',
			'show_option_none'   => __('Choose a Category', FAKTURO_TEXT_DOMAIN ),
			'orderby'            => 'name', 
			'order'              => 'ASC',
			'show_count'         => 0,
			'hide_empty'         => 0, 
			'child_of'           => 0,
			'exclude'            => '',
			'echo'               => 0,
			'selected'           => $product_data['category'],
			'hierarchical'       => 1, 
			'name'               => 'category',
			'class'              => 'form-no-clear',
			'depth'              => 1,
			'tab_index'          => 0,
			'taxonomy'           => 'fktr_category',
			'hide_if_empty'      => false
		));
		$selectProductType = wp_dropdown_categories( array(
			'show_option_all'    => '',
			'show_option_none'   => __('Choose a Product Type', FAKTURO_TEXT_DOMAIN ),
			'orderby'            => 'name', 
			'order'              => 'ASC',
			'show_count'         => 0,
			'hide_empty'         => 0, 
			'child_of'           => 0,
			'exclude'            => '',
			'echo'               => 0,
			'selected'           => $product_data['product_type'],
			'hierarchical'       => 1, 
			'name'               => 'product_type',
			'class'              => 'form-no-clear',
			'depth'              => 1,
			'tab_index'          => 0,
			'taxonomy'           => 'fktr_product_type',
			'hide_if_empty'      => false
		));	

		$selectTax = wp_dropdown_categories( array(
			'show_option_all'    => '',
			'show_option_none'   => __('Choose a Tax', FAKTURO_TEXT_DOMAIN ),
			'orderby'            => 'name', 
			'order'              => 'ASC',
			'show_count'         => 0,
			'hide_empty'         => 0, 
			'child_of'           => 0,
			'exclude'            => '',
			'echo'               => 0,
			'selected'           => $product_data['tax'],
			'hierarchical'       => 1, 
			'name'               => 'tax',
			'class'              => 'form-no-clear',
			'depth'              => 1,
			'tab_index'          => 0,
			'taxonomy'           => 'fktr_tax',
			'hide_if_empty'      => false
		));		
		
		
		$echoHtml = '<table class="form-table">
					<tbody>
			
			<tr class="user-facebook-wrap">
				<th><label for="provider">'.__('Provider', FAKTURO_TEXT_DOMAIN ).'	</label></th>
				<td>'.$selectProvider.'</td>
			</tr>
			<tr class="user-facebook-wrap">
				<th><label for="product_type">'.__('Model', FAKTURO_TEXT_DOMAIN ).'	</label></th>
				<td>'.$selectModel.'</td>
			</tr>
			<tr class="user-facebook-wrap">
				<th><label for="product_type">'.__('Category', FAKTURO_TEXT_DOMAIN ).'	</label></th>
				<td>'.$selectCategory.'</td>
			</tr>
			<tr class="user-facebook-wrap">
				<th><label for="product_type">'.__('Product Type', FAKTURO_TEXT_DOMAIN ).'	</label></th>
				<td>'.$selectProductType.'</td>
			</tr>
			<tr class="user-facebook-wrap">
				<th><label for="tax">'.__('Tax', FAKTURO_TEXT_DOMAIN ).'	</label></th>
				<td>'.$selectTax.'</td>
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
	public static function clean_fields($fields) {
		
		if (!isset($fields['cost'])) {
			$fields['cost'] = '0';
		}
		if (!isset($fields['currency'])) {
			$fields['currency'] = 0;
		}

		if (!isset($fields['model'])) {
			$fields['model'] = 0;
		}
		if (!isset($fields['category'])) {
			$fields['category'] = 0;
		}
		if (!isset($fields['product_type'])) {
			$fields['product_type'] = 0;
		}
		if (!isset($fields['tax'])) {
			$fields['tax'] = 0;
		}
		
		
		
		if (!isset($fields['reference'])) {
			$fields['reference'] = '';
		}
		if (!isset($fields['internal'])) {
			$fields['internal'] = '';
		}
		if (!isset($fields['manufacturers'])) {
			$fields['manufacturers'] = '';
		}
		if (!isset($fields['description'])) {
			$fields['description'] = '';
		}
		if (!isset($fields['short'])) {
			$fields['short'] = '';
		}
		if (!isset($fields['min'])) {
			$fields['min'] = '';
		}
		if (!isset($fields['min_alert'])) {
			$fields['min_alert'] = '';
		}
		if (!isset($fields['unit'])) {
			$fields['unit'] = '';
		}
		if (!isset($fields['note'])) {
			$fields['note'] = '';
		}
		
		return $fields;
	}
	public static function before_save($fields) {
		$setting_system = get_option('fakturo_system_options_group', false);
		if (!isset($fields['cost'])) {
			$fields['cost'] = '0';
		}
		if (strpos($fields['cost'], $setting_system['decimal']) !== false) {
			$pieceNumber = explode($setting_system['decimal'], $fields['cost']);
			$pieceNumber[0] = str_replace($setting_system['thousand'], '', $pieceNumber[0]);
			$fields['cost'] = implode('.', $pieceNumber);
			$fields['cost'] = filter_var($fields['cost'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
		}
		
		return $fields;
	}
	public static function default_fields($new_status, $old_status, $post ) {
		
		if( $post->post_type == 'fktr_product' && $old_status == 'new'){		
			
			$fields = array();
			$fields['cost'] = '0';
			$fields['currency'] = 0;
			
			
			$fields['model'] = 0;
			$fields['category'] = 0;
			$fields['product_type'] = 0;
			$fields['tax'] = 0;
			
			
			
			$fields['reference'] = '';
			$fields['internal'] = '';
			$fields['manufacturers'] = '';
			$fields['description'] = '';
			$fields['short'] = '';
			$fields['min'] = '';
			$fields['min_alert'] = '';
			$fields['unit'] = '';
			$fields['note'] = '';
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
	
	public static function save($post_id, $post) {
		
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX') && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) {
			return false;
		}

		if ( isset( $post->post_type ) && 'revision' == $post->post_type ) {
			return false;
		}

		if ( ! current_user_can( 'manage_options', $post_id ) ) {
			return false;
		}
		
		$fields = apply_filters('fktr_clean_product_fields',$_POST);
		$fields = apply_filters('fktr_product_before_save',$fields);
		
		if (isset($fields['webcam_image']) && $fields['webcam_image'] != NULL ) {
			delete_post_meta($post_id, '_thumbnail_id');
			$filename = 'webcam_image_'.microtime().'.jpg';
			$file = wp_upload_bits($filename, null, base64_decode(substr($fields['webcam_image'], 23)));
			if ($file['error'] == FALSE) {
				$wp_filetype = wp_check_filetype($filename, null );
				$attachment = array(
					'post_mime_type' => $wp_filetype['type'],
					'post_parent' => $post_id,
					'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
					'post_content' => '',
					'post_status' => 'inherit'
				);
				$attachment_id = wp_insert_attachment( $attachment, $file['file'], $post_id );
				if (!is_wp_error($attachment_id)) {
					require_once(ABSPATH . "wp-admin" . '/includes/image.php');
					$attachment_data = wp_generate_attachment_metadata( $attachment_id, $file['file'] );
					wp_update_attachment_metadata( $attachment_id,  $attachment_data );
				}
				$new = apply_filters('fktr_product_thumbnail_id', $attachment_id);
				add_post_meta($post_id, '_thumbnail_id', $new);
				unset($fields['webcam_image']);
			}
		}
		
		
		
		foreach ($fields as $field => $value ) {
			
			if ( !is_null( $value ) ) {
				$new = apply_filters('fktr_product_metabox_save_' . $field, $value );  //filtra cada campo antes de grabar
				update_post_meta( $post_id, $field, $new );
				
			}
			
		}
		do_action( 'fktr_save_product', $post_id, $post );
		
	}
	
	
} 

endif;

$fktrPostTypeProducts = new fktrPostTypeProducts();

?>