var current_request = 0;
var ajax_urls = new Array();

jQuery(document).ready(function() {
	
	for (var c = 1; c <= 117; c++) {
		ajax_urls.push(backend_object.ajax_url+'?action=fktr_load_countries_states&country_id='+c+'&nonce='+backend_object.ajax_nonce);
	}
	
	jQuery('input[type="submit"]').click(function(e) {
		if (jQuery('input[name="load_contries_states"]:checked').val() == 'yes') {
			jQuery('#content_step').fadeOut();
			jQuery('#buttons_container').html(backend_object.loading_states_text+'<img src="'+backend_object.loading_image+'"/> <div id="porcent_loading_fe" style="display: inline;"> 0%</div>');
			execute_load_countries();
			e.preventDefault();
		} else if (jQuery('input[name="load_contries_states"]:checked').val() == 'yes_only_a_country') {
			jQuery('#content_step').fadeOut();
			jQuery('#buttons_container').html(backend_object.loading_states_text+'<img src="'+backend_object.loading_image+'"/>');
			current_request = parseInt(jQuery('#selected_country').val())-1;
			jQuery.ajax({
		        url: ajax_urls[current_request],
		        type: 'get',
		        contentType: false,
		        processData: false,
		                    
		        success: function (response) {
		            jQuery('form').submit();
		        },  
		        error: function (response) {
		            console.log('error');
		        }

		    });
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
});


function execute_load_countries() {
	if (current_request < ajax_urls.length) {
		upload_bar();
		jQuery.ajax({
	        url: ajax_urls[current_request],
	        type: 'get',
	        contentType: false,
	        processData: false,
	                    
	        success: function (response) {
	        	if (response == 'last_country') {
	        		jQuery('form').submit();
	        	} else {
	        		current_request++;
	            	execute_load_countries();
	        	}
	        },  
	        error: function (response) {
	            console.log('error');
	            current_request++;
	           	execute_load_countries();
	        }

	    });
	}  else {
		jQuery('form').submit();
	}
}
function upload_bar() {
	var new_width = (backend_object.porcent_per_steep/ajax_urls.length)*current_request;
	jQuery('.stepwizard-row-bar').css("width", new_width+"%");
	jQuery('#porcent_loading_fe').html(' '+Math.round((100/ajax_urls.length)*current_request)+' %');
	
}