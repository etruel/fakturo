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
		const REVISION = 4;
		const OPTION_KEY = 'roles_access';

		/**
		 * Capabilities every team role has: they are not a permission to choose.
		 */
		private static function fixed_caps() {
			return array('read' => true, 'upload_files' => true);
		}

		/**
		 * The roles of the people who work with Fakturo. The two built-in ones are here; an add-on adds its own (a "fakturo_accountant", for example) with the `fktr_team_roles` filter:
		 * $roles['fakturo_accountant'] = array('label' => 'Fakturo Accountant', 'groups' => array('menu', 'report_client_incomes')); `groups` are the permission groups it starts with.
		 * The slug has to start with "fakturo_". The role is created the next time the roles are regenerated, and its permissions can be changed in Settings > System.
		 */
		public static function roles() {
			$roles = array(
				'fakturo_manager'	 => array('label' => __('Fakturo Manager', 'fakturo')),
				'fakturo_seller'	 => array('label' => __('Fakturo Salesman', 'fakturo')),
			);
			$roles = apply_filters('fktr_team_roles', $roles);
			$clean = array();
			foreach ((array) $roles as $slug => $role) {
				if (is_string($slug) && preg_match('/^fakturo_[a-z0-9_]+$/', $slug) && is_array($role)) {
					$clean[$slug] = array(
						'label'		 => !empty($role['label']) ? (string) $role['label'] : $slug,
						'groups'	 => isset($role['groups']) ? (array) $role['groups'] : array(),
					);
				}
			}
			return $clean;
		}

		public static function slugs() {
			return array_keys(self::roles());
		}

		private static function type_caps($types) {
			$caps = array();
			foreach ((array) $types as $type) {
				$object = get_post_type_object($type);
				if ($object && !empty($object->cap)) {
					foreach ($object->cap as $cap) {
						$caps[$cap] = true;
					}
				}
			}
			return $caps;
		}

		private static function taxonomy_caps() {
			$caps = array();
			foreach (get_taxonomies(array('public' => false, '_builtin' => false), 'names') as $taxonomy) {
				if (strpos($taxonomy, 'fktr') !== false) {
					foreach (array('manage_', 'edit_', 'delete_', 'assign_') as $prefix) {
						$caps[$prefix . $taxonomy] = true;
					}
				}
			}
			return $caps;
		}

		/**
		 * What can be given to a role, in groups the administrator understands. Each group lists the capabilities it grants (`caps`, `types` for the capabilities of a post type, `taxonomies` => true for those of the lookup tables) and the roles that have it from the start (`default`).
		 * An add-on adds its groups with the `fktr_role_permission_groups` filter. Everything outside the groups (manage_options, edit_files, unfiltered_html, the licenses) can not be given from the screen.
		 */
		public static function groups() {
			$both = array('fakturo_manager', 'fakturo_seller');
			$manager = array('fakturo_manager');
			$groups = array(
				'menu'			 => array('label' => __('Fakturo menu and dashboard', 'fakturo'), 'description' => __('Shows the Fakturo menu and its dashboard. Without it the person can not reach anything else.', 'fakturo'), 'caps' => array('edit_fakturo_dashboard'), 'default' => $both),
				'sales'			 => array('label' => __('Invoices', 'fakturo'), 'description' => __('See, create and delete invoices. A published invoice can not be changed by anyone.', 'fakturo'), 'types' => array('fktr_sale'), 'default' => $both),
				'receipts'		 => array('label' => __('Receipts', 'fakturo'), 'description' => __('See, create and cancel receipts.', 'fakturo'), 'types' => array('fktr_receipt'), 'default' => $both),
				'clients'		 => array('label' => __('Clients', 'fakturo'), 'description' => __('See and edit the clients.', 'fakturo'), 'types' => array('fktr_client'), 'default' => $both),
				'products'		 => array('label' => __('Products', 'fakturo'), 'description' => __('See and edit the products and their stock.', 'fakturo'), 'types' => array('fktr_product'), 'default' => $manager),
				'providers'		 => array('label' => __('Providers', 'fakturo'), 'description' => __('See and edit the providers.', 'fakturo'), 'types' => array('fktr_provider'), 'default' => $manager),
				'tables'		 => array('label' => __('Tables', 'fakturo'), 'description' => __('Taxes, currencies, sale points, payment types, stocks and the other lists that the invoices use.', 'fakturo'), 'taxonomies' => true, 'default' => $manager),
				'templates'		 => array('label' => __('Templates: see', 'fakturo'), 'description' => __('Open the lists of print and e-mail templates.', 'fakturo'), 'types' => array('fktr_print_template', 'fktr_email_template'), 'default' => $manager),
				'templates_edit' => array('label' => __('Templates: change', 'fakturo'), 'description' => __('Save, copy and reset print and e-mail templates.', 'fakturo'), 'warning' => __('What a template writes is shown to whoever prints the document (scripts in it are blocked): give this to people who work with the documents of the business.', 'fakturo'), 'caps' => array('fktr_edit_templates'), 'default' => array()),
				'settings'		 => array('label' => __('Settings', 'fakturo'), 'description' => __('Company, system and dashboard settings, and the setup wizard. Not the licenses.', 'fakturo'), 'caps' => array('edit_fakturo_settings', 'fktr_manage_wizard'), 'default' => $manager),
				'users_list'	 => array('label' => __('Users: see', 'fakturo'), 'description' => __('See the list of users, for example to choose the salesman of an invoice.', 'fakturo'), 'caps' => array('list_users'), 'default' => $both),
				'users_manage'	 => array('label' => __('Users: create and edit', 'fakturo'), 'description' => __('Only users who have a Fakturo role, and only with roles that do not give more than the person has.', 'fakturo'), 'caps' => array('add_users', 'create_users', 'edit_users', 'remove_users', 'delete_users'), 'default' => $manager),
			);

			// One group for each capability of the reports, so an accountant can have the money reports only.
			$report_labels = array(
				'fktr_report_sales'						 => array('report_sales', __('Report: sales', 'fakturo')),
				'fktr_report_client_summary'			 => array('report_client_summary', __('Report: clients summary and stock', 'fakturo')),
				'fktr_report_client_incomes'			 => array('report_client_incomes', __('Report: client incomes', 'fakturo')),
				'fktr_report_client_account_movements'	 => array('report_client_account_movements', __('Report: client account', 'fakturo')),
				'fktr_report_expenses'					 => array('report_expenses', __('Report: salesmen', 'fakturo')),
			);
			foreach (reports::get_tabs() as $tabs) {
				foreach ((array) $tabs as $tab) {
					if (empty($tab['cap']) || !is_string($tab['cap'])) {
						continue;
					}
					$cap = $tab['cap'];
					$key = isset($report_labels[$cap]) ? $report_labels[$cap][0] : 'report_' . sanitize_key(preg_replace('/^fktr_report_/', '', $cap));
					if (!isset($groups[$key])) {
						$label = isset($report_labels[$cap]) ? $report_labels[$cap][1] : sprintf(__('Report: %s', 'fakturo'), isset($tab['text']) ? wp_strip_all_tags($tab['text']) : $cap);
						$groups[$key] = array('label' => $label, 'description' => __('Opens this report.', 'fakturo'), 'caps' => array('fakturo_reports', $cap), 'default' => $manager);
					}
				}
			}

			$groups = apply_filters('fktr_role_permission_groups', $groups);
			$clean = array();
			foreach ((array) $groups as $key => $group) {
				if (!is_string($key) || !preg_match('/^[a-z0-9_]+$/', $key) || !is_array($group)) {
					continue;
				}
				$caps = array();
				foreach ((array) (isset($group['caps']) ? $group['caps'] : array()) as $cap) {
					$caps[$cap] = true;
				}
				$caps = array_merge($caps, self::type_caps(isset($group['types']) ? $group['types'] : array()));
				if (!empty($group['taxonomies'])) {
					$caps = array_merge($caps, self::taxonomy_caps());
				}
				$group['caps'] = $caps;
				$group['label'] = isset($group['label']) ? (string) $group['label'] : $key;
				$group['default'] = isset($group['default']) ? (array) $group['default'] : array();
				$clean[$key] = $group;
			}
			return $clean;
		}

		/**
		 * Which groups each role has with no change made on the site: what the code (and the add-ons) define, then the starting values of the network.
		 */
		public static function base_matrix() {
			$roles = self::roles();
			$groups = self::groups();
			$matrix = array();
			foreach ($roles as $slug => $role) {
				foreach ($groups as $key => $group) {
					$matrix[$slug][$key] = in_array($slug, $group['default'], true) || in_array($key, $role['groups'], true);
				}
			}
			$defaults = fktrSettings::system_defaults();
			return self::overlay($matrix, isset($defaults[self::OPTION_KEY]) ? $defaults[self::OPTION_KEY] : array());
		}

		private static function overlay($matrix, $cells) {
			if (is_array($cells)) {
				foreach ($cells as $slug => $row) {
					if (isset($matrix[$slug]) && is_array($row)) {
						foreach ($row as $key => $value) {
							if (isset($matrix[$slug][$key])) {
								$matrix[$slug][$key] = !empty($value);
							}
						}
					}
				}
			}
			return $matrix;
		}

		/**
		 * The groups each role has on this site: the base plus what the administrator changed in Settings > System (only the differences are saved).
		 */
		public static function matrix() {
			$options = get_option('fakturo_system_options_group', array());
			return self::overlay(self::base_matrix(), is_array($options) && isset($options[self::OPTION_KEY]) ? $options[self::OPTION_KEY] : array());
		}

		/**
		 * The differences with the base of a matrix sent by the form, on top of the ones already saved.
		 */
		public static function changes($input, $saved) {
			$base = self::base_matrix();
			$changes = is_array($saved) ? $saved : array();
			if (!is_array($input)) {
				return $changes;
			}
			foreach ($base as $slug => $row) {
				foreach ($row as $key => $value) {
					if (isset($input[$slug][$key]) && is_scalar($input[$slug][$key])) {
						$wanted = ((string) $input[$slug][$key] === '1');
						if ($wanted === $value) {
							unset($changes[$slug][$key]);
						} else {
							$changes[$slug][$key] = $wanted ? 1 : 0;
						}
					}
				}
			}
			return array_filter($changes);
		}

		/**
		 * A list of differences set from code: only known roles and groups, and only what differs from the base.
		 */
		public static function clean($cells) {
			$base = self::base_matrix();
			$clean = array();
			if (is_array($cells)) {
				foreach ($base as $slug => $row) {
					foreach ($row as $key => $value) {
						if (isset($cells[$slug][$key]) && is_scalar($cells[$slug][$key]) && !empty($cells[$slug][$key]) !== $value) {
							$clean[$slug][$key] = $value ? 0 : 1;
						}
					}
				}
			}
			return $clean;
		}

		/**
		 * Capabilities of a role on this site.
		 */
		public static function role_caps($slug) {
			$caps = self::fixed_caps();
			$matrix = self::matrix();
			foreach (self::groups() as $key => $group) {
				if (!empty($matrix[$slug][$key])) {
					$caps = array_merge($caps, $group['caps']);
				}
			}
			$caps = apply_filters('fktr_role_caps', $caps, $slug);
			// Older add-ons add their capabilities to the manager and the salesman with these.
			if ($slug === 'fakturo_manager') {
				$caps = apply_filters('fakturo_manager_caps', $caps);
			} elseif ($slug === 'fakturo_seller') {
				$caps = apply_filters('fakturo_seller_caps', $caps);
			}
			return $caps;
		}

		public static function get_fakturo_manager_caps() {
			return self::role_caps('fakturo_manager');
		}

		public static function get_fakturo_seller_caps() {
			return self::role_caps('fakturo_seller');
		}

		/**
		 * Every capability of Fakturo: the administrator of a site always has all of them, whatever the other roles have.
		 */
		public static function admin_caps() {
			$caps = self::fixed_caps();
			foreach (self::groups() as $group) {
				$caps = array_merge($caps, $group['caps']);
			}
			return apply_filters('fakturo_manager_caps', $caps);
		}

		/**
		 * Sets the capabilities of the administrator and creates or updates the team roles to what this site has chosen.
		 */
		public static function apply_roles() {
			$all = self::admin_caps();
			$admin = get_role('administrator');
			if ($admin) {
				foreach (array_keys((array) get_option('fktr_last_mananger_caps', array())) as $cap) {
					if (!isset($all[$cap]) && !in_array($cap, self::core_caps(), true)) {
						$admin->remove_cap($cap);
					}
				}
				foreach ($all as $cap => $grant) {
					if (empty($admin->capabilities[$cap])) {
						$admin->add_cap($cap, $grant);
					}
				}
			}
			self::keep_caps('fktr_last_mananger_caps', $all);
			foreach (self::roles() as $slug => $role) {
				$caps = self::role_caps($slug);
				$current = get_role($slug);
				if (!$current || array_filter($current->capabilities) != array_filter($caps)) {
					remove_role($slug);
					add_role($slug, $role['label'], $caps);
				}
				if ($slug === 'fakturo_seller') {
					self::keep_caps('fktr_last_seller_caps', $caps);
				}
			}
		}

		/**
		 * The permissions of the roles changed in the settings: the roles follow.
		 */
		public static function options_updated($old, $new) {
			$before = is_array($old) && isset($old[self::OPTION_KEY]) ? $old[self::OPTION_KEY] : array();
			$after = is_array($new) && isset($new[self::OPTION_KEY]) ? $new[self::OPTION_KEY] : array();
			if ($before != $after) {
				self::apply_roles();
			}
		}

		/**
		 * A person can only give a role that does not have more than they have.
		 */
		public static function can_give($user_id, $slug) {
			$role = get_role($slug);
			$user = get_userdata($user_id);
			if (!$role || !$user) {
				return false;
			}
			foreach ($role->capabilities as $cap => $grant) {
				if ($grant && empty($user->allcaps[$cap])) {
					return false;
				}
			}
			return true;
		}

		/**
		 * The users who can be the salesman of a document: the administrators and everyone with a team role that may create invoices.
		 */
		public static function salesmen($with_admins = true) {
			$roles = array();
			foreach (self::slugs() as $slug) {
				$role = get_role($slug);
				if ($role && !empty($role->capabilities['edit_fktr_sales'])) {
					$roles[] = $slug;
				}
			}
			if ($with_admins) {
				$roles[] = 'administrator';
			}
			return $roles ? get_users(array('role__in' => $roles, 'orderby' => 'display_name')) : array();
		}

		public static function has_team_role($user_id) {
			$user = get_userdata($user_id);
			return $user && array_intersect((array) $user->roles, self::slugs());
		}

		/**
		 * The section of Settings > System where the administrator chooses what each role can do.
		 */
		public static function access_section($name) {
			$roles = self::roles();
			$groups = self::groups();
			$matrix = self::matrix();
			$base = self::base_matrix();
			$html = '<p class="description">' . esc_html__('Choose what each Fakturo role can do on this site. The administrator always has everything. Changes apply when you save.', 'fakturo') . '</p>';
			$html .= '<table class="widefat striped fktr-access"><thead><tr><th>' . esc_html__('Permission', 'fakturo') . '</th>';
			foreach ($roles as $role) {
				$html .= '<th>' . esc_html($role['label']) . '</th>';
			}
			$html .= '<th>' . esc_html__('Administrator', 'fakturo') . '</th></tr></thead><tbody>';
			foreach ($groups as $key => $group) {
				$html .= '<tr><td><strong>' . esc_html($group['label']) . '</strong>';
				if (!empty($group['description'])) {
					$html .= '<p class="description">' . esc_html($group['description']) . '</p>';
				}
				if (!empty($group['warning'])) {
					$html .= '<p class="description" style="color:#b32d2e;">' . esc_html($group['warning']) . '</p>';
				}
				$html .= '</td>';
				foreach ($roles as $slug => $role) {
					$field = esc_attr($name . '[' . self::OPTION_KEY . '][' . $slug . '][' . $key . ']');
					$title = $matrix[$slug][$key] === $base[$slug][$key] ? '' : ' title="' . esc_attr__('Different from the default', 'fakturo') . '" style="outline:2px solid #dba617;"';
					$html .= '<td><input type="hidden" name="' . $field . '" value="0"/><input type="checkbox" name="' . $field . '" value="1" ' . checked($matrix[$slug][$key], true, false) . $title . '/></td>';
				}
				$html .= '<td><span class="dashicons dashicons-yes" title="' . esc_attr__('Always', 'fakturo') . '"></span></td></tr>';
			}
			$html .= '</tbody></table>';
			$html .= '<p><label><input type="checkbox" name="' . esc_attr($name) . '[roles_access_reset]" value="1"/> ' . esc_html__('Restore the default permissions of all the roles', 'fakturo') . '</label></p>';
			return $html;
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
			return self::has_team_role($user_id) && !user_can($user_id, 'manage_options') && !is_super_admin($user_id);
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
			if (!$target || empty($target->roles) || is_super_admin($target_id) || array_diff($target->roles, self::slugs())) {
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
			add_action('update_option_fakturo_system_options_group', array( __CLASS__, 'options_updated'), 10, 2);
		}
		public static function activation_fakturo($plugin, $network_activation) {
			if ($plugin != 'fakturo/fakturo.php') {
				return true;
			}
			do_action('fakturo_activation');
		}
		public static function admin_init() {
			//global $current_user;
			if (get_current_user_id() && self::has_team_role(get_current_user_id())) {
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
			if ( isset($user->ID) && self::has_team_role($user->ID) ) {
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
			if ($plugin != 'fakturo/fakturo.php') {
				return true;
			}
			self::apply_roles();
		}
		
		
		public static function deactivate($plugin, $network_activation) {
			global $wp_roles;
			if ($plugin != 'fakturo/fakturo.php') {
				return true;
			}
			foreach (array_unique(array_merge(self::slugs(), array('fakturo_manager', 'fakturo_seller'))) as $slug) {
				remove_role($slug);
			}
			$caps = array_merge(array_keys((array) get_option('fktr_last_mananger_caps', array())), array_keys(self::admin_caps()));
			foreach($caps as $key) {
				if(!in_array($key, self::core_caps())){
					$wp_roles->remove_cap( 'administrator', $key );
				}
			}
		}
		
		public static function regenerate($update = false) {
			self::apply_roles();
		}
		
	}
	endif;
	$fktrUserRoles = new fktrUserRoles();
