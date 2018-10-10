<?php


// Exit if accessed directly
if (!defined('ABSPATH'))  {
	exit;
}

if ( ! class_exists('fktr_tax_check') ) :
class fktr_tax_check {
	
	public static $tax_name = 'fktr_check';
	function __construct() {
		
		add_action( 'init', array(__CLASS__, 'init'), 1, 99 );
		add_action( 'fakturo_activation', array(__CLASS__, 'init'), 1 );
		
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
	
	public static function dashboard_icon($icon) {
		$icon = 'dashicons-exerpt-view';
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
			'name'                       => __( 'Checks', 'fakturo' ),
			'singular_name'              => __( 'Check', 'fakturo' ),
			'search_items'               => __( 'Search Checks', 'fakturo' ),
			'popular_items'              => null, // para que no aparezca la nube de populares  __( 'Popular Checks', 'fakturo' ),
			'all_items'                  => __( 'All Checks', 'fakturo' ),
			'parent_item'                => __( 'Bank', 'fakturo' ),
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Check', 'fakturo' ),
			'update_item'                => __( 'Update Check', 'fakturo' ),
			'add_new_item'               => __( 'Add New Check', 'fakturo' ),
			'new_item_name'              => __( 'New Check Name', 'fakturo' ),
			'separate_items_with_commas' => __( 'Separate Check with commas', 'fakturo' ),
			'add_or_remove_items'        => __( 'Add or remove Checks', 'fakturo' ),
			'choose_from_most_used'      => __( 'Choose from the most used Checks', 'fakturo' ),
			'not_found'                  => __( 'No Checks found.', 'fakturo' ),
			'menu_name'                  => __( 'Checks', 'fakturo' ),
		);

		$args = array(
			'public'				=> false,
			'hierarchical'          => false,
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'fktr-checks' ),
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
		if ($current_screen->id == "edit-fktr_check") {
			$parent_file = 'edit.php?post_type=fktr_sale';
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
			wp_enqueue_script( 'taxonomy-checks', FAKTURO_PLUGIN_URL . 'assets/js/taxonomy-checks.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			$setting_system = get_option('fakturo_system_options_group', false);

			$objectL10n = (object)array(
				'lang'			=> substr($locale, 0, 2),
				'UTC'			=> get_option( 'gmt_offset' ),
				'timeFormat'    => get_option( 'time_format' ),
				'dateFormat'    => self::date_format_php_to_js( $setting_system['dateformat'] ),
				'printFormat'   => self::date_format_php_to_js( $setting_system['dateformat'] ),
				'firstDay'      => get_option( 'start_of_week' ),
			);		
			
			wp_localize_script('taxonomy-checks', 'system_setting',
				array('thousand' => $setting_system['thousand'],
					'decimal' => $setting_system['decimal'],
					'decimal_numbers' => $setting_system['decimal_numbers'],
					
					'datetimepicker' => $objectL10n,
					
					'txt_search_products' => __('Search products...', 'fakturo' ),
					'characters_to_search' => apply_filters('fktr_sales_characters_to_search_product', 3),
					'ajax_url' => admin_url( 'admin-ajax.php' ),
				));
			
				
			wp_enqueue_style('style-select2',FAKTURO_PLUGIN_URL .'assets/css/select2.min.css');	
			wp_enqueue_style('style-datetimepicker',FAKTURO_PLUGIN_URL .'assets/css/jquery.datetimepicker.css');				
		}
		
		
	}
	public static function add_form_fields() {
		$setting_system = get_option('fakturo_system_options_group', false);
		$selectClients = fakturo_get_select_post(array(
											'echo' => 0,
											'post_type' => 'fktr_client',
											'show_option_none' => __('Choose a Client', 'fakturo' ),
											'name' => 'term_meta[client_id]',
											'id' => 'term_meta_client_id',
											'class' => '',
											'selected' => 0
										));
		
		$selectProviders = fakturo_get_select_post(array(
											'echo' => 0,
											'post_type' => 'fktr_provider',
											'show_option_none' => __('Choose a Provider', 'fakturo' ),
											'name' => 'term_meta[provider_id]',
											'id' => 'term_meta_provider_id',
											'class' => '',
											'selected' => 0
										));
		$selectBankEntities = wp_dropdown_categories( array(
				'show_option_all'    => '',
				'show_option_none'   => __('Choose a Bank Entity', 'fakturo' ),
				'orderby'            => 'name', 
				'order'              => 'ASC',
				'show_count'         => 0,
				'hide_empty'         => 0, 
				'child_of'           => 0,
				'exclude'            => '',
				'echo'               => 0,
				'selected'           => 0,
				'hierarchical'       => 1, 
				'name'               => 'term_meta[bank_id]',
				'class'              => '',
				'id'				 => 'term_meta_bank_id',
				'depth'              => 1,
				'tab_index'          => 0,
				'taxonomy'           => 'fktr_bank_entities',
				'hide_if_empty'      => false
			));
		$selectCurrencies = wp_dropdown_categories( array(
				'show_option_all'    => '',
				'show_option_none'   => __('Choose a Currency', 'fakturo' ),
				'orderby'            => 'name', 
				'order'              => 'ASC',
				'show_count'         => 0,
				'hide_empty'         => 0, 
				'child_of'           => 0,
				'exclude'            => '',
				'echo'               => 0,
				'selected'           => 0,
				'hierarchical'       => 1, 
				'name'               => 'term_meta[currency_id]',
				'class'              => '',
				'id'				 => 'term_meta_currency_id',
				'depth'              => 1,
				'tab_index'          => 0,
				'taxonomy'           => 'fktr_currencies',
				'hide_if_empty'      => false
			));
		
		$selectStatus = array();
		$selectStatus['status'] = array();
		$selectStatus['status'][0] = __( 'Status', 'fakturo' );
		$selectStatus['status']['C'] = __('To be cashed', 'fakturo' );
		
		$selectStatus['applied'] = array();
		$selectStatus['applied'][0] = __( 'Applied', 'fakturo' );
		$selectStatus['applied']['D'] = __( 'Deposited', 'fakturo' );
		$selectStatus['applied']['P'] = __( 'Delivered by payments', 'fakturo' );
		$selectStatus['applied']['E'] = __( 'Exchange or delivery', 'fakturo' );
		
		$selectStatus['canceled'] = array();
		$selectStatus['canceled'][0] =  __( 'Canceled', 'fakturo' );		
		$selectStatus['canceled']['R'] = __( 'Rejected', 'fakturo' );		
		$selectStatus['canceled']['X'] = __( 'Annulled', 'fakturo' );			
		$selectStatus = apply_filters('fktr_array_check_status', $selectStatus);

  
		$echoSelectStatus = '<select id="term_meta_status" name="term_meta[status]">';
		foreach ($selectStatus as $gkey => $arr_options) {
			$echoSelectStatus .= '<optgroup label="'.$arr_options[0].'">';
			foreach ($arr_options as $key => $txt) {
				if ($key === 0) {
					continue;
				}
				$echoSelectStatus .= '<option value="'.$key.'"'.selected($key, 0, false).'>'.$txt.'</option>';
			}
			$echoSelectStatus .= '</optgroup>';
		}
		$echoSelectStatus .= '</select>';

		
		$date = time();
		$echoHtml = '
		<style type="text/css">
		.form-field.term-parent-wrap,.form-field.term-slug-wrap, .form-field label[for="parent"], .form-field #parent {display: none;}  
		.form-field.term-description-wrap { display:none;} 
			
			   
		</style>
		
		<div style="clear: both;"></div>
		<div class="form-field" id="client_div">
			<label for="term_meta[client_id]">'.__( 'Client', 'fakturo' ).'</label>
			'.$selectClients.'
			<p class="description">'.__( 'Select a client.', 'fakturo' ).'</p>
		</div>
		
		<div class="form-field" id="bank_id_div">
			<label for="term_meta[bank_id]">'.__( 'Bank', 'fakturo' ).'</label>
			'.$selectBankEntities.'
			<p class="description">'.__( 'Select a bank.', 'fakturo' ).'</p>
		</div>

		<div class="form-field" id="currency_div">
			<label for="term_meta[currency_id]">'.__( 'Currency', 'fakturo' ).'</label>
			'.$selectCurrencies.'
			<p class="description">'.__( 'Select a currency.', 'fakturo' ).'</p>
		</div>
		<div class="form-field" id="value_div">
			<label for="term_meta[value]">'.__( 'Value', 'fakturo' ).'</label>
			<input type="text" name="term_meta[value]" id="term_meta_value" value="" style="width: 150px;text-align: right; padding-right: 0px; ">
			<p class="description">'.__( 'Enter a value', 'fakturo' ).'</p>
		</div>
		
		<div class="form-field" id="date_div">
			<label for="term_meta[date]">'.__( 'Date', 'fakturo' ).'</label>
			<input type="text" name="term_meta[date]" id="term_meta_date" value="'.date_i18n($setting_system['dateformat'], $date ).'" style="width: 150px; ">
			<p class="description">'.__( 'Enter a date', 'fakturo' ).'</p>
		</div>
		<div class="form-field" id="cashing_date_div">
			<label for="term_meta[cashing_date]">'.__( 'Cashing Date', 'fakturo' ).'</label>
			<input type="text" name="term_meta[cashing_date]" id="term_meta_cashing_date" value="'.date_i18n($setting_system['dateformat'], $date ).'" style="width: 150px; ">
			<p class="description">'.__( 'Enter a cashing date.', 'fakturo' ).'</p>
		</div>
		
		<div class="form-field" id="status_div">
			<label for="term_meta[status]">'.__( 'Status', 'fakturo' ).'</label>
			'.$echoSelectStatus.'
			<p class="description">'.__( 'Select a check status.', 'fakturo' ).'</p>
		</div>
		<div class="form-field" id="date_status_div" style="display:none;">
			<label for="term_meta[date]">'.__( 'Date', 'fakturo' ).'</label>
			<input type="text" name="term_meta[date_status]" id="term_meta_date_status" value="'.date_i18n($setting_system['dateformat'], $date ).'" style="width: 150px; ">
			<p class="description">'.__( 'Enter a date', 'fakturo' ).'</p>
		</div>
		<div class="form-field" id="provider_div" style="display:none;">
			<label for="term_meta[provider_id]">'.__( 'Provider', 'fakturo' ).'</label>
			'.$selectProviders.'
			<p class="description">'.__( 'Select a provider.', 'fakturo' ).'</p>
		</div>
		<div class="form-field" id="notes_div">
			<label for="term_meta[notes]">'.__( 'Notes', 'fakturo' ).'</label>
			<textarea style="width:95%;" rows="4" name="term_meta[notes]" id="term_meta_notes"></textarea>
		
			<p class="description">'.__( 'Enter the notes', 'fakturo' ).'</p>
		</div>
		';
		echo $echoHtml;
	}
	public static function edit_form_fields($term) {

		$term_meta = get_fakturo_term($term->term_id, self::$tax_name);
		$setting_system = get_option('fakturo_system_options_group', false);
		
		$selectClients = fakturo_get_select_post(array(
											'echo' => 0,
											'post_type' => 'fktr_client',
											'show_option_none' => __('Choose a Client', 'fakturo' ),
											'name' => 'term_meta[client_id]',
											'id' => 'term_meta_client_id',
											'class' => '',
											'selected' => $term_meta->client_id
										));
		$selectProviders = fakturo_get_select_post(array(
											'echo' => 0,
											'post_type' => 'fktr_provider',
											'show_option_none' => __('Choose a Provider', 'fakturo' ),
											'name' => 'term_meta[provider_id]',
											'id' => 'term_meta_provider_id',
											'class' => '',
											'selected' => $term_meta->provider_id
										));
		$selectBankEntities = wp_dropdown_categories( array(
				'show_option_all'    => '',
				'show_option_none'   => __('Choose a Bank Entity', 'fakturo' ),
				'orderby'            => 'name', 
				'order'              => 'ASC',
				'show_count'         => 0,
				'hide_empty'         => 0, 
				'child_of'           => 0,
				'exclude'            => '',
				'echo'               => 0,
				'selected'           => $term_meta->bank_id,
				'hierarchical'       => 1, 
				'name'               => 'term_meta[bank_id]',
				'class'              => '',
				'id'				 => 'term_meta_bank_id',
				'depth'              => 1,
				'tab_index'          => 0,
				'taxonomy'           => 'fktr_bank_entities',
				'hide_if_empty'      => false
			));
		$selectCurrencies = wp_dropdown_categories( array(
				'show_option_all'    => '',
				'show_option_none'   => __('Choose a Currency', 'fakturo' ),
				'orderby'            => 'name', 
				'order'              => 'ASC',
				'show_count'         => 0,
				'hide_empty'         => 0, 
				'child_of'           => 0,
				'exclude'            => '',
				'echo'               => 0,
				'selected'           => $term_meta->currency_id,
				'hierarchical'       => 1, 
				'name'               => 'term_meta[currency_id]',
				'class'              => '',
				'id'				 => 'term_meta_currency_id',
				'depth'              => 1,
				'tab_index'          => 0,
				'taxonomy'           => 'fktr_currencies',
				'hide_if_empty'      => false
			));
			
		$selectStatus = array();
		$selectStatus['status'] = array();
		$selectStatus['status'][0] = __( 'Status', 'fakturo' );
		$selectStatus['status']['C'] = __('To be cashed', 'fakturo' );
		
		$selectStatus['applied'] = array();
		$selectStatus['applied'][0] = __( 'Applied', 'fakturo' );
		$selectStatus['applied']['D'] = __( 'Deposited', 'fakturo' );
		$selectStatus['applied']['P'] = __( 'Delivered by payments', 'fakturo' );
		$selectStatus['applied']['E'] = __( 'Exchange or delivery', 'fakturo' );
		
		$selectStatus['canceled'] = array();
		$selectStatus['canceled'][0] =  __( 'Canceled', 'fakturo' );		
		$selectStatus['canceled']['R'] = __( 'Rejected', 'fakturo' );		
		$selectStatus['canceled']['X'] = __( 'Annulled', 'fakturo' );			
		$selectStatus = apply_filters('fktr_array_check_status', $selectStatus);

  
		$echoSelectStatus = '<select id="term_meta_status" name="term_meta[status]">';
		foreach ($selectStatus as $gkey => $arr_options) {
			$echoSelectStatus .= '<optgroup label="'.$arr_options[0].'">';
			foreach ($arr_options as $key => $txt) {
				if ($key === 0) {
					continue;
				}
				$echoSelectStatus .= '<option value="'.$key.'"'.selected($key, $term_meta->status, false).'>'.$txt.'</option>';
			}
			$echoSelectStatus .= '</optgroup>';
		}
		$echoSelectStatus .= '</select>';
	
		
		$echoHtml = '<style type="text/css">.form-field.term-parent-wrap, .form-field.term-slug-wrap {display: none;} .form-field.term-description-wrap { display:none;}  </style>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="term_meta[client_id]">'.__( 'Client', 'fakturo' ).'</label>
			</th>
			<td>
				'.$selectClients.'
				<p class="description">'.__( 'Select a client.', 'fakturo' ).'</p>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="term_meta[bank_id]">'.__( 'Bank', 'fakturo' ).'</label>
			</th>
			<td>
				'.$selectBankEntities.'
				<p class="description">'.__( 'Select a bank.', 'fakturo' ).'</p>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="term_meta[currency_id]">'.__( 'Currency', 'fakturo' ).'</label>
			</th>
			<td>
				'.$selectCurrencies.'
				<p class="description">'.__( 'Select a currency.', 'fakturo' ).'</p>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="term_meta[value]">'.__( 'Value', 'fakturo' ).'</label>
			</th>
			<td>
				<input type="text" name="term_meta[value]" id="term_meta_value" value="'.$term_meta->value.'" style="width: 150px;text-align: right; padding-right: 0px; ">
				<p class="description">'.__( 'Enter a value.', 'fakturo' ).'</p>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="term_meta[date]">'.__( 'Date', 'fakturo' ).'</label>
			</th>
			<td>
				<input type="text" name="term_meta[date]" id="term_meta_date" value="'.date_i18n($setting_system['dateformat'], $term_meta->date ).'" style="width: 150px;"/>
				<p class="description">'.__( 'Enter a date.', 'fakturo' ).'</p>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="term_meta[cashing_date]">'.__( 'Cashing Date', 'fakturo' ).'</label>
			</th>
			<td>
				<input type="text" name="term_meta[cashing_date]" id="term_meta_cashing_date" value="'.date_i18n($setting_system['dateformat'],  $term_meta->cashing_date ).'" style="width: 150px; ">
				<p class="description">'.__( 'Enter a cashing date.', 'fakturo' ).'</p>
			</td>
		</tr>
		<tr class="form-field" id="status_div">
			<th scope="row" valign="top">
				<label for="term_meta[status]">'.__( 'Status', 'fakturo' ).'</label>
			</th>
			<td>
				'.$echoSelectStatus.'
				<p class="description">'.__( 'Select a check status.', 'fakturo' ).'</p>
			</td>
		</tr>
		<tr class="form-field" id="date_status_div"'.(($term_meta->status!=='C')?'':'style="display:none;"').'>
			<th scope="row" valign="top">
				<label for="term_meta[date_status]">'.__( 'Date', 'fakturo' ).'</label>
			</th>
			<td>
				<input type="text" name="term_meta[date_status]" id="term_meta_date_status" value="'.date_i18n($setting_system['dateformat'],  $term_meta->date_status ).'" style="width: 150px; ">
				<p class="description">'.__( 'Enter a date.', 'fakturo' ).'</p>
			</td>
		</tr>
		<tr class="form-field" id="provider_div"'.(($term_meta->status==='P')?'':'style="display:none;"').'>
			<th scope="row" valign="top">
				<label for="term_meta[provider_id]">'.__( 'Provider', 'fakturo' ).'</label>
			</th>
			<td>
				'.$selectProviders.'
				<p class="description">'.__( 'Select a provider.', 'fakturo' ).'</p>
			</td>
		</tr>
		<tr class="form-field" id="notes_div">
			<th scope="row" valign="top">
				<label for="term_meta[notes]">'.__( 'Notes', 'fakturo' ).'</label>
			</th>
			<td>
				<textarea style="width:95%;" rows="4" name="term_meta[notes]" id="term_meta_notes">'. $term_meta->notes.'</textarea>
				<p class="description">'.__( 'Enter the notes.', 'fakturo' ).'</p>
			</td>
		</tr>
		
		
		
		
		
		';
		echo $echoHtml;
	}

	static function row_actions( $actions, $tag ){
		//unset($actions['edit']);
		unset($actions['view']);
		unset($actions['inline hide-if-no-js']);
		return $actions;
	}

	public static function columns($columns) {
		$new_columns = array(
			'cb' => '<input type="checkbox" />',
			'order' => __('Serial Number', 'fakturo'),
			'client_id' => __('Client id', 'fakturo'),
			'nameclient' => __('Name', 'fakturo'),
			'date' => __('Date', 'fakturo'),
			'value' => __('Value', 'fakturo'),
			//'type' => __('Type', 'fakturo'),
		);
		
		return $new_columns;
		
	}		

	public static function primary_column( $default, $screen_id ) {
		
		return 'order';
		
	}
	public static function theme_columns($out, $column_name, $term_id) {		
		global $primary;
		$primary = 'order';
		$setting_system = get_option('fakturo_system_options_group', false);
		$term = get_fakturo_term($term_id, self::$tax_name);
		
		switch ($column_name) {
			case 'order': 				
				$out = esc_attr($term->name).'';
				break;
			case 'client_id': 
				$out = esc_attr($term->client_id).'';
				break;
			case 'nameclient': 
				$name = 'No name';
				$dataClient = fktrPostTypeClients::get_client_data($term->client_id);
				$name = $dataClient['post_title'];
				$out = esc_attr($name).'';
				break;
			case 'date': 
				
				$out = esc_attr(date_i18n($setting_system['dateformat'], $term->date)).'';
				break;
			case 'value': 

				$out = esc_attr(number_format($term->value, $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand'])).'';
				break;
			
			default:
				break;
		}
		return $out;    
	}
	public static function before_save($fields)  {
		$setting_system = get_option('fakturo_system_options_group', false);
		if (isset($fields['value'])) {
			$fields['value'] = fakturo_mask_to_float($fields['value']);
		}
		
		if (isset($fields['date'])) {
			$fields['date'] = fakturo_date2time($fields['date'], $setting_system['dateformat'] );
		}
		if (isset($fields['cashing_date'])) {
			$fields['cashing_date'] = fakturo_date2time($fields['cashing_date'], $setting_system['dateformat'] );
		}
		if (isset($fields['date_status'])) {
			$fields['date_status'] = fakturo_date2time($fields['date_status'], $setting_system['dateformat'] );
		}
		if (isset($fields['status'])) {
			if ($fields['status'] == 'D' || $fields['status'] == 'P' || $fields['status'] == 'E') {
				$value_in_default_currency = fakturo_transform_money($fields['currency_id'], $setting_system['currency'], $fields['value']);
				fktrPostTypeClients::add_balance($fields['client_id'], $value_in_default_currency);
			}
		}
		
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

$fktr_tax_check = new fktr_tax_check();

?>