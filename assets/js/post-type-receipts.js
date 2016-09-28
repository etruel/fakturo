var DefaultMaskNumbers = '';
jQuery(document).ready(function() {
	
	jQuery('#title-prompt-text').remove();
	jQuery("#title").attr("readonly","readonly");
	jQuery("#client_id").select2();
	jQuery("#payment_type_id").select2();
	jQuery("#currency_id").select2();
	updateKeyPress();
	
	jQuery.datetimepicker.setLocale(receipts_object.datetimepicker.lang);
	
	var decimal_numbers = parseInt(receipts_object.decimal_numbers);
	var decimal_ex = '';
	for (var i = 0; i < decimal_numbers; i++) {
		decimal_ex = decimal_ex+'0';
	}
	DefaultMaskNumbers = "#"+receipts_object.thousand+"##0"+receipts_object.decimal+decimal_ex;
	jQuery('#available_to_include').mask(DefaultMaskNumbers, {reverse: true});
	jQuery('#available_to_include').keyup(function(e){
		var standarCash = converMaskToStandar(jQuery('#available_to_include').val(), receipts_object);
		var available_to_include = parseFloat(jQuery('#client_available_to_include').data('available'));
		if (standarCash > available_to_include) {
			jQuery('#available_to_include').val(available_to_include.formatMoney(receipts_object.decimal_numbers, receipts_object.decimal, receipts_object.thousand));
		}
		updateTotals();
	});
	jQuery('#available_to_include').change(function(e){
		updateTotals();
	});
	jQuery('#cash').mask(DefaultMaskNumbers, {reverse: true});
	
	jQuery('.receipt_currencies').mask(DefaultMaskNumbers, {reverse: true});
	jQuery("#currency_id").change(function(e){
		update_invoices();
		updateTotals();
	});
	jQuery(".receipt_currencies").change(function(){
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
					var total_affected = 0;
					for (receipts_id in data_client.invoice_sales[i].receipts) { 
						total_affected = total_affected+parseFloat(data_client.invoice_sales[i].receipts[receipts_id]);
					}
					var total_account = parseFloat(data_client.invoice_sales[i].in_total)-total_affected;
					var from_currency = data_client.invoice_sales[i].invoice_currency;
					var trasm_money = transformMoney(from_currency, to_currency, receipts_object.default_currency, total_account);
					
					invHtml = invHtml+'<tr id="in_'+i+'"'+((i%2 == 0)?' class="tr_gray"':'')+'><td><input type="checkbox" id="check_inv_'+i+'" class="check_invs"/></td> <td class="in_column">'+data_client.invoice_sales[i].date+'</td><td class="in_column">'+data_client.invoice_sales[i].post_title+'</td><td class="in_column">'+((receipts_object.currency_position=='before')?''+getSymbolFromCurrencyId(data_client.invoice_sales[i].invoice_currency)+' ':'')+''+parseFloat(data_client.invoice_sales[i].in_total).formatMoney(receipts_object.decimal_numbers, receipts_object.decimal, receipts_object.thousand)+''+((receipts_object.currency_position=='after')?' '+getSymbolFromCurrencyId(data_client.invoice_sales[i].invoice_currency)+'':'')+'</td><td class="in_column" id="inv_trans_total_'+i+'">'+((receipts_object.currency_position=='before')?''+getSymbolFromCurrencyId(to_currency)+' ':'')+''+parseFloat(trasm_money).formatMoney(receipts_object.decimal_numbers, receipts_object.decimal, receipts_object.thousand)+''+((receipts_object.currency_position=='after')?' '+getSymbolFromCurrencyId(to_currency)+'':'')+'</td><td class="in_column"><input type="text" class="to_pay" id="to_pay_'+i+'" name="to_pay[]" value=""/> <input type="hidden" name="inv_total_account[]" class="inv_total_account" id="inv_total_account_'+i+'" value="'+trasm_money+'"/> <input type="hidden" name="inv_origin_account[]" class="inv_origin_account" id="inv_origin_account_'+i+'" value="'+total_account+'"/><input type="hidden" name="inv_currency[]" class="inv_currency" id="inv_currency_'+i+'" value="'+from_currency+'"/></td></tr>';
				}
				jQuery('#invoices_table').html(invHtml);
				jQuery('#client_available_to_include').html(''+((receipts_object.currency_position=='before')?''+getSymbolFromCurrencyId(receipts_object.default_currency)+' ':'')+''+parseFloat(data_client.balance).formatMoney(receipts_object.decimal_numbers, receipts_object.decimal, receipts_object.thousand)+''+((receipts_object.currency_position=='after')?' '+getSymbolFromCurrencyId(receipts_object.default_currency)+'':'')+'');
				jQuery('#client_available_to_include').data('available', parseFloat(data_client.balance));
				updateTotals();
			});
			
			
		}
	});
	jQuery('#add_more_check').click(function(e){
		openAddCheckPopPup();
		e.preventDefault();
		return false;
	});
	
	jQuery('.ck_delete').click(function(e){
		var check_id = jQuery(this).data('id');
		jQuery(this).parent().parent().remove();
		e.preventDefault();
		return false;
	});
	jQuery('.ck_edit').click(function(e){
		var check_id = jQuery(this).data('id');
		openEditCheckPopup(check_id);
		e.preventDefault();
		return false;
	});
	
	
});
function getTotalDebt() {
	var retorno = 0;
	jQuery('.inv_total_account').map(function(e){
		retorno = retorno+parseFloat(jQuery(this).val());
	});
	return retorno;
}
function updateTotals() {
	var available_to_include = parseFloat(jQuery('#client_available_to_include').data('available'));
	available_to_include = transformMoney(receipts_object.default_currency, getCurrentCurrencyId(), receipts_object.default_currency, available_to_include);
	
	var total_debt = parseFloat(getTotalDebt());
	var account_debt_now = available_to_include-total_debt;
	jQuery('#receipt_acc_current_balance').html(''+((receipts_object.currency_position=='before')?''+getSymbolFromCurrencyId(getCurrentCurrencyId())+' ':'')+''+parseFloat(account_debt_now).formatMoney(receipts_object.decimal_numbers, receipts_object.decimal, receipts_object.thousand)+''+((receipts_object.currency_position=='after')?' '+getSymbolFromCurrencyId(getCurrentCurrencyId())+'':'')+'');
	
}

