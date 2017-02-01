<?php


// Exit if accessed directly
if (!defined('ABSPATH'))  {
	exit;
}

if ( ! class_exists('fktr_tax_price_scales') ) :
class fktr_tax_price_scales {
	
	public static $tax_name = 'fktr_price_scales';
	function __construct() {
		add_action( 'init', array(__CLASS__, 'init'), 1, 99 );
		add_action( 'activated_plugin', array(__CLASS__, 'init'), 1 );
		
		add_action(self::$tax_name.'_edit_form_fields', array(__CLASS__, 'edit_form_fields'));
		add_action(self::$tax_name.'_add_form_fields',  array(__CLASS__, 'add_form_fields'));
		
		add_filter('parent_file',  array( __CLASS__, 'tax_menu_correction'));
		add_filter('submenu_file',  array( __CLASS__, 'tax_submenu_correction'));
		
		add_action('edited_'.self::$tax_name, array(__CLASS__, 'save_fields'), 10, 2);
		add_action('created_'.self::$tax_name, array(__CLASS__,'save_fields'), 10, 2);
		
		add_filter('manage_edit-'.self::$tax_name.'_columns', array(__CLASS__, 'columns'), 10, 3);
		add_filter('manage_'.self::$tax_name.'_custom_column',  array(__CLASS__, 'theme_columns'), 10, 3);
		
		add_action('admin_enqueue_scripts', array(__CLASS__, 'scripts'), 10, 1);
		add_filter('before_save_tax_'.self::$tax_name, array(__CLASS__, 'before_save'), 10, 1);
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
			'name'                       => _x( 'Price Scales', 'Price Scales', FAKTURO_TEXT_DOMAIN ),
			'singular_name'              => _x( 'Price Scale', 'Price Scale', FAKTURO_TEXT_DOMAIN ),
			'search_items'               => __( 'Search Price Scales', FAKTURO_TEXT_DOMAIN ),
			'popular_items'              => __( 'Popular Price Scales', FAKTURO_TEXT_DOMAIN ),
			'all_items'                  => __( 'All Price Scales', FAKTURO_TEXT_DOMAIN ),
			'parent_item'                => __( 'Bank', FAKTURO_TEXT_DOMAIN ),
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Price Scale', FAKTURO_TEXT_DOMAIN ),
			'update_item'                => __( 'Update Price Scale', FAKTURO_TEXT_DOMAIN ),
			'add_new_item'               => __( 'Add New Price Scale', FAKTURO_TEXT_DOMAIN ),
			'new_item_name'              => __( 'New Price Scale Name', FAKTURO_TEXT_DOMAIN ),
			'separate_items_with_commas' => __( 'Separate Price Scale with commas', FAKTURO_TEXT_DOMAIN ),
			'add_or_remove_items'        => __( 'Add or remove Price Scales', FAKTURO_TEXT_DOMAIN ),
			'choose_from_most_used'      => __( 'Choose from the most used Price Scales', FAKTURO_TEXT_DOMAIN ),
			'not_found'                  => __( 'No Price Scales found.', FAKTURO_TEXT_DOMAIN ),
			'menu_name'                  => __( 'Price Scales', FAKTURO_TEXT_DOMAIN ),
		);

		$args = array(
			'public'				=> false,
			'hierarchical'          => false,
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'fktr-price-scales' ),
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
		if ($current_screen->id == 'edit-fktr_price_scales') {
			$parent_file = 'fakturo_dashboard';
		}
		return $parent_file;
	}
	
	// highlight the proper sub level menu
	static function tax_submenu_correction($submenu_file) {
		global $current_screen;
		if ($current_screen->id == 'edit-fktr_price_scales') {
			$submenu_file = 'fakturo-settings';
		}
		return $submenu_file;
	}

	public static function scripts() {
		if (isset($_GET['taxonomy']) && $_GET['taxonomy'] == self::$tax_name) {
			wp_enqueue_script( 'jquery-mask', FAKTURO_PLUGIN_URL . 'assets/js/jquery.mask.min.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			wp_enqueue_script( 'taxonomy-price-scales', FAKTURO_PLUGIN_URL . 'assets/js/taxonomy-price-scales.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			$setting_system = get_option('fakturo_system_options_group', false);
			wp_localize_script('taxonomy-price-scales', 'system_setting',
				array('thousand' => $setting_system['thousand'],
					'decimal' => $setting_system['decimal'],
					'decimal_numbers' => $setting_system['decimal_numbers'],
				));
		}
		
		
	}
	public static function add_form_fields() {
		$echoHtml = '
		<style type="text/css">.form-field.term-parent-wrap,.form-field.term-slug-wrap, .form-field label[for="parent"], .form-field #parent {display: none;}  .form-field.term-description-wrap { display:none;} .inline.hide-if-no-js{ display:none;} .view{ display:none;}</style>
		<div class="form-field" id="rate_div">
			<label for="term_meta[percentage]">'.__( 'Percentage', FAKTURO_TEXT_DOMAIN ).'</label>
			<input style="width: 60px;text-align: right; padding-right: 0px; " maxlength="6" type="text" name="term_meta[percentage]" id="term_meta_percentage" value="0"/>%
			<p class="description">'.__( 'Enter a percentage', FAKTURO_TEXT_DOMAIN ).'</p>
		</div>
		';
		echo $echoHtml;
	}
	public static function edit_form_fields($term) {
	

		$term_meta = get_fakturo_term($term->term_id, self::$tax_name);
	
		$echoHtml = '<style type="text/css">.form-field.term-parent-wrap, .form-field.term-slug-wrap {display: none;} .form-field.term-description-wrap { display:none;}  </style>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="term_meta[percentage]">'.__( 'Percentage', FAKTURO_TEXT_DOMAIN ).'</label>
			</th>
			<td>
				<input style="width: 60px;text-align: right; padding-right: 0px; " maxlength="6" type="text" name="term_meta[percentage]" id="term_meta_percentage" value="'.$term_meta->percentage.'"/>%
				<p class="description">'.__( 'Enter a percentage', FAKTURO_TEXT_DOMAIN ).'</p>
			</td>
		</tr>
		';
		echo $echoHtml;
		
	}
	public static function columns($columns) {
		$new_columns = array(
			'cb' => '<input type="checkbox" />',
			'name' => __('Name', FAKTURO_TEXT_DOMAIN),
			'percentage' => __('Percentage', FAKTURO_TEXT_DOMAIN),
		);
		return $new_columns;
	}
	public static function theme_columns($out, $column_name, $term_id) {
		
		$term = get_fakturo_term($term_id, self::$tax_name);
		
		switch ($column_name) {
			case 'percentage': 
				$setting_system = get_option('fakturo_system_options_group', false);
				$percentage = number_format($term->percentage, 2, $setting_system['decimal'], $setting_system['thousand']);
				$out = esc_attr($percentage).'%';
				break;

			default:
				break;
		}
		return $out;    
	}
	public static function before_save($fields)  {
		if (isset($fields['percentage'])) {
			$fields['percentage'] = fakturo_mask_to_float($fields['percentage']);
			
		}
		return $fields;
	}
	public static function save_fields($term_id, $tt_id) {
		if (isset( $_POST['term_meta'])) {
			$_POST['term_meta'] = apply_filters('before_save_tax_'.self::$tax_name, $_POST['term_meta']);
			set_fakturo_term($term_id, $tt_id, $_POST['term_meta']);
		}
	}
	
}
endif;

$fktr_tax_price_scales = new fktr_tax_price_scales();

?>