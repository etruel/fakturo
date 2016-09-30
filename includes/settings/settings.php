<?php


// Exit if accessed directly
if (!defined('ABSPATH'))  {
	exit;
}


if ( ! class_exists('fktrSettings') ) :
class fktrSettings {

	function __construct() {
		//add_action( 'init', array('fktrSettings', 'load_taxonomies'), 1, 99 );
		
		add_action( 'all_admin_notices', array('fktrSettings', 'add_setting_tabs'), 1, 0 );
		add_action( 'admin_init', array('fktrSettings', 'register_settings'));
				
		add_filter('parent_file',  array( __CLASS__, 'tax_menu_correction'));
		add_filter('submenu_file',  array( __CLASS__, 'tax_submenu_correction'));
		
		add_action('admin_print_scripts', array('fktrSettings', 'scripts'));
		add_action('admin_print_styles', array('fktrSettings', 'styles'));
	}
	// highlight the proper top level menu
	static function tax_menu_correction($parent_file) {
		global $current_screen;  // este no lo toma
		if ($current_screen->id == "admin_page_fakturo-settings-system") {
			//$pagenow = null;
			$parent_file = 'admin.php?page=fakturo_dashboard';
		}
		return $parent_file;
	}
	
	// highlight the proper sub level menu
	static function tax_submenu_correction($submenu_file) {
		global $current_screen;  //este lo toma pero el menu superior no
		if ($current_screen->id == "admin_page_fakturo-settings-system") {
			$submenu_file = 'fakturo-settings';
		}
		return $submenu_file;
	}
	public static function scripts() {
		wp_enqueue_script('media-upload');
		wp_enqueue_script('thickbox');
		wp_enqueue_script( 'jquery-select2', FAKTURO_PLUGIN_URL . 'assets/js/jquery.select2.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
		wp_enqueue_script( 'jquery-mask', FAKTURO_PLUGIN_URL . 'assets/js/jquery.mask.min.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
		wp_enqueue_script( 'jquery-settings', FAKTURO_PLUGIN_URL . 'assets/js/settings.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
	}
	public static function styles() {
		global $current_screen;

		wp_enqueue_style('thickbox');
		wp_enqueue_style('style-select2',FAKTURO_PLUGIN_URL .'assets/css/select2.min.css');	
		if ($current_screen->id == 'admin_page_fakturo-settings-system') {
			wp_enqueue_style('style-settings',FAKTURO_PLUGIN_URL .'assets/css/settings-system.css');	
		}
		
	}
	public static function register_settings() {
		register_setting(
			'fakturo-settings',  // settings section
			'fakturo_info_options_group' // setting name
		);
		$value = get_option('fakturo_info_options_group', false);

		if ($value===false) {
			$values = array();
			$values['name'] = '';
			$values['taxpayer'] = '';
			$values['tax'] = '';
			$values['start'] = '';
			$values['address'] = '';
			$values['telephone'] = '';
			$values['postcode'] = '';
			$values['city'] = '';
			$values['state'] = '';
			$values['country'] = '';
			$values['website'] = '';
			$values['tax_condition'] = '';
			$values['url'] = FAKTURO_PLUGIN_URL . 'assets/images/etruel-logo.png';
			$values = apply_filters('fktr_info_options_init', $values);
			update_option('fakturo_info_options_group' , $values);
		} 
		
		register_setting(
			'fakturo-settings-system',  // settings section
			'fakturo_system_options_group' // setting name
		);
		$value_system = get_option('fakturo_system_options_group', false);
		
		if ($value_system===false) {
			$value_system = array();
			$value_system['currency'] = 0;
			$value_system['currency_position'] = 'before';
			$value_system['tax'] = '';
			$value_system['thousand'] = ',';
			$value_system['decimal'] = '.';
			$value_system['decimal_numbers'] = '2';
			$value_system['invoice_type'] = -1;
			$value_system['price_scale'] = -1;
			$value_system['use_stock_product'] = 0;
			$value_system['stock_less_zero'] = 0;
			$value_system['sale_point'] = 0;
			$value_system['digits_invoice_number'] = 8;
			$value_system['list_invoice_number'] = array('sale_point', 'invoice_number');
			$value_system['list_invoice_number_separator'] = ' ';
			$value_system['individual_numeration_by_invoice_type'] = 1;
			$value_system['individual_numeration_by_sale_point'] = 1;
			$value_system['digits_receipt_number'] = 8;
			$value_system['search_code'] = array('reference');
			$value_system['default_code'] = 'reference';
			$value_system['default_description'] = 'short_description';
			$value_system['dateformat'] = 'dd/mm/YYYY';
			$value_system = apply_filters('fktr_system_options_init', $value_system);
			update_option('fakturo_system_options_group' , $value_system);
		}

	}
	

	public static function fakturo_settings() {  
		global $current_screen;
		$options = get_option('fakturo_info_options_group');
		if (empty($options['url'])) {
			$options['url'] = FAKTURO_PLUGIN_URL . 'assets/images/etruel-logo.png';
		}
		update_option('fakturo_info_options_group' , $options);
		$selectTaxCondition = wp_dropdown_categories( array(
			'show_option_all'    => '',
			'show_option_none'   => __('Choose a Tax Condition', FAKTURO_TEXT_DOMAIN ),
			'orderby'            => 'name', 
			'order'              => 'ASC',
			'show_count'         => 0,
			'hide_empty'         => 0, 
			'child_of'           => 0,
			'exclude'            => '',
			'echo'               => 0,
			'selected'           => $options['tax_condition'],
			'hierarchical'       => 1, 
			'name'               => 'fakturo_info_options_group[tax_condition]',
			'class'              => '',
			'id'				 => 'fakturo_info_options_group_tax_condition',
			'depth'              => 1,
			'tab_index'          => 0,
			'taxonomy'           => 'fktr_tax_conditions',
			'hide_if_empty'      => false
		));
		
		echo '<div id="tab_container">
			<br/><h1>Company Info</h1>
			<form method="post" action="options.php">';
			settings_fields('fakturo-settings');
			do_settings_sections('fakturo-settings');
			echo '<table class="form-table">
					<tr valign="top">
						<th scope="row">'. __( 'Name', FAKTURO_TEXT_DOMAIN ) .'</th>
						<td>
							<input type="text" size="36" name="fakturo_info_options_group[name]" value="'.$options['name'].'"/>
                        </td>
                    </tr>
					<tr valign="top">
						<th scope="row">'. __( 'Taxpayer ID', FAKTURO_TEXT_DOMAIN ) .'</th>
						<td>
							<input type="text" size="36" id="fakturo_info_options_group_taxpayer" name="fakturo_info_options_group[taxpayer]" value="'.$options['taxpayer'].'"/>
                        </td>
                    </tr>
					<tr valign="top">
						<th scope="row">'. __( 'Gross income tax ID', FAKTURO_TEXT_DOMAIN ) .'</th>
						<td>
							<input type="text" size="36" name="fakturo_info_options_group[tax]" value="'.$options['tax'].'"/>
                        </td>
                    </tr>
					<tr valign="top">
						<th scope="row">'. __( 'Start of activities', FAKTURO_TEXT_DOMAIN ) .'</th>
						<td>
							<input type="text" size="36" name="fakturo_info_options_group[start]" value="'.$options['start'].'"/>
                        </td>
                    </tr>
					<tr valign="top">
						<th scope="row">'. __( 'Address', FAKTURO_TEXT_DOMAIN ) .'</th>
						<td>
							<textarea name="fakturo_info_options_group[address]" cols="36" rows="4">'.$options['address'].'</textarea>
						</td>
                    </tr>
					<tr valign="top">
						<th scope="row">'. __( 'Telephone', FAKTURO_TEXT_DOMAIN ) .'</th>
						<td>
							<input type="text" size="36" name="fakturo_info_options_group[telephone]" value="'.$options['telephone'].'"/>
						</td>
                    </tr>
					<tr valign="top">
						<th scope="row">'. __( 'Postcode', FAKTURO_TEXT_DOMAIN ) .'</th>
						<td>
							<input type="text" size="36" name="fakturo_info_options_group[postcode]" value="'.$options['postcode'].'"/>
						</td>
                    </tr>
					<tr valign="top">
						<th scope="row">'. __( 'City', FAKTURO_TEXT_DOMAIN ) .'</th>
						<td>
							<input type="text" size="36" name="fakturo_info_options_group[city]" value="'.$options['city'].'"/>
						</td>
                    </tr>
					<tr valign="top">
						<th scope="row">'. __( 'State', FAKTURO_TEXT_DOMAIN ) .'</th>
						<td>
							<input type="text" size="36" name="fakturo_info_options_group[state]" value="'.$options['state'].'"/>
						</td>
                    </tr>
					<tr valign="top">
						<th scope="row">'. __( 'Country', FAKTURO_TEXT_DOMAIN ) .'</th>
						<td>
							<input type="text" size="36" name="fakturo_info_options_group[country]" value="'.$options['country'].'"/>
						</td>
                    </tr>
					<tr valign="top">
						<th scope="row">'. __( 'Website', FAKTURO_TEXT_DOMAIN ) .'</th>
						<td>
							<input type="text" size="36" name="fakturo_info_options_group[website]" value="'.$options['website'].'"/>
						</td>
                    </tr>
					<tr valign="top">
						<th scope="row">'. __( 'Tax condition', FAKTURO_TEXT_DOMAIN ) .'</th>
						<td>
							'.$selectTaxCondition.'
						</td>
                    </tr>
					<tr valign="top">
						<th scope="row">'. __( 'Company Logo', FAKTURO_TEXT_DOMAIN ) .'</th>
						<td>
							<label for="upload_image">
								<input id="url" type="text" size="36" value="'.$options['url'].'" name="fakturo_info_options_group[url]" />
								<input id="upload_logo_button" type="button" value="Upload Image" />
								<br />'.__( 'Enter an URL or upload an image for the company logo.', FAKTURO_TEXT_DOMAIN ).'
							</label>
							
							<p style="padding-top: 5px;">'. __( 'This is your current logo', FAKTURO_TEXT_DOMAIN ) .'</p><img id="setting_img_log" src="'. $options['url'] .'" style="padding:5px;" />
						</td>
                    </tr>
				
				';


			echo '</table>';
			submit_button();
			echo '</form>
		</div><!-- #tab_container-->';
		
	}
	
	public static function fakturo_settings_system() {  
		global $current_screen;
		$options = get_option('fakturo_system_options_group');
		if (empty($options['decimal_numbers'])) {
			$options['decimal_numbers'] = 2;
		}
		if (empty($options['thousand'])) {
			$options['thousand'] = ',';
		}
		if (empty($options['decimal'])) {
			$options['decimal'] = '.';
		}
		if (!isset($options['use_stock_product'])) {
			$options['use_stock_product'] = 0;
		}
		if (!isset($options['stock_less_zero'])) {
			$options['stock_less_zero'] = 0;
		}
		
		if (!isset($options['sale_point'])) {
			$options['sale_point'] = 0;
		}
		if (empty($options['digits_invoice_number'])) {
			$options['digits_invoice_number'] = 8;
		}
		if (empty($options['digits_receipt_number'])) {
			$options['digits_receipt_number'] = 8;
		}

		if (empty($options['list_invoice_number'])) {
			$options['list_invoice_number'] = array('sale_point', 'invoice_number');
		}
		if (!isset($options['list_invoice_number_separator'])) {
			$options['list_invoice_number_separator'] = ' ';
		}
		if (!isset($options['individual_numeration_by_invoice_type'])) {
			$options['individual_numeration_by_invoice_type'] = 0;
		}
		if (!isset($options['individual_numeration_by_sale_point'])) {
			$options['individual_numeration_by_sale_point'] = 0;
		}
		
		if (empty($options['format_number_receipt'])) {
			$options['format_number_receipt'] = '';
		}
		if (empty($options['search_code'])) {
			$options['search_code'] = array();
		}
		if (empty($options['default_code'])) {
			$options['default_code'] = 'reference';
		}
		if (empty($options['default_description'])) {
			$options['default_description'] = 'short_description';
		}
		if (empty($options['dateformat'])) {
			$options['dateformat'] = 'd/m/Y';
		}

		update_option('fakturo_system_options_group' , $options);
		
		$selectSalePoint = wp_dropdown_categories( array(
										'show_option_all'    => '',
										'show_option_none'   => __('Choose a Sale Point', FAKTURO_TEXT_DOMAIN ),
										'orderby'            => 'name', 
										'order'              => 'ASC',
										'show_count'         => 0,
										'hide_empty'         => 0, 
										'child_of'           => 0,
										'exclude'            => '',
										'echo'               => 0,
										'selected'           => $options['sale_point'],
										'hierarchical'       => 1, 
										'name'               => 'fakturo_system_options_group[sale_point]',
										'id'                 => 'fakturo_system_options_group_sale_point',
										'class'              => 'form-no-clear',
										'depth'              => 1,
										'tab_index'          => 0,
										'taxonomy'           => 'fktr_sale_points',
										'hide_if_empty'      => false
									));
		
		
		
		$selectCurrency = wp_dropdown_categories( array(
										'show_option_all'    => '',
										'show_option_none'   => __('Choose a Currency', FAKTURO_TEXT_DOMAIN ),
										'orderby'            => 'name', 
										'order'              => 'ASC',
										'show_count'         => 0,
										'hide_empty'         => 0, 
										'child_of'           => 0,
										'exclude'            => '',
										'echo'               => 0,
										'selected'           => $options['currency'],
										'hierarchical'       => 1, 
										'name'               => 'fakturo_system_options_group[currency]',
										'id'               => 'fakturo_system_options_group_currency',
										'class'              => 'form-no-clear',
										'depth'              => 1,
										'tab_index'          => 0,
										'taxonomy'           => 'fktr_currencies',
										'hide_if_empty'      => false
									));
									
									
		$selectInvoiceType = wp_dropdown_categories( array(
										'show_option_all'    => '',
										'show_option_none'   => __('Choose a Invoice Type', FAKTURO_TEXT_DOMAIN ),
										'orderby'            => 'name', 
										'order'              => 'ASC',
										'show_count'         => 0,
										'hide_empty'         => 0, 
										'child_of'           => 0,
										'exclude'            => '',
										'echo'               => 0,
										'selected'           => $options['invoice_type'],
										'hierarchical'       => 1, 
										'name'               => 'fakturo_system_options_group[invoice_type]',
										'id'            	 => 'fakturo_system_options_group_invoice_type',
										'class'              => 'form-no-clear',
										'depth'              => 1,
										'tab_index'          => 0,
										'taxonomy'           => 'fktr_invoice_types',
										'hide_if_empty'      => false
									));
			$selectPriceScales = wp_dropdown_categories( array(
										'show_option_all'    => '',
										'show_option_none'   => __('Choose a Price Scale', FAKTURO_TEXT_DOMAIN ),
										'orderby'            => 'name', 
										'order'              => 'ASC',
										'show_count'         => 0,
										'hide_empty'         => 0, 
										'child_of'           => 0,
										'exclude'            => '',
										'echo'               => 0,
										'selected'           => $options['price_scale'],
										'hierarchical'       => 1, 
										'name'               => 'fakturo_system_options_group[price_scale]',
										'id'            	 => 'fakturo_system_options_group_price_scale',
										'class'              => 'form-no-clear',
										'depth'              => 1,
										'tab_index'          => 0,
										'taxonomy'           => 'fktr_price_scales',
										'hide_if_empty'      => false
									));
									
		$selectSearchCode = array();
		$selectSearchCode['reference'] = __( 'Reference', FAKTURO_TEXT_DOMAIN );
		$selectSearchCode['internal_code'] = __( 'Internal code', FAKTURO_TEXT_DOMAIN );
		$selectSearchCode['manufacturers_code'] = __( 'Manufacturers code', FAKTURO_TEXT_DOMAIN );							
		$selectSearchCode = apply_filters('fktr_search_code_array', $selectSearchCode);
		
		//echo print_r($options['search_code'], true);
		//
		$echoSelectSearchCode = '<select id="fakturo_system_options_group_search_code" name="fakturo_system_options_group[search_code][]" multiple="multiple" >';
		foreach ($selectSearchCode as $key => $txt) {
			$echoSelectSearchCode .= '<option value="'.$key.'"'.selected($key, (array_search($key, $options['search_code'])!==false) ? $key : '' , false).'>'.$txt.'</option>';
		}
		$echoSelectSearchCode .= '</select>';						
										
									
		$selectDefaultCode = array();
		$selectDefaultCode['reference'] = __( 'Reference', FAKTURO_TEXT_DOMAIN );
		$selectDefaultCode['internal_code'] = __( 'Internal code', FAKTURO_TEXT_DOMAIN );
		$selectDefaultCode['manufacturers_code'] = __( 'Manufacturers code', FAKTURO_TEXT_DOMAIN );							
		$selectDefaultCode = apply_filters('fktr_default_code_array', $selectDefaultCode);
		
		$echoSelectDefaultCode = '<select id="fakturo_system_options_group_default_code" name="fakturo_system_options_group[default_code]">';
		foreach ($selectDefaultCode as $key => $txt) {
			$echoSelectDefaultCode .= '<option value="'.$key.'"'.selected($key, $options['default_code'], false).'>'.$txt.'</option>';
		}
		$echoSelectDefaultCode .= '</select>';		
		
		
		$selectDefaultDescription = array();
		$selectDefaultDescription['short_description'] = __( 'Short Description', FAKTURO_TEXT_DOMAIN );
		$selectDefaultDescription['description'] = __( 'Description', FAKTURO_TEXT_DOMAIN );						
		$selectDefaultDescription = apply_filters('fktr_default_description_array', $selectDefaultDescription);
		
		$echoSelectDefaultDescription = '<select id="fakturo_system_options_group_default_description" name="fakturo_system_options_group[default_description]">';
		foreach ($selectDefaultDescription as $key => $txt) {
			$echoSelectDefaultDescription .= '<option value="'.$key.'"'.selected($key, $options['default_description'], false).'>'.$txt.'</option>';
		}
		$echoSelectDefaultDescription .= '</select>';		
			
		$selectDefaultDate = array();
		$selectDefaultDate['d/m/Y'] = __( 'dd/mm/YYYY', FAKTURO_TEXT_DOMAIN );
		$selectDefaultDate['m/d/Y'] = __( 'mm/dd/YYYY', FAKTURO_TEXT_DOMAIN );						
		$selectDefaultDate = apply_filters('fktr_default_format_date_array', $selectDefaultDate);
		
		$echoSelectDefaultDate = '<select id="fakturo_system_options_group_dateformat" name="fakturo_system_options_group[dateformat]">';
		foreach ($selectDefaultDate as $key => $txt) {
			$echoSelectDefaultDate .= '<option value="'.$key.'"'.selected($key, $options['dateformat'], false).'>'.$txt.'</option>';
		}
		$echoSelectDefaultDate .= '</select>';		
		
		$selectListInvoiceNumber = array();
		$selectListInvoiceNumber['sale_point'] = __( 'Sale point', FAKTURO_TEXT_DOMAIN );
		$selectListInvoiceNumber['invoice_number'] = __('Invoice number', FAKTURO_TEXT_DOMAIN );
		$selectListInvoiceNumber['invoice_type_name'] = __('Invoice Type name', FAKTURO_TEXT_DOMAIN );
		$selectListInvoiceNumber['invoice_type_short_name'] = __('Invoice Type Short-name', FAKTURO_TEXT_DOMAIN );
		$selectListInvoiceNumber['invoice_type_symbol'] = __('Invoice Type symbol', FAKTURO_TEXT_DOMAIN );
		
		$selectListInvoiceNumber = apply_filters('fktr_list_invoice_number_array', $selectListInvoiceNumber);
		
		//echo print_r($options['search_code'], true);
		//
		$echoSelectListInvoiceNumber = '<select id="fakturo_system_options_group_list_invoice_number" name="fakturo_system_options_group[list_invoice_number][]" multiple="multiple">';
		foreach ($selectListInvoiceNumber as $key => $txt) {
			$echoSelectListInvoiceNumber .= '<option value="'.$key.'"'.selected($key, (array_search($key, $options['list_invoice_number'])!==false) ? $key : '' , false).'>'.$txt.'</option>';
		}
		$echoSelectListInvoiceNumber .= '</select>';			
		 
	
									
		echo '
		<div id="tab_container">
			<br/><h1>System Settings</h1>
			<form method="post" action="options.php">
				<table class="form-table">';
				settings_fields('fakturo-settings-system');
				do_settings_sections('fakturo-settings-system');
				echo '<tr>
						<th>'. __( 'Currency', FAKTURO_TEXT_DOMAIN ) .'</th>
						<td class="italic-label">
								  '.$selectCurrency.'	
								  <label for="fakturo_system_currency">
								  '. __( 'Choose your currency.', FAKTURO_TEXT_DOMAIN ) .' 
							        </label>
						</td>
					  </tr>
					  <tr>
							<th>'. __( 'Currency Position', FAKTURO_TEXT_DOMAIN ) .'</th>
							<td class="italic-label">
									<select id="fakturo_system_options_group_currency_position" name="fakturo_system_options_group[currency_position]">
										<option value="before"'.selected('before', $options['currency_position'], false).'>Before - $10</option>
										<option value="after"'.selected('after', $options['currency_position'], false).'>After - 10$</option>
									</select>
									<label for="fakturo_system_position">
										'. __( 'Choose the location of the currency sign.', FAKTURO_TEXT_DOMAIN ) .'             
									</label>
							</td>
						
					  </tr>
					  
					  <tr>
							<th>'. __( 'Thousands Separator', FAKTURO_TEXT_DOMAIN ) .'</th>
							<td class="italic-label">
								<input id="fakturo_system_options_group_thousand" name="fakturo_system_options_group[thousand]" type="text" size="5" value="'.$options['thousand'].'">
								<label for="fakturo_system_thousand">
									'. __( 'The symbol (usually , or .) to separate thousands', FAKTURO_TEXT_DOMAIN ) .'           
								</label>
					
							</td>
						
					  </tr>
					  
					  <tr>
							<th>'. __( 'Decimal Separator', FAKTURO_TEXT_DOMAIN ) .'</th>
							<td class="italic-label">
								<input id="fakturo_system_options_group_decimal" name="fakturo_system_options_group[decimal]" type="text" size="5" value="'.$options['decimal'].'">
								<label for="fakturo_system_decimal">
									'. __( 'The symbol (usually , or .) to separate decimal points', FAKTURO_TEXT_DOMAIN ) .'           
								</label>
					
							</td>
						
					  </tr>
					  
					  <tr>
							<th>'. __( 'Decimal numbers', FAKTURO_TEXT_DOMAIN ) .'</th>
							<td class="italic-label">
								<input id="fakturo_system_options_group_decimal_numbers" type="number" min="0" max="9" maxlength="1" name="fakturo_system_options_group[decimal_numbers]" value="'.$options['decimal_numbers'].'">
								<label for="fakturo_system_decimal_numbers">
									'. __( 'Enter the number of numbers decimals', FAKTURO_TEXT_DOMAIN ) .'           
								</label>
					
							</td>
						
					  </tr>
					  
					   <tr>
							<th>'. __( 'Default Invoice Type', FAKTURO_TEXT_DOMAIN ) .'</th>
							<td class="italic-label">
								  '.$selectInvoiceType.'	
								  <label for="fakturo_system_options_group_invoice_type">
								  '. __( 'Choose the default Invoice Type used in the system', FAKTURO_TEXT_DOMAIN ) .' 
							        </label>
							</td>
						
					  </tr>
					  
					  
					   <tr>
							<th>'. __( 'Default Price Scale', FAKTURO_TEXT_DOMAIN ) .'</th>
							<td class="italic-label">
								  '.$selectPriceScales.'	
								  <label for="fakturo_system_options_group_price_scale">
								  '. __( 'Choose the default Price Scale used in the system', FAKTURO_TEXT_DOMAIN ) .' 
							        </label>
							</td>
						
					  </tr>
					  
					  <tr>
							<th>'. __( 'Use stock for products', FAKTURO_TEXT_DOMAIN ) .'</th>
							<td class="italic-label">
								<input id="fakturo_system_options_group_use_stock_product" class="slidercheck" type="checkbox" name="fakturo_system_options_group[use_stock_product]" value="1" '.(($options['use_stock_product'])?'checked="checked"':'').'>
								<label for="fakturo_system_options_group_use_stock_product"><span class="ui"></span>'. __( 'Activate for use stock for products', FAKTURO_TEXT_DOMAIN ).'	</label>
							
							</td>
						
					  </tr>
					  <tr>
							<th>'. __( 'Allow negative stocks', FAKTURO_TEXT_DOMAIN ) .'</th>
							<td class="italic-label">
								<input id="fakturo_system_options_group_stock_less_zero" class="slidercheck" type="checkbox" name="fakturo_system_options_group[stock_less_zero]" value="1" '.(($options['stock_less_zero'])?'checked="checked"':'').'>
								<label for="fakturo_system_options_group_stock_less_zero"><span class="ui"></span>'. __( 'Activate for use stock less than zero.', FAKTURO_TEXT_DOMAIN ).'	</label>
							
						
							</td>
					  </tr>
					  
					  
					  <tr>
						<th>'. __( 'Sale Point', FAKTURO_TEXT_DOMAIN ) .'</th>
						<td class="italic-label">
								  '.$selectSalePoint.'	
								  <label for="fakturo_system_sale_point">
								  '. __( 'Choose your sale point.', FAKTURO_TEXT_DOMAIN ) .' 
							        </label>
						</td>
					  </tr>
					  <tr>
							<th>'. __( 'Number of digits of the receipt number', FAKTURO_TEXT_DOMAIN ) .'</th>
							<td class="italic-label">
								<input id="fakturo_system_options_group_digits_receipt_number" name="fakturo_system_options_group[digits_receipt_number]" type="number" maxlength="2" min=2 max=20 value="'.$options['digits_receipt_number'].'">
								<label for="fakturo_system_digits_receipt_number">
									'. __( 'Choose the default number of digits of the receipt number.', FAKTURO_TEXT_DOMAIN ) .'           
								</label>
					
							</td>
						
					  </tr>
					   <tr>
							<th>'. __( 'Number of digits of the invoice number', FAKTURO_TEXT_DOMAIN ) .'</th>
							<td class="italic-label">
								<input id="fakturo_system_options_group_digits_invoice_number" name="fakturo_system_options_group[digits_invoice_number]" type="number" maxlength="2" min=2 max=20 value="'.$options['digits_invoice_number'].'">
								<label for="fakturo_system_digits_invoice_number">
									'. __( 'Choose the default number of digits of the invoice number.', FAKTURO_TEXT_DOMAIN ) .'           
								</label>
					
							</td>
						
					  </tr>
					  <tr>
							<th>'. __( 'Format invoice numbers in lists and reports', FAKTURO_TEXT_DOMAIN ) .'</th>
							<td class="italic-label">
									'.$echoSelectListInvoiceNumber.'
									<label for="fakturo_system_position">
										'. __( '', FAKTURO_TEXT_DOMAIN ) .'             
									</label>
							</td>
						
					  </tr>
					   <tr>
							<th>'. __( 'Individual numeration by Invoice Type', FAKTURO_TEXT_DOMAIN ) .'</th>
							<td class="italic-label">
								<input id="fakturo_system_options_group_individual_numeration_by_invoice_type" class="slidercheck" type="checkbox" name="fakturo_system_options_group[individual_numeration_by_invoice_type]" value="1" '.(($options['individual_numeration_by_invoice_type'])?'checked="checked"':'').'>
								<label for="fakturo_system_options_group_individual_numeration_by_invoice_type"><span class="ui"></span>'. __( 'Activate for use individual numeration by Invoice Type', FAKTURO_TEXT_DOMAIN ).'	</label>
							
							</td>
						
					  </tr>
					   <tr>
							<th>'. __( 'Individual numeration by Sale Point', FAKTURO_TEXT_DOMAIN ) .'</th>
							<td class="italic-label">
								<input id="fakturo_system_options_group_individual_numeration_by_sale_point" class="slidercheck" type="checkbox" name="fakturo_system_options_group[individual_numeration_by_sale_point]" value="1" '.(($options['individual_numeration_by_sale_point'])?'checked="checked"':'').'>
								<label for="fakturo_system_options_group_individual_numeration_by_sale_point"><span class="ui"></span>'. __( 'Activate for use individual numeration by Sale Point ', FAKTURO_TEXT_DOMAIN ).'	</label>
							
							</td>
						
					  </tr>
					  <tr>
							<th>'. __( 'Format of number of receipt', FAKTURO_TEXT_DOMAIN ) .'</th>
							<td class="italic-label">
								<input id="fakturo_system_options_group_format_number_receipt" name="fakturo_system_options_group[format_number_receipt]" type="text" value="'.$options['format_number_receipt'].'">
								<label for="fakturo_system_format_invoice_number">
									'. __( 'Choose the default Format of number of receipt used in the system', FAKTURO_TEXT_DOMAIN ) .'           
								</label>
					
							</td>
						
					  </tr>
					  <tr>
							<th>'. __( 'Search code on invoices, budgets, etc..', FAKTURO_TEXT_DOMAIN ) .'</th>
							<td class="italic-label">
									'.$echoSelectSearchCode.'
									<label for="fakturo_system_position">
										'. __( '', FAKTURO_TEXT_DOMAIN ) .'             
									</label>
							</td>
						
					  </tr>
					   <tr>
							<th>'. __( 'Default code for invoice', FAKTURO_TEXT_DOMAIN ) .'</th>
							<td class="italic-label">
									'.$echoSelectDefaultCode.'
									<label for="fakturo_system_position">
										'. __( '', FAKTURO_TEXT_DOMAIN ) .'             
									</label>
							</td>
					  </tr>
					   <tr>
							<th>'. __( 'Default description for invoice', FAKTURO_TEXT_DOMAIN ) .'</th>
							<td class="italic-label">
									'.$echoSelectDefaultDescription.'
									<label for="fakturo_system_options_group_default_description">
										'. __( '', FAKTURO_TEXT_DOMAIN ) .'             
									</label>
							</td>
					  </tr>
					  
					   <tr>
							<th>'. __( 'Default date format', FAKTURO_TEXT_DOMAIN ) .'</th>
							<td class="italic-label">
									'.$echoSelectDefaultDate.'
									<label for="fakturo_system_options_group_dateformat">
										'. __( '', FAKTURO_TEXT_DOMAIN ) .'             
									</label>
							</td>
				
					  </tr>
					  
					  ';
				
				echo '</table>';
				submit_button();
			echo '</form>
		</div><!-- #tab_container-->
		';

	}
	
	public static function add_setting_tabs() {
		global $current_screen;
		
		
		$sections_tabs = array(
			'general' => array( 
				'company_info' => array('text' => __( 'Company Info', FAKTURO_TEXT_DOMAIN ), 'url' => admin_url('admin.php?page=fakturo-settings'), 'screen' => 'fakturo_page_fakturo-settings') , 
				'system_settings' =>  array('text' => __( 'System Settings', FAKTURO_TEXT_DOMAIN ), 'url' => admin_url('admin.php?page=fakturo-settings-system'), 'screen' => 'admin_page_fakturo-settings-system'), 
				'invoice_type' =>  array('text' => __( 'Invoice Types', FAKTURO_TEXT_DOMAIN ), 'url' => admin_url('edit-tags.php?taxonomy=fktr_invoice_types'), 'screen' => 'edit-fktr_invoice_types'),
				'sale_points' =>  array('text' => __( 'Sale Points', FAKTURO_TEXT_DOMAIN ), 'url' => admin_url('edit-tags.php?taxonomy=fktr_sale_points'), 'screen' => 'edit-fktr_sale_points'),
				'payment_types' =>  array('text' => __( 'Payment Types', FAKTURO_TEXT_DOMAIN ), 'url' => admin_url('edit-tags.php?taxonomy=fktr_payment_types'), 'screen' => 'edit-fktr_payment_types'), 
				'default' => array('text' => __( '​​General Settings', FAKTURO_TEXT_DOMAIN ), 'url' => admin_url('admin.php?page=fakturo-settings'), 'screen' => 'fakturo_page_fakturo-settings')
	
			),
			'tables' => array( 
				'print-template' =>  array('text' => __( 'Print Template', FAKTURO_TEXT_DOMAIN ), 'url' => '', 'screen' => ''), 
				'currencies' =>  array('text' => __( 'Currencies', FAKTURO_TEXT_DOMAIN ), 'url' => admin_url('edit-tags.php?taxonomy=fktr_currencies'), 'screen' => 'edit-fktr_currencies'),
				'bank_entities' =>  array('text' => __( 'Bank Entities', FAKTURO_TEXT_DOMAIN ), 'url' => admin_url('edit-tags.php?taxonomy=fktr_bank_entities'), 'screen' => 'edit-fktr_bank_entities'),
				'countries' => array('text' => __( 'Countries and States', FAKTURO_TEXT_DOMAIN ), 'url' => admin_url('edit-tags.php?taxonomy=fktr_countries'), 'screen' => 'edit-fktr_countries') ,
				'default' => array('text' => __( 'Tables', FAKTURO_TEXT_DOMAIN ), 'url' => admin_url('edit-tags.php?taxonomy=fktr_currencies'), 'screen' => 'edit-fktr_currencies')
			),
			'products' => array( 
				'product_types' =>  array('text' => __( 'Product Types', FAKTURO_TEXT_DOMAIN ), 'url' => admin_url('edit-tags.php?taxonomy=fktr_product_type'), 'screen' => 'edit-fktr_product_type') ,
				'locations' => array('text' =>  __( 'Locations', FAKTURO_TEXT_DOMAIN ), 'url' => admin_url('edit-tags.php?taxonomy=fktr_locations'), 'screen' => 'edit-fktr_locations'),
				'packagings' =>  array('text' => __( 'Packagings', FAKTURO_TEXT_DOMAIN ), 'url' => admin_url('edit-tags.php?taxonomy=fktr_packaging'), 'screen' => 'edit-fktr_packaging') , 
				'price_scales' =>  array('text' => __( 'Price Scales', FAKTURO_TEXT_DOMAIN ), 'url' => admin_url('edit-tags.php?taxonomy=fktr_price_scales'), 'screen' => 'edit-fktr_price_scales') ,
				'origins' =>  array('text' => __( 'Origins', FAKTURO_TEXT_DOMAIN ), 'url' => admin_url('edit-tags.php?taxonomy=fktr_origins'), 'screen' => 'edit-fktr_origins') ,
				'default' => array('text' => __( '​​Products', FAKTURO_TEXT_DOMAIN ), 'url' => admin_url('edit-tags.php?taxonomy=fktr_product_type'), 'screen' => 'edit-fktr_product_type')
			),
			'taxes' => array( 
				'taxes' =>  array('text' => __( 'Taxes', FAKTURO_TEXT_DOMAIN ), 'url' => admin_url('edit-tags.php?taxonomy=fktr_tax'), 'screen' => 'edit-fktr_tax') ,
				'tax_condition' => array('text' => __( 'Tax Conditions', FAKTURO_TEXT_DOMAIN ), 'url' => admin_url('edit-tags.php?taxonomy=fktr_tax_conditions'), 'screen' => 'edit-fktr_tax_conditions')  ,
				'default' => array('text' => __( 'Taxes', FAKTURO_TEXT_DOMAIN ), 'url' => admin_url('edit-tags.php?taxonomy=fktr_tax'), 'screen' => 'edit-fktr_tax')
			),
			'extensions' => array( 
				'repairs_status' =>  array('text' => __( 'Repairs Status', FAKTURO_TEXT_DOMAIN ), 'url' => '', 'screen' => '') ,
				'emails' =>  array('text' => __( 'Emails', FAKTURO_TEXT_DOMAIN ), 'url' => '', 'screen' => '') , 
				'default' => array('text' => __( '​​Extensions', FAKTURO_TEXT_DOMAIN ), 'url' => '', 'screen' => '')
			)
		);
		
		$sections_tabs = apply_filters('ftkr_tabs_sections', $sections_tabs);
		
		$print_tabs = false;
		foreach ($sections_tabs as $tabs_mains) {
			foreach ($tabs_mains as $sections) {
				if($current_screen->id == $sections['screen']) {
					$print_tabs = true;
					break;
				}
				
			}
		}
		
		
		if($print_tabs) {
			
			echo '<h2 class="nav-tab-wrapper fktr-settings-tabs">';
			$current_tab = 'general';
			foreach ($sections_tabs as $tab_id => $tabs_mains) {
				$tab_url = $tabs_mains['default']['url'];
				$tab_name = $tabs_mains['default']['text']; 
				foreach ($tabs_mains as $sections) {
					if ($current_screen->id == $sections['screen']){
						$current_tab = $tab_id;
						$active = ' nav-tab-active';
						break;
					} else  {
						$active = '';
					} 
				}
				echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab_name ) . '" class="nav-tab' . $active . '">' . esc_html( $tab_name ) . '</a>';

			}
			echo '</h2>';
			echo '<div class="fktr-sections"><ul class="subsubsub">';
			$delimiter = '';
			foreach ($sections_tabs[$current_tab] as $sec_id => $sections) {
				if ($sec_id != 'default') {
					$active = $current_screen->id == $sections['screen'] ?  ' current' : '';
					echo '<li>'.$delimiter.'<a href="' . esc_url( $sections['url'] ) . '" title="' . esc_attr( $sections['text'] ) . '" class="' . $active . '">' . esc_html( $sections['text'] ) . '</a></li>';
					$delimiter = ' | ';
				}
			}
			
			echo '</ul></div>';
			
			
		}
	}
	

	public static function load_taxonomies() {

		
	}
	
	
	
} 

endif;

$fktrSettings = new fktrSettings();



?>