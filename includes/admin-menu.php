<?php


if ( ! class_exists( 'fktrAdminMenu' ) ) :

class fktrAdminMenu {
	
	
	function __construct() {
		add_action( 'admin_menu', array('fktrAdminMenu','add_menu') );
	}
	
	public static function add_menu() {
		add_menu_page( __( 'Fakturo', FAKTURO_TEXT_DOMAIN ), __( 'Fakturo', FAKTURO_TEXT_DOMAIN ), 'manage_options', 'fakturo/admin/fakturo_admin.php', '', 'dashicons-tickets', 25  );
	}
	
}

endif; // End if class_exists check

$fktrAdminMenu = new fktrAdminMenu();

?>
