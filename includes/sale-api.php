<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Creates an invoice from code, without the edit screen: for other plugins (Pro Sites billing, a shop, a webhook).
 *
 * fktr_create_sale( $client, $lines, $args ) returns the id of the invoice, or a WP_Error. It does what the edit screen does: finds or creates the client, works out the totals
 * (the screen does that in JavaScript), takes the number from the numbering of the sale point and invoice type, checks the same rules as the screen (number free, stock),
 * takes the stock out, keeps the client snapshot and fires `fktr_save_sale` (where fakturo_afip asks for the CAE).
 *
 * $client  array  `id` of an existing client, or `email` to find one (and `name`, `address`, `city`, `postcode`, `phone`, `taxpayer`, `country_id`, `state_id`,
 *                 `tax_condition`, `payment_type`, `price_scale` to create it when there is none; `create_client` in $args turns that off).
 * $lines   array  each: `description` (or `product_id`), `quantity`, `unit_price` (a plain number, without tax unless the invoice type shows the taxes apart),
 *                 optional `product_id`, `code`, `tax_id` (a tax of Fakturo; 0 = no tax; the product's or the default tax when the key is missing).
 * $args    array  `invoice_type`, `sale_point`, `currency` (the defaults of the settings when missing), `date` (timestamp of the site clock, now by default), `discount` (percent),
 *                 `publish` (true: the invoice is published and cannot change; false: it stays a draft), `invoice_number` (the next one by default),
 *                 `stock_location` (location that gives the stock; the first one by default), `saleman` (user id), `author` (user id),
 *                 `external_ref` + `source` (a transaction id and who sends it: the same pair never makes a second invoice, the first one is returned),
 *                 `send_email` (e-mail the PDF to the client once published).
 */
if (!class_exists('fktrSaleApi')) :

	class fktrSaleApi {

		const REF_META = 'external_ref';

		public static function create($client, $lines, $args = array()) {
			$args = wp_parse_args($args, array(
				'invoice_type' => 0, 'sale_point' => 0, 'currency' => 0, 'date' => 0, 'discount' => 0, 'publish' => true, 'invoice_number' => '',
				'stock_location' => 0, 'saleman' => 0, 'author' => 0, 'external_ref' => '', 'source' => '', 'send_email' => false, 'create_client' => true,
			));
			$key = self::ref_key($args['source'], $args['external_ref']);
			if ($key === '') {
				return self::build($client, $lines, $args, '');
			}
			// Two calls with the same transaction at the same time: the second waits and finds the invoice of the first.
			return fktr_atomic::run('sale_ref_' . md5($key), function () use ($client, $lines, $args, $key) {
				$found = self::find_by_ref($args['external_ref'], $args['source']);
				return $found ? $found : self::build($client, $lines, $args, $key);
			});
		}

		private static function ref_key($source, $ref) {
			$ref = trim((string) $ref);
			return $ref === '' ? '' : ( trim((string) $source) !== '' ? trim((string) $source) . ':' : '' ) . $ref;
		}

		/**
		 * The invoice made for an external reference, or 0.
		 */
		public static function find_by_ref($ref, $source = '') {
			$key = self::ref_key($source, $ref);
			if ($key === '') {
				return 0;
			}
			$ids = get_posts(array('post_type' => 'fktr_sale', 'post_status' => array('publish', 'draft', 'pending', 'private'), 'meta_key' => self::REF_META, 'meta_value' => $key, 'fields' => 'ids', 'numberposts' => 1, 'orderby' => 'ID', 'order' => 'ASC'));
			return $ids ? (int) $ids[0] : 0;
		}

		private static function error($code, $message, $data = array()) {
			return new WP_Error($code, $message, $data);
		}

		private static function build($client, $lines, $args, $ref_key) {
			$setting = get_option('fakturo_system_options_group', false);
			$client = is_array($client) ? $client : array();

			// ---- what the invoice is made of
			$invoice_type_id = (int) ( $args['invoice_type'] ? $args['invoice_type'] : $setting['invoice_type'] );
			$sale_point_id = (int) ( $args['sale_point'] ? $args['sale_point'] : $setting['sale_point'] );
			$currency_id = (int) ( $args['currency'] ? $args['currency'] : $setting['currency'] );
			$invoice_type = get_fakturo_term($invoice_type_id, 'fktr_invoice_types');
			if (is_wp_error($invoice_type)) {
				return self::error('fktr_sale_bad_type', __('This invoice type does not exist, try another.', 'fakturo'));
			}
			if (is_wp_error(get_fakturo_term($sale_point_id, 'fktr_sale_points'))) {
				return self::error('fktr_sale_bad_point', __('The sale point does not exist.', 'fakturo'));
			}
			if (is_wp_error(get_fakturo_term($currency_id, 'fktr_currencies'))) {
				return self::error('fktr_sale_bad_currency', __('The currency does not exist.', 'fakturo'));
			}
			$discount = (float) $args['discount'];
			if ($discount < 0 || $discount > 100) {
				return self::error('fktr_sale_bad_discount', __('The discount must be a percentage between 0 and 100.', 'fakturo'));
			}
			$rows = self::lines($lines, $invoice_type);
			if (is_wp_error($rows)) {
				return $rows;
			}

			// ---- the client
			$client_id = self::client($client, !empty($args['create_client']));
			if (is_wp_error($client_id)) {
				return $client_id;
			}
			$snapshot = self::snapshot($client_id);
			$condition = $snapshot['tax_condition'] ? get_fakturo_term($snapshot['tax_condition'], 'fktr_tax_conditions') : null;
			$totals = self::totals($rows, $invoice_type, ( $condition && !is_wp_error($condition) ) ? $condition : null, $discount);

			// ---- the stock
			$stock = array();
			$takes_stock = !empty($setting['use_stock_product']);
			if ($takes_stock) {
				foreach ($rows as $row) {
					if ($row['product_id']) {
						$location = (int) $args['stock_location'];
						if (!$location) {
							$locations = get_fakturo_terms(array('taxonomy' => 'fktr_locations', 'hide_empty' => false));
							$location = $locations ? (int) $locations[0]->term_id : 0;
						}
						if (!$location) {
							return self::error('fktr_sale_no_location', __('There is no stock location to take the products from.', 'fakturo'));
						}
						$stock[$row['product_id']][$location][] = $row['quantity'];
					}
				}
			}

			// ---- one invoice at a time takes its number (the same lock as the edit screen) and checks the rules
			$publish = !empty($args['publish']);
			fktr_atomic::acquire(fktrPostTypeSales::PUBLISH_LOCK);
			$result = self::write($client_id, $snapshot, $rows, $totals, $stock, $invoice_type_id, $sale_point_id, $currency_id, $discount, $args, $publish, $ref_key);
			fktr_atomic::release(fktrPostTypeSales::PUBLISH_LOCK);
			if (is_wp_error($result)) {
				return $result;
			}

			if ($publish && !empty($args['send_email']) && get_post_status($result) === 'publish') {
				fktr_mail::send_sale_invoice_pdf_to_client($result, false, false);
			}
			return $result;
		}

		private static function write($client_id, $snapshot, $rows, $totals, $stock, $invoice_type_id, $sale_point_id, $currency_id, $discount, $args, $publish, $ref_key) {
			$setting = get_option('fakturo_system_options_group', false);
			$number = trim((string) $args['invoice_number']);
			// A published invoice takes its number for good; a draft only shows the next one, like the edit screen, and takes it when it is published.
			$reserve = ( $number === '' && $publish );
			if ($number === '') {
				$number = fktrPostTypeSales::suggestInvoiceNumber($sale_point_id, $invoice_type_id);
			}
			$number = str_pad((string) $number, (int) $setting['digits_invoice_number'], '0', STR_PAD_LEFT);

			$fields = array(
				'client_id' => $client_id, 'client_data' => $snapshot, 'invoice_type' => $invoice_type_id, 'sale_point' => $sale_point_id, 'invoice_number' => $number,
				'date' => $args['date'] ? (int) $args['date'] : current_time('timestamp'), 'invoice_currency' => $currency_id,
				'invoice_saleman' => $args['saleman'] ? (int) $args['saleman'] : get_current_user_id(), 'invoice_discount' => $discount,
				'in_sub_total' => $totals['sub_total'], 'in_total' => $totals['total'], 'product_stock_location' => $stock, 'receipts' => array(),
				'uc_id' => array(), 'uc_code' => array(), 'uc_description' => array(), 'uc_quality' => array(), 'uc_unit_price' => array(), 'uc_amount' => array(), 'uc_tax' => array(), 'uc_tax_porcent' => array(),
			);
			foreach ($totals['lines'] as $row) {
				$fields['uc_id'][] = $row['product_id'];
				$fields['uc_code'][] = $row['code'];
				$fields['uc_description'][] = $row['description'];
				$fields['uc_quality'][] = $row['quantity'];
				$fields['uc_unit_price'][] = $row['unit_price'];
				$fields['uc_amount'][] = $row['amount'];
				$fields['uc_tax'][] = $row['tax_id'];
				$fields['uc_tax_porcent'][] = $row['tax_percent'];
			}
			$fields = apply_filters('fktr_clean_sale_fields', $fields);

			// The same rules as the edit screen: number not in use, invoice type, enough stock.
			$check = $fields;
			$check['post_ID'] = 0;
			$check['original_post_status'] = 'auto-draft';
			$error = fktrPostTypeSales::validate($check);
			if ($error) {
				return self::error('fktr_sale_invalid', $error['message'], $error);
			}

			if ($reserve) {
				$number = str_pad((string) fktrPostTypeSales::reserveInvoiceNumber($sale_point_id, $invoice_type_id), (int) $setting['digits_invoice_number'], '0', STR_PAD_LEFT);
				$fields['invoice_number'] = $number;
			}

			$post_id = wp_insert_post(array(
				'post_type' => 'fktr_sale', 'post_status' => $publish ? 'publish' : 'draft', 'post_title' => $number,
				'post_author' => $args['author'] ? (int) $args['author'] : get_current_user_id(),
			), true);
			if (is_wp_error($post_id)) {
				return $post_id;
			}
			if ($ref_key !== '') {
				// Written before the rest so a call that arrives meanwhile already finds this invoice.
				update_post_meta($post_id, self::REF_META, wp_slash($ref_key));
			}
			$post = get_post($post_id);
			$fields = apply_filters('fktr_sale_before_save', $fields, $post);
			fktrPostTypeSales::store($post_id, $post, $fields, true);
			// The title and the slug are written straight to the table: wp_update_post() would also save the status it has in its cache, and an add-on may have just sent the invoice back to draft.
			clean_post_cache($post_id);
			global $wpdb;
			$title = fktrPostTypeSales::getTitleInvoiceNumber($post_id);
			$wpdb->update($wpdb->posts, array('post_title' => $title, 'post_name' => wp_unique_post_slug(sanitize_title($title), $post_id, get_post_status($post_id), 'fktr_sale', 0)), array('ID' => $post_id));
			clean_post_cache($post_id);

			if ($publish && get_post_status($post_id) !== 'publish') {
				// An add-on refused it (fakturo_afip asks the tax office when the invoice is saved and sends it back to draft if it is not authorized).
				return self::error('fktr_sale_not_published', __('The invoice was saved as a draft because it could not be completed.', 'fakturo'), array('sale_id' => $post_id));
			}
			return (int) $post_id;
		}

		/**
		 * Finds the client by id or e-mail, or creates it. Returns the id.
		 */
		private static function client($client, $create) {
			if (!empty($client['id'])) {
				$id = (int) $client['id'];
				return ( get_post_type($id) === 'fktr_client' ) ? $id : self::error('fktr_sale_no_client', __('The client does not exist.', 'fakturo'));
			}
			$email = isset($client['email']) ? sanitize_email($client['email']) : '';
			if ($email === '' || !is_email($email)) {
				return self::error('fktr_sale_no_client', __('A client id or a valid e-mail is needed.', 'fakturo'));
			}
			return fktr_atomic::run('client_email_' . md5(strtolower($email)), function () use ($client, $email, $create) {
				$found = get_posts(array('post_type' => 'fktr_client', 'post_status' => array('publish', 'draft', 'pending', 'private'), 'meta_key' => 'email', 'meta_value' => $email, 'fields' => 'ids', 'numberposts' => 1, 'orderby' => 'ID', 'order' => 'ASC'));
				if ($found) {
					return (int) $found[0];
				}
				if (!$create) {
					return self::error('fktr_sale_no_client', __('There is no client with that e-mail.', 'fakturo'));
				}
				$name = isset($client['name']) ? sanitize_text_field($client['name']) : '';
				$id = wp_insert_post(array('post_type' => 'fktr_client', 'post_status' => 'publish', 'post_title' => wp_slash($name !== '' ? $name : $email)), true); // wp_insert_post() removes slashes, so a name with a backslash needs them
				if (is_wp_error($id)) {
					return $id;
				}
				$data = array(
					'email' => $email, 'active' => 1,
					'address' => isset($client['address']) ? sanitize_text_field($client['address']) : '', 'city' => isset($client['city']) ? sanitize_text_field($client['city']) : '',
					'postcode' => isset($client['postcode']) ? sanitize_text_field($client['postcode']) : '', 'phone' => isset($client['phone']) ? sanitize_text_field($client['phone']) : '',
					'taxpayer' => isset($client['taxpayer']) ? sanitize_text_field($client['taxpayer']) : '',
					'selected_country' => isset($client['country_id']) ? (int) $client['country_id'] : 0, 'selected_state' => isset($client['state_id']) ? (int) $client['state_id'] : 0,
					'selected_tax_condition' => isset($client['tax_condition']) ? (int) $client['tax_condition'] : 0, 'selected_payment_type' => isset($client['payment_type']) ? (int) $client['payment_type'] : 0,
					'selected_price_scale' => isset($client['price_scale']) ? (int) $client['price_scale'] : 0,
				);
				$data = apply_filters('fktr_clean_client_fields', $data);
				fktr_guard::write($id, fktr_guard::fields($data, 'client'), 'client', false);
				return (int) $id;
			});
		}

		/**
		 * The data of the client kept inside the invoice, as the edit screen keeps it.
		 */
		private static function snapshot($client_id) {
			$c = fktrPostTypeClients::get_client_data($client_id);
			$name = function ($id, $taxonomy) {
				$term = $id ? get_fakturo_term($id, $taxonomy) : null;
				return ( $term && !is_wp_error($term) ) ? $term->name : '';
			};
			return array(
				'name' => get_post_field('post_title', $client_id, 'raw'), 'address' => (string) $c['address'], 'city' => (string) $c['city'],
				'state' => array('id' => (int) $c['selected_state'], 'name' => $name($c['selected_state'], 'fktr_countries')),
				'country' => array('id' => (int) $c['selected_country'], 'name' => $name($c['selected_country'], 'fktr_countries')),
				'taxpayer' => (string) $c['taxpayer'], 'tax_condition' => (int) $c['selected_tax_condition'], 'payment_type' => (int) $c['selected_payment_type'],
				'price_scale' => array('id' => (int) $c['selected_price_scale'], 'name' => $name($c['selected_price_scale'], 'fktr_price_scales')),
				'credit_limit' => $c['credit_limit'],
			);
		}

		/**
		 * Checks and completes the lines (code, description and tax from the product when missing).
		 */
		private static function lines($lines, $invoice_type) {
			if (!is_array($lines) || !$lines) {
				return self::error('fktr_sale_no_lines', __('The invoice needs at least one line.', 'fakturo'));
			}
			$setting = get_option('fakturo_system_options_group', false);
			$rows = array();
			foreach (array_values($lines) as $i => $line) {
				$line = is_array($line) ? $line : array();
				$product_id = !empty($line['product_id']) ? (int) $line['product_id'] : 0;
				if ($product_id && get_post_type($product_id) !== 'fktr_product') {
					return self::error('fktr_sale_bad_line', /* translators: %d: line number. */ sprintf(__('Line %d: the product does not exist.', 'fakturo'), $i + 1));
				}
				$quantity = isset($line['quantity']) ? $line['quantity'] : null;
				$price = isset($line['unit_price']) ? $line['unit_price'] : null;
				if (!is_numeric($quantity) || (float) $quantity <= 0 || !is_numeric($price) || (float) $price < 0) {
					return self::error('fktr_sale_bad_line', /* translators: %d: line number. */ sprintf(__('Line %d needs a quantity above zero and a price of zero or more.', 'fakturo'), $i + 1));
				}
				$code = isset($line['code']) ? sanitize_text_field($line['code']) : '';
				$description = isset($line['description']) ? sanitize_text_field($line['description']) : '';
				$product = $product_id ? fktrPostTypeProducts::get_product_data($product_id) : array();
				if ($product_id && $code === '') {
					$code = (string) get_post_meta($product_id, apply_filters('fktr_meta_key_code_product_' . $setting['default_code'], 'internal'), true);
				}
				if ($product_id && $description === '') {
					$key = apply_filters('fktr_meta_key_description_product_' . $setting['default_description'], 'title');
					$description = $key === 'title' ? get_the_title($product_id) : (string) get_post_meta($product_id, $key, true);
				}
				if ($description === '') {
					return self::error('fktr_sale_bad_line', /* translators: %d: line number. */ sprintf(__('Line %d needs a description.', 'fakturo'), $i + 1));
				}
				// `tax_id` given (even 0 = no tax) is used as it is; without it the tax of the product, then the default tax of the settings.
				if (array_key_exists('tax_id', $line)) {
					$tax_id = (int) $line['tax_id'];
				} else {
					$tax_id = !empty($product['tax']) ? (int) $product['tax'] : (int) $setting['tax'];
				}
				$tax = $tax_id ? get_fakturo_term($tax_id, 'fktr_tax') : null;
				if ($tax_id && is_wp_error($tax)) {
					return self::error('fktr_sale_bad_line', /* translators: %d: line number. */ sprintf(__('Line %d: the tax does not exist.', 'fakturo'), $i + 1));
				}
				$rows[] = array(
					'product_id' => $product_id, 'code' => $code, 'description' => $description, 'quantity' => (float) $quantity, 'unit_price' => (float) $price,
					'tax_id' => $tax_id, 'tax_percent' => $tax ? (float) $tax->percentage : 0.0,
				);
			}
			return $rows;
		}

		/**
		 * The totals the edit screen works out in JavaScript: the amount of each line, the discount in percent over the sub total and, only when the invoice type shows the
		 * taxes apart, the taxes (all at the percentage of the client's tax condition when it overwrites them, else each line at its own tax), added to the total.
		 * Everything is rounded to the decimals of the settings.
		 *
		 * @return array lines (with `amount`), sub_total, discount, taxes (tax id => amount), total
		 */
		public static function totals($rows, $invoice_type, $tax_condition, $discount_percent) {
			$sub_total = 0.0;
			foreach ($rows as $k => $row) {
				$rows[$k]['amount'] = fktr_round_money($row['quantity'] * $row['unit_price']);
				$sub_total += $rows[$k]['amount'];
			}
			$sub_total = fktr_round_money($sub_total);
			$discount = fktr_round_money($sub_total / 100 * (float) $discount_percent);
			$base = $sub_total - $discount;
			$taxes = array();
			if (!empty($invoice_type->discriminates_taxes)) {
				if ($tax_condition && !empty($tax_condition->overwrite_taxes)) {
					$taxes[0] = fktr_round_money($base / 100 * (float) $tax_condition->tax_percentage);
				} else {
					foreach ($rows as $row) {
						$taxes[$row['tax_id']] = ( isset($taxes[$row['tax_id']]) ? $taxes[$row['tax_id']] : 0 ) + fktr_round_money($row['amount'] / 100 * $row['tax_percent']);
					}
				}
			}
			return array('lines' => $rows, 'sub_total' => $sub_total, 'discount' => $discount, 'taxes' => $taxes, 'total' => fktr_round_money($base + array_sum($taxes)));
		}
	}

	/**
	 * Creates an invoice. See the description at the top of includes/sale-api.php.
	 *
	 * @return int|WP_Error The id of the invoice (the existing one when the same external reference was already invoiced) or the reason it was not made.
	 */
	function fktr_create_sale($client, $lines, $args = array()) {
		return fktrSaleApi::create($client, $lines, $args);
	}

	/**
	 * The invoice made for an external reference (a transaction id and who sent it), or 0.
	 */
	function fktr_find_sale_by_ref($ref, $source = '') {
		return fktrSaleApi::find_by_ref($ref, $source);
	}

endif;
