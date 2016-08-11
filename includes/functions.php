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

function get_fakturo_term($term_id, $taxonomy, $field = null) {
	
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


?>
