<?php

// Exit if accessed directly
if (!defined('ABSPATH'))  {
	exit;
}

if (!class_exists('fktrSiteSetup')) :
/**
 * Prepares a site for Fakturo when the plugin is switched on in it: roles and capabilities, the version markers and the
 * first visit of its administrator to the setup wizard. Pro Sites activates Fakturo (and its add-ons) on every new site
 * silently, so `activated_plugin` never runs there: the changes of the `active_plugins` option are what is watched.
 */
class fktrSiteSetup {

	public static function hooks() {
		add_action('update_option_active_plugins', array(__CLASS__, 'plugins_changed'), 10, 2);
		add_action('add_option_active_plugins', array(__CLASS__, 'plugins_added'), 10, 2);
		add_action('wp_initialize_site', array(__CLASS__, 'new_site'), 200);
	}

	public static function plugins_added($option, $value) {
		self::plugins_changed(array(), $value);
	}

	/**
	 * Fakturo or one of its add-ons was added to the active plugins of the current site.
	 */
	public static function plugins_changed($old, $new) {
		$added = array_diff((array) $new, (array) $old);
		$fktr  = array_filter($added, function ($plugin) {
			return strpos($plugin, 'fakturo') !== false;
		});
		if (!$fktr) {
			return;
		}
		self::prepare(in_array('fakturo/fakturo.php', $added, true));
	}

	/**
	 * Fakturo is active in a site that was just created without anybody activating it (network activation).
	 */
	public static function new_site($new_site) {
		if (!is_multisite()) {
			return;
		}
		if (!function_exists('is_plugin_active_for_network')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		if (!is_plugin_active_for_network('fakturo/fakturo.php')) {
			return;
		}
		switch_to_blog((int) $new_site->blog_id);
		self::prepare(true);
		restore_current_blog();
	}

	/**
	 * Idempotent. $core is true when Fakturo itself was just switched on; an add-on that comes later only regenerates the
	 * roles, so its capabilities reach the administrator of the site.
	 */
	public static function prepare($core = true) {
		if (!get_option('fktr_db_version')) {
			if (!$core) {
				return;
			}
			update_option('fktr_db_version', WPE_FAKTURO_VERSION);
			update_option('fktr_roles_revision', fktrUserRoles::REVISION);
			update_option('fktr_meta_cleaned', 1);
			fktrUserRoles::regenerate(true);
			self::queue_wizard();
			do_action('fktr_site_prepared');
			return;
		}
		fktrUserRoles::regenerate(true);
	}

	/**
	 * The administrators of a new site land on the setup wizard in their first visit; the network can turn it off.
	 */
	public static function queue_wizard() {
		if (!is_multisite() || get_option('fktr_first_time_wizard') || !apply_filters('fktr_new_site_wizard_redirect', get_site_option(fktrNetwork::WIZARD, 1))) {
			return;
		}
		foreach (get_users(array('role' => 'administrator', 'fields' => 'ID')) as $user_id) {
			fktrRedirects::add(array(
				'screen'  => 'all',
				'user_id' => (int) $user_id,
				'url'     => admin_url('admin-post.php?action=fktr_wizard'),
			));
		}
	}
}
endif;

fktrSiteSetup::hooks();
