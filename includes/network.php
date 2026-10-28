<?php

// Exit if accessed directly
if (!defined('ABSPATH'))  {
	exit;
}

if (!class_exists('fktrNetwork')) :
/**
 * Fakturo menu of the network admin: defaults for the sites of the network and the licenses of the add-ons.
 * It only exists in a multisite, and only where Fakturo is loaded in the network admin, which runs as the main site:
 * network-activated, or active on the main site.
 */
class fktrNetwork {
	const SLUG			 = 'fakturo-network';
	const LICENSES_SLUG	 = 'fakturo-network-licenses';
	const OPTION		 = 'fktr_network_defaults';
	const HIDE_MAIN		 = 'fktr_network_hide_main';
	const NONCE			 = 'fktr_network_defaults';

	public static function hooks() {
		if (!is_multisite()) {
			return;
		}
		add_action('network_admin_menu', array(__CLASS__, 'menu'));
		add_action('admin_post_fktr_save_network_defaults', array(__CLASS__, 'save_defaults'));
		add_filter('fktr_system_options_init', array(__CLASS__, 'apply_defaults'));
		add_action('admin_menu', array(__CLASS__, 'hide_menus'), 999);
		add_action('wp_dashboard_setup', array(__CLASS__, 'hide_dashboard_widget'), 99);
	}

	/**
	 * Whether Fakturo is kept out of sight here: the main site hosts Fakturo and its add-ons so the network admin can
	 * manage their licenses, but it may not be a site that issues invoices.
	 */
	public static function hidden_here() {
		return is_multisite() && is_main_site() && get_site_option(self::HIDE_MAIN);
	}

	/**
	 * Hides the menus of Fakturo and of its add-ons (the pages stay reachable by their address).
	 */
	public static function hide_menus() {
		if (!self::hidden_here()) {
			return;
		}
		global $menu;
		foreach ((array) $menu as $item) {
			if (isset($item[2]) && ( strpos($item[2], 'fakturo') === 0 || strpos($item[2], 'post_type=fktr_') !== false )) {
				remove_menu_page($item[2]);
			}
		}
	}

	public static function hide_dashboard_widget() {
		if (self::hidden_here()) {
			remove_meta_box('fktr_dashboard_widget_sale_summary', 'dashboard', 'normal');
		}
	}

	public static function menu() {
		add_menu_page(
			__('Fakturo', 'fakturo'),
			__('Fakturo', 'fakturo'),
			'manage_network_options',
			self::SLUG,
			array(__CLASS__, 'defaults_page'),
			'dashicons-tickets',
			26
		);
		add_submenu_page(self::SLUG, __('Global settings', 'fakturo'), __('Global settings', 'fakturo'), 'manage_network_options', self::SLUG, array(__CLASS__, 'defaults_page'));
		add_submenu_page(self::SLUG, __('Licenses', 'fakturo'), __('Licenses', 'fakturo'), 'manage_network_options', self::LICENSES_SLUG, array('fktr_licenses_handlers', 'license_page'));
	}

	public static function page_url($slug = self::SLUG) {
		return network_admin_url('admin.php?page=' . $slug);
	}

	/**
	 * Message for the next load of a network screen (the notices of the sites do not show in the network admin).
	 */
	public static function notice($text, $error = false) {
		set_site_transient('fktr_network_notice_' . get_current_user_id(), array('text' => $text, 'error' => (bool) $error), MINUTE_IN_SECONDS);
	}

	public static function show_notice() {
		$key	 = 'fktr_network_notice_' . get_current_user_id();
		$notice	 = get_site_transient($key);
		if (!is_array($notice)) {
			return;
		}
		delete_site_transient($key);
		echo '<div class="notice ' . ( $notice['error'] ? 'notice-error' : 'notice-success' ) . ' is-dismissible"><p>' . esc_html($notice['text']) . '</p></div>';
	}

	/**
	 * The values a new site starts with, and what each one is.
	 */
	public static function default_fields() {
		return array(
			'dateformat'			 => 'd/m/Y',
			'thousand'				 => ',',
			'decimal'				 => '.',
			'decimal_numbers'		 => 2,
			'currency_position'		 => 'before',
			'digits_invoice_number'	 => 8,
			'digits_receipt_number'	 => 8,
			'use_stock_product'		 => 0,
		);
	}

	public static function get_defaults() {
		$saved = get_site_option(self::OPTION, array());
		return array_merge(self::default_fields(), is_array($saved) ? array_intersect_key($saved, self::default_fields()) : array());
	}

	/**
	 * Only what the network admin saved: a site not set up yet takes it instead of the built-in value.
	 */
	public static function apply_defaults($values) {
		$saved = get_site_option(self::OPTION, array());
		if (is_array($saved) && is_array($values)) {
			$values = array_merge($values, array_intersect_key($saved, self::default_fields()));
		}
		return $values;
	}

