<?php

// Exit if accessed directly
if (!defined('ABSPATH'))  {
	exit;
}

if ( ! class_exists( 'fktrRedirects' ) ) :

class fktrRedirects {
	
	// Legacy: one option with the redirects of every user. They now live in the meta of each user, see meta_key().
	public static $option_redirects = 'fakturo_redirects';
	public static function hooks() {
		// The screen of the page exists from current_screen on (not yet in admin_init); AJAX and admin-post requests have none, so they are never redirected.
		add_action('current_screen', array(__CLASS__, 'redirects'), 12);
	}

	public static function meta_key() {
		global $wpdb;
		return $wpdb->get_blog_prefix() . 'fakturo_redirects';
	}

	private static function read($user_id) {
		fktr_atomic::forget_user_meta($user_id);
		$list = get_user_meta($user_id, self::meta_key(), true);
		return is_array($list) ? $list : array();
	}

	public static function add($new_redirect) {
		$default_redirect = array(
			'screen' => 'all',
			'user_id'=> get_current_user_id(),
			'url'=> admin_url(),
		);
		$new_redirect = wp_parse_args($new_redirect, $default_redirect);
		$new_redirect['user_id'] = (int) $new_redirect['user_id'];
		$new_redirect['id'] = wp_generate_uuid4();
		if (!$new_redirect['user_id']) {
			return;
		}
		fktr_atomic::run('redirects_' . $new_redirect['user_id'], function () use ($new_redirect) {
			$redirects = self::read($new_redirect['user_id']);
			$redirects[] = $new_redirect;
			update_user_meta($new_redirect['user_id'], self::meta_key(), array_slice($redirects, -20));
		});
	}

	public static function redirects($screen = null) {
		$user_id = get_current_user_id();
		// A form being saved is not interrupted.
		if (!$user_id || !$screen || (isset($_SERVER['REQUEST_METHOD']) && 'POST' === $_SERVER['REQUEST_METHOD'])) {
			return true;
		}
		if (false !== get_option(self::$option_redirects)) {
			delete_option(self::$option_redirects);
		}
		$redirects = get_user_meta($user_id, self::meta_key(), true);
		if (empty($redirects) || !is_array($redirects)) {
			return true;
		}
		foreach($redirects as $redirect) {
			if ($redirect['screen'] == 'all' || $redirect['screen'] == $screen->id) {
				fktr_atomic::run('redirects_' . $user_id, function () use ($user_id, $redirect) {
					$left = array();
					foreach (self::read($user_id) as $item) {
						if ($item['id'] !== $redirect['id']) {
							$left[] = $item;
						}
					}
					update_user_meta($user_id, self::meta_key(), $left);
				});
				wp_safe_redirect($redirect['url']);
				exit;
			}
		}
		return true;
	}
}

endif; // End if class_exists check
fktrRedirects::hooks();

?>
