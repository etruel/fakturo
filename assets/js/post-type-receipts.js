jQuery(document).ready(function() {
	
	jQuery('#title-prompt-text').remove();
	jQuery("#title").attr("readonly","readonly");
	jQuery("#client_id").select2();
	jQuery("#payment_type_id").select2();
	jQuery("#currency_id").select2();
	
	
	jQuery.datetimepicker.setLocale(receipts_object.datetimepicker.lang);
	
	var decimal_numbers = parseInt(receipts_object.decimal_numbers);
	var decimal_ex = '';
	for (var i = 0; i < decimal_numbers; i++) {
		decimal_ex = decimal_ex+'0';
	}
	DefaultMaskNumbers = "#"+receipts_object.thousand+"##0"+receipts_object.decimal+decimal_ex;
	jQuery('#available_to_include').mask(DefaultMaskNumbers, {reverse: true});
	jQuery('#cash').mask(DefaultMaskNumbers, {reverse: true});
	jQuery("#currency_id").change(function(e){
		update_invoices();
	});
	
	jQuery("#client_id").change(function(e){
		if (parseInt(jQuery("#client_id").val()) > 0) {
			
			jQuery('#invoices_table').html('<tr><th>'+receipts_object.txt_loading+'...</th></tr>');
			jQuery('#client_available_to_include').html(''+receipts_object.txt_loading+'...');
			
			var data = {
				action: 'receipt_client_data',
				client_id: this.value
			}
			
			
			jQuery.post(receipts_object.ajax_url, data, function( data ) {
				var data_client = jQuery.parseJSON(data);
				console.log(data_client);
				var to_currency = getCurrentCurrencyId();
				
				var invHtml = '';
				for(var i = 0; i < data_client.invoice_sales.length; i++) {
					var from_currency = data_client.invoice_sales[i].invoice_currency;
					var trasm_money = transformMoney(from_currency, to_currency, receipts_object.default_currency, parseInt(data_client.invoice_sales[i].in_total));
				
					invHtml = invHtml+'<tr id="in_'+i+'"'+((i%2 == 0)?' class="tr_gray"':'')+'><td><input type="checkbox" id="check_inv_'+i+'" class="check_invs"/></td> <td class="in_column">'+data_client.invoice_sales[i].date+'</td><td class="in_column">'+data_client.invoice_sales[i].post_title+'</td><td class="in_column">'+((receipts_object.currency_position=='before')?''+getSymbolFromCurrencyId(data_client.invoice_sales[i].invoice_currency)+' ':'')+''+parseInt(data_client.invoice_sales[i].in_total).formatMoney(receipts_object.decimal_numbers, receipts_object.decimal, receipts_object.thousand)+''+((receipts_object.currency_position=='after')?' '+getSymbolFromCurrencyId(data_client.invoice_sales[i].invoice_currency)+'':'')+'</td><td class="in_column">'+((receipts_object.currency_position=='before')?''+getSymbolFromCurrencyId(to_currency)+' ':'')+''+parseInt(trasm_money).formatMoney(receipts_object.decimal_numbers, receipts_object.decimal, receipts_object.thousand)+''+((receipts_object.currency_position=='after')?' '+getSymbolFromCurrencyId(to_currency)+'':'')+'</td><td class="in_column"><input type="text" id="to_pay_'+i+'" name="to_pay[]" value=""/> </td></tr>';
				}
				jQuery('#invoices_table').html(invHtml);
				jQuery('#client_available_to_include').html(''+((receipts_object.currency_position=='before')?''+getSymbolFromCurrencyId(receipts_object.default_currency)+' ':'')+''+parseInt(data_client.balance).formatMoney(receipts_object.decimal_numbers, receipts_object.decimal, receipts_object.thousand)+''+((receipts_object.currency_position=='after')?' '+getSymbolFromCurrencyId(receipts_object.default_currency)+'':'')+'');
				jQuery('#client_available_to_include').data('available', parseInt(data_client.balance));
				
			});
			
			
		}
	});
	jQuery('#add_more_check').click(function(e){
		openAddCheckPopPup();
		e.preventDefault();
		return false;
	});
	
});

function update_invoices() {
	alert("Update invoices and data.");
}

