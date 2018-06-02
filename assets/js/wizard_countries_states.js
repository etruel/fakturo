var current_request = 0;
var ajax_urls = new Array();
var ajax_urls_selected = new Array();
var total_selected_countries = 0;
var array_selected_countries = new Array();
var current_index_state = 0;
var current_count_state = 9999;
jQuery(document).ready(function() {
	
	for (var c = 1; c <= 246; c++) {
		ajax_urls.push(backend_object.ajax_url+'?action=fktr_load_countries_states&nonce='+backend_object.ajax_nonce);
		ajax_urls_selected.push(backend_object.ajax_url+'?action=fktr_load_selected_countries_states&country_id='+c+'&state_index=0&nonce='+backend_object.ajax_nonce);
	}
	
	jQuery('input[type="submit"]').click(function(e) {
		if (jQuery('input[name="load_contries_states"]:checked').val() == 'yes') {
			jQuery('#content_step').fadeOut();
			jQuery('.buttons_container').first().remove();
			jQuery('input[type="submit"]').fadeOut();
			jQuery('.buttons_container').prepend(backend_object.loading_states_text+'<img src="'+backend_object.loading_image+'"/> <div id="porcent_loading_fe" style="display: inline;"> 0%</div>');
			execute_load_countries();
			e.preventDefault();
		} else if (jQuery('input[name="load_contries_states"]:checked').val() == 'yes_only_a_country') {
			jQuery('#content_step').fadeOut();
			jQuery('.buttons_container').first().remove();
			jQuery('input[type="submit"]').fadeOut();
			jQuery('.buttons_container').prepend(backend_object.loading_states_text+'<img src="'+backend_object.loading_image+'"/> <div id="porcent_loading_fe" style="display: inline;"> 0%</div>');
			total_selected_countries = jQuery('.selected_some_countries').length;
			jQuery('.selected_some_countries').map(function(e) {
				array_selected_countries.push(jQuery(this).val());
			});
			execute_selected_countries_new();
		    e.preventDefault();
		}
	})
	jQuery('#selected_country').select2();
	jQuery('input[name="load_contries_states"]').change(function(e) {
		if (jQuery('input[name="load_contries_states"]:checked').val() == 'yes_only_a_country') {
			jQuery('#container_select_countries').fadeIn();
		} else {
			jQuery('#container_select_countries').fadeOut();
		}
	});

	jQuery('#btn_add_select_country').click(function(e) {
		if (!jQuery('#tr_selected_'+jQuery('#selected_country').val()).length) {
			jQuery('#selected_countries').append('<tr id="tr_selected_'+jQuery('#selected_country').val()+'"><td>'+jQuery("#selected_country option[value='"+jQuery('#selected_country').val()+"']").text()+' <input type="hidden" name="selected_some_countries[]" class="selected_some_countries" value="'+jQuery('#selected_country').val()+'"/></td><td style="width: 20px;"><label title="" data-id="'+jQuery('#selected_country').val()+'" class="delete"></label></td></tr>');
			events_delete();
		}
	});

	events_delete();
	
});

function events_delete() {
	jQuery('.delete').click(function(e) {
		if (jQuery('#tr_selected_'+jQuery(this).data('id')).length) {
			jQuery('#tr_selected_'+jQuery(this).data('id')).remove();
		}
	});
}
var on_ejecution_states = false;
function execute_states_country() {
	if (current_index_state < current_count_state) {
		upload_bar(total_selected_countries, current_request+((1/current_count_state)*current_index_state));
		var index_country = parseInt(array_selected_countries[current_request])-1;

		var current_ajax_url_selected = ajax_urls_selected[index_country].replace('state_index=0', 'state_index='+current_index_state);
		jQuery.ajax({
	        url: current_ajax_url_selected,
	        type: 'get',
	        contentType: false,
	        processData: false,
	                    
	        success: function (response) {
	        	current_index_state++;
	        	current_count_state = parseInt(response);
	        	execute_states_country();
	        	
	        },  
	        error: function (response) {
	           	execute_states_country();
	        }

	    });

	} else {
		on_ejecution_states = false;
		current_request++;
	}
}
function execute_selected_countries() {
	if(current_request < total_selected_countries) {
		if (!on_ejecution_states) {
			current_count_state = 9999;
			on_ejecution_states = true;
			current_index_state = 0;
			execute_states_country();
			
		}
		setTimeout(function(){ execute_selected_countries(); }, 100);
		return false;
	}

	jQuery('form').submit();
	
	

}

function execute_selected_countries_new() {
	
	current_request = 1;
	var data = {
		countries : array_selected_countries
	};
	
	jQuery.post(ajax_urls[current_request], data, function(response) {
		jQuery('form').submit();
	})
	.fail(function(jquery_xhr) {
		jQuery('form').submit();
	});

	
}

function execute_load_countries() {
	
	if (current_request < ajax_urls.length - 1) {
		upload_bar(ajax_urls.length, current_request);
		var data = {
			countries : new Array()
		};
		for (var ct = 0; ct <= 49; ct++) {
			data.countries[ct] =  current_request + ct;
		}
		
		jQuery.post(ajax_urls[current_request], data, function(response) {

			if (response == 'last_country') {
	        	jQuery('form').submit();
        	} else {
        		current_request = current_request + 49;
            	execute_load_countries();
        	}
		})
		.fail(function(jquery_xhr) {
			execute_load_countries();
		});

	}  else {
		jQuery('form').submit();
	}
}
function upload_bar(length_requests, curr_request) {
	var new_width = (backend_object.porcent_per_steep/length_requests)*curr_request;
	jQuery('.stepwizard-row-bar').css("width", new_width+"%");
	jQuery('#porcent_loading_fe').html(' '+Math.round((100/length_requests)*curr_request)+' %');
	
}
