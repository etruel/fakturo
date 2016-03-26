<?php
add_action( 'admin_init', 'fakturo_client_init' );

function fakturo_client_init() {
	global $pagenow;
	$labels = array( 
		'name' => __( 'Clients', FAKTURO_TEXT_DOMAIN ),
		'singular_name' => __( 'Client', FAKTURO_TEXT_DOMAIN ),
		'add_new' => __( 'Add New', FAKTURO_TEXT_DOMAIN ),
		'add_new_item' => __( 'Add New Client', FAKTURO_TEXT_DOMAIN ),
		'edit_item' => __( 'Edit Client', FAKTURO_TEXT_DOMAIN ),
		'new_item' => __( 'New Client', FAKTURO_TEXT_DOMAIN ),
		'view_item' => __( 'View Client', FAKTURO_TEXT_DOMAIN ),
		'search_items' => __( 'Search Clients', FAKTURO_TEXT_DOMAIN ),
		'not_found' => __( 'No clients found', FAKTURO_TEXT_DOMAIN ),
		'not_found_in_trash' => __( 'No clients found in Trash', FAKTURO_TEXT_DOMAIN ),
		'parent_item_colon' => __( 'Parent Client:', FAKTURO_TEXT_DOMAIN ),
		'menu_name' => __( 'Clients', FAKTURO_TEXT_DOMAIN ),
	);
	$capabilities = array(
		'publish_post' => 'publish_fakturo_client',
		'publish_posts' => 'publish_fakturo_clients',
		'read_post' => 'read_fakturo_client',
		'read_private_posts' => 'read_private_fakturo_clients',
		'edit_post' => 'edit_fakturo_client',
		'edit_published_posts' => 'edit_published_fakturo_clients',
		'edit_private_posts' => 'edit_private_fakturo_clients',
		'edit_posts' => 'edit_fakturo_clients',
		'edit_others_posts' => 'edit_others_fakturo_clients',
		'delete_post' => 'delete_fakturo_client',
		'delete_posts' => 'delete_fakturo_clients',
		'delete_published_posts' => 'delete_published_fakturo_clients',
		'delete_private_posts' => 'delete_private_fakturo_clients',
		'delete_others_posts' => 'delete_others_fakturo_clients',
		);

	$args = array( 
		'labels' => $labels,
		'hierarchical' => false,
		'description' => 'Fakturo Clients',
		'supports' => array( 'title', 'thumbnail',/* 'custom-fields' */),
		'register_meta_box_cb' => 'fakturo_client_meta_boxes',
		'public' => true,
		'show_ui' => true,
		'show_in_menu' => 'admin.php?page=fakturo/view/fakturo_admin.php',
		'menu_position' => 3,
		'menu_icon' => '/images/icon_20.png',
		'show_in_nav_menus' => false,
		'publicly_queryable' => false,
		'exclude_from_search' => false,
		'has_archive' => false,
		'query_var' => true,
		'can_export' => true,
		'rewrite' => true,
//		'capabilities' => $capabilities,
	);

	register_post_type( 'fakturo_client', $args );


	add_filter(	'fakturo_check_client', 'fakturo_check_client',10,1);
	add_action('save_post',  'fakturo_save_client_data');
	// Clients list
	add_filter('manage_edit-fakturo_client_columns' ,  'set_edit_fakturo_client_columns');
	add_action('manage_fakturo_client_posts_custom_column','custom_fakturo_client_column',10,2);
	add_filter("manage_edit-fakturo_client_sortable_columns",  "sortable_columns" );

	if( ($pagenow == 'edit.php') && (isset($_GET['post_type']) && $_GET['post_type'] == 'fakturo_client') ) {
		add_filter('post_row_actions' ,  'fakturo_client_quick_actions', 10, 2);
		add_action('pre_get_posts',  'column_orderby');
		add_action('pre_get_posts',  'query_set_only_author' );
//			add_action('admin_print_styles-edit.php', 'list_admin_styles'));
//			add_action('admin_print_scripts-edit.php', 'list_admin_scripts'));
	}	

	if( ($pagenow == 'post-new.php' || $pagenow == 'post.php') ) {
		add_action('parent_file',   'facturo_client_tax_menu_correction');
		add_filter('enter_title_here', 'fakturo_client_name_placeholder',10,2);
		add_action('admin_print_styles-post.php', 'fakturo_clients_admin_styles');
		add_action('admin_print_styles-post-new.php', 'fakturo_clients_admin_styles');
		add_action('admin_print_scripts-post.php', 'fakturo_clients_admin_scripts');
		add_action('admin_print_scripts-post-new.php', 'fakturo_clients_admin_scripts');
	}
		
}


