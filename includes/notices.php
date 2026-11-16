<?php

// Exit if accessed directly
if (!defined('ABSPATH'))  {
	exit;
}

if ( ! class_exists( 'fktrNotices' ) ) :

class fktrNotices {
	
	// Legacy: one option with the notices of every user. They now live in the meta of each user, see meta_key().
	public static $option_notices = 'fakturo_notices';
	// Notices a user keeps waiting; the oldest go first.
	const MAX = 50;

	public static function hooks() {
		add_action( 'admin_notices', array(__CLASS__, 'show'));
	}

	/**
	 * The notices wait in the meta of the user they are for, one list per site (the key carries the table prefix), so users and sites never share a list.
	 */
	public static function meta_key() {
		global $wpdb;
		return $wpdb->get_blog_prefix() . 'fakturo_notices';
	}

	private static function read($user_id) {
		fktr_atomic::forget_user_meta($user_id);
		$list = get_user_meta($user_id, self::meta_key(), true);
		return is_array($list) ? $list : array();
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
		$adm_notice['user_ID'] = (int) ((!isset($new_notice['user_ID'])) ? get_current_user_id() : $new_notice['user_ID']);
		$adm_notice['id'] = wp_generate_uuid4();
		if (!$adm_notice['user_ID']) {
			return;
		}
		
		fktr_atomic::run('notices_' . $adm_notice['user_ID'], function () use ($adm_notice) {
			$notices = self::read($adm_notice['user_ID']);
			$notices[] = $adm_notice;
			update_user_meta($adm_notice['user_ID'], self::meta_key(), array_slice($notices, -self::MAX));
		});
	}

	public static function show() {
		$screen = get_current_screen();
		$user_id = get_current_user_id();

		self::settings_notices();

		if (false !== get_option(self::$option_notices)) {
			delete_option(self::$option_notices);
		}
		$notice = $user_id ? get_user_meta($user_id, self::meta_key(), true) : array();

		$admin_message = '';
		$shown = array();
		if (!empty($notice) && is_array($notice)) {
			foreach($notice as $mess) {
				if ($mess['screen'] == 'all' || $mess['screen'] == $screen->id) {
					$class = ($mess['error']) ? "notice notice-error" : "notice notice-success";
					$class .= ($mess['is-dismissible']) ? " is-dismissible" : "";
					$class .= ($mess['below-h2']) ? " below-h2" : "";
					$admin_message .= '<div id="message" class="'.$class.'"><p>'.$mess['text'].'</p></div>';
					$shown[] = $mess['id'];
				}
			}
			if ($shown) {
				// Only the notices that were printed are removed; one added meanwhile stays.
				fktr_atomic::run('notices_' . $user_id, function () use ($user_id, $shown) {
					$left = array();
					foreach (self::read($user_id) as $mess) {
						if (!in_array($mess['id'], $shown, true)) {
							$left[] = $mess;
						}
					}
					update_user_meta($user_id, self::meta_key(), $left);
				});
			}
		}
		
		echo $admin_message;
	}
	public static function settings_notices() {
		
		if (class_exists('fktrNetwork') && fktrNetwork::hidden_here()) {
			return false;
		}
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
