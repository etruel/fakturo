<?php

// Exit if accessed directly
if (!defined('ABSPATH'))  {
	exit;
}

if ( ! class_exists( 'fktrRedirects' ) ) :

class fktrRedirects {
	
	public static $option_redirects = 'fakturo_redirects';
	public static function hooks() {
		add_action('admin_init', array(__CLASS__, 'redirects'), 12);
	}
	public static function add($new_redirect) {
		$default_redirect = array(
			'screen' => 'all',
			'user_id'=> get_current_user_id(),
			'url'=> admin_url(),
		);
		$new_redirect = wp_parse_args($new_redirect, $default_redirect);
		$redirects = get_option(self::$option_redirects, array());
		$redirects[] = $new_redirect;
		update_option(self::$option_redirects, $redirects);
	}

	public static function redirects() {
		$screen = get_current_screen();
		$redirects = get_option(self::$option_redirects, array());
		if (!empty($redirects)) {
			foreach($redirects as $key => $redirect) {
				if($redirect['user_id'] == get_current_user_id()) {
					if ($redirect['screen'] == 'all' || $redirect['screen'] == $screen->id) {
						unset($redirects[$key]);
						update_option(self::$option_redirects, $redirects);
						wp_safe_redirect($redirect['url']);
						exit;
					}	
				}
			}
			
		}
		return true;
	}
}

endif; // End if class_exists check
fktrRedirects::hooks();

?>
