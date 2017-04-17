<?php
/**
 * Fakturo Reports Class.
 *
 * @package Fakturo
 * @subpackage Report
 *
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * client_account_movements class.
 *
 * @since 0.6
 */
class client_account_movements {
	/**
	 * @access public
	 * @return void
	 * @since 0.6
	 * Add hooks for reports.
	 */
	public static function hooks() {
		add_action('report_page_before_content_client_account_movements', array(__CLASS__, 'before_content'), 10, 2);
		add_action('report_page_content_client_account_movements', array(__CLASS__, 'content'), 10, 2);
		add_filter('get_objects_reports_client_account_movements', array(__CLASS__, 'get_objects'), 10, 3);
		add_action('admin_post_client_account_movements_print_pdf', array(__CLASS__, 'print_pdf'));
	}
	/**
	* Static function print_pdf used to print the report on PDF.
	* @access public
	* @return void
	* @since 0.6
	*/
	public static function print_pdf() {

		$request = wp_parse_args($_REQUEST, reports::default_request());
		$ranges = array();
		$ranges['from'] = 0;
		$ranges['to'] = 0;
		/*
		* This filter can be used to create or update timestamp ranges.
		* $ranges will be used by get_object_chart()
		*/
		$total_html_print = '';
		$ranges = apply_filters('fktr_reports_ranges_timestamp', $ranges, $request);
		$access = reports::access_tab($request);
		if (!$access) {
			$total_html_print .= '<div class="postbox" style="margin-top:10px; padding:30px;"><h2>'.__( "Sorry, you don't have access to this page.", FAKTURO_TEXT_DOMAIN ).'</h2></div>';
			echo $total_html_print;
			return true;
		}
		$setting_system = get_option('fakturo_system_options_group', false);
		$currencyDefault = get_fakturo_term($setting_system['currency'], 'fktr_currencies');
		if (is_wp_error($currencyDefault)) {
			$total_html_print .= '<p>'.__( 'Account Movements needs the default currency on system settings.', FAKTURO_TEXT_DOMAIN ).'</p>';
			echo $total_html_print;
			return true;
		}
		$total_html_print .= '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
				<html>
					<head>
						<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15">
						<title></title>

					</head>
					<body>
					';
		
		$html_client_data = '';
		if (is_numeric($request['client_id']) && $request['client_id'] > 0) {
			$client_data = fktrPostTypeClients::get_client_data($request['client_id']);
			
			
			$html_client_data = '<div style="width:50%; text-align: left; display: inline-block;"><h3>'.__('Client', FAKTURO_TEXT_DOMAIN ).': '.$client_data['post_title'].'</h3></div>';
		} else {
			$total_html_print = '<div style="margin-left:15px;"><h3>'.__('Select a client please.', FAKTURO_TEXT_DOMAIN ).'</h3></div>';
			echo $total_html_print;
			return false;
		}
		$total_html_print .= $html_client_data;

		$objects_client = client_summmary::get_objects_client($request, $ranges, false);
		$documents_values = $objects_client['documents_values'];

		$total_html_print .= '<div style="width:50%; text-align: right; display: inline-block;"><h3>'.sprintf(__('Date: since %s til %s', FAKTURO_TEXT_DOMAIN ), date_i18n($setting_system['dateformat'], $ranges['from']), date_i18n($setting_system['dateformat'], $ranges['to'])).'</h3></div>';
		$html_objects = '<div style="clear: both;"><h2>No results with this filters</h2></div>';
		if (!empty($objects_client['objects'])) {
			$html_objects = '<table class="wp-list-table widefat fixed striped posts" style="width: 100%;">
				<thead>
				<tr>
					<td>
						'.__('Date', FAKTURO_TEXT_DOMAIN).'
					</td>
					<td>
						'.__('Type', FAKTURO_TEXT_DOMAIN).'
					</td>
					<td>
						'.__('Reference', FAKTURO_TEXT_DOMAIN).'
					</td>
					<td>
						'.__('Debit', FAKTURO_TEXT_DOMAIN).'
					</td>
					<td>
						'.__('Credit', FAKTURO_TEXT_DOMAIN).'
					</td>
					<td>
						'.__('Balance', FAKTURO_TEXT_DOMAIN).'
					</td>
				</tr>
				</thead>
				<tbody id="the-list">';
			$balance = 0;
			foreach ($objects_client['objects'] as $obj) {

				$obj_type = '';
				$obj_link = admin_url('post.php?post='.$obj['ID'].'&action=edit');
				
				if ($obj['post_type']=='fktr_sale') {
					$obj_type = __('Invoice', FAKTURO_TEXT_DOMAIN);
					
				} else {
					$obj_type = __('Receipt', FAKTURO_TEXT_DOMAIN);
					
				}
				$debit = 0;
				$credit = 0;
				$total = $obj['report_total'];
				if ($total < 0) {
					$debit = -$total;
					$balance -= $debit;
				} else {
					
					$credit = $total;
					$balance += $credit;
				}

				

				$debit_print = (($setting_system['currency_position'] == 'before')?$currencyDefault->symbol.' ':'').''.number_format($debit, $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']).''.(($setting_system['currency_position'] == 'after')?' '.$currencyDefault->symbol:'');
				
				$credit_print = (($setting_system['currency_position'] == 'before')?$currencyDefault->symbol.' ':'').''.number_format($credit, $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']).''.(($setting_system['currency_position'] == 'after')?' '.$currencyDefault->symbol:'');

				$balance_print = (($setting_system['currency_position'] == 'before')?$currencyDefault->symbol.' ':'').''.number_format($balance, $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']).''.(($setting_system['currency_position'] == 'after')?' '.$currencyDefault->symbol:'');

				
				$html_objects .= '<tr>
					<td>
						'. date_i18n($setting_system['dateformat'].' '.get_option( 'time_format' ), $obj['report_timestamp']).'
					</td>
					<td>
						'.$obj_type.'
					</td>
					<td>
						'.$obj['post_title'].'
					</td>
					<td>
						'.$debit_print.'
					</td>
					<td>
						'.$credit_print.'
					</td>
					<td>
						'.$balance_print.'
					</td>
				</tr>';
			}
			$html_objects .= '</tbody>
			</table>
			<div style="width:100%; text-align: right;"><h3>'.$balance_print.'</h3></div>
			<div class="clear"></div>';
		}
		
		$total_html_print .= '
		<div style="width: 100%; float: left;">
		'.($html_objects).'
		
		</div>';
		$total_html_print .= '</body>
			</html>';

		$pdf = fktr_pdf::getInstance();
		$pdf ->set_paper("A4", "portrait");
		$pdf ->load_html(utf8_decode($total_html_print));
		$pdf ->render();
		$pdf ->stream('pdf.pdf', array('Attachment'=>0));

	}
	/**
	* Print HTML before content on report page.
	* @access public
	* @param $request Array of values the $_REQUEST filtered.
	* @param $ranges Array of ranges on timestamp to get objects.
	* @return void
	*/
	public static function before_content($request, $ranges) {
		wp_enqueue_script( 'jquery-select2', FAKTURO_PLUGIN_URL . 'assets/js/jquery.select2.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
		wp_enqueue_script('fakturo_reports_client_summary', FAKTURO_PLUGIN_URL . 'assets/js/reports-client-summary.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
	}
	
	/**
	* Print HTML on report page content.
	* @access public
	* @param $request Array of values the $_REQUEST filtered.
	* @param $ranges Array of ranges on timestamp to get objects.
	* @return void
	* @since 0.6
	*/
	public static function content($request, $ranges) {
		$setting_system = get_option('fakturo_system_options_group', false);
		$currencyDefault = get_fakturo_term($setting_system['currency'], 'fktr_currencies');
		if (is_wp_error($currencyDefault)) {
			echo '<p>'.__( 'Account Movements needs the default currency on system settings.', FAKTURO_TEXT_DOMAIN ).'</p>';
			return true;
		}
		
		self::get_form_filters($request);
		
		$html_client_data = '';
		if (is_numeric($request['client_id']) && $request['client_id'] > 0) {
			$client_data = fktrPostTypeClients::get_client_data($request['client_id']);
			
			
			$html_client_data = '<div style="float:left; margin-left:15px;"><h3>'.__('Client', FAKTURO_TEXT_DOMAIN ).': '.$client_data['post_title'].'</h3></div>';
		} else {
			echo '<div style="margin-left:15px;"><h3>'.__('Select a client please.', FAKTURO_TEXT_DOMAIN ).'</h3></div>';
			return false;
		}
		echo $html_client_data;

		$objects_client = client_summmary::get_objects_client($request, $ranges, false);
		$documents_values = $objects_client['documents_values'];

		echo '<div style="float:right; margin-right:15px;"><h3>'.sprintf(__('Date: since %s til %s', FAKTURO_TEXT_DOMAIN ), date_i18n($setting_system['dateformat'], $ranges['from']), date_i18n($setting_system['dateformat'], $ranges['to'])).'</h3></div>';
		$html_objects = '<div style="clear: both;"><h2>No results with this filters</h2></div>';
		if (!empty($objects_client['objects'])) {
			$html_objects = '<table class="wp-list-table widefat fixed striped posts">
				<thead>
				<tr>
					<td>
						'.__('Date', FAKTURO_TEXT_DOMAIN).'
					</td>
					<td>
						'.__('Type', FAKTURO_TEXT_DOMAIN).'
					</td>
					<td>
						'.__('Reference', FAKTURO_TEXT_DOMAIN).'
					</td>
					<td>
						'.__('Debit', FAKTURO_TEXT_DOMAIN).'
					</td>
					<td>
						'.__('Credit', FAKTURO_TEXT_DOMAIN).'
					</td>
					<td>
						'.__('Balance', FAKTURO_TEXT_DOMAIN).'
					</td>
				</tr>
				</thead>
				<tbody id="the-list">';
			$balance = 0;
			foreach ($objects_client['objects'] as $obj) {

				$obj_type = '';
				$obj_link = admin_url('post.php?post='.$obj['ID'].'&action=edit');
				
				if ($obj['post_type']=='fktr_sale') {
					$obj_type = __('Invoice', FAKTURO_TEXT_DOMAIN);
					
				} else {
					$obj_type = __('Receipt', FAKTURO_TEXT_DOMAIN);
					
				}
				$debit = 0;
				$credit = 0;
				$total = $obj['report_total'];
				if ($total < 0) {
					$debit = -$total;
					$balance -= $debit;
				} else {
					
					$credit = $total;
					$balance += $credit;
				}

				

				$debit_print = (($setting_system['currency_position'] == 'before')?$currencyDefault->symbol.' ':'').''.number_format($debit, $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']).''.(($setting_system['currency_position'] == 'after')?' '.$currencyDefault->symbol:'');
				
				$credit_print = (($setting_system['currency_position'] == 'before')?$currencyDefault->symbol.' ':'').''.number_format($credit, $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']).''.(($setting_system['currency_position'] == 'after')?' '.$currencyDefault->symbol:'');

				$balance_print = (($setting_system['currency_position'] == 'before')?$currencyDefault->symbol.' ':'').''.number_format($balance, $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']).''.(($setting_system['currency_position'] == 'after')?' '.$currencyDefault->symbol:'');

				
				$html_objects .= '<tr>
					<td>
						'. date_i18n($setting_system['dateformat'].' '.get_option( 'time_format' ), $obj['report_timestamp']).'
					</td>
					<td>
						'.$obj_type.'
					</td>
					<td>
						<a href="'.$obj_link.'" target="_blank">'.$obj['post_title'].'</a>
					</td>
					<td>
						'.$debit_print.'
					</td>
					<td>
						'.$credit_print.'
					</td>
					<td>
						'.$balance_print.'
					</td>
				</tr>';
			}
			$html_objects .= '</tbody>
			</table>
			<div style="float:right; margin-right:15px;"><h3>'.$balance_print.'</h3></div>
			<div class="clear"></div>';
		}
		
		echo '<div style="width: 100%;">
		'.($html_objects).'
		
		</div>';
	}

	public static function get_form_filters($request) {

		$selectClients = fakturo_get_select_post(array(
											'echo' => 0,
											'post_type' => 'fktr_client',
											'show_option_none' => __('Select a client please.', FAKTURO_TEXT_DOMAIN ),
											'name' => 'client_id',
											'id' => 'client_id',
											'class' => '',
											'selected' => $request['client_id']
										));

		$array_range = array();
		$array_range['today'] = __( 'Today', FAKTURO_TEXT_DOMAIN );
		$array_range['yesterday'] = __( 'Yesterday', FAKTURO_TEXT_DOMAIN );
		$array_range['this_week'] = __( 'This Week', FAKTURO_TEXT_DOMAIN );
		$array_range['last_week'] = __( 'Last Week', FAKTURO_TEXT_DOMAIN );
		$array_range['this_month'] = __( 'This Month', FAKTURO_TEXT_DOMAIN );
		$array_range['last_month'] = __( 'Last Month', FAKTURO_TEXT_DOMAIN );
		$array_range['this_quarter'] = __( 'This Quarter', FAKTURO_TEXT_DOMAIN );
		$array_range['last_quarter'] = __( 'Last Quarter', FAKTURO_TEXT_DOMAIN );
		$array_range['this_year'] = __( 'This Year', FAKTURO_TEXT_DOMAIN );
		$array_range['last_year'] = __( 'Last Year', FAKTURO_TEXT_DOMAIN );
		$array_range['other'] = __( 'Custom', FAKTURO_TEXT_DOMAIN );
		/*
		* These filters can be used to add or update range values on select html.
		*/
		$array_range = apply_filters('report_filters_range', $array_range, $request);

		$select_range_html = '<select name="range" id="range">';
		foreach ($array_range as $key => $value) {
			$select_range_html .= '<option value="'.$key.'" '.selected($key, $request['range'], false).'>'.$value.'</option>';
		}
		$select_range_html .= '</select>';

		$return_html = '<div id="div_filter_form" style="padding:5px;">
			<form name="filter_form" method="get" action="'.admin_url('admin.php').'">
				<input type="hidden" name="page" value="fakturo_reports"/>
				<input type="hidden" name="sec" value="'.$request['sec'].'"/>
				'.$select_range_html.'
				'.$selectClients.'
				
				<input type="submit" class="button-secondary" value="'.__( 'Filter', FAKTURO_TEXT_DOMAIN ).'"/>
				
				<a class="button-secondary right" href="'.admin_url('admin-post.php?action=print_invoice').'">'.__( 'CSV', FAKTURO_TEXT_DOMAIN ).'</a>
				<a class="button-secondary right" style="margin-right:10px;" href="'.admin_url('admin-post.php?action=client_account_movements_print_pdf&'.http_build_query($request)).'">'.__( 'PDF', FAKTURO_TEXT_DOMAIN ).'</a>
				
			</form>
		</div>';

		echo $return_html;
	}
	/**
	* Print HTML on report page content.
	* @access public
	* @global wpdb $wpdb WordPress database abstraction object.
	* @param Array $return to update or add new objects.
	* @param Array $request of values the $_REQUEST filtered.
	* @param Array $ranges of ranges on timestamp to get objects.
	* @return Array of objects.
	* @since 0.6
	*/
	public static function get_objects($return, $request, $ranges) {
		global $wpdb;
		$sql = sprintf("SELECT p.ID, pm.meta_key, pm.meta_value as timestamp_value, p.post_type as post_type FROM {$wpdb->posts} as p
		LEFT JOIN {$wpdb->postmeta} as pm ON p.ID = pm.post_id
        WHERE 
        pm.meta_key = 'date'
		AND p.post_status = 'publish'
		AND (p.post_type = 'fktr_sale' OR p.post_type = 'fktr_receipt')
		AND pm.meta_value >= '%s'
		AND pm.meta_value < '%s'
        GROUP BY p.ID 
		", $ranges['from'], $ranges['to']);
		$results = $wpdb->get_results($sql, OBJECT);
		if (!empty($results)) {
			$return = $results;
		}
		return $return;
	}
}
/**
 * Execute all hooks on client_summmary
 */
client_account_movements::hooks();
?>