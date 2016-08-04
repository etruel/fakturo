<?php
/**
 * Description of users-roles: CLASE PARA NUEVOS ROLES DE USUARIOS
 * @author esteban
 */

// don't load directly 
if ( !defined('ABSPATH') ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

//$fktrUserRoles = new fktrUserRoles();

if ( class_exists( 'fktrUserRoles' ) ) return;
class fktrUserRoles {
	//Plugin capabilities
	private static $fakturo_manager_caps = array ();
	
	public static function get_fakturo_manager_caps(){
		
		$fakturo_manager_caps = array (
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
		
		// publicos y privados para que pueda mostrar el boton en todos
		$args=array( 'public'   => false );
		$output = 'names'; // names or objects
		$post_types=get_post_types($args,$output);
		foreach ($post_types  as $post_type_name ) {
			if (strpos($post_type_name, 'fktr') ) continue;  // ignore 'attachment'
			$cap_cpt = get_post_type_capabilities( $post_type_name );
		
		}

		
		return apply_filters('fakturo_manager_caps', $fakturo_manager_caps );
	}
	
	private static $fakturo_seller_caps = array ();
	
	public static function get_fakturo_seller_caps(){ 
		
		return apply_filters('fakturo_seller_caps', array (
		// clients capabilities here
			'publish_fakturo_clients' => true,
			'read_fakturo_clients' => true,
//			'read_private_fakturo_clients' => true,
			'edit_fakturo_client' => true,
			'edit_fakturo_clients' => true,
			'edit_published_fakturo_clients' => true,
//			'edit_private_fakturo_clients' => true,
//			'edit_others_fakturo_clients' => true,
			'delete_fakturo_client' => true,
			'delete_fakturo_clients' => true,
//			'delete_published_fakturo_clients' => true,
//			'delete_private_fakturo_clients' => true,
//			'delete_others_fakturo_clients' => true,
		// more capabilities here
//			'edit_fakturo_settings' => true,
		// more standard capabilities here
			'read' => true,
			'upload_files' => true,
			'edit_files' => true,
//			'manage_options' => true,
//			'promote_users' => true,
//			'remove_users' => true,
//			'add_users' => true,
//			'edit_users' => true,
			'list_users' => true,
//			'create_users' => true,
//			'delete_users' => true,
			'MailPress_manage_subscriptions' => false,
		));
	}
//		private static $fakturo_customer_caps = array ('read' => true);

		
		
	public function __construct( ) {
		add_filter('login_redirect', array( __CLASS__ ,'fakturo_login_redirect'), 10, 3);
		//Remove Wordpress core dashboard widgets
		if ( $current_user->ID && ( current_user_can('fakturo_manager') ||  current_user_can('fakturo_seller') ) ) {
			add_action('wp_dashboard_setup',  array( __CLASS__, 'remove_dashboard_widgets' ));
			add_action( 'admin_menu', array( __CLASS__, 'remove_menus' ) );
		}

	}

	public static function fakturo_login_redirect($redirect_url, $POST_redirect_url, $user) {
		if ( isset($user->ID) and ( user_can($user, 'fakturo_manager') || user_can($user, 'fakturo_seller') ) ) {
			return admin_url('edit.php?post_type=fakturollerevents');
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
		unset($wp_meta_boxes['dashboard']['normal']['core']["dashboard_activity"]);
	}

	
	public static function activate() {
		global $wp_roles;
		add_role( 'fakturo_manager', __( 'Manager', FAKTURO_TEXT_DOMAIN ), self::$fakturo_manager_caps );
		add_role( 'fakturo_seller', __( 'Salesman', FAKTURO_TEXT_DOMAIN ), self::$fakturo_seller_caps  );
		// add_role( 'fakturo_customer', __( 'Customer', FAKTURO_TEXT_DOMAIN ), self::$fakturo_customer_caps );
		
		//Add capabilities to admin (if don't want to allow admins to edits Seller events can be disabled from Settings ;-)
		foreach(self::$fakturo_manager_caps as $key => $value) {
			$wp_roles->add_cap( 'administrator', $key, $value );
		}
	}

	
	public static function deactivate() {
		global $wp_roles;
		 remove_role( 'fakturo_manager' ); 
		 remove_role( 'fakturo_seller' ); 
		 remove_role( 'fakturo_customer' ); 
		foreach(self::$fakturo_manager_caps as $key => $value) {
			$adm_cap = array('read','upload_files','edit_files','manage_options',
				'promote_users','remove_users','add_users','edit_users',
				'list_users','create_users','delete_users',);
			if(!in_array($key, $adm_cap ))
				$wp_roles->remove_cap( 'administrator', $key, $value );
		}
	}
	
}