function openAddCheckPopPup() {
	
	var newHtml = '<div id="content_popup_check"><table class="form-table"><tr><td>Bank</td><td>'+receipts_object.select_bank_entities+'</td></tr><tr><td>Serial number</td><td><input type="text" name="popup_check_serial_number" id="popup_check_serial_number"/> </td></tr> <tr><td>Currency</td><td>'+receipts_object.select_bank_currencies+'</td></tr>  <tr><td>Value</td><td><input type="text" name="popup_check_value" id="popup_check_value"/> </td></tr> </tr>  <tr><td>Due date</td><td><input type="text" name="popup_check_due_date" id="popup_check_due_date" value="'+receipts_object.current_date+'"/> </td></tr> <tr><td>Notes</td><td><textarea style="width:95%;" rows="4" name="popup_check_notes" id="popup_check_notes"></textarea></td></tr> </table></div><div id="buttons_check_popup"><a href="#" class="button-primary add" id="accept_check" style="margin:3px;">Accept</a> <a href="#" class="button" id="btn_cancel_check_popup" style="margin:3px;">'+receipts_object.txt_cancel+'</a></div>';
	jQuery('#receipt_check_popup').html(newHtml);
	jQuery('#receipt_check_popup').fadeIn();
	jQuery('#popup_check_background').fadeIn();
	jQuery('#popup_check_banks').select2();
	jQuery('#popup_check_currencies').select2();

	jQuery('#popup_check_due_date').datetimepicker({
				lang: receipts_object.datetimepicker.lang,
				dayOfWeekStart:  receipts_object.datetimepicker.firstDay,
				formatTime: receipts_object.datetimepicker.timeFormat,
				format: receipts_object.datetimepicker.printFormat,
				formatDate: receipts_object.datetimepicker.dateFormat,
				timepicker:false
			});
	
	
	jQuery('#btn_cancel_check_popup').click(function(e){
		jQuery('#receipt_check_popup').fadeOut();
		jQuery('#popup_check_background').fadeOut();
		jQuery('#receipt_check_popup').html('');
		e.preventDefault();
		return false;
	});
	jQuery('#accept_check').click(function(e){
		var error = false;
		if (!error && parseInt(jQuery('#popup_check_banks').val()) < 1) {
			jQuery('#popup_check_banks').select2('open');
			error = true;
		}
		if (!error && jQuery('#popup_check_serial_number').val() == '') {
			jQuery('#popup_check_serial_number').focus();
			error = true;
		}
		
		if (!error && parseInt(jQuery('#popup_check_currencies').val()) < 1) {
			jQuery('#popup_check_currencies').select2('open');
			error = true;
		}
		if (!error && jQuery('#popup_check_value').val() == '') {
			jQuery('#popup_check_value').focus();
			error = true;
		}
		if (!error && jQuery('#popup_check_due_date').val() == '') {
			jQuery('#popup_check_due_date').focus();
			error = true;
		}
		if (!error) {
			alert("Add check to list");
		}
		e.preventDefault();
		return false;
	});
	
	
}
function getRateFromCurrencyId(term_id) {
	var r = 1;
	var currencies = receipts_object.currencies;
	for (var i = 0; i < currencies.length; i++) {
		if (currencies[i].term_id == term_id) {
			r = currencies[i].rate;
			break;
		}
	}
	return parseFloat(r);
}
function getCurrentCurrencyId() {
	if (jQuery("#currency_id").val() > 0) {
		return jQuery("#currency_id").val();
	}
	return receipts_object.default_currency;
}

function getSymbolFromCurrencyId(term_id) {
	var r = '$';
	var currencies = receipts_object.currencies;
	for (var i = 0; i < currencies.length; i++) {
		if (currencies[i].term_id == term_id) {
			r = currencies[i].symbol;
			break;
		}
	}
	return r;
}

function transformMoney(from_c, to_c, default_c, value_money) {
	var retorno = value_money;
	var current_currency = from_c;
	if (from_c != to_c) {
		if (default_c != current_currency) {
			var rate = getRateFromCurrencyId(current_currency);
			retorno = retorno*rate;
			current_currency = default_c;
		}
		if (current_currency != to_c) {
			var rate = getRateFromCurrencyId(to_c);
			retorno = retorno/rate;
		}
	}
	return retorno;
}

Number.prototype.formatMoney = function(c, d, t){
var n = this, 
    c = isNaN(c = Math.abs(c)) ? 2 : c, 
    d = d == undefined ? "." : d, 
    t = t == undefined ? "," : t, 
    s = n < 0 ? "-" : "", 
    i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "", 
    j = (j = i.length) > 3 ? j % 3 : 0;
   return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
 };