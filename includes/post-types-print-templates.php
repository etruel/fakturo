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

		$echoHtml = '<table>
					<tbody>
						<tr class="tr_fktr">
						<th><label for="pdescription">'.__('Description', FAKTURO_TEXT_DOMAIN ) .'	</label></th>
						<td><input id="pdescription" type="text" name="pdescription" value="'.$print_template['pdescription'].'" class="regular-text"></td>
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
		if (!isset($fields['pdescription'])) {
			$fields['pdescription'] = '';
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
			$fields['pdescription'] = '';
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