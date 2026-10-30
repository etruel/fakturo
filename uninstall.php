<?php
/**
 * Runs when Fakturo is deleted from the Plugins screen (not when it is deactivated).
 *
 * The technical leftovers always go: roles and capabilities, version markers, caches and notices.
 * The data (documents, lists, settings, numbering) goes only on the sites where the option "Delete data on uninstall"
 * is on, and the network data (license keys, defaults) only if the network option is on, so deleting the plugin by mistake
 * does not wipe invoices. In a network it runs once and walks every site. The plugin is not loaded here: nothing of its code is available.
 */
if (!defined('WP_UNINSTALL_PLUGIN')) {
	exit;
}

if (!class_exists('fktrUninstall')) :
class fktrUninstall {
	const POST_TYPES = array('fktr_product', 'fktr_client', 'fktr_provider', 'fktr_sale', 'fktr_receipt', 'fktr_print_template', 'fktr_email_template');
	const TAXONOMIES = array('fktr_bank_entities', 'fktr_check', 'fktr_commission_scales', 'fktr_countries', 'fktr_currencies', 'fktr_invoice_types', 'fktr_locations', 'fktr_origins', 'fktr_packaging', 'fktr_payment_types', 'fktr_price_scales', 'fktr_product_type', 'fktr_sale_points', 'fktr_stock', 'fktr_tax', 'fktr_tax_conditions');
	const SITE_CACHE_OPTIONS = array('fktr_db_version', 'fktr_roles_revision', 'fktr_meta_cleaned', 'fktr_last_mananger_caps', 'fktr_last_seller_caps', 'fakturo_notices', 'fakturo_redirects');
	const SITE_DATA_OPTIONS = array('fakturo_info_options_group', 'fakturo_system_options_group', 'fakturo_dashboard_options_group', 'last_invoice_number', 'last_receipt_number', 'fktr_first_time_wizard', 'fktr_countries_children');
	const NETWORK_DATA_OPTIONS = array('fktr_license_keys', 'fktr_license_status', 'fktr_license_info', 'fktr_licenses_network', 'fktr_network_defaults', 'fktr_network_hide_main', 'fktr_network_new_site_wizard', 'fktr_network_delete_data');

	public static function run() {
		if (is_multisite()) {
			$sites = get_sites(array('fields' => 'ids', 'number' => 0));
			foreach ($sites as $blog_id) {
				switch_to_blog($blog_id);
				self::site();
				restore_current_blog();
			}
			self::network((bool) get_site_option('fktr_network_delete_data'));
		} else {
			// No network screen here: the license keys follow the setting of the only site.
			self::network(self::site());
		}
		wp_cache_flush();
	}

	/**
	 * Cleans the current site. Returns true if its data was deleted too.
	 */
	public static function site() {
		global $wpdb;

		self::remove_roles();
		foreach (self::SITE_CACHE_OPTIONS as $name) {
			delete_option($name);
		}
		$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'edd\\_sl\\_%' OR option_name LIKE '\\_transient\\_%fktr\\_%' OR option_name LIKE '\\_transient\\_timeout\\_%fktr\\_%'");

		$settings = get_option('fakturo_system_options_group');
		$delete = is_array($settings) && !empty($settings['delete_data_on_uninstall']);
		if ($delete) {
			self::site_data();
		}
		return $delete;
	}

	private static function remove_roles() {
		remove_role('fakturo_manager');
		remove_role('fakturo_seller');
		$admin = get_role('administrator');
		if ($admin) {
			foreach (array_keys($admin->capabilities) as $cap) {
				if (strpos($cap, 'fktr_') !== false || strpos($cap, 'fakturo') !== false) {
					$admin->remove_cap($cap);
				}
			}
		}
	}

	private static function site_data() {
		global $wpdb;

		$types = "'" . implode("','", self::POST_TYPES) . "'";
		$ids = "SELECT ID FROM {$wpdb->posts} WHERE post_type IN ($types)";

		// Photos of clients, providers and products: their files go too.
		$attachments = $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_parent IN ($ids)");
		foreach ($attachments as $attachment_id) {
			wp_delete_attachment((int) $attachment_id, true);
		}

		$wpdb->query("DELETE pm FROM {$wpdb->postmeta} pm INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE p.post_type = 'revision' AND p.post_parent IN ($ids)");
		$wpdb->query("DELETE FROM {$wpdb->posts} WHERE post_parent IN (SELECT ID FROM (SELECT ID FROM {$wpdb->posts} WHERE post_type IN ($types)) AS owners) AND post_type = 'revision'");
		$wpdb->query("DELETE pm FROM {$wpdb->postmeta} pm INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE p.post_type IN ($types)");
		$wpdb->query("DELETE tr FROM {$wpdb->term_relationships} tr INNER JOIN {$wpdb->posts} p ON p.ID = tr.object_id WHERE p.post_type IN ($types)");
		$wpdb->query("DELETE FROM {$wpdb->posts} WHERE post_type IN ($types)");

		$taxonomies = "'" . implode("','", self::TAXONOMIES) . "'";
		$term_ids = $wpdb->get_col("SELECT term_id FROM {$wpdb->term_taxonomy} WHERE taxonomy IN ($taxonomies)");
		$wpdb->query("DELETE tr FROM {$wpdb->term_relationships} tr INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id WHERE tt.taxonomy IN ($taxonomies)");
		$wpdb->query("DELETE FROM {$wpdb->term_taxonomy} WHERE taxonomy IN ($taxonomies)");
		foreach (array_chunk(array_map('intval', $term_ids), 500) as $chunk) {
			$list = implode(',', $chunk);
			$wpdb->query("DELETE FROM {$wpdb->termmeta} WHERE term_id IN ($list)");
			$wpdb->query("DELETE FROM {$wpdb->terms} WHERE term_id IN ($list) AND term_id NOT IN (SELECT term_id FROM {$wpdb->term_taxonomy})");
		}

		foreach (self::SITE_DATA_OPTIONS as $name) {
			delete_option($name);
		}
	}

	/**
	 * Cleans the network (or the only site of a single install): caches always, license keys and defaults if asked.
	 */
	public static function network($delete_data) {
		global $wpdb;

		delete_site_transient('fakturo_addons_data');
		if (is_multisite()) {
			$wpdb->query("DELETE FROM {$wpdb->sitemeta} WHERE meta_key LIKE '\\_site\\_transient\\_%fktr\\_%' OR meta_key LIKE '\\_site\\_transient\\_timeout\\_%fktr\\_%'");
		}
		if ($delete_data) {
			foreach (self::NETWORK_DATA_OPTIONS as $name) {
				delete_site_option($name);
			}
		}
	}
}
endif;

fktrUninstall::run();
