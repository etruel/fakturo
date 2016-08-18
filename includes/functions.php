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
		'echo'				=> 1
	);
 
	$r = wp_parse_args( $args, $defaults );
		
	$itemsPosts = get_posts( array( 
								'post_type'			=> $r['post_type'],
								'post_status'		=> $r['post_status'],
								'meta_key'			=> $r['meta_key'],
								'posts_per_page'	=> $r['posts_per_page'],
							));
	$select = '<select name="'.$r['name'].'" id="'.$r['id'].'" class="'.$r['class'].'">';
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

?>
