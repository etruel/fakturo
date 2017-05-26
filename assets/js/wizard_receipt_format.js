var example_number = 0;
jQuery(document).ready(function() {
	
	
	jQuery('#fakturo_system_options_group_digits_receipt_number').change(function(e) {
		updateExample();
	});
	
	updateExampleReceipt();

	setInterval(function(){ update_example_number_receipt(); }, 300);


});


function update_example_number_receipt() {
	example_number = example_number+1;
	if (parseInt(jQuery('#fakturo_system_options_group_digits_receipt_number').val())+1 == example_number.toString().length) {
		example_number = 0;
	}
	updateExampleReceipt();

}


function updateExampleReceipt() {
	var newVal = '';
	newVal = newVal+padLeft(example_number.toString(),  parseInt(jQuery('#fakturo_system_options_group_digits_receipt_number').val()) );
	jQuery("#receipt_format_test").html(newVal);
}

