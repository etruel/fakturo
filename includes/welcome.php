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
		remove_submenu_page( 'index.php', 'fakturo-changelog' );
		remove_submenu_page( 'index.php', 'fakturo-getting-started' );
		remove_submenu_page('index.php', 'fakturo-privacy');

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
				.wpe-flex{ display: flex; }
				.about__section { background: #fff; font-size: 1.2em; margin: 0;}
				.about__section.mb-0{ margin-bottom: 0; }
				.about__section h2, .about__section h3, .about__section h4, .about__section h5 { margin: 1em 0; }
				.about__section a{ color: #222; }
				.about__section a:hover{ color: #f7b63e; }
				.about__header { background-image: <?php echo 'url('.FAKTURO_PLUGIN_URL . 'assets/images/about-header.png)'; ?>; background-size: 80%; background-position: bottom right; padding-top: 3rem; background-color: #f7b63e; }
				.about__header-title{ margin: 5rem 2rem 0;padding: 1em 0; }
				.about__header-title p { margin: 0; padding: 80px 0 0; font-size: 4em; color: #222; line-height: 1; font-weight: 900; text-transform: uppercase; }
				.about__header-title p span { color: #333; }
				.about__header-text { max-width: 25em; margin: 0 2rem 3rem; padding: 0; font-size: 1.5em; line-height: 1.4;}
				.about__header-text p { color: #222; margin-top: 0; }
				.about__header-navigation { display: flex; justify-content: flex-start; background: #fff; color: #222; border-color: #222; padding-top: 0;}
				.about__header-navigation .nav-tab{color: #222 ; margin: 0; padding: 24px 32px; float: none; font-size: 1.4em; line-height: 1; border-style: solid; background: 0 0; border-width: 0 0 3px; border-color: transparent;}
				.about__header-navigation .nav-tab-active { margin-bottom: -3px; }
				.about__header-navigation .nav-tab-active:active, .about__header-navigation .nav-tab-active:hover, .about__header-navigation .nav-tab-active { color: #f7b63e; border-color: #f7b63e;}
				.about__header-navigation .nav-tab:active, .about__header-navigation .nav-tab:hover{ background: #f5f5f5; color: #f7b63e; }
				.about__container .has-accent-background-color { background: #f7b63e; }
				.about__container .has-subtle-background-color { background: #f9f9f9; }
				.about__container .text{ font-size: 14px; }
				.about__header-title .fakturo-badge { align-self: flex-end; margin-bottom: 10px; max-height: 80px; width: auto; }
				.about__section.about__section_height { min-height: 560px; }
				.about__section.about__section_height-2 { min-height: 400px; }
				.about__section.is-feature { font-size: 1.4em; }
				.about__container h1, .about__container h2, .about__container h3.is-larger-heading{
					    margin-top: 0; margin-bottom: .5em; font-size: 1.75em; line-height: 1.2; font-weight: 600; }
				.about__container h1.is-smaller-heading, .about__container h2.is-smaller-heading, .about__container h3 { margin-top: 0; font-size: 1.25em; font-weight: 700; }
				.about__container .about__image { padding: 0 32px; }
				.about__container .about__image.mx-auto { margin-left: auto; margin-right: auto; }
				.about__section .span-text { font-size: .9em; }
				.feature-section a, .about__section p a { font-weight: 600; }
				.addon_block { display: flex; margin-bottom: 1em;}
				.addon_block .addon_img img { display:block; max-width: 120px; height: auto; margin-right: 10px; }
				.addon_block .addon_text { text-align: right;}
				.addon_block .addon_text p { margin: 0; font-size: 13px; }
				.about__section.has-2-columns, .about__section.has-3-columns, .about__section.has-4-columns, .about__section.has-overlap-style { display: grid; }
				.about__section.has-2-columns{ -ms-grid-columns: 1fr 1fr; grid-template-columns: 1fr 1fr; }
				.about__section.has-2-columns .column:nth-of-type(2n+1) { -ms-grid-column: 1; grid-column-start: 1; }
				.about__section.has-2-columns .column:nth-of-type(2n) { -ms-grid-column: 2; grid-column-start: 2; }
				.about__section .column.is-edge-to-edge{ color: #fff; padding: 0; }
				.about__section + .about__section .column{ padding-top: 32px; }
				.about__container .is-vertically-aligned-center{ align-self: center; }
				@media all and ( max-width: 1035px ) {
					.about__header { background-size: 95%; }
				}
				@media all and ( max-width: 782px ) {
					.about__header{	background-image: none; }
					.about__header-title{ margin-top: 0; padding-top: 0;}
					.about__header-title p{ font-size: 3em; }
				}
				@media all and ( max-width: 782px ) and (min-width: 481px) {
					.about__header-navigation .nav-tab{ padding: 24px 16px; }
				}
				@media all and ( max-width: 600px ) and (min-width: 481px) {
					.about__header-navigation .nav-tab{ font-size: 1.1em; }
				}
				@media all and ( max-width: 600px ) {
					.about__header-title p{ font-size: 2.25em; }
					.about__section.has-2-columns, .about__section.has-2-columns.is-wider-left, .about__section.has-2-columns.is-wider-right, .about__section.has-3-columns{ display: block; padding-bottom: 16px; }
					.about__section + .about__section .column{ padding-top: 16px; }
					.about__section.has-2-columns .column:nth-of-type(n){ padding-top: 16px; padding-bottom: 16px; }
					.about__header-navigation{ flex-direction: column; }
					.about__header-navigation .nav-tab { float: none; display: block; margin-bottom: 0; padding: 16px 16px; border-left-width: 6px; border-bottom: none; }
					.about__header-navigation .nav-tab-active { border-bottom: none; border-left-width: 6px; }
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
		<div class="about__header-title">
			<p>
				<?php _e('Fakturo'); ?>
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

			<?php $this->subscription_form(); ?>

			<hr/>

			<div class="about__section has-3-columns mb-0">
				<div class="column">
					<h3><?php _e('Funciones esenciales', 'fakturo'); ?></h3>
					<p><?php _e("Fakturo incluye una gran variedad de funciones esenciales para la gestión de ventas, de productos, stocks, clientes, cheques, cuentas, proveedores, recibos, pagos, etc. ofreciendo la posibilidad de acceso a la información en tiempo real, optimiza el proceso de ventas y permite brindar un servicio más eficiente.", 'fakturo'); ?></p>
				</div>
				<div class="column">
					<h3><?php _e('Emisión de reportes', 'fakturo'); ?></h3>
					<p><?php _e('Permite la emisión de reportes estadísticos, por clientes o por ingresos, controlados y generados a partir de diferentes tipos filtros como ser rangos de fecha, por grupos de facturas o clientes específicos lo cual permite obtener fácilmente resúmenes prácticos o informas detallados.', 'fakturo'); ?></p>
				</div>
				<div class="column">
					<h3><?php _e('Almacenamiento de datos', 'fakturo'); ?></h3>
					<p><?php _e('El almacenamiento de información está preparado para usar la base de datos de WordPress en manera optimizada para asegurar el menor tamaño y más rápido acceso a los registros de las tablas. Asegurando así la compatibilidad con los backups tradicionales para WordPress o para cualquier servidor.', 'fakturo'); ?></p>
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
					<h2><?php _e('¿Por qué deberías elegir Fakturo?', 'fakturo'); ?></h2>

					<h4><span class="dashicons dashicons-admin-settings"></span> <?php _e('Multiconfiguración', 'fakturo'); ?></h4>
					<p><?php _e('Múltiples configuraciones disponibles para adaptarse a los requerimientos de cada situación y cada país.', 'fakturo'); ?></p>

					<h4><span class="dashicons dashicons-money"></span> <?php _e('Asistente virtual o Wizard.', 'fakturo'); ?></h4>
					<p><?php _e('Configuración inicial automatizada por medio de un asistente virtual, y con soporte personalizado.', 'fakturo'); ?></p>

					<h4><span class="dashicons dashicons-groups"></span> <?php _e('Multiusuario.', 'fakturo'); ?></h4>
					<p><?php _e('Permiten que dos o más usuarios compartan los mismos recursos simultáneamente, Fakturo maneja los siguientes roles de usuarios: Administrador, Fakturo Supervisor, Vendedor.', 'fakturo'); ?></p>

					<h4><span class="dashicons dashicons-translation"></span> <?php _e('Multilenguaje.', 'fakturo'); ?></h4>
					<p><?php _e('Fakturo soporta múltiples idiomas, actualmente incluye los idiomas Español e Inglés y también archivos de idioma para traducir.', 'fakturo'); ?></p>

					<h4><span class="dashicons dashicons-editor-code"></span> <?php _e('Preparado para programadores.', 'fakturo'); ?></h4>
					<p><?php _e('Desarrollado y optimizado para los estándares de WordPress con docenas de Filtros y Acciones para que puedan extender sus funcionalidades.', 'fakturo'); ?></p>

					<h4><span class="dashicons dashicons-sos"></span> <?php _e('Ayuda contextual completa.', 'fakturo'); ?></h4>
					<p><?php _e('Integrado para usar la ayuda contextual de WordPress. En cada pantalla del sistema se encuentra la pestaña superior de Ayuda explicando esa etapa del proceso.', 'fakturo'); ?></p>
				</div>
			</div>

			<hr/>

			<div class="about__section has-3-columns mb-0">
				<h2 class="is-section-header"><?php _e('Even More Developer Happiness', 'fakturo'); ?></h2>
				<div class="column">
					<h3><?php _e('JavaScript hooks', 'fakturo'); ?></h3>
					<p><?php _e("We've implemented the JavaScript hooks like WordPress actions and filters! You can make functions to enqueue the scripts and hooks to already added filters in the code.", 'fakturo'); ?></p>
				</div>
				<div class="column">
					<h3><a href="https://etruel.com/my-account/support/" target="_blank"><?php _e('Support ticket system for free', 'fakturo'); ?></a></h3>
					<p><?php _e('Ask for any problem you may have and you\'ll get support for free. If it is necessay we will see into your website to solve your issue.', 'fakturo'); ?></p>
				</div>
				<div class="column">
					<h3><a href="https://etruel.com/downloads/premium-support/" target="_blank"><?php _e('Premium Support', 'fakturo'); ?></a></h3>
					<p><?php _e('Get access to in-depth setup assistance. We\'ll dig in and do our absolute best to resolve issues for you. Any support that requires code or setup your site will need this service.', 'fakturo'); ?></p>
				</div>
			</div>
			<div class="about__section has-3-columns mb-0">
				<div class="column">
					<h3><?php _e('Nags updates individually for extensions', 'fakturo'); ?><span class="plugin-count" style="display: inline-block;background-color: #d54e21;color: #fff;font-size: 9px;line-height: 17px;font-weight: 600;margin: 1px 0 0 2px;vertical-align: top;-webkit-border-radius: 10px;border-radius: 10px;z-index: 26;padding: 0 6px;">1</span></h3>
					<p><?php _e('A more clear nag update was added for the addons in the Fakturo Extensions and Addons menu items.', 'fakturo'); ?></p>
				</div>
				<div class="column">
					<h3><?php _e('Hidden Options in Settings -> Writing', 'fakturo'); ?></h3>
					<p><?php _e("If you have any problem with Fakturo item menu, Settings page or lost some plugin, we've put there a Fakturo Section, to try to avoid weird behaviors made by some thirds plugins.", 'fakturo'); ?></p>
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
				<a href="<?php echo esc_url(admin_url(add_query_arg(array('post_type' => 'fakturo', 'page' => 'fakturo_settings'), 'edit.php'))); ?>"><?php _e('Go to WPeMatico Settings', 'fakturo'); ?></a>
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

			<div class="about__section about__section_height has-2-columns">
				<div class="column wpe-flex is-edge-to-edge has-accent-background-color">
					<div class="about__image is-vertically-aligned-center">
						<img src="<?php echo FAKTURO_PLUGIN_URL . 'screenshot-6.png'; ?>" class="Un intuitivo Dashboard Principal"/>
					</div>
				</div>
				<div class="column is-vertically-aligned-center">
					<h3 style="margin-bottom: 0;"><?php _e('Un intuitivo', 'fakturo'); ?></h3>
					<h2><?php _e('Dashboard Principal', 'fakturo'); ?></h2>
					<p><?php _e('Una vez instalado el plugin con la ayuda de un increíble el asistente de instalación (Wizard) Podras acceder desde la pestaña de Wordpress al Escritorio principal de Fakturo. El Intuitivo Dashboard.', 'fakturo'); ?></p>
					<p><?php _e('En el mismo podrás visualizar 7 grandes iconos de diferentes colores. Serán los accesos directos y rápidos hacia las principales funciones de tu nuevo Fakturo. Además también podrás ver un panel con una vista previa de tu resumen de Ventas del día, del mes actual y del mes anterior.', 'fakturo'); ?></p>
					<p><?php _e('Desde estos vistosos accesos directos podras llegar fácilmente y muy rápido a gestionar la Venta que quieres facturar, cheques, recibos  y demás opciones de configuración.', 'fakturo'); ?></p>
					<p><?php _e('¿Y si mis vendedores o a quien pongo en la caja a manejar el sistema alteran los valores de configuración que deje establecidos? Muy buena pregunta… Tranquil@!', 'fakturo'); ?></p>
					<p><?php _e('Generalmente para los roles que asignes como “Vendedor” o “Supervisor Fakturo” no estarán disponibles estas opciones de configuración para que no puedan alterar los valores y configuraciones del sistema que ya tú has establecido, algo que solo podrá hacerse desde tu rol de administrador.', 'fakturo'); ?></p>
				</div>
			</div>

			<div class="about__section about__section_height has-2-columns">
				<div class="column is-vertically-aligned-center">
					<h2><?php _e('Facturación', 'fakturo'); ?></h2>
					<p><?php _e('La facturación engloba todos los pasos relacionados con la elaboración, registro, envío y cobro de las facturas de débito y crédito.', 'fakturo'); ?></p>
					<p><?php _e('Este software de gestión, incorpora documentos acreditativos de una transacción comercial además de plantillas de factura con todos los contenidos esenciales que dicta la normativa de facturación.', 'fakturo'); ?></p>

					<h4><?php _e('Múltiples puntos de venta', 'fakturo'); ?></h4>
					<p class="text"><?php _e('Numeraciones diferentes para emitir comprobantes desde distintas sucursales o distintos puntos de venta usando el mismo sistema.', 'fakturo'); ?></p>

					<h4><?php _e('Formato personalizado del Nro de la factura', 'fakturo'); ?></h4>
					<p class="text"><?php _e("Establece un formato personalizado para el Nro de la factura con los atributos de punto de venta, tipo y número de factura, cantidad de dígitos, etc.", 'fakturo'); ?></p>

					<h4><?php _e('Personalización total de la factura por plantillas', 'fakturo'); ?></h4>
					<p class="text"><?php _e("Diseña la estructura de las facturas de tu empresa a traves de plantillas personalizadas y variables pre-definidas que puedes utilizar para incluir la información que necesites por medio de un editor de código enriquecido que además incluye un pre visualizador.", 'fakturo'); ?></p>

					<h4><?php _e('Impresiones sobre hoja pre-impresas', 'fakturo'); ?></h4>
					<p class="text"><?php _e("Permite impresiones sobre hoja pre-impresas o en blanco dependiendo de la estructura de los recibos.", 'fakturo'); ?></p>

					<h4><?php _e('Personalización de emails con plantillas', 'fakturo'); ?></h4>
					<p class="text"><?php _e("Define la estructura de los emails a través de plantillas para envío de documentos adjuntos.", 'fakturo'); ?></p>

					<h4><?php _e('Envio de archivos PDF', 'fakturo'); ?></h4>
					<p class="text"><?php _e("Permite enviar archivos PDF de forma individual por cliente o por lotes, seleccionando a cuales clientes del listado.", 'fakturo'); ?></p>
					
					<h4><?php _e('Múltiples medios de pago', 'fakturo'); ?></h4>
					<p class="text"><?php _e("Multiples medios de pago por cada cliente, como depósitos bancarios, efectivo, e incluso cheques y además del seguimiento de los mismos.", 'fakturo'); ?></p>
					
					<h4><?php _e('Condiciones de impuestos', 'fakturo'); ?></h4>
					<p class="text"><?php _e("Gestiona los impuestos y la condición de impuestos predeterminada por cada cliente, por tipos de factura o por producto.", 'fakturo'); ?></p>
					
					<h4><?php _e('Facturas electrónicas', 'fakturo'); ?></h4>
					<p class="text"><?php _e("Fakturo está desarrollado y preparado para emitir facturas electrónicas adquiriendo una extensión adicional.", 'fakturo'); ?></p>

					<h4><?php _e('Facturación automática mensual', 'fakturo'); ?></h4>
					<p class="text"><?php _e("A través de la extensión Suscripciones puedes emitir facturas de forma automática mediante suscripciones periódicas a clientes", 'fakturo'); ?></p>
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
					<h2><?php _e('Clientes', 'fakturo'); ?></h2>
					<p><?php _e('La gestión de los clientes es imprescindible para llevar un control no sólo de la facturación sino también de la contabilidad.', 'fakturo'); ?></p>
					
					<h4><?php _e('Cuentas corrientes', 'fakturo'); ?></h4>
					<p class="text"><?php _e("Reportes, resumen de cuentas o detallados de entradas y salidas por facturación de créditos, débitos y recibos con los saldos actuales de cada cliente.", 'fakturo'); ?></p>
					
					<h4><?php _e('Fotografías al instante desde la webcam', 'fakturo'); ?></h4>
					<p class="text"><?php _e("Utiliza la webcam de tu ordenador para tomar fotografías de los clientes directamente desde el sistema para agregarla junto a sus datos.", 'fakturo'); ?></p>
					
					<h4><?php _e('Contactos del cliente', 'fakturo'); ?></h4>
					<p class="text"><?php _e("En casos de que tu cliente sea una empresa o compañía puedes registrar información de contacto de dicho cliente, esta información será útil para contactar a los representantes de la empresa cliente.", 'fakturo'); ?></p>
					
					<h4><?php _e('Pre configuraciones para carga automática en la facturación', 'fakturo'); ?></h4>
					<p class="text"><?php _e("Durante el proceso de facturación al seleccionar el cliente su información previamente registrada se cargará de forma automática en la factura, agilizando la creación de la misma.", 'fakturo'); ?></p>
					
					<h4><?php _e('Tipo de pago/Cuenta bancaria', 'fakturo'); ?></h4>
					<p class="text"><?php _e("Gestiona el tipo de pago predeterminado y la cuenta bancaria por cada cliente durante el proceso de registro, además también puedes cambiar el tipo de pago del cliente por uno diferente en el proceso de facturación.", 'fakturo'); ?></p>
					
					<h4><?php _e('Condición de impuestos', 'fakturo'); ?></h4>
					<p class="text"><?php _e("Gestiona la condición de impuestos predeterminada por cada cliente durante el proceso de registro, además también puedes cambiarlo por uno diferente en el proceso de facturación.", 'fakturo'); ?></p>
					
					<h4><?php _e('Escala de precios y moneda del cliente', 'fakturo'); ?></h4>
					<p class="text"><?php _e("Establece las escalas de precios diferentes por producto y clientes basados en el costo más un porcentaje, facilitando la modificación masiva de todas las escalas por familia de productos.", 'fakturo'); ?></p>

					<h4><?php _e('Límite de crédito', 'fakturo'); ?></h4>
					<p class="text"><?php _e("Asigna el límite de crédito que necesita algún cliente para realizar operaciones comerciales a lo largo del año y cubrir sus necesidades crediticias en todo momento. En caso de sumar saldos negativos se avisará al vendedor antes de facturar.", 'fakturo'); ?></p>
				</div>
			</div>

			<div class="about__section about__section_height has-2-columns">
				<div class="column is-vertically-aligned-center">
					<h2><?php _e('Productos', 'fakturo'); ?></h2>
					<p><?php _e('El inventario de productos es la función que se ocupa de llevar el control de existencias de cada producto en cada ubicación dada.', 'fakturo'); ?></p>

					<h4><?php _e('Tipos de productos, separados en categorías y/o modelos', 'fakturo'); ?></h4>
					<p class="text"><?php _e('Permite ordenar tus productos en conjuntos de “familia” para tenerlos agrupados a través de tipos de producto, categorías y/o modelos. El orden es necesario en cualquier inventario. Si conocemos donde pertenece cada producto, lo encontraremos con mayor facilidad.', 'fakturo'); ?></p>

					<h4><?php _e('Entradas y salidas automáticas de stock', 'fakturo'); ?></h4>
					<p class="text"><?php _e("Gestiona la entrada y salida de los productos en el almacén, cualquier entrada a inventario o salida del mismo deberá tener un número de remisión que puede ser una factura de compra, un número de remisión o un documento soporte.", 'fakturo'); ?></p>

					<h4><?php _e('Stocks diferentes por depósito', 'fakturo'); ?></h4>
					<p class="text"><?php _e("El sistema permite gestionar varios stocks diferentes por depósitos al mismo tiempo.", 'fakturo'); ?></p>

					<h4><?php _e('Depósitos para productos', 'fakturo'); ?></h4>
					<p class="text"><?php _e("Registra la cantidad de depósitos que necesites, para llevar un control del almacenamiento y la ubicación de tus productos, se recomienda crear al menos uno.", 'fakturo'); ?></p>

					<h4><?php _e('Fotografías al instante desde la webcam', 'fakturo'); ?></h4>
					<p class="text"><?php _e("Utiliza la webcam de tu ordenador para tomar fotografías de los productos directamente desde el sistema para agregarla junto al resto de su información.", 'fakturo'); ?></p>

					<h4><?php _e('Gestión de proveedores, impuestos, embalajes y orígenes de productos', 'fakturo'); ?></h4>
					<p class="text"><?php _e("Gestionar los proveedores es un factor clave en los procesos de toma de decisiones de la empresa, además de esto, durante el proceso de registro puedes establecer los impuestos, el embalaje, las unidades por paquetes y el origen de los productos.", 'fakturo'); ?></p>
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
					<h2><?php _e('Extensiones', 'fakturo'); ?></h2>
					<p><?php _e('Preparado para agregarle funciones mediante extensiones, con algunas ya creadas como Suscripciones o Facturación Electrónica.', 'fakturo'); ?></p>

					<h3><?php _e('Suscripciones', 'fakturo'); ?></h3>
					<p><?php _e('- Permite la facturación automática mediante suscripciones periódicas a clientes.', 'fakturo'); ?></p>
					<p><?php _e('- Totalmente configurable se establece el periodo de la suscripción a mensual, semanal, etc.', 'fakturo'); ?></p>
					<p><?php _e('- Según la fecha determinada en la suscripción, se genera la factura automáticamente.', 'fakturo'); ?></p>
					<p><?php _e('- Se puede generar en modo borrador o ya finalizada y aprobada con envío por email al cliente.', 'fakturo'); ?></p>

					<h3><?php _e('Facturación Electrónica', 'fakturo'); ?></h3>
					<p><?php _e('- Permite la facturación electrónica de Argentina.', 'fakturo'); ?></p>
					<p><?php _e('- Emití tu factura electrónica de AFIP muy fácilmente.', 'fakturo'); ?></p>
					<p><?php _e('- Hace todas las pruebas necesarias en borrador y completa electrónicamente cuando estás seguro.', 'fakturo'); ?></p>
					<p><?php _e('- A tu elección envía automáticamente por email al cliente.', 'fakturo'); ?></p>
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
				<h2 class="is-section-header"><?php _e('Stay Up to Date', 'fakturo'); ?></h2>
				<div class="column">
					<h3><?php _e('Get Notified of Extension Releases', 'fakturo'); ?></h3>
					<p><?php echo __('New extensions that make WPeMatico even more powerful are released nearly every single week. Subscribe to the newsletter to stay up to date with our latest releases. ', 'fakturo') . '<a href="http://eepurl.com/bX2ANz" target="_blank">' . __('Sign up now', 'fakturo') . '</a>' . __(' to ensure you do not miss a release!', 'fakturo'); ?></p>
				</div>
				<div class="column">
					<h3><?php _e('Get Alerted About New Tutorials', 'fakturo'); ?></h3>
					<p><?php echo '<a href="http://eepurl.com/bX2ANz" target="_blank">' . __('Sign up now', 'fakturo') . '</a>' . __(' to hear about the latest tutorial releases that explain how to take WPeMatico further.', 'fakturo'); ?></p>
				</div>
			</div>
			<div class="about__section has-2-columns has-subtle-background-color">
				<h2 class="is-section-header"><?php _e('Fakturo Add-ons', 'fakturo'); ?></h2>
				<div class="column">
					<h3><?php _e('Extend the plugin features', 'fakturo'); ?></h3>
					<p><?php _e('Existen plugins complementarios que amplían en gran medida la funcionalidad por defecto de Fakturo. Como Suscripciones y AFIP.', 'fakturo'); ?></p>
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
	 * Render Subscription Screen
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function subscription_form() {
		?>
		<?php
		$current_user	 = wp_get_current_user();
		?>
		<style type="text/css">
			.subscription {
			}
			.form-group label{
				display: block;
				margin-bottom: .5em;
				font-size: 1rem;
				font-weight: 600;
				color: #fff;
			}
			.two-form-group{
				display: flex;
				flex-wrap: wrap;
				align-items: flex-end;
				margin-left: -7.5px;
				margin-right: -7.5px;
			}
			.two-form-group .form-group{
				-ms-flex: 0 0 50%;
				flex: 0 0 50%;
				max-width: 50%;
				padding-left: 7.5px;
				padding-right: 7.5px;
				box-sizing: border-box;
				margin-bottom: 1em;
			}
			.subscription #wpsubscription_form .form-control{
				padding: 7.5px 15px;
				box-shadow: none!important;
				display: block;
				width: 100%;
				border-radius: 0;
				border: 2px solid #d3741c;
			}
			.wpbutton-submit-subscription{
				margin-top: 32px;
				text-align: right;
			}
			.wpbutton-submit-subscription .button-primary{
				padding: 10px 20px;
				border-radius: 0;
				font-size: 15px;
				background: #222;
				border-color: #222;
			}
			.wpbutton-submit-subscription .button-primary:hover{
				background: #111;
				border-color: #111;
			}
		</style>
		<?php
		$suscripted_user = get_option('fakturo_subscription_email_' . md5($current_user->ID), false);
		$classes		 = "";
		if($suscripted_user === false) {
			$classes = "about__section_height-2 has-2-columns";
		}
		?>
		<div class="about__section <?php echo $classes; ?> subscription">
			<div class="column is-vertically-aligned-center">
				<h2><?php _e('¡Últimas novedades en esta versión!', 'fakturo'); ?></h2>
				<p><?php _e('Luego de años de estar en versión Beta, Fakturo se lanza oficialmente en su primera versión 1.0. Este increíble plugin para WordPress seguirá mejorando e innovando con cada actualización. Una vez más incluimos nuevas características con el fin de mejorar la experiencia del usuario en el sistema de gestión de ventas de su empresa y cubrir todas sus necesidades.', 'fakturo'); ?></p>
				<p><?php _e('En breve estaremos lanzando su propio sitio web <a href="https://www.fakturo.org/">www.fakturo.org</a> desde el cual se podrá acceder a sus actualizaciones, diferentes extensiones, soporte técnico y algunos servicios Premium muy innovadores de los que pronto sabrán más 😉.','fakturo')?></p>
				<p><?php _e('Para quien esté leyendo esto y se esté preguntando ¿Qué rayos es "Fakturo"?, o ¿Cómo rayos comienzo entre tantas opciones?', 'fakturo'); ?></p>
				<h3><strong><?php _e('Tranquilo amig@, no te vayas a electrocutar... ¡Aquí te lo contamos todo!', 'fakturo'); ?></strong></h3>
				<p><?php _e('Fakturo es un sistema completo de gestión de Medianas y Pequeñas empresas gratuito, lanzado como plugin de WordPress con múltiples funciones para la gestión diaria de ventas y facturación. Es totalmente configurable, personalizable y actualizable a través de filtros de WordPress y también mediante AddOns o soporte personalizado.', 'fakturo'); ?></p>
			</div>
			<?php if($suscripted_user === false) { ?>
				<div class="column wpe-flex is-edge-to-edge has-accent-background-color">
					<div class="about__image is-vertically-aligned-center">
						<p></p>
						<h2><strong><?php _e('Stay Informed!', 'fakturo'); ?></strong></h2>
						<h3 class="wpsubscription_info"><?php _e('Subscribe to our Newsletter and be the first to receive our news.', 'fakturo'); ?> 
							<?php _e('We send around 4 or 5 emails per year. Really.', 'fakturo'); ?></h3>
						<form action="<?php echo admin_url('admin-post.php'); ?>" id="wpsubscription_form" method="post" class="wpcf7-form">
							<input type="hidden" name="action" value="save_subscription_fakturo"/>
							<?php wp_nonce_field('save_subscription_fakturo'); ?>
							<div class="two-form-group">
								<div class="form-group">
									<label><?php _e("Name", "fakturo"); ?></label>
									<input type="text" id="" name="fakturo_subscription[fname]" value="<?php echo $current_user->user_firstname; ?>" size="40" class="form-control" placeholder="<?php _e("First Name", "fakturo"); ?>">
								</div>
								<div class="form-group">
									<input type="text" id="" name="fakturo_subscription[lname]" value="<?php echo $current_user->user_lastname; ?>" size="40" class="form-control" placeholder="<?php _e("Last Name", "fakturo"); ?>">
								</div>
							</div>

							<div class="form-group">
								<label><?php _e("Email", "fakturo"); ?> <span>(*)</span></label>
								<input type="text" id="" name="fakturo_subscription[email]" value="<?php echo $current_user->user_email; ?>" size="40" class="form-control" placeholder="<?php _e("Email", "fakturo"); ?>">
							</div>

							<div class="wpbutton-submit-subscription">
								<input type="submit" class="button button-primary"  value="<?php _e('Subscribe', 'fakturo'); ?>">
							</div>
						</form>
					</div>
				</div>
			<?php } ?>
		</div>
		<?php
	}

	/**
	 * Static function save_subscription
	 * @access public
	 * @return void
	 * @since 1.0
	 */
	public function save_subscription() {
		if(!wp_verify_nonce($_POST['_wpnonce'], 'save_subscription_fakturo')) {
			wp_die(__('Security check', 'fakturo'));
		}
		$fname	 = sanitize_text_field($_POST['fakturo_subscription']['fname']);
		$lname	 = sanitize_text_field($_POST['fakturo_subscription']['lname']);
		$email	 = sanitize_email($_POST['fakturo_subscription']['email']);
		$redir	 = wp_sanitize_redirect($_POST['_wp_http_referer']);

		if(empty($fname) || empty($lname) || empty($email) || !is_email($email)) {
			wp_redirect($redir);
			exit;
		}
		$current_user	 = wp_get_current_user();
		$response		 = wp_remote_post($this->api_url_subscription, array(
			'method'		 => 'POST',
			'timeout'		 => 45,
			'redirection'	 => 2,
			'httpversion'	 => '1.0',
			'blocking'		 => true,
			'headers'		 => array(),
			'body'			 => array('FNAME' => $fname, 'LNAME' => $lname, 'EMAIL' => $email),
			'cookies'		 => array()
			)
		);
		if(!is_wp_error($response)) {
			update_option('fakturo_subscription_email_' . md5($current_user->ID), $email);
			WPeMatico::add_wp_notice(array('text' => __('Subscription saved', 'fakturo'), 'below-h2' => true));
		}

		wp_redirect($redir);
		exit;
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
