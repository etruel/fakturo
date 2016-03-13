<?php

function fakturo_client_name_placeholder( $title_placeholder , $post ) {
	if($post->post_type == 'fakturo_client')
		$title_placeholder = __('Enter Client name here', FAKTURO_TEXT_DOMAIN );
	return $title_placeholder;
}

function fakturo_client_meta_boxes() {
	global $post,$client_data;
	$client_data = fakturo_get_client_data($post->ID);
	
	add_action('wp_ajax_webcam_shot', 'fakturo_ajax_webcam_shot');
	
	// Remove Custom Fields Metabox
	//remove_meta_box( 'postcustom','fakturo_client','normal' ); 
	//	add_meta_box( $id, $title, $callback, $post_type, $context, $priority, $callback_args );
	remove_meta_box( 'postimagediv', 'fakturo_client', 'side' );
	add_meta_box('postimagediv', __('Client Image', FAKTURO_TEXT_DOMAIN ), 'Fakturo_post_thumbnail_meta_box', 'fakturo_client', 'side', 'high');
	add_meta_box( 'fakturo-seller-box', __('Assign Seller', FAKTURO_TEXT_DOMAIN ), 'Fakturo_seller_box','fakturo_client','side', 'high' );
	add_meta_box( 'fakturo-data-box', __('Complete Client Data', FAKTURO_TEXT_DOMAIN ), 'Fakturo_data_box','fakturo_client','normal', 'default' );
	add_meta_box( 'fakturo-options-box', __('Client Contacts', FAKTURO_TEXT_DOMAIN ), 'Fakturo_options_box','fakturo_client','normal', 'default' );
}

function Fakturo_seller_box( $post ) {  
	global $post, $client_data, $current_user;
	$user_aseller = $client_data['user_aseller'];
	?>
	<table class="form-table">
	<tbody>
	<tr class="user-display-name-wrap" id="row_user_aseller">
		<td>
		<?php
		if(!current_user_can('fakturo_seller'))	 {
			$allsellers = get_users( array( 'role' => 'fakturo_seller' ) );
			// Array of stdClass objects.
			$select = '<select name="user_aseller" id="user_aseller">';
			if( !isset( $user_aseller ) || $user_aseller == '' ) {
				$select .='<option value="" selected="selected">'. __('Choose a Salesman', FAKTURO_TEXT_DOMAIN  ) . '</option>';
			}
			foreach ( $allsellers as $suser ) {
				$select .='<option value="' . $suser->ID . '" ' . selected($user_aseller, $suser->ID, false) . '>' . esc_html( $suser->display_name ) . '</option>';
			}
			$select .= '</select>';
			echo $select;
		}else{
			//$user = get_user_by( 'ID', get_current_user_id() );
			echo $current_user->display_name;
			echo '<input type="hidden" name="user_aseller" id="user_aseller" value="'. get_current_user_id() .'" class="regular-text ltr">';
		}
		?>
		</td>
	</tr>
	</tbody>
	</table>
	<?php
}


function Fakturo_data_box( $post ) {  
	global $post, $client_data;		
	?>
	<table class="form-table">
	<tbody><tr class="user-email-wrap">
		<th><label for="email"><?php _e("E-mail", FAKTURO_TEXT_DOMAIN ) ?></label></th>
		<td><input type="email" name="email" id="email" value="<?php echo $client_data['email'] ?>" class="regular-text ltr"></td>
	</tr>
	<tr class="user-address-wrap">
		<th><label for="address"><?php _e("Address", FAKTURO_TEXT_DOMAIN ) ?>	</label></th>
		<td><input type="text" name="address" id="address" value="<?php echo $client_data['address'] ?>" class="regular-text"></td>
	</tr>
	<tr class="user-phone-wrap">
		<th><label for="phone"><?php _e("Telephone", FAKTURO_TEXT_DOMAIN ) ?>	</label></th>
		<td><input type="text" name="phone" id="phone" value="<?php echo $client_data['phone'] ?>" class="regular-text"></td>
	</tr>
	<tr class="user-cellular-wrap">
		<th><label for="cellular"><?php _e("Cellular", FAKTURO_TEXT_DOMAIN ) ?>	</label></th>
		<td><input type="text" name="cellular" id="cellular" value="<?php echo $client_data['cellular'] ?>" class="regular-text"></td>
	</tr>
	<tr class="user-facebook-wrap">
		<th><label for="facebook"><?php _e("Facebook URL", FAKTURO_TEXT_DOMAIN ) ?>	</label></th>
		<td><input type="text" name="facebook" id="facebook" value="<?php echo $client_data['facebook'] ?>" class="regular-text"></td>
	</tr>
	</tbody></table>
	<?php
}


