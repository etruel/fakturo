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

		add_action( 'admin_post_reset_email_template', array(__CLASS__, 'reset_email_template'));
		add_action( 'post_submitbox_misc_actions', array(__CLASS__, 'submitbox') );
	}
	public static function reset_email_template() {
		$template_id = fktr_guard::item('reset_email_template', 'fktr_email_template', 'manage_options')->ID;
		
		// Obtener los datos actuales
		$email_template = self::get_email_template_data($template_id);
		
		// Guardar los datos actuales antes de cualquier cambio
		$current_description = isset($email_template['description']) ? $email_template['description'] : '';
		$current_subject = isset($email_template['subject']) ? $email_template['subject'] : '';
		$current_assigned = isset($email_template['assigned']) ? $email_template['assigned'] : '';
		
		// Obtener la plantilla por defecto manteniendo el assigned actual
		$new_content = self::get_default_template_by_assigned($current_assigned);
		if ($new_content === '') {
			fktrNotices::add(array('below-h2' => false, 'text' => __('Default template not found.', 'fakturo' )));
			wp_redirect(admin_url('post.php?post='.$template_id.'&action=edit'));
			exit;
		}
		if ($current_subject === '') {
			$current_subject = self::get_default_subject($current_assigned);
		}
	
		// Actualizar solo el contenido
		$args_template = array(
			'ID' => $template_id,
			'post_content' => $new_content
		);
		wp_update_post($args_template);
	
		// Actualizar el meta 'content' pero mantener el resto de metadatos
		update_post_meta($template_id, 'content', $new_content);
		update_post_meta($template_id, 'description', $current_description);
		update_post_meta($template_id, 'subject', $current_subject);
		update_post_meta($template_id, 'assigned', $current_assigned);
	
		fktrNotices::add(array('below-h2' => false, 'text' => __('Template content has been reset to default.', 'fakturo' )));
		wp_redirect(admin_url('post.php?post='.$template_id.'&action=edit'));
		exit;
	}
	public static function submitbox() {
		global $post;
		if ($post->post_type != 'fktr_email_template') {
			return true;
		}
		$preview_button = '<a  id="preview_button" class="button button-large" href="'.fktr_guard::url('show_email_template', $post->ID).'" target="_new" style="margin-left:5px;">'. __('Preview', 'fakturo') . '</a>';	

		$reset_url = fktr_guard::url('reset_email_template', $post->ID);
		$reset_button = '<a  id="reset_button" class="button button-large" href="'.$reset_url.'" style="margin:5px;">'. __('Reset to default', 'fakturo') . '</a>';


		$echoHtml = '
		
		<div class="misc-pub-section fktr_email_template_preview_button">
			'.$preview_button.'
		</div>
		<div class="misc-pub-section fktr_email_template_reset_button">
			'.$reset_button.'
		</div>

		';
		echo $echoHtml;

	}
	public static function show_email_template() {
		$template_id = fktr_guard::item('show_email_template', 'fktr_email_template')->ID;
		
		$email_template = self::get_email_template_data($template_id);
		if (!isset($email_template['assigned'])) {
			wp_redirect(admin_url('post.php?post='.$template_id.'&action=edit'));
			exit;
		}
		if ($email_template['assigned'] == -1) {
			wp_die('<h3>'.__('This e-mail template has no assigned object.', 'fakturo' ).'</h3>');
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
			echo '<div style="padding: 15px 10px; font-size: 14px; border-radius: 2px; border: 1px solid #ddd;">'.__('E-mail template', 'fakturo' ).': '.esc_html(get_post_field('post_title', $template_id)).'</div><br/>';
			echo '<div style="padding: 15px 10px; font-size: 14px; border-radius: 2px; border: 1px solid #ddd;">'.__('E-mail Subject', 'fakturo' ).': ['.$htmlSubject.']</div><br/>';
			echo '<div style="padding: 15px 10px; font-size: 14px; border-radius: 2px; border: 1px solid #ddd;">'.__('E-mail Body', 'fakturo' ).': ['.$htmlContent.']</div><br/>';

			
			exit();
		}
		wp_die('<h3>'.__('Could not find any object related to this e-mail template', 'fakturo' ).'</h3>');
		
	}
	public static function assignment($tpl, $object, $default_template) {
		if (!$default_template) {
			$id_email_template = self::get_id_by_assigned($object->assgined);
			if ($id_email_template) {
				$default_template = self::get_email_template_data($id_email_template);
			}
		}
		return fktrPostTypePrintTemplates::assignment($tpl, $object, $default_template);
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
	/**
	 * Ids of the published templates assigned to an object type, newest first.
	 */
	public static function get_ids_by_assigned($assigned) {
		global $wpdb;
		return array_map('intval', $wpdb->get_col($wpdb->prepare("SELECT p.ID FROM {$wpdb->posts} as p
				 INNER JOIN {$wpdb->postmeta} as pm ON p.ID = pm.post_id
				 WHERE pm.meta_key = 'assigned'
				 AND p.post_status = 'publish'
				 AND p.post_type = 'fktr_email_template'
				 AND pm.meta_value = %s
				 GROUP BY p.ID
				 ORDER BY p.ID DESC
			 ", $assigned)));
	}

	/**
	 * The template in use for an object type: the newest published one.
	 */
	public static function get_id_by_assigned($assigned) {
		$ids = self::get_ids_by_assigned($assigned);
		return empty($ids) ? false : $ids[0];
	}

	public static function duplicates_notice($assigned, $post_id) {
		if ($assigned == -1) {
			return '';
		}
		$ids = self::get_ids_by_assigned($assigned);
		if (count($ids) < 2 || !in_array((int) $post_id, $ids)) {
			return '';
		}
		$text = sprintf(__('There are %1$d published templates for this object. The one in use is "%2$s" (#%3$d), the newest one. Unpublish or delete the others to avoid confusion.', 'fakturo'), count($ids), get_post_field('post_title', $ids[0]), $ids[0]);
		return '<div class="notice notice-warning inline"><p>' . esc_html($text) . '</p></div>';
	}

	public static function default_assigned($data) {
		$args = array(
			'public'   => false
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
			'name' => __( 'Email Templates', 'fakturo' ),
			'singular_name' => __( 'Email Template', 'fakturo' ),
			'add_new' => __( 'Add New', 'fakturo' ),
			'add_new_item' => __( 'Add New Email Template', 'fakturo' ),
			'edit_item' => __( 'Edit Email Template', 'fakturo' ),
			'new_item' => __( 'New Email Template', 'fakturo' ),
			'view_item' => __( 'View Email Template', 'fakturo' ),
			'search_items' => __( 'Search Email Templates', 'fakturo' ),
			'not_found' => __( 'No Email Templates found', 'fakturo' ),
			'not_found_in_trash' => __( 'No Email Templates found in Trash', 'fakturo' ),
			'parent_item_colon' => __( 'Parent Email Template:', 'fakturo' ),
			'menu_name' => __( 'Email Templates', 'fakturo' ),
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

		register_post_type( 'fktr_email_template', $args );

		
		add_filter('enter_title_here', array(__CLASS__, 'name_placeholder'),10,2);
		
		
	}
	public static function updated_messages( $messages ) {
		global $post, $post_ID;
		$messages['fktr_email_template'] = array(
			 0 => '', 
			 1 => __('Email template updated.', 'fakturo' ),
			 2 => '',
			 3 => '',
			 4 => __( 'Email template updated.', 'fakturo' ),
			 5 => '',
			 6 => __('Email template published.', 'fakturo' ),
			 7 => __('Email template saved.', 'fakturo' ),
			 8 => __('Email template submitted.', 'fakturo' ),
			 9 => sprintf(__('Email template scheduled for: <strong>%1$s</strong>.', 'fakturo' ), date_i18n( __( 'M j, Y @ G:i', 'fakturo' ), strtotime( $post->post_date ) )),
			10 => __('Pending Email template.', 'fakturo' ),
		);
		return $messages;
	}
	public static function name_placeholder( $title_placeholder , $post ) {
		if($post->post_type == 'fktr_email_template') {
			$title_placeholder = __('Your E-mail template name', 'fakturo' );
			
		}
		return $title_placeholder;
	}


	public static function actions($actions, $post) {
	    //check for your post type
	    if ($post->post_type =="fktr_email_template"){
	       
	        $actions['show_email_template'] = '<a href="'.fktr_guard::url('show_email_template', $post->ID).'" target="_new">'.__( 'Preview', 'fakturo' ).'</a>';
	        $actions['copy'] = '<a href="'.fktr_guard::url('copy_email_template', $post->ID, array(), 'admin.php', 'post').'" title="' . esc_attr(__("Clone this item", 'fakturo')) . '">' .  __('Copy', 'fakturo') . '</a>';
	       
	    }
	    return $actions;
	}
	public static function copy_email_template() {
		// Get the original post
		$post = fktr_guard::item('copy_email_template', 'fktr_email_template', 'manage_options', 'post');

		// Copy the post and insert it
		if (isset($post) && $post!=null) {
			if ($post->post_type != 'fktr_email_template') {
				return;
			}
			$prefix = "";
			$suffix = __("(Copy)",  'fakturo') ;
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
			wp_die(esc_attr(__('Copy email template failed, could not find original:',  'fakturo')) . ' ' . $id);
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

			$preview_button = '<a  id="preview_button" class="button button-large" href="'.fktr_guard::url('show_email_template', $post->ID).'" target="_new" style="margin-left:5px;">'. __('Preview', 'fakturo') . '</a>';
			
			wp_localize_script('post-type-email-template', 'email_template_object',
				array(
						'ajax_url' => admin_url( 'admin-ajax.php' ),
						'preview_button' => $preview_button,
						'msg_save_before' => __('Save before to preview print template', 'fakturo'),
						'msg_loading_var' => __('Loading vars...', 'fakturo'),
						'msg_reset' => __('This will remove all your modified data. Are you sure?', 'fakturo'),
						'msg_before_reset' => __('Save before to reset to default.', 'fakturo'),
					
				));
			wp_localize_script('post-type-email-template', 'fktr_default_template', fktrDefaultTemplates::script_strings('email'));
		
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
		

		add_meta_box('fakturo-template-data-box', __('E-mail Template Data', 'fakturo' ), array(__CLASS__, 'email_template_data_box'),'fktr_email_template','before_editor', 'high' );
		add_meta_box('fakturo-template-vars-box', __('E-mail Template Vars', 'fakturo' ), array(__CLASS__, 'email_template_vars_box'),'fktr_email_template','normal', 'high' );
		
		
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
		fktr_guard::ajax('edit_fktr_email_templates');
		$email_template = self::get_email_template_data(fktr_guard::post_id(isset($_POST['template_id']) ? $_POST['template_id'] : 0, 'fktr_email_template'));
		$email_template['assigned'] = isset($_POST['assigned']) ? sanitize_key($_POST['assigned']) : -1;
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
		$echoHtml .= '<div>'.__('Vars with members <strong>ArrayToLoop</strong> means that they are list arrays and should be used in a <strong>Loop</strong>.', 'fakturo' ) .'</div>';
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
							<option value="-1" '.selected(-1, $email_template['assigned'], false).'> '.__('Select please', 'fakturo' ) .' </option>';
		foreach ($array_assigned as $key => $value) {
			$selectHtml .= '<option value="'.esc_attr($key).'" '.selected($key, $email_template['assigned'], false).'> '.esc_html($value).' </option>';
		}
		$selectHtml .= '</select>';
		$echoHtml = self::duplicates_notice($email_template['assigned'], $post->ID).'<table>
					<tbody>
						<tr class="tr_fktr">
							<th><label for="description">'.__('Description', 'fakturo' ) .'	</label></th>
							<td><input id="description" type="text" name="description" value="'.esc_attr($email_template['description']).'" class="regular-text"></td>
						</tr>
						<tr class="tr_fktr">
							<th><label for="subject">'.__('Subject', 'fakturo' ) .'	</label></th>
							<td><input id="subject" type="text" name="subject" value="'.esc_attr($email_template['subject']).'" class="regular-text"></td>
						</tr>
						<tr class="tr_fktr">
							<th><label for="assigned">'.__('Assigned to', 'fakturo' ) .'	</label></th>
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
		if (!isset($fields['content'])) {
			$fields['content'] = '';
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
	public static function get_default_template() {
		$default_email_templates = array();
		$args = array(
			'public'   => false
		); 
		$output = 'objects'; 
		$operator = 'and'; 
		$post_types = get_post_types($args, $output, $operator); 
		foreach ($post_types  as $post_type  ) {
			if (strpos($post_type->name, 'fktr') === false ) {
				continue;
			}
			if (empty($default_email_templates[$post_type->name])) {
				$default_email_templates[$post_type->name] = FAKTURO_PLUGIN_DIR.'templates/'.$post_type->name.'-email-default.html';
			}
			
		}

		$default_email_templates = apply_filters('fktr_default_email_template_paths', $default_email_templates);
		return $default_email_templates;
	}
	public static function get_default_subject($assigned) {
		$subjects = array(
			'fktr_sale'				=> __('Invoice {$invoice.post_title} from {$company.name}', 'fakturo'),
			'fktr_receipt'			=> __('Receipt {$receipt.post_title} from {$company.name}', 'fakturo'),
			'fktr_client'			=> __('A message from {$company.name}', 'fakturo'),
			'fktr_provider'			=> __('A message from {$company.name}', 'fakturo'),
			'fktr_product'			=> __('{$product.post_title} - {$company.name}', 'fakturo'),
			'fktr_print_template'	=> __('Print template: {$print_template.post_title}', 'fakturo'),
			'fktr_email_template'	=> __('E-mail template: {$email_template.post_title}', 'fakturo'),
		);
		$subjects = apply_filters('fktr_default_email_subjects', $subjects);
		return isset($subjects[$assigned]) ? $subjects[$assigned] : '';
	}
	public static function get_default_template_by_assigned($assigned) {
		$ret = '';
		$default_templates = self::get_default_template();
		if (!empty($default_templates[$assigned])) {
			if (file_exists($default_templates[$assigned])) {
				$ret = file_get_contents($default_templates[$assigned]);
			}
		}
		return $ret;
	}

	public static function get_email_template_data($template_id) {
		$custom_field_keys = get_post_custom($template_id);
		foreach ( $custom_field_keys as $key => $value ) {
			$custom_field_keys[$key] = maybe_unserialize($value[0]);
		}
		
		$custom_field_keys = apply_filters('fktr_clean_email_template_fields', $custom_field_keys );
		$custom_field_keys = fktr_post_columns($custom_field_keys, $template_id);
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
		
		if ( ! fktr_guard::can_save( $post_id, 'manage_options' ) ) {
			return false;
		}
		if ( ( defined( 'FKTR_STOP_PROPAGATION') && FKTR_STOP_PROPAGATION ) ) {
			return false;
		}	
	
		$setting_system = get_option('fakturo_system_options_group', false);
		$fields = apply_filters('fktr_clean_email_template_fields',$_POST);
		$fields = apply_filters('fktr_email_template_before_save',$fields);
		
		
		foreach (fktr_guard::fields($fields, 'email_template') as $field => $value ) {
			
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