// highlight the proper top level menu
function facturo_client_tax_menu_correction($parent_file) {
	global $current_screen;
	if ($current_screen->post_type == "fakturo_client") {
		$parent_file = 'fakturo/view/fakturo_admin.php';
	}
	return $parent_file;
}

function fakturo_check_client($options) {
	$fieldsArray = array('email', 'address', 'phone', 'cellular', 'facebook', 'taxpayer', 'states', 'city', 'payment_type', 'price_scale', 'bank_entity', 
		'bank_account', 'tax_condition', 'postcode', 'phone', 'cell_phone', 'web', 'credit_limit', 'credit_interval', 'credit_currency', 'active', 'currency');
	foreach ($fieldsArray as $field) {
		$client_data[$field]	= (!isset($options[$field])) ? '' : $options[$field];
	}

	$user_contacts = (!isset($options['user_contacts']))? Array() : $options['user_contacts'];
	// Proceso los array sacando los que estan en blanco
//		if(!isset($options['user_contacts'])) $user_contacts = Array() ;
	if(isset($options['uc_description'])) {
		foreach($options['uc_description'] as $id => $cf_value) {       
			$uc_description = esc_attr($options['uc_description'][$id]);
			$uc_phone = esc_attr($options['uc_phone'][$id]);
			$uc_email = esc_attr($options['uc_email'][$id]);
			$uc_position = esc_attr($options['uc_position'][$id]);
			$uc_address = esc_attr($options['uc_address'][$id]);
			if(!empty($uc_description))  {
				$user_contacts['description'][]=$uc_description ;
				$user_contacts['phone'][]=$uc_phone ;
				$user_contacts['email'][]=$uc_email ;
				$user_contacts['position'][]=$uc_position ;
				$user_contacts['address'][]=$uc_address ;
			}
		}
	}
	if(!isset($user_contacts['description'])) {
		$user_contacts = array(
			'description'=>array(''),
			'phone'=>array(''),
			'email'=>array(''),
			'position'=>array(''),
			'address'=>array(''),	
		);
	}
	$client_data['user_contacts']= $user_contacts;
	$client_data['user_aseller']= (isset($options['user_aseller']) && !empty($options['user_aseller']) ) ? $options['user_aseller'] : 0 ;

	return $client_data;
}

function fakturo_get_client_data( $client_id = 0){
	global $post, $post_id;
	if($client_id==0) {
		if( isset( $post->ID ) ) {
			$client_id==$post->ID;
		}elseif( isset( $post_id ) ) {
			$client_id==$post_id;
		}else {
			return false;
		}
	}
	$client_data = array();
	$custom_fields = get_post_custom($client_id) ;
	foreach ( $custom_fields as $field_key => $field_values ) {
		if(!isset($field_values[0])) continue;
		$client_data[$field_key] = get_post_meta( $client_id, $field_key, true );
	}	

	$client_data = apply_filters('fakturo_check_client', $client_data );

	return $client_data;
}

