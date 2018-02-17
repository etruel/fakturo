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
		global $submenu;
		
		add_menu_page( 
			__( 'Fakturo', 'fakturo' ), 
			__( 'Fakturo', 'fakturo' ), 
			'edit_fakturo_dashboard', 
			'fakturo_dashboard', 
			array( __CLASS__, 'fakturo_dashboard'),
			'dashicons-tickets', 26  );
		
		$page = add_submenu_page(
			'fakturo_dashboard',
			__( 'Providers', 'fakturo' ),
			__( 'Providers', 'fakturo' ),
			'edit_fktr_provider',
			'edit.php?post_type=fktr_provider'
		);
		$page = add_submenu_page(
			'fakturo_dashboard',
			__( 'Add New', 'fakturo' ),
			__( 'Add Provider', 'fakturo' ),
			'edit_fktr_provider',
			'post-new.php?post_type=fktr_provider'
		);
		$page = add_submenu_page(
			'fakturo_dashboard',
			__( 'Clients', 'fakturo' ),
			__( 'Clients', 'fakturo' ),
			'edit_fktr_client',
			'edit.php?post_type=fktr_client'
		);
		$page = add_submenu_page(
			'fakturo_dashboard',
			__( 'Add New', 'fakturo' ),
			__( 'Add Client', 'fakturo' ),
			'edit_fktr_client',
			'post-new.php?post_type=fktr_client'
		);
		$page = add_submenu_page(
			'fakturo_dashboard',
			__( 'Reports', 'fakturo'),
			__( 'Reports', 'fakturo'),
			'fakturo_reports',
			'fakturo_reports',
			array('reports','page')
			
		);	
		$page = add_submenu_page(
			'fakturo_dashboard',
			__( 'Settings', 'fakturo' ), 
			__( 'Settings', 'fakturo' ), 
			'edit_fakturo_settings', 
			'fakturo-settings',
			array('fktrSettings','fakturo_settings'), 
			'dashicons-tickets', 27  );
		$page = add_submenu_page(
			null,
			__( 'Settings', 'fakturo' ), 
			__( 'Settings', 'fakturo' ), 
			'edit_fakturo_settings', 
			'fakturo-settings-system',
			array('fktrSettings','fakturo_settings_system'), 
			'dashicons-tickets', 27  );	

		
		$page = add_submenu_page(
			null,
			__( 'Settings', 'fakturo' ), 
			__( 'Settings', 'fakturo' ), 
			'edit_fakturo_settings', 
			'fakturo-settings-dashboard',
			array('fktrSettings','fakturo_settings_dashboard'), 
			'dashicons-tickets', 27  );	

		


		$page = add_submenu_page(
			null,
			__( 'Settings', 'fakturo' ), 
			__( 'Settings', 'fakturo' ), 
			'edit_fakturo_settings', 
			'fakturo-license-page',
			array('fktr_licenses_handlers','license_page'), 
			'dashicons-tickets', 27  );	
		
	
		add_menu_page( 
			__( 'Products', 'fakturo' ), 
			__( 'Products', 'fakturo' ), 
			'edit_fktr_product', 
			'edit.php?post_type=fktr_product', 
			'', 
			'dashicons-tickets', 27  );
		
		$page = add_submenu_page(
			'edit.php?post_type=fktr_product',
			__( 'Add New', 'fakturo' ),
			__( 'Add Product', 'fakturo' ),
			'edit_fktr_product',
			'post-new.php?post_type=fktr_product'
		);

		$page = add_submenu_page(
			'edit.php?post_type=fktr_product',
			__( 'Category', 'fakturo' ),
			__( 'Category', 'fakturo' ),
			'edit_fktr_product',
			'edit-tags.php?taxonomy=fktr_category'
		);

		$page = add_submenu_page(
			'edit.php?post_type=fktr_product',
			__( 'Models', 'fakturo' ),
			__( 'Models', 'fakturo' ),
			'edit_fktr_product',
			'edit-tags.php?taxonomy=fktr_model'
		);
		$setting_system = get_option('fakturo_system_options_group', false);
		if (isset($setting_system['use_stock_product']) && $setting_system['use_stock_product']) {
			$page = add_submenu_page(
			'edit.php?post_type=fktr_product',
			__( 'Stock', 'fakturo' ),
			__( 'Stock', 'fakturo' ),
			'edit_fktr_product',
			'edit-tags.php?taxonomy=fktr_stock'
		);
			
		}
			
		add_menu_page( 
			__( 'Sales Invoices', 'fakturo' ), 
			__( 'Sales Invoices', 'fakturo' ), 
			'edit_fktr_sales', 
			'edit.php?post_type=fktr_sale', 
			'', 
			'dashicons-tickets', 26  );
		$page = add_submenu_page(
			'edit.php?post_type=fktr_sale',
			__( 'Add New', 'fakturo' ),
			__( 'Add Invoice', 'fakturo' ),
			'edit_fktr_sales',
			'post-new.php?post_type=fktr_sale'
		);
		$page = add_submenu_page(
			'edit.php?post_type=fktr_sale',
			__( 'Receipts', 'fakturo' ),
			__( 'Receipts', 'fakturo' ),
			'edit_fktr_receipt',
			'edit.php?post_type=fktr_receipt'
		);
		$page = add_submenu_page(
			'edit.php?post_type=fktr_sale',
			__( 'Add New Receipt', 'fakturo' ),
			__( 'Add New Receipt', 'fakturo' ),
			'edit_fktr_receipt',
			'post-new.php?post_type=fktr_receipt'
		);
		$page = add_submenu_page(
			'edit.php?post_type=fktr_sale',
			__( 'Checks', 'fakturo' ),
			__( 'Checks', 'fakturo' ),
			'manage_fktr_check',
			'edit-tags.php?taxonomy=fktr_check'
		);
		
		
		// other tests
//		remove_submenu_page('fakturo_dashboard', 'post-new.php?post_type=fktr_provider');
//		unset($submenu['edit.php?post_type=fktr_provider'][10]);

	}
	
	public static function styles() {
		global $post_type, $current_screen;
		if( strpos($current_screen->id, "fktr")!==FALSE || strpos($current_screen->id, "fakturo")!==FALSE ) {
			wp_enqueue_style('main',FAKTURO_PLUGIN_URL .'assets/css/main.css');	
			wp_enqueue_style('fktr_icons',FAKTURO_PLUGIN_URL .'assets/css/icons.css');	
		}
		if($current_screen->id == "toplevel_page_fakturo_dashboard" ) {
			wp_enqueue_style('fktr_dashboard', FAKTURO_PLUGIN_URL .'assets/css/dashboard.css');	
		}
		 
		// hide some items that we don't want to show in WP menu
		echo '<style>.wp-submenu li a[href="post-new.php?post_type=fktr_provider"] {display: none !important;}</style>';
		echo '<style>.wp-submenu li a[href="post-new.php?post_type=fktr_client"] {display: none !important;}</style>';
		echo '<style>.wp-submenu li a[href="post-new.php?post_type=fktr_product"] {display: none !important;}</style>';
		
	}
	public static function fakturo_dashboard() {
		global $post_type, $current_screen;
		
		include_once FAKTURO_PLUGIN_DIR.'includes/settings/dashboard.php';
	}
	
}

endif; // End if class_exists check

$fktrAdminMenu = new fktrAdminMenu();

?>
