<?php


// Exit if accessed directly
if (!defined('ABSPATH'))  {
	exit;
}


if ( ! class_exists('fktrPostTypeReceipts') ) :
class fktrPostTypeReceipts {
	function __construct() {
		
		add_action( 'init', array(__CLASS__, 'setup'), 1 );
		add_action( 'activated_plugin', array(__CLASS__, 'setup'), 1 );
		
		add_action('transition_post_status', array(__CLASS__, 'default_fields'), 10, 3);
		add_action('save_post', array(__CLASS__, 'save'), 99, 2 );
		
		add_action( 'admin_print_scripts-post-new.php', array(__CLASS__,'scripts'), 11 );
		add_action( 'admin_print_scripts-post.php', array(__CLASS__,'scripts'), 11 );
		
		add_action('admin_print_styles-post-new.php', array(__CLASS__,'styles'));
		add_action('admin_print_styles-post.php', array(__CLASS__,'styles'));
		
		add_action('get_delete_post_link', array(__CLASS__, 'set_delete_post_link'), 10, 3);
		
		add_filter('fktr_clean_receipt_fields', array(__CLASS__, 'clean_fields'), 10, 1);
		add_filter('fktr_receipt_before_save', array(__CLASS__, 'before_save'), 10, 1);

		add_action('wp_ajax_receipt_client_data', array(__CLASS__, 'get_client_data'));
		
		add_filter('post_updated_messages', array(__CLASS__, 'updated_messages') );
		add_filter('attribute_escape', array(__CLASS__, 'change_button_texts'), 10, 2);
		add_action('before_delete_post', array(__CLASS__, 'before_delete'), 10, 1);
		
	}
	
	public static function change_button_texts($safe_text, $text ){
		global $post, $current_screen, $screen;
		
		if (isset($post) && $post->post_type == 'fktr_receipt') {
			switch( $safe_text ) {
				case 'Save Draft';
					$safe_text = __('Save as Pendient', FAKTURO_TEXT_DOMAIN );
					break;

				case 'Publish';
					$safe_text = __('Finish Invoice', FAKTURO_TEXT_DOMAIN );
					break;

				default:
					break;
			}
		}
		return $safe_text;
	}
	
	public static function set_delete_post_link( $delink, $post_id, $force_delete){
		global $post;
		if ($post->post_type == 'fktr_receipt') {
			$setting_system = get_option('fakturo_system_options_group', false);
			if ($post->post_status == 'publish') { // don't allow delete
				$delink = "javascript:alert('".__('Cannot delete finished Invoices', FAKTURO_TEXT_DOMAIN )."');";
			}
			if ($setting_system['use_stock_product'] && $post->post_status == 'draft') {
				//$delink = str_replace('trash', 'delete', $delink);
				$action = 'delete';
				$post_type_object = get_post_type_object( $post->post_type );
				$delete_link = add_query_arg( 'action', $action, admin_url( sprintf( $post_type_object->_edit_link, $post_id ) ) );
				$delete_link = wp_nonce_url( $delete_link, "$action-post_{$post->ID}" );
				
				$delink = "$delete_link\" onclick=\"return confirm('".__('Delete this item permanently ?', FAKTURO_TEXT_DOMAIN )."')";
			}
		}
		return $delink;
	}
	
	
	public static function setup() {
		
		$labels = array( 
			'name' => __( 'Receipts', FAKTURO_TEXT_DOMAIN ),
			'singular_name' => __( 'Receipt', FAKTURO_TEXT_DOMAIN ),
			'add_new' => __( 'Add New', FAKTURO_TEXT_DOMAIN ),
			'add_new_item' => __( 'Add New Receipt', FAKTURO_TEXT_DOMAIN ),
			'edit_item' => __( 'Edit Receipt', FAKTURO_TEXT_DOMAIN ),
			'new_item' => __( 'New Receipt', FAKTURO_TEXT_DOMAIN ),
			'view_item' => __( 'View Receipt', FAKTURO_TEXT_DOMAIN ),
			'search_items' => __( 'Search Receipts', FAKTURO_TEXT_DOMAIN ),
			'not_found' => __( 'No Receipts found', FAKTURO_TEXT_DOMAIN ),
			'not_found_in_trash' => __( 'No Receipts found in Trash', FAKTURO_TEXT_DOMAIN ),
			'parent_item_colon' => __( 'Parent Receipt:', FAKTURO_TEXT_DOMAIN ),
			'menu_name' => __( 'Receipts', FAKTURO_TEXT_DOMAIN ),
		);
		$capabilities = array(
			'publish_post' => 'publish_fktr_receipt',
			'publish_posts' => 'publish_fktr_receipts',
			'read_post' => 'read_fktr_receipt',
			'read_private_posts' => 'read_private_fktr_receipts',
			'edit_post' => 'edit_fktr_receipt',
			'edit_published_posts' => 'edit_published_fktr_receipts',
			'edit_private_posts' => 'edit_private_fktr_receipts',
			'edit_posts' => 'edit_fktr_receipts',
			'edit_others_posts' => 'edit_others_fktr_receipts',
			'delete_post' => 'delete_fktr_receipt',
			'delete_posts' => 'delete_fktr_receipts',
			'delete_published_posts' => 'delete_published_fktr_receipts',
			'delete_private_posts' => 'delete_private_fktr_receipts',
			'delete_others_posts' => 'delete_others_fktr_receipts',
		);

		$args = array( 
			'labels' => $labels,
			'hierarchical' => false,
			'description' => 'Fakturo Receipts',
			'supports' => array( 'title',/* 'custom-fields' */),
			'register_meta_box_cb' => array(__CLASS__,'meta_boxes'),
			'public' => true,
			'show_ui' => true,
			'show_in_menu' => false, 
			'menu_position' => 26,
			'menu_icon' => 'dashicons-tickets', 
			'show_in_nav_menus' => false,
			'publicly_queryable' => false,
			'exclude_from_search' => false,
			'has_archive' => false,
			'query_var' => true,
			'can_export' => true,
			'rewrite' => true,
			'capabilities' => $capabilities
		);

		register_post_type( 'fktr_receipt', $args );

		
		add_filter('enter_title_here', array(__CLASS__, 'name_placeholder'),10,2);
		
		
		
	}
	

