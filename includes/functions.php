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

?>