function update_invoices() {
	jQuery('.inv_origin_account').map(function(){
		var indentifier = this.id.replace('inv_origin_account_', '');
		var origin_account = parseFloat(jQuery(this).val());
		var invoice_currency = jQuery('#inv_currency_'+indentifier).val();
		var to_currency = getCurrentCurrencyId();
		var trasm_money = transformMoney(invoice_currency, to_currency, receipts_object.default_currency, origin_account);
		jQuery('#inv_total_account_'+indentifier).val(trasm_money);
		jQuery('#inv_trans_total_'+indentifier).html(''+((receipts_object.currency_position=='before')?''+getSymbolFromCurrencyId(to_currency)+' ':'')+''+parseFloat(trasm_money).formatMoney(receipts_object.decimal_numbers, receipts_object.decimal, receipts_object.thousand)+''+((receipts_object.currency_position=='after')?' '+getSymbolFromCurrencyId(to_currency)+'':'')+'');

	});
	
}

function openEditCheckPopup(check_id) {
	editing_check = check_id;
	openAddCheckPopPup();
	jQuery('#popup_check_banks').val(jQuery('#ck_bank_'+check_id+'').val()).trigger("change");
	jQuery('#popup_check_serial_number').val(jQuery('#ck_number_'+check_id+'').val());
	jQuery('#popup_check_currencies').val(jQuery('#ck_currency_'+check_id+'').val()).trigger("change");;
	jQuery('#popup_check_value').val(jQuery('#ck_value_'+check_id+'').val());
	jQuery('#popup_check_date').val(jQuery('#ck_date_'+check_id+'').val());
	jQuery('#popup_check_cashing_date').val(jQuery('#ck_cashing_date_'+check_id+'').val());
	jQuery('#popup_check_notes').val(jQuery('#ck_notes_'+check_id+'').val());
	
}

