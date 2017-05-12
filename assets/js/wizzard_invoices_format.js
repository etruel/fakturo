var example_invoi_type = {term_id:759, name: "Type B", slug:"tipo-b", short_name:"invB", symbol:"B"};
var example_sale_point = {term_id:759, code: "2"};
var example_number = '1295';
jQuery(document).ready(function() {
	
	jQuery('#fakturo_system_options_group_invoice_type').select2();
	jQuery('#fakturo_system_options_group_sale_point').select2();

	jQuery('#fakturo_system_options_group_list_invoice_number').select2();
	jQuery('#fakturo_system_options_group_list_invoice_number').on("select2:select", function (evt) {
		var element = evt.params.data.element;
		var jelement = jQuery(element);

		jelement.detach();
		jQuery(this).append(jelement);
		jQuery(this).trigger("change");
	});
	jQuery('#fakturo_system_options_group_list_invoice_number').change(function(e) {
		updateExample();
	});
	jQuery('#fakturo_system_options_group_digits_invoice_number').change(function(e) {
		updateExample();
	});
	updateExample();

	jQuery('#error-page form').submit(function(e) {
		validate_next(e);
	})
});

function validate_next(e) {
	if (parseInt(jQuery('#fakturo_system_options_group_invoice_type').val()) <= 0) {
		jQuery('#fakturo_system_options_group_invoice_type').select2('open');
		e.preventDefault();
		return false;
	}
}

function updateExample() {
	
	if (parseInt(jQuery('#fakturo_system_options_group_digits_invoice_number').val()) == 3) {
		var example_number = '129';
	} else if (parseInt(jQuery('#fakturo_system_options_group_digits_invoice_number').val()) == 2) {
		var example_number = '12';
	} else {
		var example_number = '1295';
	}
	var newVal = '';
	var sale_point = example_sale_point;
	var invoice_type = example_invoi_type;
	var cur_list_invoice_number = jQuery('#fakturo_system_options_group_list_invoice_number').val();
	var cur_list_invoice_number_separator = ' ';
	var add_separator = true;
	if (cur_list_invoice_number == null || cur_list_invoice_number.length == 0) {
		jQuery("#invoice_format_test").html('Select a element.');
		return false;
	}
	for (var i = 0; i < cur_list_invoice_number.length; i++) {
		
		if ((i+1) == cur_list_invoice_number.length) {
			add_separator = false;
		}
		if (cur_list_invoice_number[i] == 'sale_point') {
			if (sale_point) {
				newVal = newVal+padLeft(sale_point.code, 4)+(add_separator?cur_list_invoice_number_separator:'');
			}
		}
		if (cur_list_invoice_number[i] == 'invoice_type_name') {
			if (invoice_type) {
				newVal = newVal+invoice_type.name+(add_separator?cur_list_invoice_number_separator:'');
			}
		}
		if (cur_list_invoice_number[i] == 'invoice_type_short_name') {
			if (invoice_type) {
				newVal = newVal+invoice_type.short_name+(add_separator?cur_list_invoice_number_separator:'');
			}
		}
		if (cur_list_invoice_number[i] == 'invoice_type_symbol') {
			if (invoice_type) {
				newVal = newVal+invoice_type.symbol+(add_separator?cur_list_invoice_number_separator:'');
			}
		}
		if (cur_list_invoice_number[i] == 'invoice_number') {
			newVal = newVal+padLeft(example_number,  parseInt(jQuery('#fakturo_system_options_group_digits_invoice_number').val()) )+(add_separator?cur_list_invoice_number_separator:'');
		}
	}
	
	jQuery("#invoice_format_test").html(newVal);
}

function padLeft(nr, n, str) {
    return Array(n-String(nr).length+1).join(str||'0')+nr;
}