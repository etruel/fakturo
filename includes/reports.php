<?php
/**
 * Fakturo Reports Class.
 *
 * @package Fakturo
 * @subpackage Reports
 *
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Reports class.
 *
 * @since 0.6
 */
class reports {
	/**
	 * Add hooks for reports.
	 */
	public static function hooks() {
		
		add_action('all_admin_notices', array(__CLASS__, 'tabs'), 1, 0 );
		add_filter('fktr_reports_ranges_timestamp', array(__CLASS__, 'default_timestand_ranges'), 1, 2);
		add_action('admin_print_scripts', array(__CLASS__, 'scripts'));
		add_action('admin_print_styles', array(__CLASS__, 'styles'));
		self::includes();
	}
	public static function includes() {
		require_once FAKTURO_PLUGIN_DIR . 'includes/reports/sales.php';
		require_once FAKTURO_PLUGIN_DIR . 'includes/reports/client_summary.php';
		require_once FAKTURO_PLUGIN_DIR . 'includes/reports/client_incomes.php';
		require_once FAKTURO_PLUGIN_DIR . 'includes/reports/client_account_movements.php';
		require_once FAKTURO_PLUGIN_DIR . 'includes/reports/stock_products.php';
		require_once FAKTURO_PLUGIN_DIR . 'includes/reports/expenses_summary.php';
	}
	/**
	 * Print the page the reports.
	 */
	public static function page() {
		global $current_screen; 
		$request = wp_parse_args($_REQUEST, self::default_request());
		$ranges = array();
		$ranges['from'] = 0;
		$ranges['to'] = 0;
		
		
		/*
		* This filter can be used to create or update timestamp ranges.
		* $ranges will be used by get_object_chart()
		*/
		$ranges = apply_filters('fktr_reports_ranges_timestamp', $ranges, $request);
		$access = self::access_tab($request);
		if (!$access) {
			echo '<div class="postbox" style="margin-top:10px; padding:30px;"><h2>'.__( "Sorry, you don't have access to this page.", 'fakturo' ).'</h2></div>';
			return true;
		}
		/*
		* Executing hook to add content before report page. 
		*/
		do_action('report_page_before_content_'.$request['sec'], $request, $ranges);
		
		echo '<div class="wrap"><div class="postbox">';
		//var_export('report_page_content_'.$request['sec']);
			/*
			* Executing hook to add content on report page. 
			*/
			do_action('report_page_content_'.$request['sec'], $request, $ranges);
		echo '</div></div>';
	}

