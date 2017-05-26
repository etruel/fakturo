
jQuery(document).ready(function() {
	
	
	jQuery('#fakturo_system_options_group_dateformat').change(function(e) {
		updateExampleDate();
	});
	
	updateExampleDate();


});


function updateExampleDate() {
	var newVal = '';
	if (jQuery('#fakturo_system_options_group_dateformat').val() == 'd/m/Y') {
		newVal = date_object.date_one;
	} else {
		newVal = date_object.date_two;
	}
	jQuery("#date_format_test").html(newVal);
}

