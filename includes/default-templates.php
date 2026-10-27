<?php

// Exit if accessed directly
if (!defined('ABSPATH'))  {
	exit;
}

if (!class_exists('fktrDefaultTemplates')) :
class fktrDefaultTemplates {
	const NONCE = 'fktr_default_templates';

	public static function hooks() {
		add_action('admin_post_fktr_create_default_templates', array(__CLASS__, 'create_from_button'));
		add_action('wp_ajax_fktr_default_template', array(__CLASS__, 'ajax_default_template'));
		add_action('manage_posts_extra_tablenav', array(__CLASS__, 'list_button'));
	}

	/**
	 * Class that handles the templates of a kind.
	 */
	public static function handler($kind) {
		return $kind == 'email' ? 'fktrPostTypeEmailTemplates' : 'fktrPostTypePrintTemplates';
	}

	/**
	 * Default content (and subject for e-mails) of an object type, or false if there is none.
	 */
	public static function get_default($kind, $assigned) {
		$class = self::handler($kind);
		$content = call_user_func(array($class, 'get_default_template_by_assigned'), $assigned);
		if ($content === '') {
			return false;
		}
		$default = array('content' => $content, 'subject' => '');
		if ($kind == 'email') {
			$default['subject'] = fktrPostTypeEmailTemplates::get_default_subject($assigned);
		}
		return $default;
	}

	/**
	 * Creates a template for every object type of the "Assigned to" list.
	 * An existing template of the type is only touched when $overwrite is true or its content is empty.
	 *
	 * @return array Counts of created, updated and skipped templates.
	 */
	public static function create_all($kinds = array('print', 'email'), $overwrite = false) {
		$result = array('created' => 0, 'updated' => 0, 'skipped' => 0);
		foreach ((array) $kinds as $kind) {
			$class = self::handler($kind);
			$list = apply_filters('fktr_assigned_' . $kind . '_template', array());
			// the save_post handler of the class would store the current request as meta
			remove_action('save_post', array($class, 'save'), 99);
			foreach ($list as $assigned => $label) {
				$default = self::get_default($kind, $assigned);
				if (!$default) {
					$result['skipped']++;
					continue;
				}
				$id = call_user_func(array($class, 'get_id_by_assigned'), $assigned);
				if ($id && !$overwrite && trim((string) get_post_meta($id, 'content', true)) !== '') {
					$result['skipped']++;
					continue;
				}
				if (!$id) {
					$id = wp_insert_post(array(
						'post_type'		 => 'fktr_' . $kind . '_template',
						'post_status'	 => 'publish',
						'post_title'	 => $label,
					));
					if (!$id || is_wp_error($id)) {
						$result['skipped']++;
						continue;
					}
					update_post_meta($id, 'description', __('Default template', 'fakturo'));
					update_post_meta($id, 'assigned', $assigned);
					$result['created']++;
				} else {
					$result['updated']++;
				}
				update_post_meta($id, 'content', $default['content']);
				if ($kind == 'email') {
					update_post_meta($id, 'subject', $default['subject']);
					wp_update_post(array('ID' => $id, 'post_content' => $default['content']));
				}
			}
			add_action('save_post', array($class, 'save'), 99, 2);
		}
		return $result;
	}

	public static function create_from_button() {
		if (!current_user_can('manage_options') || !isset($_GET['_nonce']) || !wp_verify_nonce($_GET['_nonce'], self::NONCE)) {
			wp_die(__('A problem, please try again.', 'fakturo'), '', 403);
		}
		$kind = (isset($_GET['kind']) && $_GET['kind'] == 'email') ? 'email' : 'print';
		$result = self::create_all(array($kind), true);
		fktrNotices::add(array('below-h2' => false, 'text' => sprintf(
			__('Default templates: %1$d created, %2$d reset to default.', 'fakturo'),
			$result['created'],
			$result['updated']
		)));
		wp_redirect(admin_url('edit.php?post_type=fktr_' . $kind . '_template'));
		exit;
	}

	public static function list_button($which) {
		global $typenow;
		if ($which != 'top' || !in_array($typenow, array('fktr_print_template', 'fktr_email_template')) || !current_user_can('manage_options')) {
			return;
		}
		$kind = $typenow == 'fktr_email_template' ? 'email' : 'print';
		$url = wp_nonce_url(admin_url('admin-post.php?action=fktr_create_default_templates&kind=' . $kind), self::NONCE, '_nonce');
		echo '<a href="' . esc_url($url) . '" class="button" onclick="return confirm(\'' . esc_js(__('This creates one template for every object type and resets to default the ones that already exist, removing their changes. Are you sure?', 'fakturo')) . '\');">' . esc_html__('Create default templates', 'fakturo') . '</a>';
	}

	public static function ajax_default_template() {
		check_ajax_referer(self::NONCE, 'nonce');
		if (!current_user_can('manage_options')) {
			wp_send_json_error(__('A problem, please try again.', 'fakturo'));
		}
		$kind = (isset($_POST['kind']) && $_POST['kind'] == 'email') ? 'email' : 'print';
		$assigned = isset($_POST['assigned']) ? sanitize_key($_POST['assigned']) : '';
		$default = self::get_default($kind, $assigned);
		if (!$default) {
			wp_send_json_error(__('Default template not found.', 'fakturo'));
		}
		wp_send_json_success($default);
	}

	/**
	 * Variables of a print or e-mail template used as the object of another template.
	 */
	public static function template_vars($kind, $template_id) {
		$class = self::handler($kind);
		$data = call_user_func(array($class, 'get_' . $kind . '_template_data'), $template_id);
		$list = apply_filters('fktr_assigned_' . $kind . '_template', array());
		$data['post_title'] = get_the_title($template_id);
		$data['assigned_label'] = isset($list[$data['assigned']]) ? $list[$data['assigned']] : '';
		$data['content_escaped'] = esc_html(isset($data['content']) ? $data['content'] : '');
		return $data;
	}

	/**
	 * Localized values for the "Assigned to" select of the template editors.
	 */
	public static function script_strings($kind) {
		return array(
			'kind'				 => $kind,
			'nonce'				 => wp_create_nonce(self::NONCE),
			'msg_overwrite'		 => __('This replaces the current content with the default template of the selected type. Are you sure?', 'fakturo'),
			'msg_no_default'	 => __('Default template not found.', 'fakturo'),
		);
	}
}
endif;

fktrDefaultTemplates::hooks();
