<?php
add_action( 'admin_init', 'fakturo_product_init' );

function fakturo_product_init() {
	global $pagenow;
	$labels = array( 
		'name' => __( 'Products', FAKTURO_TEXT_DOMAIN ),
		'singular_name' => __( 'Product', FAKTURO_TEXT_DOMAIN ),
		'add_new' => __( 'Add New', FAKTURO_TEXT_DOMAIN ),
		'add_new_item' => __( 'Add New Product', FAKTURO_TEXT_DOMAIN ),
		'edit_item' => __( 'Edit Product', FAKTURO_TEXT_DOMAIN ),
		'new_item' => __( 'New Product', FAKTURO_TEXT_DOMAIN ),
		'view_item' => __( 'View Product', FAKTURO_TEXT_DOMAIN ),
		'search_items' => __( 'Search Products', FAKTURO_TEXT_DOMAIN ),
		'not_found' => __( 'No products found', FAKTURO_TEXT_DOMAIN ),
		'not_found_in_trash' => __( 'No products found in Trash', FAKTURO_TEXT_DOMAIN ),
		'parent_item_colon' => __( 'Parent Product:', FAKTURO_TEXT_DOMAIN ),
		'menu_name' => __( 'Products', FAKTURO_TEXT_DOMAIN ),
	);
	$capabilities = array(
		'publish_post' => 'publish_fakturo_product',
		'publish_posts' => 'publish_fakturo_products',
		'read_post' => 'read_fakturo_product',
		'read_private_posts' => 'read_private_fakturo_products',
		'edit_post' => 'edit_fakturo_product',
		'edit_published_posts' => 'edit_published_fakturo_products',
		'edit_private_posts' => 'edit_private_fakturo_products',
		'edit_posts' => 'edit_fakturo_products',
		'edit_others_posts' => 'edit_others_fakturo_products',
		'delete_post' => 'delete_fakturo_product',
		'delete_posts' => 'delete_fakturo_products',
		'delete_published_posts' => 'delete_published_fakturo_products',
		'delete_private_posts' => 'delete_private_fakturo_products',
		'delete_others_posts' => 'delete_others_fakturo_products',
		);

	$args = array( 
		'labels' => $labels,
		'hierarchical' => false,
		'description' => 'Fakturo Products',
		'supports' => array( 'title', 'thumbnail',/* 'custom-fields' */),
		'register_meta_box_cb' => 'fakturo_product_meta_boxes',
		'public' => true,
		'show_ui' => true,
		'show_in_menu' => 'admin.php?page=fakturo/view/fakturo_admin.php',
		'menu_position' => 5,
		'menu_icon' => '/images/icon_20.png',
		'show_in_nav_menus' => false,
		'publicly_queryable' => false,
		'exclude_from_search' => false,
		'has_archive' => false,
		'query_var' => true,
		'can_export' => true,
		'rewrite' => true
	);

	register_post_type( 'fakturo_product', $args );


	add_filter(	'fakturo_check_product', 'fakturo_check_product',10,1);
	add_action('save_post',  'fakturo_save_product_data');
	// Products list
	add_filter('manage_edit-fakturo_client_columns' ,  'set_edit_fakturo_client_columns');
	add_action('manage_fakturo_client_posts_custom_column','custom_fakturo_client_column',10,2);
	add_filter("manage_edit-fakturo_client_sortable_columns",  "sortable_columns" );

	if( ($pagenow == 'edit.php') && (isset($_GET['post_type']) && $_GET['post_type'] == 'fakturo_product') ) {
		add_filter('post_row_actions' ,  'fakturo_custom_post_quick_actions', 10, 2);
		add_action('pre_get_posts',  'column_orderby');
		add_action('pre_get_posts',  'query_set_only_author' );
	}	

	if( ($pagenow == 'post-new.php' || $pagenow == 'post.php') ) {
		add_action('parent_file',   'facturo_product_tax_menu_correction');
		add_filter('enter_title_here', 'fakturo_product_name_placeholder',10,2);
		add_action('admin_print_styles-post.php', 'fakturo_products_admin_styles');
		add_action('admin_print_styles-post-new.php', 'fakturo_products_admin_styles');
		add_action('admin_print_scripts-post.php', 'fakturo_products_admin_scripts');
		add_action('admin_print_scripts-post-new.php', 'fakturo_products_admin_scripts');
	}
		
}

