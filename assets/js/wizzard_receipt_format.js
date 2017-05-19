var example_number = 0;
jQuery(document).ready(function() {
	
	
	jQuery('#fakturo_system_options_group_digits_receipt_number').change(function(e) {
		updateExample();
	});
	
	updateExample();

	setInterval(function(){ update_example_number(); }, 300);


});


function update_example_number() {
	example_number = example_number+1;
	if (parseInt(jQuery('#fakturo_system_options_group_digits_receipt_number').val())+1 == example_number.toString().length) {
		example_number = 0;
	}
	updateExample();

}


function updateExample() {
	var newVal = '';
	newVal = newVal+padLeft(example_number.toString(),  parseInt(jQuery('#fakturo_system_options_group_digits_receipt_number').val()) );
	jQuery("#receipt_format_test").html(newVal);
}


function padLeft(nr, n, str) {
    return Array(n-String(nr).length+1).join(str||'0')+nr;
}