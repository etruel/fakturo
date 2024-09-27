<?php
/**
 * Fakturo sales_report Class.
 *
 * @package Fakturo
 * @subpackage Report
 *
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * sales_report class.
 *
 * @since 0.6
 */
class sales_report {
	/**
	 * Add hooks for reports.
	 */
	public static function hooks() {
		
		add_action('report_page_before_content_sales', array(__CLASS__, 'before_content'), 10, 2);
		add_action('report_page_content_sales', array(__CLASS__, 'content'), 10, 2);
		
	}
	/**
	* Print HTML before content on report page.
	* @param $request Array of values the $_REQUEST filtered.
	* @param $ranges Array of ranges on timestamp to get objects.
	*/
	public static function before_content($request, $ranges) {
		wp_enqueue_script('fakturo_reports_sales', FAKTURO_PLUGIN_URL . 'assets/js/reports-sales.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
		wp_localize_script('fakturo_reports_sales', 'chartjs_object',
							array(
								'data' => self::get_object_chart($request, $ranges),
								 ) 
							);
	}
	/**
	* Print HTML on report page content.
	* @param $request Array of values the $_REQUEST filtered.
	* @param $ranges Array of ranges on timestamp to get objects.
	*/
	public static function content($request, $ranges) {
		self::get_form_filters($request);
		echo '<div style="width: 100%;">
        			<canvas id="canvas"></canvas>
    			</div>';
	}
	/**
	* Get the HTML of filters form.
	* @param $request Array of values the $_REQUEST filtered.
	* @return String with HTML of form.
	*/
	public static function get_form_filters($request) {
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
	* Get the object to print on Javascript.
	* @param $request Array of values the $_REQUEST filtered.
	* @param $ranges Array of ranges on timestamp to get objects.
	* @return $data_chart array of objects stdClass with data that will be used on Front-end.
	*/
	public static function get_object_chart($request, $ranges) {
		$setting_system = get_option('fakturo_system_options_group', false);
		$current_tab = reports::get_tabs($request['sec']);
		$labels_by = 'days';
		$labels_interval = DAY_IN_SECONDS;
		if (($ranges['to']-$ranges['from']) > MONTH_IN_SECONDS) {
			$labels_by = 'month';
			$labels_interval = MONTH_IN_SECONDS;
		}
		$datasets = array();
		$datasets[0] = new stdClass();
		$datasets[0]->label = $current_tab['default']['text'];
	    $datasets[0]->fill = false;
	    $datasets[0]->lineTension = 0.1;
	    $datasets[0]->backgroundColor = "rgba(75,192,192,0.4)";
	    $datasets[0]->borderColor = "rgba(75,192,192,1)";
	    $datasets[0]->borderCapStyle = 'butt';
	    $datasets[0]->borderDash = array();
	    $datasets[0]->borderDashOffset = 0.0;
	    $datasets[0]->borderJoinStyle = 'miter';
	    $datasets[0]->pointBorderColor = "rgba(75,192,192,1)";
	    $datasets[0]->pointBackgroundColor = "#fff";
	    $datasets[0]->pointBorderWidth = 1;
	    $datasets[0]->pointHoverRadius = 5;
	    $datasets[0]->pointHoverBackgroundColor = "rgba(75,192,192,1)";
	    $datasets[0]->pointHoverBorderColor = "rgba(220,220,220,1)";
	    $datasets[0]->pointHoverBorderWidth = 2;
	    $datasets[0]->pointRadius = 1;
	    $datasets[0]->pointHitRadius = 10;
	    $datasets[0]->spanGaps = false;
	    $datasets[0]->data = array();
	    /*
		* This stdClass object is a format required by ChartJs.
		*/
		$labels = array();
		for($t=$ranges['from']; $t<=$ranges['to']; $t=$t+$labels_interval) {

			$from = strtotime('midnight', $t);
			$to   = $from + ($labels_interval - 1);
			if ($labels_by == 'month') {
				$from = strtotime('first day of this month', $t);
				$to = strtotime('last day of this month', $from);
				$labels_interval = (strtotime('first day of next month', $t)-$from);
			}
			$new_label = date($setting_system['dateformat'], $from);
			if ($labels_by == 'month') {
				$new_label = __(date('F', $from));
			}
			$labels[] = $new_label;
			$sec_objects = reports::get_objects($request, array('from' => $from, 'to' => $to));
			$new_data = 0;
			
			foreach ($sec_objects as $id_sale) {

				$sales_data = fktrPostTypeSales::get_sale_data($id_sale);
				$new_data = $new_data+fakturo_transform_money($sales_data['invoice_currency'], $setting_system['currency'], $sales_data['in_total']);
			}
			$datasets[0]->data[] = $new_data;
		}
		$data_chart = new stdClass();
		$data_chart->labels = $labels;
		$data_chart->datasets = $datasets;
		return $data_chart;
	}
}
/**
 * Execute all hooks on sales_report
 */
sales_report::hooks();

?>