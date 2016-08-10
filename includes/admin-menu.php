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
			'manage_options', 
			'fakturo_dashboard', 
			array( __CLASS__, 'fakturo_dashboard'),
			'dashicons-tickets', 26  );
		
		$page = add_submenu_page(
			'fakturo_dashboard',
			__( 'Providers', FAKTURO_TEXT_DOMAIN ),
			__( 'Providers', FAKTURO_TEXT_DOMAIN ),
			'manage_options',
			'edit.php?post_type=fktr_provider'
		);
		$page = add_submenu_page(
			'fakturo_dashboard',
			__( 'Clients', FAKTURO_TEXT_DOMAIN ),
			__( 'Clients', FAKTURO_TEXT_DOMAIN ),
			'manage_options',
			'edit.php?post_type=fktr_client'
		);

		$page = add_submenu_page(
			'fakturo_dashboard',
			__( 'Settings', FAKTURO_TEXT_DOMAIN ), 
			__( 'Settings', FAKTURO_TEXT_DOMAIN ), 
			'manage_options', 
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
			__( 'Models', FAKTURO_TEXT_DOMAIN ),
			__( 'Models', FAKTURO_TEXT_DOMAIN ),
			'manage_options',
			'edit-tags.php?taxonomy=fktr_model'
		);
	
		
//		remove_submenu_page( 'fakturo_dashboard', 'fakturo_dashboard' ); //remuevo 1º subitem repetido Fakturo


		/**
		 * De aca para abajo no irian 
		 * ya que se accede desde las tabs de settings del menu Fakturo 
		 */
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
	public static function fakturo_dashboard() {
		global $post_type, $current_screen;
		echo "Fakturo Dashboard";
	}
	
}

endif; // End if class_exists check

$fktrAdminMenu = new fktrAdminMenu();

?>
