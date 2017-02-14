<?php

// Exit if accessed directly
if (!defined('ABSPATH'))  {
	exit;
}

if ( ! class_exists( 'fktrNotices' ) ) :

class fktrNotices {
	
	public static $option_notices = 'fakturo_notices';
	public static function hooks() {
		add_action( 'admin_notices', array(__CLASS__, 'show'));
	}
	public static function add($new_notice) {
		if(is_string($new_notice)) {
			$adm_notice['text'] = $new_notice;
		} else {
			$adm_notice['text'] = (!isset($new_notice['text'])) ? '' : $new_notice['text'];
		}
		$adm_notice['screen'] = (!isset($new_notice['screen'])) ? 'all' : $new_notice['screen']; 
		$adm_notice['error'] = (!isset($new_notice['error'])) ? false : $new_notice['error'];
		$adm_notice['below-h2'] = (!isset($new_notice['below-h2'])) ? true : $new_notice['below-h2'];
		$adm_notice['is-dismissible'] = (!isset($new_notice['is-dismissible'])) ? true : $new_notice['is-dismissible'];
		$adm_notice['user_ID'] = (!isset($new_notice['user_ID'])) ? get_current_user_id() : $new_notice['user_ID'];
		
		$notice = get_option(self::$option_notices, array());
		$notice[] = $adm_notice;
		update_option(self::$option_notices, $notice);
	}

	public static function show() {
		$screen = get_current_screen();
		$notice = get_option(self::$option_notices, array());

		self::settings_notices();


		$admin_message = '';
		if (!empty($notice)) {
			foreach($notice as $key => $mess) {
				if($mess['user_ID'] == get_current_user_id()) {
					if ($mess['screen'] == 'all' || $mess['screen'] == $screen->id) {
						$class = ($mess['error']) ? "notice notice-error" : "notice notice-success";
						$class .= ($mess['is-dismissible']) ? " is-dismissible" : "";
						$class .= ($mess['below-h2']) ? " below-h2" : "";
						$admin_message .= '<div id="message" class="'.$class.'"><p>'.$mess['text'].'</p></div>';
						unset( $notice[$key] );
					}	
				}
			}
			update_option(self::$option_notices, $notice);
		}
		
		echo $admin_message;
	}
	public static function settings_notices() {
		$setting_system = get_option('fakturo_system_options_group', false);
		
		if ($setting_system['currency'] <= 0) {
			echo '<div id="message" class="notice notice-warning is-dismissible"><p>'.__('Fakturo needs you to complete all system settings information for proper operation.', FAKTURO_TEXT_DOMAIN ).'</p></div>';
		} else if ($setting_system['invoice_type'] <= 0) {
			echo '<div id="message" class="notice notice-warning is-dismissible"><p>'.__('Fakturo needs you to complete all system settings information for proper operation.', FAKTURO_TEXT_DOMAIN ).'</p></div>';
		}
	}
}

endif; // End if class_exists check
fktrNotices::hooks();

?>
