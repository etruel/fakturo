<?php


// Exit if accessed directly
if (!defined('ABSPATH'))  {
	exit;
}


if ( ! class_exists('fktrPostTypeClients') ) :
class fktrPostTypeClients {
	function __construct() {
		
		add_action( 'init', array('fktrPostTypeClients', 'setup'), 1 );
		add_action( 'fakturo_activation', array('fktrPostTypeClients', 'setup'), 1 );
		
		add_action('transition_post_status', array('fktrPostTypeClients', 'default_fields'), 10, 3);
		add_action('save_post', array('fktrPostTypeClients', 'save'), 10, 2 );
		
		add_action( 'admin_print_scripts-post-new.php', array('fktrPostTypeClients','scripts'), 11 );
		add_action( 'admin_print_scripts-post.php', array('fktrPostTypeClients','scripts'), 11 );
		
		add_action('admin_print_styles-post-new.php', array('fktrPostTypeClients','styles'));
		add_action('admin_print_styles-post.php', array('fktrPostTypeClients','styles'));
		
		add_filter('fktr_clean_client_fields', array('fktrPostTypeClients', 'clean_fields'), 10, 1);
		
		
		add_action('wp_ajax_get_client_states', array('fktrPostTypeClients', 'get_client_states'));
		
	}
	public static function setup() {
		$labels = array( 
			'name' => __( 'Clients', 'fakturo' ),
			'singular_name' => __( 'Client', 'fakturo' ),
			'add_new' => __( 'Add New', 'fakturo' ),
			'add_new_item' => __( 'Add New Client', 'fakturo' ),
			'edit_item' => __( 'Edit Client', 'fakturo' ),
			'new_item' => __( 'New Client', 'fakturo' ),
			'view_item' => __( 'View Client', 'fakturo' ),
			'search_items' => __( 'Search Clients', 'fakturo' ),
			'not_found' => __( 'No clients found', 'fakturo' ),
			'not_found_in_trash' => __( 'No clients found in Trash', 'fakturo' ),
			'parent_item_colon' => __( 'Parent Client:', 'fakturo' ),
			'menu_name' => __( 'Clients', 'fakturo' ),
		);
		$capabilities = array(
			'publish_post' => 'publish_fktr_client',
			'publish_posts' => 'publish_fktr_clients',
			'read_post' => 'read_fktr_client',
			'read_private_posts' => 'read_private_fktr_clients',
			'edit_post' => 'edit_fktr_client',
			'edit_published_posts' => 'edit_published_fktr_clients',
			'edit_private_posts' => 'edit_private_fktr_clients',
			'edit_posts' => 'edit_fktr_clients',
			'edit_others_posts' => 'edit_others_fktr_clients',
			'delete_post' => 'delete_fktr_client',
			'delete_posts' => 'delete_fktr_clients',
			'delete_published_posts' => 'delete_published_fktr_clients',
			'delete_private_posts' => 'delete_private_fktr_clients',
			'delete_others_posts' => 'delete_others_fktr_clients',
			);

		$args = array( 
			'labels' => $labels,
			'hierarchical' => false,
			'description' => 'Fakturo Clients',
			'supports' => array( 'title', 'thumbnail',/* 'custom-fields' */),
			'register_meta_box_cb' => array('fktrPostTypeClients','meta_boxes'),
			'public' => false,
			'show_ui' => true,
			'show_in_menu' => false,
			'menu_position' => 27,
			'menu_icon' => 'dashicons-id-alt',
			'show_in_nav_menus' => false,
			'publicly_queryable' => false,
			'exclude_from_search' => false,
			'has_archive' => false,
			'query_var' => true,
			'can_export' => true,
			'rewrite' => true,
			'capabilities' => $capabilities
		);

		register_post_type( 'fktr_client', $args );
		add_filter('enter_title_here', array('fktrPostTypeClients', 'name_placeholder'),10,2);
	}
	public static function name_placeholder( $title_placeholder , $post ) {
		if($post->post_type == 'fktr_client') {
			$title_placeholder = __('Enter Client name here', 'fakturo' );
			
		}
		return $title_placeholder;
	}
	public static function scripts() {
		global $post_type;
		if($post_type == 'fktr_client') {
			wp_enqueue_script('webcam', FAKTURO_PLUGIN_URL .'assets/js/webcamjs-master/webcam.min.js', array('jquery'), WPE_FAKTURO_VERSION, true);
			wp_enqueue_script( 'jquery-snapshot', FAKTURO_PLUGIN_URL . 'assets/js/snapshot.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			wp_enqueue_script( 'jquery-select2', FAKTURO_PLUGIN_URL . 'assets/js/jquery.select2.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			wp_enqueue_script( 'jquery-vsort', FAKTURO_PLUGIN_URL . 'assets/js/jquery.vSort.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			
			wp_enqueue_script( 'post-type-clients', FAKTURO_PLUGIN_URL . 'assets/js/post-type-clients.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			wp_localize_script('post-type-clients', 'client_object',
				array('ajax_url' => admin_url( 'admin-ajax.php' ),
					'loading_states_text' => __('Loading states...', 'fakturo' ),
					'update_client_contacts' => __('Update Client to save changes.', 'fakturo' ),
					'privider_delete_this_item' => __('Delete this item',  'fakturo'  )
				) );
			
			
		}
		
	}
	public static function styles() {
		global $post_type;
		if($post_type == 'fktr_client') {
			wp_enqueue_style('style-select2',FAKTURO_PLUGIN_URL .'assets/css/select2.min.css');	
			wp_enqueue_style('post-type-clients',FAKTURO_PLUGIN_URL .'assets/css/post-type-clients.css');	
			
		}
		
	}
	public static function meta_boxes() {
		
		add_meta_box('fakturo-active-box', __('Active client', 'fakturo' ), array('fktrPostTypeClients', 'active'), 'fktr_client', 'side', 'high');
		remove_meta_box('postimagediv', 'fktr_client', 'side' );
		add_meta_box('postimagediv', __('Client Image', 'fakturo' ), array('fktrPostTypeClients', 'thumbnail_box'), 'fktr_client', 'side', 'high');
		add_meta_box('fakturo-trade-box', __('Trader data', 'fakturo' ), array('fktrPostTypeClients', 'trade_box'),'fktr_client','normal', 'default' );
		add_meta_box('fakturo-data-box', __('Client data', 'fakturo' ), array('fktrPostTypeClients', 'data_box'),'fktr_client','normal', 'default' );
		add_meta_box('fakturo-options-box', __('Client Contacts', 'fakturo' ), array('fktrPostTypeClients', 'options_box'),'fktr_client','normal', 'default' );
		do_action('add_ftkr_client_meta_boxes');
	}
	public static function thumbnail_box() {
		global $post;
		$thumbnail_id = get_post_meta($post->ID, '_thumbnail_id', true );
		$echoHtml = '
			<div id="snapshot_container_wrapper">
				<div id="snapshot_container_buttons">
					<a id="snapshot_btn" href="javascript:showSnapshot()" class="nobutton">' . __( 'Take a snapshot', 'fakturo' ) . '</a>
					<div id="my_camera" style="display:none;">				
					</div>
					<img src="" id="snap_image" style="display:none;">
					<input type="hidden" name="webcam_image">
					<a href="javascript:take_snapshot()" class="button" id="take_snapshot" style="display:none;">'.__( 'Snapshot').'</a>
					<a href="javascript:reset_webcam()" class="button" id="snapshot_reset" style="display:none;">'.__( 'Reset').'</a>
					<a href="javascript:snapshot_cancel()" class="button" id="snapshot_cancel" style="display:none;">'.__( 'Cancel').'</a>
				</div>
			</div>
			<div class="featured-image-client">
							'._wp_post_thumbnail_html( $thumbnail_id, $post->ID ).'
				</div>
			';
			
		$echoHtml = apply_filters('fktr_client_thumbnail_box', $echoHtml);
		echo $echoHtml;
		do_action('add_fktr_client_thumbnail_box', $echoHtml);
		
	}
	public static function active() {
		global $post;
		
		$client_data = self::get_client_data($post->ID);
		$echoHtml = '<table class="form-table">
					<tbody>
					<tr class="tr_fktr">
						<th rowspan="2">
						<input id="active" type="checkbox" class="slidercheck" name="active" value="1" '.(($client_data['active'])?'checked="checked"':'').'><label for="active"><span class="ui"></span>'.__('Active', 'fakturo' ).'	</label>
						</th>
					</tr>
					</tbody>
				</table>';
		$echoHtml = apply_filters('fktr_client_active_box', $echoHtml);
		echo $echoHtml;
		do_action('add_fktr_client_active_box', $echoHtml);
		
	}
	
	public static function trade_box() {
		global $post;
		$client_data = self::get_client_data($post->ID);
		
		
		$selectPaymentTypes = wp_dropdown_categories( array(
			'show_option_all'    => '',
			'show_option_none'   => __('Choose a Payment Type', 'fakturo' ),
			'orderby'            => 'name', 
			'order'              => 'ASC',
			'show_count'         => 0,
			'hide_empty'         => 0, 
			'child_of'           => 0,
			'exclude'            => '',
			'echo'               => 0,
			'selected'           => $client_data['selected_payment_type'],
			'hierarchical'       => 1, 
			'name'               => 'selected_payment_type',
			'class'              => 'form-no-clear',
			'depth'              => 1,
			'tab_index'          => 0,
			'taxonomy'           => 'fktr_payment_types',
			'hide_if_empty'      => false
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
			'selected'           => $client_data['selected_bank_entity'],
			'hierarchical'       => 1, 
			'name'               => 'selected_bank_entity',
			'class'              => 'form-no-clear',
			'depth'              => 1,
			'tab_index'          => 0,
			'taxonomy'           => 'fktr_bank_entities',
			'hide_if_empty'      => false
		));
		
		
		
		$selectTaxCondition = wp_dropdown_categories( array(
			'show_option_all'    => '',
			'show_option_none'   => __('Choose a Tax Condition', 'fakturo' ),
			'orderby'            => 'name', 
			'order'              => 'ASC',
			'show_count'         => 0,
			'hide_empty'         => 0, 
			'child_of'           => 0,
			'exclude'            => '',
			'echo'               => 0,
			'selected'           => $client_data['selected_tax_condition'],
			'hierarchical'       => 1, 
			'name'               => 'selected_tax_condition',
			'class'              => 'form-no-clear',
			'depth'              => 1,
			'tab_index'          => 0,
			'taxonomy'           => 'fktr_tax_conditions',
			'hide_if_empty'      => false
		));
		
		$selectPriceScale = wp_dropdown_categories( array(
			'show_option_all'    => '',
			'show_option_none'   => __('Choose a Price Scale', 'fakturo' ),
			'orderby'            => 'name', 
			'order'              => 'ASC',
			'show_count'         => 0,
			'hide_empty'         => 0, 
			'child_of'           => 0,
			'exclude'            => '',
			'echo'               => 0,
			'selected'           => $client_data['selected_price_scale'],
			'hierarchical'       => 1, 
			'name'               => 'selected_price_scale',
			'class'              => 'form-no-clear',
			'depth'              => 1,
			'tab_index'          => 0,
			'taxonomy'           => 'fktr_price_scales',
			'hide_if_empty'      => false
		));
		
		$selectCurrency = wp_dropdown_categories( array(
			'show_option_all'    => '',
			'show_option_none'   => __('Choose a Currency', 'fakturo' ),
			'orderby'            => 'name', 
			'order'              => 'ASC',
			'show_count'         => 0,
			'hide_empty'         => 0, 
			'child_of'           => 0,
			'exclude'            => '',
			'echo'               => 0,
			'selected'           => $client_data['selected_currency'],
			'hierarchical'       => 1, 
			'name'               => 'selected_currency',
			'class'              => 'form-no-clear',
			'depth'              => 1,
			'tab_index'          => 0,
			'taxonomy'           => 'fktr_currencies',
			'hide_if_empty'      => false
		));
	
		
		
		$echoHtml = '<table class="form-table">
					<tbody>
					
					<tr class="tr_fktr">
						<th><label for="taxpayer">'.__('Taxpayer ID', 'fakturo' ).'	</label></th>
						<td>
							<input id="taxpayer" type="text" name="taxpayer" value="'.$client_data['taxpayer'].'" class="regular-text">
							<span id="cuit_validation"></span>
							<div style="font-size:0.85em;" id="cuit_validation_note">'.__("Cuit number's validation only. Check www.afip.gov.ar", 'fakturo' ).'</div>
						</td>
					</tr>
					<tr class="tr_fktr">
						<th><label for="selected_payment_type">'.__('Payment Type', 'fakturo' ).'	</label></th>
						<td>'.$selectPaymentTypes.'</td>
					</tr>
					<tr class="tr_fktr">
						<th><label for="selected_bank_entity">'.__('Bank Entity', 'fakturo' ).'	</label></th>
						<td>'.$selectBankEntities.'</td>
					</tr>
					<tr class="tr_fktr">
						<th><label for="bank_account">'.__('Bank Account', 'fakturo' ) .'	</label></th>
						<td><input id="bank_account" type="text" name="bank_account" value="'.$client_data['bank_account'].'" class="regular-text"></td>
					</tr>
					<tr class="tr_fktr">
						<th><label for="selected_tax_condition">'.__('Tax Condition', 'fakturo' ).'	</label></th>
						<td>'.$selectTaxCondition.'</td>
					</tr>
					<tr class="tr_fktr">
						<th><label for="selected_price_scale">'.__('Price Scale', 'fakturo' ).'	</label></th>
						<td>'.$selectPriceScale.'</td>
					</tr>
					<tr class="tr_fktr">
						<th><label for="selected_currency">'.__('Currency', 'fakturo' ).'	</label></th>
						<td>'.$selectCurrency.'</td>
					</tr>
					
					<tr class="tr_fktr">
						<th><label for="credit_limit">'.__('Credit Limit', 'fakturo' ).'	</label></th>
						<td><input id="credit_limit" type="number" name="credit_limit" value="'.$client_data['credit_limit'].'" class="small-text"/></td>
					</tr>
					
					<tr class="tr_fktr">
						<th><label for="credit_limit_interval">'.__('Credit Limit Interval', 'fakturo' ).'	</label></th>
						<td><input id="credit_limit_interval" type="number" name="credit_limit_interval" value="'.$client_data['credit_limit_interval'].'" class="small-text"/></td>
					</tr>
					
					</tbody>
				</table>';
		$echoHtml = apply_filters('fktr_client_trade_box', $echoHtml);
		echo $echoHtml;
		do_action('add_fktr_client_trade_box', $echoHtml);
		
	}
	public static function data_box() {
		global $post;
		$client_data = self::get_client_data($post->ID);
		$selectCountry = wp_dropdown_categories( array(
			'show_option_all'    => '',
			'show_option_none'   => __('Choose a country', 'fakturo' ),
			'orderby'            => 'name', 
			'order'              => 'ASC',
			'show_count'         => 0,
			'hide_empty'         => 0, 
			'child_of'           => 0,
			'exclude'            => '',
			'echo'               => 0,
			'selected'           => $client_data['selected_country'],
			'hierarchical'       => 1, 
			'name'               => 'selected_country',
			'class'              => 'form-no-clear',
			'depth'              => 1,
			'tab_index'          => 0,
			'taxonomy'           => 'fktr_countries',
			'hide_if_empty'      => false
		));
		
		
		$selectState = wp_dropdown_categories( array(
			'show_option_all'    => '',
			'show_option_none'   => __('Choose a state', 'fakturo' ),
			'orderby'            => 'name', 
			'order'              => 'ASC',
			'show_count'         => 0,
			'hide_empty'         => 0, 
			'child_of'           => $client_data['selected_country'],
			'exclude'            => '',
			'echo'               => 0,
			'selected'           => $client_data['selected_state'],
			'hierarchical'       => 1, 
			'name'               => 'selected_state',
			'class'              => 'form-no-clear',
			'depth'              => 1,
			'tab_index'          => 0,
			'taxonomy'           => 'fktr_countries',
			'hide_if_empty'      => false
		));

		$selectEmptyState = wp_dropdown_categories( array(
			'show_option_all'    => '',
			'show_option_none'   => __('Choose a state', 'fakturo' ),
			'orderby'            => 'name', 
			'order'              => 'ASC',
			'show_count'         => 0,
			'hide_empty'         => 0, 
			'child_of'           => -99,
			'exclude'            => '',
			'echo'               => 0,
			'selected'           => $client_data['selected_state'],
			'hierarchical'       => 1, 
			'name'               => 'selected_state',
			'class'              => 'form-no-clear',
			'depth'              => 1,
			'tab_index'          => 0,
			'taxonomy'           => 'fktr_countries',
			'hide_if_empty'      => false
		));
		
		if ($client_data['selected_country'] == 0 || strlen($selectState) < strlen($selectEmptyState)+1) {
			
			$selectState = '<select name="selected_state" id="selected_state">
								<option value="0">'. __('Choose a country before', 'fakturo' ) .'</option>
							</select>';
		}
		$echoHtml = '<table class="form-table">
					<tbody>
					
					<tr class="user-address-wrap">
						<th><label for="address">'.__('Address', 'fakturo' ).'	</label></th>
						<td><input type="text" name="address" id="address" value="'.$client_data['address'].'" class="regular-text"></td>
					</tr>
					<tr class="tr_fktr">
						<th><label for="country">'. __('Country', 'fakturo' ).'	</label></th>
						<td>'.$selectCountry.'</td>
					</tr>
					<tr class="tr_fktr">
						<th><label for="states">'. __('States', 'fakturo' ) .'	</label></th>
						<td id="td_select_state">
							'.$selectState.'
						</td>
					</tr>
					<tr class="tr_fktr">
						<th><label for="city">'.__('City', 'fakturo' ) .'	</label></th>
						<td><input id="city" type="text" name="city" value="'.$client_data['city'].'" class="regular-text"></td>
					</tr>
					<tr class="tr_fktr">
						<th><label for="postcode">'.__('Postcode', 'fakturo' ) .'	</label></th>
						<td><input id="postcode" type="text" name="postcode" value="'.$client_data['postcode'].'" class="regular-text"></td>
					</tr>
					
					<tr class="tr_fktr">
						<th><label for="phone">'.__('Phone', 'fakturo' ) .'	</label></th>
						<td><input id="phone" type="text" name="phone" value="'.$client_data['phone'].'" class="regular-text"></td>
					</tr>
					<tr class="tr_fktr">
						<th><label for="cell_phone">'.__('Cell phone', 'fakturo' ) .'	</label></th>
						<td><input id="cell_phone" type="text" name="cell_phone" value="'.$client_data['cell_phone'].'" class="regular-text"></td>
					</tr>
					<tr class="user-email-wrap">
						<th><label for="email">'.__('E-mail', 'fakturo' ) .'</label></th>
						<td><input type="email" name="email" id="email" value="'.$client_data['email'].'" class="regular-text ltr"></td>
					</tr>
					<tr class="tr_fktr">
						<th><label for="facebook_url">'.__('Facebook URL', 'fakturo' ).'</label></th>
						<td><input id="facebook_url" type="text" name="facebook_url" value="'.$client_data['facebook_url'].'" class="regular-text"></td>
					</tr>
					<tr class="tr_fktr">
						<th><label for="web">'.__('Web', 'fakturo' ).'</label></th>
						<td><input id="web" type="text" name="web" value="'.$client_data['web'].'" class="regular-text"></td>
					</tr>
					
					</tbody>
				</table>';
		$echoHtml = apply_filters('fktr_client_data_box', $echoHtml);
		echo $echoHtml;
		do_action('add_fktr_client_data_box', $echoHtml);
		
	}
	
	public static function options_box() {
		global $post;
		$client_data = self::get_client_data($post->ID);
		$user_contacts = $client_data;
		
		
		$echoContacts = '';
		$a = 0;
		foreach ($user_contacts['uc_description'] as $i => $desc) {
	
			$lastitem = $i==count(@$user_contacts['uc_description']); 
			$echoContacts .= '<div id="uc_ID'.$i.'" class="sortitem '.((($i % 2) == 0) ? 'bw' :  'lightblue').' '.(($lastitem)?'uc_new_field':'').'" '.(($lastitem)?'style="display:none;"':'').' > 
						<div class="sorthandle"> </div> <!-- sort handle -->
						<div class="uc_column" id="">
							<input name="uc_description[]" type="text" value="'.stripslashes(@$user_contacts['uc_description'][$i]).'" class="large-text"/>
						</div>
						<div class="uc_column" id="">
							<input name="uc_phone[]" type="text" value="'.stripslashes(@$user_contacts['uc_phone'][$i]).'" class="large-text"/>
						</div>
						<div class="uc_column" id="">
							<input name="uc_email[]" type="text" value="'.stripslashes(@$user_contacts['uc_email'][$i]).'" class="large-text"/>
						</div>
						<div class="uc_column" id="">
							<input name="uc_position[]" type="text" value="'.stripslashes(@$user_contacts['uc_position'][$i]).'" class="large-text"/>
						</div>
						<div class="uc_column" id="">
							<input name="uc_address[]" type="text" value="'.stripslashes(@$user_contacts['uc_address'][$i]).'" class="large-text"/>
						</div>
						<div class="" id="uc_actions">
							<label title="'. __('Delete this item',  'fakturo'  ).'" data-id="'.$i.'" class="delete"></label>
						</div>
					</div>';
			$a=$i;
		}
		
		
		$echoHtml = '<table class="form-table">
					<tbody>
					<tr class="user-display-name-wrap">
						<td>
							<div class="uc_header">
							<div class="uc_column">'.__('Description', 'fakturo'  ).'</div>
							<div class="uc_column">'.__('Phone', 'fakturo'  ) .'</div>
							<div class="uc_column">'. __('Email', 'fakturo'  ) .'</div>
							<div class="uc_column">'. __('Position', 'fakturo'  ) .'</div>
							<div class="uc_column">'. __('Address', 'fakturo'  ) .'</div>
							</div>
							<br />
			
							<div id="user_contacts"> 
								'.$echoContacts.'
							</div>
							<input id="ucfield_max" value="'.$a.'" type="hidden" name="ucfield_max"/>
							<div id="paging-box">		  
								<a href="#" class="button-primary add" id="addmoreuc" style="font-weight: bold; text-decoration: none;"> '.__('Add User Contact', 'fakturo'  ).'</a>
								<label id="msgdrag"></label>
							</div>
						</td>
						</tr>
					</tbody>
					</table>';
		$echoHtml = apply_filters('fktr_client_options_box', $echoHtml);
		echo $echoHtml;
		do_action('add_fktr_client_options_box', $echoHtml);
		
	}
	
	
	public static function get_client_states() {
		if (!is_numeric($_POST['country_id'])) {
			$_POST['country_id']= 0;
		}
		
		$selectState = wp_dropdown_categories( array(
			'show_option_all'    => '',
			'show_option_none'   => __('Choose a state', 'fakturo' ),
			'orderby'            => 'name', 
			'order'              => 'ASC',
			'show_count'         => 0,
			'hide_empty'         => 0, 
			'child_of'           => $_POST['country_id'],
			'exclude'            => '',
			'echo'               => 0,
			'selected'           => -1,
			'hierarchical'       => 1, 
			'name'               => 'selected_state',
			'class'              => 'form-no-clear',
			'depth'              => 1,
			'tab_index'          => 0,
			'taxonomy'           => 'fktr_countries',
			'hide_if_empty'      => false
		));
		if ($_POST['country_id'] < 1 ) {
			
			$selectState = '<select name="selected_state" id="selected_state">
								<option value="0">'. __('Choose a country before', 'fakturo' ) .'</option>
							</select>';
		}
		wp_die($selectState);
		
		
	}
	
	public static function clean_fields($fields) {
		
		if (!isset($fields['active'])) {
			$fields['active'] = 1;
		}
		if (!isset($fields['balance'])) {
			$fields['balance'] = 0;
		}
		
		if (!isset($fields['taxpayer'])) {
			$fields['taxpayer'] = '';
		}
		if (!isset($fields['selected_payment_type'])) {
			$fields['selected_payment_type'] = 0;
		}
		if (!isset($fields['selected_bank_entity'])) {
			$fields['selected_bank_entity'] = 0;
		}
		if (!isset($fields['bank_account'])) {
			$fields['bank_account'] = '';
		}
		if (!isset($fields['selected_tax_condition'])) {
			$fields['selected_tax_condition'] = 0;
		}
		if (!isset($fields['selected_price_scale'])) {
			$fields['selected_price_scale'] = 0;
		}
		if (!isset($fields['selected_currency'])) {
			$fields['selected_currency'] = 0;
		}
		if (!isset($fields['credit_limit'])) {
			$fields['credit_limit'] = 0;
		}
		if (!isset($fields['credit_limit_interval'])) {
			$fields['credit_limit_interval'] = 0;
		}
		
		
		
		if (!isset($fields['address'])) {
			$fields['address'] = '';
		}
		if (!isset($fields['selected_country'])) {
			$fields['selected_country'] = 0;
		}
		if (!isset($fields['selected_state'])) {
			$fields['selected_state'] = 0;
		}
		if (!isset($fields['city'])) {
			$fields['city'] = '';
		}
		if (!isset($fields['postcode'])) {
			$fields['postcode'] = '';
		}
		if (!isset($fields['phone'])) {
			$fields['phone'] = '';
		}
		if (!isset($fields['cell_phone'])) {
			$fields['cell_phone'] = '';
		}
		if (!isset($fields['email'])) {
			$fields['email'] = '';
		}
		if (!isset($fields['facebook_url'])) {
			$fields['facebook_url'] = '';
		}
		if (!isset($fields['web'])) {
			$fields['web'] = '';
		}
	


		
		
		if (!isset($fields['uc_description']) || !is_array($fields['uc_description'])) {
			$fields['uc_description'] = array();
		}


		
		return $fields;
	}
	public static function default_fields($new_status, $old_status, $post ) {
		
		if( $post->post_type == 'fktr_client' && $old_status == 'new'){		
			
			$fields = array();
			$fields['active'] = 1;
			
			$fields['taxpayer'] = '';
			$fields['selected_payment_type'] = 0;
			$fields['selected_bank_entity'] = 0;
			$fields['bank_account'] = '';
			$fields['selected_tax_condition'] = 0;
			$fields['selected_price_scale'] = 0;
			$fields['selected_currency'] = 0;
			$fields['credit_limit'] = 0;
			$fields['credit_limit_interval'] = 0;
			
			
			$fields['address'] = '';
			$fields['selected_country'] = 0;
			$fields['selected_state'] = 0;
			$fields['city'] = '';
			$fields['postcode'] = '';
			$fields['phone'] = '';
			$fields['cell_phone'] = '';
			$fields['email'] = '';
			$fields['facebook_url'] = '';
			$fields['web'] = '';
			
			
			
			$fields['uc_description'] = array();
			$fields = apply_filters('fktr_clean_client_fields', $fields);

			foreach ( $fields as $field => $value ) {
				if ( !is_null( $value ) ) {
					
					$new = apply_filters( 'fktr_client_metabox_save_' . $field, $value );  //filtra cada campo antes de grabar
					update_post_meta( $post->ID, $field, $new );
				}
			}
		}
	}
	public static function remove_balance($client_id, $value) {
		
		$data_client = self::get_client_data($client_id);
		if (!isset($data_client['balance'])) {
			$data_client['balance'] = 0;
		}
		$data_client['balance'] = $data_client['balance']-$value;
		$new = apply_filters('fktr_client_metabox_save_balance', $data_client['balance']);
		update_post_meta($client_id, 'balance', $new);
		return true;
	}
	public static function add_balance($client_id, $value) {
		
		$data_client = self::get_client_data($client_id);
		if (!isset($data_client['balance'])) {
			$data_client['balance'] = 0;
		}
		$data_client['balance'] = $data_client['balance']+$value;
		$new = apply_filters('fktr_client_metabox_save_balance', $data_client['balance']);
		update_post_meta($client_id, 'balance', $new);
		return true;
	}
	public static function get_client_data($client_id) {
		$custom_field_keys = get_post_custom($client_id);
		foreach ( $custom_field_keys as $key => $value ) {
			$custom_field_keys[$key] = maybe_unserialize($value[0]);
		}
		$custom_field_keys = apply_filters('fktr_clean_client_fields', $custom_field_keys );
		return $custom_field_keys;
	}
	public static function save($post_id, $post) {
		
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX') && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) {
			return false;
		}

		if ( isset( $post->post_type ) && $post->post_type == 'revision' || $post->post_type != 'fktr_client') {
			return false;
		}

		if ( ! current_user_can( 'edit_fakturo_settings', $post_id ) ) {
			return false;
		}
		if ( ( defined( 'FKTR_STOP_PROPAGATION') && FKTR_STOP_PROPAGATION ) ) {
			return false;
		}
		$fields = apply_filters('fktr_clean_client_fields',$_POST);
		$fields = apply_filters('fktr_client_before_save',$fields);
		
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
				$new = apply_filters('fktr_client_thumbnail_id', $attachment_id);
				add_post_meta($post_id, '_thumbnail_id', $new);
				unset($fields['webcam_image']);
			}
		}
		
		
		
		foreach ($fields as $field => $value ) {
			
			if ( !is_null( $value ) ) {
				$new = apply_filters('fktr_client_metabox_save_' . $field, $value );  //filtra cada campo antes de grabar
				update_post_meta( $post_id, $field, $new );
				
			}
			
		}
		do_action( 'fktr_save_client', $post_id, $post );
		
	}
	
} 

endif;

$fktrPostTypeClients = new fktrPostTypeClients();

?>