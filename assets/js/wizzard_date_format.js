
jQuery(document).ready(function() {
	
	
	jQuery('#fakturo_system_options_group_dateformat').change(function(e) {
		updateExample();
	});
	
	updateExample();


});


function updateExample() {
	var newVal = '';
	if (jQuery('#fakturo_system_options_group_dateformat').val() == 'd/m/Y') {
		newVal = date_object.date_one;
	} else {
		newVal = date_object.date_two;
	}
	jQuery("#date_format_test").html(newVal);
}

