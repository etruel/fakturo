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
		add_action('admin_post_client_account_movements_download_csv', array(__CLASS__, 'download_csv'));
	}
	/**
	* Static function download_csv
	* @access public
	* @return void
	* @since 0.6
	*/
	public static function download_csv() {
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
			$total_html_print .= '<div class="postbox" style="margin-top:10px; padding:30px;"><h2>'.__( "Sorry, you don't have access to this page.", 'fakturo' ).'</h2></div>';
			echo $total_html_print;
			return true;
		}
		$setting_system = get_option('fakturo_system_options_group', false);
		$currencyDefault = get_fakturo_term($setting_system['currency'], 'fktr_currencies');
		if (is_wp_error($currencyDefault)) {
			$total_html_print .= '<p>'.__( 'Account Movements needs the default currency on system settings.', 'fakturo' ).'</p>';
			echo $total_html_print;
			return true;
		}
		$default_array_data = array('', '', '', '', '', '');
		$array_data = array();
		
		$new_array = $default_array_data;
		$html_client_data = '';
		if (is_numeric($request['client_id']) && $request['client_id'] > 0) {
			$client_data = fktrPostTypeClients::get_client_data($request['client_id']);
			
			$new_array[0] = __('Client', 'fakturo' ).': '.$client_data['post_title'];
			
		} else {
			$total_html_print = '<div style="margin-left:15px;"><h3>'.__('Select a client please.', 'fakturo' ).'</h3></div>';
			echo $total_html_print;
			return false;
		}
		

		$objects_client = client_summmary::get_objects_client($request, $ranges, false);
		$documents_values = $objects_client['documents_values'];

		$new_array[5] = sprintf(__('Date: since %s til %s', 'fakturo' ), date_i18n($setting_system['dateformat'], $ranges['from']), date_i18n($setting_system['dateformat'], $ranges['to']));
		$array_data[] = $new_array;
		$new_array = $default_array_data;
		if (!empty($objects_client['objects'])) {

			$new_array[0] = __('Date', 'fakturo');
			$new_array[1] = __('Type', 'fakturo');
			$new_array[2] = __('Reference', 'fakturo');
			$new_array[3] = __('Debit', 'fakturo');
			$new_array[4] = __('Credit', 'fakturo');
			$new_array[5] = __('Balance', 'fakturo');
			$array_data[] = $new_array;
			$new_array = $default_array_data;

			$balance = 0;
			foreach ($objects_client['objects'] as $obj) {

				$obj_type = '';
				$obj_link = admin_url('post.php?post='.$obj['ID'].'&action=edit');
				
				if ($obj['post_type']=='fktr_sale') {
					$obj_type = __('Invoice', 'fakturo');
					
				} else {
					$obj_type = __('Receipt', 'fakturo');
					
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

				

				$new_array[0] = date_i18n($setting_system['dateformat'].' '.get_option( 'time_format' ), $obj['report_timestamp']);
				$new_array[1] = $obj_type;
				$new_array[2] = $obj['post_title'];
				$new_array[3] = $debit_print;
				$new_array[4] = $credit_print;
				$new_array[5] = $balance_print;
				$array_data[] = $new_array;
				$new_array = $default_array_data;
		
			}

			$new_array[5] = $balance_print;
			$array_data[] = $new_array;
			$new_array = $default_array_data;
			
		}
		header('Content-Type: application/excel');
		header('Content-Disposition: attachment; filename="client_account_movements.csv"');
		$out = fopen('php://output', 'w');
		foreach ($array_data as $k => $arr) {
			fputcsv($out, $arr, ';');
		}
		fclose($out);
		//print_r($array_data);
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
			$total_html_print .= '<div class="postbox" style="margin-top:10px; padding:30px;"><h2>'.__( "Sorry, you don't have access to this page.", 'fakturo' ).'</h2></div>';
			echo $total_html_print;
			return true;
		}
		$setting_system = get_option('fakturo_system_options_group', false);
		$currencyDefault = get_fakturo_term($setting_system['currency'], 'fktr_currencies');
		if (is_wp_error($currencyDefault)) {
			$total_html_print .= '<p>'.__( 'Account Movements needs the default currency on system settings.', 'fakturo' ).'</p>';
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
			
			
			$html_client_data = '<div style="width:50%; text-align: left; display: inline-block;"><h3>'.__('Client', 'fakturo' ).': '.$client_data['post_title'].'</h3></div>';
		} else {
			$total_html_print = '<div style="margin-left:15px;"><h3>'.__('Select a client please.', 'fakturo' ).'</h3></div>';
			echo $total_html_print;
			return false;
		}
		$total_html_print .= $html_client_data;

		$objects_client = client_summmary::get_objects_client($request, $ranges, false);
		$documents_values = $objects_client['documents_values'];

		$total_html_print .= '<div style="width:50%; text-align: right; display: inline-block;"><h3>'.sprintf(__('Date: since %s til %s', 'fakturo' ), date_i18n($setting_system['dateformat'], $ranges['from']), date_i18n($setting_system['dateformat'], $ranges['to'])).'</h3></div>';
		$html_objects = '<div style="clear: both;"><h2>No results with this filters</h2></div>';
		if (!empty($objects_client['objects'])) {
			$html_objects = '<table class="wp-list-table widefat fixed striped posts" style="width: 100%;">
				<thead>
				<tr>
					<td>
						'.__('Date', 'fakturo').'
					</td>
					<td>
						'.__('Type', 'fakturo').'
					</td>
					<td>
						'.__('Reference', 'fakturo').'
					</td>
					<td>
						'.__('Debit', 'fakturo').'
					</td>
					<td>
						'.__('Credit', 'fakturo').'
					</td>
					<td>
						'.__('Balance', 'fakturo').'
					</td>
				</tr>
				</thead>
				<tbody id="the-list">';
			$balance = 0;
			foreach ($objects_client['objects'] as $obj) {

				$obj_type = '';
				$obj_link = admin_url('post.php?post='.$obj['ID'].'&action=edit');
				
				if ($obj['post_type']=='fktr_sale') {
					$obj_type = __('Invoice', 'fakturo');
					
				} else {
					$obj_type = __('Receipt', 'fakturo');
					
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
		
		$pdf ->set_option('isRemoteEnabled', true);
		$pdf ->set_option('isHtml5ParserEnabled', true);

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
			echo '<p>'.__( 'Account Movements needs the default currency on system settings.', 'fakturo' ).'</p>';
			return true;
		}
		
		self::get_form_filters($request);
		
		$html_client_data = '';
		if (is_numeric($request['client_id']) && $request['client_id'] > 0) {
			$client_data = fktrPostTypeClients::get_client_data($request['client_id']);
			
			
			$html_client_data = '<div style="float:left; margin-left:15px;"><h3>'.__('Client', 'fakturo' ).': '.$client_data['post_title'].'</h3></div>';
		} else {
			echo '<div style="margin-left:15px;"><h3>'.__('Select a client please.', 'fakturo' ).'</h3></div>';
			return false;
		}
		echo $html_client_data;

		$objects_client = client_summmary::get_objects_client($request, $ranges, false);
		$documents_values = $objects_client['documents_values'];

		echo '<div style="float:right; margin-right:15px;"><h3>'.sprintf(__('Date: since %s til %s', 'fakturo' ), date_i18n($setting_system['dateformat'], $ranges['from']), date_i18n($setting_system['dateformat'], $ranges['to'])).'</h3></div>';
		$html_objects = '<div style="clear: both;"><h2>No results with this filters</h2></div>';
		if (!empty($objects_client['objects'])) {
			$html_objects = '<table class="wp-list-table widefat fixed striped posts">
				<thead>
				<tr>
					<td>
						'.__('Date', 'fakturo').'
					</td>
					<td>
						'.__('Type', 'fakturo').'
					</td>
					<td>
						'.__('Reference', 'fakturo').'
					</td>
					<td>
						'.__('Debit', 'fakturo').'
					</td>
					<td>
						'.__('Credit', 'fakturo').'
					</td>
					<td>
						'.__('Balance', 'fakturo').'
					</td>
				</tr>
				</thead>
				<tbody id="the-list">';
			$balance = 0;
			foreach ($objects_client['objects'] as $obj) {

				$obj_type = '';
				$obj_link = admin_url('post.php?post='.$obj['ID'].'&action=edit');
				
				if ($obj['post_type']=='fktr_sale') {
					$obj_type = __('Invoice', 'fakturo');
					
				} else {
					$obj_type = __('Receipt', 'fakturo');
					
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
											'show_option_none' => __('Select a client please.', 'fakturo' ),
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
	
		$date_inputs_html = '<div id="custom_dates" style="display:none;">';
		$date_inputs_html .= '<label for="from_date">' . __( 'From', 'fakturo' ) . '</label>';
		$date_inputs_html .= '<input type="date" name="from_date" id="from_date" value="'.$from_date.'" />';
		$date_inputs_html .= '<label for="to_date" style="margin-left:10px;">' . __( 'To', 'fakturo' ) . '</label>';
		$date_inputs_html .= '<input type="date" name="to_date" id="to_date" value="'.$to_date.'" />';
		$date_inputs_html .= '</div>';
	
		$return_html = '<div id="div_filter_form" style="padding:5px;">
        <form name="filter_form" method="get" action="'.admin_url('admin.php').'">
            <input type="hidden" name="page" value="fakturo_reports"/>
            <input type="hidden" name="sec" value="'.$request['sec'].'"/>
            '.$select_range_html.'
            '.$selectClients.'
            <label style="margin-left:10px;margin-right:10px;">
                <input type="checkbox" name="show_details" id="show_details" value="1" '.checked($request['show_details'], 1, false).'/>
                '.__( 'Show details', 'fakturo' ).'
            </label>
            '.$date_inputs_html.'
            <input type="submit" class="button-secondary" value="'.__( 'Filter', 'fakturo' ).'"/>
			
			
			<input id="print-table-pdf" type="button" class="button-secondary" value="'.__( 'Print/Save Table as PDF', 'fakturo' ).'"/>
			
			
			<input id="download-table-csv" type="button" class="button-secondary" value="'.__( 'Download Table as CSV', 'fakturo' ).'"/>
        </form>
    </div>';

    
    $return_html .= '<style>
        @media print {
            body > *:not(.wp-list-table.widefat.fixed.striped.posts) {
                display: none !important;
            }
            .wp-list-table.widefat.fixed.striped.posts {
                display: block !important;
                width: 100% !important;
                border: none !important;
            }
            .wp-list-table.widefat.fixed.striped.posts th,
            .wp-list-table.widefat.fixed.striped.posts td {
                border: 1px solid #000 !important;
            }
        }
    </style>';

	
	$return_html .= '<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>';
	$return_html .= '<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>';

$return_html .= '<script>
    document.getElementById("range").addEventListener("change", function() {
        if (this.value === "other") {
            document.getElementById("custom_dates").style.display = "block";
        } else {
            document.getElementById("custom_dates").style.display = "none";
        }
    });

  
    if (document.getElementById("range").value === "other") {
        document.getElementById("custom_dates").style.display = "block";
    }

    
    document.getElementById("print-table-pdf").addEventListener("click", function() {
        var table = document.querySelector(".wp-list-table.widefat.fixed.striped.posts");
        if (table) {
            
            var { jsPDF } = window.jspdf;
            var doc = new jsPDF();

            doc.text("", 10, 10);
            if (doc.autoTable) {
                doc.autoTable({ html: table });
            } else {
                doc.text("jsPDF autoTable plugin is not available", 10, 20);
            }

            doc.save("table.pdf");
        } else {
            alert("Table not found!");
        }
    });

    
    document.getElementById("download-table-csv").addEventListener("click", function() {
        var table = document.querySelector(".wp-list-table.widefat.fixed.striped.posts");
        if (table) {
            var rows = Array.from(table.querySelectorAll("tr"));
            var csvContent = "";
            
            rows.forEach(function(row) {
                var cols = Array.from(row.querySelectorAll("th, td"));
                var rowData = cols.map(function(col) {
                    return col.innerText.replace(/"/g, \'""\'); // Escapar comillas dobles
                }).join(",");
                
                csvContent += rowData + "\\n";
            });

           
            var blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
            var link = document.createElement("a");
            var url = URL.createObjectURL(blob);
            link.setAttribute("href", url);
            link.setAttribute("download", "table.csv");
            link.style.visibility = "hidden";
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        } else {
            alert("Table not found!");
        }
    });
</script>';
		
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
