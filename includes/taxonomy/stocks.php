<?php


// Exit if accessed directly
if (!defined('ABSPATH'))  {
	exit;
}

if ( ! class_exists('fktr_tax_stock') ) :
class fktr_tax_stock {
	
	public static $tax_name = 'fktr_stock';
	function __construct() {
		$use_tax_stock = apply_filters('fktr_use_tax_stock', true);
		if ($use_tax_stock) {
			add_action( 'init', array(__CLASS__, 'init'), 1, 99 );
			add_action( 'activated_plugin', array(__CLASS__, 'init'), 1 );
			add_action(self::$tax_name.'_edit_form_fields', array(__CLASS__, 'edit_form_fields'));
			add_action(self::$tax_name.'_add_form_fields',  array(__CLASS__, 'add_form_fields'));
			
			add_filter('parent_file',  array( __CLASS__, 'tax_menu_correction'));
	
			add_action('edited_'.self::$tax_name, array(__CLASS__, 'save_fields'), 10, 2);
			add_action('created_'.self::$tax_name, array(__CLASS__,'save_fields'), 10, 2);

			add_filter( 'list_table_primary_column', array(__CLASS__, 'primary_column'), 10, 2);
			add_filter( self::$tax_name.'_row_actions', array(__CLASS__, 'row_actions'), 10, 2);
			add_filter('manage_edit-'.self::$tax_name.'_columns', array(__CLASS__, 'columns'), 10, 3);
			add_filter('manage_'.self::$tax_name.'_custom_column',  array(__CLASS__, 'theme_columns'), 10, 3);
			
			add_action('admin_enqueue_scripts', array(__CLASS__, 'scripts'), 10, 1);
			add_filter('before_save_tax_'.self::$tax_name, array(__CLASS__, 'before_save'), 10, 1);
			add_filter('redirect_term_location', array(__CLASS__, 'redirect_term_location'), 0, 2);
			add_filter('fktr_get_dialer_icon_'.self::$tax_name, array(__CLASS__, 'dashboard_icon'), 10, 1);
		}
	}
	public static function dashboard_icon($icon) {
		$icon = 'dashicons-list-view';
		return $icon;
	}
	
	static function redirect_term_location($location, $tax ){
		if($tax->name == self::$tax_name){
			$location = admin_url('edit-tags.php?taxonomy='.self::$tax_name);
		}
		return $location;
	}
	public static function init() {
		$labels = array(
			'name'                       => _x( 'Stocks', 'Stocks', FAKTURO_TEXT_DOMAIN ),
			'singular_name'              => _x( 'Stock', 'Stock', FAKTURO_TEXT_DOMAIN ),
			'search_items'               => __( 'Search Stocks', FAKTURO_TEXT_DOMAIN ),
			'popular_items'              => null, // para que no aparezca la nube de populares  __( 'Popular Stocks', FAKTURO_TEXT_DOMAIN ),
			'all_items'                  => __( 'All Stocks', FAKTURO_TEXT_DOMAIN ),
			'parent_item'                => __( 'Bank', FAKTURO_TEXT_DOMAIN ),
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Stock', FAKTURO_TEXT_DOMAIN ),
			'update_item'                => __( 'Update Stock', FAKTURO_TEXT_DOMAIN ),
			'add_new_item'               => __( 'Add New Stock', FAKTURO_TEXT_DOMAIN ),
			'new_item_name'              => __( 'New Stock Name', FAKTURO_TEXT_DOMAIN ),
			'separate_items_with_commas' => __( 'Separate Stock with commas', FAKTURO_TEXT_DOMAIN ),
			'add_or_remove_items'        => __( 'Add or remove Stocks', FAKTURO_TEXT_DOMAIN ),
			'choose_from_most_used'      => __( 'Choose from the most used Stocks', FAKTURO_TEXT_DOMAIN ),
			'not_found'                  => __( 'No Stocks found.', FAKTURO_TEXT_DOMAIN ),
			'menu_name'                  => __( 'Stocks', FAKTURO_TEXT_DOMAIN ),
		);

		$args = array(
			'public'				=> false,
			'hierarchical'          => false,
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'fktr-stocks' ),
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
		if ($current_screen->id == "edit-fktr_stock") {
			$parent_file = 'edit.php?post_type=fktr_product';
		}
		return $parent_file;
	}
	
