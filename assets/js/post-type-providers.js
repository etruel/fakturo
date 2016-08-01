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
	
	
	
  
});