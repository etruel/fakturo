<?php
// don't load directly
if ( !defined('ABSPATH') ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

/**
 *  PLUGINS PAGES ADDONS
 *  Experimental.  Uses worpdress plugins.php file filtered
 */
class fktr_admin_page {

	const PAGE		 = 'fakturo';
	const SCREEN	 = 'plugins_page_fakturo';
	const FEED_URL	 = 'https://etruel.com/downloads/category/fakturo-add-ons/feed/';
	const TRANSIENT	 = 'fakturo_addons_data';
	const KEY_PREFIX = 'fakturo_'; // Every add-on key of the list carries it.

	function __construct() {

		add_action('admin_init', array(__CLASS__, 'redirect_to_addon_page'),0  );
		add_action('admin_menu', array(__CLASS__, 'admin_menu'),99 );
		add_action('admin_print_styles', array(__CLASS__,'styles'));
		add_action('admin_enqueue_scripts', array(__CLASS__, 'addons_scripts'));
		add_filter('all_plugins', array(__CLASS__, 'showhide_addons'));
		add_filter('views_plugins', array(__CLASS__, 'addons_view_link'));
		add_filter('wp_redirect', array(__CLASS__, 'keep_screen_on_autoupdate'));
		add_filter('manage_plugins_page_fakturo_columns', array(__CLASS__, 'addons_get_columns'));
		add_action('manage_plugins_custom_column', array(__CLASS__, 'addons_custom_columns') ,10,3);
		add_action('upgrader_process_complete', array(__CLASS__, 'flush_feed_backoff'));

	}

	/**
	 * Sends the user back to the add-ons screen after an activate, deactivate or delete
	 * started there, which core answers by redirecting to the plain plugins list.
	 */
	public static function redirect_to_addon_page() {
		global $pagenow;
		if (wp_doing_ajax() || wp_doing_cron() || $pagenow !== 'plugins.php' || !empty($_REQUEST['page'])) {
			return;
		}
		$actioned = fktr_array_multi_key_exists(array('error', 'deleted', 'activate', 'activate-selected', 'activate-multi', 'deactivate', 'deactivate-selected', 'deactivate-multi', '_error_nonce'), $_REQUEST, false);
		$referer  = isset($_SERVER['HTTP_REFERER']) ? wp_unslash($_SERVER['HTTP_REFERER']) : '';
		if ($actioned && preg_match('/[?&]page=' . self::PAGE . '(&|$)/', $referer) && !headers_sent()) {
			// Keep the query core just built: it carries the outcome of the action.
			wp_safe_redirect(add_query_arg('page', self::PAGE, esc_url_raw(wp_unslash($_SERVER['REQUEST_URI']))));
			exit;
		}
	}

	public static function admin_menu() {
		$page = add_submenu_page(
		'plugins.php',
			__( 'Add-ons', 'fakturo'),
			__( 'Fakturo Add-ons', 'fakturo'),
			'manage_options',
			self::PAGE,
			array(__CLASS__, 'plugins_page')
		);
		add_action('load-' . $page, array(__CLASS__, 'handle_bulk_actions'));
		$page = add_submenu_page(
			'fakturo_dashboard',
			__( 'Add-ons', 'fakturo'),
			__( 'Extensions', 'fakturo'),
			'manage_options',
			'plugins.php?page=fakturo'
		);

	}

	public static function styles() {
		wp_dequeue_style('icons');
	}

	public static function addons_scripts($hook_suffix) {
		if ($hook_suffix !== self::SCREEN) {
			return;
		}
		wp_enqueue_script('jquery');
		wp_enqueue_script('plupload-all');
		wp_enqueue_style('plugin-install');
		wp_enqueue_script('plugin-install');
		add_thickbox();
		$css = FAKTURO_PLUGIN_DIR . 'assets/css/addons_page.css';
		$js	 = FAKTURO_PLUGIN_DIR . 'assets/js/addons_page.js';
		wp_enqueue_style('fktr-addons-page', FAKTURO_PLUGIN_URL . 'assets/css/addons_page.css', array(), WPE_FAKTURO_VERSION . '.' . filemtime($css));
		wp_enqueue_script( 'jquery-addons-page', FAKTURO_PLUGIN_URL . 'assets/js/addons_page.js', array( 'jquery' ), WPE_FAKTURO_VERSION . '.' . filemtime($js), true );
		wp_localize_script('jquery-addons-page', 'fktr_addons_object', array(
			/* translators: 1: matching add-ons, 2: add-ons listed. */
			'live' => __('Showing %1$s of %2$s.', 'fakturo'),
			'none' => __('No add-ons match your search.', 'fakturo'),
			'page' => self::PAGE,
		));
	}

	/**
	 * The bulk action of the current request, read as WP_List_Table does: only
	 * `action`, "-1" meaning none.
	 *
	 * @return string Action name, empty when none was chosen.
	 */
	private static function current_action() {
		if (isset($_REQUEST['action']) && '-1' != $_REQUEST['action']) {
			return sanitize_text_field(wp_unslash($_REQUEST['action']));
		}
		return '';
	}

	/**
	 * Hands the request over to wp-admin/plugins.php, which owns every bulk action
	 * offered here: capability and nonce checks, the delete confirmation screen and
	 * the redirect. `page=fakturo` exits before core would otherwise see the action.
	 *
	 * Runs on `load-{$page_hook}`, never on `admin_menu`: `delete-selected` renders a
	 * confirmation through admin-header.php, and admin_menu fires before the screen
	 * is set. The selection must reach core exactly as it arrived.
	 */
	public static function handle_bulk_actions() {
		// Core refreshes its update data on its own plugins list; here, before any
		// output. It self-throttles to twelve hours.
		wp_update_plugins();
		if (function_exists('wp_plugin_update_rows')) {
			wp_plugin_update_rows();
		}

		// "-1" means no action was chosen, and handing that over would render core's
		// own plugins list on top of this screen.
		if ('' === self::current_action()) {
			return;
		}
		global $user_ID, $status, $page;
		$user_ID = get_current_user_id();
		$status	 = 'all';
		$page	 = 1;
		require ABSPATH . 'wp-admin/plugins.php';
	}

	public static function plugins_page() {
		global $s, $status, $page;
		if (!class_exists('WP_List_Table')) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
		}
		if (!class_exists('WP_Plugins_List_Table')) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-plugins-list-table.php';
		}

		// $s is the one piece of state WP_Plugins_List_Table does not set for itself;
		// only wp-admin/plugins.php does, and this screen never reaches that file.
		// urlencode() matches core: _search_callback() urldecode()s it back.
		$s		= isset($_REQUEST['s']) ? urlencode(wp_unslash($_REQUEST['s'])) : '';
		$status	= 'all';
		$page	= 1;

		// Same cleanup core does, so the result of the last action does not ride along
		// in the paging and Screen Options URLs and get reported again on every click.
		$_SERVER['REQUEST_URI'] = remove_query_arg(
			array('error', 'deleted', 'activate', 'activate-multi', 'deactivate', 'deactivate-multi',
				'enabled-auto-update', 'disabled-auto-update', 'enabled-auto-update-multi', 'disabled-auto-update-multi', '_error_nonce'),
			$_SERVER['REQUEST_URI']
		);

		// On multisite core reads the header file of every row (is_network_only_plugin()), and a
		// catalogue row has none. Only that read is silenced, only for the length of this screen.
		set_error_handler(function ($errno, $errstr) {
			return 0 === strpos($errstr, 'file_get_contents(' . WP_PLUGIN_DIR . '/' . self::KEY_PREFIX)
				&& false !== strpos($errstr, 'No such file or directory');
		}, E_WARNING);

		// "Inactive" means installed and switched off: the catalogue rows belong to "All" only.
		add_filter('plugins_list', array(__CLASS__, 'installed_views_only'));
		$plugins_list_table = new WP_Plugins_List_Table();
		$plugins_list_table->prepare_items();
		remove_filter('plugins_list', array(__CLASS__, 'installed_views_only'));

		echo '<div class="wrap">';
		echo '<h1 class="wp-heading-inline">'.esc_html__( 'Fakturo Add-ons Plugins', 'fakturo').'</h1>';
		echo '<hr class="wp-header-end">';
		self::result_notice();
		$plugins_list_table->views();
		?>
		<?php // Core's search box is bound to the AJAX search of the plugins screen, which this one is not: own markup, own filtering. ?>
		<form class="search-form fktr-addons-search" method="get">
			<input type="hidden" name="page" value="<?php echo esc_attr(self::PAGE); ?>" />
			<p class="search-box">
				<span class="fktr-addons-filter-live" aria-live="polite" hidden></span>
				<label class="screen-reader-text" for="fktr-addons-search-input"><?php esc_html_e('Search Add-ons', 'fakturo'); ?></label>
				<input type="search" id="fktr-addons-search-input" name="s" value="<?php _admin_search_query(); ?>" autocomplete="off" placeholder="<?php esc_attr_e('Add-on name, feature…', 'fakturo'); ?>" />
				<input type="submit" id="search-submit" class="button" value="<?php esc_attr_e('Search Add-ons', 'fakturo'); ?>" />
			</p>
		</form>
		<form method="post" id="bulk-action-form">
			<input type="hidden" name="plugin_status" value="<?php echo esc_attr($status); ?>" />
			<input type="hidden" name="paged" value="<?php echo esc_attr($page); ?>" />
			<?php // Marks the request as coming from here, so core's redirect sends the user back. ?>
			<input type="hidden" name="fktr_addons" value="1" />
			<?php $plugins_list_table->display(); ?>
		</form>
		<?php
		restore_error_handler();
		echo '</div>';
	}

	/**
	 * Keeps the catalogue out of every view but "All". A row the store sells but the
	 * site does not have installed carries 'Remote'; core counts it as inactive.
	 * The 'search' bucket is left alone: searching looks through the whole catalogue.
	 */
	public static function installed_views_only($plugins) {
		foreach ($plugins as $bucket => $rows) {
			if ('all' === $bucket || 'search' === $bucket || !is_array($rows)) {
				continue;
			}
			foreach ($rows as $plugin_file => $plugin_data) {
				if (!empty($plugin_data['Remote'])) {
					unset($plugins[$bucket][$plugin_file]);
				}
			}
		}
		return $plugins;
	}

	/**
	 * The outcome of the last action, reported as core's plugins.php does: a template
	 * this screen never reaches. Strings with no text domain are core's own.
	 */
	private static function result_notice() {
		if (isset($_GET['error'])) {
			$message = isset($_GET['main'])
				? __('You cannot delete a plugin while it is active on the main site.')
				: __('The plugin could not be activated because it triggered a fatal error.', 'fakturo');
			echo '<div id="message" class="error notice is-dismissible"><p>' . esc_html($message) . '</p></div>';
			return;
		}

		if (isset($_GET['deleted'])) {
			// delete_plugins() leaves its result in an option, and a failure comes back
			// as a WP_Error there and nowhere else.
			$option	 = 'plugins_delete_result_' . get_current_user_id();
			$result	 = get_option($option);
			delete_option($option);

			if (is_wp_error($result)) {
				echo '<div id="message" class="error notice is-dismissible"><p><strong>'
					. esc_html__('Deletion failed:', 'fakturo') . '</strong> '
					. esc_html($result->get_error_message()) . '</p></div>';
				return;
			}

			$message = ('1' === (string) $_GET['deleted'])
				? __('The selected add-on has been deleted.', 'fakturo')
				: __('The selected add-ons have been deleted.', 'fakturo');
			echo '<div id="message" class="updated notice is-dismissible"><p>' . esc_html($message) . '</p></div>';
			return;
		}

		$messages = array(
			'activate'			=> __('Plugin activated.'),
			'activate-multi'	=> __('Selected plugins activated.'),
			'deactivate'		=> __('Plugin deactivated.'),
			'deactivate-multi'	=> __('Selected plugins deactivated.'),
			'enabled-auto-update'		 => __('Plugin will be auto-updated.'),
			'disabled-auto-update'		 => __('Plugin will no longer be auto-updated.'),
			'enabled-auto-update-multi'	 => __('Selected plugins will be auto-updated.'),
			'disabled-auto-update-multi' => __('Selected plugins will no longer be auto-updated.'),
		);
		foreach ($messages as $arg => $message) {
			if (isset($_GET[$arg])) {
				echo '<div id="message" class="updated notice is-dismissible"><p>' . esc_html($message) . '</p></div>';
				return;
			}
		}
	}

	/**
	 * True during any plugin auto-update save request. They intersect the saved option
	 * against apply_filters('all_plugins', ...), so hiding the add-ons then would strip
	 * their auto-update setting every time any plugin is toggled.
	 */
	private static function is_autoupdate_save_action() {
		return in_array(
			self::current_action(),
			array('toggle-auto-updates', 'enable-auto-update', 'disable-auto-update', 'enable-auto-update-selected', 'disable-auto-update-selected'),
			true
		);
	}

	/**
	 * Sends the user back to this screen, instead of the generic plugins list, after
	 * toggling auto-updates from here (row link or bulk form, both marked `fktr_addons`).
	 */
	public static function keep_screen_on_autoupdate($location) {
		if (!empty($_REQUEST['fktr_addons']) && self::is_autoupdate_save_action() && 'toggle-auto-updates' !== self::current_action()
				&& false !== strpos($location, 'plugins.php')) {
			$location = add_query_arg('page', self::PAGE, $location);
		}
		return $location;
	}

	public static function showhide_addons($plugins) {
		global $current_screen;

		if (self::is_autoupdate_save_action()) {
			return $plugins;
		}

		// The screen is not always there (an early all_plugins filter, WP-CLI), and
		// reading ->id on null is a PHP 8 warning.
		$screen_id = ($current_screen instanceof WP_Screen) ? $current_screen->id : '';
		if ($screen_id == self::SCREEN){
			$plugins = apply_filters('fktr_addons_array', self::read_addons($plugins));
			foreach ($plugins as $key => $value) {
				if(strpos($key, 'fakturo_')===FALSE) {
					unset( $plugins[$key] );
				} else {
					if(isset($plugins[$key]['Remote'])){
						add_filter( "plugin_action_links_{$key}", array(__CLASS__, 'addons_row_actions'),15,4);
					}
				}
			}
		} elseif (is_admin()) { // Hidden from the plugins list only; WP-CLI and other callers see them.
			foreach ($plugins as $key => $value) {
				if(strpos($key, 'fakturo_')!==FALSE) {
					unset( $plugins[$key] );
				}
			}
		}

		return $plugins;
	}

	/**
	 * The add-ons are not on the plugins list, so it links to the screen that has them.
	 */
	public static function addons_view_link($views) {
		$views['fakturo-addons'] = '<a href="' . esc_url(admin_url('plugins.php?page=' . self::PAGE)) . '" class="fktr-links" target="_top">' . esc_html__('Fakturo Add-ons', 'fakturo') . '</a>';
		return $views;
	}

	public static function addons_row_actions($actions, $plugin_file, $plugin_data, $context) {
		$actions = array();
		$actions['buynow'] =  '<a target="_Blank" rel="noopener" class="edit" aria-label="' . esc_attr( sprintf( __( 'Go to %s WebPage','fakturo'), $plugin_data['Name'] ) ) . '" title="' . esc_attr( sprintf( __( 'Open %s WebPage in new window.','fakturo'), $plugin_data['Name'] ) ) . '" href="'.esc_url($plugin_data['PluginURI']).'">' . __('Details','fakturo') . '</a>';
		return $actions;
	}

	/**
	 * How long the store feed is allowed to hold the admin up, in seconds.
	 */
	public static function feed_timeout() {
		return (int) apply_filters('fakturo_addons_feed_timeout', 8);
	}

	public static function feed_options($feed) {
		$feed->enable_cache(false); // The result is kept in a transient of its own.
		$feed->set_timeout(self::feed_timeout());
	}

	/**
	 * The add-ons the store sells, from its feed, cached.
	 *
	 * A failed or empty answer is never stored as "the store sells nothing": it sets a
	 * short back-off, so a store that is down costs one bounded attempt every so often
	 * instead of one on every load of this screen. A per-request memo covers the table,
	 * which asks once per column of every row.
	 *
	 * @return array
	 */
	public static function get_feed_addons() {
		static $memo = null;
		if (null !== $memo) {
			return $memo;
		}

		$cached = get_transient(self::TRANSIENT);
		if (!is_array($cached) && !get_transient(self::TRANSIENT . '_backoff')) {
			include_once ABSPATH . WPINC . '/feed.php';
			add_action('wp_feed_options', array(__CLASS__, 'feed_options'));
			$addonitems = fetch_feed(self::FEED_URL);
			remove_action('wp_feed_options', array(__CLASS__, 'feed_options'));

			$addon = array();
			if (!is_wp_error($addonitems)) {
				foreach ($addonitems->get_items() as $item) {
					$itemtitle	 = sanitize_text_field($item->get_title());
					$versions	 = $item->get_item_tags('', 'version');
					$version	 = (is_array($versions)) ? sanitize_text_field($versions[0]['data']) : '';
					$guid		 = $item->get_item_tags('', 'guid');
					$guid		 = (is_array($guid)) ? $guid[0]['data'] : '';
					$query		 = array();
					wp_parse_str((string) wp_parse_url($guid, PHP_URL_QUERY), $query);
					$download_id = isset($query['p']) ? absint($query['p']) : 0;

					// The list keeps only the keys carrying "fakturo_".
					$plugindirname = str_replace('-', '_', strtolower(sanitize_file_name($itemtitle)));
					if (false === strpos($plugindirname, 'fakturo_')) {
						$plugindirname = 'fakturo_' . $plugindirname;
					}

					$enclosure	 = $item->get_enclosure();
					$img		 = ( $enclosure && !empty($enclosure->link) ) ? $enclosure->link : '';
					if ('' !== $img) {
						$icon = '<img width="100" src="' . esc_url($img) . '" alt="' . esc_attr($itemtitle) . '">';
					} else {
						$icon = '<span class="fktr-addon-icon-placeholder" aria-hidden="true"><span class="dashicons dashicons-admin-plugins"></span></span>';
					}

					$buynowURI = ($download_id) ? 'https://etruel.com/checkout?edd_action=add_to_cart&download_id='.$download_id.'&edd_options[price_id]=1' : 'https://etruel.com/';
					$addon[ $plugindirname ] = Array (
						'Name'		  => $itemtitle,
						'icon'		  => $icon,
						'PluginURI'	  => esc_url_raw($item->get_permalink()),
						'buynowURI'	  => $buynowURI,
						'Version'	  => $version,
						'Description' => wp_kses_post($item->get_description()),
						'Author'	  => 'etruel',
						'AuthorURI'   => 'https://etruel.com',
						'TextDomain'  => '',
						'DomainPath'  => '',
						'Network'	  => '',
						'Title'		  => $itemtitle,
						'AuthorName'  => 'etruel',
						'Remote'	  => true,
						'id'		  => $download_id,
					);
				}
			}
			$addons = array_filter($addon);

			if (empty($addons)) {
				set_transient(self::TRANSIENT . '_backoff', 1, apply_filters('fakturo_addons_feed_backoff', 15 * MINUTE_IN_SECONDS));
			} else {
				set_transient(self::TRANSIENT, $addons, apply_filters('fakturo_addons_transient_length', DAY_IN_SECONDS));
				$cached = $addons;
			}
		}

		$memo = is_array($cached) ? $cached : array();
		return $memo;
	}

	/**
	 * Drops the back-off after a plugin install or update, where "what is for sale" may
	 * have changed, so the next visit asks the store again.
	 */
	public static function flush_feed_backoff() {
		delete_transient(self::TRANSIENT . '_backoff');
	}

	/**
	 * Whether two entries describe the same add-on: same name, or same store page.
	 * Comparing only strstr($uri, '://') made two plugins with no PluginURI equal.
	 */
	private static function is_same_addon($plugin, $addon) {
		if (!empty($plugin['Name']) && !empty($addon['Name']) && $plugin['Name'] === $addon['Name']) {
			return true;
		}
		$plugin_uri	 = empty($plugin['PluginURI']) ? '' : untrailingslashit(strstr($plugin['PluginURI'], '://'));
		$addon_uri	 = empty($addon['PluginURI']) ? '' : untrailingslashit(strstr($addon['PluginURI'], '://'));

		return ('' !== $plugin_uri && $plugin_uri === $addon_uri);
	}

	/**
	 * The add-ons published free on WordPress.org. The store feed lists what is sold, so
	 * these are offered for installation from wordpress.org instead.
	 *
	 * Keyed by wp.org slug, valued by the `Plugin Name` header: the folder of an installed
	 * copy is not the slug.
	 *
	 * @return array slug => Plugin Name
	 */
	public static function free_addons() {
		return apply_filters('fakturo_free_addons', array(
			'etruel-stock-in-list-for-eshop' => 'Fakturo Stock in List',
		));
	}

	/**
	 * Whether a plugin with this `Plugin Name` is installed on the site.
	 */
	private static function is_installed_addon($name) {
		static $installed = null;
		if (null === $installed) {
			if (!function_exists('get_plugins')) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			$installed = wp_list_pluck(get_plugins(), 'Name');
		}
		return in_array($name, $installed, true);
	}

	/**
	 * Return the array of plugins plus the Add-ons found on the etruel.com website.
	 * @param array $plugins current plugins
	 */
	public static function read_addons($plugins){
		$cached = self::get_feed_addons();

		// Tag the feed entries that are free on wordpress.org, and add the free ones the
		// feed does not carry and the site does not have.
		foreach (self::free_addons() as $slug => $name) {
			$found = false;
			foreach ($cached as $Akey => $addon) {
				if (isset($addon['Name']) && $addon['Name'] === $name) {
					$cached[$Akey]['wporg_slug'] = $slug;
					$found						 = true;
					break;
				}
			}
			if ($found || self::is_installed_addon($name)) {
				continue;
			}
			$cached['fakturo_' . str_replace('-', '_', $slug)] = array(
				'Name'			 => $name,
				'icon'			 => '<span class="fktr-addon-icon-placeholder" aria-hidden="true"><span class="dashicons dashicons-admin-plugins"></span></span>',
				'PluginURI'		 => 'https://wordpress.org/plugins/' . $slug . '/',
				'Version'		 => '',
				'Description'	 => __('Add a column specifying the stock of each product plus other features like print or export Products list.', 'fakturo'),
				'Author'		 => 'Etruel Developments LLC',
				'AuthorURI'		 => 'https://etruel.com',
				'TextDomain'	 => '',
				'DomainPath'	 => '',
				'Network'		 => '',
				'Title'			 => $name,
				'AuthorName'	 => 'etruel',
				'Remote'		 => true,
				'id'			 => 0,
				'wporg_slug'	 => $slug,
			);
		}

		foreach ($plugins as $key => $plugin) {
			foreach ($cached as $Akey => $addon) {
				// Same add-on on both sides: keep the installed one, drop the feed row.
				if (self::is_same_addon($plugin, $addon)) {
					unset($cached[$Akey]);
					$plugins[$key]['installed'] = true;
				}
			}
		}

		return array_merge( $plugins, $cached );
	}

	public static function addons_get_columns() {
		global $status;

		return array(
			'cb'          => !in_array( $status, array( 'mustuse', 'dropins' ) ) ? '<input type="checkbox" />' : '',
			'icon'        => __( 'Icon', 'fakturo' ),
			'name'        => __( 'Add On', 'fakturo' ),
			'description' => __( 'Description', 'fakturo' ),
			'buybutton'   => __( 'Purchase', 'fakturo' ),
		);
	}

	/**
	 * The "Installed" label of an add-on, linked to its page at the store when it has one.
	 */
	private static function installed_button($store_url, $title) {
		if ('' === $store_url) {
			return '<span class="button disabled">' . esc_html__('Installed', 'fakturo') . '</span>';
		}
		return sprintf(
			'<a class="button fktr-btn-installed" target="_blank" rel="noopener" title="%s" href="%s">%s</a>',
			esc_attr($title),
			esc_url($store_url),
			esc_html__('Installed', 'fakturo')
		);
	}

	/**
	 * The control of an add-on that is on this site, the one that matters for its state:
	 * start it when stopped; send it to the license screen when it has no valid license,
	 * since the store only updates licensed add-ons; otherwise the auto-updates switch.
	 *
	 * The switch is a link that plugins.php handles and redirects back from, because core's
	 * own toggle script is bound to the plugins screen.
	 *
	 * @param string $plugin_file Plugin basename (dir/file.php).
	 * @param array  $plugin_data Plugin data row.
	 * @param array  $addon       Its row in the store feed, empty when it has none.
	 * @return string HTML.
	 */
	private static function row_control($plugin_file, $plugin_data, $addon) {
		$name = isset($plugin_data['Name']) ? $plugin_data['Name'] : '';

		if (!is_plugin_active($plugin_file) && current_user_can('activate_plugin', $plugin_file)) {
			$url = wp_nonce_url(
				self_admin_url('plugins.php?action=activate&plugin=' . urlencode($plugin_file) . '&plugin_status=all&paged=1'),
				'activate-plugin_' . $plugin_file
			);
			return sprintf(
				'<a class="button button-primary fktr-btn-activate" title="%s" href="%s"><span class="dashicons dashicons-controls-play" aria-hidden="true"></span> %s</a>',
				/* translators: %s: add-on name. */
				esc_attr(sprintf(__('Activate %s on this site', 'fakturo'), $name)),
				esc_url($url),
				esc_html__('Activate', 'fakturo')
			);
		}

		$store_url		 = !empty($addon['PluginURI']) ? set_url_scheme($addon['PluginURI'], 'https') : '';
		$autoupdates_off = !function_exists('wp_is_auto_update_enabled_for_type') || !wp_is_auto_update_enabled_for_type('plugin');
		if (!is_plugin_active($plugin_file) || $autoupdates_off || !current_user_can('update_plugins')) {
			$title = ( $autoupdates_off && is_plugin_active($plugin_file) )
				? __('Automatic updates are turned off on this site.', 'fakturo')
				: __('See details and prices on etruel\'s store', 'fakturo');
			return self::installed_button($store_url, $title);
		}

		// An add-on with no license slot (a free one) keeps the switch.
		$license_name = class_exists('fktr_licenses_handlers') ? fktr_licenses_handlers::name_by_file($plugin_file) : false;
		if ($license_name && !fktr_licenses_handlers::has_valid_license($license_name)) {
			$prompt = fktr_licenses_handlers::license_prompt($license_name);
			return sprintf(
				'<a class="button fktr-btn-license" title="%s" href="%s">%s</a>',
				esc_attr($prompt['title']),
				esc_url($prompt['url']),
				esc_html($prompt['caption'])
			);
		}

		$enabled = in_array($plugin_file, (array) get_site_option('auto_update_plugins', array()), true);
		$url	 = add_query_arg(
			array(
				'action'		 => ( $enabled ? 'disable' : 'enable' ) . '-auto-update',
				'plugin'		 => $plugin_file,
				'paged'			 => 1,
				'plugin_status'	 => 'all',
				'fktr_addons'	 => 1, // Marker: the request comes from this screen.
			),
			self_admin_url('plugins.php')
		);
		return sprintf(
			'<a href="%s" class="button fktr-btn-autoupdate%s">%s</a>',
			esc_url(wp_nonce_url($url, 'updates')),
			$enabled ? ' is-enabled' : '',
			// Core's own strings, translated everywhere without a copy of ours.
			esc_html($enabled ? __('Disable auto-updates') : __('Enable auto-updates'))
		);
	}

	public static function addons_custom_columns($column_name, $plugin_file, $plugin_data ) {
		$name	 = isset($plugin_data['Name']) ? (string) $plugin_data['Name'] : '';
		$uri	 = isset($plugin_data['PluginURI']) ? (string) $plugin_data['PluginURI'] : '';
		if (empty($plugin_data['Remote']) && strpos($name, 'Fakturo ') === false && stripos($uri, 'fakturo') === false) {
			return true;
		}

		// The feed row of this add-on: an installed plugin is built from its headers,
		// which carry neither artwork nor a store address.
		$addon = array();
		foreach (self::get_feed_addons() as $value) {
			if (self::is_same_addon($plugin_data, $value)) {
				$addon = $value;
				break;
			}
		}

		switch ($column_name) {
			case 'icon':
				if (!empty($plugin_data['icon'])) {
					echo $plugin_data['icon'];
				} elseif (isset($addon['icon'])) {
					echo $addon['icon'];
				}
				break;

			case 'buybutton':
				if (empty($plugin_data['Remote'])) { // On this site.
					echo self::row_control($plugin_file, $plugin_data, $addon);
					break;
				}
				// Free on wordpress.org: install it right here, with WordPress' own installer.
				if (!empty($plugin_data['wporg_slug']) && current_user_can('install_plugins')) {
					$slug = $plugin_data['wporg_slug'];
					printf(
						'<a class="button button-primary fktr-btn-install" title="%s" href="%s"><span class="dashicons dashicons-download" aria-hidden="true"></span> %s</a>',
						/* translators: %s: add-on name. */
						esc_attr(sprintf(__('Install %s from WordPress.org, free', 'fakturo'), $plugin_data['Name'])),
						esc_url(wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . urlencode($slug)), 'install-plugin_' . $slug)),
						esc_html__('Install now', 'fakturo')
					);
					break;
				}
				printf(
					'<a target="_blank" rel="noopener" class="button button-primary" title="%s" href="%s">%s</a>',
					esc_attr__('Go to purchase on the etruel\'s store', 'fakturo'),
					esc_url(set_url_scheme($plugin_data['buynowURI'], 'https')),
					esc_html__('Purchase', 'fakturo')
				);
				break;
		}
		return true;
	}

}
$fktr_admin_page = new fktr_admin_page();
