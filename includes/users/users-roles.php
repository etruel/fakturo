<?php
/**
 * Description of users-roles.
 * @author esteban
 */

// Exit if accessed directly
if (!defined('ABSPATH'))  {
	exit;
}


if (!class_exists( 'fktrUserRoles' ) ) :
class fktrUserRoles {
	//Plugin capabilities
	private static $fakturo_manager_caps = array ();
	private static $fakturo_seller_caps = array ();
	
	public static function get_fakturo_manager_caps(){
		
		self::$fakturo_manager_caps = array (
		// more capabilities here
			'edit_fakturo_settings' => true,

			'read' => true,
			'upload_files' => true,
			'edit_files' => true,

			'remove_users' => true,
			'add_users' => true,
			'edit_users' => true,
			'list_users' => true,
			'create_users' => true,
			'delete_users' => true,
			
			
			
		);
		$taxonomies_caps = array();
		$args = array(
		  'public'   => false,
		  '_builtin' => false
		  
		); 
		$output = 'names'; 
		$operator = 'and';
		$taxonomies = get_taxonomies( $args, $output, $operator ); 
		if ($taxonomies) {
			foreach ($taxonomies  as $taxonomy) {
				if (strpos($taxonomy, 'fktr') !== false)  {
					$taxonomies_caps['manage_'.$taxonomy] = true;
					$taxonomies_caps['edit_'.$taxonomy] = true;
					$taxonomies_caps['delete_'.$taxonomy] = true;
					$taxonomies_caps['assign_'.$taxonomy] = true;
				}
			}
		}
		self::$fakturo_manager_caps = array_merge(self::$fakturo_manager_caps, $taxonomies_caps);
		
		
		
		$args = array( 'public' => false );
		$output = 'names'; // names or objects
		$post_types = get_post_types($args,$output);
		
		foreach ($post_types  as $post_type_name ) {
			if (strpos($post_type_name, 'fktr') !== false)  {
				$newArrayCaps = array();
				$cap_cpt = get_post_type_object($post_type_name)->cap;
				foreach ($cap_cpt as $c) {
					$newArrayCaps[$c] = true;
				}
				//error_log(print_r($newArrayCaps, true));
				self::$fakturo_manager_caps = array_merge(self::$fakturo_manager_caps, $newArrayCaps);
			}
		}
		self::$fakturo_manager_caps = apply_filters('fakturo_manager_caps', self::$fakturo_manager_caps);
		return self::$fakturo_manager_caps;
	}
	
	
	
	public static function get_fakturo_seller_caps(){ 
		self::$fakturo_seller_caps = array (
		// clients capabilities here
			'publish_fakturo_clients' => true,
			'read_fakturo_clients' => true,
			'edit_fakturo_client' => true,
			'edit_fakturo_clients' => true,
			'edit_published_fakturo_clients' => true,
			'delete_fakturo_client' => true,
			'delete_fakturo_clients' => true,
			'read' => true,
			'upload_files' => true,
			'edit_files' => true,
			'list_users' => true,
			'MailPress_manage_subscriptions' => false,
		);
		self::$fakturo_seller_caps = apply_filters('fakturo_seller_caps', self::$fakturo_seller_caps);
		return self::$fakturo_seller_caps;
	}


	public static function fakturo_map_meta_caps( $caps, $cap, $user_id, $args ) {
		global $post;
//		if( is_null($post) ) return $caps;
		/* If editing, deleting, or reading a fktr_product, get the post and post type object. */
		if ( 'edit_fktr_product' == $cap || 'delete_fktr_product' == $cap || 'read_fktr_product' == $cap ) {
			//$post = get_post($args[0]);
			$post_type = get_post_type_object( $post->post_type );

			/* Set an empty array for the caps. */
			$caps = array();
		}

		/* If editing a edit_fktr_product, assign the required capability. */
		if ( 'edit_fktr_product' == $cap ) {
			if ( $user_id == $post->post_author )
				$caps[] = $post_type->cap->edit_posts;
			else
				$caps[] = $post_type->cap->edit_others_posts;
		}

		/* If deleting a fktr_product, assign the required capability. */
		elseif ( 'delete_fktr_product' == $cap ) {
			if ( $user_id == $post->post_author )
				$caps[] = $post_type->cap->delete_posts;
			else
				$caps[] = $post_type->cap->delete_others_posts;
		}

		/* If reading a private fktr_product, assign the required capability. */
		elseif ( 'read_fktr_product' == $cap ) {

			if ( 'private' != $post->post_status )
				$caps[] = 'read';
			elseif ( $user_id == $post->post_author )
				$caps[] = 'read';
			else
				$caps[] = $post_type->cap->read_private_posts;
		}

		/* Return the capabilities required by the user. */
		return $caps;
	}

	
	public function __construct() {
		add_action('activated_plugin', array( __CLASS__,'activate' ), 10, 2);
		add_action('deactivated_plugin', array( __CLASS__,'deactivate' ), 10, 2 );

		add_filter('login_redirect', array( __CLASS__ ,'fakturo_login_redirect'), 10, 3);

		add_action('admin_init', array( __CLASS__,'admin_init' ), 10 );
	}

