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
		// Raise it when the capabilities of the roles change: fktr_version regenerates the roles of the sites that have an older one.
		const REVISION = 3;
		//Plugin capabilities
		private static $fakturo_manager_caps = array ();
		private static $fakturo_seller_caps = array ();
		
		public static function get_fakturo_manager_caps(){
			
			self::$fakturo_manager_caps = array (
				// more capabilities here
				'edit_fakturo_settings' => true,
				'edit_fakturo_dashboard' => true,
				'read' => true,
				'upload_files' => true,
				
				'remove_users' => true,
				'add_users' => true,
				'edit_users' => true,
				'list_users' => true,
				'create_users' => true,
				'delete_users' => true,
				
				'fakturo_reports' => true,
				'fktr_manage_wizard' => true,
			
			);
			/*
				*
				* Add all caps to access to reports.
			*/
			$report_tabs = reports::get_tabs();
			$reports_new_cap = array();
			foreach ($report_tabs as $key => $value) {
				foreach ($value as $keys => $values) {
					$reports_new_cap[$value[$keys]['cap']] = true;
				}
				
			}
			self::$fakturo_manager_caps = array_merge(self::$fakturo_manager_caps, $reports_new_cap);
			
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
					self::$fakturo_manager_caps = array_merge(self::$fakturo_manager_caps, $newArrayCaps);
				}
			}
			
			self::$fakturo_manager_caps = apply_filters('fakturo_manager_caps', self::$fakturo_manager_caps);
			return self::$fakturo_manager_caps;
		}
		
		
		
		public static function get_fakturo_seller_caps(){ 
			self::$fakturo_seller_caps = array (
			// clients capabilities here
			'edit_fakturo_dashboard' => true,
			
			'read' => true,
			'upload_files' => true,
			'list_users' => true,
			
			
			);
			$cpt_clients = array();
			$cpt_clients[] = 'fktr_client';
			$cpt_clients[] = 'fktr_sale';
			$cpt_clients[] = 'fktr_receipt';
			
			$args = array( 'public' => false );
			$output = 'names'; // names or objects
			$post_types = get_post_types($args,$output);
			
			foreach ($post_types  as $post_type_name ) {
				if (in_array($post_type_name, $cpt_clients)!== false)  {
					$newArrayCaps = array();
					$cap_cpt = get_post_type_object($post_type_name)->cap;
					foreach ($cap_cpt as $c) {
						$newArrayCaps[$c] = true;
					}
					self::$fakturo_seller_caps = array_merge(self::$fakturo_seller_caps, $newArrayCaps);
				}
			}
			
			self::$fakturo_seller_caps = apply_filters('fakturo_seller_caps', self::$fakturo_seller_caps);
			return self::$fakturo_seller_caps;
		}
		
		
		/**
		 * Capabilities that WordPress gives to administrators on its own: never taken away from them.
		 */
		private static function core_caps() {
			return array('read','upload_files','edit_files','manage_options',
				'promote_users','remove_users','add_users','edit_users',
				'list_users','create_users','delete_users');
		}

		/**
		 * A Fakturo manager or seller who is not an administrator: the team members of a site.
		 */
		public static function is_team_user($user_id) {
			return (user_can($user_id, 'fakturo_manager') || user_can($user_id, 'fakturo_seller'))
				&& !user_can($user_id, 'manage_options') && !is_super_admin($user_id);
		}

		/**
		 * A team user can only act on users who have no other role than the Fakturo ones.
		 */
		public static function map_user_caps($caps, $cap, $user_id, $args) {
			if (!in_array($cap, array('edit_user', 'delete_user', 'remove_user', 'promote_user'), true) || empty($args[0])) {
				return $caps;
			}
			$target_id = (int) $args[0];
			if ($target_id === (int) $user_id || !self::is_team_user($user_id)) {
				return $caps;
			}
			$target = get_userdata($target_id);
			if (!$target || empty($target->roles) || is_super_admin($target_id) || array_diff($target->roles, array('fakturo_manager', 'fakturo_seller'))) {
				return array('do_not_allow');
			}
			return $caps;
		}

		/**
		 * Settings forms go through options.php, which asks for manage_options unless told otherwise.
		 */
		public static function settings_capability() {
			return 'edit_fakturo_settings';
		}
		
		
		public function __construct() {
			add_action('activated_plugin', array( __CLASS__,'activate' ), 10, 2);
			add_action('activated_plugin', array( __CLASS__,'activation_fakturo' ), 2, 2);
			add_action('deactivated_plugin', array( __CLASS__,'deactivate' ), 10, 2 );
			
			add_filter('login_redirect', array( __CLASS__ ,'fakturo_login_redirect'), 10, 3);
			add_filter('map_meta_cap', array( __CLASS__, 'map_user_caps'), 10, 4);
			foreach (array('fakturo-settings', 'fakturo-settings-system', 'fakturo-settings-dashboard') as $option_page) {
				add_filter('option_page_capability_' . $option_page, array( __CLASS__, 'settings_capability'));
			}
			
			add_action('admin_init', array( __CLASS__,'admin_init' ), 10 );
		}
		public static function activation_fakturo($plugin, $network_activation) {
			if ($plugin != 'fakturo/fakturo.php') {
				return true;
			}
			do_action('fakturo_activation');
		}
		public static function admin_init() {
			//global $current_user;
			if (get_current_user_id() && ( current_user_can('fakturo_manager') ||  current_user_can('fakturo_seller') ) ) {
				//Remove Wordpress core dashboard widgets
				add_action('wp_dashboard_setup',  array( __CLASS__, 'remove_dashboard_widgets' ));
				//Remove Media Wordpress menu
				add_action( 'admin_menu', array( __CLASS__, 'remove_menus' ) );
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
		
		/**
		 * The lists of the last capabilities are only read when the roles are regenerated: they stay out of the options loaded on every request (update_option() keeps the old autoload flag when the value does not change).
		 */
		private static function keep_caps($name, $caps) {
			delete_option($name);
			add_option($name, $caps, '', 'no');
		}

		public static function activate($plugin, $network_activation) {
			global $wp_roles;
			if ($plugin != 'fakturo/fakturo.php') {
				return true;
			}
			add_role( 'fakturo_manager', __( 'Fakturo Manager', 'fakturo' ), self::get_fakturo_manager_caps());
			add_role( 'fakturo_seller', __( 'Fakturo Salesman', 'fakturo' ), self::get_fakturo_seller_caps());
			
			self::keep_caps('fktr_last_mananger_caps', self::$fakturo_manager_caps);
			self::keep_caps('fktr_last_seller_caps', self::$fakturo_seller_caps);
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
			$manager_caps = array_merge((array) get_option('fktr_last_mananger_caps', array()), self::get_fakturo_manager_caps());
			foreach($manager_caps as $key => $value) {
				if(!in_array($key, self::core_caps())){
					$wp_roles->remove_cap( 'administrator', $key );
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
				if(!in_array($key, self::core_caps())) {
					$wp_roles->remove_cap( 'administrator', $key );
				}
			}
			
			self::activate('fakturo/fakturo.php', false);
			
		}
		
	}
	endif;
	$fktrUserRoles = new fktrUserRoles();
