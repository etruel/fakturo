<?php
// Exit if accessed directly
if ( !defined('ABSPATH') ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

/**
 * Licenses of the add-ons against the etruel.com store (EDD Software Licensing).
 *
 * Add-ons register through the `fktr_plugins_updater_args` filter. Options: `fktr_license_keys` (key per
 * add-on), `fktr_license_status` (status per add-on) and `fktr_license_info` (what the store last said,
 * so the screen never asks it on every load).
 */
class fktr_licenses_handlers {

	const SCREEN		 = 'admin_page_fakturo-license-page';
	const SLUG			 = 'fakturo-license-page';
	const NONCE			 = 'fktr_licenses';
	const CAPABILITY	 = 'manage_options';
	const CHECK_EVERY	 = 12 * HOUR_IN_SECONDS;
	const ACCOUNT_URL	 = 'https://etruel.com/my-account/purchase-history/';
	const CHECKOUT_URL	 = 'https://etruel.com/checkout/';
	const LICENSES_URL	 = 'https://etruel.com/my-account/licenses/';

	private static $api_urls = array();

	public static function hooks() {
		add_action('init', array(__CLASS__, 'plugin_updater'), 0);
		add_action('fktr_licenses_forms', array(__CLASS__, 'license_page'));
		add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue'));
		add_action('wp_ajax_fktr_license', array(__CLASS__, 'ajax_license'));
		add_action('admin_post_fktr_save_licenses', array(__CLASS__, 'save_licenses'));
	}

	/**
	 * Updates come from the store. It runs in the admin and in WP-Cron, whose update check never fires admin_init.
	 */
	public static function plugin_updater() {
		if (!is_admin() && !wp_doing_cron()) {
			return;
		}
		$plugins_args = apply_filters('fktr_plugins_updater_args', array());
		if (empty($plugins_args)) {
			return;
		}
		self::migrate();
		if (is_multisite() && !has_filter('http_request_args', array(__CLASS__, 'network_url'))) {
			add_filter('http_request_args', array(__CLASS__, 'network_url'), 10, 2);
		}
		if (!class_exists('EDD_SL_Plugin_Updater')) {
			$lib = FAKTURO_PLUGIN_DIR . 'includes/libs/Plugin_Updater.php';
			if (!file_exists($lib)) {
				return;
			}
			require_once $lib;
		}
		foreach ($plugins_args as $plugin_name => $args) {
			self::$api_urls[] = $args['api_url'];
			$data = array(
				'version'	 => $args['api_data']['version'],
				'license'	 => self::get_key($plugin_name),
				'item_name'	 => $args['api_data']['item_name'],
				'author'	 => $args['api_data']['author'],
			);
			if (!empty($args['api_data']['item_id'])) {
				$data['item_id'] = $args['api_data']['item_id'];
			}
			new EDD_SL_Plugin_Updater($args['api_url'], $args['plugin_file'], $data);
			if (!is_multisite()) {
				add_action('after_plugin_row_' . plugin_basename($args['plugin_file']), 'wp_plugin_update_row', 10, 2);
			}
		}
	}

	public static function enqueue() {
		$screen = get_current_screen();
		if (!$screen || ( $screen->id !== self::SCREEN && strpos($screen->id, fktrNetwork::LICENSES_SLUG) === false )) {
			return;
		}
		$css = FAKTURO_PLUGIN_DIR . 'assets/css/licenses_handlers.css';
		$js	 = FAKTURO_PLUGIN_DIR . 'assets/js/licenses_handlers.js';
		wp_enqueue_style('fktr-settings-licenses', FAKTURO_PLUGIN_URL . 'assets/css/licenses_handlers.css', array(), WPE_FAKTURO_VERSION . '.' . filemtime($css));
		wp_enqueue_script('fktr-jquery-settings-licenses', FAKTURO_PLUGIN_URL . 'assets/js/licenses_handlers.js', array('jquery'), WPE_FAKTURO_VERSION . '.' . filemtime($js), true);
		wp_localize_script('fktr-jquery-settings-licenses', 'fktr_license_object', array(
			'ajax_url'	 => admin_url('admin-ajax.php'),
			'nonce'		 => wp_create_nonce(self::NONCE),
			'working'	 => __('Working…', 'fakturo'),
			'failed'	 => __('The license server did not answer. Try again in a moment.', 'fakturo'),
		));
	}

	/* ---------- Stored data ---------- */

	/**
	 * Keys, status and account data are kept per network in a multisite (one license activates the whole network),
	 * and per site otherwise; on a single site the site options are the regular ones.
	 */
	private static function get_map($option) {
		$map = get_site_option($option, array());
		return is_array($map) ? $map : array();
	}

	private static function set_map($option, $map, $autoload = true) {
		if (is_multisite()) {
			update_site_option($option, $map);
		} else {
			update_option($option, $map, $autoload);
		}
	}

	/**
	 * The keys a multisite had on its main site, from before they were kept per network, are moved once.
	 */
	private static function migrate() {
		if (!is_multisite() || get_site_option('fktr_licenses_network')) {
			return;
		}
		foreach (array('fktr_license_keys', 'fktr_license_status', 'fktr_license_info') as $option) {
			$old = get_blog_option(get_main_site_id(), $option, array());
			if (!empty($old) && is_array($old) && !get_site_option($option)) {
				update_site_option($option, $old);
			}
		}
		update_site_option('fktr_licenses_network', 1);
	}

	/**
	 * Who may handle the keys: the super admin of a network, the settings managers of a single site.
	 */
	public static function allowed() {
		return is_multisite() ? current_user_can('manage_network_options') : current_user_can(self::CAPABILITY);
	}

	/**
	 * The address the store knows this install by: the network, not each of its sites.
	 */
	public static function license_url() {
		return is_multisite() ? untrailingslashit(network_home_url()) : home_url();
	}

	/**
	 * The license screen: in the network admin of a multisite.
	 */
	public static function page_url() {
		return is_multisite() ? fktrNetwork::page_url(fktrNetwork::LICENSES_SLUG) : admin_url('admin.php?page=' . self::SLUG);
	}

	/**
	 * The store gets the address of the network for the update checks the sites make.
	 */
	public static function network_url($args, $url) {
		if (!is_array($args) || !isset($args['body']) || !is_array($args['body']) || !isset($args['body']['url']) || $args['body']['url'] !== home_url()) {
			return $args;
		}
		foreach (self::$api_urls as $api_url) {
			if (strpos($url, $api_url) === 0) {
				$args['body']['url'] = self::license_url();
				break;
			}
		}
		return $args;
	}

	public static function get_key($plugin_name) {
		$keys = self::get_map('fktr_license_keys');
		return ( isset($keys[$plugin_name]) && is_string($keys[$plugin_name]) && $keys[$plugin_name] !== '' ) ? $keys[$plugin_name] : false;
	}

	public static function get_license_status($plugin_name) {
		$status = self::get_map('fktr_license_status');
		return ( !empty($status[$plugin_name]) && is_string($status[$plugin_name]) ) ? $status[$plugin_name] : false;
	}

	public static function set_license_status($plugin_name, $status) {
		$map				 = self::get_map('fktr_license_status');
		$map[$plugin_name]	 = sanitize_key($status);
		self::set_map('fktr_license_status', $map);
	}

	public static function get_license_info($plugin_name) {
		$info = self::get_map('fktr_license_info');
		return ( isset($info[$plugin_name]) && is_array($info[$plugin_name]) ) ? $info[$plugin_name] : array();
	}

	/**
	 * The license slot an installed add-on registered, found by its plugin file, or false.
	 * Add-ons declare their updater under a key of their own in `fktr_plugins_updater_args`.
	 *
	 * @param string $plugin_file Plugin basename (dir/file.php).
	 */
	public static function name_by_file($plugin_file) {
		static $by_file = null;
		if (null === $by_file) {
			$by_file = array();
			foreach ((array) apply_filters('fktr_plugins_updater_args', array()) as $plugin_name => $args) {
				if (!empty($args['plugin_file'])) {
					$by_file[plugin_basename($args['plugin_file'])] = $plugin_name;
				}
			}
		}
		return isset($by_file[$plugin_file]) ? $by_file[$plugin_file] : false;
	}

	public static function has_valid_license($plugin_name) {
		return ( self::get_key($plugin_name) && self::get_license_status($plugin_name) === 'valid' );
	}

	/**
	 * What an add-on without a valid license shows in its row: the caption of the button
	 * and the sentence that explains it. Where the license screen is the answer to all of them.
	 *
	 * @return array caption, title, url
	 */
	public static function license_prompt($plugin_name) {
		$status = self::get_key($plugin_name) ? self::get_license_status($plugin_name) : '';
		if ($status === '') {
			$caption = __('Add license', 'fakturo');
			$title	 = __('Add the license key of this add-on to enable automatic updates.', 'fakturo');
		} elseif ($status === 'expired') {
			$caption = __('Renew license', 'fakturo');
			$title	 = __('This license has expired. Renew it to enable automatic updates.', 'fakturo');
		} elseif (in_array($status, array('inactive', 'site_inactive', 'deactivated'), true)) {
			$caption = __('Activate license', 'fakturo');
			$title	 = __('The key is right but this site is not activated with it. Use Activate License to receive updates and support.', 'fakturo');
		} else {
			$caption = __('Check license', 'fakturo');
			$title	 = __('Check the license key of this add-on to enable automatic updates.', 'fakturo');
		}
		return array('caption' => $caption, 'title' => $title, 'url' => self::page_url());
	}

	/**
	 * Keeps what a store answer says about the license: status and the account data shown on the screen.
	 */
	private static function remember($plugin_name, $data) {
		if (!is_object($data) || empty($data->license) || !is_string($data->license)) {
			return;
		}
		self::set_license_status($plugin_name, $data->license);
		$expires = isset($data->expires) ? (string) $data->expires : '';
		$info	 = array(
			'expires'			 => ( $expires === 'lifetime' ) ? 'lifetime' : substr(sanitize_text_field($expires), 0, 10),
			'site_count'		 => isset($data->site_count) ? (int) $data->site_count : 0,
			'license_limit'		 => isset($data->license_limit) ? (int) $data->license_limit : 0,
			'activations_left'	 => isset($data->activations_left) ? ( ( $data->activations_left === 'unlimited' ) ? 'unlimited' : (int) $data->activations_left ) : 0,
			'payment_id'		 => isset($data->payment_id) ? (int) $data->payment_id : 0,
			'license_id'		 => isset($data->license_id) ? (int) $data->license_id : 0,
			'customer_name'		 => isset($data->customer_name) ? sanitize_text_field($data->customer_name) : '',
			'customer_email'	 => isset($data->customer_email) ? sanitize_email($data->customer_email) : '',
			'checked'			 => time(),
		);
		$all				 = self::get_map('fktr_license_info');
		$all[$plugin_name]	 = $info;
		self::set_map('fktr_license_info', $all, false);
	}

	/* ---------- Talking to the store ---------- */

	/**
	 * @param string $action check_license | activate_license | deactivate_license
	 * @return object|false The store's answer, or false when it did not give a usable one.
	 */
	private static function request($plugin_name, $action, $license) {
		$plugins_args = apply_filters('fktr_plugins_updater_args', array());
		if (empty($plugins_args[$plugin_name]) || $license === '' || $license === false) {
			return false;
		}
		$args	 = $plugins_args[$plugin_name];
		$body	 = array(
			'edd_action' => $action,
			'license'	 => $license,
			'item_name'	 => rawurlencode($args['api_data']['item_name']),
			'url'		 => self::license_url(),
		);
		if (!empty($args['api_data']['item_id'])) {
			$body['item_id'] = $args['api_data']['item_id'];
		}
		$response = wp_remote_post(esc_url_raw($args['api_url']), array('timeout' => 15, 'body' => $body));
		if (is_wp_error($response) || (int) wp_remote_retrieve_response_code($response) !== 200) {
			return false;
		}
		$data = json_decode(wp_remote_retrieve_body($response));
		return is_object($data) ? $data : false;
	}

	/**
	 * Asks the store about a license and remembers the answer.
	 */
	private static function refresh($plugin_name, $action = 'check_license', $license = null) {
		if ($license === null) {
			$license = self::get_key($plugin_name);
		}
		$data = self::request($plugin_name, $action, $license);
		if ($data) {
			if ($action === 'deactivate_license') {
				if (isset($data->license) && $data->license === 'deactivated') {
					self::set_license_status($plugin_name, 'inactive');
				}
				// The account data stays what the store last said.
			} else {
				self::remember($plugin_name, $data);
			}
		}
		return $data;
	}

	private static function plugin_names() {
		return array_keys(apply_filters('fktr_plugins_updater_args', array()));
	}

	/* ---------- Screen ---------- */

	private static function status_label($status) {
		switch ($status) {
			case 'valid':
				return array('valid', __('Valid', 'fakturo'));
			case 'expired':
				return array('bad', __('Expired', 'fakturo'));
			case 'inactive':
			case 'site_inactive':
			case 'deactivated':
				return array('warn', __('Inactive', 'fakturo'));
			case false:
				return array('none', '');
			default:
				return array('bad', __('Invalid', 'fakturo'));
		}
	}

	/**
	 * The status part of a license card: state, buttons and what the store says about the account.
	 */
	public static function render_status($plugin_name, $args) {
		$license = self::get_key($plugin_name);
		if (!$license) {
			return self::render_help('');
		}
		$info = self::get_license_info($plugin_name);
		if (empty($info['checked']) || !isset($info['license_id']) || $info['checked'] < time() - self::CHECK_EVERY) {
			self::refresh($plugin_name);
			$info = self::get_license_info($plugin_name);
		}
		$status = self::get_license_status($plugin_name);
		list($class, $label) = self::status_label($status);

		$html = '<div class="fktr-license-state"><span class="fktr-license-badge ' . esc_attr($class) . '">' . esc_html($label) . '</span>';
		if ($status === 'valid') {
			$html .= '<button type="button" class="button fktr-license-act fktr-license-off" data-do="deactivate_license">' . esc_html__('Deactivate License', 'fakturo') . '</button>';
		} elseif (in_array($status, array('inactive', 'site_inactive', 'deactivated'), true)) {
			$html .= '<button type="button" class="button fktr-license-act fktr-license-on" data-do="activate_license">' . esc_html__('Activate License', 'fakturo') . '</button>';
		}
		$html .= '<button type="button" class="button fktr-license-act" data-do="check_license">' . esc_html__('Check License', 'fakturo') . '</button></div>';

		if (!empty($info['payment_id']) && !empty($info['license_limit'])) {
			$expires = isset($info['expires']) ? $info['expires'] : '';
			$left	 = isset($info['activations_left']) ? $info['activations_left'] : 0;
			$extend	 = self::extend_url($license, $args);
			$html	.= '<div class="fktr-license-meta">';
			if ($status !== 'valid' && $left !== 'unlimited' && (int) $left <= 0) {
				$html .= '<div class="fktr-license-notice"><a href="' . esc_url(self::account_url($info)) . '" target="_blank" rel="noopener">' . esc_html__("No activations left. Click here to manage the sites you've activated licenses on.", 'fakturo') . '</a></div>';
			}
			if ($expires !== 'lifetime' && $expires !== '' && strtotime($expires) !== false && strtotime($expires) < strtotime('+2 weeks')) {
				$html .= '<div class="fktr-license-notice"><a href="' . esc_url($extend) . '" target="_blank" rel="noopener">' . esc_html__('Renew your license to continue receiving updates and support.', 'fakturo') . '</a></div>';
			}
			$html .= '<strong>' . esc_html__('Activations', 'fakturo') . ':</strong> ' . (int) $info['site_count'] . '/' . (int) $info['license_limit'] . ' (' . esc_html(( $left === 'unlimited' ) ? __('unlimited', 'fakturo') : (int) $left) . ' ' . esc_html__('left', 'fakturo') . ')<br>'
					. '<strong>' . esc_html__('Expires on', 'fakturo') . ':</strong> <code>' . esc_html(( $expires === 'lifetime' ) ? __('Lifetime', 'fakturo') : $expires) . '</code><br>'
					. '<strong>' . esc_html__('Version', 'fakturo') . ':</strong> ' . esc_html($args['api_data']['version']) . '<br>'
					. '<strong>' . esc_html__('Registered to', 'fakturo') . ':</strong> ' . esc_html($info['customer_name']) . ' (<code>' . esc_html($info['customer_email']) . '</code>)'
					. '<div class="fktr-license-links">'
					. '<a href="' . esc_url(self::account_url($info)) . '" target="_blank" rel="noopener">' . esc_html__('Manage Sites', 'fakturo') . '</a> &ndash; '
					. '<a href="' . esc_url(self::account_url($info, 'upgrades')) . '" target="_blank" rel="noopener">' . esc_html__('View Upgrades', 'fakturo') . '</a>'
					. ( $expires === 'lifetime' ? '' : ' &ndash; <a href="' . esc_url($extend) . '" target="_blank" rel="noopener">' . esc_html__('Extend', 'fakturo') . '</a>' )
					. '</div></div>';
		}
		return $html . self::render_help($status);
	}

	/**
	 * Where to find or get a key, for a blank key and for one the store does not accept.
	 */
	private static function render_help($status) {
		if ($status === 'valid') {
			return '';
		}
		$licenses	 = '<a href="' . esc_url(self::LICENSES_URL) . '" target="_blank" rel="noopener">' . esc_html__('My Licenses', 'fakturo') . '</a>';
		$addons		 = '<a href="' . esc_url(admin_url('plugins.php?page=fakturo')) . '">' . esc_html__('Fakturo add-ons', 'fakturo') . '</a>';
		if ($status === '') {
			$text = sprintf(
				/* translators: 1: link to the licenses page of the account, 2: link to the add-ons list. */
				esc_html__('Paste the license key from your purchase email. Bought it but cannot find the email? Your key is always in your account: %1$s (log in with the email you used to buy). Not a customer yet? See the %2$s.', 'fakturo'),
				$licenses,
				$addons
			);
		} elseif ($status === 'expired') {
			$text = sprintf(
				/* translators: %s: link to the licenses page of the account. */
				esc_html__('This license has expired. Renew it to keep receiving updates and support, or check your other keys in %s.', 'fakturo'),
				$licenses
			);
		} elseif (in_array($status, array('inactive', 'site_inactive', 'deactivated'), true)) {
			$text = esc_html__('The key is right but this site is not activated with it. Use Activate License to receive updates and support.', 'fakturo');
		} else {
			$text = sprintf(
				/* translators: %s: link to the licenses page of the account. */
				esc_html__('The store does not accept this key. Check that it was copied whole, without spaces, or copy it again from %s.', 'fakturo'),
				$licenses
			);
		}
		return '<p class="fktr-license-help">' . $text . '</p>';
	}

	/**
	 * Page of the customer's account for this license: its sites or its upgrades.
	 */
	private static function account_url($info, $view = '') {
		$args = array('action' => 'manage_licenses', 'payment_id' => $info['payment_id']);
		if ($view !== '') {
			$args = array('view' => $view) + $args;
		}
		if (!empty($info['license_id'])) {
			$args['license_id'] = $info['license_id'];
		}
		return add_query_arg($args, self::ACCOUNT_URL);
	}

	private static function extend_url($license, $args) {
		$query = array('edd_license_key' => $license);
		if (!empty($args['api_data']['item_id'])) {
			$query['download_id'] = $args['api_data']['item_id'];
		}
		return add_query_arg($query, self::CHECKOUT_URL);
	}

	public static function license_page() {
		if (!self::allowed()) {
			wp_die(esc_html__('You are not allowed to do this.', 'fakturo'));
		}
		$plugins_args	 = apply_filters('fktr_plugins_updater_args', array());
		$network		 = is_multisite();

		echo $network ? '<div class="wrap" id="tab_container"><h1>' : '<div id="tab_container"><br/><h1>';
		echo esc_html__('Licenses', 'fakturo') . '</h1>';
		if ($network) {
			fktrNetwork::show_notice();
			echo '<p>' . esc_html(sprintf(
				/* translators: %s: address of the network. */
				__('The license keys are activated for the whole network, with its address (%s). Updates are installed from the network admin for every site.', 'fakturo'), self::license_url()
			)) . '</p>';
		}
		if (empty($plugins_args)) {
			echo '<div class="msg"><p>' . esc_html__('This is where you would enter the license keys for one of our premium plugins, should you activate one.', 'fakturo') . '</p>';
			echo '<p>' . esc_html__('See some of the Fakturo Add-ons in the', 'fakturo') . ' <a href="' . esc_url(admin_url('plugins.php?page=fakturo')) . '">' . esc_html__('Extensions list', 'fakturo') . '</a>.</p></div></div>';
			return;
		}

		echo '<form action="' . esc_url(admin_url('admin-post.php')) . '" id="form_fktr_licenses" method="post">
				<input type="hidden" name="action" value="fktr_save_licenses"/>';
		wp_nonce_field(self::NONCE);
		echo '<div class="fktr-licenses">';
		foreach ($plugins_args as $plugin_name => $args) {
			$license = self::get_key($plugin_name);
			echo '<div class="fktr-license-card" data-plugin="' . esc_attr($plugin_name) . '">
					<h2>' . esc_html(sprintf(
						/* translators: %s: add-on name. */
						__('%s License', 'fakturo'), $args['api_data']['item_name']
					)) . '</h2>
					<div class="fktr-license-body">
						<label class="fktr-license-label" for="license_key_' . esc_attr($plugin_name) . '">' . esc_html__('License Key', 'fakturo') . '</label>
						<input id="license_key_' . esc_attr($plugin_name) . '" class="fktr-license-key" name="license_key[' . esc_attr($plugin_name) . ']" type="text" autocomplete="off" spellcheck="false" value="' . esc_attr($license ? $license : '') . '">
						<span class="description">' . esc_html__('Enter your license key and save: the site is activated for updates and support.', 'fakturo') . '</span>
						<div class="fktr-license-status">' . self::render_status($plugin_name, $args) . '</div>
						<div class="fktr-license-message" role="status"></div>
					</div>
				</div>';
		}
		echo '</div>';
		submit_button();
		echo '</form></div>';
	}

	/* ---------- Actions ---------- */

	private static function done($message, $error = false) {
		if (is_multisite()) {
			fktrNetwork::notice($message, $error);
		} elseif (class_exists('fktrNotices')) {
			fktrNotices::add(array('text' => esc_html($message), 'error' => $error, 'screen' => self::SCREEN));
		}
		wp_safe_redirect(self::page_url());
		exit;
	}

	public static function save_licenses() {
		if (!self::allowed() || !isset($_POST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), self::NONCE)) {
			wp_die(esc_html__('You are not allowed to do this.', 'fakturo'));
		}
		$posted	 = ( isset($_POST['license_key']) && is_array($_POST['license_key']) ) ? wp_unslash($_POST['license_key']) : array();
		$keys	 = self::get_map('fktr_license_keys');
		$msg	 = '';
		$error	 = false;
		foreach (self::plugin_names() as $plugin_name) {
			if (!isset($posted[$plugin_name]) || !is_string($posted[$plugin_name])) {
				continue;
			}
			$new = trim(sanitize_text_field($posted[$plugin_name]));
			$old = isset($keys[$plugin_name]) ? (string) $keys[$plugin_name] : '';
			if ($new === $old && $old !== '') {
				continue;
			}
			if ($old !== '') {
				self::refresh($plugin_name, 'deactivate_license', $old);
			}
			if ($new === '') {
				unset($keys[$plugin_name]);
				self::forget($plugin_name);
				continue;
			}
			$keys[$plugin_name] = $new;
			self::set_map('fktr_license_keys', $keys);
			$data	 = self::refresh($plugin_name, 'activate_license', $new);
			$msg	 = self::activation_message($data);
			$error	 = !( $data && isset($data->license) && $data->license === 'valid' );
		}
		self::set_map('fktr_license_keys', $keys);
		self::done($msg !== '' ? $msg : __('License keys saved.', 'fakturo'), $error);
	}

	private static function activation_message($data) {
		if (!$data) {
			return __('The license server did not answer. Save again in a moment.', 'fakturo');
		}
		if (!empty($data->license) && $data->license === 'valid') {
			return is_multisite() ? __('License activated for this network!', 'fakturo') : __('License activated for this site!', 'fakturo');
		}
		$errors = array(
			'expired'				 => __('This license has expired.', 'fakturo'),
			'disabled'				 => __('This license has been disabled.', 'fakturo'),
			'missing'				 => __('This license key does not exist.', 'fakturo'),
			'invalid'				 => __('This license key does not exist.', 'fakturo'),
			'no_activations_left'	 => __('This license has no activations left.', 'fakturo'),
			'item_name_mismatch'	 => __('This license key is for another product.', 'fakturo'),
			'invalid_item_id'		 => __('This license key is for another product.', 'fakturo'),
		);
		$error = ( isset($data->error) && is_string($data->error) ) ? $data->error : '';
		return isset($errors[$error]) ? $errors[$error] : __('The license could not be activated.', 'fakturo');
	}

	private static function forget($plugin_name) {
		foreach (array('fktr_license_status', 'fktr_license_info') as $option) {
			$map = self::get_map($option);
			unset($map[$plugin_name]);
			self::set_map($option, $map, false);
		}
	}

	/**
	 * Check, activate or deactivate from the screen's buttons; answers the refreshed status block.
	 */
	public static function ajax_license() {
		if (!self::allowed() || !check_ajax_referer(self::NONCE, 'nonce', false)) {
			wp_send_json_error(array('message' => __('You are not allowed to do this.', 'fakturo')), 403);
		}
		$do				 = isset($_POST['do']) ? sanitize_key(wp_unslash($_POST['do'])) : '';
		$plugin_name	 = isset($_POST['plugin']) ? sanitize_text_field(wp_unslash($_POST['plugin'])) : '';
		$plugins_args	 = apply_filters('fktr_plugins_updater_args', array());
		if (!in_array($do, array('check_license', 'activate_license', 'deactivate_license'), true) || empty($plugins_args[$plugin_name])) {
			wp_send_json_error(array('message' => __('Invalid request.', 'fakturo')), 400);
		}
		if (!self::get_key($plugin_name)) {
			wp_send_json_error(array('message' => __('Save a license key first.', 'fakturo')));
		}
		$data = self::refresh($plugin_name, $do);
		if (!$data) {
			wp_send_json_error(array('message' => __('The license server did not answer. Try again in a moment.', 'fakturo')));
		}
		wp_send_json_success(array(
			'html'		 => self::render_status($plugin_name, $plugins_args[$plugin_name]),
			'message'	 => ( $do === 'activate_license' ) ? self::activation_message($data) : '',
		));
	}

}

fktr_licenses_handlers::hooks();
