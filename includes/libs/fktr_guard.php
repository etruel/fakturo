<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

/**
 * One place to protect the AJAX and admin-post handlers: every handler calls it first.
 * ajax(): AJAX calls carry the nonce in `fktr_nonce` (printed in every admin page as fktr_ajax_nonce).
 * item(): links to one post carry a nonce tied to the action and the post id, see url().
 */
if (!class_exists('fktr_guard')) :
class fktr_guard {

	const AJAX_NONCE = 'fktr_ajax';
	const AJAX_FIELD = 'fktr_nonce';

	public static function hooks() {
		add_action('admin_enqueue_scripts', array(__CLASS__, 'print_ajax_nonce'));
	}

	public static function ajax_nonce() {
		return wp_create_nonce(self::AJAX_NONCE);
	}

	public static function print_ajax_nonce() {
		wp_add_inline_script('jquery-core', 'var fktr_ajax_nonce = ' . wp_json_encode(self::ajax_nonce()) . ';');
	}

	/**
	 * True when the current user has any of the capabilities.
	 */
	public static function can($caps) {
		foreach ((array) $caps as $cap) {
			if (current_user_can($cap)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * AJAX handlers: ends the request with a 403 unless the call has its nonce and the user has one of the capabilities.
	 */
	public static function ajax($caps) {
		if (!check_ajax_referer(self::AJAX_NONCE, self::AJAX_FIELD, false) || !self::can($caps)) {
			wp_send_json_error(array('message' => __('Sorry, you are not allowed to do that.', 'fakturo')), 403);
		}
	}

	/**
	 * Links to one post: the nonce belongs to the action and the post id. Returns the post of the given type that the user can edit.
	 */
	public static function item($action, $type, $cap = 'edit_post', $id_key = 'id') {
		$id = isset($_REQUEST[$id_key]) ? absint($_REQUEST[$id_key]) : 0;
		$nonce = isset($_REQUEST['_wpnonce']) ? sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'])) : '';
		if (!$id || !wp_verify_nonce($nonce, $action . '_' . $id)) {
			wp_die(__('The link has expired. Go back and try again.', 'fakturo'), '', 403);
		}
		$post = get_post($id);
		if (!$post || $post->post_type !== $type || !current_user_can($cap, $id)) {
			wp_die(__('Sorry, you are not allowed to do that.', 'fakturo'), '', 403);
		}
		return $post;
	}

	/**
	 * Link that item() accepts. The result is ready for an href.
	 */
	public static function url($action, $id, $args = array(), $page = 'admin-post.php', $id_key = 'id') {
		$args = array_merge(array('action' => $action, $id_key => $id), $args);
		return wp_nonce_url(add_query_arg($args, admin_url($page)), $action . '_' . $id);
	}

	/**
	 * A numeric id from the request that must be a post of the given type; 0 otherwise.
	 */
	public static function post_id($value, $type) {
		$id = is_scalar($value) ? absint($value) : 0;
		return ($id && get_post_type($id) === $type) ? $id : 0;
	}
}
fktr_guard::hooks();
endif;