	public static function date_format_php_to_js( $sFormat ) {

		switch( $sFormat ) {

			//Predefined WP date formats

			case 'F j, Y':

			case 'Y/m/d':

			case 'm/d/Y':

			case 'd/m/Y':

				return $sFormat;

				break;

			default :

				return( 'm/d/Y' );

				break;

		 }

	}
	public static function scripts() {
		global $wp_locale, $locale;
		if (isset($_GET['taxonomy']) && $_GET['taxonomy'] == self::$tax_name) {
			wp_enqueue_script( 'jquery-select2', FAKTURO_PLUGIN_URL . 'assets/js/jquery.select2.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			wp_enqueue_script( 'jquery-mask', FAKTURO_PLUGIN_URL . 'assets/js/jquery.mask.min.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			wp_enqueue_script( 'jquery-datetimepicker', FAKTURO_PLUGIN_URL . 'assets/js/jquery.datetimepicker.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			wp_enqueue_script( 'taxonomy-stocks', FAKTURO_PLUGIN_URL . 'assets/js/taxonomy-stocks.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			$setting_system = get_option('fakturo_system_options_group', false);

			$objectL10n = (object)array(
				'lang'			=> substr($locale, 0, 2),
				'UTC'			=> get_option( 'gmt_offset' ),
				'timeFormat'    => get_option( 'time_format' ),
				'dateFormat'    => self::date_format_php_to_js( $setting_system['dateformat'] ),
				'printFormat'   => self::date_format_php_to_js( $setting_system['dateformat'] ),
				'firstDay'      => get_option( 'start_of_week' ),
			);		
			
			wp_localize_script('taxonomy-stocks', 'system_setting',
				array('thousand' => $setting_system['thousand'],
					'decimal' => $setting_system['decimal'],
					'decimal_numbers' => $setting_system['decimal_numbers'],
					
					'datetimepicker' => json_encode($objectL10n),
					
					'txt_search_products' => __('Search products...', FAKTURO_TEXT_DOMAIN ),
					'characters_to_search' => apply_filters('fktr_sales_characters_to_search_product', 3),
					'ajax_url' => admin_url( 'admin-ajax.php' ),
				));
			
				
			wp_enqueue_style('style-select2',FAKTURO_PLUGIN_URL .'assets/css/select2.min.css');	
			wp_enqueue_style('style-datetimepicker',FAKTURO_PLUGIN_URL .'assets/css/jquery.datetimepicker.css');				
		}
		
		
	}
	public static function add_form_fields() {
		$setting_system = get_option('fakturo_system_options_group', false);
	
		$selectLocation = wp_dropdown_categories( array(
			'show_option_all'    => '',
			'show_option_none'   => __('Choose a Location', FAKTURO_TEXT_DOMAIN ),
			'orderby'            => 'name', 
			'order'              => 'ASC',
			'show_count'         => 0,
			'hide_empty'         => 0, 
			'child_of'           => 0,
			'exclude'            => '',
			'echo'               => 0,
			'selected'           => 0,
			'hierarchical'       => 1, 
			'name'               => 'term_meta[location]',
			'class'              => 'form-no-clear',
			'id'				 => 'term_meta_location',
			'depth'              => 1,
			'tab_index'          => 0,
			'taxonomy'           => 'fktr_locations',
			'hide_if_empty'      => false
		));
		$date = time();
		$echoHtml = '
		<style type="text/css">
		.form-field.term-parent-wrap,.form-field.term-slug-wrap, .form-field label[for="parent"], .form-field #parent {display: none;}  
		.form-field.term-description-wrap { display:none;} 
			.term-name-wrap {
				float: left;
				    width: 67%;
			}
			#type_div {
				float: right;
				width: 30%;
				border: 1px solid #c4c4c4;
				border-radius: 3px;
				background-color: white;
				padding: 3px;
				line-height: 28px;
			}
			.select2-result-product {
				padding-top: 4px;
				padding-bottom: 3px;
			}
			.select2-result-product__avatar  {
				float: left;
				width: 40px;
				height:40px;
				margin-right: 10px;
			}
			.select2-result-product__avatar img {
				width: 100%;
				height: 100%;
				border-radius: 2px;
			}
			.select2-result-product__meta {
				margin-left: 70px;
				
			}
			.select2-result-product__title {
				color: black;
				font-weight: bold;
				word-wrap: break-word;
				line-height: 1.1;
				margin-bottom: 4px;
			}
			.product__description {
				font-size: 13px;
				color: #777;
				margin-top: 4px;
			}
			.select2-result-product__forks, .select2-result-product__stargazers, .select2-result-product__watchers {
				display: inline-block;
				color: black;
				font-size: 11px;
			}    
		</style>
		<div class="form-field" id="type_div">
			
			<input type="radio" name="term_meta[type]" checked="true" id="term_meta_type" value="1"/><span>'.__('Entry', FAKTURO_TEXT_DOMAIN ).'</span>
			<br/><input type="radio" name="term_meta[type]" id="term_meta_type" value="2"/><span>'.__('Output', FAKTURO_TEXT_DOMAIN ).'</span>
			
		</div>
		<div style="clear: both;"></div>
		
		<div class="form-field" id="date_div">
			<label for="term_meta[date]">'.__('Date', FAKTURO_TEXT_DOMAIN ).'</label>
			<input style="width: 100px;text-align: center; padding-right: 0px; "  type="text" name="term_meta[date]" id="term_meta_date" value="'.date_i18n($setting_system['dateformat'], $date ).'"/>
			<p class="description">'.__( 'Enter a date.', FAKTURO_TEXT_DOMAIN ).'</p>
		</div>
		<div class="form-field" id="location_div">
			<label for="term_meta[location]">'.__( 'Location', FAKTURO_TEXT_DOMAIN ).'</label>
			'.$selectLocation.'
			<p class="description">'.__( 'Select a stock location.', FAKTURO_TEXT_DOMAIN ).'</p>
		</div>
		<div class="form-field" id="product_div">
			<label for="term_meta[product]">'.__( 'Product', FAKTURO_TEXT_DOMAIN ).'</label>
			<select name="term_meta[product]" id="product_select" class="js-example-basic-multiple select2-hidden-accessible" multiple="" style="width:65%;" tabindex="-1" aria-hidden="true">
			</select>
			<p class="description">'.__( 'Select a product for add stock.', FAKTURO_TEXT_DOMAIN ).'</p>
		</div>
		
		<div class="form-field" id="cost_div">
			<label for="term_meta[cost]">'.__('Cost', FAKTURO_TEXT_DOMAIN ).'</label>
			<input style="width: 120px;" type="text" name="term_meta[cost]" id="term_meta_cost" value=""/>
			<p class="description">'.__( 'Enter the cost.', FAKTURO_TEXT_DOMAIN ).'</p>
		</div>
		<div class="form-field" id="quantity_div">
			<label for="term_meta[quantity]">'.__('Quantity', FAKTURO_TEXT_DOMAIN ).'</label>
			<input style="width: 60px;text-align: right; padding-right: 0px; " maxlength="6" type="text" name="term_meta[quantity]" id="term_meta_quantity" value="1"/>
			<p class="description">'.__( 'Enter a quantity.', FAKTURO_TEXT_DOMAIN ).'</p>
		</div>
		
		
		
		';
		echo $echoHtml;
	}
	public static function edit_form_fields($term) {

		$term_meta = get_fakturo_term($term->term_id, self::$tax_name);
		
	}

	static function row_actions( $actions, $tag ){
		unset($actions['edit']);
		unset($actions['view']);
		unset($actions['inline hide-if-no-js']);
		return $actions;
	}

	public static function columns($columns) {
		$new_columns = array(
			'cb' => '<input type="checkbox" />',
			'order' => __('Order number', FAKTURO_TEXT_DOMAIN),
			'product' => __('Product', FAKTURO_TEXT_DOMAIN),
			'location' => __('Location', FAKTURO_TEXT_DOMAIN),
			'quantity' => __('Quantity', FAKTURO_TEXT_DOMAIN),
			'type' => __('Type', FAKTURO_TEXT_DOMAIN),
		);
		
		return $new_columns;
		
	}		

	public static function primary_column( $default, $screen_id ) {
		
		return 'order';
		
	}
	public static function theme_columns($out, $column_name, $term_id) {		
		global $primary;
		$primary = 'order';
		$term = get_fakturo_term($term_id, self::$tax_name);
		
		switch ($column_name) {
			case 'order': 				
				$out = esc_attr($term->name).'';
				break;
			case 'product': 
				$product_name = __( 'No product', FAKTURO_TEXT_DOMAIN );
				if ($term->product > 0) {
					$product_data = fktrPostTypeProducts::get_product_data($term->product);
					
					if(isset($product_data['post_title'])) {
						$product_name = $product_data['post_title'];
					}
				}
				$out = esc_attr($product_name).'';
				break;
			case 'location': 
				$location = __('No location', FAKTURO_TEXT_DOMAIN );
				if ($term->location > 0) {
					$tax_locations =  get_fakturo_term($term->location, 'fktr_locations');
					$location =  $tax_locations->name;
				}
				$out = esc_attr($location).'';
				break;
			case 'quantity': 
				$quantity = 0;
				if (isset($term->quantity)) {
					$quantity =  $term->quantity;
				}
				$out = esc_attr($quantity).'';
				break;
			case 'type': 
				$type = 'Entry';
				if ($term->type == 2) {
					$type = 'Output';
				}
				$out = esc_attr($type).'';
				break;
			default:
				break;
		}
		return $out;    
	}
	public static function before_save($fields)  {
		if (isset($fields['cost'])) {
			$fields['cost'] = fakturo_mask_to_float($fields['cost']);
		}
		if (isset($fields['date'])) {
			$fields['date'] = str_replace('/', '-', $fields['date']);
			$fields['date'] = date('Y-m-d', strtotime($fields['date']));
		}
		fktrPostTypeProducts::addStock($fields['product'], $fields['quantity'], $fields['location']);
		
		return $fields;
	}
	public static function save_fields($term_id, $tt_id) {
		global $wpdb;

		if (isset( $_POST['term_meta'])) {

			$_POST['term_meta'] = apply_filters('before_save_tax_'.self::$tax_name, $_POST['term_meta']);
			set_fakturo_term($term_id, $tt_id, $_POST['term_meta']);
			
		}
	}
	
}
endif;

$fktr_tax_stock = new fktr_tax_stock();

?>