function openAddCheckPopPup() {
	
	var newHtml = '<div id="content_popup_check"><table class="form-table"><tr><td>Bank</td><td>'+receipts_object.select_bank_entities+'</td></tr><tr><td>Serial number</td><td><input type="text" name="popup_check_serial_number" id="popup_check_serial_number"/> </td></tr> <tr><td>Currency</td><td>'+receipts_object.select_bank_currencies+'</td></tr>  <tr><td>Value</td><td><input type="text" name="popup_check_value" id="popup_check_value"/> </td></tr> </tr>  <tr><td>Date</td><td><input type="text" name="popup_check_date" id="popup_check_date" value="'+receipts_object.current_date+'"/> </td></tr> <tr><td>Cashing Date</td><td><input type="text" name="popup_check_cashing_date" id="popup_check_cashing_date" value="'+receipts_object.current_date+'"/> </td></tr> <tr><td>Notes</td><td><textarea style="width:95%;" rows="4" name="popup_check_notes" id="popup_check_notes"></textarea></td></tr>  </table></div><div id="buttons_check_popup"><a href="#" class="button-primary add" id="accept_check" style="margin:3px;">Accept</a> <a href="#" class="button" id="btn_cancel_check_popup" style="margin:3px;">'+receipts_object.txt_cancel+'</a></div>';
	jQuery('#receipt_check_popup').html(newHtml);
	jQuery('#receipt_check_popup').fadeIn();
	jQuery('#popup_check_background').fadeIn();
	jQuery('#popup_check_banks').select2();
	jQuery('#popup_check_currencies').select2();
	jQuery('#popup_check_value').mask(DefaultMaskNumbers, {reverse: true});
	jQuery('#popup_check_date').datetimepicker({
				lang: receipts_object.datetimepicker.lang,
				dayOfWeekStart:  receipts_object.datetimepicker.firstDay,
				formatTime: receipts_object.datetimepicker.timeFormat,
				format: receipts_object.datetimepicker.printFormat,
				formatDate: receipts_object.datetimepicker.dateFormat,
				timepicker:false
			});
	jQuery('#popup_check_cashing_date').datetimepicker({
				lang: receipts_object.datetimepicker.lang,
				dayOfWeekStart:  receipts_object.datetimepicker.firstDay,
				formatTime: receipts_object.datetimepicker.timeFormat,
				format: receipts_object.datetimepicker.printFormat,
				formatDate: receipts_object.datetimepicker.dateFormat,
				timepicker:false
			});
	jQuery('#popup_check_banks').select2('open');
	jQuery('#popup_check_banks').on("select2:select", function(e) { 
		jQuery('#popup_check_serial_number').focus();
	});
		

	jQuery('#popup_check_currencies').on("select2:select", function(e) {
		jQuery('#popup_check_value').focus();
	});
	
	
	jQuery('#btn_cancel_check_popup').click(function(e){
		jQuery('#receipt_check_popup').fadeOut();
		jQuery('#popup_check_background').fadeOut();
		jQuery('#receipt_check_popup').html('');
		e.preventDefault();
		return false;
	});
	jQuery('#accept_check').click(function(e){
		submitPopupForm();
		e.preventDefault();
		return false;
	});
	
	
}
var editing_check = -1;
var idck = 0;
function submitPopupForm() {
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
	if (!error && jQuery('#popup_check_date').val() == '') {
		jQuery('#popup_check_date').focus();
		error = true;
	}
	if (!error && jQuery('#popup_check_cashing_date').val() == '') {
		jQuery('#popup_check_cashing_date').focus();
		error = true;
	}
	
	
	if (!error) {
		jQuery('#message_check_table').remove();
		var bank_name = getBankNameFromId(jQuery('#popup_check_banks').val());
		var standarValue = converMaskToStandar(jQuery('#popup_check_value').val(), receipts_object);
		if (editing_check == -1) {
			jQuery('#checks_table').append('<tr class="tr_check_list" id="tr_ck_id_'+idck+'"><td class="ck_column">'+jQuery('#popup_check_serial_number').val()+'</td><td class="ck_column">'+bank_name+'</td><td class="ck_column">'+((receipts_object.currency_position=='before')?''+getSymbolFromCurrencyId(jQuery('#popup_check_currencies').val())+' ':'')+''+jQuery('#popup_check_value').val()+''+((receipts_object.currency_position=='after')?' '+getSymbolFromCurrencyId(jQuery('#popup_check_currencies').val())+'':'')+'</td class="ck_column"><td><a href="#" class="ck_edit" data-id="'+idck+'">Edit</a> - <a href="#" class="ck_delete" data-id="'+idck+'">Delete</a>  <input type="hidden" name="ck_ids[]" class="ck_id" id="ck_id_'+idck+'" value="'+idck+'"/><input type="hidden" name="ck_banks[]" class="ck_bank" id="ck_bank_'+idck+'" value="'+jQuery('#popup_check_banks').val()+'"/><input type="hidden" name="ck_numbers[]" class="ck_number" id="ck_number_'+idck+'" value="'+jQuery('#popup_check_serial_number').val()+'"/><input type="hidden" name="ck_currencies[]" class="ck_currency" id="ck_currency_'+idck+'" value="'+jQuery('#popup_check_currencies').val()+'"/><input type="hidden" name="ck_values[]" class="ck_value" id="ck_value_'+idck+'" value="'+standarValue+'"/><input type="hidden" name="ck_dates[]" class="ck_date" id="ck_date_'+idck+'" value="'+jQuery('#popup_check_date').val()+'"/><input type="hidden" name="ck_cashing_dates[]" class="ck_cashing_date" id="ck_cashing_date_'+idck+'" value="'+jQuery('#popup_check_cashing_date').val()+'"/><input type="hidden" name="ck_notes[]" class="ck_notes" id="ck_notes_'+idck+'" value="'+jQuery('#popup_check_notes').val()+'"/></td></tr>');
			idck = idck+1;
		} else {
			jQuery('#tr_ck_id_'+editing_check).html('<td class="ck_column">'+jQuery('#popup_check_serial_number').val()+'</td><td class="ck_column">'+bank_name+'</td><td class="ck_column">'+((receipts_object.currency_position=='before')?''+getSymbolFromCurrencyId(jQuery('#popup_check_currencies').val())+' ':'')+''+jQuery('#popup_check_value').val()+''+((receipts_object.currency_position=='after')?' '+getSymbolFromCurrencyId(jQuery('#popup_check_currencies').val())+'':'')+'</td class="ck_column"><td><a href="#" class="ck_edit" data-id="'+editing_check+'">Edit</a> - <a href="#" class="ck_delete" data-id="'+editing_check+'">Delete</a>  <input type="hidden" name="ck_ids[]" class="ck_id" id="ck_id_'+editing_check+'" value="'+editing_check+'"/><input type="hidden" name="ck_banks[]" class="ck_bank" id="ck_bank_'+editing_check+'" value="'+jQuery('#popup_check_banks').val()+'"/><input type="hidden" name="ck_numbers[]" class="ck_number" id="ck_number_'+editing_check+'" value="'+jQuery('#popup_check_serial_number').val()+'"/><input type="hidden" name="ck_currencies[]" class="ck_currency" id="ck_currency_'+editing_check+'" value="'+jQuery('#popup_check_currencies').val()+'"/><input type="hidden" name="ck_values[]" class="ck_value" id="ck_value_'+editing_check+'" value="'+standarValue+'"/><input type="hidden" name="ck_dates[]" class="ck_date" id="ck_date_'+editing_check+'" value="'+jQuery('#popup_check_date').val()+'"/><input type="hidden" name="ck_cashing_dates[]" class="ck_cashing_date" id="ck_cashing_date_'+editing_check+'" value="'+jQuery('#popup_check_cashing_date').val()+'"/><input type="hidden" name="ck_notes[]" class="ck_notes" id="ck_notes_'+editing_check+'" value="'+jQuery('#popup_check_notes').val()+'"/></td>');
			editing_check = -1;
		}
		jQuery('#receipt_check_popup').fadeOut();
		jQuery('#popup_check_background').fadeOut();
		jQuery('#receipt_check_popup').html('');
		
		updateChecksList();
		updateTotals();
		
		
		jQuery('.ck_delete').click(function(e){
			var check_id = jQuery(this).data('id');
			jQuery(this).parent().parent().remove();
			updateChecksList();
			e.preventDefault();
			return false;
		});
		jQuery('.ck_edit').click(function(e){
			var check_id = jQuery(this).data('id');
			openEditCheckPopup(check_id);
			e.preventDefault();
			return false;
		});
	}
	
	
}
function updateChecksList() {
	var count = 0;
	jQuery('.tr_check_list').map(function(){
		if (count%2 == 0) {
			jQuery(this).addClass('tr_gray');
		} else {
			jQuery(this).removeClass('tr_gray');
		}
		count++;
	});
	
}


