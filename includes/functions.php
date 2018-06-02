<?php

// Exit if accessed directly
if (!defined('ABSPATH'))  {
	exit;
}
function fakturo_get_select_post($args = null) {
		
	$defaults = array(
		'post_type' 		=> 'post',
		'post_status'		=> 'publish',
		'meta_key'			=> '',
		'posts_per_page'	=> -1,
		'id'				=> '',
		'name'				=> '',
		'class'				=> '',
		'selected'			=> -1,
		'show_option_none'	=> '',
		'echo'				=> 1,
		'attributes'     	=> array(),
	);
 
	$r = wp_parse_args( $args, $defaults );
		
	$itemsPosts = get_posts( array( 
								'post_type'			=> $r['post_type'],
								'post_status'		=> $r['post_status'],
								'meta_key'			=> $r['meta_key'],
								'posts_per_page'	=> $r['posts_per_page'],
							));
	
	$htmlAttributes	= '';
	foreach ($r['attributes'] as $att => $val) {
		$htmlAttributes	.= ' '.$att.'="'.$val.'"';
	}
	$select = '<select name="'.$r['name'].'" id="'.$r['id'].'" class="'.$r['class'].'"'.$htmlAttributes.'>';
	if (!empty($r['show_option_none'])) {
		$select .= '<option value="-1" '.selected($r['selected'], -1, false).'>'.$r['show_option_none']. '</option>';
	}
	foreach ($itemsPosts as $post ) {
		$select .='<option value="' .$post->ID. '" ' . selected($r['selected'], $post->ID, false) . '>'.esc_html(get_the_title($post->ID)).'</option>';
	}
		
	$select .= '</select>';
	if ($r['echo']) {
		echo $select;
		return true;
	} else {
		return $select;
	}
		
}
function get_fakturo_terms($args = array()) {
	$return_terms = array();
	$terms = get_terms($args);
	if ($terms) {
		foreach ($terms as $t) {
			$newTerm = new stdClass();
			$newTerm->term_id = $t->term_id;
			$newTerm->name = $t->name;
			$newTerm->slug = $t->slug;
			$newTerm->term_group = $t->term_group;
			$newTerm->term_taxonomy_id = $t->term_taxonomy_id;
			$newTerm->taxonomy = $t->taxonomy;
			$newTerm->parent = $t->parent;
			$newTerm->count = $t->count;
			
			$t->description = trim($t->description);
			$t->description = utf8_encode($t->description);
			$t->description = str_replace('&quot;', '"', $t->description);
			$term_meta = json_decode($t->description);
			if (isset($term_meta)) {
				foreach($term_meta as $fieldmeta => $value) {
					$newTerm->$fieldmeta = $value;
				}
			}
			
			$return_terms[] = $newTerm;
			
		}
	}
	return $return_terms;
}
function get_fakturo_term($term_id, $taxonomy, $field = null) {
	if ($term_id < 1) {
		return new WP_Error( 'incorrect_term_id', __('You has send a incorrect term_id', 'fakturo'));
	}
	$term = get_term($term_id, $taxonomy);
	if(is_wp_error($term)) {
		return $term;
	}
	if(!is_object($term)) {
        return new WP_Error( 'incorrect_term_id', __('You has send a incorrect term_id', 'fakturo'));
    }
	$return = new stdClass();
	$return->term_id = $term->term_id;
	$return->name = $term->name;
	$return->slug = $term->slug;
	$return->term_group = $term->term_group;
	$return->term_taxonomy_id = $term->term_taxonomy_id;
	$return->taxonomy = $term->taxonomy;
	$return->parent = $term->parent;
	$return->count = $term->count;
	
	$term->description = trim($term->description);
	$term->description = utf8_encode($term->description);
	$term->description = str_replace('&quot;', '"', $term->description);
	$term_meta = json_decode($term->description);
	if (isset($term_meta)) {
		foreach($term_meta as $fieldmeta => $value) {
			$return->$fieldmeta = $value;
		}
	}
	if (isset($field) && isset($return->$field)) {
		return $return->$field;
	}
	$return = apply_filters('get_fakturo_term_'.$return->taxonomy, $return);
	return $return;
}

function set_fakturo_term($term_id = null, $tt_id = null, $args = null) {
	global $wpdb;
	if (isset($args)) {
		$wpdb->update( $wpdb->term_taxonomy, array('description' => json_encode($args)), array( 'term_taxonomy_id' => $tt_id ) );
	}
	
}

