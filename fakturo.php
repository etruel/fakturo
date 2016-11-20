<?php
/*
 Plugin Name: Fakturo
 Plugin URI: http://www.wpematico.com
 Description: Make invoices with products and clients.  If you like it, please rate it 5 stars.
 Version: 0.2 Beta
 Author: etruel <esteban@netmdp.com>
 Author URI: http://www.netmdp.com
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Plugin version
if ( ! defined('WPE_FAKTURO_VERSION' ) ) define('WPE_FAKTURO_VERSION', '0.2' ); 

if ( ! class_exists( 'fakturo' ) ) :

class fakturo {
	
	private static $instance = null;
	
	public static function getInstance() {
		if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
	}
	function __construct() {
		$this->setupGlobals();
		$this->includes();
		$this->loadTextDomain();
	}
	private function includes() {
		require_once FAKTURO_PLUGIN_DIR . 'includes/helps.php'; 
		require_once FAKTURO_PLUGIN_DIR . 'includes/functions.php'; 
		require_once FAKTURO_PLUGIN_DIR . 'includes/admin-menu.php'; 
		require_once FAKTURO_PLUGIN_DIR . 'includes/post-types-products.php'; 
		require_once FAKTURO_PLUGIN_DIR . 'includes/post-types-providers.php'; 
		require_once FAKTURO_PLUGIN_DIR . 'includes/post-types-clients.php'; 
		require_once FAKTURO_PLUGIN_DIR . 'includes/post-types-sales.php'; 
		require_once FAKTURO_PLUGIN_DIR . 'includes/post-types-receipts.php'; 
		require_once FAKTURO_PLUGIN_DIR . 'includes/post-types-print-templates.php'; 
		
		require_once FAKTURO_PLUGIN_DIR . 'includes/taxonomy/sale-points.php'; 
		require_once FAKTURO_PLUGIN_DIR . 'includes/taxonomy/currencies.php'; 
		require_once FAKTURO_PLUGIN_DIR . 'includes/taxonomy/taxes.php'; 
		require_once FAKTURO_PLUGIN_DIR . 'includes/taxonomy/tax-conditions.php'; 
		require_once FAKTURO_PLUGIN_DIR . 'includes/taxonomy/invoice-types.php'; 
		require_once FAKTURO_PLUGIN_DIR . 'includes/taxonomy/payment-types.php'; 
		require_once FAKTURO_PLUGIN_DIR . 'includes/taxonomy/bank-entities.php'; 
		require_once FAKTURO_PLUGIN_DIR . 'includes/taxonomy/countries.php'; 
		require_once FAKTURO_PLUGIN_DIR . 'includes/taxonomy/product-types.php'; 
		require_once FAKTURO_PLUGIN_DIR . 'includes/taxonomy/locations.php'; 
		require_once FAKTURO_PLUGIN_DIR . 'includes/taxonomy/packagings.php'; 
		require_once FAKTURO_PLUGIN_DIR . 'includes/taxonomy/price-scales.php'; 
		require_once FAKTURO_PLUGIN_DIR . 'includes/taxonomy/origins.php'; 
		require_once FAKTURO_PLUGIN_DIR . 'includes/taxonomy/stocks.php'; 
		require_once FAKTURO_PLUGIN_DIR . 'includes/taxonomy/checks.php'; 
		
		require_once FAKTURO_PLUGIN_DIR . 'includes/settings/settings.php'; 
		require_once FAKTURO_PLUGIN_DIR . 'includes/users/users-list.php'; 
		require_once FAKTURO_PLUGIN_DIR . 'includes/users/users-roles.php'; 

		require_once FAKTURO_PLUGIN_DIR . 'includes/libs/fktr_tpl.php'; 
		
		do_action('fakturo_include_files');
		
	}
	private function setupGlobals() {

		// Plugin Folder Path
		if (!defined('FAKTURO_PLUGIN_DIR')) {
			define('FAKTURO_PLUGIN_DIR', plugin_dir_path( __FILE__ ));
		}

		// Plugin Folder URL
		if (!defined('FAKTURO_PLUGIN_URL')) {
			define('FAKTURO_PLUGIN_URL', plugin_dir_url(__FILE__));
		}

		// Plugin Root File
		if (!defined('FAKTURO_PLUGIN_FILE')) {
			define('FAKTURO_PLUGIN_FILE', __FILE__ );
		}
		
		// Plugin text domain
		if (!defined('FAKTURO_TEXT_DOMAIN')) {
			define('FAKTURO_TEXT_DOMAIN', 'fakturo' );
		}

	}
	public function loadTextDomain() {
		// Set filter for plugin's languages directory
		$lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
		$lang_dir = apply_filters('fakturo_languages_directory', $lang_dir );

		// Traditional WordPress plugin locale filter
		$locale        = apply_filters( 'plugin_locale',  get_locale(), 'fakturo' );
		$mofile        = sprintf( '%1$s-%2$s.mo', 'fakturo', $locale );

		// Setup paths to current locale file
		$mofile_local  = $lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/fakturo/' . $mofile;

		if ( file_exists( $mofile_global ) ) {
			// Look in global /wp-content/languages/fakturo/ folder
			load_textdomain( 'fakturo', $mofile_global );
		} elseif ( file_exists( $mofile_local ) ) {
			// Look in local /wp-content/plugins/fakturo/languages/ folder
			load_textdomain( 'fakturo', $mofile_local );
		} else {
			// Load the default language files
			load_plugin_textdomain( 'fakturo', false, $lang_dir );
		}
		
	}
}

endif; // End if class_exists check

$fakturo = null;
function getClassFakturo() {
	global $fakturo;
	if (is_null($fakturo)) {
		$fakturo = fakturo::getInstance();
	}
	return $fakturo;
}
getClassFakturo();
?>
