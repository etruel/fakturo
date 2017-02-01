<?php

// Exit if accessed directly
if (!defined('ABSPATH'))  {
	exit;
}

add_action('admin_print_styles', 'styles_dashboard');
function styles_dashboard() {
	$screen = get_current_screen();
	if ($screen->id == 'dashboard') {
		wp_enqueue_style('fktr_dashboard_widget',FAKTURO_PLUGIN_URL .'assets/css/dashboard-widget.css');	
	}
}

function fktr_add_dashboard_widgets() {

	wp_add_dashboard_widget(
                 'fktr_dashboard_widget_sale_summary',         // Widget slug.
                 'Fakturo - Sales Summary',         // Title.
                 'fktr_widget_dashboard_sale_summary' // Display function.
        );
	global $wp_meta_boxes;
 	
 	$normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];
 
 	$example_widget_backup = array( 'fktr_dashboard_widget_sale_summary' => $normal_dashboard['fktr_dashboard_widget_sale_summary'] );
 	unset( $normal_dashboard['fktr_dashboard_widget_sale_summary'] );
 
 	$sorted_dashboard = array_merge( $example_widget_backup, $normal_dashboard );
 
 	$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
}
add_action( 'wp_dashboard_setup', 'fktr_add_dashboard_widgets' );

function fktr_default_dashboard_widgets() {
	
	fktr_add_dashboard_widget(
        'fktr_dashboard_widget_sale_summary',         // Widget slug.
        'Fakturo - Sales Summary',         // Title.
        'fktr_widget_dashboard_sale_summary' // Display function.
    );
	/* fktr_add_dashboard_widget(
        'fktr_dashboard_widget_news',         // Widget slug.
        'Fakturo News',         // Title.
        'fktr_widget_dashboard_news' // Display function.
    );
	
    fktr_add_dashboard_widget(
        'fktr_dashboard_widget_test',         // Widget slug.
        'Fakturo - Test 1',         // Title.
        'fktr_widget_dashboard_test' // Display function.
    );
    fktr_add_dashboard_widget(
        'fktr_dashboard_widget_testtow',         // Widget slug.
        'Fakturo - Test 2',         // Title.
        'fktr_widget_dashboard_testtow' // Display function.
    );
     fktr_add_dashboard_widget(
        'fktr_dashboard_widget_testtheree',         // Widget slug.
        'Fakturo - Test 3',         // Title.
        'fktr_widget_dashboard_testtow' // Display function.
    );*/
}
function fktr_widget_dashboard_test() {
	echo 'content';
}
function fktr_widget_dashboard_testtow() {
	echo 'content';
}
add_action( 'fktr_dashboard_setup', 'fktr_default_dashboard_widgets' );

function fktr_widget_dashboard_news() {
	echo '<ul>
            <li>
                <span class="dashicons dashicons-admin-settings"></span>
                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                       tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
                        quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
                        consequat...</p>
                        <div style="clear: both;"></div>
                </li>
            <li>
                <span class="dashicons dashicons-admin-settings"></span>
                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                       tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
                        quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
                        consequat...</p>
                        <div style="clear: both;"></div>
                </li>
        </ul>';
}