	/**
	* Enqueue all scripts elements on reports page.
	* @global $current_screen to get the screen obect.
	*/
	public static function scripts() {
		global $current_screen;  
		if ($current_screen->id == "fakturo_page_fakturo_reports") {
			wp_enqueue_script('fakturo_chartjs', FAKTURO_PLUGIN_URL . 'assets/js/chartjs/Chart.bundle.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			
		
		}
	}
	
	
	
	/**
	 * Enqueue all styles elements on reports page.
	 */
										   
	public static function styles() {
	
	}
	/**
	* Get the object ids by sec request.
	* @param $request Array of values the $_REQUEST filtered.
	* @param $ranges Array of ranges on timestamp to get objects.
	* @return Array of ids or a empty array on failed.
	*/
	public static function get_objects($request, $ranges, $limit = '') {
		$return = array();
		if ($request['sec'] == 'sales') {
			$return = get_sales_on_range($ranges['from'], $ranges['to']);
		}
		/**
		* Can filter and add objects to use on sections.
		*/
		$return = apply_filters('get_objects_reports_'.$request['sec'], $return, $request, $ranges, $limit);
		return $return;
	}
	
	/**
	* Get user access to current section.
	* @param $request Array of values the $_REQUEST filtered.
	* @return TRUE if the user has access and returns, 
	* FALSE if user does not have access.
	*/
	public static function access_tab($request) {
		
		$current_tab = self::get_tabs($request['sec']);
		$user_access = true;
		
		if (!$current_tab) {
			$user_access = false;
		} else {
			if (isset($current_tab[$request['sec']]['cap'])) {
				if (!current_user_can($current_tab[$request['sec']]['cap'])) {
					$user_access = false;
				}
			} else if (isset($current_tab['default']['cap'])) {
				if (!current_user_can($current_tab['default']['cap'])) {
					$user_access = false;
				}
			} else {
				$user_access = false;
			}
		}
		return $user_access;
	}
	/**
	* Function used by fktr_reports_ranges_timestamp filter to get the default timestamp ranges.
	* @param $request Array of values the $_REQUEST filtered.
	* @param $ranges Array of ranges on timestamp to get objects.
	* @return Array $ranges with "from, to" keys and its timestamp.
	*/
	public static function default_timestand_ranges($ranges, $request) {

		$current_time = current_time('timestamp');

		if ($request['range'] == 'today') {
			$ranges['from'] = strtotime('midnight', $current_time);
			$ranges['to'] =  $ranges['from']+(DAY_IN_SECONDS-1);
		}
		if ($request['range'] == 'yesterday') {
			$ranges['from'] = strtotime('midnight', strtotime('yesterday', $current_time));
			$ranges['to'] =  $ranges['from']+(DAY_IN_SECONDS-1);
		}
		// The week starts on the day of the site settings: count back from today's midnight (PHP's "Sunday this week" is the end of a Monday-based week).
		$days_into_week = ((int) gmdate('w', $current_time) - (int) get_option('start_of_week', 0) + 7) % 7;
		$week_start = strtotime('midnight', $current_time) - $days_into_week * DAY_IN_SECONDS;
		if ($request['range'] == 'this_week') {
			$ranges['from'] = $week_start;
			$ranges['to'] =  $current_time;
		}
		if ($request['range'] == 'last_week') {
			$ranges['from'] = $week_start - WEEK_IN_SECONDS;
			$ranges['to'] =  $week_start - 1;
		}
		if ($request['range'] == 'this_month') {
			$ranges['from'] = strtotime('first day of this month midnight', $current_time);
			$ranges['to'] = $current_time;
		}
		if ($request['range'] == 'last_month') {
			$ranges['from'] = strtotime('first day of last month midnight', $current_time);
			$ranges['to'] = strtotime('last day of last month 23:59:59', $current_time);
		}
		if ($request['range'] == 'this_quarter' || $request['range'] == 'last_quarter') {
			// The dates are the wall clock of the site written as UTC, so $current_time is read with gmdate().
			$current_month = (int) gmdate('n', $current_time);
			$year = (int) gmdate('Y', $current_time);
			$quarter_start = 3 * (int) floor(($current_month - 1) / 3) + 1;
			if ($request['range'] == 'last_quarter') {
				$quarter_start -= 3;
				if ($quarter_start < 1) {
					$quarter_start += 12;
					$year--;
				}
			}
			$ranges['from'] = gmmktime(0, 0, 0, $quarter_start, 1, $year);
			$ranges['to'] = gmmktime(23, 59, 59, $quarter_start + 2, (int) gmdate('t', gmmktime(0, 0, 0, $quarter_start + 2, 1, $year)), $year);
		}
		if ($request['range'] == 'this_year') {
			$ranges['from'] = gmmktime(0, 0, 0, 1, 1, (int) gmdate('Y', $current_time));
			$ranges['to'] = gmmktime(23, 59, 59, 12, 31, (int) gmdate('Y', $current_time));
		}
		if ($request['range'] == 'last_year') {
			$ranges['from'] = gmmktime(0, 0, 0, 1, 1, (int) gmdate('Y', $current_time) - 1);
			$ranges['to'] = gmmktime(23, 59, 59, 12, 31, (int) gmdate('Y', $current_time) - 1);
		}
		if ($request['range'] == 'other'){
			   // Verificar si existen las fechas personalizadas
			   if (!empty($request['from_date']) && !empty($request['to_date'])) {
				$from_date = sanitize_text_field($request['from_date']);
				$to_date = sanitize_text_field($request['to_date']);
		
				// Convertir las fechas a formato timestamp
				$ranges['from'] = strtotime($from_date . ' 00:00:00 UTC');
				$ranges['to'] = strtotime($to_date . ' 23:59:59 UTC');
				if ($ranges['from'] === false || $ranges['to'] === false) {
					$ranges['from'] = gmmktime(0, 0, 0, 1, 1, (int) gmdate('Y', $current_time));
					$ranges['to'] = $current_time;
				}
			} else {
				// Manejo en caso de que falte alguna de las fechas
				$ranges['from'] = gmmktime(0, 0, 0, 1, 1, (int) gmdate('Y', $current_time)); // Valor por defecto si no se ingresan las fechas
				$ranges['to'] = $current_time;
			}
		}
		
		return $ranges;
	}
	/**
	* Get the defaults values of a request.
	* @return Array $array with default values.
	*/
	public static function default_request() {
		$array = array(
			'sec' => 'sales',
			'range' => 'this_month',
			'range_f' => '',
			'range_t' => '',
			'client_id' => '0',
			'product_id' => '0',
			'show_details' => '0',
		);
		$array = apply_filters('report_default_requests_values', $array);
		return $array;
	}
	/**
	* get tabs used on reports.
	* @param $tab if it is a key tab only that tab is returned, Otherwise all tabs will be returned.
	* @return Array $sections_tabs with tabs values.
	*/
	public static function get_tabs($tab = false) {
		$sections_tabs = array(
			'sales' => apply_filters('ftkr_report_sales_sections', array( 
				'default' => array('text' => __( '​​Sales', 'fakturo' ), 'sec' => 'sales', 'cap' => 'fktr_report_sales')
			)),
			
			'clients' => apply_filters('ftkr_report_clients_sections', array( 
				'client_summary' =>  array('text' => __( 'Summary', 'fakturo' ), 'sec' => 'client_summary', 'cap' => 'fktr_report_client_summary'),
				'client_incomes' =>  array('text' => __( 'Incomes', 'fakturo' ), 'sec' => 'client_incomes', 'cap' => 'fktr_report_client_incomes'),
				'client_account_movements' =>  array('text' => __( 'Client&#x27;s account', 'fakturo' ), 'sec' => 'client_account_movements', 'cap' => 'fktr_report_client_account_movements'),
				'default' =>  array('text' => __( 'Clients', 'fakturo' ), 'sec' => 'client_summary', 'cap' => 'fktr_report_client_summary')                
			)),
			
			'stock_products' => apply_filters('ftkr_report_stok_products_sections', array(
				'stock_products' =>  array('text' => __( 'Summary', 'fakturo' ), 'sec' => 'stock_products', 'cap' => 'fktr_report_client_summary'),
				'default' =>  array('text' => __( 'Products', 'fakturo' ), 'sec' => 'stock_products', 'cap' => 'fktr_report_client_summary')                
			)),

			'salesman' => apply_filters('ftkr_report_expenses_sections', array(
				'expenses_summary' => array('text' => __( 'Summary', 'fakturo' ), 'sec' => 'expenses_summary', 'cap' => 'fktr_report_expenses'),
				'default' => array('text' => __( 'Vendedores', 'fakturo' ), 'sec' => 'expenses_summary', 'cap' => 'fktr_report_expenses')
			)),
	
		);
		/* 
		* These filters can be used to add or update tab values.
		*/
		$sections_tabs = apply_filters('ftkr_report_tabs_sections', $sections_tabs);
		if (!$tab) {
			return $sections_tabs;
		} else {
			if (isset($sections_tabs[$tab])) {
				return $sections_tabs[$tab];
			} else {
				foreach ($sections_tabs as $k => $v) {
					foreach ($v as $ks => $vs) {
						if ($tab == $ks) {
							return $sections_tabs[$k];
						}
					}
				}
				return false;
			}
		}
	}
	
	
	/**
	* Print the HTML of tabs, fired by all_admin_notices hook.
	* @global $current_screen to get the screen obect.
	*/
	public static function tabs() {
	//var_export(get_tabs());
		global $current_screen;  
		if ($current_screen->id != "fakturo_page_fakturo_reports") {
			return true;
		}
		$request = wp_parse_args($_REQUEST, self::default_request());
		
		$all_tabs = self::get_tabs();
		$sections_tabs = array();
		foreach ($all_tabs as $key => $value) {
			$sections_tabs[$key] = array();
			foreach ($value as $keys => $values) {
		//var_export($all_tabs[$key][$keys]['cap']);
				if (current_user_can($all_tabs[$key][$keys]['cap'])) {
					$sections_tabs[$key][$keys] = $values;
				}
			}
		}
		
		
		$print_tabs = false;
		foreach ($sections_tabs as $tabs_mains) {
			foreach ($tabs_mains as $sections) {
				if($request['sec'] == $sections['sec']) {
					$print_tabs = true;
					break;
				}
			}
		}
		if($print_tabs) {
			echo '<div class="wrap">';
			echo '<h2 class="nav-tab-wrapper fktr-settings-tabs">';
			$current_tab = 'general';
			foreach ($sections_tabs as $tab_id => $tabs_mains) {
				$tab_url = admin_url('admin.php?page=fakturo_reports&sec='.$tabs_mains['default']['sec']);
				$tab_name = $tabs_mains['default']['text']; 
				foreach ($tabs_mains as $sections) {
					if ($request['sec'] == $sections['sec']){
						$current_tab = $tab_id;
						$active = ' nav-tab-active';
						break;
					} else  {
						$active = '';
					} 
				}
				echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab_name ) . '" class="nav-tab' . $active . '">' . esc_html( $tab_name ) . '</a>';
			}
			echo '</h2>';
			
			echo '<div class="fktr-sections"><ul class="subsubsub">';
			$delimiter = '';
			foreach ($sections_tabs[$current_tab] as $sec_id => $sections) {
				if ($sec_id != 'default') {
					$sec_url = admin_url('admin.php?page=fakturo_reports&sec='.$sections['sec']);
					$active = ($request['sec'] == $sections['sec'] ) ?  ' current' : '';
					echo '<li>'.$delimiter.'<a href="' . esc_url( $sec_url ) . '" title="' . esc_attr( $sections['text'] ) . '" class="' . $active . '">' . esc_html( $sections['text'] ) . '</a></li>';
					$delimiter = ' | ';
				}
			}
			
			echo '</ul></div>';
			echo '</div>';/* .wrap end */
		}
	}
}
/*
* Execute hooks.
*/
reports::hooks();
?>