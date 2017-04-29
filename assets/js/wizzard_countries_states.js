var current_request = 0;
var ajax_urls = new Array();

jQuery(document).ready(function() {
	
	for (var c = 1; c <= 246; c++) {
		ajax_urls.push(backend_object.ajax_url+'?action=fktr_load_countries_states&country_id='+c);
	}
	jQuery('input[type="submit"]').click(function(e) {
		if (jQuery('input[name="load_contries_states"]:checked').val() == 'yes') {
			jQuery('#buttons_container').html(backend_object.loading_states_text+'<img src="'+backend_object.loading_image+'"/>');
			execute_load_countries();
			e.preventDefault();
		}
	})
});


function execute_load_countries() {
	if (current_request < ajax_urls.length) {
		upload_bar();
		console.log('executing:'+ajax_urls[current_request]);
		jQuery.ajax({
	        url: ajax_urls[current_request],
	        type: 'get',
	        contentType: false,
	        processData: false,
	                    
	        success: function (response) {
	            current_request++;
	            execute_load_countries();
	        },  
	        error: function (response) {
	            console.log('error');
	        }

	    });
	}  else {
		jQuery('form').submit();
	}
}
function upload_bar() {
	var new_width = (backend_object.porcent_per_steep/ajax_urls.length)*current_request;
	jQuery('.stepwizard-row-bar').css("width", new_width+"%");
}