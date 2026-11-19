<?php
/**
 * Smoke test of Fakturo: creates its own data, walks the main flow, checks the results and deletes everything it made.
 *
 *   wp eval-file wp-content/plugins/fakturo/tests/smoke.php [--url=https://site.example]
 *   FKTR_SMOKE_SKIP_SCREENS=1 wp eval-file ...      (only the in-process checks, no HTTP crawl)
 *
 * It needs a site where Fakturo is set up (currency, invoice types, a sale point and a stock location). It refuses to run on a site that
 * is not local/development/staging unless FKTR_SMOKE=1 is set, because it writes (and deletes) data. It is not part of the release.
 */
if (!defined('ABSPATH')) {
	exit;
}

class fktrSmoke {
	public static $pass = 0;
	public static $fail = 0;
	public static $skipped = 0;
	public static $posts = array();
	public static $terms = array();
	public static $snapshot = array();
	public static $session = '';

	public static function ok($condition, $label, $detail = '') {
		if ($condition) {
			self::$pass++;
			echo "  ok    $label\n";
		} else {
			self::$fail++;
			echo "  FAIL  $label" . ($detail !== '' ? "  [$detail]" : '') . "\n";
		}
		return (bool) $condition;
	}

	public static function skip($label, $why) {
		self::$skipped++;
		echo "  skip  $label ($why)\n";
	}

	public static function section($title) {
		echo "\n== $title\n";
	}

	/** A post made by the test; removed by cleanup(). */
	public static function post($args) {
		$id = wp_insert_post($args);
		self::$posts[] = $id;
		return $id;
	}

	/**
	 * Saves a post the way its edit screen does: the form fields in $_POST with the save nonce of that post, then the status change that makes WordPress call save_post.
	 */
	public static function submit($id, $fields, $status, $original = 'auto-draft') {
		$_POST = wp_slash(array_merge(array('post_ID' => $id, 'original_post_status' => $original), $fields));
		$_POST['fktr_save_nonce'] = wp_create_nonce('fktr_save_nonce_' . $id);
		$_REQUEST = $_POST;
		$result = wp_update_post(array('ID' => $id, 'post_status' => $status, 'post_title' => get_post_field('post_title', $id)));
		$_POST = $_REQUEST = array();
		clean_post_cache($id);
		return get_post_status($id);
	}

	public static function notices_text() {
		$list = get_user_meta(get_current_user_id(), fktrNotices::meta_key(), true);
		return is_array($list) ? implode(' | ', array_map(function ($n) { return wp_strip_all_tags($n['text']); }, $list)) : '';
	}

	public static function clear_notices() {
		delete_user_meta(get_current_user_id(), fktrNotices::meta_key());
	}

	public static function log_file() {
		return (defined('WP_CONTENT_DIR') ? WP_CONTENT_DIR : ABSPATH . 'wp-content') . '/debug.log';
	}

	public static function log_size() {
		$f = self::log_file();
		clearstatcache(true, $f);
		return file_exists($f) ? filesize($f) : 0;
	}

	/** Gives back everything the test touched, also when it dies half way. */
	public static function cleanup() {
		static $done = false;
		if ($done) {
			return;
		}
		$done = true;
		foreach (array_reverse(self::$posts) as $id) {
			if (get_post($id)) {
				wp_delete_post($id, true);
			}
		}
		foreach (self::$terms as $t) {
			wp_delete_term($t[0], $t[1]);
		}
		foreach (self::$snapshot as $option => $value) {
			if ($value === null) {
				delete_option($option);
			} else {
				update_option($option, $value);
			}
		}
		if (class_exists('fktrNotices') && class_exists('fktrRedirects')) {
			delete_user_meta(get_current_user_id(), fktrNotices::meta_key());
			delete_user_meta(get_current_user_id(), fktrRedirects::meta_key());
		}
		if (self::$session) {
			WP_Session_Tokens::get_instance(get_current_user_id())->destroy(self::$session);
		}
	}
}

register_shutdown_function(array('fktrSmoke', 'cleanup'));

// ---------------------------------------------------------------- guards
$host = (string) wp_parse_url(home_url(), PHP_URL_HOST);
$safe = in_array(wp_get_environment_type(), array('local', 'development', 'staging'), true) || preg_match('/(\.local|\.test|\.localhost|^localhost)$/', $host) || getenv('FKTR_SMOKE');
if (!$safe) {
	echo "Refusing to run on $host: this test writes data. Set FKTR_SMOKE=1 to run it anyway on a test site.\n";
	exit(1);
}
if (!class_exists('fakturo')) {
	echo "Fakturo is not active on this site.\n";
	exit(1);
}
$admins = get_users(array('role' => 'administrator', 'number' => 1, 'fields' => 'ID'));
$admin = $admins ? (int) $admins[0] : 1;
wp_set_current_user($admin);
echo "Fakturo smoke test on $host (PHP " . PHP_VERSION . ", WP " . get_bloginfo('version') . ", user $admin)\n";
$log_start = fktrSmoke::log_size();
foreach (array('last_invoice_number', 'last_receipt_number', 'last_subscription_number') as $o) {
	fktrSmoke::$snapshot[$o] = get_option($o, null);
}

$setting = get_option('fakturo_system_options_group');

// ---------------------------------------------------------------- setup the test needs
fktrSmoke::section('Setup');
$types = get_fakturo_terms(array('taxonomy' => 'fktr_invoice_types', 'hide_empty' => false));
$type_out = $type_in = $type_tax = $sale_point = $location = null;
foreach ((array) $types as $t) {
	if (isset($t->sum) && !$t->sum && !$type_out) {
		$type_out = $t->term_id;
	}
	if (isset($t->sum) && $t->sum && !$type_in) {
		$type_in = $t->term_id;
	}
	if (!empty($t->discriminates_taxes) && !$type_tax) {
		$type_tax = $t->term_id;
	}
}
$points = get_fakturo_terms(array('taxonomy' => 'fktr_sale_points', 'hide_empty' => false));
$sale_point = $points ? $points[0]->term_id : 0;
$locations = get_fakturo_terms(array('taxonomy' => 'fktr_locations', 'hide_empty' => false));
$location = $locations ? $locations[0]->term_id : 0;
$currency = (int) $setting['currency'];
fktrSmoke::ok($currency > 0, 'default currency is set');
fktrSmoke::ok($type_out && $type_in, 'there is an invoice type that takes stock out and one that does not');
fktrSmoke::ok($sale_point > 0, 'there is a sale point');
fktrSmoke::ok($location > 0, 'there is a stock location');
fktrSmoke::ok(!empty($setting['use_stock_product']), 'stock control is on (the test needs it)');
if (!($currency && $type_out && $type_in && $sale_point && $location)) {
	echo "\nThe site is not set up enough for the rest of the test.\n";
	exit(1);
}
$padded = function ($n) use ($setting) {
	return str_pad((string) $n, (int) $setting['digits_invoice_number'], '0', STR_PAD_LEFT);
};