function fktr_widget_dashboard_sale_summary() {
	

		$setting_system = get_option('fakturo_system_options_group', false);
		$currencyDefault = get_fakturo_term($setting_system['currency'], 'fktr_currencies');

		$sales_today = get_sales_on_range(strtotime('-1 day', time()), strtotime('+1 day', time()));
	
		$earning_today = 0;
		$count_sales_today = 0;
		foreach ($sales_today as $id_sale_today) {
			$count_sales_today++;
			$sales_data = fktrPostTypeSales::get_sale_data($id_sale_today);
			$earning_today = $earning_today+fakturo_transform_money($sales_data['invoice_currency'], $setting_system['currency'], $sales_data['in_total']);
		}
		$money_format_today = (($setting_system['currency_position'] == 'before')?$currencyDefault->symbol.' ':'').''.number_format($earning_today, $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']).''.(($setting_system['currency_position'] == 'after')?' '.$currencyDefault->symbol:'');
		$sales_current_month = get_sales_on_range(strtotime('first day of this month', time()), time());
		$earning_current_month = 0;
		$count_sales_current_month = 0;
		foreach ($sales_current_month as $id_sale_current_month) {
			$count_sales_current_month++;
			$sales_data = fktrPostTypeSales::get_sale_data($id_sale_current_month);
			$earning_current_month = $earning_current_month+fakturo_transform_money($sales_data['invoice_currency'], $setting_system['currency'], $sales_data['in_total']);
		}

		$money_format_current_month = (($setting_system['currency_position'] == 'before')?$currencyDefault->symbol.' ':'').''.number_format($earning_current_month, $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']).''.(($setting_system['currency_position'] == 'after')?' '.$currencyDefault->symbol:'');
		
		
		$sales_last_month = get_sales_on_range(strtotime('first day of last month', time()), strtotime('last day of last month', time()));

		$earning_last_month = 0;
		$count_sales_last_month = 0;
		
		foreach ($sales_last_month as $id_sale_last_month) {
			$count_sales_last_month++;
			$sales_data = fktrPostTypeSales::get_sale_data($id_sale_last_month);
			$earning_last_month = $earning_last_month+fakturo_transform_money($sales_data['invoice_currency'], $setting_system['currency'], $sales_data['in_total']);
		}

		$money_format_last_month = (($setting_system['currency_position'] == 'before')?$currencyDefault->symbol.' ':'').''.number_format($earning_last_month, $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']).''.(($setting_system['currency_position'] == 'after')?' '.$currencyDefault->symbol:'');
		
		$sales_total = get_sales_on_range(0, 0);
		
		$earning_total = 0;
		$count_sales_total = 0;
		
		foreach ($sales_total as $id_sale_total) {
			$count_sales_total++;
			$sales_data = fktrPostTypeSales::get_sale_data($id_sale_total);
			$earning_total = $earning_total+fakturo_transform_money($sales_data['invoice_currency'], $setting_system['currency'], $sales_data['in_total']);
		}

		$money_format_total = (($setting_system['currency_position'] == 'before')?$currencyDefault->symbol.' ':'').''.number_format($earning_total, $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']).''.(($setting_system['currency_position'] == 'after')?' '.$currencyDefault->symbol:'');
		
		
			
		echo '<div class="seccion1" class="white">
				
				<table  width="100%" cellspacing="0">
					<tr>
						<td>
							<p>Current Mothn</p>
							<ol>
								<li><span class="sp_left">Earning</span>  <span class="sp_right"> '.$money_format_current_month.'</span><br style="clear: both;"></li>
								<li><span class="sp_left">Sales</span> <span class="sp_right">'.$count_sales_current_month.' </span></li>
							</ol>
						</td>
						<td>
							<p>Today</p>
							<ol>
								<li><span class="sp_left">Earning</span> <span class="sp_right">'.$money_format_today.'</span><br style="clear: both;"></li>
								<li><span class="sp_left">Sales</span> <span class="sp_right">'.$count_sales_today.'</span></li>
							</ol>
						</td>
					</tr>
					<tr>
						<td>
							<p>Last Mothn</p>
							<ol>
								<li><span class="sp_left">Earning</span> <span class="sp_right">'.$money_format_last_month.'</span><br style="clear: both;"></li>
								<li><span class="sp_left">Sales</span> <span class="sp_right">'.$count_sales_last_month.'</span></li>
							</ol>
						</td>
						<td>
							<p>Total</p>
							<ol>
								<li><span class="sp_left">Earning</span> <span class="sp_right">'.$money_format_total.'</span><br style="clear: both;"></li>
								<li><span class="sp_left">Sales</span> <span class="sp_right">'.$count_sales_total.'</span></li>
							</ol>
						</td>
					</tr>
				</table>
			</div> ';
}

?>