// component

// highlight the proper top level menu
function facturo_product_tax_menu_correction($parent_file) {
	global $current_screen;
	if ($current_screen->post_type == "fakturo_product") {
		$parent_file = 'fakturo/view/fakturo_admin.php';
	}
	return $parent_file;
}

function fakturo_product_name_placeholder( $title_placeholder , $post ) {
	if($post->post_type == 'fakturo_product')
		$title_placeholder = __('Enter Product name here', FAKTURO_TEXT_DOMAIN );
	return $title_placeholder;
}

function fakturo_save_product_data( $post_id ) {
	return fakturo_save_custom_post_data($post_id, 'fakturo_product', 'fakturo_check_product', 'fakturo_contact_nonce', 'edit-contact');
}

function fakturo_product_meta_boxes() {
	global $post,$product_data;
	$product_data = fakturo_get_custom_post_data($post->ID, 'fakturo_check_product');
	
	add_action('wp_ajax_webcam_shot', 'fakturo_ajax_webcam_shot');
	
	// Remove Custom Fields Metabox
	remove_meta_box( 'postimagediv', 'fakturo_product', 'side' );
	add_meta_box('postimagediv', __('Product Image', FAKTURO_TEXT_DOMAIN ), 'Fakturo_post_thumbnail_meta_box', 'fakturo_product', 'side', 'high');
	add_meta_box( 'fakturo-seller-box', __('Assign Seller', FAKTURO_TEXT_DOMAIN ), 'Fakturo_seller_box','fakturo_product','side', 'high' );
	add_meta_box( 'fakturo-data-box', __('Complete Product Data', FAKTURO_TEXT_DOMAIN ), 'Fakturo_provider_data_box','fakturo_product','normal', 'default' );
	add_meta_box( 'fakturo-options-box', __('Product Contacts', FAKTURO_TEXT_DOMAIN ), 'Fakturo_options_box','fakturo_product','normal', 'default' );
}

function fakturo_products_admin_styles(){
	global $post;
	if($post->post_type != 'fakturo_product') return $post->ID;
	wp_enqueue_style('fakturo-sprite',FAKTURO_URI .'css/sprite.css');	
	add_action('admin_head', 'fakturo_custom_post_head_style');
}

function fakturo_products_admin_scripts(){
	global $post;
	if($post->post_type != 'fakturo_product') return $post->ID;
	wp_register_script('jquery-vsort', FAKTURO_URI .'js/jquery.vSort.min.js', array('jquery'));
	wp_enqueue_script('jquery-vsort');
	wp_register_script('webcam', FAKTURO_URI .'libraries/webcamjs-master/webcam.min.js', array('jquery'));
	wp_enqueue_script('webcam');
	add_action('admin_head', 'fakturo_custom_post_head_scripts');
	add_action('admin_head', 'fakturo_products_head_scripts');
}

