<?php


// Exit if accessed directly
if (!defined('ABSPATH'))  {
	exit;
}


if ( ! class_exists('fktrPostTypeSales') ) :
class fktrPostTypeSales {
	function __construct() {
		
		add_action( 'init', array('fktrPostTypeSales', 'setup'), 1 );
		add_action( 'activated_plugin', array('fktrPostTypeSales', 'setup'), 1 );
		
		add_action('transition_post_status', array('fktrPostTypeSales', 'default_fields'), 10, 3);
		add_action('save_post', array('fktrPostTypeSales', 'save'), 99, 2 );
		
		add_action( 'admin_print_scripts-post-new.php', array('fktrPostTypeSales','scripts'), 11 );
		add_action( 'admin_print_scripts-post.php', array('fktrPostTypeSales','scripts'), 11 );
		
		add_action('admin_print_styles-post-new.php', array('fktrPostTypeSales','styles'));
		add_action('admin_print_styles-post.php', array('fktrPostTypeSales','styles'));
		
		
		add_filter('fktr_clean_sale_fields', array('fktrPostTypeSales', 'clean_fields'), 10, 1);
		add_filter('fktr_sale_before_save', array('fktrPostTypeSales', 'before_save'), 10, 1);
		
		add_action('wp_ajax_get_client_data', array('fktrPostTypeSales', 'get_client_data'));
		
	}
	public static function setup() {
		
		$labels = array( 
			'name' => __( 'Sales', FAKTURO_TEXT_DOMAIN ),
			'singular_name' => __( 'Sale', FAKTURO_TEXT_DOMAIN ),
			'add_new' => __( 'Add New', FAKTURO_TEXT_DOMAIN ),
			'add_new_item' => __( 'Add New Sale', FAKTURO_TEXT_DOMAIN ),
			'edit_item' => __( 'Edit Sale', FAKTURO_TEXT_DOMAIN ),
			'new_item' => __( 'New Sale', FAKTURO_TEXT_DOMAIN ),
			'view_item' => __( 'View Sale', FAKTURO_TEXT_DOMAIN ),
			'search_items' => __( 'Search Sales', FAKTURO_TEXT_DOMAIN ),
			'not_found' => __( 'No Sales found', FAKTURO_TEXT_DOMAIN ),
			'not_found_in_trash' => __( 'No Sales found in Trash', FAKTURO_TEXT_DOMAIN ),
			'parent_item_colon' => __( 'Parent Sale:', FAKTURO_TEXT_DOMAIN ),
			'menu_name' => __( 'Sales', FAKTURO_TEXT_DOMAIN ),
		);
		$capabilities = array(
			'publish_post' => 'publish_fktr_sale',
			'publish_posts' => 'publish_fktr_sales',
			'read_post' => 'read_fktr_sale',
			'read_private_posts' => 'read_private_fktr_sales',
			'edit_post' => 'edit_fktr_sale',
			'edit_published_posts' => 'edit_published_fktr_sales',
			'edit_private_posts' => 'edit_private_fktr_sales',
			'edit_posts' => 'edit_fktr_sales',
			'edit_others_posts' => 'edit_others_fktr_sales',
			'delete_post' => 'delete_fktr_sale',
			'delete_posts' => 'delete_fktr_sales',
			'delete_published_posts' => 'delete_published_fktr_sales',
			'delete_private_posts' => 'delete_private_fktr_sales',
			'delete_others_posts' => 'delete_others_fktr_sales',
		);

		$args = array( 
			'labels' => $labels,
			'hierarchical' => false,
			'description' => 'Fakturo Sales',
			'supports' => array( 'title',/* 'custom-fields' */),
			'register_meta_box_cb' => array('fktrPostTypeSales','meta_boxes'),
			'public' => true,
			'show_ui' => true,
			'show_in_menu' => true, //'edit.php?post_type=fktr_sale',
			'menu_position' => 26,
			'menu_icon' => 'dashicons-tickets', // '/images/icon_20.png',
			'show_in_nav_menus' => false,
			'publicly_queryable' => false,
			'exclude_from_search' => false,
			'has_archive' => false,
			'query_var' => true,
			'can_export' => true,
			'rewrite' => true,
			'capabilities' => $capabilities
		);

		register_post_type( 'fktr_sale', $args );

		
		add_filter('enter_title_here', array('fktrPostTypeSales', 'name_placeholder'),10,2);
		
		
		
	}
	public static function name_placeholder( $title_placeholder , $post ) {
		if($post->post_type == 'fktr_sale') {
			$title_placeholder = __('Enter Sale name here', FAKTURO_TEXT_DOMAIN );
			
		}
		return $title_placeholder;
	}
	public static function get_client_data() {
		if (!is_numeric($_POST['client_id'])) {
			$_POST['client_id'] = 0;
		}
	
		$client_data = fktrPostTypeClients::get_client_data($_POST['client_id']);
		
		
		$country_name = __('No country', FAKTURO_TEXT_DOMAIN );
		$country_data = get_fakturo_term($client_data['selected_country'], 'fktr_countries');
		if(!is_wp_error($country_data)) {
			$country_name = $country_data->name;
		}
		$client_data['selected_country_name'] = $country_name;
		
		$state_name = __('No state', FAKTURO_TEXT_DOMAIN );
		$state_data = get_fakturo_term($client_data['selected_state'], 'fktr_countries');
		if(!is_wp_error($state_data)) {
			$state_name = $state_data->name;
		}
		$client_data['selected_state_name'] = $state_name;
		
		$price_scale_name = __('No price scale', FAKTURO_TEXT_DOMAIN );
		$price_scale_data = get_fakturo_term($client_data['selected_price_scale'], 'fktr_price_scales');
		if(!is_wp_error($price_scale_data)) {
			$price_scale_name = $price_scale_data->name;
		}
		$client_data['selected_price_scale_name'] = $price_scale_name;
		
		echo json_encode($client_data);
		wp_die();
	}
	public static function styles() {
		global $post_type;
		if($post_type == 'fktr_sale') {
			wp_enqueue_style('style-select2',FAKTURO_PLUGIN_URL .'assets/css/select2.min.css');	
			wp_enqueue_style('post-type-products',FAKTURO_PLUGIN_URL .'assets/css/post-type-products.css');	
		}
	}
	public static function scripts() {
		global $post_type;
		if($post_type == 'fktr_sale') {
			wp_enqueue_script( 'jquery-select2', FAKTURO_PLUGIN_URL . 'assets/js/jquery.select2.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			wp_enqueue_script( 'jquery-vsort', FAKTURO_PLUGIN_URL . 'assets/js/jquery.vSort.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			wp_enqueue_script( 'jquery-mask', FAKTURO_PLUGIN_URL . 'assets/js/jquery.mask.min.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			wp_enqueue_script( 'post-type-sales', FAKTURO_PLUGIN_URL . 'assets/js/post-type-sales.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			
			$setting_system = get_option('fakturo_system_options_group', false);
			$tax_coditions = get_fakturo_terms(array(
							'taxonomy' => 'fktr_tax_conditions',
							'hide_empty' => false,
				));
			
			wp_localize_script('post-type-sales', 'sales_object',
				array('ajax_url' => admin_url( 'admin-ajax.php' ),
					'thousand' => $setting_system['thousand'],
					'decimal' => $setting_system['decimal'],
					'decimal_numbers' => $setting_system['decimal_numbers'],
					'tax_coditions' => json_encode($tax_coditions)

				));
		
		}
		
	}
	
	public static function meta_boxes() {
		

		//add_meta_box('fakturo-client-box', __('Client Data', FAKTURO_TEXT_DOMAIN ), array('fktrPostTypeSales', 'client_box'),'fktr_sale','side', 'high' );
		add_meta_box('fakturo-invoice-data-box', __('Invoice Data', FAKTURO_TEXT_DOMAIN ), array('fktrPostTypeSales', 'invoice_data_box'),'fktr_sale','normal', 'high' );
		
		do_action('add_ftkr_sale_meta_boxes');
	}
	public static function invoice_data_box() {
		global $post;
		$sale_data = self::get_sale_data($post->ID);
		
		$selectInvoiceTypes = wp_dropdown_categories( array(
			'show_option_all'    => '',
			'show_option_none'   => __('Choose a Invoice Type', FAKTURO_TEXT_DOMAIN ),
			'orderby'            => 'name', 
			'order'              => 'ASC',
			'show_count'         => 0,
			'hide_empty'         => 0, 
			'child_of'           => 0,
			'exclude'            => '',
			'echo'               => 0,
			'selected'           => $sale_data['invoice_type'],
			'hierarchical'       => 1, 
			'name'               => 'invoice_type',
			'class'              => 'form-no-clear',
			'id'				 => 'invoice_type',
			'depth'              => 1,
			'tab_index'          => 0,
			'taxonomy'           => 'fktr_invoice_types',
			'hide_if_empty'      => false
		));
		
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
			'selected'           => $sale_data['invoice_currency'],
			'hierarchical'       => 1, 
			'name'               => 'invoice_currency',
			'class'              => 'form-no-clear',
			'id'				 => 'invoice_currency',
			'depth'              => 1,
			'tab_index'          => 0,
			'taxonomy'           => 'fktr_currencies',
			'hide_if_empty'      => false
		));
		$allsellers = get_users( array( 'role' => 'fakturo_seller' ) );
			
		$select_sale_mans = '<select name="invoice_saleman" id="invoice_saleman">';
		$select_sale_mans .= '<option value="'.(($sale_data['invoice_saleman'] == 0)?' selected="selected"':'').'">'. __('Choose a Salesman', FAKTURO_TEXT_DOMAIN  ) . '</option>';
		foreach ( $allsellers as $suser ) {
			$select_sale_mans .= '<option value="' . $suser->ID . '" ' . selected($sale_data['invoice_saleman'], $suser->ID, false) . '>' . esc_html( $suser->display_name ) . '</option>';
		}
		$select_sale_mans .= '</select>';
		
		
		
		$selectClients = fakturo_get_select_post(array(
											'echo' => 0,
											'post_type' => 'fktr_client',
											'show_option_none' => __('Choose a Client', FAKTURO_TEXT_DOMAIN ),
											'name' => 'client_id',
											'id' => 'client_id',
											'class' => '',
											'selected' => $sale_data['client_id']
										));
										
										
		
		$show_client_data = true;
		if ($sale_data['client_id'] < 1) {
			$show_client_data = false;
		}
		
		
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
			'selected'           => $sale_data['client_data']['tax_condition'],
			'hierarchical'       => 1, 
			'name'               => 'client_data[tax_condition]',
			'class'              => '',
			'id'				 => 'client_data_tax_condition',
			'depth'              => 1,
			'tab_index'          => 0,
			'taxonomy'           => 'fktr_tax_conditions',
			'hide_if_empty'      => false
		));
		
		$selectPaymentTypes = wp_dropdown_categories( array(
			'show_option_all'    => '',
			'show_option_none'   => __('Choose a Payment Type', FAKTURO_TEXT_DOMAIN ),
			'orderby'            => 'name', 
			'order'              => 'ASC',
			'show_count'         => 0,
			'hide_empty'         => 0, 
			'child_of'           => 0,
			'exclude'            => '',
			'echo'               => 0,
			'selected'           => $sale_data['client_data']['payment_type'],
			'hierarchical'       => 1, 
			'name'               => 'client_data[payment_type]',
			'class'              => 'form-no-clear',
			'id'				 => 'client_data_payment_type',
			'depth'              => 1,
			'tab_index'          => 0,
			'taxonomy'           => 'fktr_payment_types',
			'hide_if_empty'      => false
		));
		
		
		
		$echoHtml = '<table>
					<tbody>
						<tr>
							<td style="width:50%;" valign="top">
								
								<table style="width: 90%;">
									<tbody>
									<tr class="user-address-wrap">
										<th style="text-align:left;"><label for="client">'.__('Client', FAKTURO_TEXT_DOMAIN ).'</label></th>
										<td style="text-align:right;">
											'.$selectClients.'
										</td>		
									</tr>	
									
									<tr class="client_data"'.($show_client_data?'':' style="display:none;"').'>
										<th style="text-align:left;">'.__('Client ID', FAKTURO_TEXT_DOMAIN ).'</th>
										<td style="text-align:right;" id="client_data_id">
											'.$sale_data['client_id'].'
										</td>		
									</tr>
									<tr class="client_data"'.($show_client_data?'':' style="display:none;"').'>
										<th style="text-align:left;">'.__('Client name', FAKTURO_TEXT_DOMAIN ).'</th>
										<td style="text-align:right;" id="client_name">
											'.$sale_data['client_data']['name'].'
											<input type="hidden" name="client_data[name]" value="'.$sale_data['client_data']['name'].'" id="client_data_name"/>
										</td>		
									</tr>
									<tr class="client_data"'.($show_client_data?'':' style="display:none;"').'>
										<th style="text-align:left;">'.__('Client address', FAKTURO_TEXT_DOMAIN ).'</th>
										<td style="text-align:right;" id="client_address">
											'.$sale_data['client_data']['address'].'
											<input type="hidden" name="client_data[address]" value="'.$sale_data['client_data']['address'].'" id="client_data_address"/>
										</td>		
									</tr>
									<tr class="client_data"'.($show_client_data?'':' style="display:none;"').'>
										<th style="text-align:left;">'.__('City', FAKTURO_TEXT_DOMAIN ).'</th>
										<td style="text-align:right;" id="client_city">
											'.$sale_data['client_data']['city'].'
											<input type="hidden" name="client_data[city]" value="'.$sale_data['client_data']['city'].'" id="client_data_city"/>
										</td>		
									</tr>
									<tr class="client_data"'.($show_client_data?'':' style="display:none;"').'>
										<th style="text-align:left;">'.__('State', FAKTURO_TEXT_DOMAIN ).'</th>
										<td style="text-align:right;" id="client_state">
											'.$sale_data['client_data']['state']['name'].'
											<input type="hidden" name="client_data[state][id]" value="'.$sale_data['client_data']['state']['id'].'" id="client_data_state_id"/>
											<input type="hidden" name="client_data[state][name]" value="'.$sale_data['client_data']['state']['name'].'" id="client_data_state_name"/>
										</td>		
									</tr>
									<tr class="client_data"'.($show_client_data?'':' style="display:none;"').'>
										<th style="text-align:left;">'.__('Country', FAKTURO_TEXT_DOMAIN ).'</th>
										<td style="text-align:right;" id="client_country">
											'.$sale_data['client_data']['country']['name'].'
											<input type="hidden" name="client_data[country][id]" value="'.$sale_data['client_data']['country']['id'].'" id="client_data_country_id"/>
											<input type="hidden" name="client_data[country][name]" value="'.$sale_data['client_data']['country']['name'].'" id="client_data_country_name"/>
										</td>		
									</tr>
									<tr class="client_data"'.($show_client_data?'':' style="display:none;"').'>
										<th style="text-align:left;">'.__('Taxpayer ID', FAKTURO_TEXT_DOMAIN ).'</th>
										<td style="text-align:right;" id="client_taxpayer">
											'.$sale_data['client_data']['taxpayer'].'
											<input type="hidden" name="client_data[taxpayer]" value="'.$sale_data['client_data']['taxpayer'].'" id="client_data_taxpayer"/>
										</td>		
									</tr>
									<tr class="client_data"'.($show_client_data?'':' style="display:none;"').'>
										<th style="text-align:left;">'.__('Tax condition', FAKTURO_TEXT_DOMAIN ).'</th>
										<td style="text-align:right;" id="client_tax_condition">
											'.$selectTaxCondition.'
										</td>		
									</tr>
									<tr class="client_data"'.($show_client_data?'':' style="display:none;"').'>
										<th style="text-align:left;">'.__('Payment Type', FAKTURO_TEXT_DOMAIN ).'</th>
										<td style="text-align:right;" id="client_payment_type">
											'.$selectPaymentTypes.'
										</td>		
									</tr>
									<tr class="client_data"'.($show_client_data?'':' style="display:none;"').'>
										<th style="text-align:left;">'.__('Price scale', FAKTURO_TEXT_DOMAIN ).'</th>
										<td style="text-align:right;" id="client_price_scale">
											'.$sale_data['client_data']['price_scale']['name'].'
											<input type="hidden" name="client_data[price_scale][id]" value="'.$sale_data['client_data']['price_scale']['id'].'" id="client_data_price_scale_id"/>
											<input type="hidden" name="client_data[price_scale][name]" value="'.$sale_data['client_data']['price_scale']['name'].'" id="client_data_price_scale_name"/>
										</td>		
									</tr>
									<tr class="client_data"'.($show_client_data?'':' style="display:none;"').'>
										<th style="text-align:left;">'.__('Credit limit', FAKTURO_TEXT_DOMAIN ).'</th>
										<td style="text-align:right;" id="client_credit_limit">
											'.$sale_data['client_data']['credit_limit'].'
											<input type="hidden" name="client_data[credit_limit]" value="'.$sale_data['client_data']['credit_limit'].'" id="client_data_credit_limit"/>
										</td>		
									</tr>
									</tbody>
								</table>
							</td>
							<td>
								<table class="form-table">
									<tbody>
										<tr>
											<th><label for="invoice_type">'.__('Invoice Type', FAKTURO_TEXT_DOMAIN ).'</label></th>
											<td>
												'.$selectInvoiceTypes.'
											</td>		
										</tr>
										
										<tr>
											<th><label for="invoice_number">'.__('Invoice Number', FAKTURO_TEXT_DOMAIN ).'</label></th>
											<td>
												<input type="text" name="invoice_number" id="invoice_number" value="'.$sale_data['invoice_number'].'"/>
											</td>		
										</tr>
										
										<tr>
											<th><label for="date">'.__('Date', FAKTURO_TEXT_DOMAIN ).'</label></th>
											<td>
												<input type="text" name="date" id="date" value="'.$sale_data['date'].'"/>
											</td>		
										</tr>
										<tr>
											<th><label for="invoice_currency">'.__('Invoice Currency', FAKTURO_TEXT_DOMAIN ).'</label></th>
											<td>
												'.$selectCurrencies.'
											</td>		
										</tr>
										<tr>
											<th><label for="invoice_saleman">'.__('Salesman', FAKTURO_TEXT_DOMAIN ).'</label></th>
											<td>
												'.$select_sale_mans.'
											</td>		
										</tr>
									</tbody>
								</table>
								
							</td>		
						</tr>
			
				
			</tbody>
		</table>';
	
		$echoHtml = apply_filters('fktr_sale_client_box', $echoHtml);
		echo $echoHtml;
		do_action('add_fktr_sale_client_box', $echoHtml);
		
	}
	
	public static function client_box() {
		global $post;
		
	
		$sale_data = self::get_sale_data($post->ID);
		$setting_system = get_option('fakturo_system_options_group', false);
		
		$show_client_data = true;
		if ($sale_data['client_id'] < 1) {
			$show_client_data = false;
		}
		
		$selectClients = fakturo_get_select_post(array(
											'echo' => 0,
											'post_type' => 'fktr_client',
											'show_option_none' => __('Choose a Client', FAKTURO_TEXT_DOMAIN ),
											'name' => 'client_id',
											'id' => 'client_id',
											'class' => '',
											'selected' => $sale_data['client_id']
										));
										
										
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
			'selected'           => $sale_data['client_data']['tax_condition'],
			'hierarchical'       => 1, 
			'name'               => 'client_data[tax_condition]',
			'class'              => '',
			'id'				 => 'client_data_tax_condition',
			'depth'              => 1,
			'tab_index'          => 0,
			'taxonomy'           => 'fktr_tax_conditions',
			'hide_if_empty'      => false
		));
		
		$selectPaymentTypes = wp_dropdown_categories( array(
			'show_option_all'    => '',
			'show_option_none'   => __('Choose a Payment Type', FAKTURO_TEXT_DOMAIN ),
			'orderby'            => 'name', 
			'order'              => 'ASC',
			'show_count'         => 0,
			'hide_empty'         => 0, 
			'child_of'           => 0,
			'exclude'            => '',
			'echo'               => 0,
			'selected'           => $sale_data['client_data']['payment_type'],
			'hierarchical'       => 1, 
			'name'               => 'client_data[payment_type]',
			'class'              => 'form-no-clear',
			'id'				 => 'client_data_payment_type',
			'depth'              => 1,
			'tab_index'          => 0,
			'taxonomy'           => 'fktr_payment_types',
			'hide_if_empty'      => false
		));
		
		$echoHtml = '<table>
					<tbody>
			
			<tr class="user-address-wrap">
				<th style="text-align:left;"><label for="client">'.__('Client', FAKTURO_TEXT_DOMAIN ).'</label></th>
				<td style="text-align:right;">
					'.$selectClients.'
				</td>		
			</tr>
			<tr class="client_data"'.($show_client_data?'':' style="display:none;"').'>
				<th style="text-align:left;">'.__('Client ID', FAKTURO_TEXT_DOMAIN ).'</th>
				<td style="text-align:right;" id="client_data_id">
					'.$sale_data['client_id'].'
				</td>		
			</tr>
			<tr class="client_data"'.($show_client_data?'':' style="display:none;"').'>
				<th style="text-align:left;">'.__('Client name', FAKTURO_TEXT_DOMAIN ).'</th>
				<td style="text-align:right;" id="client_name">
					'.$sale_data['client_data']['name'].'
					<input type="hidden" name="client_data[name]" value="'.$sale_data['client_data']['name'].'" id="client_data_name"/>
				</td>		
			</tr>
			<tr class="client_data"'.($show_client_data?'':' style="display:none;"').'>
				<th style="text-align:left;">'.__('Client address', FAKTURO_TEXT_DOMAIN ).'</th>
				<td style="text-align:right;" id="client_address">
					'.$sale_data['client_data']['address'].'
					<input type="hidden" name="client_data[address]" value="'.$sale_data['client_data']['address'].'" id="client_data_address"/>
				</td>		
			</tr>
			<tr class="client_data"'.($show_client_data?'':' style="display:none;"').'>
				<th style="text-align:left;">'.__('City', FAKTURO_TEXT_DOMAIN ).'</th>
				<td style="text-align:right;" id="client_city">
					'.$sale_data['client_data']['city'].'
					<input type="hidden" name="client_data[city]" value="'.$sale_data['client_data']['city'].'" id="client_data_city"/>
				</td>		
			</tr>
			<tr class="client_data"'.($show_client_data?'':' style="display:none;"').'>
				<th style="text-align:left;">'.__('State', FAKTURO_TEXT_DOMAIN ).'</th>
				<td style="text-align:right;" id="client_state">
					'.$sale_data['client_data']['state']['name'].'
					<input type="hidden" name="client_data[state][id]" value="'.$sale_data['client_data']['state']['id'].'" id="client_data_state_id"/>
					<input type="hidden" name="client_data[state][name]" value="'.$sale_data['client_data']['state']['name'].'" id="client_data_state_name"/>
				</td>		
			</tr>
			<tr class="client_data"'.($show_client_data?'':' style="display:none;"').'>
				<th style="text-align:left;">'.__('Country', FAKTURO_TEXT_DOMAIN ).'</th>
				<td style="text-align:right;" id="client_country">
					'.$sale_data['client_data']['country']['name'].'
					<input type="hidden" name="client_data[country][id]" value="'.$sale_data['client_data']['country']['id'].'" id="client_data_country_id"/>
					<input type="hidden" name="client_data[country][name]" value="'.$sale_data['client_data']['country']['name'].'" id="client_data_country_name"/>
				</td>		
			</tr>
			<tr class="client_data"'.($show_client_data?'':' style="display:none;"').'>
				<th style="text-align:left;">'.__('Taxpayer ID', FAKTURO_TEXT_DOMAIN ).'</th>
				<td style="text-align:right;" id="client_taxpayer">
					'.$sale_data['client_data']['taxpayer'].'
					<input type="hidden" name="client_data[taxpayer]" value="'.$sale_data['client_data']['taxpayer'].'" id="client_data_taxpayer"/>
				</td>		
			</tr>
			<tr class="client_data"'.($show_client_data?'':' style="display:none;"').'>
				<th style="text-align:left;">'.__('Tax condition', FAKTURO_TEXT_DOMAIN ).'</th>
				<td style="text-align:right;" id="client_tax_condition">
					'.$selectTaxCondition.'
				</td>		
			</tr>
			<tr class="client_data"'.($show_client_data?'':' style="display:none;"').'>
				<th style="text-align:left;">'.__('Payment Type', FAKTURO_TEXT_DOMAIN ).'</th>
				<td style="text-align:right;" id="client_payment_type">
					'.$selectPaymentTypes.'
				</td>		
			</tr>
			<tr class="client_data"'.($show_client_data?'':' style="display:none;"').'>
				<th style="text-align:left;">'.__('Price scale', FAKTURO_TEXT_DOMAIN ).'</th>
				<td style="text-align:right;" id="client_price_scale">
					'.$sale_data['client_data']['price_scale']['name'].'
					<input type="hidden" name="client_data[price_scale][id]" value="'.$sale_data['client_data']['price_scale']['id'].'" id="client_data_price_scale_id"/>
					<input type="hidden" name="client_data[price_scale][name]" value="'.$sale_data['client_data']['price_scale']['name'].'" id="client_data_price_scale_name"/>
				</td>		
			</tr>
			<tr class="client_data"'.($show_client_data?'':' style="display:none;"').'>
				<th style="text-align:left;">'.__('Credit limit', FAKTURO_TEXT_DOMAIN ).'</th>
				<td style="text-align:right;" id="client_credit_limit">
					'.$sale_data['client_data']['credit_limit'].'
					<input type="hidden" name="client_data[credit_limit]" value="'.$sale_data['client_data']['credit_limit'].'" id="client_data_credit_limit"/>
				</td>		
			</tr>
			</tbody>
		</table>';
	
		$echoHtml = apply_filters('fktr_sale_client_box', $echoHtml);
		echo $echoHtml;
		do_action('add_fktr_sale_client_box', $echoHtml);
		
		
	}
	
	

	public static function clean_fields($fields) {
		
		if (!isset($fields['client_id'])) {
			$fields['client_id'] = 0;
		}
		if (!isset($fields['client_data']) || !is_array($fields['client_data'])) {
			$fields['client_data'] = array();
			$fields['client_data']['name'] = __('No name', FAKTURO_TEXT_DOMAIN );
			$fields['client_data']['address'] = __('No address', FAKTURO_TEXT_DOMAIN );
			$fields['client_data']['city'] = __('No city', FAKTURO_TEXT_DOMAIN );
			$fields['client_data']['state'] = array();
			$fields['client_data']['state']['id'] = 0;
			$fields['client_data']['state']['name'] = __('No state', FAKTURO_TEXT_DOMAIN );
			$fields['client_data']['country'] = array();
			$fields['client_data']['country']['id'] = 0;
			$fields['client_data']['country']['name'] = __('No country', FAKTURO_TEXT_DOMAIN );
			$fields['client_data']['taxpayer'] = __('No Taxpayer', FAKTURO_TEXT_DOMAIN );
			$fields['client_data']['tax_condition'] = 0;
			$fields['client_data']['payment_type'] = 0;
			$fields['client_data']['price_scale'] = array();
			$fields['client_data']['price_scale']['id'] = 0;
			$fields['client_data']['price_scale']['name'] = __('No price scale', FAKTURO_TEXT_DOMAIN );
			$fields['client_data']['credit_limit'] = 0;
			
			
		}
		if (!isset($fields['invoice_type'])) {
			$fields['invoice_type'] = 0;
		}
		if (!isset($fields['invoice_number'])) {
			$fields['invoice_number'] = '';
		}
		if (!isset($fields['date'])) {
			$fields['date'] = '';
		}
		if (!isset($fields['invoice_currency'])) {
			$fields['invoice_currency'] = 0;
		}
		if (!isset($fields['invoice_saleman'])) {
			$fields['invoice_saleman'] = 0;
		}

		return $fields;
	}
	public static function before_save($fields) {
		$setting_system = get_option('fakturo_system_options_group', false);
		
		
		return $fields;
	}
	public static function default_fields($new_status, $old_status, $post ) {
		
		if( $post->post_type == 'fktr_product' && $old_status == 'new'){		
			
			$fields = array();
			$fields['client_id'] = 0;
			$fields['client_data'] = array();
			$fields['client_data']['name'] = __('No name', FAKTURO_TEXT_DOMAIN );
			$fields['client_data']['address'] = __('No address', FAKTURO_TEXT_DOMAIN );
			$fields['client_data']['city'] = __('No city', FAKTURO_TEXT_DOMAIN );
			$fields['client_data']['state'] = array();
			$fields['client_data']['state']['id'] = 0;
			$fields['client_data']['state']['name'] = __('No state', FAKTURO_TEXT_DOMAIN );
			$fields['client_data']['country'] = array();
			$fields['client_data']['country']['id'] = 0;
			$fields['client_data']['country']['name'] = __('No country', FAKTURO_TEXT_DOMAIN );
			$fields['client_data']['taxpayer'] = __('No Taxpayer', FAKTURO_TEXT_DOMAIN );
			$fields['client_data']['tax_condition'] = 0;
			$fields['client_data']['payment_type'] = 0;
			$fields['client_data']['price_scale'] = array();
			$fields['client_data']['price_scale']['id'] = 0;
			$fields['client_data']['price_scale']['name'] = __('No price scale', FAKTURO_TEXT_DOMAIN );
			$fields['client_data']['credit_limit'] = 0;

			$fields['invoice_type'] = 0;
			$fields['invoice_number'] = '';
			$fields['date'] = '';
			$fields['invoice_currency'] = 0;
			$fields['invoice_saleman'] = 0;
			
			$fields = apply_filters('fktr_clean_sale_fields', $fields);

			foreach ( $fields as $field => $value ) {
				if ( !is_null( $value ) ) {
					
					$new = apply_filters( 'fktr_sale_metabox_save_' . $field, $value );  //filtra cada campo antes de grabar
					update_post_meta( $post->ID, $field, $new );
				}
			}
		}
	}
	public static function get_sale_data($sale_id) {
		$custom_field_keys = get_post_custom($sale_id);
		foreach ( $custom_field_keys as $key => $value ) {
			$custom_field_keys[$key] = maybe_unserialize($value[0]);
		}
		$custom_field_keys = apply_filters('fktr_clean_sale_fields', $custom_field_keys );
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
		
		$fields = apply_filters('fktr_clean_sale_fields',$_POST);
		$fields = apply_filters('fktr_sale_before_save',$fields);
		
		
		
		foreach ($fields as $field => $value ) {
			
			if ( !is_null( $value ) ) {
				$new = apply_filters('fktr_sale_metabox_save_' . $field, $value );  //filtra cada campo antes de grabar
				update_post_meta( $post_id, $field, $new );
				
			}
			
		}
		do_action( 'fktr_save_sale', $post_id, $post );
		
	}
	
	
} 

endif;

$fktrPostTypeSales = new fktrPostTypeSales();

?>