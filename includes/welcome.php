<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


class fktr_welcome {


	public static $minimum_capability = 'edit_fakturo_settings';
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

		// Privacy
		add_dashboard_page(
			__('Fakturo Privacy', 'fakturo'),
			__('Fakturo Privacy', 'fakturo'),
			self::$minimum_capability,
			'fakturo-privacy',
			array($this, 'privacy_screen')
		);

		// Now remove them from the menus so plugins that allow customizing the admin menu don't show them
//		remove_submenu_page( 'index.php', 'fakturo-about' );
		

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
				[class*="dashboard_page_"] #wpcontent { /*background: #fff;*/ padding: 0 24px; }
				[class*="dashboard_page_fakturo-"] .wpe-flex{ display: flex; }
				[class*="dashboard_page_fakturo-"] .about__section { background: #fff; font-size: 1.2em; margin: 0;}
				[class*="dashboard_page_fakturo-"] .about__section.mb-0{ margin-bottom: 0; }
				[class*="dashboard_page_fakturo-"] .about__section h2, .about__section h3, .about__section h4, .about__section h5 { margin: 1em 0; }
				[class*="dashboard_page_fakturo-"] .about__section a{ color: #222; }
				[class*="dashboard_page_fakturo-"] .about__section a:hover{ color: #f7b63e; }
				[class*="dashboard_page_fakturo-"] .about__header { background-image: <?php echo 'url('.FAKTURO_PLUGIN_URL . 'assets/images/about-header.png)'; ?>; background-size: 80%; background-position: bottom right; padding-top: 3rem; background-color: #f7b63e; height: initial; padding-bottom: 0; }
				[class*="dashboard_page_fakturo-"] .about__header-title{ margin: 5rem 2rem 0;padding: 1em 0; }
				[class*="dashboard_page_fakturo-"] .about__header-title p { margin: 0; padding: 80px 0 0; font-size: 4em; color: #222; line-height: 1; font-weight: 900; text-transform: uppercase; }
				[class*="dashboard_page_fakturo-"] .about__header-title p span { color: #333; }
				[class*="dashboard_page_fakturo-"] .about__header-text { max-width: 25em; margin: 0 2rem 3rem; padding: 0; font-size: 1.5em; line-height: 1.4;}
				[class*="dashboard_page_fakturo-"] .about__header-text p { color: #222; margin-top: 0; text-align: left; }
				[class*="dashboard_page_fakturo-"] .about__header-navigation { display: flex; justify-content: center; width: 100%; margin-bottom: 0; background: #fff; color: #222; border-color: #222; padding-top: 0;}
				[class*="dashboard_page_fakturo-"] .about__header-navigation .nav-tab{color: #222 ; margin: 0; padding: 24px 32px; float: none; font-size: 1.4em; line-height: 1; border-style: solid; background: 0 0; border-width: 0 0 3px; border-color: transparent;}
				[class*="dashboard_page_fakturo-"] .about__header-navigation .nav-tab-active { margin-bottom: -3px; }
				[class*="dashboard_page_fakturo-"] .about__header-navigation .nav-tab-active:active, [class*="dashboard_page_fakturo-"] .about__header-navigation .nav-tab-active:hover, [class*="dashboard_page_fakturo-"] .about__header-navigation .nav-tab-active { color: #f7b63e; border-color: #f7b63e;}
				[class*="dashboard_page_fakturo-"] .about__header-navigation .nav-tab:active, [class*="dashboard_page_fakturo-"] .about__header-navigation .nav-tab:hover{ background: #f5f5f5; color: #f7b63e; }
				[class*="dashboard_page_fakturo-"] .about__container .has-accent-background-color { background: #f7b63e; }
				[class*="dashboard_page_fakturo-"] .about__container .has-subtle-background-color { background: #f9f9f9; }
				[class*="dashboard_page_fakturo-"] .about__container .text{ font-size: 14px; }
				[class*="dashboard_page_fakturo-"] .about__header-title .fakturo-badge { align-self: flex-end; margin-bottom: 10px; max-height: 80px; width: auto; }
				[class*="dashboard_page_fakturo-"] .about__section.about__section_height { min-height: 560px; }
				[class*="dashboard_page_fakturo-"] .about__section.about__section_height-2 { min-height: 400px; }
				[class*="dashboard_page_fakturo-"] .about__section.is-feature { font-size: 1.4em; }
				[class*="dashboard_page_fakturo-"] .about__container h1, .about__container h2, .about__container h3.is-larger-heading{
					    margin-top: 0; margin-bottom: .5em; font-size: 1.75em; line-height: 1.2; font-weight: 600; }
				[class*="dashboard_page_fakturo-"] .about__container h1.is-smaller-heading, .about__container h2.is-smaller-heading, .about__container h3 { margin-top: 0; font-size: 1.25em; font-weight: 700; }
				[class*="dashboard_page_fakturo-"] .about__container .about__image { padding: 0 32px; }
				[class*="dashboard_page_fakturo-"] .about__container .about__image.mx-auto { margin-left: auto; margin-right: auto; }
				[class*="dashboard_page_fakturo-"] .about__section .span-text { font-size: .9em; }
				[class*="dashboard_page_fakturo-"] .feature-section a, .about__section p a { font-weight: 600; }
				[class*="dashboard_page_fakturo-"] .addon_block { display: flex; margin-bottom: 1em;}
				[class*="dashboard_page_fakturo-"] .addon_block .addon_img img { display:block; max-width: 120px; height: auto; margin-right: 10px; }
				[class*="dashboard_page_fakturo-"] .addon_block .addon_text { text-align: right;}
				[class*="dashboard_page_fakturo-"] .addon_block .addon_text p { margin: 0; font-size: 13px; }
				[class*="dashboard_page_fakturo-"] .about__section.has-2-columns, .about__section.has-3-columns, .about__section.has-4-columns, .about__section.has-overlap-style { display: grid; }
				[class*="dashboard_page_fakturo-"] .about__section.has-2-columns{ -ms-grid-columns: 1fr 1fr; grid-template-columns: 1fr 1fr; }
				[class*="dashboard_page_fakturo-"] .about__section.has-2-columns .column:nth-of-type(2n+1) { -ms-grid-column: 1; grid-column-start: 1; }
				[class*="dashboard_page_fakturo-"] .about__section.has-2-columns .column:nth-of-type(2n) { -ms-grid-column: 2; grid-column-start: 2; }
				[class*="dashboard_page_fakturo-"] .about__section .column.is-edge-to-edge{ color: #fff; padding: 0; }
				[class*="dashboard_page_fakturo-"] .about__section + .about__section .column{ padding-top: 32px; }
				[class*="dashboard_page_fakturo-"] .about__container .is-vertically-aligned-center{ align-self: center; }
				@media all and ( max-width: 1035px ) {
					[class*="dashboard_page_fakturo-"] .about__header { background-size: 95%; }
				}
				@media all and ( max-width: 782px ) {
					[class*="dashboard_page_fakturo-"] .about__header{	background-image: none; }
					[class*="dashboard_page_fakturo-"] .about__header-title{ margin-top: 0; padding-top: 0;}
					[class*="dashboard_page_fakturo-"] .about__header-title p{ font-size: 3em; }
				}
				@media all and ( max-width: 782px ) and (min-width: 481px) {
					[class*="dashboard_page_fakturo-"] .about__header-navigation .nav-tab{ padding: 24px 16px; }
				}
				@media all and ( max-width: 600px ) and (min-width: 481px) {
					[class*="dashboard_page_fakturo-"] .about__header-navigation .nav-tab{ font-size: 1.1em; }
				}
				@media all and ( max-width: 600px ) {
					[class*="dashboard_page_fakturo-"] .about__header-title p{ font-size: 2.25em; }
					[class*="dashboard_page_fakturo-"] .about__section.has-2-columns, .about__section.has-2-columns.is-wider-left, .about__section.has-2-columns.is-wider-right, .about__section.has-3-columns{ display: block; padding-bottom: 16px; }
					[class*="dashboard_page_fakturo-"] .about__section + .about__section .column{ padding-top: 16px; }
					[class*="dashboard_page_fakturo-"] .about__section.has-2-columns .column:nth-of-type(n){ padding-top: 16px; padding-bottom: 16px; }
					[class*="dashboard_page_fakturo-"] .about__header-navigation{ flex-direction: column; }
					[class*="dashboard_page_fakturo-"] .about__header-navigation .nav-tab { float: none; display: block; margin-bottom: 0; padding: 16px 16px; border-left-width: 6px; border-bottom: none; }
					[class*="dashboard_page_fakturo-"] .about__header-navigation .nav-tab-active { border-bottom: none; border-left-width: 6px; }
				}
				/*]]>*/
			</style>
		<?php
		//remove sub menu page from dashboard menu
		remove_submenu_page( 'index.php', 'fakturo-changelog' );
		remove_submenu_page( 'index.php', 'fakturo-getting-started' );
		remove_submenu_page('index.php', 'fakturo-privacy');
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
		<div class="about__header-title">
			<p>
				<?php _e('Fakturo', 'fakturo'); ?>
				<span><?php echo $display_version; ?></span>
			</p>
		</div>
		<div class="about__header-text">
			<p>
				<?php
				_e('Thank you for updating to the latest version!', 'fakturo');
				printf(	'<br />'.__('Fakturo %s is ready to make your money management faster, safer, and better!', 'fakturo'),
					$display_version
				);
				?>
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
		<nav class="about__header-navigation nav-tab-wrapper wp-clearfix" aria-label="<?php esc_attr_e('Secondary menu'); ?>">
			<a class="nav-tab <?php echo $selected == 'fakturo-about' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'fakturo-about' ), 'index.php' ) ) ); ?>">
				<?php _e( "What's New", 'fakturo' ); ?>
			</a>
			<a class="nav-tab <?php echo $selected == 'fakturo-getting-started' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'fakturo-getting-started' ), 'index.php' ) ) ); ?>">
				<?php _e( 'Getting Started', 'fakturo' ); ?>
			</a>
			<a class="nav-tab <?php echo $selected == 'fakturo-changelog' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'fakturo-changelog' ), 'index.php' ) ) ); ?>">
				<?php _e( 'Changelog', 'fakturo' ); ?>
			</a>
			<a class="nav-tab <?php echo $selected == 'fakturo-privacy' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url(admin_url(add_query_arg(array('page' => 'fakturo-privacy'), 'index.php'))); ?>">
				<?php _e('Privacy', 'fakturo'); ?>
			</a>
		</nav>
		<?php
	}
	
	/**
	 * Render About Screen
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function about_screen() {
		?>
		<div class="wrap about__container">

			<div class="about__header">

			<?php
				// load welcome message and content tabs
				$this->welcome_message();
				$this->tabs();
			?>

			</div>

			<hr/>

			<div class="about__section about__section_height-2">
				<div class="column is-vertically-aligned-center">
					<p><?php _e('For anyone reading this and wondering what on earth "Fakturo" is, or how on earth to start with so many options:', 'fakturo'); ?></p>
					<h3><strong><?php _e("Relax, don't worry. Here we tell you everything!", 'fakturo'); ?></strong></h3>
					<p><?php _e('Fakturo is a complete and free management system for small and medium-sized businesses, released as a WordPress plugin with many features for daily sales and invoicing management. It is fully configurable, customizable and extendable through WordPress filters and also through add-ons or custom support.', 'fakturo'); ?></p>
				</div>
			</div>

			<hr/>

			<div class="about__section has-3-columns mb-0">
				<div class="column">
					<h3><?php _e('Essential features', 'fakturo'); ?></h3>
					<p><?php _e("Fakturo includes a wide range of essential features to manage sales, products, stock, clients, checks, accounts, providers, receipts, payments and more. With real-time access to your information it streamlines the sales process and lets you offer a more efficient service.", 'fakturo'); ?></p>
				</div>
				<div class="column">
					<h3><?php _e('Report generation', 'fakturo'); ?></h3>
					<p><?php _e('Generate statistical reports by client or by income, filtered by date ranges, groups of invoices or specific clients, to easily get practical summaries or detailed reports.', 'fakturo'); ?></p>
				</div>
				<div class="column">
					<h3><?php _e('Data storage', 'fakturo'); ?></h3>
					<p><?php _e('Data is stored in the WordPress database in an optimized way to keep tables small and records fast to reach, and it stays compatible with traditional WordPress backups and with any server.', 'fakturo'); ?></p>
				</div>
			</div>

			<hr/>

			<div class="about__section about__section_height has-2-columns">
				<div class="column wpe-flex is-edge-to-edge has-accent-background-color">
					<div class="about__image is-vertically-aligned-center mx-auto">
						<img src="<?php echo FAKTURO_PLUGIN_URL . 'assets/images/icon-256x256.png'; ?>" alt="Fakturo" style="display:block; max-height: 500px; margin: auto;" />
					</div>
				</div>
				<div class="column is-vertically-aligned-center">
					<h2><?php _e('Why should you choose Fakturo?', 'fakturo'); ?></h2>

					<h4><span class="dashicons dashicons-admin-settings"></span> <?php _e('Multi-configuration', 'fakturo'); ?></h4>
					<p><?php _e('Many settings are available to fit the requirements of every situation and every country.', 'fakturo'); ?></p>

					<h4><span class="dashicons dashicons-money"></span> <?php _e('Virtual assistant or Wizard.', 'fakturo'); ?></h4>
					<p><?php _e('Automated initial setup through a virtual assistant, with personalized support.', 'fakturo'); ?></p>

					<h4><span class="dashicons dashicons-groups"></span> <?php _e('Multi-user.', 'fakturo'); ?></h4>
					<p><?php _e('Allows two or more users to share the same resources at the same time. Fakturo handles these user roles: Administrator, Fakturo Supervisor and Seller.', 'fakturo'); ?></p>

					<h4><span class="dashicons dashicons-translation"></span> <?php _e('Multi-language.', 'fakturo'); ?></h4>
					<p><?php _e('Fakturo supports multiple languages. It currently includes Spanish and English, plus language files to translate it.', 'fakturo'); ?></p>

					<h4><span class="dashicons dashicons-editor-code"></span> <?php _e('Ready for developers.', 'fakturo'); ?></h4>
					<p><?php _e('Built and optimized following WordPress standards, with dozens of filters and actions to extend its features.', 'fakturo'); ?></p>

					<h4><span class="dashicons dashicons-sos"></span> <?php _e('Complete contextual help.', 'fakturo'); ?></h4>
					<p><?php _e('Integrated with the WordPress contextual help. Every screen has a Help tab at the top explaining that step of the process.', 'fakturo'); ?></p>
				</div>
			</div>

			<hr/>

			<div class="about__section has-3-columns mb-0">
				<div class="column">
					<h3><a href="https://etruel.com/my-account/support/" target="_blank"><?php _e('Support ticket system for free', 'fakturo'); ?></a></h3>
					<p><?php _e('Ask for any problem you may have and you\'ll get support for free. If it is necessay we will see into your website to solve your issue.', 'fakturo'); ?></p>
				</div>
				<div class="column">
					<h3><a href="https://etruel.com/downloads/premium-support/" target="_blank"><?php _e('Premium Support', 'fakturo'); ?></a></h3>
					<p><?php _e('Get access to in-depth setup assistance. We\'ll dig in and do our absolute best to resolve issues for you. Any support that requires code or setup your site will need this service.', 'fakturo'); ?></p>
				</div>
				<div class="column">
					<h3><a href="https://wordpress.org/support/view/plugin-reviews/fakturo?filter=5&rate=5#new-post" target="_blank"><?php _e('Rate 5 stars on Wordpress', 'fakturo'); ?></a><div class="wporg-ratings" title="5 out of 5 stars" style="color:#ffb900;"><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span></div></h3>
					<p><?php _e('We need your positive rating of 5 stars in WordPress. Your comment will be published on the bottom of the website and besides it will help making the plugin better.', 'fakturo'); ?></p>
				</div>
			</div>

		</div>
		<?php
	}
	
	/**
	 * Render Changelog Screen
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function changelog_screen() {
		?>
		<div class="wrap about__container">

			<div class="about__header">

			<?php

				// load welcome message and content tabs
				$this->welcome_message();
				$this->tabs();

			?>

			</div>


			<div class="about__section">

				<h2 class="is-section-header"><?php _e('Full Changelog', 'fakturo'); ?></h2>

				<div class="column is-vertically-aligned-center" style="padding-top: 0;">
				
				<div class="feature-section">
					<?php echo $this->parse_readme(); ?>
					</div>
				
				</div>

			</div>

			<hr />

			<div class="return-to-dashboard">

				<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'fakturo-settings' ), 'admin.php' ) ) ); ?>"><?php _e( 'Go to Fakturo Settings', 'fakturo' ); ?></a>
			
			</div>
		</div>
		<?php
	}

	/**
	 * Render Privacy Screen
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function privacy_screen() {
		?>

		<div class="wrap about__container">

			<div class="about__header">

				<?php $this->welcome_message(); ?>

				<?php $this->tabs(); ?>

			</div>

			<div class="about__section">

				<h2 class="is-section-header"><?php _e('Privacy terms', 'fakturo'); ?></h2>

				<div class="column is-vertically-aligned-center">

					<div class="feature-section">
						<?php echo $this->parse_privacy(); ?>
					</div>

				</div>
			</div>

			<hr />

			<div class="return-to-dashboard">
				<a href="<?php echo esc_url(admin_url(add_query_arg(array('page' => 'fakturo-settings'), 'admin.php'))); ?>"><?php _e('Go to Fakturo Settings', 'fakturo'); ?></a>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Getting Started Screen
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function getting_started_screen() {
		?>
		<div class="wrap about__container">

			<div class="about__header">

			<?php
				// load welcome message and content tabs
				$this->welcome_message();
				$this->tabs();
			?>
		
			</div>

			<hr/>

			<div class="about__section about__section_height has-2-columns">
				<div class="column wpe-flex is-edge-to-edge has-accent-background-color">
					<div class="about__image is-vertically-aligned-center">
						<img src="<?php echo FAKTURO_PLUGIN_URL . 'screenshot-6.png'; ?>" alt="<?php esc_attr_e('Main Dashboard', 'fakturo'); ?>"/>
					</div>
				</div>
				<div class="column is-vertically-aligned-center">
					<h3 style="margin-bottom: 0;"><?php _e('An intuitive', 'fakturo'); ?></h3>
					<h2><?php _e('Main Dashboard', 'fakturo'); ?></h2>
					<p><?php _e('Once the plugin is installed, with the help of an amazing setup wizard, you can reach the main Fakturo desktop from the WordPress menu: the intuitive Dashboard.', 'fakturo'); ?></p>
					<p><?php _e('There you will see 7 big colored icons. They are quick shortcuts to the main functions of your new Fakturo. You will also see a panel with a preview of your sales summary for today, the current month and the previous month.', 'fakturo'); ?></p>
					<p><?php _e('From these shortcuts you can easily and quickly reach the sale you want to invoice, checks, receipts and other settings.', 'fakturo'); ?></p>
					<p><?php _e('What if my sellers, or whoever I put at the cash register, change the settings I have established? Great question… Relax!', 'fakturo'); ?></p>
					<p><?php _e('Usually the settings are not available to the roles you assign as “Seller” or “Fakturo Supervisor”, so they cannot change the values and configuration you already established. That can only be done from your administrator role.', 'fakturo'); ?></p>
				</div>
			</div>

			<div class="about__section about__section_height has-2-columns">
				<div class="column is-vertically-aligned-center">
					<h2><?php _e('Invoicing', 'fakturo'); ?></h2>
					<p><?php _e('Invoicing covers all the steps related to preparing, recording, sending and collecting debit and credit invoices.', 'fakturo'); ?></p>
					<p><?php _e('This management software includes the documents that support a commercial transaction, plus invoice templates with all the essential content required by invoicing regulations.', 'fakturo'); ?></p>

					<h4><?php _e('Multiple sale points', 'fakturo'); ?></h4>
					<p class="text"><?php _e('Different numbering to issue vouchers from different branches or sale points using the same system.', 'fakturo'); ?></p>

					<h4><?php _e('Custom invoice number format', 'fakturo'); ?></h4>
					<p class="text"><?php _e("Set a custom format for the invoice number using the sale point, type and invoice number attributes, number of digits, etc.", 'fakturo'); ?></p>

					<h4><?php _e('Full invoice customization with templates', 'fakturo'); ?></h4>
					<p class="text"><?php _e("Design the layout of your company's invoices with custom templates and predefined variables to include the information you need, using a rich code editor that also includes a preview.", 'fakturo'); ?></p>

					<h4><?php _e('Printing on pre-printed sheets', 'fakturo'); ?></h4>
					<p class="text"><?php _e("Print on pre-printed or blank sheets depending on the structure of your receipts.", 'fakturo'); ?></p>

					<h4><?php _e('Email customization with templates', 'fakturo'); ?></h4>
					<p class="text"><?php _e("Define the structure of the emails used to send attached documents.", 'fakturo'); ?></p>

					<h4><?php _e('Sending PDF files', 'fakturo'); ?></h4>
					<p class="text"><?php _e("Send PDF files individually per client or in batches, choosing which clients from the list.", 'fakturo'); ?></p>
					
					<h4><?php _e('Multiple payment methods', 'fakturo'); ?></h4>
					<p class="text"><?php _e("Multiple payment methods for each client, like bank deposits, cash and even checks, with their follow-up.", 'fakturo'); ?></p>
					
					<h4><?php _e('Tax conditions', 'fakturo'); ?></h4>
					<p class="text"><?php _e("Manage taxes and the default tax condition of each client, by invoice type or by product.", 'fakturo'); ?></p>
					
					<h4><?php _e('Electronic invoices', 'fakturo'); ?></h4>
					<p class="text"><?php _e("Fakturo is built and ready to issue electronic invoices with an additional extension.", 'fakturo'); ?></p>

					<h4><?php _e('Automatic monthly invoicing', 'fakturo'); ?></h4>
					<p class="text"><?php _e("With the Subscriptions extension you can issue invoices automatically through recurring subscriptions to clients.", 'fakturo'); ?></p>
				</div>
				<div class="column wpe-flex is-edge-to-edge has-accent-background-color">
					<div class="about__image is-vertically-aligned-center">
						<img src="<?php echo FAKTURO_PLUGIN_URL . 'screenshot-1.png'; ?>" alt="Facturación" />
						<hr>
						<img src="<?php echo FAKTURO_PLUGIN_URL . 'screenshot-3.png'; ?>" alt="Facturación" />
					</div>
				</div>
			</div>

			<div class="about__section about__section_height has-2-columns">
				<div class="column wpe-flex is-edge-to-edge has-accent-background-color">
					<div class="about__image is-vertically-aligned-center">
						<img src="<?php echo FAKTURO_PLUGIN_URL . 'screenshot-2.png'; ?>" class="Clientes"/>
					</div>
				</div>
				<div class="column is-vertically-aligned-center">
					<h2><?php _e('Clients', 'fakturo'); ?></h2>
					<p><?php _e('Client management is essential to keep control not only of invoicing but also of accounting.', 'fakturo'); ?></p>
					
					<h4><?php _e('Current accounts', 'fakturo'); ?></h4>
					<p class="text"><?php _e("Summary or detailed reports of incoming and outgoing movements from credit and debit invoices and receipts, with the current balance of each client.", 'fakturo'); ?></p>
					
					<h4><?php _e('Instant photos from the webcam', 'fakturo'); ?></h4>
					<p class="text"><?php _e("Use your computer's webcam to take photos of your clients right from the system and add them to their data.", 'fakturo'); ?></p>
					
					<h4><?php _e('Client contacts', 'fakturo'); ?></h4>
					<p class="text"><?php _e("If your client is a company you can record its contact information, which is useful to reach the company's representatives.", 'fakturo'); ?></p>
					
					<h4><?php _e('Preset values loaded automatically when invoicing', 'fakturo'); ?></h4>
					<p class="text"><?php _e("When you select the client while invoicing, the information previously recorded is loaded into the invoice automatically, speeding up its creation.", 'fakturo'); ?></p>
					
					<h4><?php _e('Payment type / Bank account', 'fakturo'); ?></h4>
					<p class="text"><?php _e("Manage the default payment type and bank account of each client during registration. You can also choose a different payment type for the client while invoicing.", 'fakturo'); ?></p>
					
					<h4><?php _e('Tax condition', 'fakturo'); ?></h4>
					<p class="text"><?php _e("Manage the default tax condition of each client during registration. You can also choose a different one while invoicing.", 'fakturo'); ?></p>
					
					<h4><?php _e('Client price scale and currency', 'fakturo'); ?></h4>
					<p class="text"><?php _e("Set different price scales per product and client based on the cost plus a percentage, making it easy to change all the scales at once by product family.", 'fakturo'); ?></p>

					<h4><?php _e('Credit limit', 'fakturo'); ?></h4>
					<p class="text"><?php _e("Assign the credit limit a client needs to operate along the year and cover their credit needs at any time. If the balance goes negative, the seller is warned before invoicing.", 'fakturo'); ?></p>
				</div>
			</div>

			<div class="about__section about__section_height has-2-columns">
				<div class="column is-vertically-aligned-center">
					<h2><?php _e('Products', 'fakturo'); ?></h2>
					<p><?php _e('Product inventory controls the stock of each product in each location.', 'fakturo'); ?></p>

					<h4><?php _e('Product types, separated into categories and/or models', 'fakturo'); ?></h4>
					<p class="text"><?php _e('Sort your products into “families” grouped by product type, category and/or model. Order is necessary in any inventory: if we know where each product belongs, we find it more easily.', 'fakturo'); ?></p>

					<h4><?php _e('Automatic stock entries and exits', 'fakturo'); ?></h4>
					<p class="text"><?php _e("Manage the entry and exit of products in the warehouse. Every stock entry or exit must have a reference number, which can be a purchase invoice, a delivery note or a supporting document.", 'fakturo'); ?></p>

					<h4><?php _e('Different stocks per warehouse', 'fakturo'); ?></h4>
					<p class="text"><?php _e("The system can manage several different stocks per warehouse at the same time.", 'fakturo'); ?></p>

					<h4><?php _e('Warehouses for products', 'fakturo'); ?></h4>
					<p class="text"><?php _e("Register as many warehouses as you need to control the storage and location of your products. Creating at least one is recommended.", 'fakturo'); ?></p>

					<h4><?php _e('Instant photos from the webcam', 'fakturo'); ?></h4>
					<p class="text"><?php _e("Use your computer's webcam to take photos of your products right from the system and add them to the rest of their information.", 'fakturo'); ?></p>

					<h4><?php _e('Management of providers, taxes, packaging and product origins', 'fakturo'); ?></h4>
					<p class="text"><?php _e("Managing providers is a key factor in the company's decision making. Besides that, while registering a product you can set its taxes, packaging, units per package and origin.", 'fakturo'); ?></p>
				</div>
				<div class="column wpe-flex is-edge-to-edge has-accent-background-color">
					<div class="about__image is-vertically-aligned-center">
						<img src="<?php echo FAKTURO_PLUGIN_URL . 'screenshot-5.png'; ?>" alt="Facturación" />
					</div>
				</div>
			</div>

			<div class="about__section about__section_height has-2-columns">
				<div class="column wpe-flex is-edge-to-edge has-accent-background-color">
					<div class="about__image is-vertically-aligned-center">
						<img src="<?php echo FAKTURO_PLUGIN_URL . 'screenshot-5.png'; ?>" alt="Facturación" />
					</div>
				</div>
				<div class="column is-vertically-aligned-center">
					<h2><?php _e('Extensions', 'fakturo'); ?></h2>
					<p><?php _e('Ready to add features through extensions, with some already built like Subscriptions or Electronic Invoicing.', 'fakturo'); ?></p>

					<h3><?php _e('Subscriptions', 'fakturo'); ?></h3>
					<p><?php _e('- Automatic invoicing through recurring subscriptions to clients.', 'fakturo'); ?></p>
					<p><?php _e('- Fully configurable: set the subscription period to monthly, weekly, etc.', 'fakturo'); ?></p>
					<p><?php _e('- The invoice is generated automatically on the date set in the subscription.', 'fakturo'); ?></p>
					<p><?php _e('- It can be generated as a draft, or already finished and approved and sent by email to the client.', 'fakturo'); ?></p>

					<h3><?php _e('Electronic Invoicing', 'fakturo'); ?></h3>
					<p><?php _e('- Allows electronic invoicing for Argentina.', 'fakturo'); ?></p>
					<p><?php _e('- Issue your AFIP electronic invoice very easily.', 'fakturo'); ?></p>
					<p><?php _e('- Runs all the necessary tests as a draft and completes it electronically when you are sure.', 'fakturo'); ?></p>
					<p><?php _e('- If you choose, it emails the invoice to the client automatically.', 'fakturo'); ?></p>
				</div>
			</div>

			<hr />

			<div class="about__section has-2-columns has-subtle-background-color">
				<h2 class="is-section-header"><?php _e('Need more Help?', 'fakturo'); ?></h2>
				<div class="column">
					<h3><?php _e('Phenomenal Support', 'fakturo'); ?></h3>
					<p><?php echo __('We do our best to provide the best support we can. If you encounter a problem or have a question, simply open a ticket using our ', 'fakturo') . '<a target="_blank" href="https://etruel.com/my-account/support">' . __('support form', 'fakturo') . '</a>.'; ?></p>
				</div>
				<div class="column">
					<h3><?php _e('Need Even Better Support?', 'fakturo'); ?></h3>
					<p><?php echo __('Our ', 'fakturo') . '<a target="_blank" href="https://etruel.com/downloads/premium-support/">' . __('Premium Support', 'fakturo') . '</a>' . __('system is there for customers that need faster and/or more in-depth assistance.', 'fakturo'); ?></p>
				</div>
			</div>
			<div class="about__section has-2-columns has-subtle-background-color">
				<h2 class="is-section-header"><?php _e('Fakturo Add-ons', 'fakturo'); ?></h2>
				<div class="column">
					<h3><?php _e('Extend the plugin features', 'fakturo'); ?></h3>
					<p><?php _e('There are companion plugins that greatly extend the default features of Fakturo, like Subscriptions and AFIP.', 'fakturo'); ?></p>
				</div>
				<div class="column">
					<h3><?php _e('Visit the Extension Store', 'fakturo'); ?></h3>
					<p><?php echo '<a href="https://etruel.com/downloads" target="_blank">' . __('The etruel store ', 'fakturo') . '</a>' . __('has a list of all available extensions for Fakturo, also other Worpdress plugins, some of them for free. Including convenient category filters so you can find exactly what you are looking for.', 'fakturo'); ?></p>
				</div>
			</div>

		</div>
		<?php
	}

	/**
	 * Parse the FAKTURO readme.txt file
	 *
	 * @since 1.0
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


	/**
	 * Parse the text with *limited* markdown support.
	 *
	 * @param string $text
	 * @return string
	 */
	private function fakturo_markdown($text) {
// Make it HTML safe for starters
		$text	 = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
// headlines
		$s		 = array('===', '==', '=');
		$r		 = array('h2', 'h3', 'h4');
		for($x = 0; $x < sizeof($s); $x++)
			$text	 = preg_replace('/(.*?)' . $s[$x] . '(?!\")(.*?)' . $s[$x] . '(.*?)/', '$1<' . $r[$x] . '>$2</' . $r[$x] . '>$3', $text);

// inline
		$s		 = array('\*\*', '\'');
		$r		 = array('strong', 'code');
		for($x = 0; $x < sizeof($s); $x++)
			$text	 = preg_replace('/(.*?)' . $s[$x] . '(?!\s)(.*?)(?!\s)' . $s[$x] . '(.*?)/', '$1<' . $r[$x] . '>$2</' . $r[$x] . '>$3', $text);

// ' _italic_ '
		$text = preg_replace('/(\s)_(\S.*?\S)_(\s|$)/', ' <em>$2</em> ', $text);

// Blockquotes (they have email-styled > at the start)
		$regex = '^&gt;.*?$(^(?:&gt;).*?\n|\n)*';
		preg_match_all("~$regex~m", $text, $matches, PREG_SET_ORDER);
		foreach($matches as $set) {
			$block	 = "<blockquote>\n" . trim(preg_replace('~(^|\n)[&gt; ]+~', "\n", $set[0])) . "\n</blockquote>\n";
			$text	 = str_replace($set[0], $block, $text);
		}
// Titles
		$text = preg_replace_callback("~(^|\n)(#{1,6}) ([^\n#]+)[^\n]*~", function($match) {
			$n = strlen($match[2]);
			return "\n<h$n>" . $match[3] . "</h$n>";
		}, $text);
// ul lists	
		$s		 = array('\*', '\+', '\-');
		for($x = 0; $x < sizeof($s); $x++)
			$text	 = preg_replace('/^[' . $s[$x] . '](\s)(.*?)(\n|$)/m', '<li>$2</li>', $text);
		$text	 = preg_replace('/\n<li>(.*?)/', '<ul><li>$1', $text);
		$text	 = preg_replace('/(<\/li>)(?!<li>)/', '$1</ul>', $text);

		// ol lists
		$text	 = preg_replace('/(\d{1,2}\.)\s(.*?)(\n|$)/', '<li>$2</li>', $text);
		$text	 = preg_replace('/\n<li>(.*?)/', '<ol><li>$1', $text);
		$text	 = preg_replace('/(<\/li>)(?!(\<li\>|\<\/ul\>))/', '$1</ol>', $text);

		/* 		// ol screenshots style
		  $text = preg_replace('/(?=Screenshots)(.*?)<ol>/', '$1<ol class="readme-parser-screenshots">', $text);

		  // line breaks
		  $text	 = preg_replace('/(.*?)(\n)/', "$1<br/>\n", $text);
		  $text	 = preg_replace('/(1|2|3|4)(><br\/>)/', '$1>', $text);
		  $text	 = str_replace('</ul><br/>', '</ul>', $text);
		  $text	 = str_replace('<br/><br/>', '<br/>', $text);

		  // urls
		  $text	 = str_replace('http://www.', 'www.', $text);
		  $text	 = str_replace('www.', 'http://www.', $text);
		  $text	 = preg_replace('#(^|[^\"=]{1})(http://|ftp://|mailto:|https://)([^\s<>]+)([\s\n<>]|$)#', '$1<a target=\"_blank\" href="$2$3">$3</a>$4', $text);
		 */
		// Links and Images
		$regex = '(!)*\[([^\]]+)\]\(([^\)]+?)(?: &quot;([\w\s]+)&quot;)*\)';
		preg_match_all("~$regex~", $text, $matches, PREG_SET_ORDER);
		foreach($matches as $set) {
			$title = isset($set[4]) ? " title=\"{$set[4]}\"" : '';
			if($set[1]) {
				$text = str_replace($set[0], "<img src=\"{$set[3]}\"$title alt=\"{$set[2]}\"/>", $text);
			}else {
				$text = str_replace($set[0], "<a target=\"_blank\" href=\"{$set[3]}\"$title>{$set[2]}</a>", $text);
			}
		}

		// Paragraphs
		//		$text	 = preg_replace('~\n([^><\t]+)\n~', "\n\n<p>$1</p>\n\n", $text);
		// Paragraphs (what about fixing the above?)
		//		$text	 = str_replace(array("<p>\n", "\n</p>"), array('<p>', '</p>'), $text);
		// Lines that end in two spaces require a BR
		//		$text	 = str_replace("  \n", "<br>\n", $text);
		// Reduce crazy newlines
		//		$text	= preg_replace("~\n\n\n+~", "\n\n", $text);

		return $text;
	}

	/**
	 * Column Privacy with the privacy also in readme.txt file
	 *
	 * @since 2.0.3
	 * @return string $readme HTML formatted readme file
	 */

	public function parse_privacy() {
		$file = file_exists(FAKTURO_PLUGIN_DIR . 'readme.txt') ? FAKTURO_PLUGIN_URL . 'readme.txt' : null;

		if(!$file) {
			$readme = '<p>' . __('No valid changelog was found.', 'fakturo') . '</p>';
		}else {
			$readme = file_get_contents( $file );
			$readme	 = explode('Privacy terms =', $readme);
			$readme	 = end($readme);
			$readme	 = explode('== Installation ==', $readme);
			$readme	 = $readme[0];
			$readme	 = nl2br(html_entity_decode($readme));

			$readme	 = preg_replace('/`(.*?)`/', '<code>\\1</code>', $readme);
			$readme	 = preg_replace('/[\040]\*\*(.*?)\*\*/', ' <strong>\\1</strong>', $readme);
			$readme	 = preg_replace('/[\040]\*(.*?)\*/', ' <em>\\1</em>', $readme);
			$readme	 = preg_replace('/= (.*?) =/', '<h4>\\1</h4>', $readme);
			$readme	 = preg_replace('/\[(.*?)\]\((.*?)\)/', '<a href="\\2">\\1</a>', $readme);
		}

		return $readme;
	}

}
$fktr_welcome = new fktr_welcome();

?>
