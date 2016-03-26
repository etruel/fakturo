<?php
add_action( 'admin_init', 'fakturo_provider_init' );

function fakturo_provider_init() {
	global $pagenow;
	$labels = array( 
		'name' => __( 'Providers', FAKTURO_TEXT_DOMAIN ),
		'singular_name' => __( 'Provider', FAKTURO_TEXT_DOMAIN ),
		'add_new' => __( 'Add New', FAKTURO_TEXT_DOMAIN ),
		'add_new_item' => __( 'Add New Provider', FAKTURO_TEXT_DOMAIN ),
		'edit_item' => __( 'Edit Provider', FAKTURO_TEXT_DOMAIN ),
		'new_item' => __( 'New Provider', FAKTURO_TEXT_DOMAIN ),
		'view_item' => __( 'View Provider', FAKTURO_TEXT_DOMAIN ),
		'search_items' => __( 'Search Providers', FAKTURO_TEXT_DOMAIN ),
		'not_found' => __( 'No providers found', FAKTURO_TEXT_DOMAIN ),
		'not_found_in_trash' => __( 'No providers found in Trash', FAKTURO_TEXT_DOMAIN ),
		'parent_item_colon' => __( 'Parent Provider:', FAKTURO_TEXT_DOMAIN ),
		'menu_name' => __( 'Providers', FAKTURO_TEXT_DOMAIN ),
	);
	$capabilities = array(
		'publish_post' => 'publish_fakturo_provider',
		'publish_posts' => 'publish_fakturo_providers',
		'read_post' => 'read_fakturo_provider',
		'read_private_posts' => 'read_private_fakturo_providers',
		'edit_post' => 'edit_fakturo_provider',
		'edit_published_posts' => 'edit_published_fakturo_providers',
		'edit_private_posts' => 'edit_private_fakturo_providers',
		'edit_posts' => 'edit_fakturo_providers',
		'edit_others_posts' => 'edit_others_fakturo_providers',
		'delete_post' => 'delete_fakturo_provider',
		'delete_posts' => 'delete_fakturo_providers',
		'delete_published_posts' => 'delete_published_fakturo_providers',
		'delete_private_posts' => 'delete_private_fakturo_providers',
		'delete_others_posts' => 'delete_others_fakturo_providers',
		);

	$args = array( 
		'labels' => $labels,
		'hierarchical' => false,
		'description' => 'Fakturo Providers',
		'supports' => array( 'title', 'thumbnail',/* 'custom-fields' */),
		'register_meta_box_cb' => 'fakturo_provider_meta_boxes',
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

	register_post_type( 'fakturo_provider', $args );


	add_filter(	'fakturo_check_provider', 'fakturo_check_provider',10,1);
	add_action('save_post',  'fakturo_save_provider_data');
	// Providers list
	add_filter('manage_edit-fakturo_client_columns' ,  'set_edit_fakturo_client_columns');
	add_action('manage_fakturo_client_posts_custom_column','custom_fakturo_client_column',10,2);
	add_filter("manage_edit-fakturo_client_sortable_columns",  "sortable_columns" );

	if( ($pagenow == 'edit.php') && (isset($_GET['post_type']) && $_GET['post_type'] == 'fakturo_provider') ) {
		add_filter('post_row_actions' ,  'fakturo_provider_quick_actions', 10, 2);
		add_action('pre_get_posts',  'column_orderby');
		add_action('pre_get_posts',  'query_set_only_author' );
	}	

	if( ($pagenow == 'post-new.php' || $pagenow == 'post.php') ) {
		add_action('parent_file',   'facturo_provider_tax_menu_correction');
		add_filter('enter_title_here', 'fakturo_provider_name_placeholder',10,2);
		add_action('admin_print_styles-post.php', 'fakturo_providers_admin_styles');
		add_action('admin_print_styles-post-new.php', 'fakturo_providers_admin_styles');
		add_action('admin_print_scripts-post.php', 'fakturo_providers_admin_scripts');
		add_action('admin_print_scripts-post-new.php', 'fakturo_providers_admin_scripts');
	}
		
}

// component

// highlight the proper top level menu
function facturo_provider_tax_menu_correction($parent_file) {
	global $current_screen;
	if ($current_screen->post_type == "fakturo_provider") {
		$parent_file = 'fakturo/view/fakturo_admin.php';
	}
	return $parent_file;
}

function fakturo_provider_name_placeholder( $title_placeholder , $post ) {
	if($post->post_type == 'fakturo_provider')
		$title_placeholder = __('Enter Provider name here', FAKTURO_TEXT_DOMAIN );
	return $title_placeholder;
}

function fakturo_save_provider_data( $post_id ) {
	global $post, $cfg;
	if((defined('DOING_AJAX') && DOING_AJAX) || isset($_REQUEST['bulk_edit'])) {
		return $post_id;
	}
	if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || (defined('DOING_AJAX') && DOING_AJAX) || isset($_REQUEST['bulk_edit']))
		return $post_id;
	if ( !wp_verify_nonce( @$_POST['fakturo_contact_nonce'], 'edit-contact' ) )
		return $post_id;
	if($post->post_type != 'fakturo_provider') return $post_id;
	// Stop WP from clearing custom fields on autosave, and also during ajax requests (e.g. quick edit) and bulk edits.

	$nivelerror = error_reporting(E_ERROR | E_WARNING | E_PARSE);

	$_POST['ID']=$post_id;
	$provider = array();
	$provider = apply_filters('fakturo_check_provider', $_POST);
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
	fakturo_update_provider($post_id, $provider);

	return $post_id ;
}

