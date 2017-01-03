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
			__( 'Add New', FAKTURO_TEXT_DOMAIN ),
			__( 'Add Provider', FAKTURO_TEXT_DOMAIN ),
			'edit_fktr_provider',
			'post-new.php?post_type=fktr_provider'
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
			__( 'Add New', FAKTURO_TEXT_DOMAIN ),
			__( 'Add Client', FAKTURO_TEXT_DOMAIN ),
			'edit_fktr_provider',
			'post-new.php?post_type=fktr_client'
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
			'edit_fakturo_settings', 
			'fakturo-settings-system',
			array('fktrSettings','fakturo_settings_system'), 
			'dashicons-tickets', 27  );	

		$page = add_submenu_page(
			null,
			__( 'Settings', FAKTURO_TEXT_DOMAIN ), 
			__( 'Settings', FAKTURO_TEXT_DOMAIN ), 
			'edit_fakturo_settings', 
			'fakturo-license-page',
			array('fktr_licenses_handlers','license_page'), 
			'dashicons-tickets', 27  );	
		
	
		add_menu_page( 
			__( 'Products', FAKTURO_TEXT_DOMAIN ), 
			__( 'Products', FAKTURO_TEXT_DOMAIN ), 
			'edit_fktr_product', 
			'edit.php?post_type=fktr_product', 
			'', 
			'dashicons-tickets', 27  );
		
		$page = add_submenu_page(
			'edit.php?post_type=fktr_product',
			__( 'Add New', FAKTURO_TEXT_DOMAIN ),
			__( 'Add Product', FAKTURO_TEXT_DOMAIN ),
			'edit_fktr_product',
			'post-new.php?post_type=fktr_product'
		);

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
		$setting_system = get_option('fakturo_system_options_group', false);
		if (isset($setting_system['use_stock_product']) && $setting_system['use_stock_product']) {
			$page = add_submenu_page(
			'edit.php?post_type=fktr_product',
			__( 'Stock', FAKTURO_TEXT_DOMAIN ),
			__( 'Stock', FAKTURO_TEXT_DOMAIN ),
			'edit_fktr_product',
			'edit-tags.php?taxonomy=fktr_stock'
		);
			
		}
			
		add_menu_page( 
			__( 'Sales Invoices', FAKTURO_TEXT_DOMAIN ), 
			__( 'Sales Invoices', FAKTURO_TEXT_DOMAIN ), 
			'edit_fktr_sales', 
			'edit.php?post_type=fktr_sale', 
			'', 
			'dashicons-tickets', 26  );
		$page = add_submenu_page(
			'edit.php?post_type=fktr_sale',
			__( 'Add New', FAKTURO_TEXT_DOMAIN ),
			__( 'Add Invoice', FAKTURO_TEXT_DOMAIN ),
			'edit_fktr_sales',
			'post-new.php?post_type=fktr_sale'
		);
		$page = add_submenu_page(
			'edit.php?post_type=fktr_sale',
			__( 'Receipts', FAKTURO_TEXT_DOMAIN ),
			__( 'Receipts', FAKTURO_TEXT_DOMAIN ),
			'edit_fktr_receipt',
			'edit.php?post_type=fktr_receipt'
		);
		$page = add_submenu_page(
			'edit.php?post_type=fktr_sale',
			__( 'Add New Receipt', FAKTURO_TEXT_DOMAIN ),
			__( 'Add New Receipt', FAKTURO_TEXT_DOMAIN ),
			'edit_fktr_receipt',
			'post-new.php?post_type=fktr_receipt'
		);
		$page = add_submenu_page(
			'edit.php?post_type=fktr_sale',
			__( 'Checks', FAKTURO_TEXT_DOMAIN ),
			__( 'Checks', FAKTURO_TEXT_DOMAIN ),
			'edit_fktr_receipt',
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
		$setting_system = get_option('fakturo_system_options_group', false);
		$currencyDefault = get_fakturo_term($setting_system['currency'], 'fktr_currencies');

		$sales_today = get_sales_on_range(strtotime('-1 day', time()), strtotime('+1 day', time()));
		error_log(var_export($sales_today, true));
		$earning_today = 0;
		$count_sales_today = 0;
		foreach ($sales_today as $id_sale_today) {
			$count_sales_today++;
			$sales_data = fktrPostTypeSales::get_sale_data($id_sale_today);
			$earning_today = $earning_today+fakturo_transform_money($sales_data['invoice_currency'], $setting_system['currency'], $sales_data['in_total']);
		}
		$money_format_today = (($setting_system['currency_position'] == 'before')?$currencyDefault->symbol.' ':'').''.number_format($earning_today, $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']).''.(($setting_system['currency_position'] == 'after')?' '.$currencyDefault->symbol:'');
		$sales_current_month = get_sales_on_range(strtotime('first day of this month', time()), time());
		$earning_current_month = 0;
		$count_sales_current_month = 0;
		foreach ($sales_current_month as $id_sale_current_month) {
			$count_sales_current_month++;
			$sales_data = fktrPostTypeSales::get_sale_data($id_sale_current_month);
			$earning_current_month = $earning_current_month+fakturo_transform_money($sales_data['invoice_currency'], $setting_system['currency'], $sales_data['in_total']);
		}

		$money_format_current_month = (($setting_system['currency_position'] == 'before')?$currencyDefault->symbol.' ':'').''.number_format($earning_current_month, $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']).''.(($setting_system['currency_position'] == 'after')?' '.$currencyDefault->symbol:'');
		
		
		$sales_last_month = get_sales_on_range(strtotime('first day of last month', time()), strtotime('last day of last month', time()));

		$earning_last_month = 0;
		$count_sales_last_month = 0;
		
		foreach ($sales_last_month as $id_sale_last_month) {
			$count_sales_last_month++;
			$sales_data = fktrPostTypeSales::get_sale_data($id_sale_last_month);
			$earning_last_month = $earning_last_month+fakturo_transform_money($sales_data['invoice_currency'], $setting_system['currency'], $sales_data['in_total']);
		}

		$money_format_last_month = (($setting_system['currency_position'] == 'before')?$currencyDefault->symbol.' ':'').''.number_format($earning_last_month, $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']).''.(($setting_system['currency_position'] == 'after')?' '.$currencyDefault->symbol:'');
		
		$sales_total = get_sales_on_range(0, 0);
		error_log(var_export($sales_total, true));
		$earning_total = 0;
		$count_sales_total = 0;
		
		foreach ($sales_total as $id_sale_total) {
			$count_sales_total++;
			$sales_data = fktrPostTypeSales::get_sale_data($id_sale_total);
			$earning_total = $earning_last_month+fakturo_transform_money($sales_data['invoice_currency'], $setting_system['currency'], $sales_data['in_total']);
		}

		$money_format_total = (($setting_system['currency_position'] == 'before')?$currencyDefault->symbol.' ':'').''.number_format($earning_total, $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']).''.(($setting_system['currency_position'] == 'after')?' '.$currencyDefault->symbol:'');
		
		
		
		
		include_once FAKTURO_PLUGIN_DIR.'includes/settings/dashboard.php';
	}
	
}

endif; // End if class_exists check

$fktrAdminMenu = new fktrAdminMenu();

?>
