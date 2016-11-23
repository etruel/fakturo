<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


class fktr_welcome {


	public static $minimum_capability = 'manage_options';
	public function __construct() {
		add_action( 'admin_menu', array(__CLASS__, 'admin_menus') );
	}

	
	public static function admin_menus() {
		// About Page
		add_dashboard_page(
			__( 'Welcome to Fakturo', FAKTURO_TEXT_DOMAIN),
			__( 'Fakturo News', FAKTURO_TEXT_DOMAIN),
			self::$minimum_capability,
			'fakturo-about',
			array(__CLASS__, 'about_screen' )
		);

		// Changelog Page
		add_dashboard_page(
			__( 'Fakturo Changelog', FAKTURO_TEXT_DOMAIN),
			__( 'Fakturo Changelog', FAKTURO_TEXT_DOMAIN),
			self::$minimum_capability,
			'fakturo-changelog',
			array(__CLASS__, 'changelog_screen' )
		);

		// Getting Started Page
		add_dashboard_page(
			__( 'Getting started with Fakturo', FAKTURO_TEXT_DOMAIN),
			__( 'Getting started with Fakturo', FAKTURO_TEXT_DOMAIN),
			self::$minimum_capability,
			'fakturo-getting-started',
			array(__CLASS__, 'getting_started_screen' )
		);


	}
	public static function about_screen() {

	}
	public static function changelog_screen() {
		
	}
	public static function getting_started_screen() {
		
	}

}
$fktr_welcome = new fktr_welcome();

?>
