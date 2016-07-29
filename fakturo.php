<?php
/*
 Plugin Name: Fakturo
 Plugin URI: http://www.wpematico.com
 Description: Make invoices with products and clients.  If you like it, please rate it 5 stars.
 Version: 1.0.0
 Author: etruel <esteban@netmdp.com>
 Author URI: http://www.netmdp.com
 */

if ( ! defined( 'ABSPATH' ) ) exit;
// Plugin version
if ( ! defined('WPE_FAKTURO_VERSION' ) ) define('WPE_FAKTURO_VERSION', '0.0' ); 

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
		
	}
	private function includes() {
		
	}
	private function setupGlobals() {

		// Plugin Folder Path
		if (!defined('WPE_FAKTURO_PLUGIN_DIR')) {
			define('WPE_FAKTURO_PLUGIN_DIR', plugin_dir_path( __FILE__ ));
		}

		// Plugin Folder URL
		if (!defined('WPE_FAKTURO_PLUGIN_URL')) {
			define('WPE_FAKTURO_PLUGIN_URL', plugin_dir_url(__FILE__));
		}

		// Plugin Root File
		if (!defined('WPE_FAKTURO_PLUGIN_FILE')) {
			define('WPE_FAKTURO_PLUGIN_FILE', __FILE__ );
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
