<?php


// Exit if accessed directly
if (!defined('ABSPATH'))  {
	exit;
}

if ( ! class_exists('fktr_tax_tax_conditions') ) :
class fktr_tax_tax_conditions {
	
	public static $tax_name = 'fktr_tax_conditions';
	function __construct() {
		add_action( 'init', array(__CLASS__, 'init'), 1, 99 );
		add_action( 'fakturo_activation', array(__CLASS__, 'init'), 1 );
		add_action(self::$tax_name.'_edit_form_fields', array(__CLASS__, 'edit_form_fields'));
		add_action(self::$tax_name.'_add_form_fields',  array(__CLASS__, 'add_form_fields'));
		
		add_filter('parent_file',  array( __CLASS__, 'tax_menu_correction'));
		add_filter('submenu_file',  array( __CLASS__, 'tax_submenu_correction'));
		
		add_action('edited_'.self::$tax_name, array(__CLASS__, 'save_fields'), 10, 2);
		add_action('created_'.self::$tax_name, array(__CLASS__,'save_fields'), 10, 2);
		
		add_filter('manage_edit-'.self::$tax_name.'_columns', array(__CLASS__, 'columns'), 10, 3);
		add_filter('manage_'.self::$tax_name.'_custom_column',  array(__CLASS__, 'theme_columns'), 10, 3);
		
		add_action('admin_enqueue_scripts', array(__CLASS__, 'scripts'), 10, 1);
		add_action('fktr_popup_tax_'.self::$tax_name.'_print_scripts', array(__CLASS__, 'scripts'), 10, 1);
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
			'name'                       => __( 'Tax Conditions', 'fakturo' ),
			'singular_name'              => __( 'Tax Condition', 'fakturo' ),
			'search_items'               => __( 'Search Tax Conditions', 'fakturo' ),
			'popular_items'              => __( 'Popular Tax Conditions', 'fakturo' ),
			'all_items'                  => __( 'All Tax Conditions', 'fakturo' ),
			'parent_item'                => __( 'Bank', 'fakturo' ),
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Tax Condition', 'fakturo' ),
			'update_item'                => __( 'Update Tax Condition', 'fakturo' ),
			'add_new_item'               => __( 'Add New Tax Condition', 'fakturo' ),
			'new_item_name'              => __( 'New Tax Condition Name', 'fakturo' ),
			'separate_items_with_commas' => __( 'Separate Tax Condition with commas', 'fakturo' ),
			'add_or_remove_items'        => __( 'Add or remove Tax Conditions', 'fakturo' ),
			'choose_from_most_used'      => __( 'Choose from the most used Tax Conditions', 'fakturo' ),
			'not_found'                  => __( 'No Tax Conditions found.', 'fakturo' ),
			'menu_name'                  => __( 'Tax Conditions', 'fakturo' ),
		);

