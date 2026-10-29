<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class fktr_version {
	public static function hooks() {
		add_action('admin_init', array(__CLASS__, 'init'), 11);
	}

	public static function init() {
		$current_version = get_option('fktr_db_version', 0.0);
		if (version_compare($current_version, WPE_FAKTURO_VERSION, '<')) {
			// Update
			update_option('fktr_db_version', WPE_FAKTURO_VERSION);
			fktrUserRoles::regenerate(true);
			if (version_compare($current_version, 0.0, '=')) {
				fktrRedirects::add(array(
								'screen' => 'all',
								'user_id'=> get_current_user_id(),
								'url'=> admin_url('index.php?page=fakturo-about'),
							));
			}
			
		}
		if ((int) get_option('fktr_roles_revision', 0) < fktrUserRoles::REVISION) {
			update_option('fktr_roles_revision', fktrUserRoles::REVISION);
			if (version_compare($current_version, WPE_FAKTURO_VERSION, '>=')) {
				fktrUserRoles::regenerate(true);
			}
		}
		if (!get_option('fktr_meta_cleaned')) {
			update_option('fktr_meta_cleaned', 1);
			self::clean_post_meta();
		}
	}

	/**
	 * Older versions saved the whole edit form as post meta. The live columns and form leftovers are removed from the Fakturo posts, the data arrays read the columns from the post.
	 */
	public static function clean_post_meta() {
		global $wpdb;
		$types = array('fktr_client', 'fktr_provider', 'fktr_product', 'fktr_sale', 'fktr_receipt', 'fktr_print_template', 'fktr_email_template');
		$keys = array('_wpnonce', '_wp_http_referer', '_wp_original_http_referer', 'user_ID', 'action', 'originalaction', 'post_author', 'post_type', 'original_post_status', 'referredby', 'auto_draft', 'post_ID', 'ID', 'meta-box-order-nonce', 'closedpostboxesnonce', 'samplepermalinknonce', 'post_title', 'post_status', 'post_name', 'post_content', 'post_mime_type', 'post_password', 'comment_status', 'ping_status', 'visibility', 'original_publish', 'publish', 'save', 'aa', 'mm', 'jj', 'hh', 'mn', 'ss', 'ucfield_max');
		$in_types = "'" . implode("','", array_map('esc_sql', $types)) . "'";
		$in_keys = "'" . implode("','", array_map('esc_sql', $keys)) . "'";
		$wpdb->query("DELETE pm FROM {$wpdb->postmeta} pm INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE p.post_type IN ($in_types) AND (pm.meta_key IN ($in_keys) OR pm.meta_key LIKE 'hidden\\_%' OR pm.meta_key LIKE 'cur\\_%')");
	}
}
fktr_version::hooks();
?>
