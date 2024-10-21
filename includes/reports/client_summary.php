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
 * client_summmary class.
 *
 * @since 0.6
 */
class client_summmary {
	/**
	 * Add hooks for reports.
	 */
	public static function hooks() {
		add_action('report_page_before_content_client_summary', array(__CLASS__, 'before_content'), 10, 2);
		add_action('report_page_content_client_summary', array(__CLASS__, 'content'), 10, 2);
		add_filter('get_objects_reports_client_summary', array(__CLASS__, 'get_objects'), 10, 3);
	}
	/**
	* Print HTML before content on report page.
	* @param $request Array of values the $_REQUEST filtered.
	* @param $ranges Array of ranges on timestamp to get objects.
	*/
	public static function before_content($request, $ranges) {
		wp_enqueue_script( 'jquery-select2', FAKTURO_PLUGIN_URL . 'assets/js/jquery.select2.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
		wp_enqueue_script('fakturo_reports_client_summary', FAKTURO_PLUGIN_URL . 'assets/js/reports-client-summary.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
	}
	public static function get_objects_client($request, $ranges, $subtract_the_sum = true) {
		$setting_system = get_option('fakturo_system_options_group', false);
		$currencyDefault = get_fakturo_term($setting_system['currency'], 'fktr_currencies');
		if (is_wp_error($currencyDefault)) {
			return false;
		}
		$objects = reports::get_objects($request, $ranges);
		$new_objects = array();
		$default_document = array('subtotal' => 0, 'total' => 0);
		$documents_values = array();
		$array_taxes = array();
		if (!empty($objects)) {
			
			foreach ($objects as $obj) {

				$obj_type = '';
				$subtotal_print = 0;
				$total_print = 0;
				$tax = 0;
				if ($obj->post_type=='fktr_sale') {
					$object_data = fktrPostTypeSales::get_sale_data($obj->ID);
					if (is_numeric($request['client_id']) && $request['client_id'] > 0) {
						if ($object_data['client_id'] != $request['client_id']) {
							continue;
						}
					}
					$discriminates_taxes = false;
					if ($object_data['invoice_type'] > 0) {
						$term_invoice_type = get_fakturo_term($object_data['invoice_type'], 'fktr_invoice_types');		
						if (!is_wp_error($term_invoice_type)) {
							if ($term_invoice_type->discriminates_taxes) {
								$discriminates_taxes = true;
							}
						} else {
							continue;
						}
					}

					$sum = false;
					if (!empty($term_invoice_type->sum)) {
						$sum = true;
					}
					$new_sum = true;
					if ($subtract_the_sum) {
						if ($sum) {
							$new_sum = false;
						} else {
							$new_sum = true;
						}
					} else {
						if ($sum) {
							$new_sum = true;
						} else {
							$new_sum = false;
						}
					}
					if (!isset($documents_values[$term_invoice_type->name])) {
						$documents_values[$term_invoice_type->name] = $default_document;
					}

					if ($discriminates_taxes) {
						if (!empty($object_data['taxes_in_products'])) {
							foreach ($object_data['taxes_in_products'] as $key => $value) {
								$taxPorcent = 0;
								$taxName = 'Tax';
								if ($key > 0) {
									$term_tax = get_fakturo_term($key, 'fktr_tax');
									if(!is_wp_error($term_tax)) {
										$taxPorcent = $term_tax->percentage;
										$taxName = $term_tax->name;
									} 
								} else {
									if ($object_data['client_data']['tax_condition'] > 0) {
										$term_tax_condition = get_fakturo_term($object_data['client_data']['tax_condition'], 'fktr_tax_conditions');
										if(!is_wp_error($term_tax_condition)) {
											if ($term_tax_condition->overwrite_taxes) {
												//$taxPorcent = $term_tax_condition->tax_percentage;
											}
										} 
									}
								}
								if (!isset($array_taxes[$key])) {
									$array_taxes[$key] = array('name' =>$taxName, 'total' => 0, 'porcent' => $taxPorcent, 'id' => $key);
								}
								if (!isset($default_document[$key.'tax'])) { 
									$default_document[$key.'tax'] = 0;
									
									foreach ($documents_values as $kd => $doc) {
										$documents_values[$kd] = array_merge($default_document, $documents_values[$kd]);	
									}
								}

								$new_value = fakturo_transform_money($object_data['invoice_currency'], $setting_system['currency'], $value);
								if ($new_sum) {
									$array_taxes[$key]['total']  = $array_taxes[$key]['total']+$new_value;
									$tax = $tax+$new_value;
									$documents_values[$term_invoice_type->name][$key.'tax'] = $new_value;
								} else {
									$array_taxes[$key]['total']  = $array_taxes[$key]['total']-$new_value;
									$tax = $tax-$new_value;
									$documents_values[$term_invoice_type->name][$key.'tax'] = -$new_value;
								}

								
							}
						}


					}

					$obj_type = __('Invoice', 'fakturo');


					if ($new_sum) {
						$subtotal = fakturo_transform_money($object_data['invoice_currency'], $setting_system['currency'], $object_data['in_sub_total']);
						$total = fakturo_transform_money($object_data['invoice_currency'], $setting_system['currency'], $object_data['in_total']);
						
					} else {
						$subtotal = -fakturo_transform_money($object_data['invoice_currency'], $setting_system['currency'], $object_data['in_sub_total']);
						$total = -fakturo_transform_money($object_data['invoice_currency'], $setting_system['currency'], $object_data['in_total']);
						
					}
					$documents_values[$term_invoice_type->name]['subtotal'] = $documents_values[$term_invoice_type->name]['subtotal']+$subtotal;
					$documents_values[$term_invoice_type->name]['total'] = $documents_values[$term_invoice_type->name]['total']+$total;
					

				} else {
					$object_data = fktrPostTypeReceipts::get_receipt_data($obj->ID);
					if (is_numeric($request['client_id']) && $request['client_id'] > 0) {
						if ($object_data['client_id'] != $request['client_id']) {
							continue;
						}
					}
					if (!isset($documents_values['Receipts'])) {
						$documents_values['Receipts'] = $default_document;
					}
					$sum = true;
					$new_sum = true;
					if ($subtract_the_sum) {
						if ($sum) {
							$new_sum = false;
						} else {
							$new_sum = true;
						}
					} else {
						if ($sum) {
							$new_sum = true;
						} else {
							$new_sum = false;
						}
					}
					if ($new_sum) {
						$subtotal = fakturo_transform_money($object_data['currency_id'], $setting_system['currency'], $object_data['total_to_pay']);
						$total = fakturo_transform_money($object_data['currency_id'], $setting_system['currency'], $object_data['total_to_pay']);
						
					} else {
						$subtotal = -fakturo_transform_money($object_data['currency_id'], $setting_system['currency'], $object_data['total_to_pay']);
						$total = -fakturo_transform_money($object_data['currency_id'], $setting_system['currency'], $object_data['total_to_pay']);
						
					}
					$obj_type = __('Receipt', 'fakturo');
					
					$documents_values['Receipts']['subtotal'] = $documents_values['Receipts']['subtotal']+$subtotal;
					$tax = 0;
					
					$documents_values['Receipts']['total'] = $documents_values['Receipts']['total']+$total;

				}
				$new_object = $object_data;
				$new_object['report_subtotal'] = $subtotal;
				$new_object['report_tax'] = $tax;
				$new_object['report_total'] = $total;
				$new_object['report_timestamp'] = $obj->timestamp_value;
				
				$new_objects[] = $new_object;
			}
			
		}
		return array('objects' => $new_objects, 'documents_values' => $documents_values, 'array_taxes' => $array_taxes);
	}
	/**
	* Print HTML on report page content.
	* @param $request Array of values the $_REQUEST filtered.
	* @param $ranges Array of ranges on timestamp to get objects.
	*/
	public static function content($request, $ranges) {
		$setting_system = get_option('fakturo_system_options_group', false);
		$currencyDefault = get_fakturo_term($setting_system['currency'], 'fktr_currencies');
		if (is_wp_error($currencyDefault)) {
			echo '<p>'.__( 'Client Summary needs the default currency on system settings.', 'fakturo' ).'</p>';
			return true;
		}
		
		self::get_form_filters($request);
		$objects_client = self::get_objects_client($request, $ranges);
		$documents_values = $objects_client['documents_values'];
		$total_documents = array('subtotal' => 0, 'total' => 0);
		$html_client_data = '';
		if (is_numeric($request['client_id']) && $request['client_id'] > 0) {
			$client_data = fktrPostTypeClients::get_client_data($request['client_id']);
			$client_link = admin_url('post.php?post='.$request['client_id'].'&action=edit');
			$country_name = __('No country', 'fakturo' );
			$country_data = get_fakturo_term($client_data['selected_country'], 'fktr_countries');
			if(!is_wp_error($country_data)) {
				$country_name = $country_data->name;
			}
			
			$state_name = __('No state', 'fakturo' );
			$state_data = get_fakturo_term($client_data['selected_state'], 'fktr_countries');
			if(!is_wp_error($state_data)) {
				$state_name = $state_data->name;
			}

			$html_client_data = '<div class="fktr_info-item"><h3>'.__('Client', 'fakturo' ).': '.$client_data['post_title'].'</h3></div>';
		}
		echo '<div class="fktr_report-info">' . $html_client_data;
		echo '<div class="fktr_info-item"><h3>'.sprintf(__('Date: since %s til %s', 'fakturo' ), date_i18n($setting_system['dateformat'].' '.get_option( 'time_format' ), $ranges['from']), date_i18n($setting_system['dateformat'].' '.get_option( 'time_format' ), $ranges['to'])).'</h3></div></div>';
		$html_objects = '<div style="clear: both; text-align: center;"><h2>'.__("No results with this filters").'</h2></div>';
		if (!empty($objects_client['objects'])) {
		
			$html_objects = '<div class="fktr_table-resp"><table class="wp-list-table widefat fixed striped posts" style="min-width: 1000px;">
				<thead>
				<tr>
					<td>
						'.__('Date', 'fakturo').'
					</td>
					<td>
						'.__('Type', 'fakturo').'
					</td>
					<td>
						'.__('Reference', 'fakturo').'</td>';
						if (is_numeric($request['client_id']) && $request['client_id'] < 0) {
							$html_objects .= '<td>'.__('Client', 'fakturo').'</td>';
						}
						$html_objects .=	'<td>
						'.__('Subtotal', 'fakturo').'
					</td>
					<td>
						'.__('Tax', 'fakturo').'
					</td>
					<td>
						'.__('Total', 'fakturo').'
					</td>
				</tr>
				</thead>
				<tbody id="the-list">';

			foreach ($objects_client['objects'] as $obj) {

				$obj_type = '';
				$obj_link = admin_url('post.php?post='.$obj['ID'].'&action=edit');
				$subtotal_print = 0;
				$total_print = 0;
				$tax = 0;
				if ($obj['post_type']=='fktr_sale') {
					$obj_type = __('Invoice', 'fakturo');
					$total_documents['subtotal'] = $total_documents['subtotal']+$obj['report_subtotal'];
					$total_documents['total'] = $total_documents['total']+$obj['report_total'];
				} else {
					$obj_type = __('Receipt', 'fakturo');
					$total_documents['subtotal'] = $total_documents['subtotal']+$obj['report_subtotal'];
					$total_documents['total'] = $total_documents['total']+$obj['report_total'];
				}
				$tax = $obj['report_tax'];
				$subtotal = $obj['report_subtotal'];
				$total = $obj['report_total'];
				$client_print = $obj['client_data']['name'] ?? 'No name available';

				$subtotal_print = (($setting_system['currency_position'] == 'before')?$currencyDefault->symbol.' ':'').''.number_format($subtotal, $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']).''.(($setting_system['currency_position'] == 'after')?' '.$currencyDefault->symbol:'');
				
				$total_print = (($setting_system['currency_position'] == 'before')?$currencyDefault->symbol.' ':'').''.number_format($total, $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']).''.(($setting_system['currency_position'] == 'after')?' '.$currencyDefault->symbol:'');
				
				$tax_print = '-';
				if ($tax != 0) {
					$tax_print = (($setting_system['currency_position'] == 'before')?$currencyDefault->symbol.' ':'').''.number_format($tax, $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']).''.(($setting_system['currency_position'] == 'after')?' '.$currencyDefault->symbol:'');
				}
				
				$html_objects .= '<tr>
					<td>
						'. date_i18n($setting_system['dateformat'].' '.get_option( 'time_format' ), $obj['report_timestamp']).'
					</td>
					<td>
						'.$obj_type.'
					</td>
					<td>
						<a href="'.$obj_link.'" target="_blank">'.$obj['post_title'].'</a>
					</td>';
					if (is_numeric($request['client_id']) && $request['client_id'] < 0){
					$html_objects .='<td>
						'.$client_print.'
					</td>';
					}
			$html_objects .= '<td>
						'.$subtotal_print.'
					</td>
					<td>
						'.$tax_print.'
					</td>
					<td>
						'.$total_print.'
					</td>
				</tr>';
			}
			$html_objects .= '</tbody>
			</table></div>';
		}
		$html_totals_data = '';
		if (!empty($objects_client['objects'])) {
			$array_taxes = $objects_client['array_taxes'];
			if (!isset($array_taxes)) {
				$array_taxes = array();
			}
			$html_totals_data = '<div class="fktr_report-summary"><table class="wp-list-table widefat fixed striped posts">
					<thead>
					<tr>
						<td>
							'.__('Documents', 'fakturo').'
						</td>';
			$html_totals_data .= '<td>
							'.__('Subtotal', 'fakturo').'
						</td>';
			foreach ($array_taxes as $tk => $tx) {
				$html_totals_data .= '<td>
							'.$tx['name'].' ('.$tx['porcent'].'%)
						</td>';
			}
			$html_totals_data .= '<td>
							'.__('Total', 'fakturo').'
						</td>';

	 
			$html_totals_data .= '</tr>
					</thead>
					<tbody id="the-list">
						';
			foreach ($documents_values as $nd => $valdoc) {
				$html_totals_data .= '<tr>
						<td>
							'.$nd.'
						</td>
						<td>
							'.(($setting_system['currency_position'] == 'before')?$currencyDefault->symbol.' ':'').''.number_format($valdoc['subtotal'], $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']).''.(($setting_system['currency_position'] == 'after')?' '.$currencyDefault->symbol:'').'
						</td>';
						foreach ($array_taxes as $tkd => $txd) {
							$html_totals_data .= '<td>
										'.(($setting_system['currency_position'] == 'before')?$currencyDefault->symbol.' ':'').''.number_format($valdoc[$tkd.'tax'], $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']).''.(($setting_system['currency_position'] == 'after')?' '.$currencyDefault->symbol:'').'
									</td>';
						}

						$html_totals_data .= '<td>
							'.(($setting_system['currency_position'] == 'before')?$currencyDefault->symbol.' ':'').''.number_format($valdoc['total'], $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']).''.(($setting_system['currency_position'] == 'after')?' '.$currencyDefault->symbol:'').'
						</td>
						</tr>';
			}

			$html_totals_data .= '<tr>
						<td>
							
						</td>
						<td>
							'.(($setting_system['currency_position'] == 'before')?$currencyDefault->symbol.' ':'').''.number_format($total_documents['subtotal'], $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']).''.(($setting_system['currency_position'] == 'after')?' '.$currencyDefault->symbol:'').'
						</td>';
						foreach ($array_taxes as $tk => $tx) {
							$html_totals_data .= '<td>
										'.(($setting_system['currency_position'] == 'before')?$currencyDefault->symbol.' ':'').''.number_format($tx['total'], $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']).''.(($setting_system['currency_position'] == 'after')?' '.$currencyDefault->symbol:'').'
									</td>';
						}
						

						$html_totals_data .= '<td>
							'.(($setting_system['currency_position'] == 'before')?$currencyDefault->symbol.' ':'').''.number_format($total_documents['total'], $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']).''.(($setting_system['currency_position'] == 'after')?' '.$currencyDefault->symbol:'').'
						</td>
						</tr>';


			$html_totals_data .= '
					</tbody>
				</table></div><div style="clear:both;"> </div>';
		}
		echo '<div class="fktr_reports_container">
		'.($request['show_details']?$html_objects:'').'
		'.$html_totals_data.'
		</div>';
	}

	public static function get_form_filters($request) {

		$selectClients = fakturo_get_select_post(array(
											'echo' => 0,
											'post_type' => 'fktr_client',
											'show_option_none' => __('All clients', 'fakturo' ),
											'name' => 'client_id',
											'id' => 'client_id',
											'class' => '',
											'selected' => $request['client_id']
										));

		$array_range = array();
		$array_range['today'] = __( 'Today', 'fakturo' );
		$array_range['yesterday'] = __( 'Yesterday', 'fakturo' );
		$array_range['this_week'] = __( 'This Week', 'fakturo' );
		$array_range['last_week'] = __( 'Last Week', 'fakturo' );
		$array_range['this_month'] = __( 'This Month', 'fakturo' );
		$array_range['last_month'] = __( 'Last Month', 'fakturo' );
		$array_range['this_quarter'] = __( 'This Quarter', 'fakturo' );
		$array_range['last_quarter'] = __( 'Last Quarter', 'fakturo' );
		$array_range['this_year'] = __( 'This Year', 'fakturo' );
		$array_range['last_year'] = __( 'Last Year', 'fakturo' );
		$array_range['other'] = __( 'Custom', 'fakturo' );
		/*
		* These filters can be used to add or update range values on select html.
		*/
		$array_range = apply_filters('report_filters_range', $array_range, $request);

		$select_range_html = '<select name="range" id="range">';
		foreach ($array_range as $key => $value) {
			$select_range_html .= '<option value="'.$key.'" '.selected($key, $request['range'], false).'>'.$value.'</option>';
		}
		$select_range_html .= '</select>';
	
		// Adding date inputs for custom range
		$from_date = isset($request['from_date']) ? esc_attr($request['from_date']) : '';
		$to_date = isset($request['to_date']) ? esc_attr($request['to_date']) : '';
	
		$date_inputs_html = '<div id="custom_dates" class="fktr_date-ranges" style="display:none;">';
		$date_inputs_html .= '<div class="fktr_date-from"><label for="from_date">' . __( 'From', 'fakturo' ) . '</label>';
		$date_inputs_html .= '<input type="date" name="from_date" id="from_date" value="'.$from_date.'" /></div>';
		$date_inputs_html .= '<div class="fktr_date-from"><label for="to_date">' . __( 'To', 'fakturo' ) . '</label>';
		$date_inputs_html .= '<input type="date" name="to_date" id="to_date" value="'.$to_date.'" /></div>';
		$date_inputs_html .= '</div>';
	
		$return_html = '<div id="div_filter_form" style="padding:5px;">
			<form name="filter_form" method="get" action="'.admin_url('admin.php').'">
				<input type="hidden" name="page" value="fakturo_reports"/>
				<input type="hidden" name="sec" value="'.$request['sec'].'"/>
				'.$select_range_html.'
				'.$selectClients.'
				<label style="margin-left:10px;margin-right:10px;"><input type="checkbox" name="show_details" id="show_details" value="1" '.checked($request['show_details'], 1, false).'/>'.__( 'Show details', 'fakturo' ).'</label>
				'.$date_inputs_html.'
				<input type="submit" class="button-secondary" value="'.__( 'Filter', 'fakturo' ).'"/>
			</form>
		</div>';
	
		// JavaScript to show date inputs if "Custom" is selected
		$return_html .= '<script>
			document.getElementById("range").addEventListener("change", function() {
				if (this.value === "other") {
					document.getElementById("custom_dates").style.display = "block";
				} else {
					document.getElementById("custom_dates").style.display = "none";
				}
			});
	
			// Trigger on page load in case "Custom" is pre-selected
			if (document.getElementById("range").value === "other") {
				document.getElementById("custom_dates").style.display = "block";
			}
		</script>';

		echo $return_html;
	}
	/**
	* Print HTML on report page content.
	* @global wpdb $wpdb WordPress database abstraction object.
	* @param Array $return to update or add new objects.
	* @param Array $request of values the $_REQUEST filtered.
	* @param Array $ranges of ranges on timestamp to get objects.
	* @return Array of objects.
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
client_summmary::hooks();
?>