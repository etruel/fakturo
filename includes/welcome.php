<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


class fktr_welcome {


	public static $minimum_capability = 'fakturo_manager';
	public function __construct() {
		add_action( 'admin_menu', array($this, 'admin_menus') );
		add_action( 'admin_head', array($this, 'admin_head' ) );
	}

	
	public function admin_menus() {
		// About Page
		add_dashboard_page(
			__( 'Welcome to Fakturo', 'fakturo'),
			__( 'Fakturo News', 'fakturo'),
			self::$minimum_capability,
			'fakturo-about',
			array($this, 'about_screen' )
		);

		// Changelog Page
		add_dashboard_page(
			__( 'Fakturo Changelog', 'fakturo'),
			__( 'Fakturo Changelog', 'fakturo'),
			self::$minimum_capability,
			'fakturo-changelog',
			array($this, 'changelog_screen' )
		);

		// Getting Started Page
		add_dashboard_page(
			__( 'Getting started with Fakturo', 'fakturo'),
			__( 'Getting started with Fakturo', 'fakturo'),
			self::$minimum_capability,
			'fakturo-getting-started',
			array($this, 'getting_started_screen' )
		);

		// Now remove them from the menus so plugins that allow customizing the admin menu don't show them
//		remove_submenu_page( 'index.php', 'fakturo-about' );
		remove_submenu_page( 'index.php', 'fakturo-changelog' );
		remove_submenu_page( 'index.php', 'fakturo-getting-started' );

	}
	
	/**
	 * Hide Individual Dashboard Pages
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function admin_head() {
		?>
		<style type="text/css" media="screen">
			/*<![CDATA[*/
			.fakturo-about-wrap .fakturo-badge { float: right; border-radius: 4px; margin: 0 0 15px 15px; max-width: 100px; }
			.fakturo-about-wrap #fakturo-header { margin-bottom: 15px; }
			.fakturo-about-wrap #fakturo-header h1 { margin-bottom: 15px !important; }
			.fakturo-about-wrap .about-text { margin: 0 0 15px; max-width: 670px; }
			.fakturo-about-wrap .feature-section { margin-top: 20px; }
			.fakturo-about-wrap .feature-section-content,
			.fakturo-about-wrap .feature-section-media { width: 50%; box-sizing: border-box; }
			.fakturo-about-wrap .feature-section-content { float: left; padding-right: 50px; }
			.fakturo-about-wrap .feature-section-content h4 { margin: 0 0 1em; }
			.fakturo-about-wrap .feature-section-media { float: right; text-align: right; margin-bottom: 20px; }
			.fakturo-about-wrap .feature-section-media img { border: 1px solid #ddd; }
			.fakturo-about-wrap .feature-section:not(.under-the-hood) .col { margin-top: 0; }
			/* responsive */
			@media all and ( max-width: 782px ) {
				.fakturo-about-wrap .feature-section-content,
				.fakturo-about-wrap .feature-section-media { float: none; padding-right: 0; width: 100%; text-align: left; }
				.fakturo-about-wrap .feature-section-media img { float: none; margin: 0 0 20px; }
			}
			/*]]>*/
		</style>
		<?php
	}

	/**
	 * Welcome header and message 
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function welcome_message() {
		list( $display_version ) = explode( '-', WPE_FAKTURO_VERSION );
		?>
		<div id="fakturo-header">
			<img class="fakturo-badge" src="<?php echo FAKTURO_PLUGIN_URL . '/assets/images/icon-256x256.png'; ?>" alt="<?php _e( 'Fakturo', 'fakturo' ); ?>" / >
			<h1><?php printf( __( 'Welcome to Fakturo %s', 'fakturo' ), $display_version ); ?> Beta</h1>
			<p class="about-text">
				<?php printf( __( 'Thank you for updating to the latest version! Fakturo %s is ready to make your money management faster, safer, and better!', 'fakturo' ), $display_version ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Navigation tabs
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function tabs() {
		$selected = isset( $_GET['page'] ) ? $_GET['page'] : 'fakturo-about';
		?>
		<h1 class="nav-tab-wrapper">
			<a class="nav-tab <?php echo $selected == 'fakturo-about' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'fakturo-about' ), 'index.php' ) ) ); ?>">
				<?php _e( "What's New", 'fakturo' ); ?>
			</a>
			<a class="nav-tab <?php echo $selected == 'fakturo-getting-started' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'fakturo-getting-started' ), 'index.php' ) ) ); ?>">
				<?php _e( 'Getting Started', 'fakturo' ); ?>
			</a>
			<a class="nav-tab <?php echo $selected == 'fakturo-changelog' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'fakturo-changelog' ), 'index.php' ) ) ); ?>">
				<?php _e( 'Changelog', 'fakturo' ); ?>
			</a>
		</h1>
		<?php
	}

	/**
	 * Parse the FAKTURO readme.txt file
	 *
	 * @since 2.0.3
	 * @return string $readme HTML formatted readme file
	 */
	public function parse_readme() {
		$file = file_exists( FAKTURO_PLUGIN_DIR . 'readme.txt' ) ? FAKTURO_PLUGIN_DIR . 'readme.txt' : null;

		if ( ! $file ) {
			$readme = '<p>' . __( 'No valid changelog was found.', 'fakturo' ) . '</p>';
		} else {
			$readme = file_get_contents( $file );
			$readme = nl2br( esc_html( $readme ) );
			$readme = explode( '== Changelog ==', $readme );
			$readme = end( $readme );

			$readme = preg_replace( '/`(.*?)`/', '<code>\\1</code>', $readme );
			$readme = preg_replace( '/[\040]\*\*(.*?)\*\*/', ' <strong>\\1</strong>', $readme );
			$readme = preg_replace( '/[\040]\*(.*?)\*/', ' <em>\\1</em>', $readme );
			$readme = preg_replace( '/= (.*?) =/', '<h4>\\1</h4>', $readme );
			$readme = preg_replace( '/\[(.*?)\]\((.*?)\)/', '<a href="\\2">\\1</a>', $readme );
		}

		return $readme;
	}
	
	
	
	public function about_screen() {
		?>
		<div class="wrap about-wrap fakturo-about-wrap">
			<?php
				// load welcome message and content tabs
				$this->welcome_message();
				$this->tabs();
			?>

			<div class="changelog"></div>
		</div>
			<?php
	}
	public function changelog_screen() {
		?>
		<div class="wrap about-wrap fakturo-about-wrap">
			<?php
				// load welcome message and content tabs
				$this->welcome_message();
				$this->tabs();
			?>

			<div class="changelog">				
				<h3><?php _e( 'Full Changelog', 'fakturo' );?></h3>
				<div class="feature-section">
					<?php echo $this->parse_readme(); ?>
				</div>
			</div>

			<div class="return-to-dashboard">
				<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'fakturo-settings' ), 'admin.php' ) ) ); ?>"><?php _e( 'Go to Fakturo Settings', 'fakturo' ); ?></a>
			</div>
		</div>
			<?php
	}
	public function getting_started_screen() {
		?>
		<div class="wrap about-wrap fakturo-about-wrap">
			<?php
				// load welcome message and content tabs
				$this->welcome_message();
				$this->tabs();
			?>

			<div class="changelog"></div>
		</div>
			<?php
	}

}
$fktr_welcome = new fktr_welcome();

?>
