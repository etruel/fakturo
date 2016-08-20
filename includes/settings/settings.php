<?php


// Exit if accessed directly
if (!defined('ABSPATH'))  {
	exit;
}


if ( ! class_exists('fktrSettings') ) :
class fktrSettings {

	function __construct() {
		add_action( 'init', array('fktrSettings', 'load_taxonomies'), 1, 99 );
		//add_action( 'in_admin_header', array('fktrSettings', 'probandoarriba'), 1, 0 );
		add_action( 'all_admin_notices', array('fktrSettings', 'add_setting_tabs'), 1, 0 );
		add_action( 'admin_init', array('fktrSettings', 'register_settings'));
				
		add_action('admin_print_scripts', array('fktrSettings', 'scripts'));
		add_action('admin_print_styles', array('fktrSettings', 'styles'));
	}
	public static function scripts() {
		wp_enqueue_script('media-upload');
		wp_enqueue_script('thickbox');
		wp_enqueue_script( 'jquery-select2', FAKTURO_PLUGIN_URL . 'assets/js/jquery.select2.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
		wp_enqueue_script( 'jquery-mask', FAKTURO_PLUGIN_URL . 'assets/js/jquery.mask.min.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
		wp_enqueue_script( 'jquery-settings', FAKTURO_PLUGIN_URL . 'assets/js/settings.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
	}
	public static function styles() {
		wp_enqueue_style('thickbox');
		wp_enqueue_style('style-select2',FAKTURO_PLUGIN_URL .'assets/css/select2.min.css');	
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
			$value_system['ignore_stock'] = 0;
			$value_system['format_invoice_number'] = '';
			$value_system['search_code'] = 'reference';
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
							<input type="text" size="36" name="fakturo_info_options_group[tax_condition]" value="'.$options['tax_condition'].'"/>
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
		if (!isset($options['ignore_stock'])) {
			$options['ignore_stock'] = 0;
		}
		if (empty($options['format_invoice_number'])) {
			$options['format_invoice_number'] = '';
		}
		if (empty($options['format_number_receipt'])) {
			$options['format_number_receipt'] = '';
		}
		if (empty($options['search_code'])) {
			$options['search_code'] = 'reference';
		}
		
		update_option('fakturo_system_options_group' , $options);
		
		
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
		
		$echoSelectSearchCode = '<select id="fakturo_system_options_group_search_code" name="fakturo_system_options_group[search_code]">';
		foreach ($selectSearchCode as $key => $txt) {
			$echoSelectSearchCode .= '<option value="'.$key.'"'.selected($key, $options['search_code'], false).'>'.$txt.'</option>';
		}
		$echoSelectSearchCode .= '</select>';						
										
									
			
									
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
								  '. __( 'Choose your currency. Note that some payment gateways have currency restrictions.', FAKTURO_TEXT_DOMAIN ) .' 
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
						</td>
					  </tr>
					  
					  <tr>
							<th>'. __( 'Ignore stock?', FAKTURO_TEXT_DOMAIN ) .'</th>
							<td class="italic-label">
								<input id="fakturo_system_options_group_ignore_stock" class="slidercheck" type="checkbox" name="fakturo_system_options_group[ignore_stock]" value="1" '.(($options['ignore_stock'])?'checked="checked"':'').'>
								<label for="fakturo_system_options_group_ignore_stock"><span class="ui"></span>'. __( 'Ignore stock used in the system', FAKTURO_TEXT_DOMAIN ).'	</label>
							
							</td>
						</td>
					  </tr>
					   <tr>
							<th>'. __( 'Format of invoice number', FAKTURO_TEXT_DOMAIN ) .'</th>
							<td class="italic-label">
								<input id="fakturo_system_options_group_format_invoice_number" name="fakturo_system_options_group[format_invoice_number]" type="text" value="'.$options['format_invoice_number'].'">
								<label for="fakturo_system_format_invoice_number">
									'. __( 'Choose the default Format of invoice number used in the system', FAKTURO_TEXT_DOMAIN ) .'           
								</label>
					
							</td>
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