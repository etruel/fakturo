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

function fakturo_client_select_data($taxonomies, $name, $client_data) {
	$data = get_terms($taxonomies, 'hide_empty=0');
    $selected = "";
    echo '<select name="' . $name . '"><option></option>';
    foreach ($data as $value) {
    	if ($client_data[$name] == $value->name) {
    		$selected = " selected";
    	} else {
    		$selected = "";
    	}
      	echo "<option$selected>$value->name</option>";
    }
    echo '</select>';
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
	<tr class="user-cellular-wrap">
		<th><label for="cellular"><?php _e("Cellular", FAKTURO_TEXT_DOMAIN ) ?>	</label></th>
		<td><input type="text" name="cellular" id="cellular" value="<?php echo $client_data['cellular'] ?>" class="regular-text"></td>
	</tr>
	<tr class="user-facebook-wrap">
		<th><label for="facebook"><?php _e("Facebook URL", FAKTURO_TEXT_DOMAIN ) ?>	</label></th>
		<td><input type="text" name="facebook" id="facebook" value="<?php echo $client_data['facebook'] ?>" class="regular-text"></td>
	</tr>
	<tr class="user-facebook-wrap">
		<th><label for="facebook"><?php _e("Taxpayer ID", FAKTURO_TEXT_DOMAIN ) ?>	</label></th>
		<td>
			<input id="cuit" type="text" name="taxpayer" value="<?php echo $client_data['taxpayer'] ?>" class="regular-text">
			<span id="cuit_validation"></span>
			<div style="font-size:0.85em;" id="cuit_validation_note"><?php _e("Cuit number's validation only. Check www.afip.gov.ar", FAKTURO_TEXT_DOMAIN ) ?></div>
		</td>
	</tr>
	<tr class="user-facebook-wrap">
		<th><label for="facebook"><?php _e("States", FAKTURO_TEXT_DOMAIN ) ?>	</label></th>
		<td><?php fakturo_client_select_data('fakturo_states', 'states', $client_data); ?></td>
	</tr>
	<tr class="user-facebook-wrap">
		<th><label for="facebook"><?php _e("City", FAKTURO_TEXT_DOMAIN ) ?>	</label></th>
		<td><input type="text" name="city" value="<?php echo $client_data['city'] ?>" class="regular-text"></td>
	</tr>
	<tr class="user-facebook-wrap">
		<th><label for="facebook"><?php _e("Payment Type", FAKTURO_TEXT_DOMAIN ) ?>	</label></th>
		<td><?php fakturo_client_select_data('fakturo_payment_types', 'payment_type', $client_data); ?></td>
	</tr>
	<tr class="user-facebook-wrap">
		<th><label for="facebook"><?php _e("Price Scale", FAKTURO_TEXT_DOMAIN ) ?>	</label></th>
		<td><?php fakturo_client_select_data('fakturo_price_scales', 'price_scale', $client_data); ?></td>
	</tr>
	<tr class="user-facebook-wrap">
		<th><label for="facebook"><?php _e("Bank Entity", FAKTURO_TEXT_DOMAIN ) ?>	</label></th>
		<td><?php fakturo_client_select_data('fakturo_bank_entities', 'bank_entity', $client_data); ?></td>
	</tr>
	<tr class="user-facebook-wrap">
		<th><label for="facebook"><?php _e("Bank Account", FAKTURO_TEXT_DOMAIN ) ?>	</label></th>
		<td><input type="text" name="bank_account" value="<?php echo $client_data['bank_account'] ?>" class="regular-text"></td>
	</tr>
	<tr class="user-facebook-wrap">
		<th><label for="facebook"><?php _e("Tax Condition", FAKTURO_TEXT_DOMAIN ) ?>	</label></th>
		<td><?php fakturo_client_select_data('fakturo_tax_condition', 'tax_condition', $client_data); ?></td>
	</tr>
	<tr class="user-facebook-wrap">
		<th><label for="facebook"><?php _e("Postcode", FAKTURO_TEXT_DOMAIN ) ?>	</label></th>
		<td><input type="text" name="postcode" value="<?php echo $client_data['postcode'] ?>" class="regular-text"></td>
	</tr>
	<tr class="user-facebook-wrap">
		<th><label for="facebook"><?php _e("Phone", FAKTURO_TEXT_DOMAIN ) ?>	</label></th>
		<td><input type="text" name="phone" value="<?php echo $client_data['phone'] ?>" class="regular-text"></td>
	</tr>
	<tr class="user-facebook-wrap">
		<th><label for="facebook"><?php _e("Cell phone", FAKTURO_TEXT_DOMAIN ) ?>	</label></th>
		<td><input type="text" name="cell_phone" value="<?php echo $client_data['cell_phone'] ?>" class="regular-text"></td>
	</tr>
	<tr class="user-facebook-wrap">
		<th><label for="facebook"><?php _e("Web", FAKTURO_TEXT_DOMAIN ) ?>	</label></th>
		<td><input type="text" name="web" value="<?php echo $client_data['web'] ?>" class="regular-text"></td>
	</tr>
	<tr class="user-facebook-wrap">
		<th><label for="facebook"><?php _e("Credit Limit", FAKTURO_TEXT_DOMAIN ) ?>	</label></th>
		<td><input type="text" name="credit_limit" value="<?php echo $client_data['credit_limit'] ?>" class="regular-text"></td>
	</tr>
	<tr class="user-facebook-wrap">
		<th><label for="facebook"><?php _e("Credit Limit Interval", FAKTURO_TEXT_DOMAIN ) ?>	</label></th>
		<td><input type="text" name="credit_interval" value="<?php echo $client_data['credit_interval'] ?>" class="regular-text"></td>
	</tr>
	<tr class="user-facebook-wrap">
		<th><label for="facebook"><?php _e("Credit Limit Currency", FAKTURO_TEXT_DOMAIN ) ?>	</label></th>
		<td><input type="text" name="credit_currency" value="<?php echo $client_data['credit_currency'] ?>" class="regular-text"></td>
	</tr>
	<tr class="user-facebook-wrap">
		<th><label for="facebook"><?php _e("Active", FAKTURO_TEXT_DOMAIN ) ?>	</label></th>
		<td><input type="checkbox" name="active" value="1" <?php if ($client_data['active']) { echo 'checked="checked"'; } ?>></td>
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
	wp_register_script('webcam', FAKTURO_URI .'libraries/webcamjs-master/webcam.min.js', array('jquery'));
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
.cuit_ok {color: #2FB42F;text-shadow: 1px 1px 1px #eee;}
.cuit_err {color: #FF3232;font-weight: 700;text-shadow: 1px 1px 1px #eee;}
.sb_js_errors {float: right;color: #FF3232;}
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
		

		showSnapshot = function() {
			$('#snapshot_btn').css('display', 'none');
			$('#my_camera').css('display', 'block');
			$('#take_snapshot').css('display', 'block');
			
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
			// take snapshot and get image data
			Webcam.snap( function(data_uri) {
				// display results in page				
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
			$('#snapshot_btn').css('display', 'block');
			$('#snapshot_reset').css('display', 'none');
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
		$('#cuit').keyup(function(){
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
	echo '<div class="featured-image-client">';
	echo _wp_post_thumbnail_html( $thumbnail_id, $post->ID );
	echo "</div>";
}

function Faktura_get_webcam_link($thumbnail_id = null, $post = null){
	?>
	<div id="snapshot_container_wrapper">
	<div id="snapshot_container_buttons">
		<?php echo '<a id="snapshot_btn" href="javascript:showSnapshot()" class="nobutton">"' . __( 'Take a snapshot', FAKTURO_TEXT_DOMAIN ) . '"</a>'; ?>
		<div id="my_camera" style="display:none;">				
		</div>
		<img src="" id="snap_image" style="display:none;">
		<input type="hidden" name="webcam_image">
		<a href="javascript:take_snapshot()" class="nobutton" id="take_snapshot" style="display:none;"><?php _e( 'Take a snapshot') ?></a>
		<a href="javascript:reset_webcam()" class="nobutton" id="snapshot_reset" style="display:none;"><?php _e( 'Reset') ?></a>
	</div>
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
