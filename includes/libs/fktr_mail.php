<?php
if ( ! class_exists('fktr_mail') ) :
	class fktr_mail {
		public static $sending = false;
		public static $attachments = array();
		public static function hooks() {
			add_action( 'phpmailer_init', array(__CLASS__, 'phpmailer_init') ,10 ,1 ); 
			add_action('admin_post_send_invoice_to_client', array(__CLASS__, 'send_invoice_2_client'));
			add_action('admin_post_send_receipt_to_client', array(__CLASS__, 'send_receipt_2_client'));
		}
		public static function send_invoice_2_client() {
			self::$sending = __FUNCTION__;
			

			if (!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], 'send_invoice_to_client')) {
				fktrNotices::add(__('WP-NONCE violation or expired.', 'fakturo'));
				wp_redirect(admin_url('edit.php?post_type=fktr_sale'));
				exit;
			}
			if (empty($_REQUEST['id'])) {
				fktrNotices::add(__('A problem has been occurred on trying send the email.', 'fakturo'));
				wp_redirect(admin_url('edit.php?post_type=fktr_sale'));
				exit;
			}
			if (!is_numeric($_REQUEST['id'])) {
				fktrNotices::add(__('A problem has been occurred on trying send the email.', 'fakturo'));
				wp_redirect(admin_url('edit.php?post_type=fktr_sale'));
				exit;
			}

			self::send_sale_invoice_pdf_to_client($_REQUEST['id']);
			wp_redirect(admin_url('edit.php?post_type=fktr_sale'));
			exit;

		}
		public static function send_sale_invoice_pdf_to_client($id, $redirect = true, $notices = true) {
			$sale_data = fktrPostTypeSales::get_sale_data($id);
			$client_data = fktrPostTypeClients::get_client_data($sale_data['client_id']);
			if ($sale_data['post_status'] != 'publish') {
				if ($notices) {
					fktrNotices::add(__('Invoice not completed or incorrect status.', 'fakturo'));
				}
				if ($redirect) {
					wp_redirect(admin_url('edit.php?post_type=fktr_sale'));
					exit;
				}
				return false;
			}
			if (empty($client_data['email'])) {
				if ($notices) {
					fktrNotices::add(__('The client does not have email.', 'fakturo'));
				}
				if ($redirect) {
					wp_redirect(admin_url('edit.php?post_type=fktr_sale'));
					exit;
				}
				return false;
			}
			if (!is_email($client_data['email'])) {
				if ($notices) {
					fktrNotices::add(__('The E-mail client is a format incorrect.', 'fakturo'));
				}
				if ($redirect) {
					wp_redirect(admin_url('edit.php?post_type=fktr_sale'));
					exit;
				}
				return false;
			}
			$object = new stdClass();
			$object->type = 'post';
			$object->id = $id;
			$object->assgined = 'fktr_sale';
			$id_email_template = fktrPostTypeEmailTemplates::get_id_by_assigned($object->assgined);
			if ($id_email_template) {
				$email_template = fktrPostTypeEmailTemplates::get_email_template_data($id_email_template);
			} else {
				if ($notices) {
					fktrNotices::add(__('No email template assigned to sales invoices', 'fakturo' ));
				}
				if ($redirect) {
					wp_redirect(admin_url('edit.php?post_type=fktr_sale'));
					exit;
				}
				return false;
			}
			$subject = '';
      		$mailbody = '';
      		$attachments = array();
			$tpl = new fktr_tpl;
			$tpl = apply_filters('fktr_email_template_assignment', $tpl, $object, false);
			$mailbody = $tpl->fromString($email_template['content']);
			$subject = $tpl->fromString($email_template['subject']);
			$headers = array();
      		$headers[] = 'Content-Type: '. apply_filters('fktr_mail_content_type', 'text/html', self::$sending) .'; charset='. apply_filters('fktr_mail_charset', 'UTF-8', self::$sending);
 
			$id_print_template = fktrPostTypePrintTemplates::get_id_by_assigned($object->assgined);
			if ($id_print_template) {
				$print_template = fktrPostTypePrintTemplates::get_print_template_data($id_print_template);
			} else {
				if ($notices) {
					fktrNotices::add(__('No print template assigned to sales invoices', 'fakturo'));
				}
				if ($redirect) {
					wp_redirect(admin_url('edit.php?post_type=fktr_sale'));
					exit;
				}
				return false;
			}
			$tpl_print = new fktr_tpl;
			$tpl_print = apply_filters('fktr_print_template_assignment', $tpl_print, $object, false);
			$html = $tpl_print->fromString($print_template['content']);
			
			$pdf = fktr_pdf::getInstance();
			

			try {
				$pdf ->set_paper("A4", "portrait");
				$pdf ->load_html(utf8_decode($html));
				$pdf ->render();
			} catch (Exception $e) {
				if ($notices) {
					fktrNotices::add(__('A problem to generate the pdf.', 'fakturo'));
				}
				if ($redirect) {
					wp_redirect(admin_url('edit.php?post_type=fktr_sale'));
					exit;
				}
				return false;
			}

			
			$new_attachment = new stdClass();
			$new_attachment->content = $pdf->output();
			$new_attachment->basename = 'invoice_'.$id.'.pdf';
			self::$attachments[] = $new_attachment;
			self::$attachments = apply_filters('fktr_attachments_'.self::$sending, self::$attachments);
			
			$sent = wp_mail($client_data['email'], $subject, $mailbody, $headers, $attachments );
			if (!$sent) {
				if ($notices) {
					fktrNotices::add(sprintf(__('A problem has been occurred on trying send the email to %s.', 'fakturo'), $client_data['email']));
				}
				
				if ($redirect) {
					wp_redirect(admin_url('edit.php?post_type=fktr_sale'));
					exit;
				}
				return false;
			}
			if ($notices) {
				fktrNotices::add(sprintf(__('The email has been sent successfully to %s.', 'fakturo'), $client_data['email']));
			}
		}
		public static function send_receipt_2_client() {
			self::$sending = __FUNCTION__;
			

			if (!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], 'send_receipt_to_client')) {
				fktrNotices::add(__('WP-NONCE violation or expired.', 'fakturo'));
				wp_redirect(admin_url('edit.php?post_type=fktr_receipt'));
				exit;
			}
			if (empty($_REQUEST['id'])) {
				fktrNotices::add(__('A problem has been occurred on trying send the email.', 'fakturo'));
				wp_redirect(admin_url('edit.php?post_type=fktr_receipt'));
				exit;
			}
			if (!is_numeric($_REQUEST['id'])) {
				fktrNotices::add(__('A problem has been occurred on trying send the email.', 'fakturo'));
				wp_redirect(admin_url('edit.php?post_type=fktr_receipt'));
				exit;
			}

			self::send_receipt_pdf_to_client($_REQUEST['id']);
			wp_redirect(admin_url('edit.php?post_type=fktr_receipt'));
			exit;

		}
		public static function send_receipt_pdf_to_client($id, $redirect = true) {
			$receipt_data = fktrPostTypeReceipts::get_receipt_data($id);
			
			$client_data = fktrPostTypeClients::get_client_data($receipt_data['client_id']);
			
			if (empty($client_data['email'])) {
				fktrNotices::add(__('The client does not have email.', 'fakturo'));
				if ($redirect) {
					wp_redirect(admin_url('edit.php?post_type=fktr_receipt'));
					exit;
				}
				return false;
			}
			if (!is_email($client_data['email'])) {
				fktrNotices::add(__('The E-mail client is a format incorrect.', 'fakturo'));
				if ($redirect) {
					wp_redirect(admin_url('edit.php?post_type=fktr_receipt'));
					exit;
				}
				return false;
			}
			$object = new stdClass();
			$object->type = 'post';
			$object->id = $id;
			$object->assgined = 'fktr_receipt';
			$id_email_template = fktrPostTypeEmailTemplates::get_id_by_assigned($object->assgined);
			if ($id_email_template) {
				$email_template = fktrPostTypeEmailTemplates::get_email_template_data($id_email_template);
			} else {
				fktrNotices::add(__('No email template assigned to receipts', 'fakturo' ));
				if ($redirect) {
					wp_redirect(admin_url('edit.php?post_type=fktr_receipt'));
					exit;
				}
				return false;
			}
			$subject = '';
      		$mailbody = '';
      		$attachments = array();
			$tpl = new fktr_tpl;
			$tpl = apply_filters('fktr_email_template_assignment', $tpl, $object, false);
			$mailbody = $tpl->fromString($email_template['content']);
			$subject = $tpl->fromString($email_template['subject']);
			$headers = array();
      		$headers[] = 'Content-Type: '. apply_filters('fktr_mail_content_type', 'text/html', self::$sending) .'; charset='. apply_filters('fktr_mail_charset', 'UTF-8', self::$sending);
 
			$id_print_template = fktrPostTypePrintTemplates::get_id_by_assigned($object->assgined);
			if ($id_print_template) {
				$print_template = fktrPostTypePrintTemplates::get_print_template_data($id_print_template);
			} else {
				fktrNotices::add(__('No print template assigned to receipts', 'fakturo'));
				if ($redirect) {
					wp_redirect(admin_url('edit.php?post_type=fktr_receipt'));
					exit;
				}
				return false;
			}
			$tpl_print = new fktr_tpl;
			$tpl_print = apply_filters('fktr_print_template_assignment', $tpl_print, $object, false);
			$html = $tpl_print->fromString($print_template['content']);
			$pdf = fktr_pdf::getInstance();
			try {
				$pdf ->set_paper("A4", "portrait");
				$pdf ->load_html(utf8_decode($html));
				$pdf ->render();
			} catch (Exception $e) {
				fktrNotices::add(__('A problem to generate the pdf.', 'fakturo'));
				if ($redirect) {
					wp_redirect(admin_url('edit.php?post_type=fktr_receipt'));
					exit;
				}
				return false;
			}
			
			
			
			$new_attachment = new stdClass();
			$new_attachment->content = $pdf->output();
			$new_attachment->basename = 'receipt_'.$id.'.pdf';
			self::$attachments[] = $new_attachment;
			self::$attachments = apply_filters('fktr_attachments_'.self::$sending, self::$attachments);
			
			$sent = wp_mail($client_data['email'], $subject, $mailbody, $headers, $attachments );
			if (!$sent) {
				fktrNotices::add(sprintf(__('A problem has been occurred on trying send the email to %s.', 'fakturo'), $client_data['email']));
				if ($redirect) {
					wp_redirect(admin_url('edit.php?post_type=fktr_receipt'));
					exit;
				}
				return false;
			}
			fktrNotices::add(sprintf(__('The email has been sent successfully to %s.', 'fakturo'), $client_data['email']));
		}
		public static function phpmailer_init($mail) {
			if (!self::$sending) {
				return true;
			}
			do_action('fktr_mail_phpmailer_init_'.self::$sending, $mail);
			$mail = self::add_attachments($mail);
			do_action('fktr_mail_phpmailer_after_attachments', $mail);
			do_action('fktr_mail_phpmailer_after_attach_'.self::$sending, $mail);
		}
		public static function add_attachments($mail) {
			foreach (self::$attachments as $att) {
				$mail->addStringAttachment($att->content , $att->basename, 'base64' );
			}
			return $mail;
		}
	}
endif;
fktr_mail::hooks();
?>