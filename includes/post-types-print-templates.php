<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}


if (!class_exists('fktrPostTypePrintTemplates')) :

	class fktrPostTypePrintTemplates {

		function __construct() {

			add_action('init', array(__CLASS__, 'setup'), 1);
			add_action('fakturo_activation', array(__CLASS__, 'setup'), 1);

			add_action('transition_post_status', array(__CLASS__, 'default_fields'), 10, 3);
			add_action('save_post', array(__CLASS__, 'save'), 99, 2);

			add_action('admin_print_scripts-post-new.php', array(__CLASS__, 'scripts'), 11);
			add_action('admin_print_scripts-post.php', array(__CLASS__, 'scripts'), 11);

			add_action('admin_print_styles-post-new.php', array(__CLASS__, 'styles'));
			add_action('admin_print_styles-post.php', array(__CLASS__, 'styles'));

			add_filter('fktr_clean_print_template_fields', array(__CLASS__, 'clean_fields'), 10, 1);
			add_filter('fktr_print_template_before_save', array(__CLASS__, 'before_save'), 10, 1);

			add_filter('post_updated_messages', array(__CLASS__, 'updated_messages'));

			add_filter('fktr_assigned_print_template', array(__CLASS__, 'default_assigned'), 10, 1);
			add_filter('fktr_print_template_assignment', array(__CLASS__, 'assignment'), 10, 3);

			add_action('admin_post_show_print_template', array(__CLASS__, 'show_print_template'));
			add_action('admin_post_reset_print_template', array(__CLASS__, 'reset_print_template'));
			add_action('admin_action_copy_print_template', array(__CLASS__, 'copy_print_template'));
			add_filter('post_row_actions', array(__CLASS__, 'actions'), 10, 2);

			add_action('wp_ajax_get_vars_assigned_print', array(__CLASS__, 'get_vars_assigned'));

			add_action('post_submitbox_misc_actions', array(__CLASS__, 'submitbox'));
		}

		public static function reset_print_template() {

			if (!isset($_GET['_nonce']) || !wp_verify_nonce($_GET['_nonce'], 'reset_print_to_default')) {
				fktrNotices::add(array('below-h2' => false, 'text' => __('A problem, please try again.', 'fakturo')));
				wp_redirect(admin_url('edit.php?post_type=fktr_print_template'));
				exit;
			}

			$template_id = $_REQUEST['id'];
			if (empty($template_id)) {
				fktrNotices::add(array('below-h2' => false, 'text' => __('Invalid print template id.', 'fakturo')));
				wp_redirect(admin_url('edit.php?post_type=fktr_print_template'));
				exit;
			}

			$print_template = self::get_print_template_data($template_id);
			if (!isset($print_template['assigned'])) {
				fktrNotices::add(array('below-h2' => false, 'text' => __('This print template has no assigned object.', 'fakturo')));
				wp_redirect(admin_url('post.php?post=' . $template_id . '&action=edit'));
				exit;
			}
			if ($print_template['assigned'] == -1) {
				fktrNotices::add(array('below-h2' => false, 'text' => __('This print template has no assigned object.', 'fakturo')));
				wp_redirect(admin_url('post.php?post=' . $template_id . '&action=edit'));
				exit;
			}

			$new_content = self::get_default_template_by_assigned($print_template['assigned']);
			$new		 = apply_filters('fktr_print_template_metabox_save_content', $new_content);  //filtra cada campo antes de grabar
			update_post_meta($template_id, 'content', $new);
			fktrNotices::add(array('below-h2' => false, 'text' => __('This print template has been reset to default.', 'fakturo')));
			wp_redirect(admin_url('post.php?post=' . $template_id . '&action=edit'));
			exit;
		}

		public static function actions($actions, $post) {
			//check for your post type
			if ($post->post_type == "fktr_print_template") {

				$actions['show_print_template']	 = '<a href="' . admin_url('admin-post.php?id=' . $post->ID . '&action=show_print_template') . '" target="_new">' . __('Preview', 'fakturo') . '</a>';
				$actions['copy']				 = '<a href="' . admin_url('admin.php?action=copy_print_template&post=' . $post->ID . '') . '" title="' . esc_attr(__("Clone this item", 'fakturo')) . '">' . __('Copy', 'fakturo') . '</a>';
			}
			return $actions;
		}

		public static function copy_print_template() {
			if (!( isset($_GET['post']) || isset($_POST['post']) || ( isset($_REQUEST['action']) && 'copy_print_template' == $_REQUEST['action'] ) )) {
				wp_die(__('No print template ID has been supplied!', 'fakturo'));
			}

			// Get the original post
			$id		 = (isset($_GET['post']) ? $_GET['post'] : $_POST['post']);
			$post	 = get_post($id);

			// Copy the post and insert it
			if (isset($post) && $post != null) {
				if ($post->post_type != 'fktr_print_template') {
					return;
				}
				$prefix	 = "";
				$suffix	 = __("(Copy)", 'fakturo');
				if (!empty($prefix))
					$prefix	 .= " ";
				if (!empty($suffix))
					$suffix	 = " " . $suffix;
				$status	 = 'publish';

				$new_post = array(
					'menu_order'	 => $post->menu_order,
					'guid'			 => $post->guid,
					'comment_status' => $post->comment_status,
					'ping_status'	 => $post->ping_status,
					'pinged'		 => $post->pinged,
					'post_author'	 => @$post->author,
					'post_content'	 => $post->post_content,
					'post_excerpt'	 => $post->post_excerpt,
					'post_mime_type' => $post->post_mime_type,
					'post_parent'	 => $post->post_parent,
					'post_password'	 => $post->post_password,
					'post_status'	 => $status,
					'post_title'	 => $prefix . $post->post_title . $suffix,
					'post_type'		 => $post->post_type,
					'to_ping'		 => $post->to_ping,
					'post_date'		 => $post->post_date,
					'post_date_gmt'	 => get_gmt_from_date($post->post_date)
				);

				$new_post_id = wp_insert_post($new_post);

				$post_meta_keys = get_post_custom_keys($post->ID);
				if (!empty($post_meta_keys)) {
					foreach ($post_meta_keys as $meta_key) {
						$meta_values = get_post_custom_values($meta_key, $post->ID);
						foreach ($meta_values as $meta_value) {

							$meta_value = maybe_unserialize($meta_value);
							update_post_meta($new_post_id, $meta_key, $meta_value);
						}
					}
				}


				if ($status == '') {
					// Redirect to the post list screen
					wp_redirect(admin_url('edit.php?post_type=' . $post->post_type));
				} else {
					// Redirect to the edit screen for the new draft post
					wp_redirect(admin_url('post.php?action=edit&post=' . $new_post_id));
				}
				exit;
			} else {
				$post_type_obj = get_post_type_object($post->post_type);
				wp_die(esc_attr(__('Copy print template failed, could not find original:', 'fakturo')) . ' ' . $id);
			}
		}

		public static function assignment($tpl, $object, $default_template) {
			$setting_system				 = get_option('fakturo_system_options_group', array());
			$tpl->assign("setting_system", $setting_system);
			$company					 = get_option('fakturo_info_options_group', array());
			$company['img_url']			 = $company['url'];
			$company['tax_condition']	 = (array) get_fakturo_term($company['tax_condition'], 'fktr_tax_conditions');
			$tpl->assign("company", $company);
			if (!$default_template) {
				$id_print_template = self::get_id_by_assigned($object->assgined);
				if ($id_print_template) {
					$default_template = self::get_print_template_data($id_print_template);
				}
			}
			if (!$default_template) {
				return $tpl;
			}


			/**
			 * Sale Invoices Print templates 
			 */
			if ($object->assgined == 'fktr_sale') {
				// assign vars to print template assgined to fktr_sale.

				$imgfiles	 = scandir(FAKTURO_PLUGIN_DIR . 'assets/images/');
				// echo '$imgfiles=' . '<pre>' . print_r($imgfiles, 1) . '</pre>' . '<br>';
				$locale		 = substr(get_locale(), 0, 2); // en, es
				if (in_array(('invoice_background_' . $locale . '.jpg'), $imgfiles)) {
					$backgroundimage = 'invoice_background_' . $locale . '.jpg';
				} else {
					// english by default if doesn't exist current language  
					$backgroundimage = 'invoice_background_en.jpg';
				}

				//$tpl->assign("fktr_invoice_background_image", FAKTURO_PLUGIN_URL . 'assets/images/invoice_background.jpg');
				$tpl->assign("fktr_invoice_background_image", FAKTURO_PLUGIN_URL . 'assets/images/' . $backgroundimage);
				$tpl->assign("fktr_invoice_demo_image", FAKTURO_PLUGIN_URL . 'assets/images/demo.png');

				$sale_invoice				 = fktrPostTypeSales::get_sale_data($object->id);
				$sale_invoice['datei18n']	 = date_i18n(get_option('date_format'), $sale_invoice['date']);

				$client_data									 = fktrPostTypeClients::get_client_data($sale_invoice['client_id']);
				$sale_invoice['client_data']['phone']			 = $client_data['phone'];
				$sale_invoice['client_data']['tax_condition']	 = (array) get_fakturo_term($sale_invoice['client_data']['tax_condition'], 'fktr_tax_conditions');
				$sale_invoice['client_data']['payment_type']	 = (array) get_fakturo_term($sale_invoice['client_data']['payment_type'], 'fktr_payment_types');
				$sale_invoice['invoice_type']					 = (array) get_fakturo_term($sale_invoice['invoice_type'], 'fktr_invoice_types');
				$sale_invoice['currency']						 = (array) get_fakturo_term($sale_invoice['invoice_currency'], 'fktr_currencies');
				$sale_invoice['products']						 = array();
				if (!empty($sale_invoice['uc_id'])) {
					foreach ($sale_invoice['uc_id'] as $key => $product_id) {
						$newProduct					 = array();
						$newProduct['code']			 = $sale_invoice['uc_code'][$key];
						$newProduct['description']	 = $sale_invoice['uc_description'][$key];
						$newProduct['quantity']		 = $sale_invoice['uc_quality'][$key];
						$newProduct['unit_price']	 = $sale_invoice['uc_unit_price'][$key];
						$newProduct['tax']			 = $sale_invoice['uc_tax'][$key];
						$newProduct['tax_porcent']	 = $sale_invoice['uc_tax_porcent'][$key];
						$newProduct['amount']		 = $sale_invoice['uc_amount'][$key];
						$sale_invoice['products'][]	 = $newProduct;
					}
				}
				$sale_invoice['subtotal']	 = $sale_invoice['in_sub_total'];
				$sale_invoice['total']		 = $sale_invoice['in_total'];

				$tpl->assign("invoice", $sale_invoice);

				/**
				 * Receipts Print templates 
				 */
			} else if ($object->assgined == 'fktr_receipt') {
				// assign vars to print template assgined to fktr_receipt.
				$imgfiles	 = scandir(FAKTURO_PLUGIN_DIR . 'assets/images/');
				$locale		 = substr(get_locale(), 0, 2); // en, es
				if (in_array(('receipt_background_' . $locale . '.jpg'), $imgfiles)) {
					$backgroundimage = 'receipt_background_' . $locale . '.jpg';
				} else {
					// english by default if doesn't exist current language  
					$backgroundimage = 'receipt_background_en.jpg';
				}
				$tpl->assign("fktr_receipt_background_image", FAKTURO_PLUGIN_URL . 'assets/images/' . $backgroundimage);

				$receipt			 = fktrPostTypeReceipts::get_receipt_data($object->id);
				$receipt['datei18n'] = date_i18n(get_option('date_format'), $receipt['date']);

				$client_data							 = fktrPostTypeClients::get_client_data($receipt['client_id']);
				//$receipt['client_data']				 = $client_data;
				//echo '$client_data=' . '<pre>' . print_r($client_data, 1) . '</pre>' . '<br>';
				$receipt['client_data']['active']		 = $client_data['active'];
				$receipt['client_data']['name']			 = $client_data['post_title'];
				$receipt['client_data']['address']		 = $client_data['address'];
				$receipt['client_data']['city']			 = $client_data['city'];
				$receipt['client_data']['postcode']		 = $client_data['postcode'];
				$receipt['client_data']['phone']		 = $client_data['phone'];
				$receipt['client_data']['cell_phone']	 = $client_data['cell_phone'];
				$receipt['client_data']['email']		 = $client_data['email'];
				$receipt['client_data']['photo']		 = (isset($client_data['webcam_image'])) ? $client_data['webcam_image'] : '';
				$receipt['client_data']['taxpayer']		 = $client_data['taxpayer'];

				$tax_condition	 = __('No Tax', 'fakturo');
				$tax_data		 = get_fakturo_term($client_data['selected_tax_condition'], 'fktr_tax_conditions');
				if (!is_wp_error($tax_data)) {
					$tax_condition = $tax_data->name;
				}
				$receipt['client_data']['tax_condition'] = $tax_condition;

				$country_name	 = __('No country', 'fakturo');
				$country_data	 = get_fakturo_term($client_data['selected_country'], 'fktr_countries');
				if (!is_wp_error($country_data)) {
					$country_name = $country_data->name;
				}
				$receipt['client_data']['country']['name'] = $country_name;

				$state_name	 = __('No state', 'fakturo');
				$state_data	 = get_fakturo_term($client_data['selected_state'], 'fktr_countries');
				if (!is_wp_error($state_data)) {
					$state_name = $state_data->name;
				}
				$client_data['selected_state_name'] = $state_name;

				// imputed invoices
				if (!empty($receipt['check_invs'])) {
					foreach ($receipt['check_invs'] as $key => $invoice_id) {
						$invoice_data = fktrPostTypeSales::get_sale_data($invoice_id);

						$ript_invoice					 = array();
						$ript_invoice['title']			 = $invoice_data['post_title'];
						$ript_invoice['invoice_type']	 = $invoice_data['invoice_type'];
						$ript_invoice['sale_point']		 = $invoice_data['sale_point'];
						$ript_invoice['number']			 = $invoice_data['invoice_number'];
						$ript_invoice['date']			 = $invoice_data['date'];
						$ript_invoice['datei18n']		 = date_i18n(get_option('date_format'), $invoice_data['date']);
						$ript_invoice['currency']		 = $invoice_data['invoice_currency'];
						$ript_invoice['saleman']		 = $invoice_data['invoice_saleman'];
						$ript_invoice['discount']		 = $invoice_data['invoice_discount'];
						$ript_invoice['sub_total']		 = $invoice_data['in_sub_total'];
						$ript_invoice['total']			 = $invoice_data['in_total'];
						$receipt['invoices'][]			 = $ript_invoice;
					}
				}

				// checks 
				$total_checks = 0;
				if (!empty($receipt['ck_ids'])) {
					foreach ($receipt['ck_ids'] as $term_id) {
						$check[]	 = array();
						$term_check	 = get_fakturo_term($term_id, 'fktr_check');
						if (!is_wp_error($term_check)) {
							$bank_text	 = __('No bank', 'fakturo');
							$term_bank	 = get_fakturo_term($term_check->bank_id, 'fktr_bank_entities');
							if (!is_wp_error($term_bank)) {
								$bank_text = $term_bank->name;
							}
							$symbol			 = ''; //__('No Symbol', 'fakturo');
							$checkCurrency	 = get_fakturo_term($term_check->currency_id, 'fktr_currencies');
							if (!is_wp_error($checkCurrency)) {
								$symbol = $checkCurrency->symbol;
							}
							$check['id']			 = $term_id;
							$check['number']		 = $term_check->name;
							$check['cashing_date']	 = $term_check->cashing_date;
							$check['bank']			 = $bank_text;
							$check['value']			 = (($setting_system['currency_position'] == 'before') ? '' . $symbol . ' ' : '') . '' . number_format($term_check->value, $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']) . '' . (($setting_system['currency_position'] == 'after') ? ' ' . $symbol . '' : '');
							$total_checks			 += $term_check->value;
							$receipt['checks'][]	 = $check;
						}
					}
				} else {
					// make an empty check to show their variables
					$check[]				 = array();
					$check['id']			 = '';
					$check['number']		 = '';
					$check['cashing_date']	 = '';
					$check['bank']			 = '';
					$check['value']			 = '';
					$receipt['checks'][]	 = $check;
				}
				$receipt['total_checks'] = $total_checks;

				$tpl->assign("receipt", $receipt);

				/**
				 * Products Print templates 
				 */
			} else if ($object->assgined == 'fktr_product') {
				// assign vars to print template assgined to fktr_product.
				$imgfiles	 = scandir(FAKTURO_PLUGIN_DIR . 'assets/images/');
				$locale		 = substr(get_locale(), 0, 2); // en, es
				if (in_array(('product_background_' . $locale . '.jpg'), $imgfiles)) {
					$backgroundimage = 'product_background_' . $locale . '.jpg';
				} else {
					// english by default if doesn't exist current language  
					$backgroundimage = 'product_background_en.jpg';
				}
				$tpl->assign("fktr_product_background_image", FAKTURO_PLUGIN_URL . 'assets/images/' . $backgroundimage);

				$product = fktrPostTypeProducts::get_product_data($object->id);
				$tpl->assign("product", $product);
			} else if ($object->assgined == 'fktr_client') {
				// assign vars to print template assgined to fktr_client.
				$imgfiles	 = scandir(FAKTURO_PLUGIN_DIR . 'assets/images/');
				$locale		 = substr(get_locale(), 0, 2); // en, es
				if (in_array(('client_background_' . $locale . '.jpg'), $imgfiles)) {
					$backgroundimage = 'client_background_' . $locale . '.jpg';
				} else {
					// english by default if doesn't exist current language  
					$backgroundimage = 'client_background_en.jpg';
				}
				$tpl->assign("fktr_client_background_image", FAKTURO_PLUGIN_URL . 'assets/images/' . $backgroundimage);

				$client = fktrPostTypeClients::get_client_data($object->id);
				$tpl->assign("client", $client);
			} else if ($object->assgined == 'fktr_provider') {
				// assign vars to print template assgined to fktr_provider.
				$imgfiles	 = scandir(FAKTURO_PLUGIN_DIR . 'assets/images/');
				$locale		 = substr(get_locale(), 0, 2); // en, es
				if (in_array(('provider_background_' . $locale . '.jpg'), $imgfiles)) {
					$backgroundimage = 'provider_background_' . $locale . '.jpg';
				} else {
					// english by default if doesn't exist current language  
					$backgroundimage = 'provider_background_en.jpg';
				}
				$tpl->assign("fktr_provider_background_image", FAKTURO_PLUGIN_URL . 'assets/images/' . $backgroundimage);

				$provider = fktrPostTypeProviders::get_provider_data($object->id);
				$tpl->assign("provider", $provider);
			}

			return $tpl;
		}

		public static function show_print_template() {
			$template_id = $_REQUEST['id'];
			if (empty($template_id)) {
				echo 'Invalid print template id.';
				wp_die();
			}

			$print_template = self::get_print_template_data($template_id);
			if (!isset($print_template['assigned'])) {
				wp_redirect(admin_url('post.php?post=' . $template_id . '&action=edit'));
				exit;
			}
			if ($print_template['assigned'] == -1) {
				wp_die('<h3>' . __('This print template has no assigned object.', 'fakturo') . '</h3>');
			}

			$object				 = new stdClass();
			$object->type		 = self::get_object_type($print_template);
			$object->id			 = self::get_rand_object_id($object->type, $print_template);
			$object->assgined	 = $print_template['assigned'];
			if ($object->id) {
				$tpl	 = new fktr_tpl;
				$tpl	 = apply_filters('fktr_print_template_assignment', $tpl, $object, $print_template);
				$html	 = $tpl->fromString($print_template['content']);
				if (isset($_REQUEST['pdf'])) {
					$pdf = fktr_pdf::getInstance();

					$pdf->set_option('isRemoteEnabled', true);
					$pdf->set_option('isHtml5ParserEnabled', true);

					$pdf->set_paper("A4", "portrait");
					$pdf->load_html($html);  // removed utf8_decode($html) for RTL
					$pdf->render();
					$pdf->stream('pdf.pdf', array('Attachment' => 0));
				} else {
					echo $html;
				}


				exit();
			}
			wp_die('<h3>' . __('Could not find any object related to this print template', 'fakturo') . '</h3>');
		}

		public static function get_id_by_assigned($assigned) {
			global $wpdb;
			$return	 = false;
			$sql	 = sprintf("SELECT p.ID, pm.meta_key, pm.meta_value as assigned FROM {$wpdb->posts} as p
				 LEFT JOIN {$wpdb->postmeta} as pm ON p.ID = pm.post_id
                 WHERE 
                 pm.meta_key = 'assigned'
				 AND p.post_status = 'publish'
				 AND p.post_type = 'fktr_print_template'
				 AND pm.meta_value = '%s'
                 GROUP BY p.ID 
				 LIMIT 1
			 ", $assigned);
			$r		 = $wpdb->get_results($sql, ARRAY_A);
			if (!empty($r)) {
				foreach ($r as $key => $value) {
					$return = $value['ID'];
				}
			}
			return $return;
		}

		public static function get_rand_object_id($object_type, $print_template) {
			$ret = false;
			if ($object_type == 'taxonomy') {
				$terms = get_terms(array(
					'taxonomy'	 => $print_template['assigned'],
					'hide_empty' => false,
					'number'	 => 1,
				));
				if (!is_wp_error($terms)) {
					if ($terms) {
						foreach ($terms as $t) {
							$ret = $t->term_id;
							break;
						}
					}
				}
			}
			if ($object_type == 'post') {
				$args			 = array(
					'post_type'		 => $print_template['assigned'],
					'post_status'	 => 'publish',
					'posts_per_page' => 1,
					'orderby'		 => 'rand'
				);
				$my_random_post	 = new WP_Query($args);
				while ($my_random_post->have_posts()) {
					$my_random_post->the_post();

					$ret = get_the_ID();
				}
			}

			return $ret;
		}

		public static function get_object_type($print_template) {
			$object_type = 'post';
			$is_taxonomy = taxonomy_exists($print_template['assigned']);
			if ($is_taxonomy) {
				$object_type = 'taxonomy';
			}
			$object_type = apply_filters('fktr_get_object_type_by_slug', $object_type);
			return $object_type;
		}

		public static function default_assigned($data) {
			$args		 = array(
				'public' => false
			);
			$output		 = 'objects'; // names or objects, note names is the default
			$operator	 = 'and'; // 'and' or 'or'
			$post_types	 = get_post_types($args, $output, $operator);
			foreach ($post_types as $post_type) {

				if (strpos($post_type->name, 'fktr') === false) {
					continue;
				}
				if (empty($data[$post_type->name])) {
					$data[$post_type->name] = $post_type->label;
				}
			}
			/* DISABLE TAXONOMYS ON ASSIGNED TO
			  $args = array(
			  'public'   => true,
			  );
			  $output = 'objects';
			  $operator = 'and';
			  $taxonomies = get_taxonomies( $args, $output, $operator );
			  if ( $taxonomies ) {
			  foreach ( $taxonomies  as $taxonomy ) {
			  if (strpos($taxonomy->name, 'fktr') === false ) {
			  continue;
			  }
			  if (empty($data[$taxonomy->name])) {
			  $data[$taxonomy->name] =  $taxonomy->label;
			  }
			  }
			  }
			 */
			return $data;
		}

		public static function setup() {

			$labels			 = array(
				'name'				 => __('Print Templates', 'fakturo'),
				'singular_name'		 => __('Print Template', 'fakturo'),
				'add_new'			 => __('Add New', 'fakturo'),
				'add_new_item'		 => __('Add New Print Template', 'fakturo'),
				'edit_item'			 => __('Edit Print Template', 'fakturo'),
				'new_item'			 => __('New Print Template', 'fakturo'),
				'view_item'			 => __('View Print Template', 'fakturo'),
				'search_items'		 => __('Search Print Templates', 'fakturo'),
				'not_found'			 => __('No Print Templates found', 'fakturo'),
				'not_found_in_trash' => __('No Print Templates found in Trash', 'fakturo'),
				'parent_item_colon'	 => __('Parent Print Template:', 'fakturo'),
				'menu_name'			 => __('Print Templates', 'fakturo'),
			);
			$capabilities	 = array(
				'publish_post'			 => 'publish_fktr_print_template',
				'publish_posts'			 => 'publish_fktr_print_templates',
				'read_post'				 => 'read_fktr_print_template',
				'read_private_posts'	 => 'read_private_fktr_print_templates',
				'edit_post'				 => 'edit_fktr_print_template',
				'edit_published_posts'	 => 'edit_published_fktr_print_templates',
				'edit_private_posts'	 => 'edit_private_fktr_print_templates',
				'edit_posts'			 => 'edit_fktr_print_templates',
				'edit_others_posts'		 => 'edit_others_fktr_print_templates',
				'delete_post'			 => 'delete_fktr_print_template',
				'delete_posts'			 => 'delete_fktr_print_templates',
				'delete_published_posts' => 'delete_published_fktr_print_templates',
				'delete_private_posts'	 => 'delete_private_fktr_print_templates',
				'delete_others_posts'	 => 'delete_others_fktr_print_templates',
			);

			$args = array(
				'labels'				 => $labels,
				'hierarchical'			 => false,
				'description'			 => 'Fakturo Print Templates',
				'supports'				 => array('title', /* 'custom-fields' */),
				'register_meta_box_cb'	 => array(__CLASS__, 'meta_boxes'),
				'public'				 => false,
				'show_ui'				 => true,
				'show_in_menu'			 => false,
				'menu_position'			 => 26,
				'menu_icon'				 => 'dashicons-tickets',
				'show_in_nav_menus'		 => false,
				'publicly_queryable'	 => false,
				'exclude_from_search'	 => false,
				'has_archive'			 => false,
				'query_var'				 => true,
				'can_export'			 => true,
				'rewrite'				 => true,
				'capabilities'			 => $capabilities
			);

			register_post_type('fktr_print_template', $args);

			add_filter('enter_title_here', array(__CLASS__, 'name_placeholder'), 10, 2);
		}

		public static function updated_messages($messages) {
			global $post, $post_ID;
			$messages['fktr_print_template'] = array(
				0	 => '',
				1	 => __('Print template updated.', 'fakturo'),
				2	 => '',
				3	 => '',
				4	 => __('Print template updated.', 'fakturo'),
				5	 => '',
				6	 => __('Print template published.', 'fakturo'),
				7	 => __('Print template saved.', 'fakturo'),
				8	 => __('Print template submitted.', 'fakturo'),
				9	 => sprintf(__('Print template scheduled for: <strong>%1$s</strong>.', 'fakturo'), date_i18n(__('M j, Y @ G:i', 'fakturo'), strtotime($post->post_date))),
				10	 => __('Pending Print template.', 'fakturo'),
			);
			return $messages;
		}

		public static function name_placeholder($title_placeholder, $post) {
			if ($post->post_type == 'fktr_print_template') {
				$title_placeholder = __('Your print template name', 'fakturo');
			}
			return $title_placeholder;
		}

		public static function styles() {
			global $post_type;
			if ($post_type == 'fktr_print_template') {
				wp_enqueue_style('post-type-print-template', FAKTURO_PLUGIN_URL . 'assets/css/post-type-print-template.css');
				wp_enqueue_style('wpecf7vb-codemirror', FAKTURO_PLUGIN_URL . 'assets/codemirror/css/codemirror.css');

				wp_enqueue_style('wpecf7vb-monokai', FAKTURO_PLUGIN_URL . 'assets/codemirror/css/monokai.css');
				wp_enqueue_style('wpecf7vb-colbat', FAKTURO_PLUGIN_URL . 'assets/codemirror/css/colbat.css');
				wp_enqueue_style('wpecf7vb-blackboard', FAKTURO_PLUGIN_URL . 'assets/codemirror/css/blackboard.css');
			}
		}

		public static function scripts() {
			global $post_type, $post, $wp_locale, $locale;
			if ($post_type == 'fktr_print_template') {
				wp_dequeue_script('autosave');

				wp_enqueue_script('post-type-print-template', FAKTURO_PLUGIN_URL . 'assets/js/post-type-print-template.js', array('jquery'), WPE_FAKTURO_VERSION, true);

				wp_enqueue_script('wpecf7vb-mirrorcode', FAKTURO_PLUGIN_URL . 'assets/codemirror/js/codemirror.js', array('jquery', 'post-type-print-template'), WPE_FAKTURO_VERSION, true);
				wp_enqueue_script('wpecf7vb-javascript', FAKTURO_PLUGIN_URL . 'assets/codemirror/js/javascript.js', array('wpecf7vb-mirrorcode'), WPE_FAKTURO_VERSION, true);
				wp_enqueue_script('wpecf7vb-xml', FAKTURO_PLUGIN_URL . 'assets/codemirror/js/xml.js', array('wpecf7vb-mirrorcode'), WPE_FAKTURO_VERSION, true);
				wp_enqueue_script('wpecf7vb-css', FAKTURO_PLUGIN_URL . 'assets/codemirror/js/css.js', array('wpecf7vb-mirrorcode'), WPE_FAKTURO_VERSION, true);
				wp_enqueue_script('wpecf7vb-htmlmixed', FAKTURO_PLUGIN_URL . 'assets/codemirror/js/htmlmixed.js', array('wpecf7vb-mirrorcode', 'wpecf7vb-xml'), WPE_FAKTURO_VERSION, true);

				$preview_button	 = '<a  id="preview_button" class="button button-large" href="' . admin_url('admin-post.php?id=' . $post->ID . '&action=show_print_template') . '" target="_new" style="margin:5px;">' . __('Preview', 'fakturo') . '</a>';
				$pdf_button		 = '<a  id="pdf_button" class="button button-large" href="' . admin_url('admin-post.php?id=' . $post->ID . '&action=show_print_template&pdf=true') . '" target="_new" style="margin:5px;">' . __('See PDF', 'fakturo') . '</a>';
				wp_localize_script('post-type-print-template', 'print_template_object',
						array(
							'ajax_url'			 => admin_url('admin-ajax.php'),
							'preview_button'	 => $preview_button,
							'pdf_button'		 => $pdf_button,
							'msg_save_before'	 => __('Save before to preview print template', 'fakturo'),
							'msg_loading_var'	 => __('Loading vars...', 'fakturo'),
							'msg_reset'			 => __('This will remove all your modified data. Are you sure?', 'fakturo'),
							'msg_before_reset'	 => __('Save before to reset to default.', 'fakturo'),
				));
			}
		}

		public static function meta_boxes() {


			add_meta_box('fakturo-invoice-data-box', __('Print Template Data', 'fakturo'), array(__CLASS__, 'print_template_data_box'), 'fktr_print_template', 'normal', 'high');
			add_meta_box('fakturo-template-vars-box', __('Print Template Vars', 'fakturo'), array(__CLASS__, 'print_template_vars_box'), 'fktr_print_template', 'normal', 'high');

			do_action('add_ftkr_print_template_meta_boxes');
		}

		public static function print_template_data_box() {
			global $post;
			$screen = get_current_screen();

			$print_template = self::get_print_template_data($post->ID);

			self::get_default_template();
			$setting_system	 = get_option('fakturo_system_options_group', false);
			$array_assigned	 = apply_filters('fktr_assigned_print_template', array());
			$selectHtml		 = '<select name="assigned" id="assigned">
							<option value="-1" ' . selected(-1, $print_template['assigned'], false) . '> ' . __('Select please', 'fakturo') . ' </option>';
			foreach ($array_assigned as $key => $value) {
				$selectHtml .= '<option value="' . $key . '" ' . selected($key, $print_template['assigned'], false) . '> ' . $value . ' </option>';
			}
			$selectHtml	 .= '</select>';
			$echoHtml	 = '<table>
					<tbody>
						<tr class="tr_fktr">
							<th><label for="description">' . __('Description', 'fakturo') . '	</label></th>
							<td><input id="description" type="text" name="description" value="' . $print_template['description'] . '" class="regular-text"></td>
						</tr>
						<tr class="tr_fktr">
							<th><label for="assigned">' . __('Assigned to', 'fakturo') . '	</label></th>
							<td>' . $selectHtml . '</td>
						</tr>
					
				
			</tbody>
		</table>
		<br/>
		<div class="print_template_editors">
			<div class="wpecf7vb_col" id="print_template_visualeditor">
								
			</div>
		</div>
		<textarea name="content" cols="100" rows="24" id="content" class="">' . $print_template['content'] . '</textarea>

		';

			$echoHtml = apply_filters('fktr_print_template_data_box', $echoHtml);
			echo $echoHtml;
			do_action('add_fktr_print_template_data_box', $echoHtml);
		}

		public static $array_sended = array();

		public static function print_vars_array($array, $index, $current_var) {
			foreach ($array as $key => $val) {
				if (is_array($val)) {
					$key_send = $key;
					if (is_numeric($key)) {
						$key_send = '<strong>ArrayToLoop</strong>';
					}
					self::print_vars_array($val, $index, $current_var . '.' . $key_send . '');
				} else {
					$key_var = array_search('{' . $current_var . '.' . $key . '}', self::$array_sended);
					if ($key_var === false) {
						self::$array_sended[] = '{' . $current_var . '.' . $key . '}';
					}
				}
			}
		}

		public static function get_vars_assigned() {
			$email_template				 = self::get_print_template_data($_POST['template_id']);
			$email_template['assigned']	 = $_POST['assigned'];
			$object						 = new stdClass();
			$object->type				 = self::get_object_type($email_template);
			$object->id					 = self::get_rand_object_id($object->type, $email_template);
			$object->assgined			 = $email_template['assigned'];
			if ($object->id) {
				$tpl = new fktr_tpl;
				$tpl = apply_filters('fktr_print_template_assignment', $tpl, $object, $email_template);

				$index = 0;
				foreach ($tpl->var as $key => $val) {
					if (is_array($val)) {
						self::print_vars_array($val, $index, '$' . $key . '');
					} else {
						self::$array_sended[] = '{$' . $key . '}';
					}
				}
			}
			foreach (self::$array_sended as $v) {
				echo $v . '</br>';
			}
			exit;
		}

		public static function print_template_vars_box() {
			global $post;
			$email_template		 = self::get_print_template_data($post->ID);
			$object				 = new stdClass();
			$object->type		 = self::get_object_type($email_template);
			$object->id			 = self::get_rand_object_id($object->type, $email_template);
			$object->assgined	 = $email_template['assigned'];
			$echoHtml			 = '';
			$echoHtml			 .= '<div>' . __('Vars with members <strong>ArrayToLoop</strong> means that they are list arrays and should be used in a <strong>Loop</strong>.', 'fakturo') . '</div>';
			$echoHtml			 .= '<div id="vars_template_content">';
			if ($object->id) {
				$tpl = new fktr_tpl;
				$tpl = apply_filters('fktr_print_template_assignment', $tpl, $object, $email_template);

				$index = 0;
				foreach ($tpl->var as $key => $val) {
					if (is_array($val)) {
						self::print_vars_array($val, $index, '$' . $key . '');
					} else {
						self::$array_sended[] = '{$' . $key . '}';
					}
				}
			} else {
				$echoHtml .= __('There must be at least one document saved to show here all the list of variables available for printing in the template.', 'fakturo');
			}

			foreach (self::$array_sended as $v) {
				$echoHtml .= $v . '</br>';
			}
			$echoHtml	 .= '</div>';
			$echoHtml	 = apply_filters('fktr_print_template_vars_box', $echoHtml);
			echo $echoHtml;
			do_action('add_fktr_print_template_vars_box', $echoHtml);
		}

		public static function get_default_template() {
			$default_print_templates = array();
			$args					 = array(
				'public' => false
			);
			$output					 = 'objects';
			$operator				 = 'and';
			$post_types				 = get_post_types($args, $output, $operator);
			foreach ($post_types as $post_type) {
				if (strpos($post_type->name, 'fktr') === false) {
					continue;
				}
				if (empty($default_print_templates[$post_type->name])) {
					$default_print_templates[$post_type->name] = FAKTURO_PLUGIN_DIR . 'templates/' . $post_type->name . '-print-default.html';
				}
			}

			$default_print_templates = apply_filters('fktr_default_print_template_paths', $default_print_templates);
			return $default_print_templates;
		}

		public static function get_default_template_by_assigned($assigned) {
			$ret				 = '';
			$default_templates	 = self::get_default_template();
			if (!empty($default_templates[$assigned])) {
				if (file_exists($default_templates[$assigned])) {
					$ret = file_get_contents($default_templates[$assigned]);
				}
			}
			return $ret;
		}

		public static function submitbox() {
			global $post;
			if ($post->post_type != 'fktr_print_template') {
				return true;
			}

			$preview_button	 = '<a  id="preview_button" class="button button-large" href="' . admin_url('admin-post.php?id=' . $post->ID . '&action=show_print_template') . '" target="_new" style="margin:5px;">' . __('Preview', 'fakturo') . '</a>';
			$pdf_button		 = '<a  id="pdf_button" class="button button-large" href="' . admin_url('admin-post.php?id=' . $post->ID . '&action=show_print_template&pdf=true') . '" target="_new" style="margin:5px;">' . __('See PDF', 'fakturo') . '</a>';

			$reset_url		 = wp_nonce_url(admin_url('admin-post.php?id=' . $post->ID . '&action=reset_print_template'), 'reset_print_to_default', '_nonce');
			$reset_button	 = '<a  id="reset_button" class="button button-large" href="' . $reset_url . '" style="margin:5px;">' . __('Reset to default', 'fakturo') . '</a>';

			$echoHtml = '
		<div class="misc-pub-section fktr_subscriptions_preview_button">
			' . $preview_button . '
		</div>
		<div class="misc-pub-section fktr_subscriptions_pdf_button">
			' . $pdf_button . '
		</div>
		<div class="misc-pub-section fktr_subscriptions_reset_button">
			' . $reset_button . '
		</div>

		';
			echo $echoHtml;
		}

		public static function clean_fields($fields) {
			$setting_system = get_option('fakturo_system_options_group', false);
			if (!isset($fields['description'])) {
				$fields['description'] = '';
			}
			if (!isset($fields['content'])) {
				$fields['content'] = '';
			}
			if (!isset($fields['assigned'])) {
				$fields['assigned'] = -1;
			}

			return $fields;
		}

		public static function before_save($fields) {
			$setting_system = get_option('fakturo_system_options_group', false);

			return $fields;
		}

		public static function default_fields($new_status, $old_status, $post) {

			if ($post->post_type == 'fktr_print_template' && $old_status == 'new') {
				$setting_system			 = get_option('fakturo_system_options_group', false);
				$fields					 = array();
				$fields['description']	 = '';
				$fields['content']		 = '';
				$fields['assigned']		 = -1;

				$fields = apply_filters('fktr_clean_print_template_fields', $fields);

				foreach ($fields as $field => $value) {
					if (!is_null($value)) {
						$new = apply_filters('fktr_print_template_metabox_save_' . $field, $value);  //filtra cada campo antes de grabar
						update_post_meta($post->ID, $field, $new);
					}
				}
			}
		}

		public static function get_print_template_data($template_id) {
			$custom_field_keys = get_post_custom($template_id);
			foreach ($custom_field_keys as $key => $value) {
				$custom_field_keys[$key] = maybe_unserialize($value[0]);
			}

			$custom_field_keys = apply_filters('fktr_clean_print_template_fields', $custom_field_keys);
			return $custom_field_keys;
		}

		public static function save($post_id, $post) {
			global $wpdb;

			if (( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) || ( defined('DOING_AJAX') && DOING_AJAX ) || isset($_REQUEST['bulk_edit'])) {
				return false;
			}

			if (isset($post->post_type) && $post->post_type == 'revision' || $post->post_type != 'fktr_print_template') {
				return false;
			}

			if (!current_user_can('manage_options', $post_id)) {
				return false;
			}
			if (( defined('FKTR_STOP_PROPAGATION') && FKTR_STOP_PROPAGATION)) {
				return false;
			}

			$setting_system	 = get_option('fakturo_system_options_group', false);
			$fields			 = apply_filters('fktr_clean_print_template_fields', $_POST);
			$fields			 = apply_filters('fktr_print_template_before_save', $fields);

			foreach ($fields as $field => $value) {

				if (!is_null($value)) {
					$new = apply_filters('fktr_print_template_metabox_save_' . $field, $value);  //filtra cada campo antes de grabar
					update_post_meta($post_id, $field, $new);
				}
			}

			do_action('fktr_save_print_template', $post_id, $post);
		}

	}

	endif;

$fktrPostTypePrintTemplates = new fktrPostTypePrintTemplates();
?>