	public static function updated_messages( $messages ) {
		global $post, $post_ID;
		$messages['fktr_receipt'] = array(
			 0 => '', 
			 1 => __('Receipt updated.', FAKTURO_TEXT_DOMAIN ),
			 2 => '',
			 3 => '',
			 4 => __( 'Receipt updated.', FAKTURO_TEXT_DOMAIN ),
			 5 => '',
			 6 => __('Receipt published.', FAKTURO_TEXT_DOMAIN ),
			 7 => __('Receipt saved.', FAKTURO_TEXT_DOMAIN ),
			 8 => __('Receipt submitted.', FAKTURO_TEXT_DOMAIN ),
			 9 => sprintf(__('Receipt scheduled for: <strong>%1$s</strong>.', FAKTURO_TEXT_DOMAIN ), date_i18n( __( 'M j, Y @ G:i', FAKTURO_TEXT_DOMAIN ), strtotime( $post->post_date ) )),
			10 => __('Pending updated.', FAKTURO_TEXT_DOMAIN ),
		);
		return $messages;
	}
	public static function name_placeholder( $title_placeholder , $post ) {
		if($post->post_type == 'fktr_receipt') {
			$title_placeholder = __('Your invoice number', FAKTURO_TEXT_DOMAIN );
		}
		return $title_placeholder;
	}
	public static function get_client_data() {
		global $wpdb;
		if (!is_numeric($_POST['client_id'])) {
			$_POST['client_id'] = 0;
		}
		$client_data = fktrPostTypeClients::get_client_data($_POST['client_id']);
		$client_data['invoice_sales'] = array();
		$sqlInvoices = sprintf("SELECT p.ID FROM {$wpdb->postmeta} pm
				 LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
				 WHERE pm.meta_key = 'client_id'
				 AND pm.meta_value = '%s'
				 AND p.post_status = 'publish'
				 AND p.post_type = 'fktr_sale'
			 ", $_POST['client_id']);
		$r = $wpdb->get_results($sqlInvoices);
		if (!empty($r)) {
			foreach ($r as $inv) {
				$client_data['invoice_sales'][] = fktrPostTypeSales::get_sale_data($inv->ID);
			}
		}
		echo json_encode($client_data);
		wp_die();
	}
	public static function styles() {
		global $post_type;
		if($post_type == 'fktr_receipt') {
			wp_enqueue_style('style-select2',FAKTURO_PLUGIN_URL .'assets/css/select2.min.css');	
			wp_enqueue_style('post-type-receipts',FAKTURO_PLUGIN_URL .'assets/css/post-type-receipts.css');	
			wp_enqueue_style('style-datetimepicker',FAKTURO_PLUGIN_URL .'assets/css/jquery.datetimepicker.css');	
		}
	}
	public static function scripts() {
		global $post_type, $post, $wp_locale, $locale;
		if($post_type == 'fktr_receipt') {
			wp_dequeue_script( 'autosave' );
			wp_enqueue_script( 'jquery-select2', FAKTURO_PLUGIN_URL . 'assets/js/jquery.select2.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			wp_enqueue_script( 'jquery-datetimepicker', FAKTURO_PLUGIN_URL . 'assets/js/jquery.datetimepicker.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			wp_enqueue_script( 'jquery-vsort', FAKTURO_PLUGIN_URL . 'assets/js/jquery.vSort.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			wp_enqueue_script( 'jquery-mask', FAKTURO_PLUGIN_URL . 'assets/js/jquery.mask.min.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			wp_enqueue_script( 'post-type-receipts', FAKTURO_PLUGIN_URL . 'assets/js/post-type-receipts.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			$currencies = get_fakturo_terms(array(
							'taxonomy' => 'fktr_currencies',
							'hide_empty' => false,
				));
			$selectBankEntities = wp_dropdown_categories( array(
				'show_option_all'    => '',
				'show_option_none'   => __('Choose a Bank Entity', FAKTURO_TEXT_DOMAIN ),
				'orderby'            => 'name', 
				'order'              => 'ASC',
				'show_count'         => 0,
				'hide_empty'         => 0, 
				'child_of'           => 0,
				'exclude'            => '',
				'echo'               => 0,
				'selected'           => 0,
				'hierarchical'       => 1, 
				'name'               => 'popup_check_banks',
				'class'              => '',
				'id'				 => 'popup_check_banks',
				'depth'              => 1,
				'tab_index'          => 0,
				'taxonomy'           => 'fktr_bank_entities',
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
				'selected'           => 0,
				'hierarchical'       => 1, 
				'name'               => 'popup_check_currencies',
				'class'              => '',
				'id'				 => 'popup_check_currencies',
				'depth'              => 1,
				'tab_index'          => 0,
				'taxonomy'           => 'fktr_currencies',
				'hide_if_empty'      => false
			));
			
			
			$setting_system = get_option('fakturo_system_options_group', false);
			
			$objectL10n = (object)array(
				'lang'			=> substr($locale, 0, 2),
				'UTC'			=> get_option( 'gmt_offset' ),
				'timeFormat'    => get_option( 'time_format' ),
				'dateFormat'    => self::date_format_php_to_js( $setting_system['dateformat'] ),
				'printFormat'   => self::date_format_php_to_js( $setting_system['dateformat'] ),
				'firstDay'      => get_option( 'start_of_week' ),
			);		
			
			wp_localize_script('post-type-receipts', 'receipts_object',
				array('ajax_url' => admin_url( 'admin-ajax.php' ),
					'thousand' => $setting_system['thousand'],
					'decimal' => $setting_system['decimal'],
					'decimal_numbers' => $setting_system['decimal_numbers'],
					'currency_position' => $setting_system['currency_position'],
					'default_currency' => $setting_system['currency'],
					'current_date' => date_i18n($setting_system['dateformat'],  time()),
					
					'datetimepicker' => $objectL10n,
					
					
					'txt_loading' => __('Loading', FAKTURO_TEXT_DOMAIN ),
					'txt_cancel' => __('Cancel', FAKTURO_TEXT_DOMAIN ),
					
					'currencies' => $currencies,
					
					'select_bank_entities' => $selectBankEntities,
					'select_bank_currencies' => $selectCurrencies,
				)
			);
				
			
		
		}
		
	}
	
	public static function meta_boxes() {
		
		add_meta_box('fakturo-receipt-box', __('Receipt data', FAKTURO_TEXT_DOMAIN ), array(__CLASS__, 'receipt_box'),'fktr_receipt','normal', 'high' );
		do_action('add_ftkr_receipt_meta_boxes');
	}
	public static function receipt_box() {
		global $post;
		$receipt_data = self::get_receipt_data($post->ID);
		$selectClients = fakturo_get_select_post(array(
											'echo' => 0,
											'post_type' => 'fktr_client',
											'show_option_none' => __('Choose a Client', FAKTURO_TEXT_DOMAIN ),
											'name' => 'client_id',
											'id' => 'client_id',
											'class' => '',
											'selected' => $receipt_data['client_id']
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
				'selected'           => $receipt_data['payment_type_id'],
				'hierarchical'       => 1, 
				'name'               => 'payment_type_id',
				'class'              => 'form-no-clear',
				'id'				 => 'payment_type_id',
				'depth'              => 1,
				'tab_index'          => 0,
				'taxonomy'           => 'fktr_payment_types',
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
				'selected'           => $receipt_data['currency_id'],
				'hierarchical'       => 1, 
				'name'               => 'currency_id',
				'class'              => 'form-no-clear',
				'id'				 => 'currency_id',
				'depth'              => 1,
				'tab_index'          => 0,
				'taxonomy'           => 'fktr_currencies',
				'hide_if_empty'      => false
			));

		$echoHtml = '
			<table class="form-table">
				<tbody>

					<tr class="user-facebook-wrap">
						<th><label for="client_id">'.__('Client', FAKTURO_TEXT_DOMAIN ).'	</label></th>
						<td>'.$selectClients.'</td>
					</tr>
					<tr class="user-facebook-wrap">
						<th><label for="receipt_number">'.__('Receipt Number', FAKTURO_TEXT_DOMAIN ).'	</label></th>
						<td><input type="text" name="receipt_number" id="receipt_number" value="'.$receipt_data['receipt_number'].'"/></td>
					</tr>
					<tr class="user-facebook-wrap">
						<th><label for="available_to_include">'.__('Available to include', FAKTURO_TEXT_DOMAIN ).'	</label></th>
						<td><div id="client_available_to_include" data-available="0">No money available to include</div>   <input type="text" name="available_to_include" id="available_to_include" value="'.$receipt_data['available_to_include'].'"/></td>
					</tr>
					<tr class="user-facebook-wrap">
						<th><label for="payment_type_id">'.__('Payment Type', FAKTURO_TEXT_DOMAIN ).'	</label></th>
						<td>'.$selectPaymentTypes.'</td>
					</tr>
					<tr class="user-facebook-wrap">
						<th><label for="currency_id">'.__('Currency', FAKTURO_TEXT_DOMAIN ).'	</label></th>
						<td>'.$selectCurrencies.'</td>
					</tr>
					<tr class="user-facebook-wrap">
						<th><label for="cash">'.__('Cash', FAKTURO_TEXT_DOMAIN ).'	</label></th>
						<td><input type="text" name="cash" id="cash" value="'.$receipt_data['cash'].'"/></td>
					</tr>
					
			</tbody>
			</table>
			<div id="popup_check_background" style="display:none;"></div> 
			<div id="receipt_check_popup" style="display:none;"></div>
			<strong>Checks</strong>
			<div id="checks_content">
				<table id="checks_header_table">
					<tr>
						<th class="ck_column">'. __('Nro', FAKTURO_TEXT_DOMAIN  ) .'</th>
						<th class="ck_column">'. __('Bank', FAKTURO_TEXT_DOMAIN  ) .'</th>
						<th class="ck_column">'. __('Value', FAKTURO_TEXT_DOMAIN  ) .'</th>
						<th class="ck_column">'. __('Actions', FAKTURO_TEXT_DOMAIN  ) .'</th>
					</tr>
				</table>
				<table id="checks_table">
					<tr>
						<th>'. __('No checks in receipt, You can add a check.', FAKTURO_TEXT_DOMAIN  ) .'</th>
					</tr>
				</table>
				<a href="#" class="button-primary add" id="add_more_check" style="margin-top:5px; font-weight: bold; text-decoration: none; height: 31px;line-height: 29px;"> '.__('Add ckeck', FAKTURO_TEXT_DOMAIN  ).'</a>
			</div>
			
			<strong>Invoices</strong>
			<div id="invoice_content">
				<table id="invoices_header_table">
					<tr>
						<th></th>
						<th class="in_column">'. __('Date', FAKTURO_TEXT_DOMAIN  ) .'</th>
						<th class="in_column">'. __('Invoice Number', FAKTURO_TEXT_DOMAIN  ) .'</th>
						<th class="in_column">'. __('Value Original', FAKTURO_TEXT_DOMAIN  ) .'</th>
						<th class="in_column">'. __('In current currecy', FAKTURO_TEXT_DOMAIN  ) .'</th>
						<th class="in_column">'. __('To Pay', FAKTURO_TEXT_DOMAIN  ) .'</th>
					</tr>
				</table>
				<table id="invoices_table">
					<tr>
						<th>'. __('No invoice available, please select a cliente.', FAKTURO_TEXT_DOMAIN  ) .'</th>
					</tr>
				</table>
				
			</div>
			<table class="form-table">
				<tr class="user-facebook-wrap">
					<th><label>'.__('Current account balance', FAKTURO_TEXT_DOMAIN ).'	</label></th>
					<td>0$</td>
				</tr>
				<tr class="user-facebook-wrap">
					<th><label>'.__('Future account balance', FAKTURO_TEXT_DOMAIN ).'	</label></th>
					<td>0$</td>
				</tr>
			</table>	
					
			';
		
		$echoHtml = apply_filters('fktr_receipt_invoice_box', $echoHtml);
		echo $echoHtml;
		do_action('add_fktr_receipt_invoice_box', $echoHtml);
		
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
	
	public static function ajax_validate_receipt() {
		$setting_system = get_option('fakturo_system_options_group', false);
		$response = new WP_Ajax_Response;
		$fields = array();
		parse_str($_POST['inputs'], $fields);
		$fields = apply_filters('fktr_clean_receipt_fields',$fields);
		
		
		
		do_action('fktr_validate_receipt', $fields);
		
		// Everything fine, go to save invoice :D
		$response->add( array(  
				'data'	=> 'success',
				'supplemental' => array(
					'message' => '',
					'inputSelector' => '',
					'function' => '',
				),
			)); 
		$response->send();//		wp_die();
	}
	
	public static function before_delete($post_id) {  // just permanent delete (when uses stock)
		$post_type = get_post_type($post_id);
		
	}
	
	public static function clean_fields($fields) {
		$setting_system = get_option('fakturo_system_options_group', false);
		if (!isset($fields['client_id'])) {
			$fields['client_id'] = 0;
		}
		if (!isset($fields['receipt_number'])) {
			$fields['receipt_number'] = '';
		}
		if (!isset($fields['available_to_include'])) {
			$fields['available_to_include'] = '';
		}
		if (!isset($fields['payment_type_id'])) {
			$fields['payment_type_id'] = 0;
		}
		if (!isset($fields['currency_id'])) {
			$fields['currency_id'] = 0;
		}
		if (!isset($fields['cash'])) {
			$fields['cash'] = '';
		}
		
		
		return $fields;
	}
	public static function before_save($fields) {
		$setting_system = get_option('fakturo_system_options_group', false);
		
		
		return $fields;
	}
	public static function default_fields($new_status, $old_status, $post ) {
		
		if( $post->post_type == 'fktr_receipt' && $old_status == 'new'){		
			$setting_system = get_option('fakturo_system_options_group', false);
			$fields = array();
			$fields['client_id'] = 0;
			$fields['receipt_number'] = '';
			$fields['available_to_include'] = '';
			$fields['payment_type_id'] = 0;
			$fields['currency_id'] = 0; 
			$fields['cash'] = '';
			$fields = apply_filters('fktr_clean_receipt_fields', $fields);

			foreach ( $fields as $field => $value ) {
				if ( !is_null( $value ) ) {
					$new = apply_filters( 'fktr_receipt_metabox_save_' . $field, $value );  //filtra cada campo antes de grabar
					update_post_meta( $post->ID, $field, $new );
				}
			}
		}
	}
	public static function get_receipt_data($receipt_id) {
		$custom_field_keys = get_post_custom($receipt_id);
		foreach ( $custom_field_keys as $key => $value ) {
			$custom_field_keys[$key] = maybe_unserialize($value[0]);
		}
		
		$custom_field_keys = apply_filters('fktr_clean_receipt_fields', $custom_field_keys );
		return $custom_field_keys;
	}
	
	
	public static function save($post_id, $post) {
		global $wpdb;
		if (isset($_POST['original_post_status']) && $_POST['original_post_status'] == 'publish') {
			return false;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX') && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) {
			return false;
		}

		if ( isset( $post->post_type ) && $post->post_type == 'revision' || $post->post_type!= 'fktr_receipt') {
			return false;
		}
		
		if ( ! current_user_can( 'manage_options', $post_id ) ) {
			return false;
		}
		
	
		$setting_system = get_option('fakturo_system_options_group', false);
		$fields = apply_filters('fktr_clean_receipt_fields',$_POST);
		$fields = apply_filters('fktr_receipt_before_save',$fields);
		
		
		/*
		if(isset($fields['date']) && is_string($fields['date'])) {

			$fields['date'] = fakturo_date2time($fields['date'], $setting_system['dateformat'] );
		}
		*/
		
		foreach ($fields as $field => $value ) {
			
			if ( !is_null( $value ) ) {
				$new = apply_filters('fktr_receipt_metabox_save_' . $field, $value );  //filtra cada campo antes de grabar
				update_post_meta( $post_id, $field, $new );
				
			}
			
		}
		
		do_action( 'fktr_save_receipt', $post_id, $post );
		
	}
	
	
} 

endif;

$fktrPostTypeReceipts = new fktrPostTypeReceipts();

?>