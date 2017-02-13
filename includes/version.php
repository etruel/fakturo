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
	}
}
fktr_version::hooks();
?>