function fakturo_update_provider( $provider_id, $provider_data){
	foreach ( $provider_data as $field_key => $field_values ) {
		if(!isset($field_values)) continue;
		add_post_meta( $provider_id, $field_key, $field_values, true )  or
			update_post_meta( $provider_id, $field_key, $field_values);
	}
}

function fakturo_get_provider_data( $provider_id = 0){
	global $post, $post_id;
	if($provider_id==0) {
		if( isset( $post->ID ) ) {
			$provider_id==$post->ID;
		}elseif( isset( $post_id ) ) {
			$provider_id==$post_id;
		}else {
			return false;
		}
	}
	$provider_data = array();
	$custom_fields = get_post_custom($client_id) ;
	foreach ( $custom_fields as $field_key => $field_values ) {
		if(!isset($field_values[0])) continue;
		$provider_data[$field_key] = get_post_meta( $provider_id, $field_key, true );
	}	

	$provider_data = apply_filters('fakturo_check_provider', $provider_data );

	return $provider_data;
}

function fakturo_check_provider($options) {
	$fieldsArray = array('email', 'address', 'phone', 'taxpayer', 'states', 'city', 'bank_entity', 
		'bank_account', 'postcode', 'phone', 'cell_phone', 'web', 'active', 'country');
	foreach ($fieldsArray as $field) {
		$provider_data[$field]	= (!isset($options[$field])) ? '' : $options[$field];
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
	$provider_data['user_contacts']= $user_contacts;
	$provider_data['user_aseller']= (isset($options['user_aseller']) && !empty($options['user_aseller']) ) ? $options['user_aseller'] : 0 ;

	return $provider_data;
}


// helper

function fakturo_provider_meta_boxes() {
	global $post,$provider_data;
	$provider_data = fakturo_get_custom_post_data($post->ID, 'fakturo_check_provider');
	
	add_action('wp_ajax_webcam_shot', 'fakturo_ajax_webcam_shot');
	
	// Remove Custom Fields Metabox
	remove_meta_box( 'postimagediv', 'fakturo_provider', 'side' );
	add_meta_box('postimagediv', __('Provider Image', FAKTURO_TEXT_DOMAIN ), 'Fakturo_post_thumbnail_meta_box', 'fakturo_provider', 'side', 'high');
	add_meta_box( 'fakturo-seller-box', __('Assign Seller', FAKTURO_TEXT_DOMAIN ), 'Fakturo_seller_box','fakturo_provider','side', 'high' );
	add_meta_box( 'fakturo-data-box', __('Complete Provider Data', FAKTURO_TEXT_DOMAIN ), 'Fakturo_provider_data_box','fakturo_provider','normal', 'default' );
	add_meta_box( 'fakturo-options-box', __('Provider Contacts', FAKTURO_TEXT_DOMAIN ), 'Fakturo_options_box','fakturo_provider','normal', 'default' );
}

function fakturo_providers_admin_styles(){
	global $post;
	if($post->post_type != 'fakturo_provider') return $post->ID;
	wp_enqueue_style('fakturo-sprite',FAKTURO_URI .'css/sprite.css');	
	add_action('admin_head', 'fakturo_providers_head_style');
}

function fakturo_providers_admin_scripts(){
	global $post;
	if($post->post_type != 'fakturo_provider') return $post->ID;
	wp_register_script('jquery-vsort', FAKTURO_URI .'js/jquery.vSort.min.js', array('jquery'));
	wp_enqueue_script('jquery-vsort');
	wp_register_script('webcam', FAKTURO_URI .'libraries/webcamjs-master/webcam.min.js', array('jquery'));
	wp_enqueue_script('webcam');
	add_action('admin_head', 'fakturo_providers_head_scripts');
}


function fakturo_providers_head_style() {
	global $post;
	if($post->post_type != 'fakturo_provider') return $post->ID;
		?>
<style type="text/css">
.fieldate {width: 135px !important;}
.b {font-weight: bold;}
.hide {display: none;}
.updated.notice-success a {display: none;}
#edit-slug-box, #post-preview{display: none;}
#poststuff h3 {background-color: #6EDA67;}

#msgdrag {display:none;color:red;padding: 0 0 0 20px;font-weight: 600;font-size: 1em;}
.uc_header {padding: 0 0 0 30px;font-weight: 600;font-size: 0.9em;}
div.uc_column {float: left;width: 19%;}
.uc_actions{margin-left: 5px;}
.delete{color: #F88;font-size: 1.6em;}
.delete:hover{color: red;}
.delete:before { content: "\2718";}
.add:before { content: "\271A";}

#snapshot_container_wrapper {
	position:relative; border:3px solid #eaeaea; margin:15px 0; -moz-border-radius: 12px; -webkit-border-radius: 12px;
	-moz-box-shadow: 0px 0px 6px rgba(0,0,0,.25) inset, 0px 0px 6px black inset;
	-webkit-box-shadow: 0px 0px 6px rgba(0,0,0,.25) inset, 0px 0px 6px black inset;
	background-color:#c7c7c7;
	padding:10px;
	overflow:hidden;
}
#snapshot_container_wrapper{text-align:center;}
.cuit_ok {color: #2FB42F;text-shadow: 1px 1px 1px #eee;}
.cuit_err {color: #FF3232;font-weight: 700;text-shadow: 1px 1px 1px #eee;}
.sb_js_errors {float: right;color: #FF3232;}
		</style><?php

}

function fakturo_providers_head_scripts() {
	global $post, $wp_locale, $locale;
	if($post->post_type != 'fakturo_provider') return $post->ID;
	$post->post_password = '';
	$visibility = 'public';
	$visibility_trans = __('Public');

	?>
	<script type="text/javascript" language="javascript">
	jQuery(document).ready(function($){
		$('#publish').val('<?php _e('Save Provider', FAKTURO_TEXT_DOMAIN ); ?>');
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
		
		//*****************************
		

		showSnapshot = function() {
			$('#snapshot_btn').css('display', 'none');
			$('#my_camera').css('display', 'block');
			$('#take_snapshot').css('display', 'block');
			$('#set-post-thumbnail').css('display', 'none');
			$('#remove-post-thumbnail').css('display', 'none');
			$('#snapshot_cancel').css('display', 'block');
			
			if (navigator.userAgent.toLowerCase().indexOf('chrome') > -1) {
				Webcam.set({
					width: 230,
					height: 150,
					image_format: 'jpeg',
					jpeg_quality: 90,
					force_flash: true
				})
			} else {
				Webcam.set({
					width: 230,
					height: 150,
					image_format: 'jpeg',
					jpeg_quality: 90,
					force_flash: false
				});
			}
			Webcam.attach( '#my_camera' );
		}

		take_snapshot = function() {
			Webcam.snap( function(data_uri) {
				$('#my_camera').css('display', 'none');
				$('#take_snapshot').css('display', 'none');
				$('input[name="webcam_image"]').val(data_uri);
				$('#snap_image').attr('src', data_uri);
				$('#snap_image').css('display', 'block');
				$('#snapshot_reset').css('display', 'block');
			} );
		}

		reset_webcam = function() {
			$('#snap_image').attr('src', "");
			$('input[name="webcam_image"]').val("");
			$('#snap_image').css('display', 'none');
			$('#snapshot_reset').css('display', 'none');
			$('#my_camera').css('display', 'block');
			$('#take_snapshot').css('display', 'block');
		}

		snapshot_cancel = function() {
			$('#snap_image').attr('src', "");
			$('input[name="webcam_image"]').val("");
			$('#snap_image').css('display', 'none');
			$('#snapshot_btn').css('display', 'block');
			$('#snapshot_reset').css('display', 'none');
			$('#take_snapshot').css('display', 'none');
			$('#snapshot_cancel').css('display', 'none');
			$('#my_camera').css('display', 'none');
			$('#set-post-thumbnail').css('display', 'block');
			$('#remove-post-thumbnail').css('display', 'block');
			Webcam.reset();
		}

		WPSetThumbnailHTML = function(html){
			$('.featured-image-client', '#postimagediv').html(html);
		};

		wp.media.featuredImage.set = function( id ) {
			var settings = wp.media.view.settings;

			settings.post.featuredImageId = id;

			wp.media.post( 'set-post-thumbnail', {
				json:         true,
				post_id:      settings.post.id,
				thumbnail_id: settings.post.featuredImageId,
				_wpnonce:     settings.post.nonce
			}).done( function( html ) {
				$('.featured-image-client', '#postimagediv').html(html);
			});
		}

		function CPcuitValido(cuit) {
			if(cuit == ''){
				return true;
			}
			if (!(cuit.match(/^\d{2}([\-_])?\d{8}([\-_])?\d{1}$/))) {
				return false;
			}
			cuit = cuit.toString().replace(/[-_]/g, '');
			var vec = new Array(10);
			var esCuit = false;
			var cuit_rearmado = '';
			for (i=0; i < cuit.length; i++) {
				caracter = cuit.charAt( i);
				if ( caracter.charCodeAt(0) >= 48 && caracter.charCodeAt(0) <= 57 )     {
					cuit_rearmado += caracter;
				}
			}
			cuit=cuit_rearmado;
			if ( cuit.length != 11) {  // si no estan todos los digitos
				esCuit=false;
			} else {
				x=i=dv=0;
				// Multiplico los dÃ­gitos.
				vec[0] = cuit.charAt(0) * 5;
				vec[1] = cuit.charAt(1) * 4;
				vec[2] = cuit.charAt(2) * 3;
				vec[3] = cuit.charAt(3) * 2;
				vec[4] = cuit.charAt(4) * 7;
				vec[5] = cuit.charAt(5) * 6;
				vec[6] = cuit.charAt(6) * 5;
				vec[7] = cuit.charAt(7) * 4;
				vec[8] = cuit.charAt(8) * 3;
				vec[9] = cuit.charAt(9) * 2;

				// Suma cada uno de los resultado.
				for( i = 0;i<=9; i++) {
					x += vec[i];
				}
				dv = (11 - (x % 11)) % 11;
				if (dv == cuit.charAt(10) ) {
					esCuit=true;
				}
			}
			if ( !esCuit ) {
				return false;
			}
			return true;
		}
		var error = false
		$('#taxpayer').keyup(function(){
			if(this.value==''){
				$('#cuit_validation').text('');
			} else if(!CPcuitValido(this.value)){
				$('#cuit_validation').text('Invalid cuit').removeClass('cuit_ok').addClass('cuit_err');
				error = true;
			} else {
				$('#cuit_validation').text('Cuit OK').removeClass('cuit_err').addClass('cuit_ok');
				error = false;
			}
		});
		
		jQuery( "form#post #publish" ).hide();
		jQuery( "form#post #publish" ).after("<input type=\'button\' value=\'<?php _e('Save Client', FAKTURO_TEXT_DOMAIN ); ?>\' class=\'sb_publish button-primary\' /><span class=\'sb_js_errors\'></span>");

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
		jQuery('#msgdrag').html('<?php _e('Update Provider to save changes.', FAKTURO_TEXT_DOMAIN ); ?>').fadeIn();
	}
	
	</script>
	<?php
}

function Fakturo_provider_data_box( $post ) {  
	global $post, $provider_data;
	?>
	<table class="form-table">
	<tbody>
	<tr class="user-facebook-wrap">
		<th><label for="taxpayer"><?php _e("Taxpayer ID", FAKTURO_TEXT_DOMAIN ) ?>	</label></th>
		<td>
			<input id="taxpayer" type="text" name="taxpayer" value="<?php echo $provider_data['taxpayer'] ?>" class="regular-text">
			<span id="cuit_validation"></span>
			<div style="font-size:0.85em;" id="cuit_validation_note"><?php _e("Cuit number's validation only. Check www.afip.gov.ar", FAKTURO_TEXT_DOMAIN ) ?></div>
		</td>
	</tr>
	<tr class="user-address-wrap">
		<th><label for="address"><?php _e("Address", FAKTURO_TEXT_DOMAIN ) ?>	</label></th>
		<td><input type="text" name="address" id="address" value="<?php echo $provider_data['address'] ?>" class="regular-text"></td>
	</tr>
	<tr class="user-facebook-wrap">
		<th><label for="country"><?php _e("Country", FAKTURO_TEXT_DOMAIN ) ?>	</label></th>
		<td><?php fakturo_client_select_data('fakturo_countries', 'country', $provider_data); ?></td>
	</tr>
	<tr class="user-facebook-wrap">
		<th><label for="states"><?php _e("States", FAKTURO_TEXT_DOMAIN ) ?>	</label></th>
		<td><?php fakturo_client_select_data('fakturo_states', 'states', $provider_data); ?></td>
	</tr>
	<tr class="user-facebook-wrap">
		<th><label for="city"><?php _e("City", FAKTURO_TEXT_DOMAIN ) ?>	</label></th>
		<td><input id="city" type="text" name="city" value="<?php echo $provider_data['city'] ?>" class="regular-text"></td>
	</tr>
	<tr class="user-facebook-wrap">
		<th><label for="bank_entity"><?php _e("Bank Entity", FAKTURO_TEXT_DOMAIN ) ?>	</label></th>
		<td><?php fakturo_client_select_data('fakturo_bank_entities', 'bank_entity', $provider_data); ?></td>
	</tr>
	<tr class="user-facebook-wrap">
		<th><label for="bank_account"><?php _e("Bank Account", FAKTURO_TEXT_DOMAIN ) ?>	</label></th>
		<td><input id="bank_account" type="text" name="bank_account" value="<?php echo $provider_data['bank_account'] ?>" class="regular-text"></td>
	</tr>
	<tr class="user-facebook-wrap">
		<th><label for="postcode"><?php _e("Postcode", FAKTURO_TEXT_DOMAIN ) ?>	</label></th>
		<td><input id="postcode" type="text" name="postcode" value="<?php echo $provider_data['postcode'] ?>" class="regular-text"></td>
	</tr>
	<tr class="user-facebook-wrap">
		<th><label for="phone"><?php _e("Phone", FAKTURO_TEXT_DOMAIN ) ?>	</label></th>
		<td><input id="phone" type="text" name="phone" value="<?php echo $provider_data['phone'] ?>" class="regular-text"></td>
	</tr>
	<tr class="user-facebook-wrap">
		<th><label for="cell_phone"><?php _e("Cell phone", FAKTURO_TEXT_DOMAIN ) ?>	</label></th>
		<td><input id="cell_phone" type="text" name="cell_phone" value="<?php echo $provider_data['cell_phone'] ?>" class="regular-text"></td>
	</tr>
	<tr class="user-email-wrap">
		<th><label for="email"><?php _e("E-mail", FAKTURO_TEXT_DOMAIN ) ?></label></th>
		<td><input type="email" name="email" id="email" value="<?php echo $provider_data['email'] ?>" class="regular-text ltr"></td>
	</tr>
	<tr class="user-facebook-wrap">
		<th><label for="web"><?php _e("Web", FAKTURO_TEXT_DOMAIN ) ?></label></th>
		<td><input id="web" type="text" name="web" value="<?php echo $provider_data['web'] ?>" class="regular-text"></td>
	</tr>
	<tr class="user-facebook-wrap">
		<th><label for="active"><?php _e("Active", FAKTURO_TEXT_DOMAIN ) ?>	</label></th>
		<td><input id="active" type="checkbox" name="active" value="1" <?php if ($provider_data['active']) { echo 'checked="checked"'; } ?>></td>
	</tr>
	</tbody></table>
	<?php
}