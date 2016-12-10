<?php


// Exit if accessed directly
if (!defined('ABSPATH'))  {
	exit;
}


if ( ! class_exists('fktrPostTypeEmailTemplates') ) :
class fktrPostTypeEmailTemplates {
	function __construct() {
		
		add_action('init', array(__CLASS__, 'setup'), 1 );
		add_action('edit_form_after_title', array(__CLASS__, 'before_editor'));
		
		add_action('transition_post_status', array(__CLASS__, 'default_fields'), 10, 3);
		add_action('save_post', array(__CLASS__, 'save'), 99, 2 );
		
		add_action( 'admin_print_scripts-post-new.php', array(__CLASS__,'scripts'), 11 );
		add_action( 'admin_print_scripts-post.php', array(__CLASS__,'scripts'), 11 );
		
		add_action('admin_print_styles-post-new.php', array(__CLASS__,'styles'));
		add_action('admin_print_styles-post.php', array(__CLASS__,'styles'));
		
		add_filter('fktr_clean_email_template_fields', array(__CLASS__, 'clean_fields'), 10, 1);
		add_filter('fktr_email_template_before_save', array(__CLASS__, 'before_save'), 10, 1);

		add_filter( 'post_updated_messages', array(__CLASS__, 'updated_messages') );

		add_filter('fktr_assigned_email_template', array(__CLASS__, 'default_assigned'), 10, 1);
		add_filter('fktr_email_template_assignment', array(__CLASS__, 'assignment'), 10, 3);
		

		add_action('admin_post_show_email_template', array(__CLASS__, 'show_email_template'));
		add_action('admin_action_copy_email_template', array(__CLASS__, 'copy_email_template'));
		add_filter('post_row_actions', array(__CLASS__, 'actions'), 10, 2);


		add_action('wp_ajax_get_vars_assigned', array(__CLASS__, 'get_vars_assigned'));
		
	}
	public static function show_email_template() {
		$template_id = $_REQUEST['id'];
		if (empty($template_id)) {
			echo 'Invalid e-mail template id.';
			wp_die();
		}
		
		$email_template = self::get_email_template_data($template_id);
		if (!isset($email_template['assigned'])) {
			wp_redirect(admin_url('post.php?post='.$template_id.'&action=edit'));
			exit;
		}
		if ($email_template['assigned'] == -1) {
			wp_die('<h3>'.__('This e-mail template has no assigned object.', FAKTURO_TEXT_DOMAIN ).'</h3>');
		}
		
		$object = new stdClass();
		$object->type = self::get_object_type($email_template);
		$object->id = self::get_rand_object_id($object->type, $email_template);
		$object->assgined = $email_template['assigned'];
		if ($object->id) {
			$tpl = new fktr_tpl;
			$tpl = apply_filters('fktr_email_template_assignment', $tpl, $object, $email_template);

			$htmlContent = $tpl->fromString($email_template['content']);
			$htmlSubject = $tpl->fromString($email_template['subject']);
			echo '<div style="padding: 15px 10px; font-size: 14px; border-radius: 2px; border: 1px solid #ddd;">'.__('E-mail template', FAKTURO_TEXT_DOMAIN ).': '.$email_template['post_title'].'</div><br/>';
			echo '<div style="padding: 15px 10px; font-size: 14px; border-radius: 2px; border: 1px solid #ddd;">'.__('E-mail Subject', FAKTURO_TEXT_DOMAIN ).': ['.$htmlSubject.']</div><br/>';
			echo '<div style="padding: 15px 10px; font-size: 14px; border-radius: 2px; border: 1px solid #ddd;">'.__('E-mail Body', FAKTURO_TEXT_DOMAIN ).': ['.$htmlContent.']</div><br/>';

			
			exit();
		}
		wp_die('<h3>'.__('Could not find any object related to this e-mail template', FAKTURO_TEXT_DOMAIN ).'</h3>');
		
	}
	public static function assignment($tpl, $object, $default_template) {
		$setting_system = get_option('fakturo_system_options_group', array());
		$tpl->assign("setting_system", $setting_system);
		$company = get_option('fakturo_info_options_group', array());
		$company['img_url'] = $company['url'];
		$company['tax_condition'] = (array)get_fakturo_term($company['tax_condition'] , 'fktr_tax_conditions');
		$tpl->assign( "company", $company);
		if (!$default_template) {
			$id_email_template = self::get_id_by_assigned($object->assgined);
			if ($id_email_template) {
				$default_template = self::get_email_template_data($id_email_template);
			}
		}
		if (!$default_template) {
			return $tpl;
		}
		
		if ($object->assgined == 'fktr_sale') {
			// assign vars to print template assgined to fktr_sale.
			$tpl->assign( "fktr_invoice_background_image", FAKTURO_PLUGIN_URL . 'assets/images/invoice_background.jpg');

			$sale_invoice = fktrPostTypeSales::get_sale_data($object->id);
			
			$sale_invoice['client_data']['tax_condition'] = (array)get_fakturo_term($sale_invoice['client_data']['tax_condition'], 'fktr_tax_conditions');
			$sale_invoice['client_data']['payment_type'] = (array)get_fakturo_term($sale_invoice['client_data']['payment_type'], 'fktr_payment_types');
			$sale_invoice['invoice_type'] = (array)get_fakturo_term($sale_invoice['invoice_type'], 'fktr_invoice_types');
			$sale_invoice['currency'] = (array)get_fakturo_term($sale_invoice['invoice_currency'], 'fktr_currencies');
			$sale_invoice['products'] = array();
			if (!empty($sale_invoice['uc_id'])) {
				foreach ($sale_invoice['uc_id'] as $key => $product_id) {
					$newProduct = array();
					$newProduct['code'] = $sale_invoice['uc_code'][$key]; 
					$newProduct['description'] = $sale_invoice['uc_description'][$key];
					$newProduct['quantity'] = $sale_invoice['uc_quality'][$key];
					$newProduct['unit_price'] = $sale_invoice['uc_unit_price'][$key];
					$newProduct['tax'] = $sale_invoice['uc_tax'][$key];
					$newProduct['tax_porcent'] = $sale_invoice['uc_tax_porcent'][$key];
					$newProduct['amount'] = $sale_invoice['uc_amount'][$key];
					$sale_invoice['products'][] = $newProduct;
				}
			}
			$sale_invoice['subtotal'] = $sale_invoice['in_sub_total'];
			$sale_invoice['total'] = $sale_invoice['in_total'];
			$tpl->assign( "invoice", $sale_invoice);
			
		} else if ($object->assgined == 'fktr_receipt') {
			$receipt = fktrPostTypeReceipts::get_receipt_data($object->id);
			$tpl->assign( "receipt", $receipt);
		}

		return $tpl;
	}
	public static function get_object_type($print_template) {
		$object_type = 'post';
		$is_taxonomy = taxonomy_exists($print_template['assigned']);
		if ($is_taxonomy) {
			$object_type = 'taxonomy';
		}
		$object_type = apply_filters('fktr_get_object_type_by_slug', $object_type);
		return $object_type;
	}
	public static function get_rand_object_id($object_type, $print_template) {
		$ret = false;
		if ($object_type == 'taxonomy') {
			$terms = get_terms( array(
			    'taxonomy' => $print_template['assigned'],
			    'hide_empty' => false,
			    'number' => 1,
			) );
			if(!is_wp_error($terms)) {
				if ($terms) {
					foreach ($terms as $t) {
						$ret = $t->term_id;
						break;
					}
				}
			}
		}
		if ($object_type == 'post') {
			$args = array(
			    'post_type' => $print_template['assigned'],
			    'post_status' => 'publish',
			    'posts_per_page' => 1,
			    'orderby' => 'rand'
			);
			$my_random_post = new WP_Query ( $args );
			while ( $my_random_post->have_posts () ) {
			  $my_random_post->the_post ();

			  $ret = get_the_ID();
		
			}

		}

		return $ret;

	}
	public static function get_id_by_assigned($assigned) {
		global $wpdb;
		$return = false;
		$sql = sprintf("SELECT p.ID, pm.meta_key, pm.meta_value as assigned FROM {$wpdb->posts} as p
				 LEFT JOIN {$wpdb->postmeta} as pm ON p.ID = pm.post_id
                 WHERE 
                 pm.meta_key = 'assigned'
				 AND p.post_status = 'publish'
				 AND p.post_type = 'fktr_email_template'
				 AND pm.meta_value = '%s'
                 GROUP BY p.ID 
				 LIMIT 1
			 ", $assigned);
		$r = $wpdb->get_results($sql, ARRAY_A);
		if (!empty($r)) {
			foreach ($r as $key => $value) {
				$return = $value['ID'];
			}

		}
		return $return;
	}
	public static function default_assigned($data) {
		$args = array(
			'public'   => true
		); 
		$output = 'objects'; // names or objects, note names is the default
		$operator = 'and'; // 'and' or 'or'
		$post_types = get_post_types($args, $output, $operator); 
		foreach ($post_types  as $post_type  ) {
			
			if (strpos($post_type->name, 'fktr') === false ) {
				continue;
			}
			if (empty($data[$post_type->name])) {
				$data[$post_type->name] =  $post_type->label;
			}
			
		}
		/*  DISABLE TAXONOMYS ON ASSIGNED TO
		$args = array(
		  	'public'   => true,
		); 
		$output = 'objects'; 
		$operator = 'and'; 
		$taxonomies = get_taxonomies( $args, $output, $operator ); 
		if ( $taxonomies ) {
		  	foreach ( $taxonomies  as $taxonomy ) {
		    	if (strpos($taxonomy->name, 'fktr') === false ) {
					continue;
				}
				if (empty($data[$taxonomy->name])) {
					$data[$taxonomy->name] =  $taxonomy->label;
				}
		  	}
		}
		*/
		return $data;
	}
	public static function setup() {
		
		$labels = array( 
			'name' => __( 'Email Templates', FAKTURO_TEXT_DOMAIN ),
			'singular_name' => __( 'Email Template', FAKTURO_TEXT_DOMAIN ),
			'add_new' => __( 'Add New', FAKTURO_TEXT_DOMAIN ),
			'add_new_item' => __( 'Add New Email Template', FAKTURO_TEXT_DOMAIN ),
			'edit_item' => __( 'Edit Email Template', FAKTURO_TEXT_DOMAIN ),
			'new_item' => __( 'New Email Template', FAKTURO_TEXT_DOMAIN ),
			'view_item' => __( 'View Email Template', FAKTURO_TEXT_DOMAIN ),
			'search_items' => __( 'Search Email Templates', FAKTURO_TEXT_DOMAIN ),
			'not_found' => __( 'No Email Templates found', FAKTURO_TEXT_DOMAIN ),
			'not_found_in_trash' => __( 'No Email Templates found in Trash', FAKTURO_TEXT_DOMAIN ),
			'parent_item_colon' => __( 'Parent Email Template:', FAKTURO_TEXT_DOMAIN ),
			'menu_name' => __( 'Email Templates', FAKTURO_TEXT_DOMAIN ),
		);
		$capabilities = array(
			'publish_post' => 'publish_fktr_email_template',
			'publish_posts' => 'publish_fktr_email_templates',
			'read_post' => 'read_fktr_email_template',
			'read_private_posts' => 'read_private_fktr_email_templates',
			'edit_post' => 'edit_fktr_email_template',
			'edit_published_posts' => 'edit_published_fktr_email_templates',
			'edit_private_posts' => 'edit_private_fktr_email_templates',
			'edit_posts' => 'edit_fktr_email_templates',
			'edit_others_posts' => 'edit_others_fktr_email_templates',
			'delete_post' => 'delete_fktr_email_template',
			'delete_posts' => 'delete_fktr_email_templates',
			'delete_published_posts' => 'delete_published_fktr_email_templates',
			'delete_private_posts' => 'delete_private_fktr_email_templates',
			'delete_others_posts' => 'delete_others_fktr_email_templates',
		);

		$args = array( 
			'labels' => $labels,
			'hierarchical' => false,
			'description' => 'Fakturo Email Templates',
			'supports' => array( 'title', 'editor'/* 'custom-fields' */),
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

		register_post_type( 'fktr_email_template', $args );

		
		add_filter('enter_title_here', array(__CLASS__, 'name_placeholder'),10,2);
		
		
	}
	public static function updated_messages( $messages ) {
		global $post, $post_ID;
		$messages['fktr_email_template'] = array(
			 0 => '', 
			 1 => __('Email template updated.', FAKTURO_TEXT_DOMAIN ),
			 2 => '',
			 3 => '',
			 4 => __( 'Email template updated.', FAKTURO_TEXT_DOMAIN ),
			 5 => '',
			 6 => __('Email template published.', FAKTURO_TEXT_DOMAIN ),
			 7 => __('Email template saved.', FAKTURO_TEXT_DOMAIN ),
			 8 => __('Email template submitted.', FAKTURO_TEXT_DOMAIN ),
			 9 => sprintf(__('Email template scheduled for: <strong>%1$s</strong>.', FAKTURO_TEXT_DOMAIN ), date_i18n( __( 'M j, Y @ G:i', FAKTURO_TEXT_DOMAIN ), strtotime( $post->post_date ) )),
			10 => __('Pending Email template.', FAKTURO_TEXT_DOMAIN ),
		);
		return $messages;
	}
	public static function name_placeholder( $title_placeholder , $post ) {
		if($post->post_type == 'fktr_email_template') {
			$title_placeholder = __('Your E-mail template name', FAKTURO_TEXT_DOMAIN );
			
		}
		return $title_placeholder;
	}


	public static function actions($actions, $post) {
	    //check for your post type
	    if ($post->post_type =="fktr_email_template"){
	       
	        $actions['show_email_template'] = '<a href="'.admin_url('admin-post.php?id='.$post->ID.'&action=show_email_template').'" target="_new">'.__( 'Preview', FAKTURO_TEXT_DOMAIN ).'</a>';
	        $actions['copy'] = '<a href="'.admin_url('admin.php?action=copy_email_template&post='.$post->ID.'').'" title="' . esc_attr(__("Clone this item", FAKTURO_TEXT_DOMAIN)) . '">' .  __('Copy', FAKTURO_TEXT_DOMAIN) . '</a>';
	       
	    }
	    return $actions;
	}
	public static function copy_email_template() {
		if (! ( isset( $_GET['post']) || isset( $_POST['post'])  || ( isset($_REQUEST['action']) && 'copy_email_template' == $_REQUEST['action'] ) ) ) {
			wp_die(__('No email template ID has been supplied!',  FAKTURO_TEXT_DOMAIN));
		}

		// Get the original post
		$id = (isset($_GET['post']) ? $_GET['post'] : $_POST['post']);
		$post = get_post($id);

		// Copy the post and insert it
		if (isset($post) && $post!=null) {
			if ($post->post_type != 'fktr_email_template') {
				return;
			}
			$prefix = "";
			$suffix = __("(Copy)",  FAKTURO_TEXT_DOMAIN) ;
			if (!empty($prefix)) $prefix.= " ";
			if (!empty($suffix)) $suffix = " ".$suffix;
			$status = 'publish';

			$new_post = array(
				'menu_order' => $post->menu_order,
				'guid' => $post->guid,
				'comment_status' => $post->comment_status,
				'ping_status' => $post->ping_status,
				'pinged' => $post->pinged,
				'post_author' => @$post->author,
				'post_content' => $post->post_content,
				'post_excerpt' => $post->post_excerpt,
				'post_mime_type' => $post->post_mime_type,
				'post_parent' => $post->post_parent,
				'post_password' => $post->post_password,
				'post_status' => $status,
				'post_title' => $prefix.$post->post_title.$suffix,
				'post_type' => $post->post_type,
				'to_ping' => $post->to_ping, 
				'post_date' => $post->post_date,
				'post_date_gmt' => get_gmt_from_date($post->post_date)
			);	

			$new_post_id = wp_insert_post($new_post);

			$post_meta_keys = get_post_custom_keys($post->ID);
			if (!empty($post_meta_keys)) {
				foreach ($post_meta_keys as $meta_key) {
					$meta_values = get_post_custom_values($meta_key, $post->ID);
					foreach ($meta_values as $meta_value) {

						$meta_value = maybe_unserialize($meta_value);
						update_post_meta($new_post_id, $meta_key, $meta_value);
					}
				}
			}
			

			if ($status == ''){
				// Redirect to the post list screen
				wp_redirect( admin_url( 'edit.php?post_type='.$post->post_type) );
			} else {
				// Redirect to the edit screen for the new draft post
				wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_post_id ) );
			}
			exit;

		} else {
			$post_type_obj = get_post_type_object( $post->post_type );
			wp_die(esc_attr(__('Copy email template failed, could not find original:',  FAKTURO_TEXT_DOMAIN)) . ' ' . $id);
		}
	}
	public static function styles() {
		global $post_type;
		if($post_type == 'fktr_email_template') {
			wp_enqueue_style('post-type-email-template',FAKTURO_PLUGIN_URL .'assets/css/post-type-email-template.css');	

		}
	}
	public static function scripts() {
		global $post_type, $post, $wp_locale, $locale;
		if($post_type == 'fktr_email_template') {
			wp_dequeue_script( 'autosave' );
			
			wp_enqueue_script( 'post-type-email-template', FAKTURO_PLUGIN_URL . 'assets/js/post-type-email-template.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );

			$preview_button = '<a  id="preview_button" class="button button-large" href="'.admin_url('admin-post.php?id='.$post->ID.'&action=show_email_template').'" target="_new" style="margin-left:5px;">'. __('Preview', FAKTURO_TEXT_DOMAIN) . '</a>';
			
			wp_localize_script('post-type-email-template', 'email_template_object',
				array(
						'ajax_url' => admin_url( 'admin-ajax.php' ),
						'preview_button' => $preview_button,
						'msg_save_before' => __('Save before to preview print template', FAKTURO_TEXT_DOMAIN),
						'msg_loading_var' => __('Loading vars...', FAKTURO_TEXT_DOMAIN),
					
				));
		
		}
		
	}
	public static function before_editor() {
        # Get the globals:
        global $post, $wp_meta_boxes;
        # Output the "advanced" meta boxes:
        do_meta_boxes( get_current_screen(), 'before_editor', $post );
        # Remove the initial "advanced" meta boxes:
        unset($wp_meta_boxes['post']['before_editor']);
    }


	public static function meta_boxes() {
		

		add_meta_box('fakturo-template-data-box', __('E-mail Template Data', FAKTURO_TEXT_DOMAIN ), array(__CLASS__, 'email_template_data_box'),'fktr_email_template','before_editor', 'high' );
		add_meta_box('fakturo-template-vars-box', __('E-mail Template Vars', FAKTURO_TEXT_DOMAIN ), array(__CLASS__, 'email_template_vars_box'),'fktr_email_template','normal', 'high' );
		
		
		do_action('add_ftkr_email_template_meta_boxes');
	}
	public static $array_sended = array();
	public static function print_vars_array($array, $index, $current_var) {
		foreach ($array as $key => $val) {
			if (is_array($val)) {
					$key_send = $key;
					if (is_numeric($key)) {
						$key_send = '<strong>ArrayToLoop</strong>';
					}
					self::print_vars_array($val, $index, $current_var.'.'.$key_send.'');
			} else {
				$key_var = array_search('{'.$current_var.'.'.$key.'}', self::$array_sended);
				if ($key_var === false) {
					self::$array_sended[] = '{'.$current_var.'.'.$key.'}';
					//echo '{'.$current_var.'.'.$key.'} <br/>';
				}
				
			}
		}
	}
	public static function get_vars_assigned() {
		$email_template = self::get_email_template_data($_POST['template_id']);
		$email_template['assigned'] = $_POST['assigned'];
		$object = new stdClass();
		$object->type = self::get_object_type($email_template);
		$object->id = self::get_rand_object_id($object->type, $email_template);
		$object->assgined = $email_template['assigned'];
		if ($object->id) {
			$tpl = new fktr_tpl;
			$tpl = apply_filters('fktr_email_template_assignment', $tpl, $object, $email_template);
			
			$index = 0;
			foreach ($tpl->var as $key => $val) {
				if (is_array($val)) {
					self::print_vars_array($val, $index, '$'.$key.'');
				} else {
					self::$array_sended[] = '{$'.$key.'}';
					
				}
			}
		}
		foreach (self::$array_sended as $v)  {
			echo $v.'</br>';
		}
		exit;
	}

	public static function email_template_vars_box() {
		global $post;
		$email_template = self::get_email_template_data($post->ID);
		$object = new stdClass();
		$object->type = self::get_object_type($email_template);
		$object->id = self::get_rand_object_id($object->type, $email_template);
		$object->assgined = $email_template['assigned'];
		$echoHtml = '';
		$echoHtml .= '<div>'.__('Vars with members <strong>ArrayToLoop</strong> means that they are list arrays and should be used in a <strong>Loop</strong>.', FAKTURO_TEXT_DOMAIN ) .'</div>';
		$echoHtml .= '<div id="vars_template_content">';
		if ($object->id) {
			$tpl = new fktr_tpl;
			$tpl = apply_filters('fktr_email_template_assignment', $tpl, $object, $email_template);
			
			$index = 0;
			foreach ($tpl->var as $key => $val) {
				if (is_array($val)) {
					self::print_vars_array($val, $index, '$'.$key.'');
				} else {
					self::$array_sended[] = '{$'.$key.'}';
				}
			}
		}
		foreach (self::$array_sended as $v)  {
			$echoHtml .= $v.'</br>';
		}
		$echoHtml .= '</div>';
		$echoHtml = apply_filters('fktr_email_template_vars_box', $echoHtml);
		echo $echoHtml;
		do_action('add_fktr_email_template_vars_box', $echoHtml);
	}
	
	public static function email_template_data_box() {
		global $post;
		$email_template = self::get_email_template_data($post->ID);

		$setting_system = get_option('fakturo_system_options_group', false);
		$array_assigned = apply_filters('fktr_assigned_email_template', array());
		$selectHtml = '<select name="assigned" id="assigned">
							<option value="-1" '.selected(-1, $email_template['assigned'], false).'> '.__('Select please', FAKTURO_TEXT_DOMAIN ) .' </option>';
		foreach ($array_assigned as $key => $value) {
			$selectHtml .= '<option value="'.$key.'" '.selected($key, $email_template['assigned'], false).'> '.$value .' </option>';
		}
		$selectHtml .= '</select>';
		$echoHtml = '<table>
					<tbody>
						<tr class="tr_fktr">
							<th><label for="description">'.__('Description', FAKTURO_TEXT_DOMAIN ) .'	</label></th>
							<td><input id="description" type="text" name="description" value="'.$email_template['description'].'" class="regular-text"></td>
						</tr>
						<tr class="tr_fktr">
							<th><label for="subject">'.__('Subject', FAKTURO_TEXT_DOMAIN ) .'	</label></th>
							<td><input id="subject" type="text" name="subject" value="'.$email_template['subject'].'" class="regular-text"></td>
						</tr>
						<tr class="tr_fktr">
							<th><label for="assigned">'.__('Assigned to', FAKTURO_TEXT_DOMAIN ) .'	</label></th>
							<td>'.$selectHtml.'</td>
						</tr>
					
				
			</tbody>
		</table>
	
		
		';
	
		$echoHtml = apply_filters('fktr_email_template_data_box', $echoHtml);
		echo $echoHtml;
		do_action('add_fktr_email_template_data_box', $echoHtml);
	}
	
	public static function clean_fields($fields) {
		$setting_system = get_option('fakturo_system_options_group', false);
		if (!isset($fields['description'])) {
			$fields['description'] = '';
		}
		if (!isset($fields['subject'])) {
			$fields['subject'] = '';
		}
		if (!isset($fields['assigned'])) {
			$fields['assigned'] = -1;
		}
		
		return $fields;
	}
	public static function before_save($fields) {
		$setting_system = get_option('fakturo_system_options_group', false);
		
		
		return $fields;
	}

	public static function default_fields($new_status, $old_status, $post ) {
		
		if( $post->post_type == 'fktr_email_template' && $old_status == 'new'){		
			$setting_system = get_option('fakturo_system_options_group', false);
			$fields = array();
			$fields['description'] = '';
			$fields['subject'] = '';
			$fields['assigned'] = -1;
			
			 
			$fields = apply_filters('fktr_clean_email_template_fields', $fields);

			foreach ( $fields as $field => $value ) {
				if ( !is_null( $value ) ) {
					$new = apply_filters( 'fktr_email_template_metabox_save_' . $field, $value );  //filtra cada campo antes de grabar
					update_post_meta( $post->ID, $field, $new );
				}
			}
		}
	}
	public static function get_email_template_data($template_id) {
		$custom_field_keys = get_post_custom($template_id);
		foreach ( $custom_field_keys as $key => $value ) {
			$custom_field_keys[$key] = maybe_unserialize($value[0]);
		}
		
		$custom_field_keys = apply_filters('fktr_clean_email_template_fields', $custom_field_keys );
		return $custom_field_keys;
	}
	public static function save($post_id, $post) {
		global $wpdb;
		
		
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX') && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) {
			return false;
		}

		if ( isset( $post->post_type ) && $post->post_type == 'revision' || $post->post_type!= 'fktr_email_template') {
			return false;
		}
		
		if ( ! current_user_can( 'manage_options', $post_id ) ) {
			return false;
		}
			
	
		$setting_system = get_option('fakturo_system_options_group', false);
		$fields = apply_filters('fktr_clean_email_template_fields',$_POST);
		$fields = apply_filters('fktr_email_template_before_save',$fields);
		
		
		foreach ($fields as $field => $value ) {
			
			if ( !is_null( $value ) ) {
				$new = apply_filters('fktr_email_template_metabox_save_' . $field, $value );  //filtra cada campo antes de grabar
				update_post_meta( $post_id, $field, $new );
				
			}
			
		}
		
		do_action( 'fktr_save_email_template', $post_id, $post );
		
	}
	
} 

endif;

$fktrPostTypeEmailTemplates = new fktrPostTypeEmailTemplates();

?>