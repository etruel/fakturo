<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('fktrPostTypeProducts')) :

	class fktrPostTypeProducts {

		function __construct() {

			add_action('init', array('fktrPostTypeProducts', 'setup'), 1);
			add_action('fakturo_activation', array('fktrPostTypeProducts', 'setup'), 1);
			add_action('transition_post_status', array('fktrPostTypeProducts', 'default_fields'), 10, 3);
			add_action('save_post', array('fktrPostTypeProducts', 'save'), 999, 2);

			add_action('admin_print_scripts-post-new.php', array('fktrPostTypeProducts', 'scripts'), 11);
			add_action('admin_print_scripts-post.php', array('fktrPostTypeProducts', 'scripts'), 11);
			add_action('admin_print_styles-post-new.php', array('fktrPostTypeProducts', 'styles'));
			add_action('admin_print_styles-post.php', array('fktrPostTypeProducts', 'styles'));

			add_filter('parent_file', array(__CLASS__, 'tax_menu_correction'));
			add_filter('redirect_term_location', array(__CLASS__, 'redirect_term_location'), 0, 2);

			add_filter('fktr_clean_product_fields', array('fktrPostTypeProducts', 'clean_fields'), 10, 1);
			add_filter('fktr_product_before_save', array('fktrPostTypeProducts', 'before_save'), 10, 1);

			add_filter('manage_fktr_product_posts_columns', array(__CLASS__, 'columns'));
			//add_filter('manage_edit-fktr_product_columns', array( __CLASS__, 'columns'), 10);  // Nueva
			add_filter('manage_fktr_product_posts_custom_column', array(__CLASS__, 'manage_columns'), 10, 2);

			add_filter('manage_edit-fktr_product_sortable_columns', array(__CLASS__, 'sortable_columns'));
			//add_action('pre_get_posts', array(__CLASS__, 'column_orderby') );
			add_action('parse_query', array(__CLASS__, 'column_orderby'));

			add_action('posts_search', array(__CLASS__, 'custom_search_query'), 10, 2);
		}

		public static function custom_search_query($search, $query) {
			global $wpdb, $pagenow, $post_type;
			if ('edit.php' != $pagenow || $post_type != 'fktr_product')
				return;
			if ($query->is_main_query() && !empty($query->query['s'])) {
				$sql = "
				or exists (
					select * from {$wpdb->postmeta} where post_id={$wpdb->posts}.ID
					and meta_key in ('reference','custom_field2')
					and meta_value like %s
					)
				";
				$like = '%' . $wpdb->esc_like($query->query['s']) . '%';
				$search = preg_replace("#\({$wpdb->posts}.post_title LIKE [^)]+\)\K#",
						$wpdb->prepare($sql, $like), $search);
			}
			return $search;
		}

		// Make these columns sortable
		public static function sortable_columns($columns) {
			$custom = array(
				'reference' => 'reference',
			);
			return wp_parse_args($custom, $columns);
		}

		public static function column_orderby($query) {
			global $pagenow, $post_type;
			$orderby = $query->get('orderby');
			if ('edit.php' != $pagenow || empty($orderby) || $post_type != 'fktr_product')
				return;
			switch ($orderby) {
				case 'reference':
					$meta_group = array('key' => 'reference', 'type' => 'string');
					$query->set('meta_query', array('sort_column' => 'reference', $meta_group));
					$query->set('meta_key', 'reference');
					$query->set('orderby', 'meta_value');

					break;

				default:
					break;
			}
		}

		public static function columns($columns) {
			$column_reference_pos = 2;
			$column_reference = ['reference' => __('Referencia', 'fakturo')];
			// 2nd column
			$columns = array_slice($columns, 0, $column_reference_pos, true) + $column_reference + array_slice($columns, $column_reference_pos, NULL, true);
			//$columns		 = array_merge($columns, $column_reference);
			return $columns;
		}

		public static function manage_columns($column, $post_id) {
			$product_data = self::get_product_data($post_id);
			switch ($column) {
				case 'reference':
					if ($product_data['reference'] == "") {
						_e('', 'fakturo');
					} else {
						echo $product_data['reference'];
					}
					break;
			}
		}

		static function redirect_term_location($location, $tax) {
			if ($tax->name == 'fktr_category' || $tax->name == 'fktr_model') {
				$location = admin_url('edit-tags.php?taxonomy=' . $tax->name);
			}
			return $location;
		}

		public static function setup() {
			$slug = defined('FAKTURO_PRODUCT_SLUG') ? FAKTURO_PRODUCT_SLUG : 'fktr_products';
			$labels = array(
				'name' => __('Products', 'fakturo'),
				'singular_name' => __('Product', 'fakturo'),
				'add_new' => __('Add New', 'fakturo'),
				'add_new_item' => __('Add New Product', 'fakturo'),
				'edit_item' => __('Edit Product', 'fakturo'),
				'new_item' => __('New Product', 'fakturo'),
				'view_item' => __('View Product', 'fakturo'),
				'search_items' => __('Search Products', 'fakturo'),
				'not_found' => __('No products found', 'fakturo'),
				'not_found_in_trash' => __('No products found in Trash', 'fakturo'),
				'parent_item_colon' => __('Parent Product:', 'fakturo'),
				'menu_name' => __('Products', 'fakturo'),
			);
			$capabilities = array(
				'publish_post' => 'publish_fktr_product',
				'publish_posts' => 'publish_fktr_products',
				'read_post' => 'read_fktr_product',
				'read_private_posts' => 'read_private_fktr_products',
				'edit_post' => 'edit_fktr_product',
				'edit_published_posts' => 'edit_published_fktr_products',
				'edit_private_posts' => 'edit_private_fktr_products',
				'edit_posts' => 'edit_fktr_products',
				'edit_others_posts' => 'edit_others_fktr_products',
				'delete_post' => 'delete_fktr_product',
				'delete_posts' => 'delete_fktr_products',
				'delete_published_posts' => 'delete_published_fktr_products',
				'delete_private_posts' => 'delete_private_fktr_products',
				'delete_others_posts' => 'delete_others_fktr_products',
			);
			$args = array(
				'labels' => $labels,
				'hierarchical' => false,
				'description' => 'Fakturo Products',
				'supports' => array('title', 'thumbnail', /* 'custom-fields' */),
				'register_meta_box_cb' => array('fktrPostTypeProducts', 'meta_boxes'),
				'public' => false,
				'show_ui' => true,
				'show_in_menu' => false,
				'menu_position' => 27,
				'menu_icon' => 'dashicons-images-alt2',
				'show_in_nav_menus' => false,
				'publicly_queryable' => false,
				'exclude_from_search' => false,
				'has_archive' => false,
				'query_var' => true,
				'can_export' => true,
				'rewrite' => true,
				'capabilities' => $capabilities
			);
			register_post_type('fktr_product', $args);
			// category taxonomy
			$labels_model = array(
				'name' => _x('Categories', 'Categories', 'fakturo'),
				'singular_name' => _x('Category', 'Category', 'fakturo'),
				'search_items' => __('Search Categories', 'fakturo'),
				'popular_items' => __('Popular Categories', 'fakturo'),
				'all_items' => __('All Categories', 'fakturo'),
				'parent_item' => __('Parent Category', 'fakturo'),
				'parent_item_colon' => null,
				'edit_item' => __('Edit Category', 'fakturo'),
				'update_item' => __('Update Category', 'fakturo'),
				'add_new_item' => __('Add New Category', 'fakturo'),
				'new_item_name' => __('New Category Name', 'fakturo'),
				'separate_items_with_commas' => __('Separate categories with commas', 'fakturo'),
				'add_or_remove_items' => __('Add or remove categories', 'fakturo'),
				'choose_from_most_used' => __('Choose from the most used categories', 'fakturo'),
				'not_found' => __('No categories found.', 'fakturo'),
				'menu_name' => __('Categories', 'fakturo'),
			);
			$args_model = array(
				'public' => false,
				'hierarchical' => true,
				'labels' => $labels_model,
				'show_ui' => true,
				'show_admin_column' => true,
				'query_var' => true,
				'rewrite' => array('slug' => 'fktr-category'),
				'capabilities' => array(
					'manage_terms' => 'manage_fktr_category',
					'edit_terms' => 'edit_fktr_category',
					'delete_terms' => 'delete_fktr_category',
					'assign_terms' => 'assign_fktr_category'
				)
			);
			register_taxonomy(
					'fktr_category',
					'fktr_product',
					$args_model
			);

			// model taxonomy
			$labels_model = array(
				'name' => _x('Models', 'Models', 'fakturo'),
				'singular_name' => _x('Model', 'Model', 'fakturo'),
				'search_items' => __('Search Models', 'fakturo'),
				'popular_items' => __('Popular Models', 'fakturo'),
				'all_items' => __('All Models', 'fakturo'),
				'parent_item' => null,
				'parent_item_colon' => null,
				'edit_item' => __('Edit Model', 'fakturo'),
				'update_item' => __('Update Model', 'fakturo'),
				'add_new_item' => __('Add New Model', 'fakturo'),
				'new_item_name' => __('New Model Name', 'fakturo'),
				'separate_items_with_commas' => __('Separate models with commas', 'fakturo'),
				'add_or_remove_items' => __('Add or remove models', 'fakturo'),
				'choose_from_most_used' => __('Choose from the most used models', 'fakturo'),
				'not_found' => __('No models found.', 'fakturo'),
				'menu_name' => __('Models', 'fakturo'),
			);
			$args_model = array(
				'public' => false,
				'hierarchical' => false,
				'labels' => $labels_model,
				'show_ui' => true,
				'show_admin_column' => true,
				'query_var' => true,
				'rewrite' => array('slug' => 'fktr-model'),
				'capabilities' => array(
					'manage_terms' => 'manage_fktr_model',
					'edit_terms' => 'edit_fktr_model',
					'delete_terms' => 'delete_fktr_model',
					'assign_terms' => 'assign_fktr_model'
				)
			);

			register_taxonomy(
					'fktr_model',
					'fktr_product',
					$args_model
			);

			add_filter('enter_title_here', array('fktrPostTypeProducts', 'name_placeholder'), 10, 2);
		}

		// highlight the proper top level menu
		static function tax_menu_correction($parent_file) {
			global $current_screen;
			if ($current_screen->id == "edit-fktr_category" || $current_screen->id == "edit-fktr_model") {
				$parent_file = 'edit.php?post_type=fktr_product';
			}
			return $parent_file;
		}

		public static function name_placeholder($title_placeholder, $post) {
			if ($post->post_type == 'fktr_product') {
				$title_placeholder = __('Enter Product name here', 'fakturo');
			}
			return $title_placeholder;
		}

		public static function styles() {
			global $post_type;
			if ($post_type == 'fktr_product') {
				wp_enqueue_style('style-select2', FAKTURO_PLUGIN_URL . 'assets/css/select2.min.css');
				wp_enqueue_style('post-type-products', FAKTURO_PLUGIN_URL . 'assets/css/post-type-products.css');
			}
		}

		public static function scripts() {
			global $post_type;
			if ($post_type == 'fktr_product') {
				wp_enqueue_script('webcam', FAKTURO_PLUGIN_URL . 'assets/js/webcamjs-master/webcam.min.js', array('jquery'), WPE_FAKTURO_VERSION, true);
				wp_enqueue_script('jquery-snapshot', FAKTURO_PLUGIN_URL . 'assets/js/snapshot.js', array('jquery'), WPE_FAKTURO_VERSION, true);
				wp_enqueue_script('jquery-select2', FAKTURO_PLUGIN_URL . 'assets/js/jquery.select2.js', array('jquery'), WPE_FAKTURO_VERSION, true);
				wp_enqueue_script('jquery-vsort', FAKTURO_PLUGIN_URL . 'assets/js/jquery.vSort.js', array('jquery'), WPE_FAKTURO_VERSION, true);
				wp_enqueue_script('jquery-mask', FAKTURO_PLUGIN_URL . 'assets/js/jquery.mask.min.js', array('jquery'), WPE_FAKTURO_VERSION, true);
				wp_enqueue_script('post-type-products', FAKTURO_PLUGIN_URL . 'assets/js/post-type-products.js', array('jquery'), WPE_FAKTURO_VERSION, true);
				$setting_system = get_option('fakturo_system_options_group', false);
				$taxes = get_fakturo_terms(array(
					'taxonomy' => 'fktr_tax',
					'hide_empty' => false,
				));
				wp_localize_script('post-type-products', 'products_object',
						array('ajax_url' => admin_url('admin-ajax.php'),
							'thousand' => $setting_system['thousand'],
							'decimal' => $setting_system['decimal'],
							'decimal_numbers' => $setting_system['decimal_numbers'],
							'taxes' => json_encode($taxes)
				));
			}
		}

		public static function meta_boxes() {
			//add_action('wp_ajax_webcam_shot', 'fakturo_ajax_webcam_shot');
			// Remove Custom Fields Metabox
			add_meta_box('fakturo-price-box', __('Price', 'fakturo'), array('fktrPostTypeProducts', 'price_box'), 'fktr_product', 'side', 'high');
			add_meta_box('fakturo-prices', __('Prices', 'fakturo'), array('fktrPostTypeProducts', 'prices_box'), 'fktr_product', 'normal', 'default');

			remove_meta_box('postimagediv', 'fakturo_product', 'side');
			add_meta_box('postimagediv', __('Product Image', 'fakturo'), array('fktrPostTypeProducts', 'thumbnail_meta_box'), 'fktr_product', 'side', 'high');
			add_meta_box('fakturo-data-box', __('Complete Product Data', 'fakturo'), array('fktrPostTypeProducts', 'data_box'), 'fktr_product', 'normal', 'default');
			add_meta_box('fakturo-stock-box', __('Stock', 'fakturo'), array('fktrPostTypeProducts', 'stock_box'), 'fktr_product', 'side', 'high');

			do_action('add_ftkr_product_meta_boxes');
		}

		public static function thumbnail_meta_box() {
			global $post;
			$thumbnail_id = get_post_meta($post->ID, '_thumbnail_id', true);
			$echoHtml = '
			<div id="snapshot_container_wrapper">
				<div id="snapshot_container_buttons">
					<a id="snapshot_btn" href="javascript:showSnapshot()" class="nobutton">' . __('Take a snapshot', 'fakturo') . '</a>
					<div id="my_camera" style="display:none;">				
					</div>
					<img src="" id="snap_image" style="display:none;">
					<input type="hidden" name="webcam_image">
					<a href="javascript:take_snapshot()" class="button" id="take_snapshot" style="display:none;">' . __('Snapshot') . '</a>
					<a href="javascript:reset_webcam()" class="button" id="snapshot_reset" style="display:none;">' . __('Reset') . '</a>
					<a href="javascript:snapshot_cancel()" class="button" id="snapshot_cancel" style="display:none;">' . __('Cancel') . '</a>
				</div>
			</div>
			<div class="featured-image-client">
							' . _wp_post_thumbnail_html($thumbnail_id, $post->ID) . '
				</div>
			';

			$echoHtml = apply_filters('fktr_product_thumbnail_box', $echoHtml);
			echo $echoHtml;

			do_action('add_fktr_product_thumbnail_box', $echoHtml);
		}

		public static function price_box() {
			global $post;

			$product_data = self::get_product_data($post->ID);
			$setting_system = get_option('fakturo_system_options_group', false);

			$currency = (isset($product_data['currency']) && !empty($product_data['currency']) ) ? $product_data['currency'] : $setting_system['currency'];

			$selectCurrencies = wp_dropdown_categories(array(
				'show_option_all' => '',
				'show_option_none' => __('Choose a Currency', 'fakturo'),
				'orderby' => 'name',
				'order' => 'ASC',
				'show_count' => 0,
				'hide_empty' => 0,
				'child_of' => 0,
				'exclude' => '',
				'echo' => 0,
				'selected' => $currency,
				'hierarchical' => 1,
				'name' => 'currency',
				'id' => 'currency',
				'class' => '',
				'depth' => 1,
				'tab_index' => 0,
				'taxonomy' => 'fktr_currencies',
				'hide_if_empty' => false
			));
			$echoHtml = '<table class="form-table">
				<tbody>
				<tr class="user-address-wrap">
					<th><label for="cost">' . __('Cost', 'fakturo') . '	</label></th>
					<td><input type="text" name="cost" id="cost" value="' . number_format($product_data['cost'], $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']) . '"></td>
				</tr>
				<tr class="user-address-wrap">
					<th><label for="currency">' . __('Currency', 'fakturo') . '</label></th>
					<td>
						' . $selectCurrencies . '
					</td>		
				</tr>
				</tbody>
			</table>';

			$echoHtml = apply_filters('fktr_product_prices_box', $echoHtml);
			echo $echoHtml;
			do_action('add_fktr_product_prices_box', $echoHtml);
		}

		public static function prices_box() {
			global $post;
			$product_data = self::get_product_data($post->ID);
			$setting_system = get_option('fakturo_system_options_group', false);

			$terms = get_fakturo_terms(array(
				'taxonomy' => 'fktr_price_scales',
				'hide_empty' => false,
			));

			$echoHtml = '<table class="form-table">
			<tr class="tr_fktr">
				<th style="text-align: center;">' . __('Scale', 'fakturo') . '</th>
				<th style="text-align: center;">' . __('Price', 'fakturo') . '</th>
				<th style="text-align: center;">' . __('Suggested', 'fakturo') . '</th>
				<th style="text-align: center;">' . __('Final', 'fakturo') . '</th>
			</tr>
			';
			$product_tax = get_fakturo_term($product_data['tax'], 'fktr_tax');
			if (!empty($terms)) {  // if no scale prices
				foreach ($terms as $t) {
					if (empty($product_data['prices'][$t->term_id])) {
						$product_data['prices'][$t->term_id] = ($product_data['cost'] != 0) ? ((($product_data['cost'] / 100) * $t->percentage) + $product_data['cost']) : 0;
					}
					if (empty($product_data['prices_final'][$t->term_id])) {

						$tax_porcent = 0;
						if (!is_wp_error($product_tax)) {
							$tax_porcent = $product_tax->percentage;
						}

						$product_data['prices_final'][$t->term_id] = ($product_data['prices'][$t->term_id] != 0) ? ((($product_data['prices'][$t->term_id] / 100) * $tax_porcent) + $product_data['prices'][$t->term_id]) : 0;
					}

					$echoHtml .= '<tr class="pricestr" data-id="' . $t->term_id . '" data-porcentage="' . $t->percentage . '">
						<td style="text-align: center;">' . $t->name . ' (' . $t->percentage . '%)</td>
						<td style="text-align: center;"><input type="text" value="' . (isset($product_data['prices'][$t->term_id]) ? number_format($product_data['prices'][$t->term_id], $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']) : '') . '"  id="prices_' . $t->term_id . '" class="prices" name="prices[' . $t->term_id . ']"/></td>
						<td style="text-align: center;" id="suggested_' . $t->term_id . '"></td>
						<td style="text-align: center;"><input type="text" value="' . (isset($product_data['prices_final'][$t->term_id]) ? number_format($product_data['prices_final'][$t->term_id], $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']) : '') . '" id="prices_final_' . $t->term_id . '" class="prices_final" name="prices_final[' . $t->term_id . ']"/></td>
					</tr>';
				}
			} else {
				$echoHtml .= '<tr><td colspan="4" style="text-align: center;font-size: large;" align="center">' . __('Scale prices missed. Go to Settings to add them: ', 'fakturo') . '';
				$echoHtml .= '<a href="' . admin_url('edit-tags.php?taxonomy=fktr_price_scales') . '">' . __('Scale prices', 'fakturo') . '</a></td></tr>';
			}

			$echoHtml .= '</table>';

			$echoHtml = apply_filters('fktr_product_prices_box', $echoHtml);
			echo $echoHtml;

			do_action('add_fktr_product_prices_box', $echoHtml);
		}

		public static function stock_box() {
			global $post;
			$product_data = self::get_product_data($post->ID);
			$terms = get_fakturo_terms(array(
				'taxonomy' => 'fktr_locations',
				'hide_empty' => false,
			));
			$echoHtml = '<table>
			<tr class="tr_fktr">
				<th style="text-align: left;">' . __('Location', 'fakturo') . '</th>
				<th style="text-align: center;">' . __('Quantity', 'fakturo') . '</th>
			</tr>';
			$total = 0;
			foreach ($terms as $t) {
				$total = $total + (isset($product_data['stocks'][$t->term_id]) ? $product_data['stocks'][$t->term_id] : 0);
				$echoHtml .= '<tr>
					<td>' . $t->name . ': </td>
					<td style="text-align: center;">' . (isset($product_data['stocks'][$t->term_id]) ? $product_data['stocks'][$t->term_id] : '0') . '</td>
				</tr>';
			}
			$echoHtml .= '<tr>
					<td>' . __('Total', 'fakturo') . ':</td>
					<td style="text-align: center;">' . $total . '</td>
				</tr>
			</table>';

			$echoHtml = apply_filters('fktr_product_stock_box', $echoHtml);
			echo $echoHtml;

			do_action('add_fktr_product_stock_box', $echoHtml);
		}

		public static function data_box() {
			global $post;

			$product_data = self::get_product_data($post->ID);

			$setting_system = get_option('fakturo_system_options_group', false);

			$selectProvider = fakturo_get_select_post(array(
				'echo' => 0,
				'post_type' => 'fktr_provider',
				'show_option_none' => __('Choose a Provider', 'fakturo'),
				'name' => 'provider',
				'id' => 'provider',
				'selected' => $product_data['provider'],
				'class' => ''
			));

			$selectModel = wp_dropdown_categories(array(
				'show_option_all' => '',
				'show_option_none' => __('Choose a Model', 'fakturo'),
				'orderby' => 'name',
				'order' => 'ASC',
				'show_count' => 0,
				'hide_empty' => 0,
				'child_of' => 0,
				'exclude' => '',
				'echo' => 0,
				'selected' => $product_data['model'],
				'hierarchical' => 1,
				'name' => 'model',
				'class' => 'form-no-clear',
				'depth' => 1,
				'tab_index' => 0,
				'taxonomy' => 'fktr_model',
				'hide_if_empty' => false
			));
			$selectCategory = wp_dropdown_categories(array(
				'show_option_all' => '',
				'show_option_none' => __('Choose a Category', 'fakturo'),
				'orderby' => 'name',
				'order' => 'ASC',
				'show_count' => 0,
				'hide_empty' => 0,
				'child_of' => 0,
				'exclude' => '',
				'echo' => 0,
				'selected' => $product_data['category'],
				'hierarchical' => 1,
				'name' => 'category',
				'class' => 'form-no-clear',
				'depth' => 1,
				'tab_index' => 0,
				'taxonomy' => 'fktr_category',
				'hide_if_empty' => false
			));
			$selectProductType = wp_dropdown_categories(array(
				'show_option_all' => '',
				'show_option_none' => __('Choose a Product Type', 'fakturo'),
				'orderby' => 'name',
				'order' => 'ASC',
				'show_count' => 0,
				'hide_empty' => 0,
				'child_of' => 0,
				'exclude' => '',
				'echo' => 0,
				'selected' => $product_data['product_type'],
				'hierarchical' => 1,
				'name' => 'product_type',
				'class' => 'form-no-clear',
				'depth' => 1,
				'tab_index' => 0,
				'taxonomy' => 'fktr_product_type',
				'hide_if_empty' => false
			));

			$selectTax = wp_dropdown_categories(array(
				'show_option_all' => '',
				'show_option_none' => __('Choose a Tax', 'fakturo'),
				'orderby' => 'name',
				'order' => 'ASC',
				'show_count' => 0,
				'hide_empty' => 0,
				'child_of' => 0,
				'exclude' => '',
				'echo' => 0,
				'selected' => $product_data['tax'],
				'hierarchical' => 1,
				'name' => 'tax',
				'class' => 'form-no-clear',
				'depth' => 1,
				'tab_index' => 0,
				'taxonomy' => 'fktr_tax',
				'hide_if_empty' => false
			));

			$selectPackaging = wp_dropdown_categories(array(
				'show_option_all' => '',
				'show_option_none' => __('Choose a Packaging', 'fakturo'),
				'orderby' => 'name',
				'order' => 'ASC',
				'show_count' => 0,
				'hide_empty' => 0,
				'child_of' => 0,
				'exclude' => '',
				'echo' => 0,
				'selected' => $product_data['packaging'],
				'hierarchical' => 1,
				'name' => 'packaging',
				'class' => 'form-no-clear',
				'depth' => 1,
				'tab_index' => 0,
				'taxonomy' => 'fktr_packaging',
				'hide_if_empty' => false
			));
			$selectOrigin = wp_dropdown_categories(array(
				'show_option_all' => '',
				'show_option_none' => __('Choose a Origin', 'fakturo'),
				'orderby' => 'name',
				'order' => 'ASC',
				'show_count' => 0,
				'hide_empty' => 0,
				'child_of' => 0,
				'exclude' => '',
				'echo' => 0,
				'selected' => $product_data['origin'],
				'hierarchical' => 1,
				'name' => 'origin',
				'class' => 'form-no-clear',
				'depth' => 1,
				'tab_index' => 0,
				'taxonomy' => 'fktr_origins',
				'hide_if_empty' => false
			));

			$echoHtml = '<table class="form-table">
			<tbody>
			<tr class="tr_fktr">
				<th><label for="provider">' . __('Provider', 'fakturo') . '	</label></th>
				<td>' . $selectProvider . '</td>
			</tr>
			<tr class="tr_fktr">
				<th><label for="product_type">' . __('Product Type', 'fakturo') . '	</label></th>
				<td>' . $selectProductType . '</td>
			</tr>
			<tr class="tr_fktr">
				<th><label for="tax">' . __('Tax', 'fakturo') . '	</label></th>
				<td>' . $selectTax . '</td>
			</tr>
			<tr class="user-address-wrap">
				<th><label for="reference">' . __('Reference', 'fakturo') . '</label></th>
				<td><input type="text" name="reference" id="reference" value="' . $product_data['reference'] . '" class="regular-text"></td>
			</tr>
			<tr class="user-address-wrap">
				<th><label for="internal">' . __('Internal code', 'fakturo') . '</label></th>
				<td id="td_internal_code">' . (isset($product_data['ID']) ? $product_data['ID'] : '') . '</td>
			</tr>
			<tr class="user-address-wrap">
				<th><label for="manufacturers">' . __('Manufacturers code', 'fakturo') . '</label></th>
				<td><input type="text" name="manufacturers" id="manufacturers" value="' . $product_data['manufacturers'] . '" class="regular-text"></td>
			</tr>
			<tr class="user-address-wrap">
				<th><label for="description">' . __('Description', 'fakturo') . '</label></th>
				<td>
				<textarea class="mw-95" rows="4" name="description" id="description">' . $product_data['description'] . '</textarea>
				</td>
			</tr>';

			if (isset($setting_system['use_stock_product']) && $setting_system['use_stock_product']) {

				$echoHtml .= '<tr class="user-address-wrap">
					<th><label for="min">' . __('Minimal stock', 'fakturo') . '</label></th>
					<td><input type="number" name="min" id="min" value="' . $product_data['min'] . '" class="regular-text"></td>
				</tr>
				<tr class="tr_fktr">
					<th><label for="min_alert">' . __('Minimal stock alert', 'fakturo') . '</label></th>
					<td>
					<input id="min_alert" class="slidercheck" type="checkbox" name="min_alert" value="1" ' . (($product_data['min_alert']) ? 'checked="checked"' : '') . '>
					<label for="min_alert"><span class="ui"></span>' . __('Minimal stock alert', 'fakturo') . '	</label>
				</td>
				</tr>';
			}
			$echoHtml .= '<tr class="tr_fktr">
				<th><label for="tax">' . __('Packaging', 'fakturo') . '</label></th>
				<td>' . $selectPackaging . '</td> 
			</tr>
			<tr class="user-address-wrap">
				<th><label for="unit">' . __('Units per package', 'fakturo') . '</label></th>
				<td><input type="text" name="unit" id="unit" value="' . $product_data['unit'] . '" class="regular-text"></td>
			</tr>
			<tr class="user-address-wrap">
				<th><label for="note">' . __('Notes', 'fakturo') . '</label></th>
				<td><textarea class="mw-95" rows="4" name="note" id="note">' . $product_data['note'] . '</textarea></td>
			</tr>
			<tr class="user-address-wrap">
				<th><label for="origin">' . __('Origin', 'fakturo') . '</label></th>
				<td>' . $selectOrigin . '</td>
			</tr>
			</tbody>
			</table>';

			$echoHtml = apply_filters('fktr_product_data_box', $echoHtml);
			echo $echoHtml;

			do_action('add_fktr_product_data_box', $echoHtml);
		}

		public static function getStocks($product_id) {
			$retorno = array();
			$product_data = self::get_product_data($product_id);
			$locations = get_fakturo_terms(array(
				'taxonomy' => 'fktr_locations',
				'hide_empty' => false,
			));
			if (!isset($product_data['stocks'])) {
				$product_data['stocks'] = array();
			}
			if (!is_array($product_data['stocks'])) {
				$product_data['stocks'] = array();
			}
			foreach ($locations as $location) {
				if (!empty($product_data['stocks'][$location->term_id])) {
					$retorno[$location->term_id] = $product_data['stocks'][$location->term_id];
				} else {
					$retorno[$location->term_id] = 0;
				}
			}
			return $retorno;
		}

		public static function addStock($product_id, $quantity, $location_id) {
			$product_data = self::get_product_data($product_id);
			if (!isset($product_data['stocks'])) {
				$product_data['stocks'] = array();
			}
			if (!is_array($product_data['stocks'])) {
				$product_data['stocks'] = array();
			}
			$quantity_total = 0;
			if (!empty($product_data['stocks'][$location_id])) {
				$quantity_total = $product_data['stocks'][$location_id];
			}
			$quantity_total = $quantity_total + $quantity;
			$product_data['stocks'][$location_id] = $quantity_total;
			$newQuantity = apply_filters('fktr_product_metabox_save_stocks', $product_data['stocks']);
			update_post_meta($product_id, 'stocks', $newQuantity);
		}

		public static function removeStock($product_id, $quantity, $location_id) {
			$product_data = self::get_product_data($product_id);
			if (!isset($product_data['stocks'])) {
				$product_data['stocks'] = array();
			}
			if (!is_array($product_data['stocks'])) {
				$product_data['stocks'] = array();
			}
			$quantity_total = 0;
			if (!empty($product_data['stocks'][$location_id])) {
				$quantity_total = $product_data['stocks'][$location_id];
			}
			$quantity_total = $quantity_total - $quantity;
			$product_data['stocks'][$location_id] = $quantity_total;
			$newQuantity = apply_filters('fktr_product_metabox_save_stocks', $product_data['stocks']);
			update_post_meta($product_id, 'stocks', $newQuantity);
		}

		public static function clean_fields($fields) {
			if (!isset($fields['cost'])) {
				$fields['cost'] = 0;
			}
			if (!isset($fields['currency'])) {
				$fields['currency'] = 0;
			}

			if (!isset($fields['provider'])) {
				$fields['provider'] = 0;
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
			if (!isset($fields['manufacturers'])) {
				$fields['manufacturers'] = '';
			}
			if (!isset($fields['description'])) {
				$fields['description'] = '';
			}

			if (!isset($fields['min'])) {
				$fields['min'] = '';
			}
			if (!isset($fields['min_alert'])) {
				$fields['min_alert'] = '';
			}
			if (!isset($fields['packaging'])) {
				$fields['packaging'] = 0;
			}
			if (!isset($fields['unit'])) {
				$fields['unit'] = '';
			}
			if (!isset($fields['note'])) {
				$fields['note'] = '';
			}
			if (!isset($fields['origin'])) {
				$fields['origin'] = 0;
			}
			if (!isset($fields['prices'])) {
				$fields['prices'] = array();
			}
			if (!isset($fields['prices_final'])) {
				$fields['prices_final'] = array();
			}

			return $fields;
		}

		public static function before_save($fields) {
			$setting_system = get_option('fakturo_system_options_group', false);
			$fields['cost'] = fakturo_mask_to_float($fields['cost']);
			foreach ($fields['prices'] as $key => $value) {
				$fields['prices'][$key] = fakturo_mask_to_float($value);
			}
			foreach ($fields['prices_final'] as $key => $value) {
				$fields['prices_final'][$key] = fakturo_mask_to_float($value);
			}
			return $fields;
		}

		public static function default_fields($new_status, $old_status, $post) {
			if ($post->post_type == 'fktr_product' && $old_status == 'new') {
				$fields = array();
				$fields['cost'] = 0;
				$fields['currency'] = 0;

				$fields['model'] = 0;
				$fields['category'] = 0;
				$fields['product_type'] = 0;
				$fields['tax'] = 0;

				$fields['reference'] = '';
				$fields['manufacturers'] = '';
				$fields['description'] = '';
				$fields['min'] = '';
				$fields['min_alert'] = '';
				$fields['packaging'] = 0;
				$fields['unit'] = '';
				$fields['note'] = '';
				$fields['origin'] = 0;

				$fields['prices'] = array();
				$fields['prices_final'] = array();
				$fields['stocks'] = array();

				$fields = apply_filters('fktr_clean_product_fields', $fields);

				foreach ($fields as $field => $value) {
					if (!is_null($value)) {

						$new = apply_filters('fktr_product_metabox_save_' . $field, $value);  //filtra cada campo antes de grabar
						update_post_meta($post->ID, $field, $new);
					}
				}
			}
		}

		public static function get_product_data($product_id) {
			$custom_field_keys = get_post_custom($product_id);
			$custom_field_keys = is_array($custom_field_keys) ? $custom_field_keys : array($custom_field_keys);

			foreach ($custom_field_keys as $key => $value) {
				$custom_field_keys[$key] = maybe_unserialize($value[0]);
			}
			$custom_field_keys = apply_filters('fktr_clean_product_fields', $custom_field_keys);
			return $custom_field_keys;
		}

		public static function save($post_id, $post) {
			if (( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) || ( defined('DOING_AJAX') && DOING_AJAX ) || isset($_REQUEST['bulk_edit'])) {
				return false;
			}
			if (isset($post->post_type) && $post->post_type == 'revision' || $post->post_type != 'fktr_product') {
				return false;
			}
			if (!current_user_can('edit_fakturo_settings', $post_id)) {
				return false;
			}
			if (( defined('FKTR_STOP_PROPAGATION') && FKTR_STOP_PROPAGATION)) {
				return false;
			}
			$fields = apply_filters('fktr_clean_product_fields', $_POST);
			$fields = apply_filters('fktr_product_before_save', $fields);

			if (isset($fields['webcam_image']) && $fields['webcam_image'] != NULL) {
				delete_post_meta($post_id, '_thumbnail_id');
				$filename = 'webcam_image_' . microtime() . '.jpg';
				$file = wp_upload_bits($filename, null, base64_decode(substr($fields['webcam_image'], 23)));
				if ($file['error'] == FALSE) {
					$wp_filetype = wp_check_filetype($filename, null);
					$attachment = array(
						'post_mime_type' => $wp_filetype['type'],
						'post_parent' => $post_id,
						'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
						'post_content' => '',
						'post_status' => 'inherit'
					);
					$attachment_id = wp_insert_attachment($attachment, $file['file'], $post_id);
					if (!is_wp_error($attachment_id)) {
						require_once(ABSPATH . "wp-admin" . '/includes/image.php');
						$attachment_data = wp_generate_attachment_metadata($attachment_id, $file['file']);
						wp_update_attachment_metadata($attachment_id, $attachment_data);
					}
					$new = apply_filters('fktr_product_thumbnail_id', $attachment_id);
					add_post_meta($post_id, '_thumbnail_id', $new);
					unset($fields['webcam_image']);
				}
			}

			foreach ($fields as $field => $value) {

				if (!is_null($value)) {

					$new = apply_filters('fktr_product_metabox_save_' . $field, $value);  //filtra cada campo antes de grabar
					update_post_meta($post_id, $field, $new);
				}
			}

			do_action('fktr_save_product', $post_id, $post);
		}

	}

	endif;

$fktrPostTypeProducts = new fktrPostTypeProducts();