// ---------------------------------------------------------------- pure functions
fktrSmoke::section('Numbers, dates and money');
foreach (array(array(',', '.'), array('.', ','), array(' ', ','), array("'", '.')) as $sep) {
	add_filter('option_fakturo_system_options_group', $f = function ($o) use ($sep) {
		$o['thousand'] = $sep[0];
		$o['decimal'] = $sep[1];
		return $o;
	}, 99);
	$plain = (float) fakturo_mask_to_float('1' . $sep[0] . '234' . $sep[1] . '56');
	$thousands = (float) fakturo_mask_to_float('1' . $sep[0] . '234');
	$already = (float) fakturo_mask_to_float('1234.56');
	fktrSmoke::ok(abs($plain - 1234.56) < 0.001 && abs($thousands - 1234) < 0.001 && ($sep[1] == '.' ? true : abs($already - 1234.56) < 0.001), 'mask_to_float with thousand "' . $sep[0] . '" decimal "' . $sep[1] . '"', "$plain / $thousands / $already");
	remove_filter('option_fakturo_system_options_group', $f, 99);
}
fktrSmoke::ok(fktr_round_money(0.1 + 0.2) === 0.3, 'money is rounded to the decimals of the settings');
$ts = fakturo_date2time('16/11/2026 23:30', 'd/m/Y H:i');
fktrSmoke::ok($ts === gmmktime(23, 30, 0, 11, 16, 2026) && date_i18n('d/m/Y H:i', $ts) === '16/11/2026 23:30', 'a typed date is the wall clock of the site and is shown back unchanged');
fktrSmoke::ok(fakturo_date2time('31/02/2026x', 'd/m/Y') === false, 'an invalid date is refused, not turned into a random one');
$tz_ok = true;
foreach (array('America/Argentina/Buenos_Aires', 'Europe/Madrid', 'Asia/Kolkata') as $tz) {
	$keep = get_option('timezone_string');
	update_option('timezone_string', $tz);
	$tz_ok = $tz_ok && fktr_utc_to_local(fktr_local_to_utc($ts)) === $ts;
	update_option('timezone_string', $keep);
}
fktrSmoke::ok($tz_ok, 'local time to UTC and back in three time zones');
$ranges_ok = true;
foreach (array('today', 'yesterday', 'this_week', 'last_week', 'this_month', 'last_month', 'this_quarter', 'last_quarter', 'this_year', 'last_year') as $r) {
	$x = reports::default_timestand_ranges(array(), array('range' => $r));
	$ranges_ok = $ranges_ok && isset($x['from'], $x['to']) && $x['from'] <= $x['to'];
}
fktrSmoke::ok($ranges_ok, 'every report range has a start before its end');
$name = "O'Brien \"Q\" back\\slash";
fktrSmoke::ok(wp_unslash(wp_slash($name)) === $name, 'text with quotes and backslashes survives slashing');

// ---------------------------------------------------------------- client and product
fktrSmoke::section('Client and product');
fktrSmoke::clear_notices();
$client = fktrSmoke::post(array('post_type' => 'fktr_client', 'post_status' => 'draft', 'post_title' => 'SMOKE client'));
$status = fktrSmoke::submit($client, array('uc_address' => array(), 'selected_state' => 0), 'publish');
fktrSmoke::ok($status === 'publish', 'a client is created and saved');
$cdata = fktrPostTypeClients::get_client_data($client);
fktrSmoke::ok(isset($cdata['balance']) && (float) $cdata['balance'] == 0, 'a new client has balance 0');
fktrSmoke::ok(is_array(fktrPostTypeClients::get_client_data(999999999)), 'reading a client that does not exist gives an empty record, not an error');

$product = fktrSmoke::post(array('post_type' => 'fktr_product', 'post_status' => 'draft', 'post_title' => 'SMOKE product'));
fktrSmoke::submit($product, array(), 'publish');
fktrPostTypeProducts::addStock($product, 10, $location);
fktrSmoke::ok((float) (get_post_meta($product, 'stocks', true)[$location] ?? 0) === 10.0, 'stock is added to a product');
fktrPostTypeProducts::removeStock($product, 4, $location);
fktrSmoke::ok((float) (get_post_meta($product, 'stocks', true)[$location] ?? 0) === 6.0, 'stock is taken from a product');
fktrPostTypeProducts::addStock($product, 4, $location);

fktrSmoke::section('Atomic numbers and stock');
$a = fktrPostTypeSales::reserveInvoiceNumber($sale_point, $type_out);
$b = fktrPostTypeSales::reserveInvoiceNumber($sale_point, $type_out);
fktrSmoke::ok($b === $a + 1, 'two reserved invoice numbers are consecutive', "$a, $b");
fktrPostTypeClients::add_balance($client, 0.1);
fktrPostTypeClients::add_balance($client, 0.2);
clean_post_cache($client);
fktrSmoke::ok((float) get_post_meta($client, 'balance', true) === 0.3, 'balance additions do not collect floating point dust');
fktrPostTypeClients::remove_balance($client, 0.3);
$lock_ok = fktr_atomic::run('smoke', function () {
	return fktr_atomic::run('smoke', function () {
		return 'nested';
	});
});
fktrSmoke::ok($lock_ok === 'nested', 'a lock can be asked for again by the same request');