	public static function defaults_page() {
		if (!current_user_can('manage_network_options')) {
			wp_die(esc_html__('You are not allowed to do this.', 'fakturo'));
		}
		$v		 = self::get_defaults();
		$formats = fktrSettings::date_formats();
		echo '<div class="wrap"><h1>' . esc_html__('Fakturo global settings', 'fakturo') . '</h1>';
		self::show_notice();
		echo '<form action="' . esc_url(admin_url('admin-post.php')) . '" method="post"><input type="hidden" name="action" value="fktr_save_network_defaults"/>';
		wp_nonce_field(self::NONCE);

		echo '<h2 class="title">' . esc_html__('Main site', 'fakturo') . '</h2>';
		echo '<table class="form-table" role="presentation"><tr><th scope="row">' . esc_html__('Fakturo on the main site', 'fakturo') . '</th><td><label><input name="hide_main" type="checkbox" value="1"' . checked(1, (int) get_site_option(self::HIDE_MAIN), false) . '/> ' . esc_html__('Do not show Fakturo on the main site', 'fakturo') . '</label>
				<p class="description">' . esc_html__('Hides the Fakturo menus and its widget on the main site of the network. Fakturo and its add-ons stay active there so the licenses can be managed from this network admin.', 'fakturo') . '</p></td></tr></table>';

		echo '<h2 class="title">' . esc_html__('Default values for new sites', 'fakturo') . '</h2>';
		echo '<p>' . esc_html__('The Fakturo of every new site of the network starts with these values. Sites that are already set up keep their own settings.', 'fakturo') . '</p>';
		echo '<table class="form-table" role="presentation">';

		echo '<tr><th scope="row"><label for="fktr_nd_dateformat">' . esc_html__('Default date format', 'fakturo') . '</label></th><td><select id="fktr_nd_dateformat" name="dateformat">';
		foreach ($formats as $key => $label) {
			echo '<option value="' . esc_attr($key) . '"' . selected($key, $v['dateformat'], false) . '>' . esc_html($label) . '</option>';
		}
		echo '</select></td></tr>';

		echo '<tr><th scope="row"><label for="fktr_nd_currency_position">' . esc_html__('Currency Position', 'fakturo') . '</label></th><td><select id="fktr_nd_currency_position" name="currency_position">
				<option value="before"' . selected('before', $v['currency_position'], false) . '>Before - $10</option>
				<option value="after"' . selected('after', $v['currency_position'], false) . '>After - 10$</option></select></td></tr>';

		echo '<tr><th scope="row"><label for="fktr_nd_thousand">' . esc_html__('Thousands Separator', 'fakturo') . '</label></th><td><input id="fktr_nd_thousand" name="thousand" type="text" size="5" maxlength="1" value="' . esc_attr($v['thousand']) . '"/>
				<p class="description">' . esc_html__('The symbol (usually , or .) to separate thousands', 'fakturo') . '</p></td></tr>';

		echo '<tr><th scope="row"><label for="fktr_nd_decimal">' . esc_html__('Decimal Separator', 'fakturo') . '</label></th><td><input id="fktr_nd_decimal" name="decimal" type="text" size="5" maxlength="1" value="' . esc_attr($v['decimal']) . '"/>
				<p class="description">' . esc_html__('The symbol (usually , or .) to separate decimal points', 'fakturo') . '</p></td></tr>';

		echo '<tr><th scope="row"><label for="fktr_nd_decimal_numbers">' . esc_html__('Decimal numbers', 'fakturo') . '</label></th><td><input id="fktr_nd_decimal_numbers" name="decimal_numbers" type="number" min="0" max="9" value="' . esc_attr($v['decimal_numbers']) . '"/></td></tr>';

		echo '<tr><th scope="row"><label for="fktr_nd_digits_invoice_number">' . esc_html__('Number of digits of the invoice number', 'fakturo') . '</label></th><td><input id="fktr_nd_digits_invoice_number" name="digits_invoice_number" type="number" min="2" max="20" value="' . esc_attr($v['digits_invoice_number']) . '"/></td></tr>';

		echo '<tr><th scope="row"><label for="fktr_nd_digits_receipt_number">' . esc_html__('Number of digits of the receipt number', 'fakturo') . '</label></th><td><input id="fktr_nd_digits_receipt_number" name="digits_receipt_number" type="number" min="2" max="20" value="' . esc_attr($v['digits_receipt_number']) . '"/></td></tr>';

		echo '<tr><th scope="row">' . esc_html__('Use stock for products', 'fakturo') . '</th><td><label><input name="use_stock_product" type="checkbox" value="1"' . checked(1, (int) $v['use_stock_product'], false) . '/> ' . esc_html__('Activate for use stock for products', 'fakturo') . '</label></td></tr>';

		echo '</table>';
		submit_button();
		echo '</form></div>';
	}

	public static function save_defaults() {
		if (!current_user_can('manage_network_options') || !isset($_POST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), self::NONCE)) {
			wp_die(esc_html__('You are not allowed to do this.', 'fakturo'), '', 403);
		}
		$formats = fktrSettings::date_formats();
		$post	 = wp_unslash($_POST);
		$new	 = self::default_fields();

		if (isset($post['dateformat']) && isset($formats[$post['dateformat']])) {
			$new['dateformat'] = $post['dateformat'];
		}
		if (isset($post['currency_position']) && in_array($post['currency_position'], array('before', 'after'), true)) {
			$new['currency_position'] = $post['currency_position'];
		}
		foreach (array('thousand', 'decimal') as $field) {
			if (isset($post[$field]) && is_string($post[$field]) && $post[$field] !== '') {
				$new[$field] = mb_substr(sanitize_text_field($post[$field]), 0, 1);
			}
		}
		if (isset($post['decimal_numbers'])) {
			$new['decimal_numbers'] = max(0, min(9, (int) $post['decimal_numbers']));
		}
		foreach (array('digits_invoice_number', 'digits_receipt_number') as $field) {
			if (isset($post[$field])) {
				$new[$field] = max(2, min(20, (int) $post[$field]));
			}
		}
		$new['use_stock_product'] = empty($post['use_stock_product']) ? 0 : 1;

		if ($new['thousand'] === $new['decimal']) {
			self::notice(__('The thousand and decimal separators must be different.', 'fakturo'), true);
		} else {
			update_site_option(self::OPTION, $new);
			update_site_option(self::HIDE_MAIN, empty($post['hide_main']) ? 0 : 1);
			self::notice(__('Settings saved.', 'fakturo'));
		}
		wp_safe_redirect(self::page_url());
		exit;
	}
}
endif;

fktrNetwork::hooks();
