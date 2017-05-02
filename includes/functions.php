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
		return new WP_Error( 'incorrect_term_id', __('You has send a incorrect term_id', FAKTURO_TEXT_DOMAIN));
	}
	$term = get_term($term_id, $taxonomy);
	if(is_wp_error($term)) {
		return $term;
	}
	if(!is_object($term)) {
        return new WP_Error( 'incorrect_term_id', __('You has send a incorrect term_id', FAKTURO_TEXT_DOMAIN));
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
	$new_option->text = __( 'Select a option', FAKTURO_TEXT_DOMAIN);
	$new_option->icon = 'dashicons-category';
	$new_option->type = null;
	$select_options[0] = $new_option;

	$new_option = new stdClass();
	$new_option->text = __( 'Settings', FAKTURO_TEXT_DOMAIN);
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
/**
 * Static function fktr_button_new_term
 * @return String with HTML with button add new term | Void with echo html.
 * @since 0.7
 */
function fktr_button_new_term($args) {
 	$defaults = array(
	 	'taxonomy' => 'category', 
	 	'echo' => 0,
	 	'class' => 'button',
	 	'opcional_add_new_item' => '',
	 	'selector_parent_select' => '',
 	);
	if (!isset($args) || !is_array($args)) {
		$args = array();
	} 
	$r = wp_parse_args( $args, $defaults );
	$r['class'] .= ' fktr_btn_taxonomy';
	$tax_name = esc_attr($r['taxonomy']);
	$taxonomy = get_taxonomy($r['taxonomy']);

	$add_new_item_text = $taxonomy->labels->add_new_item;
	if (!empty($r['opcional_add_new_item'])) {
		$add_new_item_text = $r['opcional_add_new_item'];
	}
	$parent_selector = '';
	if (!empty($r['selector_parent_select'])) {
		$parent_selector = ' data-selectorparent="'.$r['selector_parent_select'].'"';
	}
	//print_r($taxonomy);
	$button_html = '<input type="button" class="'.$r['class'].'" value="'.$add_new_item_text.'" data-taxonomy="'.$r['taxonomy'].'"'.$parent_selector.'/>';
	if ($r['echo']) {
		echo $button_html;
	} else {
		return $button_html;
	}
} 
?>