// ---------------------------------------------------------------- sales
fktrSmoke::section('Invoice');
$number = (int) fktrPostTypeSales::suggestInvoiceNumber($sale_point, $type_out) + 50;
$sale_fields = function ($num, $qty, $type = null) use ($client, $sale_point, $type_out, $product, $location, $padded, $name, $currency) {
	return array(
		'client_id' => $client, 'sale_point' => $sale_point, 'invoice_type' => $type ?: $type_out, 'invoice_number' => $padded($num),
		'product_stock_location' => array($product => array($location => array($qty))),
		'invoice_currency' => $currency, 'in_sub_total' => '30', 'in_total' => '30',
		'uc_id' => array($product), 'uc_code' => array('SMK'), 'uc_description' => array('SMOKE product'), 'uc_quality' => array($qty), 'uc_unit_price' => array('10'),
		'uc_amount' => array('30'), 'uc_tax' => array(0), 'uc_tax_porcent' => array('0'),
		'client_data' => array('name' => $name, 'address' => 'x', 'city' => 'x', 'state' => array('id' => 0, 'name' => 'x'), 'country' => array('id' => 0, 'name' => 'x'),
			'taxpayer' => 't', 'tax_condition' => 0, 'payment_type' => 0, 'price_scale' => array('id' => 0, 'name' => 'x'), 'credit_limit' => 0),
	);
};
$sale = fktrSmoke::post(array('post_type' => 'fktr_sale', 'post_status' => 'draft', 'post_title' => 'SMOKE sale'));
$status = fktrSmoke::submit($sale, $sale_fields($number, 3), 'publish');
fktrSmoke::ok($status === 'publish', 'an invoice is published');
$sdata = fktrPostTypeSales::get_sale_data($sale);
fktrSmoke::ok($sdata['client_data']['name'] === $name, 'the client name in the invoice is exactly as typed');
fktrSmoke::ok((float) (get_post_meta($product, 'stocks', true)[$location] ?? 0) === 7.0, 'publishing the invoice took 3 from the stock');
$last = get_option('last_invoice_number');
fktrSmoke::ok(!empty($last), 'the last invoice number was recorded');
fktrSmoke::ok(fktrPostTypeSales::exist_a_invoice_number($padded($number), $sale_point, $type_out), 'the published number is found as in use');

$dup = fktrSmoke::post(array('post_type' => 'fktr_sale', 'post_status' => 'draft', 'post_title' => 'SMOKE sale dup'));
fktrSmoke::clear_notices();
$status = fktrSmoke::submit($dup, $sale_fields($number, 1), 'publish');
fktrSmoke::ok($status === 'draft', 'an invoice with a number already in use stays a draft', $status);
fktrSmoke::ok(stripos(fktrSmoke::notices_text(), 'already in use') !== false || stripos(fktrSmoke::notices_text(), 'ya est') !== false, 'and says why in a notice', fktrSmoke::notices_text());

$big = fktrSmoke::post(array('post_type' => 'fktr_sale', 'post_status' => 'draft', 'post_title' => 'SMOKE sale big'));
fktrSmoke::clear_notices();
$status = fktrSmoke::submit($big, $sale_fields($number + 1, 1000), 'publish');
fktrSmoke::ok($status === 'draft', 'an invoice that needs more stock than there is stays a draft', $status);
fktrSmoke::ok((float) (get_post_meta($product, 'stocks', true)[$location] ?? 0) === 7.0, 'and takes nothing from the stock');

$tpl_note = '';
if (fktrSmoke::ok(class_exists('fktr_pdf'), 'the PDF engine loads')) {
	$pdf = fktr_pdf::render('<p>smoke ñandú</p>')->output();
	fktrSmoke::ok(strpos($pdf, '%PDF-') === 0, 'a PDF is made');
}

// ---------------------------------------------------------------- receipt
fktrSmoke::section('Receipt');
$rnum = str_pad((string) ((int) fktrPostTypeReceipts::suggestReceiptNumber() + 50), (int) $setting['digits_receipt_number'], '0', STR_PAD_LEFT);
$bal = function () use ($client) {
	clean_post_cache($client);
	return (float) get_post_meta($client, 'balance', true);
};
$b0 = $bal();
$receipt = fktrSmoke::post(array('post_type' => 'fktr_receipt', 'post_status' => 'auto-draft', 'post_title' => 'SMOKE receipt'));
$rf = array('client_id' => $client, 'receipt_number' => $rnum, 'available_to_include' => '100', 'currency_id' => 0);
$status = fktrSmoke::submit($receipt, $rf + array('check_invs' => array(0 => $sale), 'inv_ids' => array(0 => $sale), 'to_pay' => array(0 => '10')), 'publish');
fktrSmoke::ok($status === 'publish', 'a receipt is published');
fktrSmoke::ok($bal() - $b0 === -100.0, 'it takes its amount from the client balance', (string) ($bal() - $b0));
clean_post_cache($sale);
$paid = get_post_meta($sale, 'receipts', true);
fktrSmoke::ok(is_array($paid) && isset($paid[$receipt]), 'and is listed as a payment on the invoice');
do_action('save_post', $receipt, get_post($receipt), true);
fktrSmoke::ok($bal() - $b0 === -100.0, 'saving it again does not take the amount twice');
fktrPostTypeReceipts::action_on_delete($receipt);
fktrPostTypeReceipts::action_on_delete($receipt);
clean_post_cache($sale);
$paid = get_post_meta($sale, 'receipts', true);
fktrSmoke::ok($bal() - $b0 === 0.0, 'cancelling gives the amount back, once', (string) ($bal() - $b0));
fktrSmoke::ok(empty($paid[$receipt]), 'and removes the payment from the invoice');
$receipt2 = fktrSmoke::post(array('post_type' => 'fktr_receipt', 'post_status' => 'auto-draft', 'post_title' => 'SMOKE receipt 2'));
fktrSmoke::clear_notices();
$status = fktrSmoke::submit($receipt2, array('client_id' => $client, 'receipt_number' => $rnum, 'available_to_include' => '0', 'currency_id' => 0), 'publish');
$rn2 = get_post_meta($receipt2, 'receipt_number', true);
fktrSmoke::ok($status === 'publish' && $rn2 !== $rnum, 'a receipt number already in use gets the next free one', "$rnum -> $rn2");

