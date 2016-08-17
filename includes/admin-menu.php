<?php

// Exit if accessed directly
if (!defined('ABSPATH'))  {
	exit;
}

if ( ! class_exists( 'fktrAdminMenu' ) ) :

class fktrAdminMenu {
	
	
	function __construct() {
		add_action( 'admin_menu', array('fktrAdminMenu','add_menu') );
		add_action('admin_print_styles', array('fktrAdminMenu','styles'));
	}
	
	public static function add_menu() {
		add_menu_page( 
			__( 'Fakturo', FAKTURO_TEXT_DOMAIN ), 
			__( 'Fakturo', FAKTURO_TEXT_DOMAIN ), 
			'edit_fakturo_settings', 
			'fakturo_dashboard', 
			array( __CLASS__, 'fakturo_dashboard'),
			'dashicons-tickets', 26  );
		
		$page = add_submenu_page(
			'fakturo_dashboard',
			__( 'Providers', FAKTURO_TEXT_DOMAIN ),
			__( 'Providers', FAKTURO_TEXT_DOMAIN ),
			'edit_fktr_provider',
			'edit.php?post_type=fktr_provider'
		);
		$page = add_submenu_page(
			'fakturo_dashboard',
			__( 'Clients', FAKTURO_TEXT_DOMAIN ),
			__( 'Clients', FAKTURO_TEXT_DOMAIN ),
			'edit_fktr_client',
			'edit.php?post_type=fktr_client'
		);

		$page = add_submenu_page(
			'fakturo_dashboard',
			__( 'Settings', FAKTURO_TEXT_DOMAIN ), 
			__( 'Settings', FAKTURO_TEXT_DOMAIN ), 
			'edit_fakturo_settings', 
			'fakturo-settings',
			array('fktrSettings','fakturo_settings'), 
			'dashicons-tickets', 27  );
		$page = add_submenu_page(
			null,
			__( 'Settings', FAKTURO_TEXT_DOMAIN ), 
			__( 'Settings', FAKTURO_TEXT_DOMAIN ), 
			'manage_options', 
			'fakturo-settings-system',
			array('fktrSettings','fakturo_settings_system'), 
			'dashicons-tickets', 27  );	
		
	
	
		add_menu_page( __( 'Fakturo Products', FAKTURO_TEXT_DOMAIN ), __( 'Fakturo Products', FAKTURO_TEXT_DOMAIN ), 'edit_fktr_product', 'edit.php?post_type=fktr_product', '', 'dashicons-tickets', 27  );
		$page = add_submenu_page(
			'edit.php?post_type=fktr_product',
			__( 'Category', FAKTURO_TEXT_DOMAIN ),
			__( 'Category', FAKTURO_TEXT_DOMAIN ),
			'edit_fktr_product',
			'edit-tags.php?taxonomy=fktr_category'
		);

		$page = add_submenu_page(
			'edit.php?post_type=fktr_product',
			__( 'Models', FAKTURO_TEXT_DOMAIN ),
			__( 'Models', FAKTURO_TEXT_DOMAIN ),
			'edit_fktr_product',
			'edit-tags.php?taxonomy=fktr_model'
		);
	
		
//		remove_submenu_page( 'fakturo_dashboard', 'fakturo_dashboard' ); //remuevo 1º subitem repetido Fakturo

		
		
	}
	
	public static function styles() {
		global $post_type, $current_screen;
		if( strpos($current_screen->id, "fktr")!==FALSE || strpos($current_screen->id, "fakturo")!==FALSE ) {
			wp_enqueue_style('main',FAKTURO_PLUGIN_URL .'assets/css/main.css');	
			wp_enqueue_style('icons',FAKTURO_PLUGIN_URL .'assets/css/icons.css');	
		}
		
	}
	public static function fakturo_dashboard() {
		global $post_type, $current_screen;
		echo "Fakturo Dashboard";
	}
	
}

endif; // End if class_exists check

$fktrAdminMenu = new fktrAdminMenu();

?>