function fakturo_products_head_scripts() {
	global $post, $wp_locale, $locale;
	if($post->post_type != 'fakturo_product') return $post->ID;
	$post->post_password = '';
	$visibility = 'public';
	$visibility_trans = __('Public');

	?>
	<script type="text/javascript" language="javascript">
	jQuery(document).ready(function($){
		$('#publish').val('<?php _e('Save Product', FAKTURO_TEXT_DOMAIN ); ?>');
		$('#submitdiv h3 span').text('<?php _e('Update', FAKTURO_TEXT_DOMAIN ); ?>');
		// remove visibility
		$('#visibility').hide();

		// remove channels Most used box
		$('#channel-tabs').remove();
		$('#channel-pop').remove();
		// remove channels Ajax Quick Add 
		$('#channel-adder').remove();
		//-----Click on channel  (Allows just one)
		$(document).on("click", '#channelchecklist input[type=checkbox]', function(event) { 
			var $current = $(this).prop('checked') ; //true or false
			$('#channelchecklist input[type=checkbox]').prop('checked', false);
			$(this).prop('checked', $current );
			//if( $current ){ }
		});

		// remove segments Most used box
		$('#segment-tabs').remove();
		$('#segment-pop').remove();
		// remove segments Ajax Quick Add 
		$('#segment-adder').remove();
		//-----Click on segment (Allows just one)
		$(document).on("click", '#segmentchecklist input[type=checkbox]', function(event) { 
			var $current = $(this).prop('checked') ; //true or false
			$('#segmentchecklist input[type=checkbox]').prop('checked', false);
			$(this).prop('checked', $current );
			//if( $current ){ }
		});

		// remove interests Most used box
		$('#interest-tabs').remove();
		$('#interest-pop').remove();
		// remove interests Ajax Quick Add 
		$('#interest-adder').remove();

		$('#addmoreuc').click(function() {
			oldval = $('#ucfield_max').val();
			jQuery('#ucfield_max').val( parseInt(jQuery('#ucfield_max').val(),10) + 1 );
			newval = $('#ucfield_max').val();
			uc_new= $('.uc_new_field').clone();
			$('div.uc_new_field').removeClass('uc_new_field');
			$('div#uc_ID'+oldval).fadeIn();
			$('input[name="uc_description['+oldval+']"]').focus();
			uc_new.attr('id','uc_ID'+newval);
			$('input', uc_new).eq(0).attr('name','uc_description['+ newval +']');
			$('input', uc_new).eq(1).attr('name','uc_phone['+ newval +']');
			$('.delete', uc_new).eq(0).attr('onclick', "delete_user_contact('#uc_ID"+ newval +"');");
			$('#user_contacts').append(uc_new);
			$('#user_contacts').vSort();
		});	
		
		
		jQuery( "form#post #publish" ).hide();
		jQuery( "form#post #publish" ).after("<input type=\'button\' value=\'<?php _e('Save Product', FAKTURO_TEXT_DOMAIN ); ?>\' class=\'sb_publish button-primary\' /><span class=\'sb_js_errors\'></span>");

		jQuery( ".sb_publish" ).click(function() {			
			if (!error) {
				jQuery( "form#post #publish" ).click();
			} else {
				jQuery(".sb_js_errors").text("<?php _e('There was an error on the page and therefore this page can not yet be published.', FAKTURO_TEXT_DOMAIN ); ?>");
			}
		});

	});		// jQuery
	function delete_user_contact(row_id){
		jQuery(row_id).fadeOut(); 
		jQuery(row_id).remove();
		jQuery('#msgdrag').html('<?php _e('Update Product to save changes.', FAKTURO_TEXT_DOMAIN ); ?>').fadeIn();
	}
	
	</script>
	<?php
}

function fakturo_check_product($options) {
	$fieldsArray = array('email', 'address', 'phone', 'taxpayer', 'states', 'city', 'bank_entity', 
		'bank_account', 'postcode', 'phone', 'cell_phone', 'web', 'active', 'country');
	foreach ($fieldsArray as $field) {
		$product_data[$field]	= (!isset($options[$field])) ? '' : $options[$field];
	}

	$user_contacts = (!isset($options['user_contacts']))? Array() : $options['user_contacts'];
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
	$product_data['user_contacts']= $user_contacts;
	$product_data['user_aseller']= (isset($options['user_aseller']) && !empty($options['user_aseller']) ) ? $options['user_aseller'] : 0 ;

	return $product_data;
}