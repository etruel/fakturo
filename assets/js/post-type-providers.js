jQuery(document).ready(function() {
	jQuery("#selected_country").select2();
	
	jQuery("#selected_country").on("change", function (e) {
		
		var data = {
			action: 'get_provider_states',
			country_id: this.value
		}
		jQuery("#td_select_state").html(providers_object.loading_states_text);
		
		jQuery.post(providers_object.ajax_url, data, function( data ) {
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
			var newHTML = '<div id="uc_ID'+oldval+'" class="sortitem"> <div class="uc_column" id=""><input name="uc_description[]" type="text" value="" class="large-text"/></div><div class="uc_column" id=""><input name="uc_phone[]" type="text" value="" class="large-text"/></div><div class="uc_column" id=""><input name="uc_email[]" type="text" value="" class="large-text"/></div><div class="uc_column" id=""><input name="uc_position[]" type="text" value="" class="large-text"/></div><div class="uc_column" id=""><input name="uc_address[]" type="text" value="" class="large-text"/></div><div class="" id="uc_actions"><label title="'+providers_object.privider_delete_this_item+'" data-id="'+oldval+'" class="delete"> X </label></div></div>';
			
			jQuery('#user_contacts').append(newHTML);
			jQuery('#uc_actions label').click(function() {
				delete_user_contact('#uc_ID'+jQuery(this).attr('data-id'));
			});
			e.preventDefault();
		});
	jQuery('#uc_actions label').click(function() {
		delete_user_contact('#uc_ID'+jQuery(this).attr('data-id'));
	});
	
  
});

function delete_user_contact(row_id){
	jQuery(row_id).fadeOut(); 
	jQuery(row_id).remove();
	jQuery('#msgdrag').html(providers_object.update_provider_contacts).fadeIn();
}
