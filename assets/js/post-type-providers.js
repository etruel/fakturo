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
			oldval = jQuery('#ucfield_max').val();
			jQuery('#ucfield_max').val( parseInt(jQuery('#ucfield_max').val(),10) + 1 );
			newval = jQuery('#ucfield_max').val();
			uc_new= jQuery('.uc_new_field').clone();
			jQuery('div.uc_new_field').removeClass('uc_new_field');
			jQuery('div#uc_ID'+oldval).fadeIn();
			jQuery('input[name="uc_description['+oldval+']"]').focus();
			uc_new.attr('id','uc_ID'+newval);
			jQuery('input', uc_new).eq(0).attr('name','uc_description['+ newval +']');
			jQuery('input', uc_new).eq(1).attr('name','uc_phone['+ newval +']');
			jQuery('input', uc_new).eq(2).attr('name','uc_email['+ newval +']');
			jQuery('input', uc_new).eq(3).attr('name','uc_position['+ newval +']');
			jQuery('input', uc_new).eq(4).attr('name','uc_address['+ newval +']');
			jQuery('.delete', uc_new).eq(0).attr('onclick', "delete_user_contact('#uc_ID"+ newval +"');");
			jQuery('#user_contacts').append(uc_new);
			jQuery('#user_contacts').vSort();
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