function Fakturo_options_box( $post ) {  
	global $post, $client_data;
	wp_nonce_field( 'edit-client', 'fakturo_client_nonce' ); 
	$user_contacts = $client_data['user_contacts'];

	?>
<table class="form-table">
	<tbody>
	<tr class="user-display-name-wrap">
		<td><style>
		#user_contacts {background:#E2E2E2;}
		.sortitem{background:#fff;border:2px solid #ccc;padding-left:20px; display: flex;}
		.sortitem .sorthandle{position:absolute;top:5px;bottom:5px;left:3px;width:8px;display:none;background-image:url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAQAAAAECAYAAACp8Z5+AAAAB3RJTUUH3wIDBycZ/Cj09AAAAAlwSFlzAAALEgAACxIB0t1+/AAAAARnQU1BAACxjwv8YQUAAAAWSURBVHjaY2DABhoaGupBGMRmYiAEAKo2BAFbROu9AAAAAElFTkSuQmCC');}
		.sortitem:hover .sorthandle{display:block;}
			</style>
			<div class="uc_header">
			<div class="uc_column"><?php _e('Description', FAKTURO_TEXT_DOMAIN  ) ?></div>
			<div class="uc_column"><?php _e('Phone', FAKTURO_TEXT_DOMAIN  ) ?></div>
			<div class="uc_column"><?php _e('Email', FAKTURO_TEXT_DOMAIN  ) ?></div>
			<div class="uc_column"><?php _e('Position', FAKTURO_TEXT_DOMAIN  ) ?></div>
			<div class="uc_column"><?php _e('Address', FAKTURO_TEXT_DOMAIN  ) ?></div>
			</div>
			<br />
			<div id="user_contacts" data-callback="jQuery('#msgdrag').html('<?php _e('Update Client to save Contacts order', FAKTURO_TEXT_DOMAIN  ); ?>').fadeIn();"> <!-- callback script to run on successful sort -->
				<?php for ($i = 0; $i <= count(@$user_contacts['description']); $i++) : ?>
					<?php $lastitem = $i==count(@$user_contacts['description']); ?>			
					<div id="uc_ID<?php echo $i; ?>" class="sortitem <?php if(($i % 2) == 0) echo 'bw'; else echo 'lightblue'; ?> <?php if($lastitem) echo 'uc_new_field'; ?> " <?php if($lastitem) echo 'style="display:none;"'; ?> > <!-- sort item -->
						<div class="sorthandle"> </div> <!-- sort handle -->
						<div class="uc_column" id="">
							<input name="uc_description[<?php echo $i; ?>]" type="text" value="<?php echo stripslashes(@$user_contacts['description'][$i]) ?>" class="large-text"/>
						</div>
						<div class="uc_column" id="">
							<input name="uc_phone[<?php echo $i; ?>]" type="text" value="<?php echo stripslashes(@$user_contacts['phone'][$i]) ?>" class="large-text"/>
						</div>
						<div class="uc_column" id="">
							<input name="uc_email[<?php echo $i; ?>]" type="text" value="<?php echo stripslashes(@$user_contacts['email'][$i]) ?>" class="large-text"/>
						</div>
						<div class="uc_column" id="">
							<input name="uc_position[<?php echo $i; ?>]" type="text" value="<?php echo stripslashes(@$user_contacts['position'][$i]) ?>" class="large-text"/>
						</div>
						<div class="uc_column" id="">
							<input name="uc_address[<?php echo $i; ?>]" type="text" value="<?php echo stripslashes(@$user_contacts['address'][$i]) ?>" class="large-text"/>
						</div>
						<div class="" id="uc_actions">
							<label title="<?php _e('Delete this item',  FAKTURO_TEXT_DOMAIN  ); ?>" onclick="delete_user_contact('#uc_ID<?php echo $i; ?>');" class="delete"></label>
						</div>
					</div>
					<?php $a=$i;endfor ?>		
			</div>
			<input id="ucfield_max" value="<?php echo $a; ?>" type="hidden" name="ucfield_max">
			<div id="paging-box">		  
				<a href="JavaScript:void(0);" class="button-primary add" id="addmoreuc" style="font-weight: bold; text-decoration: none;"> <?php _e('Add User Contact', FAKTURO_TEXT_DOMAIN  ); ?>.</a>
				<label id="msgdrag"></label>
			</div>
		</td>
	</tr>
	</tbody>
	</table>
	<?php
}

function fakturo_clients_admin_styles(){
	global $post;
	if($post->post_type != 'fakturo_client') return $post->ID;
	wp_enqueue_style('fakturo-sprite',FAKTURO_URI .'css/sprite.css');	
	add_action('admin_head', 'fakturo_clients_head_style');
}

function fakturo_clients_admin_scripts(){
	global $post;
	if($post->post_type != 'fakturo_client') return $post->ID;
	wp_register_script('jquery-vsort', FAKTURO_URI .'js/jquery.vSort.min.js', array('jquery'));
	wp_enqueue_script('jquery-vsort'); 
	wp_register_script('webcam', FAKTURO_URI .'libraries/webcam/webcam.js', array('jquery'));
	wp_enqueue_script('webcam'); 
	add_action('admin_head', 'fakturo_clients_head_scripts');
}


function fakturo_clients_head_style() {
	global $post;
	if($post->post_type != 'fakturo_client') return $post->ID;
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
		</style><?php

}

function fakturo_ajax_webcam_shot(){
	$aaaaa = 'SSSSSSSS';
	echo $aaaaa;
	return "XXXXXX";
}

function fakturo_clients_head_scripts() {
	global $post, $wp_locale, $locale;
	if($post->post_type != 'fakturo_client') return $post->ID;
	$post->post_password = '';
	$visibility = 'public';
	$visibility_trans = __('Public');

	?>
	<script type="text/javascript" language="javascript">
	jQuery(document).ready(function($){
		$('#publish').val('<?php _e('Save Client', FAKTURO_TEXT_DOMAIN ); ?>');
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
		//-----Click on interest (Allows just one)
//			$(document).on("click", '#interestchecklist input[type=checkbox]', function(event) { 
//				var $current = $(this).prop('checked') ; //true or false
//				$('#interestchecklist input[type=checkbox]').prop('checked', false);
//				$(this).prop('checked', $current );
//				//if( $current ){ }
//			});

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
		var webcamOn = false;
		//var uploadOn = false;

		takeSnapshot = function(){
			$('#take_snapshot').hide();
			$('#reset_snapshot').show(); //inline
			webcam.freeze();
		}

		resetSnapshot = function(){
			$('#reset_snapshot').hide();
			$('#take_snapshot').show(); //inline
			if(webcam.loaded) webcam.reset();
		}

		showSnapshot = function(){
			$('#snapshot_container_buttons').hide();
			$('#set-post-thumbnail.thickbox').hide();
			$('#snapshot_container').show();
			webcam.set_api_url(ajaxurl);
			webcam.set_swf_url('<?php echo FAKTURO_URI .'libraries/webcam/webcam.swf' ?>');
			webcam.set_quality(90); // JPEG quality (1 - 100)
			webcam.set_shutter_sound(false); // play shutter click sound
			webcam.set_hook('onError', 'cbWebcamError');
			webcam.set_hook('onLoad', 'cbWebcamLoad');
			webcam.set_hook('onComplete', 'cbWebcamActionComplete');
			resetSnapshot();
			$('#cam_image_container').html( webcam.get_html(230, 150) );
			webcamOn = true;
		}

		hideSnapshot = function(){
			$('#snapshot_container').hide();
			webcamOn = false;
			$('#snapshot_container_buttons').show();
			$('#set-post-thumbnail.thickbox').show();
		}

		cbWebcamError = function(msg) {
			msgBox('Error: '+msg);
			resetSnapshot();
		}

		cbWebcamLoad = function() {
		}

		cbWebcamActionComplete = function(msg) {
			// extract URL out of PHP output
			if (msg.match(/error\:(.*)/)) {
				var error = RegExp.$1;
				$('#cam_image_buttons').show();
				if($('#action_buttons')) $('#action_buttons').show();
				$('#upload_results').innerHTML = '';
				resetSnapshot();
				msgBox(msg);
			} else {
				$('#image').value = msg;
				//$('<?php //echo $form;?>').submit();
			}
		}

		uploadAndSubmit = function() {
			if(webcamOn){
				// Take snapshot and upload to server
				$('#cam_image_buttons').hide();
				if($('#action_buttons')) $('#action_buttons').hide();
				$('#upload_results').innerHTML = '<?php echo __("Uploading", FAKTURO_TEXT_DOMAIN );?>';
				webcam.upload();
			} else {
				//$('<?php //echo $form;?>').submit();
			}
		}
		
		
	});		// jQuery
	function delete_user_contact(row_id){
		jQuery(row_id).fadeOut(); 
		jQuery(row_id).remove();
		jQuery('#msgdrag').html('<?php _e('Update Client to save changes.', FAKTURO_TEXT_DOMAIN ); ?>').fadeIn();
	}


	
	</script>
	<?php
}



// to save the webcam photo as _thumbnail_id  ??
// media_sideload_image( $file, $post_id, $desc = null, $return = 'html' ) 

/**
 * Display post thumbnail meta box.
 *
 * @since 2.9.0
 */
function Fakturo_post_thumbnail_meta_box( $post ) {
	$thumbnail_id = get_post_meta( $post->ID, '_thumbnail_id', true );
	echo Faktura_get_webcam_link( $thumbnail_id, $post->ID );
	echo _wp_post_thumbnail_html( $thumbnail_id, $post->ID );
}

function Faktura_get_webcam_link($thumbnail_id = null, $post = null){
	?>
	<div id="snapshot_container_wrapper">
	<?php if(! $thumbnail_id ) { ?>
		<div id="snapshot_container_buttons">
			<?php echo '<a href="javascript:showSnapshot()" class="nobutton">"' . __( 'Take a snapshot', FAKTURO_TEXT_DOMAIN ) . '"</a>'; ?>
		</div>
		<div id="snapshot_container" class="form_field" style="display:none">
			<div id="cam_image_container"></div>
			<div id="upload_results"></div>
			<input type="hidden" name="image" id="image" value="" >
			<div id="cam_image_buttons">
				<input type="button" value="<?php echo __("Configure", FAKTURO_TEXT_DOMAIN);?>" onClick="webcam.configure()">
				&nbsp;&nbsp;
				<input type="button" id="take_snapshot" value="<?php echo __("Take a snapshot", FAKTURO_TEXT_DOMAIN );?>" onClick="takeSnapshot()">
				&nbsp;&nbsp;
				<input type="button" id="reset_snapshot" style="display:none" value="<?php echo __("Reset", FAKTURO_TEXT_DOMAIN );?>" onClick="resetSnapshot()">
				&nbsp;&nbsp;
				<input type="button" id="cancel" value="<?php echo __("Cancel", FAKTURO_TEXT_DOMAIN );?>" onClick="hideSnapshot()">
				<div id="delete_image_info">
					<?php echo __("Must Save to complete image Saving", FAKTURO_TEXT_DOMAIN );?>
				</div>
			</div>
		</div>
	<?php } ?>
	</div>
	<?php
}

/** Taken by et from wordpress includes metaboxes to change something.... see if is necessary
 * 
 * Output HTML for the post thumbnail meta-box.
 *
 * @since 2.9.0
 *
 * @global int   $content_width
 * @global array $_wp_additional_image_sizes
 *
 * @param int $thumbnail_id ID of the attachment used for thumbnail
 * @param mixed $post The post ID or object associated with the thumbnail, defaults to global $post.
 * @return string html
 */
function Fakturo__wp_post_thumbnail_html( $thumbnail_id = null, $post = null ) {
	global $content_width, $_wp_additional_image_sizes;

	$post               = get_post( $post );
	$post_type_object   = get_post_type_object( $post->post_type );
	$set_thumbnail_link = '<p class="hide-if-no-js"><a title="%s" href="%s" id="set-post-thumbnail" class="thickbox">%s</a></p>';
	$upload_iframe_src  = get_upload_iframe_src( 'image', $post->ID );

	$content = sprintf( $set_thumbnail_link,
		esc_attr( $post_type_object->labels->set_featured_image ),
		esc_url( $upload_iframe_src ),
		esc_html( $post_type_object->labels->set_featured_image )
	);

	if ( $thumbnail_id && get_post( $thumbnail_id ) ) {
		$size = isset( $_wp_additional_image_sizes['post-thumbnail'] ) ? 'post-thumbnail' : array( 266, 266 );

		/**
		 * Filter the size used to display the post thumbnail image in the 'Featured Image' meta box.
		 *
		 * Note: When a theme adds 'post-thumbnail' support, a special 'post-thumbnail'
		 * image size is registered, which differs from the 'thumbnail' image size
		 * managed via the Settings > Media screen. See the `$size` parameter description
		 * for more information on default values.
		 *
		 * @since 4.4.0
		 *
		 * @param string|array $size         Post thumbnail image size to display in the meta box. Accepts any valid
		 *                                   image size, or an array of width and height values in pixels (in that order).
		 *                                   If the 'post-thumbnail' size is set, default is 'post-thumbnail'. Otherwise,
		 *                                   default is an array with 266 as both the height and width values.
		 * @param int          $thumbnail_id Post thumbnail attachment ID.
		 * @param WP_Post      $post         The post object associated with the thumbnail.
		 */
		$size = apply_filters( 'admin_post_thumbnail_size', $size, $thumbnail_id, $post );

		$thumbnail_html = wp_get_attachment_image( $thumbnail_id, $size );

		if ( !empty( $thumbnail_html ) ) {
			$ajax_nonce = wp_create_nonce( 'set_post_thumbnail-' . $post->ID );
			$content = sprintf( $set_thumbnail_link,
				esc_attr( $post_type_object->labels->set_featured_image ),
				esc_url( $upload_iframe_src ),
				$thumbnail_html
			);
			$content .= '<p class="hide-if-no-js"><a href="#" id="remove-post-thumbnail" onclick="WPRemoveThumbnail(\'' . $ajax_nonce . '\');return false;">' . esc_html( $post_type_object->labels->remove_featured_image ) . '</a></p>';
		}
	}

	/**
	 * Filter the admin post thumbnail HTML markup to return.
	 *
	 * @since 2.9.0
	 *
	 * @param string $content Admin post thumbnail HTML markup.
	 * @param int    $post_id Post ID.
	 */
	return apply_filters( 'admin_post_thumbnail_html', $content, $post->ID );
}
?>
