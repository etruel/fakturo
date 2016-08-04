<?php

// Exit if accessed directly
if (!defined('ABSPATH'))  {
	exit;
}

if ( ! class_exists( 'fktrAdminMenu' ) ) :

class fktrAdminMenu {
	
	
	function __construct() {
		add_action( 'admin_menu', array('fktrAdminMenu','add_menu') );
		add_action('admin_print_styles-post-new.php', array('fktrAdminMenu','styles'));
		add_action('admin_print_styles-post.php', array('fktrAdminMenu','styles'));
	}
	
	public static function add_menu() {
		add_menu_page( __( 'Fakturo', FAKTURO_TEXT_DOMAIN ), __( 'Fakturo', FAKTURO_TEXT_DOMAIN ), 'manage_options', 'fakturo/admin/fakturo_admin.php', '', 'dashicons-tickets', 26  );
		$page = add_submenu_page(
			'fakturo/admin/fakturo_admin.php',
			__( 'Providers', FAKTURO_TEXT_DOMAIN ),
			__( 'Providers', FAKTURO_TEXT_DOMAIN ),
			'manage_options',
			'edit.php?post_type=fktr_provider'
		);
		$page = add_submenu_page(
			'fakturo/admin/fakturo_admin.php',
			__( 'Clients', FAKTURO_TEXT_DOMAIN ),
			__( 'Clients', FAKTURO_TEXT_DOMAIN ),
			'manage_options',
			'edit.php?post_type=fktr_client'
		);
	
	
		add_menu_page( __( 'Fakturo Products', FAKTURO_TEXT_DOMAIN ), __( 'Fakturo Products', FAKTURO_TEXT_DOMAIN ), 'manage_options', 'edit.php?post_type=fktr_product', '', 'dashicons-tickets', 27  );
		$page = add_submenu_page(
			'edit.php?post_type=fktr_product',
			__( 'Category', FAKTURO_TEXT_DOMAIN ),
			__( 'Category', FAKTURO_TEXT_DOMAIN ),
			'manage_options',
			'edit-tags.php?taxonomy=fktr_category'
		);

		$page = add_submenu_page(
			'edit.php?post_type=fktr_product',
			__( 'Model', FAKTURO_TEXT_DOMAIN ),
			__( 'Model', FAKTURO_TEXT_DOMAIN ),
			'manage_options',
			'edit-tags.php?taxonomy=fktr_model'
		);
		
		add_menu_page( __( 'Fakturo Settings', FAKTURO_TEXT_DOMAIN ), __( 'Fakturo Settings', FAKTURO_TEXT_DOMAIN ), 'manage_options', 'edit.php?post_type=fakturo&page=ftkr-settings', '', 'dashicons-tickets', 27  );
		$page = add_submenu_page(
			'edit.php?post_type=fakturo&page=ftkr-settings',
			__( 'Country and States', FAKTURO_TEXT_DOMAIN ),
			__( 'Country and States', FAKTURO_TEXT_DOMAIN ),
			'manage_options',
			'edit-tags.php?taxonomy=fktr_locations'
		);
		
		$page = add_submenu_page(
			'edit.php?post_type=fakturo&page=ftkr-settings',
			__( 'Bank Entities', FAKTURO_TEXT_DOMAIN ),
			__( 'Bank Entities', FAKTURO_TEXT_DOMAIN ),
			'manage_options',
			'edit-tags.php?taxonomy=fktr_bank_entities'
		);
		$page = add_submenu_page(
			'edit.php?post_type=fakturo&page=ftkr-settings',
			__( 'Payment Types', FAKTURO_TEXT_DOMAIN ),
			__( 'Payment Types', FAKTURO_TEXT_DOMAIN ),
			'manage_options',
			'edit-tags.php?taxonomy=fktr_payment_types'
		);
		
		
		
		
		
		
	}
	public static function styles() {
		wp_enqueue_style('main',FAKTURO_PLUGIN_URL .'assets/css/main.css');	
		
	}
	
}

endif; // End if class_exists check

$fktrAdminMenu = new fktrAdminMenu();

?>