function fakturo_mask_to_float($value) {
	$setting_system = get_option('fakturo_system_options_group', false);
	if (!isset($value)) {
		$value = '0';
	}
	if (strpos($value, $setting_system['decimal']) !== false) {
		$pieceNumber = explode($setting_system['decimal'], $value);
		$pieceNumber[0] = str_replace($setting_system['thousand'], '', $pieceNumber[0]);
		$value = implode('.', $pieceNumber);
		$value = filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	}
	return $value;
}

function fakturo_porcent_to_mask($value) {
	if (!is_numeric($value)) {
		$value = 0;
	}
	$setting_system = get_option('fakturo_system_options_group', false);
	$value = number_format($value, 2, $setting_system['decimal'], $setting_system['thousand']);
	return $value;
}

	################### DATE FUNCS
	/* function date2time (also datetime to time)
	 * @param $value	str date or date time as '22-09-2008' or '22-09-2008 15:35:00' 
	 * @param $format	str format of the date in $value, as 'm-d-Y' or 'd-m-Y' 
	 * 
	 * @return int timestamp or false if error
	 */
function fakturo_date2time($value ,  $dateformat = 'd-m-Y' ){
	$date = date_parse_from_format( $dateformat , $value);
	$timestamp = mktime($date['hour'], $date['minute'], $date['second'], $date['month'], $date['day'], $date['year']);
	if($timestamp['error_count'] !=0 ) {
		 $timestamp=false;  // if error return false
	}
	return $timestamp; 
}
function getRateFromCurrencyId($currency_id) {
	$retorno = 1;
	$currency_data = get_fakturo_term($currency_id, 'fktr_currencies');
	if(!is_wp_error($currency_data)) {
		$retorno = $currency_data->rate;
	}
	return $retorno;
}
	
	
function fakturo_transform_money($from_c, $to_c, $value_money) {
	$setting_system = get_option('fakturo_system_options_group', false);
	$default_c = $setting_system['currency'];
	$retorno = $value_money;
	$current_currency = $from_c;
	if ($from_c != $to_c) {
		if ($default_c != $current_currency) {
			$rate = getRateFromCurrencyId($current_currency);
			$retorno = $retorno*$rate;
			$current_currency = $default_c;
		}
		if ($current_currency != $to_c) {
			$rate = getRateFromCurrencyId($to_c);
			$retorno = $retorno/$rate;
		}
	}
	return $retorno;
}

