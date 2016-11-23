<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class fktr_version {
	function __construct() {
		add_action('admin_init', array(__CLASS__, 'init'), 11);
	}
	public static function init() {
		$current_version = get_option('fktr_db_version', false);
		if (!$current_version) {
			// First time install
			update_option('fktr_db_version', WPE_FAKTURO_VERSION);
			fktrUserRoles::regenerate();
			wp_safe_redirect(admin_url( 'index.php?page=fakturo-getting-started'));
			exit;

		} else if (version_compare($current_version, WPE_FAKTURO_VERSION, '<')) {
			// Update
			update_option('fktr_db_version', WPE_FAKTURO_VERSION);
			fktrUserRoles::regenerate(true);
			wp_safe_redirect(admin_url( 'index.php?page=fakturo-about'));
			exit;
			
		}
		
	}
}
$fktr_version = new fktr_version();
?>
