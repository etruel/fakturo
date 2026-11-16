<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Read-modify-write on a shared value (last numbers, stock, client balance) without losing the update of a request that runs at the same time.
 * run() holds a MySQL named lock while the callback reads the value fresh, changes it and writes it back; acquire() and release() hold it across several steps. If the lock cannot be had in $wait seconds the callback runs anyway, as it did before.
 * The lock lives in the database connection, so it works on any host and with any object cache; the name carries the database and the table prefix, so each site of a network has its own.
 */
if (!class_exists('fktr_atomic')) :
class fktr_atomic {

	const WAIT = 10;

	private static $held = array();
	private static $shutdown = false;

	private static function lock_name($name) {
		global $wpdb;
		return 'fktr_' . md5(DB_NAME . $wpdb->prefix . $name);
	}

	/**
	 * Takes the lock $name and keeps it until release() or the end of the request, for a check that must stay true until the data is saved (e.g. an invoice number free until the invoice is published). True when this request holds it.
	 * Up to MySQL 5.6 a connection holds one lock at a time, so asking for a second one frees the first.
	 */
	public static function acquire($name, $wait = self::WAIT) {
		global $wpdb;
		$name = (string) $name;
		if (!empty(self::$held[$name])) {
			return true;
		}
		$got = (int) $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, %d)', self::lock_name($name), max(1, (int) $wait)));
		if ($got) {
			self::$held[$name] = true;
			if (!self::$shutdown) {
				self::$shutdown = true;
				add_action('shutdown', array(__CLASS__, 'release_all'));
			}
		}
		return (bool) $got;
	}

	public static function release($name) {
		global $wpdb;
		$name = (string) $name;
		if (!empty(self::$held[$name])) {
			unset(self::$held[$name]);
			$wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', self::lock_name($name)));
		}
	}

	public static function release_all() {
		foreach (array_keys(self::$held) as $name) {
			self::release($name);
		}
	}

	/**
	 * Calls $callback while holding the lock $name and returns what it returns. Asking again for a lock this request already holds just calls it.
	 */
	public static function run($name, $callback, $wait = self::WAIT) {
		$name = (string) $name;
		if (!empty(self::$held[$name])) {
			return call_user_func($callback);
		}
		$got = self::acquire($name, $wait);
		try {
			return call_user_func($callback);
		} finally {
			if ($got) {
				self::release($name);
			}
		}
	}

	/**
	 * An option as the database has it now, not as this request or the object cache remembers it.
	 */
	public static function option($name, $default = false) {
		wp_cache_delete('alloptions', 'options');
		wp_cache_delete('notoptions', 'options');
		wp_cache_delete($name, 'options');
		return get_option($name, $default);
	}

	/**
	 * Forgets the cached meta of a post so the next read comes from the database.
	 */
	public static function forget_meta($post_id) {
		wp_cache_delete((int) $post_id, 'post_meta');
	}

	public static function forget_user_meta($user_id) {
		wp_cache_delete((int) $user_id, 'user_meta');
	}
}
endif;