function fakturo_get_custom_post_data( $id = 0, $filterName){
	global $post, $post_id;
	if($id==0) {
		if( isset( $post->ID ) ) {
			$id==$post->ID;
		}elseif( isset( $post_id ) ) {
			$id==$post_id;
		}else {
			return false;
		}
	}
	$custom_post_data = array();
	$custom_fields = get_post_custom($id) ;
	foreach ( $custom_fields as $field_key => $field_values ) {
		if(!isset($field_values[0])) continue;
		$custom_post_data[$field_key] = get_post_meta( $id, $field_key, true );
	}	

	$custom_post_data = apply_filters($filterName, $custom_post_data );

	return $custom_post_data;
}


function fakturo_update_client( $client_id, $client_data){
	foreach ( $client_data as $field_key => $field_values ) {
		if(!isset($field_values)) continue;
		//echo $field_key . '=>' . $field_values[0];
		add_post_meta( $client_id, $field_key, $field_values, true )  or
			update_post_meta( $client_id, $field_key, $field_values);
	}
}

function fakturo_save_client_data( $post_id ) {
	global $post, $cfg;
	if((defined('DOING_AJAX') && DOING_AJAX) || isset($_REQUEST['bulk_edit'])) {
		//save_quick_edit_post($post_id);
		return $post_id;
	}
	if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || (defined('DOING_AJAX') && DOING_AJAX) || isset($_REQUEST['bulk_edit']))
		return $post_id;
	if ( !wp_verify_nonce( @$_POST['fakturo_contact_nonce'], 'edit-contact' ) )
		return $post_id;
	if($post->post_type != 'fakturo_client') return $post_id;
	// Stop WP from clearing custom fields on autosave, and also during ajax requests (e.g. quick edit) and bulk edits.

	$nivelerror = error_reporting(E_ERROR | E_WARNING | E_PARSE);

	$_POST['ID']=$post_id;
	$client = array();
	$client = apply_filters('fakturo_check_client', $_POST);
	error_reporting($nivelerror);

	if (isset($_POST['webcam_image']) && $_POST['webcam_image'] != NULL ) {
		delete_post_meta($post_id, '_thumbnail_id');
		$filename = "webcam_image_".microtime().'.jpg';
		$file = wp_upload_bits($filename, null, base64_decode(substr($_POST['webcam_image'], 23)));
		if ($file['error'] == FALSE) {
			$wp_filetype = wp_check_filetype($filename, null );
			$attachment = array(
				'post_mime_type' => $wp_filetype['type'],
				'post_parent' => $post_id,
				'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
				'post_content' => '',
				'post_status' => 'inherit'
			);
			$attachment_id = wp_insert_attachment( $attachment, $file['file'], $post_id );
			if (!is_wp_error($attachment_id)) {
				require_once(ABSPATH . "wp-admin" . '/includes/image.php');
				$attachment_data = wp_generate_attachment_metadata( $attachment_id, $file['file'] );
				wp_update_attachment_metadata( $attachment_id,  $attachment_data );
			}

			add_post_meta($post_id, '_thumbnail_id', $attachment_id);
		}
	}
	fakturo_update_client($post_id, $client);

	return $post_id ;
}



function custom_fakturo_client_column($columns) { //this function display the columns contents
	switch ( $column ) {
	  case 'status':
		//echo $event_data['event_posttype']; 
		break;
	}

}

function set_edit_fakturo_client_columns($columns) { //this function display the columns headings
	$new_columns = array(
		'title' => __('Client Name', FAKTURO_TEXT_DOMAIN),
		'date' => __('Added', FAKTURO_TEXT_DOMAIN),
	);
	return $new_columns;
	//return wp_parse_args($new_columns, $columns);
}
// Make these columns sortable
function sortable_columns($columns) {
	$custom = array(
	//	'start' => 'startdate',
	);
	return wp_parse_args($custom, $columns);
}
function column_orderby($query ) {
	global $pagenow, $post_type;
	$orderby = $query->get( 'orderby');
	if( 'edit.php' != $pagenow || empty( $orderby ) )
		return;
	switch($orderby) {
		case 'startdate':
			$meta_group = array('key' => 'fromdate','type' => 'numeric');
			$query->set( 'meta_query', array( 'sort_column'=>'startdate', $meta_group ) );
			$query->set( 'meta_key','fromdate' );
			$query->set( 'orderby','meta_value_num' );

			break;

		default:
			break;
	}
} 
	// Show only posts and media related to logged in author