// ---------------------------------------------------------------- invoice API
fktrSmoke::section('fktr_create_sale()');
$mailbox = 'smoke-' . wp_generate_password(8, false) . '@fakturo-smoke.invalid';
$api_lines = array(array('description' => 'Consulting', 'quantity' => 2, 'unit_price' => 100), array('product_id' => $product, 'quantity' => 3, 'unit_price' => 50.5));
$api_client = array('email' => $mailbox, 'name' => $name, 'address' => 'Street 1', 'city' => 'Town', 'taxpayer' => '30-1');
if ($type_tax) {
	$api = fktr_create_sale($api_client, $api_lines, array('invoice_type' => $type_tax, 'discount' => 10, 'external_ref' => 'SMOKE-1', 'source' => 'smoke'));
	if (fktrSmoke::ok(!is_wp_error($api), 'an invoice is created from code', is_wp_error($api) ? $api->get_error_message() : '')) {
		fktrSmoke::$posts[] = $api;
		$d = fktrPostTypeSales::get_sale_data($api);
		fktrSmoke::$posts[] = (int) $d['client_id'];
		fktrSmoke::ok(get_post_status($api) === 'publish', 'it is published');
		fktrSmoke::ok((float) $d['in_sub_total'] === 351.5, 'the sub total is the sum of the lines', (string) $d['in_sub_total']);
		$expected = fktr_round_money(351.5 - fktr_round_money(35.15) + fktr_round_money(200 * 0.21) + fktr_round_money(151.5 * 0.21));
		$tax_line = get_fakturo_term($d['uc_tax'][0], 'fktr_tax');
		$pct = is_wp_error($tax_line) ? 0 : (float) $tax_line->percentage;
		$expected = fktr_round_money(351.5 - 35.15 + fktr_round_money(200 * $pct / 100) + fktr_round_money(151.5 * $pct / 100));
		fktrSmoke::ok(abs((float) $d['in_total'] - $expected) < 0.005, 'the total has the discount and the taxes of an invoice type that shows them', $d['in_total'] . ' vs ' . $expected);
		fktrSmoke::ok($d['client_data']['name'] === $name, 'the client created from the e-mail keeps its name exactly');
		fktrSmoke::ok(get_the_title($api) !== '' && strpos(get_post_field('post_name', $api), '-') !== false, 'the invoice has its title and slug');
		$before = get_option('last_invoice_number');
		$again = fktr_create_sale($api_client, $api_lines, array('invoice_type' => $type_tax, 'discount' => 10, 'external_ref' => 'SMOKE-1', 'source' => 'smoke'));
		fktrSmoke::ok($again === $api && $before === get_option('last_invoice_number'), 'the same external reference gives the same invoice and uses no number');
		fktrSmoke::ok(fktr_find_sale_by_ref('SMOKE-1', 'smoke') === $api && fktr_find_sale_by_ref('SMOKE-1', 'other') === 0, 'an invoice is found by its reference and source');
		$second = fktr_create_sale(array('email' => $mailbox), array(array('description' => 'More', 'quantity' => 1, 'unit_price' => 10)), array('invoice_type' => $type_tax, 'external_ref' => 'SMOKE-2', 'source' => 'smoke'));
		if (fktrSmoke::ok(!is_wp_error($second), 'a second invoice for the same e-mail')) {
			fktrSmoke::$posts[] = $second;
			$d2 = fktrPostTypeSales::get_sale_data($second);
			fktrSmoke::ok($d2['client_id'] === $d['client_id'], 'finds the same client instead of making another');
			fktrSmoke::ok((int) $d2['invoice_number'] === (int) $d['invoice_number'] + 1, 'and takes the next number');
			// The commissions report adds up the real totals of the invoices, in any language and with decimals.
			$mine = get_posts(array('post_type' => 'fktr_sale', 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids', 'meta_key' => 'invoice_saleman', 'meta_value' => $admin));
			$sum = 0;
			$count = 0;
			foreach ($mine as $sid) {
				$kind = get_fakturo_term((int) get_post_meta($sid, 'invoice_type', true), 'fktr_invoice_types');
				$sign = (!is_wp_error($kind) && !empty($kind->sum)) ? -1 : 1;
				$count += $sign > 0 ? 1 : 0;
				$sum += $sign * fakturo_transform_money(get_post_meta($sid, 'invoice_currency', true), $setting['currency'], (float) get_post_meta($sid, 'in_total', true));
			}
			$line = expenses_summary_report::get_sales_data(array('seller_id' => $admin), array('from' => strtotime('2000-01-01'), 'to' => time() + 365 * DAY_IN_SECONDS));
			fktrSmoke::ok(count($line) === 1 && (int) $line[0]->total === $count && abs($line[0]->total_sum - $sum) < 0.01, 'the commissions report counts the invoices of a salesman and adds up their real totals, less the credit notes', $line ? $line[0]->total . '/' . $count . ' ' . $line[0]->total_sum . ' vs ' . $sum : 'no rows');
			$cleaned = fktr_tax_commission_scales::before_save(array('ranges' => array(array('from' => '0', 'to' => '5', 'percentage' => '0'), array('from' => '6', 'to' => '', 'percentage' => '3', 'fixed' => '50.5'), array('from' => '', 'to' => '9', 'percentage' => '4'), array('from' => '10', 'to' => '12', 'percentage' => '1', 'fixed' => '-7'))));
			fktrSmoke::ok($cleaned['ranges'] == array(array('from' => '0', 'to' => '5', 'percentage' => '0', 'fixed' => 0), array('from' => '6', 'to' => PHP_INT_MAX, 'percentage' => '3', 'fixed' => 50.5), array('from' => '10', 'to' => '12', 'percentage' => '1', 'fixed' => 0)), 'a commission range may start at 0 and may be 0%; one without start is dropped; an empty end is no limit; the fixed amount is optional and never negative');
			// A scale of the salesman with 3% and a fixed amount: the report adds the fixed amount once to the percentage.
			$scale = wp_insert_term('fktr_smoke_scale', 'fktr_commission_scales');
			if (fktrSmoke::ok(!is_wp_error($scale), 'a commission scale can be created')) {
				set_fakturo_term($scale['term_id'], $scale['term_taxonomy_id'], fktr_tax_commission_scales::before_save(array('type' => 'exception', 'seller_id' => $admin, 'ranges' => array(array('from' => '1', 'to' => '', 'percentage' => '3', 'fixed' => '50')))));
				ob_start();
				expenses_summary_report::content(array('seller_id' => $admin, 'range' => 'other', 'sec' => 'expenses_summary'), array('from' => strtotime('2000-01-01'), 'to' => time() + 365 * DAY_IN_SECONDS));
				$html = ob_get_clean();
				$expected_commission = number_format($sum * 0.03 + 50, 2, '.', ',');
				fktrSmoke::ok(strpos($html, '<td>' . $expected_commission . ' ') !== false, 'the commissions report gives the percentage of the range plus its fixed amount', $expected_commission);
				wp_delete_term($scale['term_id'], 'fktr_commission_scales');
			}
		}
		// e-mail with the PDF attached (the mail is stopped before it leaves)
		$seen = array();
		add_action('phpmailer_init', $hook = function ($m) use (&$seen) {
			$seen[] = count($m->getAttachments());
			$m->clearAllRecipients();
		}, 99);
		$third = fktr_create_sale($api_client, array(array('description' => 'Mailed', 'quantity' => 1, 'unit_price' => 5)), array('invoice_type' => $type_tax, 'send_email' => true));
		remove_action('phpmailer_init', $hook, 99);
		if (!is_wp_error($third)) {
			fktrSmoke::$posts[] = $third;
		}
		fktrSmoke::ok($seen === array(1), 'send_email e-mails the invoice with its PDF', json_encode($seen));
		// an add-on that sends the invoice back to draft (what fakturo_afip does when the tax office refuses it)
		add_action('fktr_save_sale', $refuse = function ($post_id) {
			global $wpdb;
			$wpdb->update($wpdb->posts, array('post_status' => 'draft'), array('ID' => $post_id));
		}, 99);
		$refused = fktr_create_sale($api_client, array(array('description' => 'Refused', 'quantity' => 1, 'unit_price' => 5)), array('invoice_type' => $type_tax, 'external_ref' => 'SMOKE-3', 'source' => 'smoke'));
		remove_action('fktr_save_sale', $refuse, 99);
		fktrSmoke::ok(is_wp_error($refused) && $refused->get_error_code() === 'fktr_sale_not_published', 'an invoice that an add-on sends back to draft is reported, not hidden');
		if (is_wp_error($refused)) {
			$refused_id = (int) $refused->get_error_data()['sale_id'];
			fktrSmoke::$posts[] = $refused_id;
			fktrSmoke::ok(get_post_status($refused_id) === 'draft', 'and stays a draft (it is not published again behind the add-on)');
		}
	}
} else {
	fktrSmoke::skip('invoice from code with taxes', 'no invoice type shows the taxes apart');
}
$refusals = array(
	'an invoice without lines' => array($api_client, array(), 'fktr_sale_no_lines'),
	'a line with no quantity' => array($api_client, array(array('description' => 'x', 'quantity' => 0, 'unit_price' => 1)), 'fktr_sale_bad_line'),
	'no client or e-mail' => array(array('name' => 'x'), $api_lines, 'fktr_sale_no_client'),
	'more stock than there is' => array($api_client, array(array('product_id' => $product, 'quantity' => 100000, 'unit_price' => 1)), 'fktr_sale_invalid'),
);
foreach ($refusals as $label => $case) {
	$r = fktr_create_sale($case[0], $case[1], array('invoice_type' => $type_out));
	fktrSmoke::ok(is_wp_error($r) && $r->get_error_code() === $case[2], $label . ' is refused', is_wp_error($r) ? $r->get_error_code() : (string) $r);
	if (!is_wp_error($r)) {
		fktrSmoke::$posts[] = $r;
	}
}
$counter = get_option('last_invoice_number');
$draft = fktr_create_sale($api_client, array(array('description' => 'Draft', 'quantity' => 1, 'unit_price' => 5)), array('invoice_type' => $type_out, 'publish' => false));
if (fktrSmoke::ok(!is_wp_error($draft) && get_post_status($draft) === 'draft', 'a draft can be made')) {
	fktrSmoke::$posts[] = $draft;
	fktrSmoke::ok($counter === get_option('last_invoice_number'), 'and does not use the numbering until it is published');
}
// the client made from the e-mail is one for the whole group above
foreach (get_posts(array('post_type' => 'fktr_client', 'meta_key' => 'email', 'meta_value' => $mailbox, 'fields' => 'ids', 'numberposts' => -1, 'post_status' => 'any')) as $made_client) {
	fktrSmoke::$posts[] = (int) $made_client;
}

// ---------------------------------------------------------------- subscriptions
fktrSmoke::section('Subscriptions add-on');
if (class_exists('fktrPostTypeSubscriptions')) {
	$u = function ($s) {
		return gmmktime((int) substr($s, 11, 2), (int) substr($s, 14, 2), 0, (int) substr($s, 5, 2), (int) substr($s, 8, 2), (int) substr($s, 0, 4));
	};
	$next = function ($end, $type, $count, $last) {
		return fktrPostTypeSubscriptions::cron_next('', $end, 'interval', array('count' => $count, 'type' => $type), $last);
	};
	$eom = $next(1, 'months', 1, $u('2026-11-30 10:05'));
	fktrSmoke::ok($eom > $u('2026-11-30 10:05') + DAY_IN_SECONDS, 'billed on the last day of the month, the next one is not the same day', gmdate('Y-m-d H:i', $eom));
	fktrSmoke::ok(gmdate('Y-m-d', fktr_utc_to_local($next(0, 'months', 1, $u('2026-01-31 10:00')))) === '2026-02-28', 'one month after January 31 is the last day of February');
	fktrSmoke::ok(gmdate('Y-m-d', fktr_utc_to_local($next(0, 'years', 1, $u('2028-02-29 10:00')))) === '2029-02-28', 'one year after February 29 is February 28');
	$cron = fktrPostTypeSubscriptions::cron_next('0 9 * * *', 0, 'cron', array(), $u('2026-11-16 00:00'));
	fktrSmoke::ok(gmdate('H:i', fktr_utc_to_local($cron)) === '09:00', 'a cron at 09:00 runs at 09:00 of the site');
	if ($type_tax && isset($api) && !is_wp_error($api)) {
		// Billing a subscription goes through fktr_create_sale() (called directly: the task itself would bill every real subscription that is due).
		$sub = fktrSmoke::post(array('post_type' => 'fktr_subscriptions', 'post_status' => 'publish', 'post_title' => 'SMOKE subscription'));
		$sub_meta = array('client_id' => $d['client_id'], 'invoice_type' => $type_tax, 'sale_point' => $sale_point, 'invoice_currency' => $currency, 'invoice_discount' => 0, 'invoice_saleman' => $admin,
			'uc_id' => array(0, 0), 'uc_code' => array('S1', 'S2'), 'uc_description' => array('Monthly plan', 'Extra "x" \\ y'), 'uc_quality' => array(1, 2), 'uc_unit_price' => array(100, 25), 'uc_amount' => array(100, 50), 'uc_tax' => array(0, 0), 'uc_tax_porcent' => array(0, 0));
		foreach ($sub_meta as $k => $v) {
			update_post_meta($sub, $k, wp_slash($v));
		}
		$billed = fktrPostTypeSubscriptions::createInvoiceFromSubscriptionId($sub, true, 1234567890);
		if (fktrSmoke::ok(!is_wp_error($billed), 'a subscription is billed with the invoice function', is_wp_error($billed) ? $billed->get_error_message() : '')) {
			fktrSmoke::$posts[] = $billed;
			$bd = fktrPostTypeSales::get_sale_data($billed);
			fktrSmoke::ok(get_post_status($billed) === 'publish' && (float) $bd['in_total'] === 150.0, 'with its totals and published', $bd['in_total']);
			fktrSmoke::ok($bd['uc_description'][1] === 'Extra "x" \\ y', 'and the text of its lines exactly as typed');
			fktrSmoke::ok(fktrPostTypeSubscriptions::createInvoiceFromSubscriptionId($sub, true, 1234567890) === $billed, 'billing the same moment again gives the same invoice');
			$missing = $sub_meta;
			update_post_meta($sub, 'client_id', 999999999);
			$failed = fktrPostTypeSubscriptions::createInvoiceFromSubscriptionId($sub, true, 1234567891);
			fktrSmoke::ok(is_wp_error($failed) && $failed->get_error_code() === 'fktr_sale_no_client', 'a subscription whose client is gone is reported, not billed');
			// What the billing task records is shown on the subscription screen and in the list.
			update_post_meta($sub, 'billing_error', array('time' => time(), 'code' => 'x', 'message' => 'No stock <b>"A"</b>'));
			global $pagenow, $post;
			$keep = array($pagenow, $post);
			$pagenow = 'post.php';
			$post = get_post($sub);
			ob_start();
			fktrPostTypeSubscriptions::billing_notices();
			$shown = ob_get_clean();
			$_GET['post_type'] = 'fktr_subscriptions';
			$pagenow = 'edit.php';
			ob_start();
			fktrPostTypeSubscriptions::billing_notices();
			$listed = ob_get_clean();
			unset($_GET['post_type']);
			list($pagenow, $post) = $keep;
			delete_post_meta($sub, 'billing_error');
			fktrSmoke::ok(strpos($shown, 'notice-error') !== false && strpos($shown, '&lt;b&gt;') !== false && strpos($shown, '<b>') === false && strpos($shown, 'No stock') !== false, 'a failed billing is shown on the subscription screen, with its reason escaped');
			fktrSmoke::ok(strpos($listed, 'notice-error') !== false && strpos($listed, 'post=' . $sub) !== false, 'and in the list, with a link to the subscription');
		}
	}
	fktrSmoke::ok(class_exists('fktrSubscriptionsCron') && wp_next_scheduled('subscription_cron') !== false || (get_site_option('fktr_subscriptions_cron')['only_external'] ?? 0), 'the billing task is scheduled (or the site bills only from the external cron)');
} else {
	fktrSmoke::skip('subscriptions', 'the add-on is not active');
}

// ---------------------------------------------------------------- access by role
fktrSmoke::section('Access by role');
{
	$opt_name = 'fakturo_system_options_group';
	fktrSettings::register_settings(); // admin_init does not run in WP-CLI: without it the sanitizer of the option is not attached
	$opt_before = get_option($opt_name);
	$roles_before = array();
	foreach (fktrUserRoles::slugs() as $slug) {
		$roles_before[$slug] = array_filter(get_role($slug)->capabilities);
	}
	require_once ABSPATH . 'wp-admin/includes/user.php';
	if (is_multisite()) {
		require_once ABSPATH . 'wp-admin/includes/ms.php';
	}
	$team = array();
	foreach (array('fakturo_manager' => 'mgr', 'fakturo_seller' => 'sel') as $role => $short) {
		$team[$short] = wp_insert_user(array('user_login' => 'fktr_smoke_' . $short, 'user_pass' => wp_generate_password(24), 'user_email' => 'smoke-' . $short . '@fktr-smoke.invalid', 'role' => $role));
	}
	$can = function ($user, $cap) {
		clean_user_cache($user);
		return user_can($user, $cap);
	};
	$has = function ($slug, $cap) {
		$role = get_role($slug);
		return $role && !empty($role->capabilities[$cap]);
	};
	$save = function ($cells, $form = false) use ($opt_name) {
		$value = get_option($opt_name);
		$value['roles_access'] = $cells;
		if ($form) {
			$_POST = array('option_page' => 'fakturo-settings-roles');
		}
		update_option($opt_name, $value);
		$_POST = array();
		return get_option($opt_name);
	};

	fktrSmoke::ok(!is_wp_error($team['mgr']) && !is_wp_error($team['sel']), 'a manager and a salesman for the test');
	fktrSmoke::ok($can($admin, 'fktr_edit_templates') && $can($admin, 'edit_fktr_products'), 'the administrator has every capability');
	fktrSmoke::ok($can($team['mgr'], 'edit_fakturo_settings') && !$can($team['mgr'], 'fktr_edit_templates'), 'by default the manager changes settings but not templates');
	fktrSmoke::ok($can($team['sel'], 'edit_fktr_sales') && !$can($team['sel'], 'edit_fakturo_settings') && !$can($team['sel'], 'fakturo_reports'), 'by default the salesman sells and has no settings or reports');
	fktrSmoke::ok(array_keys(fktrUserRoles::groups()) && isset(fktrUserRoles::groups()['report_client_incomes']), 'every money report is a group of its own');

	// A manager can not change what the roles can do.
	wp_set_current_user($team['mgr']);
	$stored = $save(array('fakturo_seller' => array('report_client_incomes' => 1)));
	wp_set_current_user($admin);
	fktrSmoke::ok(empty($stored['roles_access']) && !$has('fakturo_seller', 'fktr_report_client_incomes'), 'a manager can not change the permissions of the roles');

	// The administrator can: only the differences are saved.
	$stored = $save(array('fakturo_seller' => array('report_client_incomes' => 1, 'sales' => 1), 'fakturo_manager' => array('products' => 0, 'sales' => 1, 'bogus' => 1), 'fakturo_nobody' => array('sales' => 1)));
	fktrSmoke::ok($stored['roles_access'] == array('fakturo_seller' => array('report_client_incomes' => 1), 'fakturo_manager' => array('products' => 0)), 'only the differences with the default are saved, unknown roles and groups dropped', json_encode($stored['roles_access']));
	fktrSmoke::ok($has('fakturo_seller', 'fktr_report_client_incomes') && $has('fakturo_seller', 'fakturo_reports') && !$has('fakturo_seller', 'fktr_report_sales'), 'the salesman gets that report only');
	fktrSmoke::ok(!$has('fakturo_manager', 'edit_fktr_products') && $has('fakturo_manager', 'edit_fktr_clients'), 'the manager lost the products and kept the clients');
	fktrSmoke::ok($can($admin, 'edit_fktr_products'), 'the administrator keeps what the manager lost');

	// The form sends the whole table, unchecked boxes as 0.
	$matrix = fktrUserRoles::matrix();
	$sent = array();
	foreach ($matrix as $slug => $row) {
		foreach ($row as $key => $value) {
			$sent[$slug][$key] = $value ? '1' : '0';
		}
	}
	$sent['fakturo_seller']['templates_edit'] = '1';
	$sent['fakturo_seller']['report_client_incomes'] = '0';
	$sent['fakturo_manager']['products'] = '1';
	$stored = $save($sent, true);
	fktrSmoke::ok($stored['roles_access'] === array('fakturo_seller' => array('templates_edit' => 1)), 'the table of the form is saved as differences too', json_encode($stored['roles_access']));
	fktrSmoke::ok($has('fakturo_seller', 'fktr_edit_templates') && !$has('fakturo_seller', 'fktr_report_client_incomes') && $has('fakturo_manager', 'edit_fktr_products'), 'and the roles follow it');
	fktrSmoke::ok(!empty($stored['use_stock_product']) === !empty($opt_before['use_stock_product']) && $stored['dateformat'] === $opt_before['dateformat'], 'the form of the roles leaves the other settings as they are');
	fktrSmoke::ok($can($team['sel'], 'fktr_edit_templates'), 'a user with that role can use the permission at once');

	// A person can not hand out a role that has more than they have.
	fktrSmoke::ok(fktrUserRoles::can_give($team['mgr'], 'fakturo_manager'), 'a manager can give the manager role');
	fktrSmoke::ok(!fktrUserRoles::can_give($team['mgr'], 'fakturo_seller'), 'but not a salesman role that now can change templates, which the manager can not');
	fktrSmoke::ok(!fktrUserRoles::can_give($team['sel'], 'fakturo_manager') && fktrUserRoles::can_give($team['sel'], 'fakturo_seller'), 'a salesman can not give the manager role');

	// A role an add-on defines.
	$accountant = function ($roles) {
		$roles['fakturo_accountant'] = array('label' => 'Fakturo Accountant', 'groups' => array('menu', 'report_client_incomes', 'report_client_account_movements'));
		return $roles;
	};
	add_filter('fktr_team_roles', $accountant);
	fktrUserRoles::apply_roles();
	fktrSmoke::ok($has('fakturo_accountant', 'fktr_report_client_account_movements') && $has('fakturo_accountant', 'fakturo_reports') && $has('fakturo_accountant', 'edit_fakturo_dashboard'), 'an accountant role gets its money reports and the menu');
	fktrSmoke::ok(!$has('fakturo_accountant', 'edit_fktr_sales') && !$has('fakturo_accountant', 'fktr_report_sales') && !$has('fakturo_accountant', 'edit_fakturo_settings'), 'and nothing else');
	$table = fktrUserRoles::access_section($opt_name);
	fktrSmoke::ok(strpos($table, 'Fakturo Accountant') !== false && strpos($table, '[roles_access][fakturo_accountant][report_client_incomes]') !== false, 'the table of the settings shows the new role');
	$stored = $save(array('fakturo_accountant' => array('sales' => 1)));
	fktrSmoke::ok($has('fakturo_accountant', 'edit_fktr_sales'), 'and the administrator can give it more');
	$acc_user = wp_insert_user(array('user_login' => 'fktr_smoke_acc', 'user_pass' => wp_generate_password(24), 'user_email' => 'smoke-acc@fktr-smoke.invalid', 'role' => 'fakturo_accountant'));
	$team['acc'] = $acc_user;
	$men = wp_list_pluck(fktrUserRoles::salesmen(), 'ID');
	fktrSmoke::ok(in_array($team['sel'], $men) && in_array($acc_user, $men) && in_array($admin, $men), 'whoever can invoice, also with a role from an add-on, can be the salesman of an invoice');
	$stored = $save(array('fakturo_accountant' => array('sales' => 0)));
	$men = wp_list_pluck(fktrUserRoles::salesmen(), 'ID');
	fktrSmoke::ok(!in_array($acc_user, $men), 'and whoever can not, is not offered');
	remove_filter('fktr_team_roles', $accountant);
	remove_role('fakturo_accountant');

	// Back to the defaults.
	$stored = $save(array('fakturo_seller' => array('sales' => 0)), true);
	$_POST = array('option_page' => 'fakturo-settings-roles');
	$value = get_option($opt_name);
	$value['roles_access_reset'] = 1;
	update_option($opt_name, $value);
	$_POST = array();
	$after = array();
	foreach (fktrUserRoles::slugs() as $slug) {
		$after[$slug] = array_filter(get_role($slug)->capabilities);
	}
	fktrSmoke::ok(get_option($opt_name)['roles_access'] === array() && $after == $roles_before, 'restoring the defaults gives back the roles as they were');
	$count = count(fktrUserRoles::groups());
	fktrSmoke::ok(substr_count(fktrUserRoles::access_section($opt_name), 'type="checkbox"') === $count * count(fktrUserRoles::slugs()) + 1, 'the table has a box for every role and group');

	foreach ($team as $id) {
		if (!is_wp_error($id)) {
			is_multisite() ? wpmu_delete_user($id) : wp_delete_user($id); // in a network wp_delete_user() only takes the user off the site
		}
	}
	update_option($opt_name, $opt_before);
	fktrUserRoles::apply_roles();
	wp_set_current_user($admin);
}

// ---------------------------------------------------------------- screens, AJAX and printing over HTTP
fktrSmoke::section('Screens over HTTP');
if (getenv('FKTR_SMOKE_SKIP_SCREENS')) {
	fktrSmoke::skip('screens', 'FKTR_SMOKE_SKIP_SCREENS is set');
} else {
	$secure = 'https' === wp_parse_url(admin_url(), PHP_URL_SCHEME);
	$exp = time() + HOUR_IN_SECONDS;
	// One session for the test: the nonces made here must carry the same session token as the cookies the requests send.
	$token = WP_Session_Tokens::get_instance($admin)->create($exp);
	fktrSmoke::$session = $token;
	$logged_in = wp_generate_auth_cookie($admin, $exp, 'logged_in', $token);
	$_COOKIE[LOGGED_IN_COOKIE] = $logged_in;
	$jar = array(
		new WP_Http_Cookie(array('name' => $secure ? SECURE_AUTH_COOKIE : AUTH_COOKIE, 'value' => wp_generate_auth_cookie($admin, $exp, $secure ? 'secure_auth' : 'auth', $token))),
		new WP_Http_Cookie(array('name' => LOGGED_IN_COOKIE, 'value' => $logged_in)),
	);
	$get = function ($url, $post = null) use ($jar) {
		$args = array('cookies' => $jar, 'timeout' => 60, 'redirection' => 0, 'sslverify' => false);
		return $post === null ? wp_remote_get($url, $args) : wp_remote_post($url, $args + array('body' => $post));
	};
	$php_error = function ($body) {
		return preg_match('/(Warning|Notice|Deprecated|Fatal error|Parse error)(<\/b>)?:\s+[^\n]{3,300}\s+in\s+(<b>)?\/[^\s<]+(<\/b>)?\s+on line\s+(<b>)?\d+/', $body);
	};

	$a = admin_url();
	$urls = array($a . 'index.php', $a . 'admin.php?page=fakturo_dashboard', $a . 'admin.php?page=fakturo-settings', $a . 'admin.php?page=fakturo-settings-system', $a . 'admin.php?page=fakturo-settings-dashboard', $a . 'admin.php?page=fakturo_reports', $a . 'plugins.php?page=fakturo');
	foreach (array_keys(reports::get_tabs()) as $sec) {
		if ($sec != 'default') {
			$urls[] = $a . 'admin.php?page=fakturo_reports&sec=' . $sec . '&range=this_month';
		}
	}
	foreach (array('fktr_sale' => $sale, 'fktr_receipt' => $receipt, 'fktr_product' => $product, 'fktr_client' => $client, 'fktr_provider' => 0, 'fktr_print_template' => 0, 'fktr_email_template' => 0, 'fktr_subscriptions' => 0) as $type => $mine) {
		if (!post_type_exists($type)) {
			continue;
		}
		$urls[] = $a . 'edit.php?post_type=' . $type;
		$urls[] = $a . 'post-new.php?post_type=' . $type;
		$existing = $mine ?: (int) (get_posts(array('post_type' => $type, 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids'))[0] ?? 0);
		if ($existing) {
			$urls[] = $a . 'post.php?post=' . $existing . '&action=edit';
		}
	}
	foreach (get_taxonomies(array(), 'objects') as $tx) {
		if (strpos($tx->name, 'fktr_') !== 0) {
			continue;
		}
		$urls[] = $a . 'edit-tags.php?taxonomy=' . $tx->name;
		$term = get_terms(array('taxonomy' => $tx->name, 'hide_empty' => false, 'number' => 1));
		if ($term && !is_wp_error($term)) {
			$urls[] = $a . 'term.php?taxonomy=' . $tx->name . '&tag_ID=' . $term[0]->term_id;
		}
	}
	$bad = array();
	$dashboard_html = '';
	foreach (array_unique($urls) as $url) {
		$r = $get($url);
		$body = is_wp_error($r) ? '' : wp_remote_retrieve_body($r);
		$code = is_wp_error($r) ? 0 : wp_remote_retrieve_response_code($r);
		if ($url === $a . 'index.php') {
			$dashboard_html = $body;
		}
		if ($code !== 200 || $php_error($body) || stripos($body, 'critical error') !== false) {
			$bad[] = str_replace($a, '', $url) . ' (' . ($code ?: (is_wp_error($r) ? $r->get_error_message() : '?')) . ($php_error($body) ? ', PHP notice' : '') . ')';
		}
	}
	fktrSmoke::ok(!$bad, count(array_unique($urls)) . ' admin screens load without a PHP notice', implode('; ', $bad));

	$nonce = preg_match('/fktr_ajax_nonce = "([^"]+)"/', $dashboard_html, $m) ? $m[1] : '';
	if (fktrSmoke::ok($nonce !== '', 'the admin pages print the nonce of the AJAX calls')) {
		$bad = array();
		$calls = array('get_client_data' => array('client_id' => $client), 'get_products' => array('q' => 'smoke'), 'get_suggest_invoice_number' => array('sale_point' => $sale_point, 'invoice_type' => $type_out),
			'get_suggest_receipt_number' => array(), 'receipt_client_data' => array('client_id' => $client), 'validate_sale' => array('inputs' => ''), 'validate_receipt' => array('inputs' => ''),
			'get_client_states' => array(), 'get_states' => array(), 'get_vars_assigned' => array(), 'get_vars_assigned_print' => array());
		foreach ($calls as $action => $extra) {
			$r = $get(admin_url('admin-ajax.php'), array_merge(array('action' => $action, 'fktr_nonce' => $nonce), $extra));
			$body = is_wp_error($r) ? '' : wp_remote_retrieve_body($r);
			if (is_wp_error($r) || wp_remote_retrieve_response_code($r) !== 200 || $php_error($body)) {
				$bad[] = $action;
			}
		}
		fktrSmoke::ok(!$bad, count($calls) . ' AJAX calls answer without errors', implode(', ', $bad));
		$r = $get(admin_url('admin-ajax.php'), array('action' => 'get_client_data', 'client_id' => $client));
		fktrSmoke::ok(wp_remote_retrieve_response_code($r) === 403, 'an AJAX call without the nonce is refused');
	}

	$print = fktr_guard::url('print_invoice', $sale);
	$r = $get(html_entity_decode($print));
	fktrSmoke::ok(wp_remote_retrieve_response_code($r) === 200 && stripos(wp_remote_retrieve_body($r), '<') !== false && !$php_error(wp_remote_retrieve_body($r)), 'the print view of an invoice loads', wp_remote_retrieve_response_code($r) . ' ' . substr(trim(preg_replace('/\s+/', ' ', wp_strip_all_tags(wp_remote_retrieve_body($r)))), 0, 200) . ' ' . $print);
	fktrSmoke::ok(stripos((string) wp_remote_retrieve_header($r, 'content-security-policy'), 'sandbox') !== false, 'the print view of a template comes in a sandbox: its scripts do not run');
	$r = $get(html_entity_decode($print) . '&pdf=1');
	fktrSmoke::ok(strpos(wp_remote_retrieve_body($r), '%PDF-') === 0, 'the PDF of an invoice is served');
	$r = $get(html_entity_decode(fktr_guard::url('print_receipt', $receipt)) . '&pdf=1');
	fktrSmoke::ok(strpos(wp_remote_retrieve_body($r), '%PDF-') === 0, 'the PDF of a receipt is served');
	$r = $get(admin_url('admin-post.php?action=print_invoice&id=' . $sale));
	fktrSmoke::ok(wp_remote_retrieve_response_code($r) === 403, 'a print link without its token is refused');
}


// ---------------------------------------------------------------- end
fktrSmoke::section('Log');
$grown = fktrSmoke::log_size() - $log_start;
fktrSmoke::ok($grown <= 0, 'PHP wrote nothing to debug.log during the test', $grown . ' bytes');
fktrSmoke::cleanup();
echo "\n" . fktrSmoke::$pass . ' passed, ' . fktrSmoke::$fail . ' failed, ' . fktrSmoke::$skipped . " skipped\n";
exit(fktrSmoke::$fail ? 1 : 0);
