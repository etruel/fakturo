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
class stock_products_report {
	/**
	 * Add hooks for reports.
	 */
	public static function hooks() {
		add_action('report_page_before_content_stock_products', array(__CLASS__, 'before_content'), 10, 2);
		add_action('report_page_content_stock_products', array(__CLASS__, 'content'), 10, 2);
		add_filter('get_objects_reports_stock_products', array(__CLASS__, 'get_objects'), 10, 3);
	}
	/**
	* Print HTML before content on report page.
	* @param $request Array of values the $_REQUEST filtered.
	* @param $ranges Array of ranges on timestamp to get objects.
	*/
	public static function before_content($request, $ranges) {
		wp_enqueue_script( 'jquery-select2', FAKTURO_PLUGIN_URL . 'assets/js/jquery.select2.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
		wp_enqueue_script('fakturo_reports_stock_products', FAKTURO_PLUGIN_URL . 'assets/js/reports-client-summary.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
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
		
		//load select 2
		self::get_form_filters($request);
		
		//get stok products
		
		
		
		//load data query sql
		$objects_client = reports::get_objects($request, $ranges);
		
		
		$documents_values = $objects_client['documents_values'];
		$total_documents = array('subtotal' => 0, 'total' => 0);
		$html_client_data = '';
		
		if (is_numeric($request['product_id']) && $request['product_id'] > 0) {
			
			$product_data = fktrPostTypeProducts::get_product_data($request['product_id']);
		
			$html_client_data = '<div style="float:left; margin-left:15px;"><h3>'.__('Producto', 'fakturo' ).': '.$product_data['post_title'].'</h3></div>';
		}
		
		echo $html_client_data;
		echo '<div style="float:right; margin-right:15px;">
				<h3>'.sprintf(
							__('Date: since %s til %s', 'fakturo' ), 
							date_i18n($setting_system['dateformat'].' '.get_option( 'time_format' ), $ranges['from']), 
							date_i18n($setting_system['dateformat'].' '.get_option( 'time_format' ), $ranges['to']
						)
					).'</h3>
				</div>';
				
		$html_objects = '<div style="clear: both;"><h2>No results with this filters</h2></div>';
		if (!empty($objects_client)) {
			$html_objects = '<table class="wp-list-table widefat fixed striped posts">
				<thead>
				<tr>
					<td>
						'.__('ID', 'fakturo').'
					</td>
					<td>
						'.__('Name', 'fakturo').'
					</td>
					<td>
						'.__('Stock', 'fakturo').'
					</td>
					<td>
						'.__('Price', 'fakturo').'
					</td>
				</tr>
				</thead>
				<tbody id="the-list">';


		if (is_numeric($request['product_id']) && $request['product_id'] > 0) {
			
			$product_data = fktrPostTypeProducts::get_product_data($request['product_id']);
			
			
				$html_objects .= '<tr>
					<td>
						'.$product_data['post_ID'].'
					</td>
					<td>
						<a href="'.$obj_link.'" target="_blank">'.$product_data['post_title'].'</a>
					</td>
					<td>
						'.$product_data['stocks']['180'].'
					</td>
					<td>
						'.(!empty($product_data['prices']['52']) ? $product_data['prices']['52'] : 'Precio no registrado').'
					</td>
				</tr>';
			$html_objects .= '</tbody>
			</table>';
			//	print_r($product_data);
			
		}else{
			
		

			foreach ($objects_client as $obj => $testval) {
				

		$product_data = fktrPostTypeProducts::get_product_data($testval->ID);
		
		//var_export($product_data);
		
		//maybe_unserialize('a:2:{i:50;i:20;i:180;i:48;}')

				$obj_type = '';
				$obj_link = admin_url('post.php?post='.$testval->ID.'&action=edit');
			
				$html_objects .= '<tr>
					<td>
						'.$testval->ID.'
					</td>
					<td>
						<a href="'.$obj_link.'" target="_blank">'.$testval->timestamp_value.'</a>
					</td>
					<td>
						'.$product_data['stocks']['180'].'
					</td>
					<td>
						'.(!empty($product_data['prices']['52']) ? $product_data['prices']['52'] : 'Precio no registrado').'
					</td>
				</tr>';
			}
			$html_objects .= '</tbody>
			</table>';
		}
		}
		
		echo '<div style="width: 100%;">' . ($request['show_details'] ? $html_objects : '' ) . ' ' . $html_totals_data . '</div>';
	}

	public static function get_form_filters($request) {
		$selectClients = fakturo_get_select_post(array(
											'echo' => 0,
											'post_type' => 'fktr_product',
											'show_option_none' => __('All Products', 'fakturo' ),
											'name' => 'product_id',
											'id' => 'client_id',
											'class' => '',
											'selected' => $request['product_id']
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

		$return_html = '<div id="div_filter_form" style="padding:5px;">
			<form name="filter_form" method="get" action="'.admin_url('admin.php').'">
				<input type="hidden" name="page" value="fakturo_reports"/>
				<input type="hidden" name="sec" value="'.$request['sec'].'"/>
				'.$select_range_html.'
				'.$selectClients.'
				<label style="margin-left:10px;margin-right:10px;"><input type="checkbox" name="show_details" id="show_details" value="1" '.checked($request['show_details'], 1, false).'/>'.__( 'Show details', 'fakturo' ).'</label>
				<input type="submit" class="button-secondary" value="'.__( 'Filter', 'fakturo' ).'"/>
			</form>
		</div>';

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
		//die(var_export($ranges));
		global $wpdb;
		$sql = sprintf("SELECT p.ID, pm.meta_key, pm.meta_value as timestamp_value, p.post_type as post_type FROM {$wpdb->posts} as p
		LEFT JOIN {$wpdb->postmeta} as pm ON p.ID = pm.post_id
        WHERE 
        pm.meta_key = 'post_title'
		AND p.post_status = 'publish'
		AND (p.post_type = 'fktr_product' OR p.post_type = 'fktr_receipt')
		AND pm.meta_value >= '%s'

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
stock_products_report::hooks();
?>