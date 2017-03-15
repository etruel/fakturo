<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class reports {
	public static function hooks() {
		add_action( 'all_admin_notices', array(__CLASS__, 'tabs'), 1, 0 );
		add_filter('fktr_reports_ranges_timestamp', array(__CLASS__, 'default_timestand_ranges'), 1, 2);
		add_action('admin_print_scripts', array(__CLASS__, 'scripts'));
		add_action('admin_print_styles', array(__CLASS__, 'styles'));
	}
	
	public static function page() {
		global $current_screen; 
		//print_r($current_screen);
		$request = wp_parse_args($_REQUEST, self::default_request());
		$ranges = array();
		$ranges['from'] = 0;
		$ranges['to'] = 0;
		$ranges = apply_filters('fktr_reports_ranges_timestamp', $ranges, $request);
		$access = self::access_tab($request);
		$access = true;
		if (!$access) {
			echo '<div class="postbox" style="margin-top:10px; padding:30px;"><h2>'.__( "Sorry, you don't have access to this page.", FAKTURO_TEXT_DOMAIN ).'</h2></div>';
			return true;
		}
		wp_enqueue_script('fakturo_reports', FAKTURO_PLUGIN_URL . 'assets/js/reports.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
		
		echo '<div class="postbox" style="margin-top:10px;">';
			self::get_form_filters($request);
			echo '<div style="width: 100%;">
        			<canvas id="canvas"></canvas>
    			</div>';
		echo '</div>';
	}
	public static function scripts() {
		global $current_screen;  
		if ($current_screen->id == "fakturo_page_fakturo_reports") {
			wp_enqueue_script('fakturo_chartjs', FAKTURO_PLUGIN_URL . 'assets/js/chartjs/Chart.bundle.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
		}
	}
	public static function styles() {
		
	}
	public static function get_objects($request, $ranges) {
		if ($request['sec'] == 'sales') {
			return get_sales_on_range($ranges['from'], $ranges['to']);
		}

	}
	public static function get_form_filters($request) {
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
				<input type="submit" class="button-secondary" value="'.__( 'Filter', FAKTURO_TEXT_DOMAIN ).'"/>
			</form>
		</div>';

		echo $return_html;
	}
	public static function access_tab($request) {
		$current_tab = self::get_tabs($request['sec']);
		$user_access = true;
		if (!$current_tab) {
			$user_access = false;
		} else {
			if (isset($current_tab['default']['cap'])) {
				if (!current_user_can($current_tab['default']['cap'])) {
					$user_access = false;
				}
			} else {
				$user_access = false;
			}
		}
		return $user_access;
	}
	public static function default_timestand_ranges($ranges, $request) {
		$start_of_week = get_option('start_of_week', 0);
		$start_of_week_str = 'Sunday';
		if ($start_of_week == 0) {
			$start_of_week_str = 'Sunday';
		} else if ($start_of_week == 1) {
			$start_of_week_str = 'Monday';
		}  else if  ($start_of_week == 2) {
			$start_of_week_str = 'Tuesday';
		}  else if  ($start_of_week == 3) {
			$start_of_week_str = 'Wednesday';
		}  else if  ($start_of_week == 4) {
			$start_of_week_str = 'Thursday';
		}  else if  ($start_of_week == 5) {
			$start_of_week_str = 'Friday';
		}  else if  ($start_of_week == 6) {
			$start_of_week_str = 'Saturday';
		}
		$current_time = current_time('timestamp');

		if ($request['range'] == 'today') {
			$ranges['from'] = strtotime('-1 day', $current_time);
			$ranges['to'] =  strtotime('+1 day', $current_time);
		}
		if ($request['range'] == 'yesterday') {
			$ranges['from'] = strtotime('-2 day', $current_time);
			$ranges['to'] =  strtotime('-1 day', $current_time);
		}
		if ($request['range'] == 'this_week') {
			$ranges['from'] = strtotime($start_of_week_str.' this week', $current_time);
			$ranges['to'] =  $current_time;
		}
		if ($request['range'] == 'last_week') {
			$ranges['from'] = strtotime($start_of_week_str.' last week', $current_time);
			$ranges['to'] =  strtotime($start_of_week_str.' this week', $current_time);
		}
		if ($request['range'] == 'this_month') {
			$ranges['from'] = strtotime('first day of this month', $current_time);
			$ranges['to'] = $current_time;
		}
		return $ranges;
	}
	public static function default_request() {
		$array = array(
			'sec' => 'sales',
			'range' => 'this_month',
			'range_f' => '',
			'range_t' => '',
		);
		return $array;
	}
	public static function get_tabs($tab = false) {
		$sections_tabs = array(
			'sales' => apply_filters('ftkr_report_sales_sections', array( 
				'default' => array('text' => __( '​​Sales', FAKTURO_TEXT_DOMAIN ), 'sec' => 'sales', 'cap' => 'fktr_report_sales')
				)
			),
			'receipts' => apply_filters('ftkr_report_receipts_sections', array( 
				'default' =>  array('text' => __( 'Receipts', FAKTURO_TEXT_DOMAIN ), 'sec' => 'receipts', 'cap' => 'fktr_report_receipts')				
				)
			),
		);
		$sections_tabs = apply_filters('ftkr_report_tabs_sections', $sections_tabs);
		if (!$tab) {
			return $sections_tabs;
		} else {
			if (isset($sections_tabs[$tab])) {
				return $sections_tabs[$tab];
			} else {
				return false;
			}
		}
	}
	public static function tabs() {
		$request = wp_parse_args($_REQUEST, self::default_request());
		
		$all_tabs = self::get_tabs();
		$sections_tabs = array();
		foreach ($sections_tabs as $key => $value) {
			if (current_user_can($value['default']['cap'])) {
				$sections_tabs[$key] = $value;
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
			/*
			It is not using sub-tabs.

			echo '<div class="fktr-sections"><ul class="subsubsub">';
			$delimiter = '';
			foreach ($sections_tabs[$current_tab] as $sec_id => $sections) {
				if ($sec_id != 'default') {
					$active = ($current_screen->id == $sections['screen'] || (!empty($current_screen->post_type) && $current_screen->post_type == $sections['screen']) ) ?  ' current' : '';
					echo '<li>'.$delimiter.'<a href="' . esc_url( $sections['url'] ) . '" title="' . esc_attr( $sections['text'] ) . '" class="' . $active . '">' . esc_html( $sections['text'] ) . '</a></li>';
					$delimiter = ' | ';
				}
			}
			
			echo '</ul></div>';
			*/
			
		}
	}

}
reports::hooks();
?>