		$args = array(
			'public'				=> false,
			'hierarchical'          => false,
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'fktr-tax-conditions' ),
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
		if ($current_screen->id == "edit-fktr_tax_conditions") {
			$parent_file = 'fakturo_dashboard';
		}
		return $parent_file;
	}
	
	// highlight the proper sub level menu
	static function tax_submenu_correction($submenu_file) {
		global $current_screen;
		if ($current_screen->id == "edit-fktr_tax_conditions") {
			$submenu_file = 'fakturo-settings';
		}
		return $submenu_file;
	}

	public static function scripts() {
		if (isset($_GET['taxonomy']) && $_GET['taxonomy'] == self::$tax_name) {
			wp_enqueue_script( 'jquery-select2', FAKTURO_PLUGIN_URL . 'assets/js/jquery.select2.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			wp_enqueue_script( 'jquery-mask', FAKTURO_PLUGIN_URL . 'assets/js/jquery.mask.min.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			wp_enqueue_script( 'taxonomy-taxes', FAKTURO_PLUGIN_URL . 'assets/js/taxonomy-tax-conditions.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			$setting_system = get_option('fakturo_system_options_group', false);
			wp_localize_script('taxonomy-taxes', 'system_setting',
				array('thousand' => $setting_system['thousand'],
					'decimal' => $setting_system['decimal'],
					'decimal_numbers' => $setting_system['decimal_numbers'],
				));
		}
		
		
	}
	public static function add_form_fields() {
		$selectInvoiceTypes = wp_dropdown_categories( array(
			'show_option_all'    => '',
			'show_option_none'   => __('Choose a Invoice Type', 'fakturo' ),
			'orderby'            => 'name', 
			'order'              => 'ASC',
			'show_count'         => 0,
			'hide_empty'         => 0, 
			'child_of'           => 0,
			'exclude'            => '',
			'echo'               => 0,
			'selected'           => -1,
			'hierarchical'       => 1, 
			'name'               => 'term_meta[invoice_type]',
			'class'              => 'form-no-clear',
			'id'				 => 'term_meta_invoice_type',
			'depth'              => 1,
			'tab_index'          => 0,
			'taxonomy'           => 'fktr_invoice_types',
			'hide_if_empty'      => false
		));
		
		$echoHtml = '
		<style type="text/css">.form-field.term-parent-wrap,.form-field.term-slug-wrap, .form-field label[for="parent"], .form-field #parent {display: none;}  .form-field.term-description-wrap { display:none;} .inline.hide-if-no-js{ display:none;} .view{ display:none;}</style>
		
		<div class="form-field" id="rate_div">
			<label for="term_meta[invoice_type]">'.__( 'Invoice Types', 'fakturo' ).'</label>
			'.$selectInvoiceTypes.'
			<p class="description">'.__( 'Select default Invoice Type for this Tax Condition.', 'fakturo' ).'</p>
		</div>
		
		<div class="form-field" id="overwrite_taxes_div">
			<input type="checkbox" class="slidercheck" value="1" name="term_meta_overwrite_taxes" id="term_meta_overwrite_taxes">
			<label for="term_meta_overwrite_taxes"><span class="ui"></span>'.__('Overwrite Taxes', 'fakturo' ).'	</label>
			
		</div>
		<div class="form-field" id="tax_percentage_div" style="display:none;">
			<label for="term_meta[tax_percentage]">'.__('Tax Percentage', 'fakturo' ).'</label>
			<input style="width: 60px;text-align: right; padding-right: 0px; " maxlength="6" type="text" name="term_meta[tax_percentage]" id="term_meta_tax_percentage" value="0"/>%
			<p class="description">'.__( 'Enter a tax percentage', 'fakturo' ).'</p>
		</div>
		';
		echo $echoHtml;
	}
	public static function edit_form_fields($term) {
	

		$term_meta = get_fakturo_term($term->term_id, self::$tax_name);
		$selectInvoiceTypes = wp_dropdown_categories( array(
			'show_option_all'    => '',
			'show_option_none'   => __('Choose a Invoice Type', 'fakturo' ),
			'orderby'            => 'name', 
			'order'              => 'ASC',
			'show_count'         => 0,
			'hide_empty'         => 0, 
			'child_of'           => 0,
			'exclude'            => '',
			'echo'               => 0,
			'selected'           => $term_meta->invoice_type,
			'hierarchical'       => 1, 
			'name'               => 'term_meta[invoice_type]',
			'class'              => 'form-no-clear',
			'id'				 => 'term_meta_invoice_type',
			'depth'              => 1,
			'tab_index'          => 0,
			'taxonomy'           => 'fktr_invoice_types',
			'hide_if_empty'      => false
		));
		$echoHtml = '<style type="text/css">.form-field.term-parent-wrap, .form-field.term-slug-wrap {display: none;} .form-field.term-description-wrap { display:none;}  </style>
					<tr class="form-field">
			<th scope="row" valign="top">
				<label for="term_meta[invoice_type]">'.__( 'Invoice Types', 'fakturo' ).'</label>
			</th>
			<td>
				'.$selectInvoiceTypes.'
				<p class="description">'.__( 'Select default Invoice Type for this Tax Condition.', 'fakturo' ).'</p>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top">
				
			</th>
			<td>
				<input type="checkbox" class="slidercheck" value="1" name="term_meta_overwrite_taxes" id="term_meta_overwrite_taxes" '.(($term_meta->overwrite_taxes)?'checked="checked"':'').'>
				<label for="term_meta_overwrite_taxes"><span class="ui"></span>'.__('Overwrite Taxes', 'fakturo' ).'	</label>
			</td>
		</tr>
		<tr class="form-field" id="tax_percentage_div" '.(($term_meta->overwrite_taxes)?'':'style="display:none;"').'>
			<th scope="row" valign="top">
				<label for="term_meta[tax_percentage]">'.__( 'Tax Percentage', 'fakturo' ).'</label>
			</th>
			<td>
				<input style="width: 60px;text-align: right; padding-right: 0px; " maxlength="6" type="text" name="term_meta[tax_percentage]" id="term_meta_tax_percentage" value="'.$term_meta->tax_percentage.'"/>%
				<p class="description">'.__( 'Enter a tax percentage', 'fakturo' ).'</p>
			</td>
		</tr>
		';
		echo $echoHtml;
		
	}
	public static function columns($columns) {
		$new_columns = array(
			'cb' => '<input type="checkbox" />',
			'name' => __('Name', 'fakturo'),
			'invoice_type' => __('Invoice Type', 'fakturo'),
			'overwrite_taxes' => __('Overwrite Taxes', 'fakturo'),
		);
		return $new_columns;
	}
	public static function theme_columns($out, $column_name, $term_id) {
		
		$term = get_fakturo_term($term_id, self::$tax_name);
		
		switch ($column_name) {
			case 'invoice_type': 
				$invoice_name = __( 'No invoice type', 'fakturo' );
				if ($term->invoice_type > 0) {
					$invoice_type_data = get_fakturo_term($term->invoice_type, 'fktr_invoice_types');
					if(!is_wp_error($invoice_type_data)) {
						$invoice_name = $invoice_type_data->name;
					}
				}
				$out = esc_attr($invoice_name).'';
				break;
			case 'overwrite_taxes': 
				$overwrite_taxes = __('No', 'fakturo' );
				if ($term->overwrite_taxes > 0) {
					$tax_percentage = fakturo_porcent_to_mask($term->tax_percentage);
					$overwrite_taxes =  __('Yes:', 'fakturo').' '.$tax_percentage.'%';
				}
				$out = esc_attr($overwrite_taxes).'';
				break;
			
			default:
				break;
		}
		return $out;    
	}
	public static function before_save($fields)  {
		if (isset($fields['tax_percentage'])) {
			$fields['tax_percentage'] = fakturo_mask_to_float($fields['tax_percentage']);
		}
		return $fields;
	}
	public static function save_fields($term_id, $tt_id) {
		if (!isset($_POST['term_meta_overwrite_taxes'])) {
			$_POST['term_meta_overwrite_taxes'] = 0;
		}
		if (isset( $_POST['term_meta'])) {
			$_POST['term_meta']['overwrite_taxes'] = $_POST['term_meta_overwrite_taxes'];
			$_POST['term_meta'] = apply_filters('before_save_tax_'.self::$tax_name, $_POST['term_meta']);
			set_fakturo_term($term_id, $tt_id, $_POST['term_meta']);
		}
	}
	
}
endif;

$fktr_tax_tax_conditions = new fktr_tax_tax_conditions();

?>