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

		// export report		
		add_action('admin_post_stock_products_print_pdf', array(__CLASS__, 'print_pdf'));
		add_action('admin_post_stock_products_download_csv', array(__CLASS__, 'download_csv'));
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
		
		if (is_numeric($request['product_id']) ) {
	
			$product_data = fktrPostTypeProducts::get_product_data($request['product_id']);
			$html_client_data = '<div style="width:50%; text-align: left; display: inline-block;"><h3>'.__('Inventory', 'fakturo' ).'</h3></div>';
		}
		

		$objects_client = reports::get_objects($request, $ranges);

		//$new_array[5] = sprintf(__('Date: since %s til %s', 'fakturo' ), date_i18n($setting_system['dateformat'], $ranges['from']), date_i18n($setting_system['dateformat'], $ranges['to']));
		//$array_data[] = $new_array;
		//$new_array = $default_array_data;
		if (!empty($objects_client)) {

			$new_array[0] = __('ID', 'fakturo');
			$new_array[1] = __('Name', 'fakturo');
			$new_array[2] = __('Inventory', 'fakturo');
			$new_array[3] = __('Scale', 'fakturo');
			$new_array[4] = __('Cost', 'fakturo');
			$new_array[6] = __('Price', 'fakturo');
			$array_data[] = $new_array;
			$new_array = $default_array_data;


			if (is_numeric($request['product_id']) && $request['product_id'] > 0) {
					
					$product_data = fktrPostTypeProducts::get_product_data($request['product_id']);
					// Get Stock
					$total_stock = self::get_stock_product_report($product_data);
					
					// Get Prices
					$product_prices = self::get_prices_product_report($product_data);
					
					$new_array[0] = $product_data['post_ID'];
					$new_array[1] = $product_data['post_title'];
					$new_array[2] = $total_stock;
					$new_array[3] = $product_prices['scale'];
					$new_array[4] = $product_prices['price_initial'];
					$new_array[6] = $product_prices['price_finally'];
					$array_data[] = $new_array;
					$new_array = $default_array_data;
					
				}else{
					foreach ($objects_client as $obj => $testval) {
						
						$product_data = fktrPostTypeProducts::get_product_data($testval->ID);
						// Get Stock
						$total_stock = self::get_stock_product_report($product_data);
										
						// Get Prices
						$product_prices = self::get_prices_product_report($product_data);
			 
						$new_array[0] = $testval->ID;
						$new_array[1] = $testval->timestamp_value;
						$new_array[2] = $total_stock;
						$new_array[3] = $product_prices['scale'];
						$new_array[4] = $product_prices['price_initial'];
						$new_array[6] = $product_prices['price_finally'];
						$array_data[] = $new_array;
						$new_array = $default_array_data;
					
				
				}
			}
			
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
		
		if (is_numeric($request['product_id']) ) {
			//$product_data = fktrPostTypeProducts::get_product_data($request['product_id']);
		
			$html_client_data = '<div style="width:50%; text-align: left; display: inline-block;"><h3>'.__('Inventory', 'fakturo' ).'</h3></div>';
		}
		
		$total_html_print .= $html_client_data;

		$objects_client = reports::get_objects($request, $ranges);


		//$total_html_print .= '<div style="width:50%; text-align: right; display: inline-block;"><h3>'.sprintf(__('Date: since %s til %s', 'fakturo' ), date_i18n($setting_system['dateformat'], $ranges['from']), date_i18n($setting_system['dateformat'], $ranges['to'])).'</h3></div>';
		$html_objects = '<div style="clear: both;"><h2>No results with this filters</h2></div>';
		if (!empty($objects_client)) {
			$html_objects = '<table class="wp-list-table widefat fixed striped posts" style="width: 100%;">
				<thead>
					<tr>
						<td width="5%">
							'.__('ID', 'fakturo').'
						</td>
						<td width="10%">
							'.__('Name', 'fakturo').'
						</td>
						<td width="10%">
							'.__('Inventory', 'fakturo').'
						</td>
						<td width="15%">
							'.__('Scale', 'fakturo' ).'
						</td>
						<td width="10%">
							'.__('Cost', 'fakturo' ).'
						</td>
						<td width="10%">
							'.__('Price', 'fakturo').'
						</td>
					</tr>
				</thead>
				<tbody id="the-list">';
				
				if (is_numeric($request['product_id']) && $request['product_id'] > 0) {
					$product_data = fktrPostTypeProducts::get_product_data($request['product_id']);

					// Get Stock
					$total_stock = self::get_stock_product_report($product_data);
					
					// Get Prices
					$product_prices = self::get_prices_product_report($product_data);
			
						$obj_link = admin_url('post.php?post='.$request['product_id'].'&action=edit');
					
						$html_objects .= '<tr>
							<td>
								'.$product_data['post_ID'].'
							</td>
							<td>
								<a href="'.$obj_link.'" target="_blank">'.$product_data['post_title'].'</a>
							</td>
							<td>
								'. $total_stock .'
							</td>
							<td>
								'.$product_prices['scale'].'
							</td>
							<td>
								'.$product_prices['price_initial'].'
							</td>
							<td>
								'.$product_prices['price_finally'].'
							</td>
						</tr>';
					$html_objects .= '</tbody>
					</table>';
				}else{
					foreach ($objects_client as $obj => $testval) {
					
						$product_data = fktrPostTypeProducts::get_product_data($testval->ID);
						
						// Get Stock
						$total_stock = self::get_stock_product_report($product_data);
						
						// Get Prices
						$product_prices = self::get_prices_product_report($product_data);
						
	
						$obj_link = admin_url('post.php?post='.$testval->ID.'&action=edit');
					
						$html_objects .= '<tr>
							<td>
								'.$testval->ID.'
							</td>
							<td>
								<a href="'.$obj_link.'" target="_blank">'.$testval->timestamp_value.'</a>
							</td>
							<td>
								'. $total_stock .'
							</td>
							<td>
								'.$product_prices['scale'].'
							</td>
							<td>
								'.$product_prices['price_initial'].'
							</td>
							<td>
								'.$product_prices['price_finally'].'
							</td>
						</tr>';
					}
			}
				
			$html_objects .= '</tbody>
			</table>
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
		
		//load data query sql
		$objects_client = reports::get_objects($request, $ranges);
		
		$total_documents = array('subtotal' => 0, 'total' => 0);
		$html_client_data = '';
		
		if (is_numeric($request['product_id']) && $request['product_id'] > 0) {
			
			$product_data = fktrPostTypeProducts::get_product_data($request['product_id']);
		
			$html_client_data = '<div style="float:left; margin-left:15px;"><h3>'.__('Producto', 'fakturo' ).': '.$product_data['post_title'].'</h3></div>';
		}
		
		echo $html_client_data;
		echo '<br>';

		$html_objects = '<div style="clear: both;"><h2>No results with this filters</h2></div>';
		if (!empty($objects_client)) {
			$html_objects = '<table class="wp-list-table widefat fixed striped posts" id="table_report_product">
				<thead>
				<tr>
					<td width="5%">
						'.__('ID', 'fakturo').'
					</td>
					<td width="40%">
						'.__('Name', 'fakturo').'
					</td>
					<td width="10%">
						'.__('Inventory', 'fakturo').'
					</td>
					<td width="15%">
						'.__('Scale', 'fakturo' ).'
					</td>
					<td width="10%">
						'.__('Cost', 'fakturo' ).'
					</td>
					<td width="10%">
						'.__('Price', 'fakturo').'
					</td>
				</tr>
				</thead>
				<tbody id="the-list">';


			if (is_numeric($request['product_id']) && $request['product_id'] > 0) {
				
				$product_data = fktrPostTypeProducts::get_product_data($request['product_id']);

				// Get Stock
				$total_stock = self::get_stock_product_report($product_data);
				
				// Get Prices
				$product_prices = self::get_prices_product_report($product_data);
		
					$obj_link = admin_url('post.php?post='.$request['product_id'].'&action=edit');
				
					$html_objects .= '<tr>
						<td>
							'.$product_data['post_ID'].'
						</td>
						<td>
							<a href="'.$obj_link.'" target="_blank">'.$product_data['post_title'].'</a>
						</td>
						<td>
							'. $total_stock .'
						</td>
						<td>
							'.$product_prices['scale'].'
						</td>
						<td>
							'.$product_prices['price_initial'].'
						</td>
						<td>
							'.$product_prices['price_finally'].'
						</td>
					</tr>';
				$html_objects .= '</tbody>
				</table>';

			}else{
				
				foreach ($objects_client as $obj => $testval) {
					
					$product_data = fktrPostTypeProducts::get_product_data($testval->ID);
					
					// Get Stock
					$total_stock = self::get_stock_product_report($product_data);
					
					// Get Prices
					$product_prices = self::get_prices_product_report($product_data);
					

					$obj_link = admin_url('post.php?post='.$testval->ID.'&action=edit');
				
					$html_objects .= '<tr>
						<td>
							'.$testval->ID.'
						</td>
						<td>
							<a href="'.$obj_link.'" target="_blank">'.$testval->timestamp_value.'</a>
						</td>
						<td>
							'. $total_stock .'
						</td>
						<td>
							'.$product_prices['scale'].'
						</td>
						<td>
							'.$product_prices['price_initial'].'
						</td>
						<td>
							'.$product_prices['price_finally'].'
						</td>
					</tr>';
				}
				$html_objects .= '</tbody>
				</table>';
			}
		}
		
		echo '<div style="width: 100%;">' . $html_objects . '</div>';
	}

	public static function get_stock_product_report($product_data){
		// Get Stock
		$terms_stock = get_fakturo_terms(array(
			'taxonomy' => 'fktr_locations',
			'hide_empty' => false,
		));
		
		$total_stock = 0;
		foreach ($terms_stock as $stock) {
			$total_stock = $total_stock + (isset($product_data['stocks'][$stock->term_id]) ? $product_data['stocks'][$stock->term_id] : 0 );
		}
		return $total_stock;
	}

	public static function get_prices_product_report($product_data){
		$setting_system = get_option('fakturo_system_options_group', false);
		
		$terms_prices = get_fakturo_terms(array(
			'taxonomy' => 'fktr_price_scales',
			'hide_empty' => false,
		));

		$scale = '';
		$price_initial = 0;
		$price_finally = 0;
		$product_tax = get_fakturo_term($product_data['tax'], 'fktr_tax');
	
		foreach ($terms_prices as $price) {
			
			if (empty($product_data['prices'][$price->term_id])) {
				$product_data['prices'][$price->term_id] = ($product_data['cost']!= 0) ? ((($product_data['cost']/100)*$price->percentage)+$product_data['cost']) : 0;
			}
			if (empty($product_data['prices_final'][$price->term_id])) {
				
				$tax_porcent = 0;
				if(!is_wp_error($product_tax)) {
					$tax_porcent = $product_tax->percentage;
				}
				
				$product_data['prices_final'][$price->term_id] = ($product_data['prices'][$price->term_id]!= 0) ? ((($product_data['prices'][$price->term_id]/100)*$tax_porcent)+$product_data['prices'][$price->term_id]) : 0;
			}

			$scale = $price->name . ' ('.$price->percentage.'%)';
			$price_initial = (isset($product_data['prices'][$price->term_id]) ? number_format($product_data['prices'][$price->term_id], $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']) : '' );
			$price_finally = (isset($product_data['prices_final'][$price->term_id]) ? number_format($product_data['prices_final'][$price->term_id], $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']) : '' );
		}
		return array('scale'=>$scale, 'price_initial'=> $price_initial, 'price_finally' => $price_finally );

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

  
?>
		<div id="div_filter_form" style="padding:5px;">
			<form name="filter_form" method="get" action="<?php echo admin_url('admin.php') ?>">
				<input type="hidden" name="page" value="fakturo_reports"/>
				<input type="hidden" name="sec" value="<?php echo $request['sec'] ?>"/>
				<?php echo $selectClients ?>
				<span><?php echo __('Range from:', 'fakturo' ); ?></span>
				<select name="letter_from" id="letter_from">
					<?php
						for($i=65; $i<=90; $i++) {  
							$letter = chr($i);  
							echo '<option value="'. $letter .'" '. (( $letter == $request['letter_from'])? 'selected' : '' ) .'>'. $letter .'</option>';
						} 
					?>
				</select>
				<span><?php echo __('to:', 'fakturo' ); ?></span>
				<select name="letter_to" id="letter_to">
					<?php
						for($i=65; $i<=90; $i++) {
							$letter = chr($i);  
							echo '<option value="'. $letter .'" '. (( $letter == $request['letter_to'])? 'selected' : '' ) .'>'. $letter .'</option>';
						} 
					?>
				</select>
				<input type="submit" class="button-secondary" value="<?php echo __( 'Filter', 'fakturo' ) ?>"/>
				
				<a class="button-secondary right" href="<?php echo admin_url('admin-post.php?action=stock_products_download_csv&'.http_build_query($request) ) ?>" ><?php echo __( 'CSV', 'fakturo' ) ?></a>
				<a class="button-secondary right" style="margin-right:10px;" href="<?php echo admin_url('admin-post.php?action=stock_products_print_pdf&'.http_build_query($request)) ?>"><?php echo __( 'PDF', 'fakturo' )?> </a>
				
			</form>
		</div>
		<?php 

		
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

		$letter_from = (isset($request['letter_from']) ? $request['letter_from'] : 'A' );
		$letter_to = (isset($request['letter_to']) ? $request['letter_to'] : 'Z' );
		
		if($letter_to == 'Z' || $letter_from > $letter_to ){
			$letter_to = 'Y';
		}

		$sql = "SELECT p.ID, pm.meta_key, pm.meta_value as timestamp_value, p.post_type as post_type FROM {$wpdb->posts} as p
				LEFT JOIN {$wpdb->postmeta} as pm ON p.ID = pm.post_id
		        WHERE 
		        pm.meta_key = 'post_title'
				AND p.post_status = 'publish'
				AND (p.post_type = 'fktr_product' OR p.post_type = 'fktr_receipt')

				AND pm.meta_value between '$letter_from' and char(ascii('$letter_to') + 1)
				OR p.ID = '".$request['product_id']."'
				 ";
				
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