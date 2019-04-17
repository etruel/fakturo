jQuery(document).ready(function($) {

	// This code prevents the URL from being filled with the wp-post-new-reload value
	$('#publish').click(function(e) {
		if ( $( '#original_post_status' ).val() === 'auto-draft' && window.history.replaceState ) {
			var location;
			location = window.location.href;
			if ((location.split('wp-post-new-reload').length - 1) > 1 ) {
				location = location.replace('?wp-post-new-reload=true', '');
				location = location.replace('&wp-post-new-reload=true', '');
				window.history.replaceState( null, null, location );
			}
		}
	});

	jQuery("#selected_country").select2();
	jQuery("#selected_payment_type").select2();
	jQuery("#selected_tax_condition").select2();
	
	jQuery("#selected_price_scale").select2();
	jQuery("#selected_currency").select2();
	
	jQuery('#user_contacts').vSort();
	jQuery("#selected_country").on("change", function (e) {
		
		var data = {
			action: 'get_client_states',
			country_id: this.value
		}
		jQuery("#td_select_state").html(client_object.loading_states_text);
		
		jQuery.post(client_object.ajax_url, data, function( data ) {
			jQuery("#td_select_state").html(data);
			jQuery("#selected_state").select2();
		});
		e.preventDefault();
	});
	jQuery("#selected_state").select2();
	jQuery("#selected_bank_entity").select2();
	
	jQuery('#addmoreuc').click(function(e) {
		
			
			jQuery('#ucfield_max').val( parseInt(jQuery('#ucfield_max').val(),10) + 1 );
			oldval = jQuery('#ucfield_max').val();
			var newHTML = '<div id="uc_ID'+oldval+'" class="sortitem"><div class="sorthandle"> </div> <div class="uc_column" id=""><input name="uc_description[]" type="text" value="" class="large-text"/></div><div class="uc_column" id=""><input name="uc_phone[]" type="text" value="" class="large-text"/></div><div class="uc_column" id=""><input name="uc_email[]" type="text" value="" class="large-text"/></div><div class="uc_column" id=""><input name="uc_position[]" type="text" value="" class="large-text"/></div><div class="uc_column" id=""><input name="uc_address[]" type="text" value="" class="large-text"/></div><div class="" id="uc_actions"><label title="'+client_object.privider_delete_this_item+'" data-id="'+oldval+'" class="delete"></label></div></div>';
			
			jQuery('#user_contacts').append(newHTML);
			jQuery('#uc_actions label').click(function() {
				delete_user_contact('#uc_ID'+jQuery(this).attr('data-id'));
				jQuery('#user_contacts').vSort();
			});
			jQuery('#user_contacts').vSort();
			e.preventDefault();
		});
	jQuery('#uc_actions label').click(function() {
		delete_user_contact('#uc_ID'+jQuery(this).attr('data-id'));
		jQuery('#user_contacts').vSort();
	});
	
  
});

function delete_user_contact(row_id){
	jQuery(row_id).fadeOut(); 
	jQuery(row_id).remove();
	jQuery('#msgdrag').html(client_object.update_client_contacts).fadeIn();
}
