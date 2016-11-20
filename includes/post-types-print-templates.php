<?php


// Exit if accessed directly
if (!defined('ABSPATH'))  {
	exit;
}


if ( ! class_exists('fktrPostTypePrintTemplates') ) :
class fktrPostTypePrintTemplates {
	function __construct() {
		
		add_action( 'init', array(__CLASS__, 'setup'), 1 );
		add_action( 'activated_plugin', array(__CLASS__, 'setup'), 1 );
		
		add_action('transition_post_status', array(__CLASS__, 'default_fields'), 10, 3);
		add_action('save_post', array(__CLASS__, 'save'), 99, 2 );
		
		add_action( 'admin_print_scripts-post-new.php', array(__CLASS__,'scripts'), 11 );
		add_action( 'admin_print_scripts-post.php', array(__CLASS__,'scripts'), 11 );
		
		add_action('admin_print_styles-post-new.php', array(__CLASS__,'styles'));
		add_action('admin_print_styles-post.php', array(__CLASS__,'styles'));
		

		
		add_filter('fktr_clean_print_template_fields', array(__CLASS__, 'clean_fields'), 10, 1);
		add_filter('fktr_print_template_before_save', array(__CLASS__, 'before_save'), 10, 1);


		add_filter( 'post_updated_messages', array(__CLASS__, 'updated_messages') );

		add_filter('fktr_assigned_print_template', array(__CLASS__, 'default_assigned'), 10, 1);
		add_filter('fktr_print_template_assignment', array(__CLASS__, 'assignment'), 10, 2);
		

		add_action( 'admin_post_show_print_template', array(__CLASS__, 'show_print_template'));
		add_filter('post_row_actions', array(__CLASS__, 'actions'), 10, 2);
	}
	public static function actions($actions, $post){
	    //check for your post type
	    if ($post->post_type =="fktr_print_template"){
	       
	        $actions['show_print_template'] = '<a href="'.admin_url('admin-post.php?id='.$post->ID.'&action=show_print_template').'" target="_blank">'.__( 'Preview', FAKTURO_TEXT_DOMAIN ).'</a>';
	       
	    }
	    return $actions;
	}
	public static function assignment($tpl, $object) {
		$company = get_option('fakturo_info_options_group', array());
		$company['img_url'] = $company['url'];
		$tpl->assign( "company", $company);
		
		
		return $tpl;
	}
	public static function show_print_template() {
		$template_id = $_REQUEST['id'];
		if (empty($template_id)) {
			echo 'Invalid print template id.';
			wp_die();
		}
		
		$print_template = self::get_print_template_data($template_id);
		if (!isset($print_template['assigned'])) {
			wp_redirect(admin_url('post.php?post='.$template_id.'&action=edit'));
			exit;
		}
		if ($print_template['assigned'] == -1) {
			wp_redirect(admin_url('post.php?post='.$template_id.'&action=edit'));
			exit;
		}
		
		$object = new stdClass();
		$object->type = self::get_object_type($print_template);
		$object->id = self::get_rand_object_id($object->type, $print_template);
		if ($object->id) {
			$tpl = new fktr_tpl;
			$tpl = apply_filters('fktr_print_template_assignment', $tpl, $object);
			$html = $tpl->fromString($print_template['content']);
			echo $html;
			exit();
		}
		wp_die('<h3>'.__('Could not find any object related to this print template').'</h3>') ;
		
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
	public static function get_object_type($print_template) {
		$object_type = 'post';
		$is_taxonomy = taxonomy_exists($print_template['assigned']);
		if ($is_taxonomy) {
			$object_type = 'taxonomy';
		}
		$object_type = apply_filters('fktr_get_object_type_by_slug', $object_type);
		return $object_type;
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

		$args = array(
		  	'public'   => true,
		); 
		$output = 'objects'; // names or objects, note names is the default
		$operator = 'and'; // 'and' or 'or'
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
		return $data;
	}
	
	public static function setup() {
		
		$labels = array( 
			'name' => __( 'Print Templates', FAKTURO_TEXT_DOMAIN ),
			'singular_name' => __( 'Print Template', FAKTURO_TEXT_DOMAIN ),
			'add_new' => __( 'Add New', FAKTURO_TEXT_DOMAIN ),
			'add_new_item' => __( 'Add New Print Template', FAKTURO_TEXT_DOMAIN ),
			'edit_item' => __( 'Edit Print Template', FAKTURO_TEXT_DOMAIN ),
			'new_item' => __( 'New Print Template', FAKTURO_TEXT_DOMAIN ),
			'view_item' => __( 'View Print Template', FAKTURO_TEXT_DOMAIN ),
			'search_items' => __( 'Search Print Templates', FAKTURO_TEXT_DOMAIN ),
			'not_found' => __( 'No Print Templates found', FAKTURO_TEXT_DOMAIN ),
			'not_found_in_trash' => __( 'No Print Templates found in Trash', FAKTURO_TEXT_DOMAIN ),
			'parent_item_colon' => __( 'Parent Print Template:', FAKTURO_TEXT_DOMAIN ),
			'menu_name' => __( 'Print Templates', FAKTURO_TEXT_DOMAIN ),
		);
		$capabilities = array(
			'publish_post' => 'publish_fktr_print_template',
			'publish_posts' => 'publish_fktr_print_templates',
			'read_post' => 'read_fktr_print_template',
			'read_private_posts' => 'read_private_fktr_print_templates',
			'edit_post' => 'edit_fktr_print_template',
			'edit_published_posts' => 'edit_published_fktr_print_templates',
			'edit_private_posts' => 'edit_private_fktr_print_templates',
			'edit_posts' => 'edit_fktr_print_templates',
			'edit_others_posts' => 'edit_others_fktr_print_templates',
			'delete_post' => 'delete_fktr_print_template',
			'delete_posts' => 'delete_fktr_print_templates',
			'delete_published_posts' => 'delete_published_fktr_print_templates',
			'delete_private_posts' => 'delete_private_fktr_print_templates',
			'delete_others_posts' => 'delete_others_fktr_print_templates',
		);

		$args = array( 
			'labels' => $labels,
			'hierarchical' => false,
			'description' => 'Fakturo Print Templates',
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

		register_post_type( 'fktr_print_template', $args );

		
		add_filter('enter_title_here', array(__CLASS__, 'name_placeholder'),10,2);
		
		
		
	}
	
	

	public static function updated_messages( $messages ) {
		global $post, $post_ID;
		$messages['fktr_print_template'] = array(
			 0 => '', 
			 1 => __('Print template updated.', FAKTURO_TEXT_DOMAIN ),
			 2 => '',
			 3 => '',
			 4 => __( 'Print template updated.', FAKTURO_TEXT_DOMAIN ),
			 5 => '',
			 6 => __('Print template published.', FAKTURO_TEXT_DOMAIN ),
			 7 => __('Print template saved.', FAKTURO_TEXT_DOMAIN ),
			 8 => __('Print template submitted.', FAKTURO_TEXT_DOMAIN ),
			 9 => sprintf(__('Print template scheduled for: <strong>%1$s</strong>.', FAKTURO_TEXT_DOMAIN ), date_i18n( __( 'M j, Y @ G:i', FAKTURO_TEXT_DOMAIN ), strtotime( $post->post_date ) )),
			10 => __('Pending Print template.', FAKTURO_TEXT_DOMAIN ),
		);
		return $messages;
	}
	public static function name_placeholder( $title_placeholder , $post ) {
		if($post->post_type == 'fktr_print_template') {
			$title_placeholder = __('Your print template name', FAKTURO_TEXT_DOMAIN );
			
		}
		return $title_placeholder;
	}

	public static function styles() {
		global $post_type;
		if($post_type == 'fktr_print_template') {
			wp_enqueue_style('post-type-sales',FAKTURO_PLUGIN_URL .'assets/css/post-type-print-template.css');	
			wp_enqueue_style( 'wpecf7vb-codemirror', FAKTURO_PLUGIN_URL . 'assets/codemirror/css/codemirror.css');

			wp_enqueue_style( 'wpecf7vb-monokai', FAKTURO_PLUGIN_URL . 'assets/codemirror/css/monokai.css');
			wp_enqueue_style( 'wpecf7vb-colbat',  FAKTURO_PLUGIN_URL . 'assets/codemirror/css/colbat.css');
			wp_enqueue_style( 'wpecf7vb-blackboard', FAKTURO_PLUGIN_URL . 'assets/codemirror/css/blackboard.css');
		}
	}
	public static function scripts() {
		global $post_type, $post, $wp_locale, $locale;
		if($post_type == 'fktr_print_template') {
			wp_dequeue_script( 'autosave' );
			
			wp_enqueue_script( 'post-type-print-template', FAKTURO_PLUGIN_URL . 'assets/js/post-type-print-template.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );

			wp_enqueue_script( 'wpecf7vb-mirrorcode',	FAKTURO_PLUGIN_URL . 'assets/codemirror/js/codemirror.js', array( 'jquery', 'post-type-print-template' ), WPE_FAKTURO_VERSION, true  );
			wp_enqueue_script( 'wpecf7vb-javascript', FAKTURO_PLUGIN_URL . 'assets/codemirror/js/javascript.js', array( 'wpecf7vb-mirrorcode' ), WPE_FAKTURO_VERSION, true  );
			wp_enqueue_script( 'wpecf7vb-xml', FAKTURO_PLUGIN_URL . 'assets/codemirror/js/xml.js', array( 'wpecf7vb-mirrorcode' ), WPE_FAKTURO_VERSION, true  );
			wp_enqueue_script( 'wpecf7vb-css', FAKTURO_PLUGIN_URL . 'assets/codemirror/js/css.js', array( 'wpecf7vb-mirrorcode' ), WPE_FAKTURO_VERSION, true  );
			wp_enqueue_script( 'wpecf7vb-htmlmixed', FAKTURO_PLUGIN_URL . 'assets/codemirror/js/htmlmixed.js', array( 'wpecf7vb-mirrorcode','wpecf7vb-xml' ), WPE_FAKTURO_VERSION, true  );

			
			
			wp_localize_script('ppost-type-print-template', 'print_template_object',
				array(
						'ajax_url' => admin_url( 'admin-ajax.php' )
					
				));
		
		}
		
	}
	
	public static function meta_boxes() {
		

		add_meta_box('fakturo-invoice-data-box', __('Print Template Data', FAKTURO_TEXT_DOMAIN ), array(__CLASS__, 'print_template_data_box'),'fktr_print_template','normal', 'high' );
		
		do_action('add_ftkr_sale_meta_boxes');
	}
	
	public static function print_template_data_box() {
		global $post;
		$screen = get_current_screen();
		
		$print_template = self::get_print_template_data($post->ID);
		


		$setting_system = get_option('fakturo_system_options_group', false);
		$array_assigned = apply_filters('fktr_assigned_print_template', array());
		$selectHtml = '<select name="assigned" id="assigned">
							<option value="-1" '.selected(-1, $print_template['assigned'], false).'> '.__('Select please', FAKTURO_TEXT_DOMAIN ) .' </option>';
		foreach ($array_assigned as $key => $value) {
			$selectHtml .= '<option value="'.$key.'" '.selected($key, $print_template['assigned'], false).'> '.$value .' </option>';
		}
		$selectHtml .= '</select>';
		$echoHtml = '<table>
					<tbody>
						<tr class="tr_fktr">
							<th><label for="description">'.__('Description', FAKTURO_TEXT_DOMAIN ) .'	</label></th>
							<td><input id="description" type="text" name="description" value="'.$print_template['description'].'" class="regular-text"></td>
						</tr>
						<tr class="tr_fktr">
							<th><label for="assigned">'.__('Assigned to', FAKTURO_TEXT_DOMAIN ) .'	</label></th>
							<td>'.$selectHtml.'</td>
						</tr>
					
				
			</tbody>
		</table>
		<br/>
		<div class="print_template_editors">
			<div class="wpecf7vb_col" id="print_template_visualeditor">
								
			</div>
		</div>
		<textarea name="content" cols="100" rows="24" id="content" class="">'.$print_template['content'].'</textarea>

		';
	
		$echoHtml = apply_filters('fktr_print_template_client_box', $echoHtml);
		echo $echoHtml;
		do_action('add_fktr_print_template_client_box', $echoHtml);
		
	}
	
	
	
	public static function clean_fields($fields) {
		$setting_system = get_option('fakturo_system_options_group', false);
		if (!isset($fields['description'])) {
			$fields['description'] = '';
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
		
		if( $post->post_type == 'fktr_print_template' && $old_status == 'new'){		
			$setting_system = get_option('fakturo_system_options_group', false);
			$fields = array();
			$fields['description'] = '';
			$fields['content'] = '';
			$fields['assigned'] = -1;
			
			 
			$fields = apply_filters('fktr_clean_print_template_fields', $fields);

			foreach ( $fields as $field => $value ) {
				if ( !is_null( $value ) ) {
					$new = apply_filters( 'fktr_print_template_metabox_save_' . $field, $value );  //filtra cada campo antes de grabar
					update_post_meta( $post->ID, $field, $new );
				}
			}
		}
	}
	public static function get_print_template_data($sale_id) {
		$custom_field_keys = get_post_custom($sale_id);
		foreach ( $custom_field_keys as $key => $value ) {
			$custom_field_keys[$key] = maybe_unserialize($value[0]);
		}
		
		$custom_field_keys = apply_filters('fktr_clean_print_template_fields', $custom_field_keys );
		return $custom_field_keys;
	}
	
	

	
	public static function save($post_id, $post) {
		global $wpdb;
		
		
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX') && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) {
			return false;
		}

		if ( isset( $post->post_type ) && $post->post_type == 'revision' || $post->post_type!= 'fktr_print_template') {
			return false;
		}
		
		if ( ! current_user_can( 'manage_options', $post_id ) ) {
			return false;
		}
		
	
		$setting_system = get_option('fakturo_system_options_group', false);
		$fields = apply_filters('fktr_clean_print_template_fields',$_POST);
		$fields = apply_filters('fktr_print_template_before_save',$fields);
		
		
		foreach ($fields as $field => $value ) {
			
			if ( !is_null( $value ) ) {
				$new = apply_filters('fktr_print_template_metabox_save_' . $field, $value );  //filtra cada campo antes de grabar
				update_post_meta( $post_id, $field, $new );
				
			}
			
		}
		
		do_action( 'fktr_save_print_template', $post_id, $post );
		
	}
	
	
} 

endif;

$fktrPostTypePrintTemplates = new fktrPostTypePrintTemplates();

?>