function updateKeyPress() {
	jQuery(document).keypress(function(e) {
		if (e.which == 13) {
			if (jQuery('#receipt_check_popup').is(':visible') && !jQuery('#receipt_check_popup').is(':hidden')) {
				submitPopupForm();
			}
			e.preventDefault();
			return false;	
		}
	});
	
	
	
	jQuery('form input').keypress(function(e) {
		
		if (e.which == 13) {
			
			e.preventDefault();
			return false;
		}
	});
	
	
}

function getBankNameFromId(term_id) {
	var r = term_id;
	var bank_entities = receipts_object.bank_entities;
	for (var i = 0; i < bank_entities.length; i++) {
		if (bank_entities[i].term_id == term_id) {
			r = bank_entities[i].name;
			break;
		}
	}
	return r;
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
			var rate = getCurrentRateFromCurrencies(current_currency);
			retorno = retorno*rate;
			current_currency = default_c;
		}
		if (current_currency != to_c) {
			var rate = getCurrentRateFromCurrencies(to_c);
			retorno = retorno/rate;
		}
	}
	return retorno;
}
function getCurrentRateFromCurrencies(term_id) {
	var r = 1;
	if(jQuery('#receipt_currencies_'+term_id).length ) {
		r = converMaskToStandar(jQuery('#receipt_currencies_'+term_id).val(), receipts_object);
	} else {
		alert("Currency no found. This can cause a problem in the transaction.");
	}
	return parseFloat(r);
}
function converMaskToStandar(valueMasked, maskObject) {
	if (valueMasked == '') {
		return valueMasked;
	}
	if (valueMasked.indexOf(maskObject.decimal) !== -1) {
		var pieceNumber = valueMasked.split(maskObject.decimal);
		
		pieceNumber[0] = pieceNumber[0].split(maskObject.thousand).join('');

		valueMasked = pieceNumber.join('.');
	}
	return valueMasked;
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