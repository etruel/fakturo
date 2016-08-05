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
		$page = add_submenu_page(
			'edit.php?post_type=fakturo&page=ftkr-settings',
			__( 'Tax Conditions', FAKTURO_TEXT_DOMAIN ),
			__( 'Tax Conditions', FAKTURO_TEXT_DOMAIN ),
			'manage_options',
			'edit-tags.php?taxonomy=fktr_tax_conditions'
		);
		$page = add_submenu_page(
			'edit.php?post_type=fakturo&page=ftkr-settings',
			__( 'Price Scales', FAKTURO_TEXT_DOMAIN ),
			__( 'Price Scales', FAKTURO_TEXT_DOMAIN ),
			'manage_options',
			'edit-tags.php?taxonomy=fktr_price_scales'
		);
		$page = add_submenu_page(
			'edit.php?post_type=fakturo&page=ftkr-settings',
			__( 'Currencies', FAKTURO_TEXT_DOMAIN ),
			__( 'Currencies', FAKTURO_TEXT_DOMAIN ),
			'manage_options',
			'edit-tags.php?taxonomy=fktr_currencies'
		);
		
		
		
		
		
		
	}
	public static function styles() {
		global $post_type, $current_screen;
		if( strpos($current_screen->id, "fktr")!==FALSE || strpos($current_screen->id, "fakturo")!==FALSE ) {
			wp_enqueue_style('main',FAKTURO_PLUGIN_URL .'assets/css/main.css');	
			wp_enqueue_style('icons',FAKTURO_PLUGIN_URL .'assets/css/icons.css');	
		}
		
	}
	
}

endif; // End if class_exists check

$fktrAdminMenu = new fktrAdminMenu();

?>