function get_money_format($value, $currency) {
	if (is_array($currency)) {
		$currency = (object)$currency;
	}
	$setting_system = get_option('fakturo_system_options_group', false);
	$ret = '';
	$ret = ''.(($setting_system['currency_position'] == 'before')?$currency->symbol.' ':'').''.number_format($value, $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']).''.(($setting_system['currency_position'] == 'after')?' '.$currency->symbol:'').'';
	return $ret;
}

function fktr_array_multi_key_exists(array $arrNeedles, array $arrHaystack, $blnMatchAll=true){
    $blnFound = array_key_exists(array_shift($arrNeedles), $arrHaystack);
   
    if($blnFound && (count($arrNeedles) == 0 || !$blnMatchAll))
        return true;
   
    if(!$blnFound && count($arrNeedles) == 0 || $blnMatchAll)
        return false;
   
    return fktr_array_multi_key_exists($arrNeedles, $arrHaystack, $blnMatchAll);
}

function get_sales_on_range($from, $to) {
	global $wpdb;
	$sales_ids = array();
	if ($from != 0 && $to != 0) {
		$sql = sprintf("SELECT p.ID, pm.meta_key, pm.meta_value as timestamp_value FROM {$wpdb->posts} as p
		LEFT JOIN {$wpdb->postmeta} as pm ON p.ID = pm.post_id
        WHERE 
        pm.meta_key = 'date'
		AND p.post_status = 'publish'
		AND p.post_type = 'fktr_sale'
		AND pm.meta_value >= '%s'
		AND pm.meta_value < '%s'
        GROUP BY p.ID 
		", $from, $to);


	} else {
		$sql = sprintf("SELECT p.ID, pm.meta_key, pm.meta_value as timestamp_value FROM {$wpdb->posts} as p
		LEFT JOIN {$wpdb->postmeta} as pm ON p.ID = pm.post_id
        WHERE 
        pm.meta_key = 'date'
		AND p.post_status = 'publish'
		AND p.post_type = 'fktr_sale'
        GROUP BY p.ID 
		");
	}
	
	$results = $wpdb->get_results($sql, OBJECT);
	foreach ($results as $rs) {
		$sales_ids[] = $rs->ID;
	}
	return $sales_ids;
}
function fktr_get_dialer_options() {
	$select_options = array();
	$new_option = new stdClass();
	$new_option->text = __( 'Select a option', 'fakturo');
	$new_option->icon = 'dashicons-category';
	$new_option->type = null;
	$select_options[0] = $new_option;

	$new_option = new stdClass();
	$new_option->text = __( 'Settings', 'fakturo');
	$new_option->icon = 'dashicons-admin-settings';
	$new_option->type = 'setting';
	$new_option->caps = 'edit_fakturo_settings';
	$select_options['fktr_settings'] = $new_option;
	$args = array(
		'public'   => false
	); 
	$output = 'objects'; // names or objects, note names is the default
	$operator = 'and'; // 'and' or 'or'
	$post_types = get_post_types($args, $output, $operator); 
	foreach ($post_types  as $post_type  ) {
			
		if (strpos($post_type->name, 'fktr') === false ) {
			continue;
		}

		if (empty($select_options[$post_type->name])) {
			$new_option = new stdClass();
			$new_option->text = $post_type->label;
			$new_option->icon = apply_filters('fktr_get_dialer_icon_'.$post_type->name, $post_type->menu_icon);
			$new_option->type = 'post';
			$new_option->caps = 'edit_'.$post_type->name;
			
			$select_options[$post_type->name] = $new_option;
		}
			
	}
	$args = array(
		  	'public'   => false,
	); 
	$output = 'objects'; 
	$operator = 'and'; 
	$taxonomies = get_taxonomies( $args, $output, $operator ); 
	if ( $taxonomies ) {
		  foreach ( $taxonomies  as $taxonomy ) {
		    if (strpos($taxonomy->name, 'fktr') === false ) {
				continue;
			}
			
			if (empty($select_options[$taxonomy->name])) {
				$new_option = new stdClass();
				$new_option->text = $taxonomy->label;
				$new_option->icon = apply_filters('fktr_get_dialer_icon_'.$taxonomy->name,'dashicons-tickets');
				$new_option->type = 'taxonomy';
				$new_option->caps = 'edit_'.$taxonomy->name;
				$select_options[$taxonomy->name] = $new_option;
			}
		}
	}
	$select_options = apply_filters('fktr_get_dialer_options', $select_options);
	return $select_options;
}
 


function fktr_number_to_letter_cents() {
	global $importe_parcial;

	$importe_parcial = number_format($importe_parcial, 2, ".", "") * 100;

	if($importe_parcial > 0) {
		$num_letra = " con " . fktr_number_to_letter_ten_cents($importe_parcial);
	}else {
		$num_letra = "";
	}
	return $num_letra;
}

function fktr_number_to_letter_unit_cents($numero) {
	$numero = number_format($numero);
	switch($numero) {
		case 9: {
				$num_letra = "nueve centavos";
				break;
			}
		case 8: {
				$num_letra = "ocho centavos";
				break;
			}
		case 7: {
				$num_letra = "siete centavos";
				break;
			}
		case 6: {
				$num_letra = "seis centavos";
				break;
			}
		case 5: {
				$num_letra = "cinco centavos";
				break;
			}
		case 4: {
				$num_letra = "cuatro centavos";
				break;
			}
		case 3: {
				$num_letra = "tres centavos";
				break;
			}
		case 2: {
				$num_letra = "dos centavos";
				break;
			}
		case 1: {
				$num_letra = "un centavo";
				break;
			}
		default: $num_letra = "error:" . gettype($numero);
	}
	return $num_letra;
}


function fktr_number_to_letter_ten_cents($numero) {
	if($numero >= 10) {
		if($numero >= 90 && $numero <= 99) {
			if($numero == 90)
				return "noventa centavos";
			else if($numero == 91)
				return "noventa y un centavos";
			else
				return "noventa y " . fktr_number_to_letter_unit_cents($numero - 90);
		}
		if($numero >= 80 && $numero <= 89) {
			if($numero == 80)
				return "ochenta centavos";
			else if($numero == 81)
				return "ochenta y un centavos";
			else
				return "ochenta y " . fktr_number_to_letter_unit_cents($numero - 80);
		}
		if($numero >= 70 && $numero <= 79) {
			if($numero == 70)
				return "setenta centavos";
			else if($numero == 71)
				return "setenta y un centavos";
			else
				return "setenta y " . fktr_number_to_letter_unit_cents($numero - 70);
		}
		if($numero >= 60 && $numero <= 69) {
			if($numero == 60)
				return "sesenta centavos";
			else if($numero == 61)
				return "sesenta y un centavos";
			else
				return "sesenta y " . fktr_number_to_letter_unit_cents($numero - 60);
		}
		if($numero >= 50 && $numero <= 59) {
			if($numero == 50)
				return "cincuenta centavos";
			else if($numero == 51)
				return "cincuenta y un centavos";
			else
				return "cincuenta y " . fktr_number_to_letter_unit_cents($numero - 50);
		}
		if($numero >= 40 && $numero <= 49) {
			if($numero == 40)
				return "cuarenta centavos";
			else if($numero == 41)
				return "cuarenta y un centavos";
			else
				return "cuarenta y " . fktr_number_to_letter_unit_cents($numero - 40);
		}
		if($numero >= 30 && $numero <= 39) {
			if($numero == 30)
				return "treinta centavos";
			else if($numero == 91)
				return "treinta y un centavos";
			else
				return "treinta y " . fktr_number_to_letter_unit_cents($numero - 30);
		}
		if($numero >= 20 && $numero <= 29) {
			if($numero == 20)
				return "veinte centavos";
			else if($numero == 21)
				return "veintiun centavos";
			else
				return "veinti" . fktr_number_to_letter_unit_cents($numero - 20);
		}
		if($numero >= 10 && $numero <= 19) {
			$numero = (int) $numero;
			if($numero == 10)
				return "diez centavos";
			else if($numero == 11)
				return "once centavos";
			else if($numero == 12)
				return "doce centavos";
			else if($numero == 13)
				return "trece centavos";
			else if($numero == 14)
				return "catorce centavos";
			else if($numero == 15)
				return "quince centavos";
			else if($numero == 16)
				return "dieciseis centavos";
			else if($numero == 17)
				return "diecisiete centavos";
			else if($numero == 18)
				return "dieciocho centavos";
			else if($numero == 19)
				return "diecinueve centavos";
		}
	}else
		return fktr_number_to_letter_unit_cents($numero);
}

function fktr_number_to_letter_units($numero) {
	switch($numero) {
		case 9: {
				$num = "nueve";
				break;
			}
		case 8: {
				$num = "ocho";
				break;
			}
		case 7: {
				$num = "siete";
				break;
			}
		case 6: {
				$num = "seis";
				break;
			}
		case 5: {
				$num = "cinco";
				break;
			}
		case 4: {
				$num = "cuatro";
				break;
			}
		case 3: {
				$num = "tres";
				break;
			}
		case 2: {
				$num = "dos";
				break;
			}
		case 1: {
				$num = "uno";
				break;
			}
	}
	return $num;
}


function fktr_number_to_letter_ten($numero) {
	if($numero >= 90 && $numero <= 99) {
		$num_letra = "noventa ";
		if($numero > 90)
			$num_letra = $num_letra . "y " . fktr_number_to_letter_units($numero - 90);
	}
	else if($numero >= 80 && $numero <= 89) {
		$num_letra = "ochenta ";
		if($numero > 80)
			$num_letra = $num_letra . "y " . fktr_number_to_letter_units($numero - 80);
	}
	else if($numero >= 70 && $numero <= 79) {
		$num_letra = "setenta ";
		//$num_letra = $num_letra."setenta ";
		if($numero > 70)
			$num_letra = $num_letra . "y " . fktr_number_to_letter_units($numero - 70);
	}
	else if($numero >= 60 && $numero <= 69) {
		$num_letra = "sesenta ";
		if($numero > 60)
			$num_letra = $num_letra . "y " . fktr_number_to_letter_units($numero - 60);
	}
	else if($numero >= 50 && $numero <= 59) {
		$num_letra = "cincuenta ";
		if($numero > 50)
			$num_letra = $num_letra . "y " . fktr_number_to_letter_units($numero - 50);
	}
	else if($numero >= 40 && $numero <= 49) {
		$num_letra = "cuarenta ";
		if($numero > 40)
			$num_letra = $num_letra . "y " . fktr_number_to_letter_units($numero - 40);
	}
	else if($numero >= 30 && $numero <= 39) {
		$num_letra = "treinta ";
		if($numero > 30)
			$num_letra = $num_letra . "y " . fktr_number_to_letter_units($numero - 30);
	}
	else if($numero >= 20 && $numero <= 29) {
		if($numero == 20)
			$num_letra = "veinte ";
		else
			$num_letra = "veinti" . fktr_number_to_letter_units($numero - 20);
	}
	else if($numero >= 10 && $numero <= 19) {
		switch($numero) {
			case 10: {
					$num_letra = "diez ";
					break;
				}
			case 11: {
					$num_letra = "once ";
					break;
				}
			case 12: {
					$num_letra = "doce ";
					break;
				}
			case 13: {
					$num_letra = "trece ";
					break;
				}
			case 14: {
					$num_letra = "catorce ";
					break;
				}
			case 15: {
					$num_letra = "quince ";
					break;
				}
			case 16: {
					$num_letra = "dieciseis ";
					break;
				}
			case 17: {
					$num_letra = "diecisiete ";
					break;
				}
			case 18: {
					$num_letra = "dieciocho ";
					break;
				}
			case 19: {
					$num_letra = "diecinueve ";
					break;
				}
		}
	}else
		$num_letra = fktr_number_to_letter_units($numero);

	return $num_letra;
}


function fktr_number_to_letter_hundreds($numero) {
	if($numero >= 100) {
		if($numero >= 900 & $numero <= 999) {
			$num_letra = "novecientos ";

			if($numero > 900)
				$num_letra = $num_letra . fktr_number_to_letter_ten($numero - 900);
		}
		else if($numero >= 800 && $numero <= 899) {
			$num_letra = "ochocientos ";

			if($numero > 800)
				$num_letra = $num_letra . fktr_number_to_letter_ten($numero - 800);
		}
		else if($numero >= 700 && $numero <= 799) {
			$num_letra = "setecientos ";

			if($numero > 700)
				$num_letra = $num_letra . fktr_number_to_letter_ten($numero - 700);
		}
		else if($numero >= 600 && $numero <= 699) {
			$num_letra = "seiscientos ";

			if($numero > 600)
				$num_letra = $num_letra . fktr_number_to_letter_ten($numero - 600);
		}
		else if($numero >= 500 && $numero <= 599) {
			$num_letra = "quinientos ";

			if($numero > 500)
				$num_letra = $num_letra . fktr_number_to_letter_ten($numero - 500);
		}
		else if($numero >= 400 && $numero <= 499) {
			$num_letra = "cuatrocientos ";

			if($numero > 400)
				$num_letra = $num_letra . fktr_number_to_letter_ten($numero - 400);
		}
		else if($numero >= 300 && $numero <= 399) {
			$num_letra = "trescientos ";

			if($numero > 300)
				$num_letra = $num_letra . fktr_number_to_letter_ten($numero - 300);
		}
		else if($numero >= 200 && $numero <= 299) {
			$num_letra = "doscientos ";

			if($numero > 200)
				$num_letra = $num_letra . fktr_number_to_letter_ten($numero - 200);
		}
		else if($numero >= 100 && $numero <= 199) {
			if($numero == 100)
				$num_letra = "cien ";
			else
				$num_letra = "ciento " . fktr_number_to_letter_ten($numero - 100);
		}
	}else
		$num_letra = fktr_number_to_letter_ten($numero);

	return $num_letra;
}


function fktr_number_to_letter_hundred() {
	global $importe_parcial;

	$parcial = 0;
	$car = 0;

	while(substr($importe_parcial, 0, 1) == 0 && strlen($importe_parcial) > 0 && $importe_parcial > 0.99) {
		$importe_parcial = substr($importe_parcial, 1, strlen($importe_parcial) - 1);
	}

	if($importe_parcial >= 1 && $importe_parcial <= 9.99)
		$car = 1;
	else if($importe_parcial >= 10 && $importe_parcial <= 99.99)
		$car = 2;
	else if($importe_parcial >= 100 && $importe_parcial <= 999.99)
		$car = 3;

	$parcial = substr($importe_parcial, 0, $car);
	$importe_parcial = substr($importe_parcial, $car);

	$num_letra = fktr_number_to_letter_hundreds($parcial) . fktr_number_to_letter_cents();

	return $num_letra;
}


function fktr_number_to_letter_hundred_thousand() {
	global $importe_parcial;

	$parcial = 0;
	$car = 0;

	while(substr($importe_parcial, 0, 1) == 0 && strlen($importe_parcial) > 0 && $importe_parcial > 0.99)
		$importe_parcial = substr($importe_parcial, 1, strlen($importe_parcial) - 1);

	if($importe_parcial >= 1000 && $importe_parcial <= 9999.99)
		$car = 1;
	else if($importe_parcial >= 10000 && $importe_parcial <= 99999.99)
		$car = 2;
	else if($importe_parcial >= 100000 && $importe_parcial <= 999999.99)
		$car = 3;

	$parcial = substr($importe_parcial, 0, $car);
	$importe_parcial = substr($importe_parcial, $car);

	if($parcial > 0) {
		if($parcial == 1)
			$num_letra = "un mil ";
		else
			$num_letra = fktr_number_to_letter_hundreds($parcial) . " mil ";
	}

	return $num_letra;
}


function fktr_number_to_letter_million() {
	global $importe_parcial;

	$parcial = 0;
	$car = 0;

	while(substr($importe_parcial, 0, 1) == 0 && strlen($importe_parcial) > 0 && $importe_parcial > 0.99)
		$importe_parcial = substr($importe_parcial, 1, strlen($importe_parcial) - 1);

	if($importe_parcial >= 1000000 && $importe_parcial <= 9999999.99)
		$car = 1;
	else if($importe_parcial >= 10000000 && $importe_parcial <= 99999999.99)
		$car = 2;
	else if($importe_parcial >= 100000000 && $importe_parcial <= 999999999.99)
		$car = 3;

	$parcial = substr($importe_parcial, 0, $car);
	$importe_parcial = substr($importe_parcial, $car);

	if($parcial == 1)
		$num_letras = "un millón ";
	else
		$num_letras = fktr_number_to_letter_hundreds($parcial) . " millones ";

	return $num_letras;
}

function fktr_number_to_letter_es($numero) {
	global $importe_parcial;

	$importe_parcial = $numero;

	if($numero < 1000000000) {
		if($numero >= 1000000 && $numero <= 999999999.99)
			$num_letras = fktr_number_to_letter_million() . fktr_number_to_letter_hundred_thousand() . fktr_number_to_letter_hundred();
		else if($numero >= 1000 && $numero <= 999999.99)
			$num_letras = fktr_number_to_letter_hundred_thousand() . fktr_number_to_letter_hundred();
		else if($numero >= 1 && $numero <= 999.99)
			$num_letras = fktr_number_to_letter_hundred();
		else if($numero >= 0.01 && $numero <= 0.99) {
			if($numero == 0.01)
				$num_letras = "un centavo";
			else
				$num_letras = fktr_number_to_letter(($numero * 100) . "/100") . " centavos";
		}
	}
	return $num_letras;
} 

function fktr_insert_country_states( $term, $taxonomy, $args = array() ) {
    global $wpdb;
 	$time_start = microtime(true);
    
    $defaults = array( 'alias_of' => '', 'description' => '', 'parent' => 0, 'slug' => '');
    $args = wp_parse_args( $args, $defaults );
 
  
    $args['name'] = $term;
    $args['taxonomy'] = $taxonomy;
 
    // Coerce null description to strings, to avoid database errors.
    $args['description'] = (string) $args['description'];
 
    //$args = sanitize_term($args, $taxonomy, 'db');
 
    // expected_slashed ($name)
    $name = wp_unslash( $args['name'] );
    $description = wp_unslash( $args['description'] );
    $parent = (int) $args['parent'];
 
    $slug_provided = ! empty( $args['slug'] );
    if ( ! $slug_provided ) {
        $slug = sanitize_title( $name );
    } else {
        $slug = $args['slug'];
    }
 	
    $slug  = $slug . rand(0, 5);
  
 
    $data = compact( 'name', 'slug');
 
    if ( false === $wpdb->insert( $wpdb->terms, $data ) ) {
        return new WP_Error( 'db_insert_error', __( 'Could not insert term into the database.' ), $wpdb->last_error );
    }
 
    $term_id = (int) $wpdb->insert_id;
 
    $wpdb->insert( $wpdb->term_taxonomy, compact( 'term_id', 'taxonomy', 'description', 'parent') + array( 'count' => 0 ) );
    $tt_id = (int) $wpdb->insert_id;
 	
 	$time_end = microtime(true);
 	return array( 'term_id' => $term_id, 'term_taxonomy_id' => $tt_id );

}
?>
