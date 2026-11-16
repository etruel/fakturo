<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Read-modify-write on a shared value (last numbers, stock, client balance) without losing the update of a request that runs at the same time.
 * run() holds a MySQL named lock while the callback reads the value fresh, changes it and writes it back. If the lock cannot be had in $wait seconds the callback runs anyway, as it did before.
 * The lock lives in the database connection, so it works on any host and with any object cache; the name carries the database and the table prefix, so each site of a network has its own.
 */
if (!class_exists('fktr_atomic')) :
class fktr_atomic {

	const WAIT = 10;

	private static $held = array();

	/**
	 * Calls $callback while holding the lock $name and returns what it returns. Asking again for a lock this request already holds just calls it.
	 */
	public static function run($name, $callback, $wait = self::WAIT) {
		global $wpdb;
		$name = (string) $name;
		if (!empty(self::$held[$name])) {
			return call_user_func($callback);
		}
		$lock	 = 'fktr_' . md5(DB_NAME . $wpdb->prefix . $name);
		$got	 = (int) $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, %d)', $lock, max(1, (int) $wait)));
		if ($got) {
			self::$held[$name] = true;
		}
		try {
			return call_user_func($callback);
		} finally {
			if ($got) {
				unset(self::$held[$name]);
				$wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $lock));
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
}
endif;