	public static function admin_init() {
		//global $current_user;
		if (get_current_user_id() && ( current_user_can('fakturo_manager') ||  current_user_can('fakturo_seller') ) ) {
			//Remove Wordpress core dashboard widgets
			add_action('wp_dashboard_setup',  array( __CLASS__, 'remove_dashboard_widgets' ));
			//Remove Media Wordpress menu
			add_action( 'admin_menu', array( __CLASS__, 'remove_menus' ) );
			
//			add_filter( 'map_meta_cap', array( __CLASS__, 'fakturo_map_meta_caps'), 99, 4 );
		}
	}

	static function remove_menus() {
		remove_menu_page( 'upload.php' );			
	}

	public static function remove_dashboard_widgets() {
		global $wp_meta_boxes;
		unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_drafts']);
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);
		unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
		unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_activity']);
	}

	public static function fakturo_login_redirect($redirect_url, $POST_redirect_url, $user) {
		if ( isset($user->ID) and ( user_can($user, 'fakturo_manager') || user_can($user, 'fakturo_seller') ) ) {
			return admin_url('/admin.php?page=fakturo_dashboard');
			
		}
		return $redirect_url;
	}
	
	public static function activate($plugin, $network_activation) {
		global $wp_roles;
		if ($plugin != 'fakturo/fakturo.php') {
			return true;
		}
		add_role( 'fakturo_manager', __( 'Manager', FAKTURO_TEXT_DOMAIN ), self::get_fakturo_manager_caps());
		add_role( 'fakturo_seller', __( 'Salesman', FAKTURO_TEXT_DOMAIN ), self::get_fakturo_seller_caps());
		
		update_option('fktr_last_mananger_caps', self::$fakturo_manager_caps);
		update_option('fktr_last_seller_caps', self::$fakturo_seller_caps);
		//Add capabilities to admin (if don't want to allow admins to edits Seller events can be disabled from Settings ;-)
		foreach(self::$fakturo_manager_caps as $key => $value) {
			$wp_roles->add_cap( 'administrator', $key, $value );
		}
	}

	
	public static function deactivate($plugin, $network_activation) {
		global $wp_roles;
		if ($plugin != 'fakturo/fakturo.php') {
			return true;
		}
		remove_role( 'fakturo_manager'); 
		remove_role( 'fakturo_seller'); 
		foreach(self::$fakturo_manager_caps as $key => $value) {
			$adm_cap = array('read','upload_files','edit_files','manage_options',
				'promote_users','remove_users','add_users','edit_users',
				'list_users','create_users','delete_users',);
			if(!in_array($key, $adm_cap )){
				$wp_roles->remove_cap( 'administrator', $key, $value );
			}
		}
	}

	public static function regenerate($update = false) {
		global $wp_roles;
		remove_role( 'fakturo_manager'); 
		remove_role( 'fakturo_seller'); 

		$fktr_last_mananger_caps = get_option('fktr_last_mananger_caps', false);
		if (!$fktr_last_mananger_caps || !$update) {
			$fktr_last_mananger_caps = self::get_fakturo_manager_caps();
		}
		foreach($fktr_last_mananger_caps as $key => $value) {
			$adm_cap = array('read','upload_files','edit_files','manage_options',
				'promote_users','remove_users','add_users','edit_users',
				'list_users','create_users','delete_users',);
			if(!in_array($key, $adm_cap )) {
				$wp_roles->remove_cap( 'administrator', $key, $value );
			}
		}

		self::activate();
		
	}
	
}
endif;
$fktrUserRoles = new fktrUserRoles();
