<?php
/**
 * Description of users-roles: CLASE PARA NUEVOS ROLES DE USUARIOS
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
		// more standard capabilities here
			'read' => true,
			'upload_files' => true,
			'edit_files' => true,
//			'manage_options' => true,
//			'promote_users' => true,
			'remove_users' => true,
			'add_users' => true,
			'edit_users' => true,
			'list_users' => true,
			'create_users' => true,
			'delete_users' => true,
			
			
			
		);
		$taxonomies_caps = array();
		$args = array(
		  'public'   => true,
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
		
		
		
		$args = array( 'public' => true );
		$output = 'names'; // names or objects
		$post_types = get_post_types($args,$output);
		
		foreach ($post_types  as $post_type_name ) {
			if (strpos($post_type_name, 'fktr') !== false)  {
				$newArrayCaps = array();
				$cap_object = new stdClass();
				$cap_object->capability_type = $post_type_name;
				$cap_object->capabilities = array();
				$cap_object->map_meta_cap = null;
				$cap_cpt = get_post_type_capabilities($cap_object);
				foreach ($cap_cpt as $c) {
					$newArrayCaps[$c] = true;
				}
				
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


		
	public function __construct() {
		add_action('activated_plugin', array( __CLASS__,'activate' ) );
		add_action('deactivated_plugin', array( __CLASS__,'deactivate' ), 10, 2 );

		add_filter('login_redirect', array( __CLASS__ ,'fakturo_login_redirect'), 10, 3);
		//Remove Wordpress core dashboard widgets
		if (get_current_user_id() && ( current_user_can('fakturo_manager') ||  current_user_can('fakturo_seller') ) ) {
			add_action('wp_dashboard_setup',  array( __CLASS__, 'remove_dashboard_widgets' ));
			add_action( 'admin_menu', array( __CLASS__, 'remove_menus' ) );
		}
	}

	public static function fakturo_login_redirect($redirect_url, $POST_redirect_url, $user) {
		if ( isset($user->ID) and ( user_can($user, 'fakturo_manager') || user_can($user, 'fakturo_seller') ) ) {
			return admin_url('http://plugins_svn_git/wp-admin/fakturo/admin/fakturo_admin.php');
		}
		return $redirect_url;
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

	
	public static function activate() {
		global $wp_roles;
		add_role( 'fakturo_manager', __( 'Manager', FAKTURO_TEXT_DOMAIN ), self::get_fakturo_manager_caps());
		add_role( 'fakturo_seller', __( 'Salesman', FAKTURO_TEXT_DOMAIN ), self::get_fakturo_seller_caps() );
		
		//Add capabilities to admin (if don't want to allow admins to edits Seller events can be disabled from Settings ;-)
		foreach(self::$fakturo_manager_caps as $key => $value) {
			$wp_roles->add_cap( 'administrator', $key, $value );
		}
	}

	
	public static function deactivate() {
		global $wp_roles;
		 remove_role( 'fakturo_manager'); 
		 remove_role( 'fakturo_seller'); 
		foreach(self::$fakturo_manager_caps as $key => $value) {
			$adm_cap = array('read','upload_files','edit_files','manage_options',
				'promote_users','remove_users','add_users','edit_users',
				'list_users','create_users','delete_users',);
			if(!in_array($key, $adm_cap ))
				$wp_roles->remove_cap( 'administrator', $key, $value );
		}
	}
	
}
endif;
$fktrUserRoles = new fktrUserRoles();