function query_set_only_author( $wp_query ) {
	global $current_user;
	if( is_admin() && !current_user_can('edit_others_fakturo_clients') ) {
		$wp_query->set( 'author', $current_user->ID );
		add_filter('views_edit-fakturo_client', 'fakturo_clients_fix_post_counts');
	}
}

// Fix post counts
function fakturo_clients_fix_post_counts($views) {
	global $current_user, $wp_query;
	if( !isset($wp_query->query_vars['post_status']))		$wp_query->query_vars['post_status']=null;
	unset($views['mine']);
	$types = array(
		array( 'status' =>  NULL ),
		array( 'status' => 'publish' ),
		array( 'status' => 'draft' ),
		array( 'status' => 'pending' ),
		array( 'status' => 'trash' )
	);
	foreach( $types as $type ) {
		$query = array(
			'author'      => $current_user->ID,
			'post_type'   => 'fakturo_client',
			'post_status' => $type['status']
		);
		$result = new WP_Query($query);
		if( $type['status'] == NULL ):
			$class = ($wp_query->query_vars['post_status'] == NULL) ? ' class="current"' : '';
			$views['all'] = sprintf(__('<a href="%s"'. $class .'>All <span class="count">(%d)</span></a>', 'all'),
				admin_url('edit.php?post_type=fakturo_client'),
				$result->found_posts);
		elseif( $type['status'] == 'publish' ):
			$class = ($wp_query->query_vars['post_status'] == 'publish') ? ' class="current"' : '';
			$views['publish'] = sprintf(__('<a href="%s"'. $class .'>Published <span class="count">(%d)</span></a>', 'publish'),
				admin_url('edit.php?post_status=publish&post_type=fakturo_client'),
				$result->found_posts);
		elseif( $type['status'] == 'draft' ):
			$class = ($wp_query->query_vars['post_status'] == 'draft') ? ' class="current"' : '';
			$views['draft'] = sprintf(__('<a href="%s"'. $class .'>Draft'. ((sizeof($result->posts) > 1) ? "s" : "") .' <span class="count">(%d)</span></a>', 'draft'),
				admin_url('edit.php?post_status=draft&post_type=fakturo_client'),
				$result->found_posts);
		elseif( $type['status'] == 'pending' ):
			$class = ($wp_query->query_vars['post_status'] == 'pending') ? ' class="current"' : '';
			$views['pending'] = sprintf(__('<a href="%s"'. $class .'>Pending <span class="count">(%d)</span></a>', 'pending'),
				admin_url('edit.php?post_status=pending&post_type=fakturo_client'),
				$result->found_posts);
		elseif( $type['status'] == 'trash' ):
			$class = ($wp_query->query_vars['post_status'] == 'trash') ? ' class="current"' : '';
			$views['trash'] = sprintf(__('<a href="%s"'. $class .'>Trash <span class="count">(%d)</span></a>', 'trash'),
				admin_url('edit.php?post_status=trash&post_type=fakturo_client'),
				$result->found_posts);
		endif;
	}
	return $views;
}
//change actions from custom post type list
function fakturo_client_quick_actions( $actions ) {
/*		global $post;
	if( $post->post_type == 'fakturo_client_quick_actions' ) {
*/	//		unset( $actions['edit'] );
		unset( $actions['view'] );
//		unset( $actions['trash'] );
		unset( $actions['inline hide-if-no-js'] );
		unset( $actions['clone'] );
		unset( $actions['edit_as_new_draft'] );
//		}
	return $actions;
}

?>