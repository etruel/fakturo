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
		
		add_filter('parent_file',  array( __CLASS__, 'menu_correction'));
		add_filter('submenu_file',  array( __CLASS__, 'submenu_correction'));
		
		
		add_action( 'admin_print_scripts-post-new.php', array(__CLASS__,'scripts'), 11 );
		add_action( 'admin_print_scripts-post.php', array(__CLASS__,'scripts'), 11 );
		
		add_action('admin_print_styles-post-new.php', array(__CLASS__,'styles'));
		add_action('admin_print_styles-post.php', array(__CLASS__,'styles'));
		
		add_action('get_delete_post_link', array(__CLASS__, 'set_delete_post_link'), 10, 3);
		
		add_filter('fktr_clean_receipt_fields', array(__CLASS__, 'clean_fields'), 10, 1);
		add_filter('fktr_receipt_before_save', array(__CLASS__, 'before_save'), 10, 1);

		add_action('wp_ajax_receipt_client_data', array(__CLASS__, 'get_client_data'));
		add_action('wp_ajax_validate_receipt', array(__CLASS__, 'ajax_validate_receipt'));
		add_action('wp_ajax_get_suggest_receipt_number', array(__CLASS__, 'get_suggest_receipt_number'));
		
		add_filter('post_updated_messages', array(__CLASS__, 'updated_messages') );
		add_filter('attribute_escape', array(__CLASS__, 'change_button_texts'), 10, 2);
		add_action('before_delete_post', array(__CLASS__, 'before_delete'), 10, 1);
		add_filter('wp_insert_post_data' , array(__CLASS__, 'dont_draft'), 99, 2);  
		
		add_action( 'admin_post_cancel_receipt', array(__CLASS__, 'cancel_receipt'));
		add_filter('post_row_actions', array(__CLASS__, 'row_actions'), 10, 2);
		add_action( 'admin_print_scripts', array(__CLASS__, 'admin_inline_scripts'));

		add_action('admin_post_print_receipt', array(__CLASS__, 'print_receipt'));
		add_filter( 'bulk_actions-edit-fktr_receipt', array(__CLASS__, 'bulk_actions') );
		add_filter( 'handle_bulk_actions-edit-fktr_receipt', array(__CLASS__, 'bulk_action_handler'), 10, 3 );
		add_action('admin_print_scripts', array(__CLASS__,'scripts_list'));

		add_filter('manage_fktr_receipt_posts_columns' , array(__CLASS__, 'columns' ));
		add_filter('manage_fktr_receipt_posts_custom_column' , array(__CLASS__, 'manage_columns' ), 10, 2 );
	}

	public static function columns($columns) {
	    
		return array(
	        'cb' => '<input type="checkbox" />',
	        'title' => __('Title'),
	        'invoices_affected' =>__( 'Invoices affected', FAKTURO_TEXT_DOMAIN),
	        'date' => __('Date'),
			
	    );
	}
	public static function manage_columns( $column, $post_id) {
		$receipt_data = self::get_receipt_data($post_id);
		$setting_system = get_option('fakturo_system_options_group', false);
		
		switch ( $column ) {
			case 'invoices_affected':
				foreach ($receipt_data['check_invs'] as $kc => $invoice_id) {
					$data_inv = fktrPostTypeSales::get_sale_data($invoice_id);
					echo '<a href="'.get_edit_post_link($invoice_id).'">'.$data_inv['post_title'].'</a><br/>';
				}
			break;
		}
	}
	public static function print_receipt() {
		$object = new stdClass();
		$object->type = 'post';
		$object->id = $_REQUEST['id'];
		$object->assgined = 'fktr_receipt';
		if ($object->id) {
			$id_print_template = fktrPostTypePrintTemplates::get_id_by_assigned($object->assgined);
			if ($id_print_template) {
				$print_template = fktrPostTypePrintTemplates::get_print_template_data($id_print_template);
			} else {
				wp_die(__('No print template assigned to receipts', FAKTURO_TEXT_DOMAIN ));
			}
			$tpl = new fktr_tpl;
			$tpl = apply_filters('fktr_print_template_assignment', $tpl, $object, false);
			$html = $tpl->fromString($print_template['content']);
			if (isset($_REQUEST['pdf'])) {
				$pdf = fktr_pdf::getInstance();
				$pdf ->set_paper("A4", "portrait");
				$pdf ->load_html(utf8_decode($html));
				$pdf ->render();
				$pdf ->stream('pdf.pdf', array('Attachment'=>0));

			} else {
				echo $html;
			}
			
			exit();
		}
	}
	public static function admin_inline_scripts() {
		global $current_screen;
		if ($current_screen->post_type == 'fktr_receipt') {
			wp_enqueue_style('post-type-receipts',FAKTURO_PLUGIN_URL .'assets/css/post-type-receipts.css');	
		}
	}
	 public static function bulk_actions($actions){

        $actions['send_receipt_pdf_client'] = __( 'Send pdf to clients', FAKTURO_TEXT_DOMAIN);

        return $actions;
    }
    public static function bulk_action_handler( $redirect_to, $doaction, $post_ids ) {
		if ($doaction !== 'send_receipt_pdf_client' ) {
		    return $redirect_to;
		}
		foreach ($post_ids as $post_id) {
			fktr_mail::send_receipt_pdf_to_client($post_id, false);
		    // Perform action for each post.
		}
		$redirect_to = add_query_arg( 'bulk_emailed_posts', count( $post_ids ), $redirect_to );
		return $redirect_to;
	}
	public static function row_actions($actions, $post) {
		if ($post->post_type == 'fktr_receipt' && $post->post_status != 'cancelled') {
			$actions['cancelled'] = '<a class="submit_cancel_receipt" href="'.admin_url('admin-post.php?post='.$post->ID.'&action=cancel_receipt').'" onclick="return confirm(\''.__('Do you want cancel this receipt?', FAKTURO_TEXT_DOMAIN ).'\');">Cancel this receipt</a>';
			$actions['print_receipt'] = '<a href="'.admin_url('admin-post.php?id='.$post->ID.'&action=print_receipt').'" class="btn_print_receipt" target="_new">'.__( 'Print Receipt', FAKTURO_TEXT_DOMAIN ).'</a>';


			if (empty($actions['send_invoice_to_client'])) {
				$sale_data = self::get_receipt_data($post->ID);
				$client_data = fktrPostTypeClients::get_client_data($sale_data['client_id']);
				if (!empty($client_data['email'])) {
					$url = admin_url('admin-post.php?id='.$post->ID.'&action=send_receipt_to_client');
					$url = wp_nonce_url($url, 'send_receipt_to_client', '_wpnonce');
					$actions['send_receipt_to_client'] = '<a href="'.$url.'" class="btn_send_receipt">'.__( 'Send PDF to Client', FAKTURO_TEXT_DOMAIN ).'</a>';
				}
			}
			
		}
		return $actions;
	}
	public static function cancel_receipt() {
		if (is_numeric($_REQUEST['post'])) {
			$my_post = array(
				'ID'           => $_REQUEST['post'],
				'post_status'   => 'cancelled',
			);
			wp_update_post($my_post);
			self::action_on_delete($_REQUEST['post']);
		}
		wp_redirect(admin_url('edit.php?post_status=cancelled&post_type=fktr_receipt'));
		exit();
	}  
	public static function dont_draft($data, $postarr) {  
		if($data['post_type'] == 'fktr_receipt' && $data['post_status'] == 'draft'){
			$data['post_status'] = 'publish';  
		}
		return $data;
	}  
	public static function menu_correction($parent_file) {
		global $current_screen;
		if ($current_screen->id == 'edit-fktr_receipt' || $current_screen->id == 'fktr_receipt') {
			$parent_file = 'edit.php?post_type=fktr_sale';
		}
		return $parent_file;
	}
	public static function submenu_correction($submenu_file) {
		global $current_screen;
		if ($current_screen->id == 'edit-fktr_receipt' || $current_screen->id == 'fktr_receipt') {
			$submenu_file = 'edit.php?post_type=fktr_receipt';
		}
		return $submenu_file;
	}

	public static function change_button_texts($safe_text, $text ){
		global $post, $current_screen, $screen;
		
		if (isset($post) && $post->post_type == 'fktr_receipt') {
			switch( $safe_text ) {
				case __('Save Draft');
					$safe_text = __('Save as Pendient', FAKTURO_TEXT_DOMAIN );
					break;

				case __('Publish');
					$safe_text = __('Save', FAKTURO_TEXT_DOMAIN );
					break;
				case __('Update');
					$safe_text = __('Save', FAKTURO_TEXT_DOMAIN );
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
			
			if ($post->post_status == 'publish' || $post->post_status == 'cancelled') {
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
			'public' => false,
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

		register_post_status('cancelled', array(
			'label'                     => __('Cancelled', FAKTURO_TEXT_DOMAIN),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Cancelled <span class="count">(%s)</span>', 'Cancelled <span class="count">(%s)</span>' ),
		));
		
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
		$setting_system = get_option('fakturo_system_options_group', false);
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
				$data_inv = fktrPostTypeSales::get_sale_data($inv->ID);
				$data_inv['date'] = date_i18n($setting_system['dateformat'], $data_inv['date']);
				$client_data['invoice_sales'][] = $data_inv;
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
	public static function scripts_list() {
		global $current_screen;
		if ($current_screen->post_type == 'fktr_receipt') {
			wp_enqueue_script( 'post-type-sales-list', FAKTURO_PLUGIN_URL . 'assets/js/post-type-receipts-list.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
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
			
			$bank_entities = get_fakturo_terms(array(
							'taxonomy' => 'fktr_bank_entities',
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
					'digits_receipt_number' => $setting_system['digits_receipt_number'],
					'post_status' => $post->post_status,
					'datetimepicker' => $objectL10n,
					
					
					'txt_loading' => __('Loading', FAKTURO_TEXT_DOMAIN ),
					'txt_cancel' => __('Cancel', FAKTURO_TEXT_DOMAIN ),
					'url_loading_image' => get_bloginfo('wpurl').'/wp-admin/images/wpspin_light.gif',
					
					'currencies' => $currencies,
					'bank_entities' => $bank_entities,
					
					'select_bank_entities' => $selectBankEntities,
					'select_bank_currencies' => $selectCurrencies,
				)
			);
				
			
		
		}
		
	}
	
	public static function meta_boxes() {
		global $post;
		add_meta_box('fakturo-currencies-box', __('Currencies', FAKTURO_TEXT_DOMAIN ), array(__CLASS__, 'currencies_box'),'fktr_receipt','side', 'high' );
		if ($post->post_status == 'publish') {
			add_meta_box('fakturo-cancel-receipt-box', __('Cancel receipt', FAKTURO_TEXT_DOMAIN ), array(__CLASS__, 'cancel_box'),'fktr_receipt','side', 'low' );
		}
		add_meta_box('fakturo-receipt-box', __('Receipt data', FAKTURO_TEXT_DOMAIN ), array(__CLASS__, 'receipt_box'),'fktr_receipt','normal', 'high' );
		do_action('add_ftkr_receipt_meta_boxes');
	}
	public static function cancel_box() {
		global $post;
		$echoHtml = '<a class="submit_cancel_receipt" href="'.admin_url('admin-post.php?post='.$post->ID.'&action=cancel_receipt').'" onclick="return confirm(\''.__('Do you want cancel this receipt?', FAKTURO_TEXT_DOMAIN ).'\');">Cancel this receipt</a>';
		$echoHtml = apply_filters('fktr_receipt_cancel_box', $echoHtml);
		echo $echoHtml;
		do_action('add_fktr_receipt_cancel_box', $echoHtml);
	}
	public static function currencies_box() {
		global $post;
		
	
		$sale_data = self::get_receipt_data($post->ID);
		$setting_system = get_option('fakturo_system_options_group', false);
		
		$currencies = get_fakturo_terms(array(
											'taxonomy' => 'fktr_currencies',
											'hide_empty' => false,
											'exclude' => $setting_system['currency']
										));
		
		
		
		
		$echoHtml = '<table>
					<tbody>';
		
		foreach ($currencies as $cur) {
			$echoHtml .= '<tr>
							<td>'.((empty($cur->reference))?'':'<a href="'.$cur->reference.'" target="_blank">').''.$cur->name.''.((empty($cur->reference))?'':'</a>').'</td>'.(($setting_system['currency_position'] == 'before')?'<td><label for="receipt_currencies_'.$cur->term_id.'">'.$cur->symbol.'</label></td>':'').'<td>'.(($post->post_status != 'publish' && $post->post_status != 'cancelled')?'<input type="text" style="text-align: right; width: 120px;" value="'.$cur->rate.'" name="receipt_currencies['.$cur->term_id.']" id="receipt_currencies_'.$cur->term_id.'" class="receipt_currencies"/> ':number_format($cur->rate, $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand'])).''.(($setting_system['currency_position'] == 'after')?'<td><label for="invoice_currencies_'.$cur->term_id.'">'.$cur->symbol.'</label></td>':'').'</td>
						</tr>';
			
		}
			
		$echoHtml .= '</tbody>
				</table>';
	
		$echoHtml = apply_filters('fktr_receipt_currencies_box', $echoHtml);
		echo $echoHtml;
		do_action('add_fktr_receipt_currencies_box', $echoHtml);
		
		
	}
	public static function receipt_box() {
		global $post;
		$receipt_data = self::get_receipt_data($post->ID);
		
		$setting_system = get_option('fakturo_system_options_group', false);
		$receipt_data['receipt_number'] = (($post->post_status != 'publish' && $post->post_status != 'cancelled')? str_pad(self::suggestReceiptNumber(), $setting_system['digits_receipt_number'], '0', STR_PAD_LEFT) : $receipt_data['receipt_number'] );
		$selectClients = $receipt_data['client_id'];
		if ($post->post_status != 'publish' && $post->post_status != 'cancelled') {
			$selectClients = fakturo_get_select_post(array(
											'echo' => 0,
											'post_type' => 'fktr_client',
											'show_option_none' => __('Choose a Client', FAKTURO_TEXT_DOMAIN ),
											'name' => 'client_id',
											'id' => 'client_id',
											'class' => '',
											'selected' => $receipt_data['client_id']
										));
		} 
		$selectPaymentTypes = __('No payment type', FAKTURO_TEXT_DOMAIN );
		if ($post->post_status != 'publish' && $post->post_status != 'cancelled') {
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
		} else {
			$term_payment_type = get_fakturo_term($receipt_data['payment_type_id'], 'fktr_payment_types');
			if(!is_wp_error($term_payment_type)) {
				$selectPaymentTypes = $term_payment_type->name;
			}
		}
		
		$receiptSymbol = __('No Symbol', FAKTURO_TEXT_DOMAIN);
		$receiptCurrency = get_fakturo_term($receipt_data['currency_id'], 'fktr_currencies');
		if(!is_wp_error($receiptCurrency)) {
			$receiptSymbol = $receiptCurrency->symbol;
		}
		
		$defaultCurrency = get_fakturo_term($setting_system['currency'], 'fktr_currencies');
		
		$selectCurrencies = __('No currency, used the default currency.', FAKTURO_TEXT_DOMAIN );
		if ($post->post_status != 'publish' && $post->post_status != 'cancelled') {
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
		} else {
			$term_currency = get_fakturo_term($receipt_data['currency_id'], 'fktr_currencies');
			if(!is_wp_error($term_currency)) {
				$selectCurrencies = $term_currency->name;
			} else {
				$term_currency = $defaultCurrency;
			}
		}
		
		$checksHtml = '<tr id="message_check_table">
							<th>'. __('No checks in receipt, You can add a check.', FAKTURO_TEXT_DOMAIN  ) .'</th>
						</tr>';
		if ($post->post_status != 'publish' && $post->post_status != 'cancelled') {
			
		} else {
			if (!empty($receipt_data['ck_ids'])) {
				$checksHtml = '';
				foreach ($receipt_data['ck_ids'] as $term_id) {
					$term_check = get_fakturo_term($term_id, 'fktr_check');
					if(!is_wp_error($term_check)) {
						$bank_text = __('No bank', FAKTURO_TEXT_DOMAIN);
						$term_bank = get_fakturo_term($term_check->bank_id, 'fktr_bank_entities');
						if(!is_wp_error($term_bank)) {
							$bank_text = $term_bank->name;
						}
						$symbol = __('No Symbol', FAKTURO_TEXT_DOMAIN);
						$checkCurrency = get_fakturo_term($term_check->currency_id, 'fktr_currencies');
						if(!is_wp_error($checkCurrency)) {
							$symbol = $checkCurrency->symbol;
						}
						
						$checksHtml .= '<tr class="tr_check_list tr_gray" id="tr_ck_id_'.$term_id.'">
							<td class="ck_column">'.$term_check->name.'</td>
							<td class="ck_column">'.$bank_text.'</td>
							<td class="ck_column">'.(($setting_system['currency_position'] == 'before')?''.$symbol.' ':'').''.number_format($term_check->value, $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']).''.(($setting_system['currency_position'] == 'after')?' '.$symbol.'':'').'</td>
						</tr>';
					}
					
				}
				
			} else {
				$checksHtml = '<tr id="message_check_table">
							<th>'. __('No checks in receipt.', FAKTURO_TEXT_DOMAIN  ) .'</th>
						</tr>';
				
			}
			
		}
		$invsHtml = '<tr>
						<th>'. __('No invoice available, please select a cliente.', FAKTURO_TEXT_DOMAIN  ) .'</th>
					</tr>';
		if ($post->post_status != 'publish' && $post->post_status != 'cancelled') {
			
		} else {
			if (!empty($receipt_data['check_invs'])) {
				$invsHtml = '';
				foreach ($receipt_data['check_invs'] as $kc => $invoice_id) {
					$affected = $receipt_data['to_pay'][$kc];
					$data_inv = fktrPostTypeSales::get_sale_data($invoice_id);
					$data_inv['date'] = date_i18n($setting_system['dateformat'], $data_inv['date']);
					$symbol = __('No Symbol', FAKTURO_TEXT_DOMAIN);
					$invCurrency = get_fakturo_term($data_inv['invoice_currency'], 'fktr_currencies');
					if(!is_wp_error($invCurrency)) {
							$symbol = $invCurrency->symbol;
					}
					$in_total_curr = fakturo_transform_money($data_inv['invoice_currency'], $term_currency->term_id, $data_inv['in_total']);
					
					$invsHtml .= '<tr id="in_'.$kc.'" class="tr_gray">
						<td class="in_column">'.$data_inv['date'].'</td>
						<td class="in_column">'.$data_inv['post_title'].'</td>
						<td class="in_column">'.(($setting_system['currency_position'] == 'before')?''.$symbol.' ':'').''.number_format($data_inv['in_total'], $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']).''.(($setting_system['currency_position'] == 'after')?' '.$symbol.'':'').'</td>
						<td class="in_column">'.(($setting_system['currency_position'] == 'before')?''.$term_currency->symbol.' ':'').''.number_format($in_total_curr, $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']).''.(($setting_system['currency_position'] == 'after')?' '.$term_currency->symbol.'':'').'</td>
						<td class="in_column">'.(($setting_system['currency_position'] == 'before')?''.$term_currency->symbol.' ':'').''.number_format($affected, $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']).''.(($setting_system['currency_position'] == 'after')?' '.$term_currency->symbol.'':'').'</td>
					</tr>';
				}
			} else {
				$invsHtml = '<tr>
						<th>'. __('No invoice available.', FAKTURO_TEXT_DOMAIN  ) .'</th>
					</tr>';
				
			}
		}
		
		
		$echoHtml = '
			<table class="form-table">
				<tbody>

					<tr class="tr_fktr">
						<th><label for="client_id">'.__('Client', FAKTURO_TEXT_DOMAIN ).'	</label></th>
						<td>'.$selectClients.'</td>
					</tr>
					<tr class="tr_fktr">
						<th><label for="receipt_number">'.__('Receipt Number', FAKTURO_TEXT_DOMAIN ).'	</label></th>
						<td>'.(($post->post_status != 'publish' && $post->post_status != 'cancelled')?' <input type="text" name="receipt_number" id="receipt_number" value="'.$receipt_data['receipt_number'].'"/>': $receipt_data['receipt_number']).'</td>
					</tr>
					<tr class="tr_fktr">
						<th><label for="available_to_include">'.__('Available to include', FAKTURO_TEXT_DOMAIN ).'	</label></th>
						<td>'.(($post->post_status != 'publish' && $post->post_status != 'cancelled')?'<div id="client_available_to_include" data-available="0">No money available to include</div>   <input type="text" name="available_to_include" id="available_to_include" value="'.$receipt_data['available_to_include'].'"/>':''.(($setting_system['currency_position'] == 'before')?''.$defaultCurrency->symbol.' ':'').''.number_format($receipt_data['available_to_include'], $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']).''.(($setting_system['currency_position'] == 'after')?' '.$defaultCurrency->symbol.'':'').'').'</td>
					</tr>
					<tr class="tr_fktr">
						<th><label for="payment_type_id">'.__('Payment Type', FAKTURO_TEXT_DOMAIN ).'	</label></th>
						<td>'.$selectPaymentTypes.'</td>
					</tr>
					<tr class="tr_fktr">
						<th><label for="currency_id">'.__('Currency', FAKTURO_TEXT_DOMAIN ).'	</label></th>
						<td>'.$selectCurrencies.'</td>
					</tr>
					<tr class="tr_fktr">
						<th><label for="cash">'.__('Cash', FAKTURO_TEXT_DOMAIN ).'	</label></th>
						<td>'.(($post->post_status != 'publish' && $post->post_status != 'cancelled')?' <input type="text" name="cash" id="cash" value="'.$receipt_data['cash'].'"/>': ''.(($setting_system['currency_position'] == 'before')?''.$term_currency->symbol.' ':'').''.number_format($receipt_data['cash'], $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']).''.(($setting_system['currency_position'] == 'after')?' '.$term_currency->symbol.'':'').'').'</td>
					</tr>
					
			</tbody>
			</table>
			<div id="popup_check_background" style="display:none;"></div> 
			<div id="receipt_check_popup" style="display:none;"></div>
			<div id="title_checks" class="title_checks">Checks</div>
			<div id="checks_content">
				<table id="checks_header_table">
					<tr>
						<th class="ck_column">'. __('Nro', FAKTURO_TEXT_DOMAIN  ) .'</th>
						<th class="ck_column">'. __('Bank', FAKTURO_TEXT_DOMAIN  ) .'</th>
						<th class="ck_column">'. __('Value', FAKTURO_TEXT_DOMAIN  ) .'</th>
						'.(($post->post_status != 'publish' && $post->post_status != 'cancelled')? '<th class="ck_column">'. __('Actions', FAKTURO_TEXT_DOMAIN  ) .'</th>' : '').'
						
					</tr>
				</table>
				<table id="checks_table">
					'.$checksHtml.'
				</table>
				'.(($post->post_status != 'publish' && $post->post_status != 'cancelled')? '<a href="#" class="button-primary add" id="add_more_check" style="margin-top:5px; font-weight: bold; text-decoration: none; height: 31px;line-height: 29px;"> '.__('Add ckeck', FAKTURO_TEXT_DOMAIN  ).'</a>': '').'
				
			</div>
			
			<div id="title_invoices" class="title_invoices">Invoices</div>
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
					'.$invsHtml.'
				</table>
				
			</div>
			
			
			<table class="form-table">
				<tr class="tr_fktr">
					<th><label>'.__('Current account balance', FAKTURO_TEXT_DOMAIN ).'	</label></th>
					<td id="receipt_acc_current_balance"><input type="hidden" id="current_acc_balance" name="current_acc_balance" value="'.$receipt_data['current_acc_balance'].'"/>'.(($setting_system['currency_position'] == 'before')?''.$receiptSymbol.' ':'').''.number_format($receipt_data['current_acc_balance'], $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']).''.(($setting_system['currency_position'] == 'after')?' '.$receiptSymbol.'':'').'</td>
				</tr>
				<tr class="tr_fktr">
					<th><label>'.__('Total to impute', FAKTURO_TEXT_DOMAIN ).'	</label></th>
					<td id="receipt_total_to_impute"><input type="hidden" id="total_to_impute" name="total_to_impute" value="'.$receipt_data['total_to_impute'].'"/>'.(($setting_system['currency_position'] == 'before')?''.$receiptSymbol.' ':'').''.number_format($receipt_data['total_to_impute'], $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']).''.(($setting_system['currency_position'] == 'after')?' '.$receiptSymbol.'':'').'</td>
				</tr>
				<tr class="tr_fktr">
					<th><label>'.__('Total to pay', FAKTURO_TEXT_DOMAIN ).'	</label></th>
					<td id="receipt_total_to_pay"><input type="hidden" id="total_to_pay" name="total_to_pay" value="'.$receipt_data['total_to_pay'].'"/>'.(($setting_system['currency_position'] == 'before')?''.$receiptSymbol.' ':'').''.number_format($receipt_data['total_to_pay'], $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']).''.(($setting_system['currency_position'] == 'after')?' '.$receiptSymbol.'':'').'</td>
				</tr>
				<tr class="tr_fktr">
					<th><label>'.__('Available to include', FAKTURO_TEXT_DOMAIN ).'	</label></th>
					<td id="receipt_available_to_include"><input type="hidden" id="total_available_to_include" name="total_available_to_include" value="'.$receipt_data['total_available_to_include'].'"/>'.(($setting_system['currency_position'] == 'before')?''.$receiptSymbol.' ':'').''.number_format($receipt_data['total_available_to_include'], $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']).''.(($setting_system['currency_position'] == 'after')?' '.$receiptSymbol.'':'').'</td>
				</tr>
				<tr id="tr_positive_balance" class="tr_fktr">
					<th><label>'.__('Positive balance', FAKTURO_TEXT_DOMAIN ).'	</label></th>
					<td id="receipt_positive_balance"><input type="hidden" id="positive_balance" name="positive_balance" value="'.$receipt_data['positive_balance'].'"/>'.(($setting_system['currency_position'] == 'before')?''.$receiptSymbol.' ':'').''.number_format($receipt_data['positive_balance'], $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']).''.(($setting_system['currency_position'] == 'after')?' '.$receiptSymbol.'':'').'</td>
				</tr>
				
				<tr class="tr_fktr">
					<th><label>'.__('Future account balance', FAKTURO_TEXT_DOMAIN ).'	</label></th>
					<td id="receipt_acc_future_balance"><input type="hidden" id="future_balance" name="future_balance" value="'.$receipt_data['future_balance'].'"/>'.(($setting_system['currency_position'] == 'before')?''.$receiptSymbol.' ':'').''.number_format($receipt_data['future_balance'], $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']).''.(($setting_system['currency_position'] == 'after')?' '.$receiptSymbol.'':'').'</td>
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
		
		$invoiceNumberExiste = self::exist_a_receipt_number($fields['receipt_number']);
		if ($invoiceNumberExiste) {
			$response->add( array(
				'data'	=> 'error',
				'supplemental' => array(
					'message' => __('This receipt number is already in use, Please try again.', FAKTURO_TEXT_DOMAIN ),
					'inputSelector' => '#receipt_number',
					'function' => 'updateSuggestReceiptNumber',
				),
			)); 
			$response->send();
		}
		
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
	public static function get_suggest_receipt_number() {
		$setting_system = get_option('fakturo_system_options_group', false);
		
		$suggested = self::suggestReceiptNumber();
		wp_die($suggested);
	}
	public static function exist_a_receipt_number($receipt_number) {
		global $wpdb;
		$return = false;
		$setting_system = get_option('fakturo_system_options_group', false);
		
		$sql = sprintf("SELECT pm.meta_value FROM {$wpdb->postmeta} pm
			LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			WHERE pm.meta_key = 'receipt_number'
			AND pm.meta_value = '%s'
			AND p.post_status = 'publish'
			AND p.post_type = 'fktr_receipt'
			LIMIT 1
			", $receipt_number);
		$r = $wpdb->get_results($sql);
		if (!empty($r)) {
			$return = true;
		}
		return $return;
    }
	public static function suggestReceiptNumber() {
		$retorno = 0;
		$setting_system = get_option('fakturo_system_options_group', false);
		$last_receipt_numbers = get_option('last_receipt_number', false);
		if (!$last_receipt_numbers) {
			$last_receipt_numbers = 0;
			update_option('last_receipt_number', $last_receipt_numbers);
		}
		$retorno = $last_receipt_numbers+1;
		return $retorno;
	}
	public static function updateSuggestReceiptNumber($receipt_number = 0) {
		
		$setting_system = get_option('fakturo_system_options_group', false);
		$last_receipt_numbers = get_option('last_receipt_number', false);
		if (!$last_receipt_numbers) {
			$last_receipt_numbers = 0;
		}
		if ($last_receipt_numbers < $receipt_number) {
			$last_receipt_numbers = $receipt_number;
		}
		update_option('last_receipt_number', $last_receipt_numbers);
		
	}
	public static function action_on_delete($post_id) {
		$post_type = get_post_type($post_id);
		$post_status = get_post_status($post_id);
		if ($post_type != 'fktr_receipt') {
			return false;
		}
		
		$fields = self::get_receipt_data($post_id);
		fktrPostTypeClients::add_balance($fields['client_id'], $fields['available_to_include']);
		if (!empty($fields['ck_ids'])) {
			foreach ($fields['ck_ids'] as $k => $term_id) {
				$check = get_fakturo_term($term_id, 'fktr_check');
				$term_taxonomy_id = $check->term_taxonomy_id;
				$check->status = 'X';
				unset($check->term_id);
				unset($check->name);
				unset($check->slug);
				unset($check->term_group);
				unset($check->term_taxonomy_id);
				unset($check->taxonomy);
				unset($check->parent);
				unset($check->count);
				set_fakturo_term($term_id, $term_taxonomy_id,  (array)$check);
			}
		}
		if (!empty($fields['check_invs'])) {
			foreach ($fields['check_invs'] as $kc => $invoice_id) {
				fktrPostTypeSales::updateReceiptsAffected($invoice_id, $fields['ID'], 0, $fields['currency_id']);
			}
		}
		
	}
	public static function before_delete($post_id) {  // just permanent delete
		$post_type = get_post_type($post_id);
		$post_status = get_post_status($post_id);
		if ($post_status == 'publish') {
			self::action_on_delete($post_id);
		}
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
			$fields['currency_id'] = $setting_system['currency'];
		}
		if (!isset($fields['cash'])) {
			$fields['cash'] = '';
		}
		if (!isset($fields['ck_ids'])) {
			$fields['ck_ids'] = array();
		}
		if (!isset($fields['current_acc_balance'])) {
			$fields['current_acc_balance'] = 0;
		}
		if (!isset($fields['total_to_impute'])) {
			$fields['total_to_impute'] = 0;
		}
		if (!isset($fields['total_to_pay'])) {
			$fields['total_to_pay'] = 0;
		}
		if (!isset($fields['total_available_to_include'])) {
			$fields['total_available_to_include'] = 0;
		}
		if (!isset($fields['positive_balance'])) {
			$fields['positive_balance'] = 0;
		}
		if (!isset($fields['future_balance'])) {
			$fields['future_balance'] = 0;
		}
		
		
		return $fields;
	}
	public static function before_save($fields) {
		$setting_system = get_option('fakturo_system_options_group', false);
		
		if (empty($fields['currency_id'])) {
			$fields['currency_id'] = $setting_system['currency'];
		} else {
			if ($fields['currency_id'] < 1) {
				$fields['currency_id'] = $setting_system['currency'];
			}
		}
		if (empty($fields['available_to_include'])) {
			$fields['available_to_include'] = 0;
		} else {
			$fields['available_to_include'] = fakturo_mask_to_float($fields['available_to_include']);
		}
		fktrPostTypeClients::remove_balance($fields['client_id'], $fields['available_to_include']);
		
		if (empty($fields['cash'])) {
			$fields['cash'] = 0;
		} else {
			$fields['cash'] = fakturo_mask_to_float($fields['cash']);
		}
		$fields['ck_ids'] = array();
		if (!empty($fields['ck_banks'])) {
			foreach ($fields['ck_banks'] as $k => $bank_id) {
				$serial_number = $fields['ck_numbers'][$k];
				$fieldsCheck = array();
				$fieldsCheck['client_id'] = $fields['client_id'];
				$fieldsCheck['bank_id'] = $bank_id;
				$fieldsCheck['currency_id'] = $fields['ck_currencies'][$k];
				$fieldsCheck['value'] = $fields['ck_values'][$k];
				$fieldsCheck['date'] = $fields['ck_dates'][$k];
				$fieldsCheck['cashing_date'] = $fields['ck_cashing_dates'][$k];
				$fieldsCheck['date_status'] = $fields['ck_cashing_dates'][$k];
				$fieldsCheck['notes'] = $fields['ck_notes'][$k];
				$fieldsCheck = apply_filters('before_save_tax_fktr_check', $fieldsCheck);
				$fieldsCheck['status'] = 'D';
				$fieldsCheck['provider_id'] = 0;
				 
				if(!term_exists($serial_number, 'fktr_check')) {
					$data_check = wp_insert_term(
						$serial_number,
						'fktr_check',
						 array(
							'description' => json_encode($fieldsCheck)
						)
					);
					$fields['ck_ids'][$k] = $data_check['term_id'];
				}
			}
		}
		unset($fields['ck_banks']);
		unset($fields['ck_numbers']);
		unset($fields['ck_currencies']);
		unset($fields['ck_values']);
		unset($fields['ck_dates']);
		unset($fields['ck_cashing_dates']);
		unset($fields['ck_cashing_dates']);
		unset($fields['ck_notes']);
		
		if (!empty($fields['check_invs'])) {
			foreach ($fields['check_invs'] as $kc => $invoice_id) {
				$key_invoice = -1;
				foreach ($fields['inv_ids'] as $k => $inv_id) {
					if ($inv_id == $invoice_id) {
						$key_invoice = $k;
						break;
					}
				}
				if ($key_invoice > -1) {
					$affected = $fields['to_pay'][$kc];
					
					fktrPostTypeSales::updateReceiptsAffected($invoice_id, $fields['ID'], $affected, $fields['currency_id']);
					$fields['to_pay'][$kc] = fakturo_mask_to_float($fields['to_pay'][$kc]);
					
				}
			}
		}
		unset($fields['inv_total_account']);
		unset($fields['inv_origin_account']);
		unset($fields['inv_currency']);
		
		
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
			$fields['currency_id'] = $setting_system['currency']; 
			$fields['cash'] = '';
			$fields['ck_ids'] = array();
			$fields['current_acc_balance'] = 0;
			$fields['total_to_impute'] = 0;
			$fields['total_to_pay'] = 0;
			$fields['total_available_to_include'] = 0;
			$fields['positive_balance'] = 0;
			$fields['future_balance'] = 0;
			
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
		if (isset($_POST['original_post_status'])) {
			if ($_POST['original_post_status'] == 'publish' || $_POST['original_post_status'] == 'cancelled') {
				return false;
			}
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
		if ( ( defined( 'FKTR_STOP_PROPAGATION') && FKTR_STOP_PROPAGATION ) ) {
			return false;
		}
		if ($post->post_status == 'auto-draft') {
			return false;
		}
		if ($_REQUEST['action'] == 'cancel_receipt') {
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
		if ($post->post_status == 'publish') {
		   self::updateSuggestReceiptNumber($fields['receipt_number']);
		}
		do_action( 'fktr_save_receipt', $post_id, $post );
		
	}
	
	
} 

endif;

$fktrPostTypeReceipts = new fktrPostTypeReceipts();

?>