jQuery(document).ready(function() {
	jQuery("#client_id").select2();
	jQuery("#client_data_tax_condition").select2();
	jQuery("#client_data_payment_type").select2();
	jQuery("#invoice_type").select2();
	jQuery("#invoice_currency").select2();
	
	
	jQuery("#client_id").change(function() {
		if (jQuery("#client_id").val() < 1) {
			jQuery(".client_data").fadeOut();
			
		} else {
			jQuery(".client_data").fadeIn();
			jQuery("#client_data_id").html('Loading...');
			jQuery("#client_name").html('Loading...');
			jQuery("#client_address").html('Loading...');
			jQuery("#client_city").html('Loading...');
			jQuery("#client_state").html('Loading...');
			jQuery("#client_country").html('Loading...');
			jQuery("#client_taxpayer").html('Loading...');
			jQuery("#client_tax_condition").fadeOut();
			jQuery("#client_payment_type").fadeOut();
			jQuery("#client_price_scale").html('Loading...');
			jQuery("#client_credit_limit").html('Loading...');
			
			
			var data = {
				action: 'get_client_data',
				client_id: this.value
			}
	
			jQuery.post(sales_object.ajax_url, data, function( data ) {
				var data_client = jQuery.parseJSON(data);
				console.log(data_client);
				jQuery("#client_data_id").html(data_client.ID);
				jQuery("#client_name").html(data_client.post_title+'<input type="hidden" name="client_data[name]" value="'+data_client.post_title+'" id="client_data_name"/>');
				jQuery("#client_address").html(data_client.address+'<input type="hidden" name="client_data[address]" value="'+data_client.address+'" id="client_data_address"/>');
				jQuery("#client_city").html(data_client.city+'<input type="hidden" name="client_data[city]" value="'+data_client.city+'" id="client_data_city"/>');
				jQuery("#client_state").html(data_client.selected_state_name+'<input type="hidden" name="client_data[state][id]" value="'+data_client.selected_state+'" id="client_data_state_id"/><input type="hidden" name="client_data[state][name]" value="'+data_client.selected_state_name+'" id="client_data_state_name"/>');
				jQuery("#client_country").html(data_client.selected_country_name+'<input type="hidden" name="client_data[country][id]" value="'+data_client.selected_country+'" id="client_data_country_id"/><input type="hidden" name="client_data[country][name]" value="'+data_client.selected_country_name+'" id="client_data_country_name"/>');
				jQuery("#client_taxpayer").html(data_client.taxpayer+'<input type="hidden" name="client_data[taxpayer]" value="'+data_client.taxpayer+'" id="client_data_taxpayer"/>');
				
				jQuery("#client_tax_condition").fadeIn();
				jQuery("#client_data_tax_condition").val(data_client.selected_tax_condition);
				jQuery("#client_data_tax_condition").select2();
				
				jQuery("#client_payment_type").fadeIn();
				jQuery("#client_data_payment_type").val(data_client.selected_payment_type);
				jQuery("#client_data_payment_type").select2();
				
				jQuery("#client_price_scale").html(data_client.selected_price_scale_name+'<input type="hidden" name="client_data[price_scale][id]" value="'+data_client.selected_price_scale+'" id="client_data_price_scale_id"/><input type="hidden" name="client_data[price_scale][name]" value="'+data_client.selected_price_scale_name+'" id="client_data_price_scale_name"/>');
				jQuery("#client_credit_limit").html(data_client.credit_limit+'<input type="hidden" name="client_data[credit_limit]" value="'+data_client.credit_limit+'" id="client_data_credit_limit"/>');
				
			

			});
		}
		
		
		
	});
	
  
});



