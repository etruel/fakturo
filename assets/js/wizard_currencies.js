var total_selected_currencies = 0;
var current_request = 0;
var ajax_urls = new Array();
var array_selected_currencies = new Array();
jQuery(document).ready(function() {
	
	for (var c = 0; c <= 117; c++) {
		ajax_urls.push(backend_object.ajax_url+'?action=fktr_load_currencies&currency_id='+c+'&nonce='+backend_object.ajax_nonce);
	}
	
	jQuery('input[type="submit"]').click(function(e) {
		if (jQuery('input[name="load_currencies"]:checked').val() == 'yes') {
			jQuery('#content_step').fadeOut();
			jQuery('.buttons_container').first().fadeOut();
			jQuery('.buttons_container').html(backend_object.loading_states_text+'<img src="'+backend_object.loading_image+'"/> <div id="porcent_loading_fe" style="display: inline;"> 0%</div>');
			execute_load_currencies();
			e.preventDefault();
		} else if (jQuery('input[name="load_currencies"]:checked').val() == 'yes_only_a_currency') {
			jQuery('#content_step').fadeOut();
			jQuery('.buttons_container').first().fadeOut();
			jQuery('.buttons_container').html(backend_object.loading_states_text+'<img src="'+backend_object.loading_image+'"/> <div id="porcent_loading_fe" style="display: inline;"> 0%</div>');
			total_selected_currencies = jQuery('.selected_some_currencies').length;
			jQuery('.selected_some_currencies').map(function(e) {
				array_selected_currencies.push(jQuery(this).val());
			});
			execute_selected_currencies();
		    e.preventDefault();
		}
	})

	jQuery('#selected_currency').select2();
	jQuery('input[name="load_currencies"]').change(function(e) {
		if (jQuery('input[name="load_currencies"]:checked').val() == 'yes_only_a_currency') {
			jQuery('#container_select_currency').fadeIn();
		} else {
			jQuery('#container_select_currency').fadeOut();
		}
	});

	jQuery('#btn_add_select_currency').click(function(e) {
		if (!jQuery('#tr_selected_'+jQuery('#selected_currency').val()).length) {
			jQuery('#selected_currencies').append('<tr id="tr_selected_'+jQuery('#selected_currency').val()+'"><td>'+jQuery("#selected_currency option[value='"+jQuery('#selected_currency').val()+"']").text()+' <input type="hidden" name="selected_some_currencies[]" class="selected_some_currencies" value="'+jQuery('#selected_currency').val()+'"/></td><td style="width: 20px;"><label title="" data-id="'+jQuery('#selected_currency').val()+'" class="delete"></label></td></tr>');
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

function execute_selected_currencies() {
	if(current_request < total_selected_currencies) {
		var index_currency = parseInt(array_selected_currencies[current_request]);
		upload_bar(total_selected_currencies);
		jQuery.ajax({
	        url: ajax_urls[index_currency],
	        type: 'get',
	        contentType: false,
	        processData: false,
	                    
	        success: function (response) {
	        	
	        	current_request++;
	            execute_selected_currencies();
	        	
	        },  
	        error: function (response) {
	            //current_request++;
	           	execute_selected_currencies();
	        }

	    });

	} else {
		jQuery('form').submit();
	}

}

function execute_load_currencies() {
	if (current_request < ajax_urls.length) {
		upload_bar(ajax_urls.length);
		jQuery.ajax({
	        url: ajax_urls[current_request],
	        type: 'get',
	        contentType: false,
	        processData: false,
	                    
	        success: function (response) {
	        	if (response == 'last_currency') {
	        		jQuery('form').submit();
	        	} else {
	        		current_request++;
	            	execute_load_currencies();
	        	}
	        },  
	        error: function (response) {
	            //console.log('error');
	            //current_request++;
	           	execute_load_currencies();
	        }

	    });
	}  else {
		jQuery('form').submit();
	}
}
function upload_bar(length_requests) {
	var current_width = jQuery('.stepwizard-row-bar').width() / jQuery('.stepwizard-row-bar').parent().width() * 100;
	jQuery('.stepwizard-row-bar').css("width", (current_width+(backend_object.porcent_per_steep/length_requests))+'%');
	jQuery('#porcent_loading_fe').html(' '+Math.round((100/length_requests)*current_request)+' %');
}