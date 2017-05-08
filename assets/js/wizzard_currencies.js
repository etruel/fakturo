var current_request = 0;
var ajax_urls = new Array();

jQuery(document).ready(function() {
	
	for (var c = 0; c <= 246; c++) {
		ajax_urls.push(backend_object.ajax_url+'?action=fktr_load_currencies&currency_id='+c+'&nonce='+backend_object.ajax_nonce);
	}
	
	jQuery('input[type="submit"]').click(function(e) {
		if (jQuery('input[name="load_currencies"]:checked').val() == 'yes') {
			jQuery('#content_step').fadeOut();
			jQuery('#buttons_container').html(backend_object.loading_currencies_text+'<img src="'+backend_object.loading_image+'"/> <div id="porcent_loading_fe" style="display: inline;"> 0%</div>');
			execute_load_currencies();
			e.preventDefault();
		} else if (jQuery('input[name="load_currencies"]:checked').val() == 'yes_only_a_currency') {
			jQuery('#content_step').fadeOut();
			jQuery('#buttons_container').html(backend_object.loading_currencies_text+'<img src="'+backend_object.loading_image+'"/>');
			current_request = parseInt(jQuery('#selected_currency').val());
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

	jQuery('#selected_currency').select2();
	jQuery('input[name="load_currencies"]').change(function(e) {
		if (jQuery('input[name="load_currencies"]:checked').val() == 'yes_only_a_currency') {
			jQuery('#container_select_currency').fadeIn();
		} else {
			jQuery('#container_select_currency').fadeOut();
		}
	});
});


function execute_load_currencies() {
	if (current_request < ajax_urls.length) {
		upload_bar();
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
	            console.log('error');
	            current_request++;
	           	execute_load_currencies();
	        }

	    });
	}  else {
		jQuery('form').submit();
	}
}
function upload_bar() {
	var current_width = jQuery('.stepwizard-row-bar').width() / jQuery('.stepwizard-row-bar').parent().width() * 100;
	//var current_width = current_width.replace('%', '');
	//current_width = parseFloat(current_width);
	console.log(current_width);
	
	jQuery('.stepwizard-row-bar').css("width", (current_width+(backend_object.porcent_per_steep/ajax_urls.length))+'%');
	jQuery('#porcent_loading_fe').html(' '+Math.round((100/ajax_urls.length)*current_request)+' %');
	
}