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
		
		if (current_user_can('fktr_manage_wizard')) {
			$first_time_wizard = get_option('fktr_first_time_wizard', false);
			if (!$first_time_wizard) {
				echo '<div id="message" class="notice notice-warning is-dismissible"><p>'.__('If it is your first time in the Fakturo you can easily configure it through the wizard.', 'fakturo' ).' <a href="'.admin_url('admin-post.php?action=fktr_wizard').'" class="button button-primary">'.__('Enter Wizard', 'fakturo' ).'</a></p></div>';
				return true;
			}
		}

		$count_currencies = wp_count_terms('fktr_currencies');
		if ($count_currencies == 0) {
			echo '<div id="message" class="notice notice-warning is-dismissible"><p>'.sprintf(__('Fakturo needs <strong>%s</strong> on the <a href="%s">%s</a> for proper operation.', 'fakturo' ), __('at least one currency', 'fakturo'), admin_url('edit-tags.php?taxonomy=fktr_currencies'), __('Currencies', 'fakturo')).'</p></div>';
			return true;

		}
		$count_invoice_types = wp_count_terms('fktr_invoice_types');
		if ($count_invoice_types == 0) {
			echo '<div id="message" class="notice notice-warning is-dismissible"><p>'.sprintf(__('Fakturo needs <strong>%s</strong> on the <a href="%s">%s</a> for proper operation.', 'fakturo' ), __('at least one Invoice Type', 'fakturo'), admin_url('edit-tags.php?taxonomy=fktr_invoice_types'), __('Invoice Types', 'fakturo')).'</p></div>';
			return true;

		}
		$count_sale_points = wp_count_terms('fktr_sale_points');
		if ($count_sale_points == 0) {
			echo '<div id="message" class="notice notice-warning is-dismissible"><p>'.sprintf(__('Fakturo needs <strong>%s</strong> on the <a href="%s">%s</a> for proper operation.', 'fakturo' ), __('at least one Sale Point', 'fakturo'), admin_url('edit-tags.php?taxonomy=fktr_sale_points'), __('Sale Points', 'fakturo')).'</p></div>';
			return true;

		}

		
		$setting_system = get_option('fakturo_system_options_group', false);
		if ($setting_system['currency'] <= 0) {
			echo '<div id="message" class="notice notice-warning is-dismissible"><p>'.sprintf(__('Fakturo needs <strong>%s</strong> on the <a href="%s">%s</a> for proper operation.', 'fakturo' ), __('Default Currency', 'fakturo'), admin_url('admin.php?page=fakturo-settings-system'), __('System Settings', 'fakturo')).'</p></div>';
			return true;
		} 
		if ($setting_system['invoice_type'] <= 0) {
			echo '<div id="message" class="notice notice-warning is-dismissible"><p>'.sprintf(__('Fakturo needs <strong>%s</strong> on the <a href="%s">%s</a> for proper operation.', 'fakturo' ), __('Default Invoice Type', 'fakturo'), admin_url('admin.php?page=fakturo-settings-system'), __('System Settings', 'fakturo')).'</p></div>';
			return true;
		}
	}
}

endif; // End if class_exists check
fktrNotices::hooks();

?>
