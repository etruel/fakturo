var example_invoi_type = {term_id:759, name: "Invoice", slug:"tipo-b", short_name:"inv", symbol:"I"};
var example_sale_point = {term_id:759, code: "1"};
var example_number = 0;
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
	jQuery(document).on('popup-new-term-sucess', function(e, new_invoice_id) {
		console.log(jQuery("input[name='taxonomy']").val());
		if (jQuery("input[name='taxonomy']").val() == 'fktr_invoice_types') {
			var new_invoices_type = {term_id:new_invoice_id, name: jQuery('#tag-name').val(), short_name: jQuery('#term_meta_short_name').val(), symbol: jQuery('#term_meta_symbol').val()};
			invoices_object.invoices_types.push(new_invoices_type);
		}
	});
	
	jQuery(document).on('popup-new-term-sucess', function(e, new_sale_point_id) {
		if (jQuery("input[name='taxonomy']").val() == 'fktr_sale_points') {
			var new_sale_point = {term_id:new_sale_point_id, name: jQuery('#tag-name').val(), code: jQuery('#term_meta_code').val()};
			invoices_object.sale_points.push(new_sale_point);
		}
		
	});
	

	updateExample();

	jQuery('#error-page form').submit(function(e) {
		validate_next(e);
	})


	
	setInterval(function(){ update_example_number(); }, 300);


});


function update_example_number() {
	example_number = example_number+1;
	if (parseInt(jQuery('#fakturo_system_options_group_digits_invoice_number').val())+1 == example_number.toString().length) {
		example_number = 0;
	}
	updateExample();

}
function validate_next(e) {
	if (parseInt(jQuery('#fakturo_system_options_group_invoice_type').val()) <= 0) {
		jQuery('#fakturo_system_options_group_invoice_type').select2('open');
		e.preventDefault();
		return false;
	}
	if (parseInt(jQuery('#fakturo_system_options_group_sale_point').val()) <= 0) {
		jQuery('#fakturo_system_options_group_sale_point').select2('open');
		e.preventDefault();
		return false;
	}
}

function updateExample() {
	
	
	var newVal = '';
	var sale_point = example_sale_point;
	if (jQuery('#fakturo_system_options_group_sale_point').val() > 0) {
		var current_sale_point = getSalePoint(parseInt(jQuery('#fakturo_system_options_group_sale_point').val()));
		if (current_sale_point)  {
			sale_point = current_sale_point;
		}
	}
	var invoice_type = example_invoi_type;
	if (jQuery('#fakturo_system_options_group_invoice_type').val() > 0) {
		var current_invoice_type = getInvoiceType(parseInt(jQuery('#fakturo_system_options_group_invoice_type').val()));
		if (current_invoice_type)  {
			invoice_type = current_invoice_type;
		}
	}



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
			newVal = newVal+padLeft(example_number.toString(),  parseInt(jQuery('#fakturo_system_options_group_digits_invoice_number').val()) )+(add_separator?cur_list_invoice_number_separator:'');
		}
	}
	
	jQuery("#invoice_format_test").html(newVal);
}
function getInvoiceType(invoice_type_id) {
	var r = false;
	var invoice_types = invoices_object.invoices_types;
	for (var i = 0; i < invoice_types.length; i++) {
		if (invoice_types[i].term_id == invoice_type_id) {
			r = invoice_types[i];
			break;
		}
	}
	return r;
}
function getSalePoint(sale_point_id) {
	var r = false;
	var sale_points = invoices_object.sale_points;
	for (var i = 0; i < sale_points.length; i++) {
		if (sale_points[i].term_id == sale_point_id) {
			r = sale_points[i];
			break;
		}
	}
	return r;
}

function padLeft(nr, n, str) {
    return Array(n-String(nr).length+1).join(str||'0')+nr;
}