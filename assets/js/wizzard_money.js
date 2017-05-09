var money_to_test = 9999999.989;
jQuery(document).ready(function() {
	jQuery('#fakturo_system_options_group_currency').select2();
	update_money_test();
	jQuery('#fakturo_system_options_group_decimal_numbers').change(function(e) {
		update_money_test();
		e.preventDefault();
	});
	jQuery('#fakturo_system_options_group_decimal_numbers').keyup(function(e) {
		update_money_test();
		e.preventDefault();
	});
	jQuery('#fakturo_system_options_group_decimal').change(function(e) {
		update_money_test();
		e.preventDefault();
	});
	jQuery('#fakturo_system_options_group_decimal').keyup(function(e) {
		update_money_test();
		e.preventDefault();
	});
	jQuery('#fakturo_system_options_group_thousand').change(function(e) {
		update_money_test();
		e.preventDefault();
	});
	jQuery('#fakturo_system_options_group_thousand').keyup(function(e) {
		update_money_test();
		e.preventDefault();
	});
	jQuery('#fakturo_system_options_group_currency_position').change(function(e) {
		update_money_test();
		e.preventDefault();
	});
	jQuery('#fakturo_system_options_group_currency_position').keyup(function(e) {
		update_money_test();
		e.preventDefault();
	});
	jQuery('#fakturo_system_options_group_currency').change(function(e) {
		update_money_test();
		e.preventDefault();
	});
	jQuery(document).on('popup-new-term-sucess', function(e, new_currency_id) {
		var new_currency = {term_id:new_currency_id, symbol: jQuery('#term_meta_symbol').val()};
		money_object.currencies.push(new_currency);
	});
	
});
function update_money_test() { 
	var simbol = '$';
	if (jQuery('#fakturo_system_options_group_currency').val() > 0) {
		var current_currency = getCurrency(parseInt(jQuery('#fakturo_system_options_group_currency').val()));
		if (current_currency)  {
			simbol = current_currency.symbol;
		}
	}
	var simbol_before = '';
	var simbol_after = '';
	if (jQuery('#fakturo_system_options_group_currency_position').val() == 'before') {
		simbol_before = simbol+' ';
	} else {
		simbol_after = ' '+simbol;
	}
	var new_money_format = money_to_test.formatMoney(jQuery('#fakturo_system_options_group_decimal_numbers').val(), jQuery('#fakturo_system_options_group_decimal').val(),  jQuery('#fakturo_system_options_group_thousand').val());
	jQuery('#money_format_test').html(simbol_before+new_money_format+simbol_after);
}
function getCurrency(currency_id) {
	var r = false;
	var currencies = money_object.currencies;
	for (var i = 0; i < currencies.length; i++) {
		if (currencies[i].term_id == currency_id) {
			r = currencies[i];
			break;
		}
	}
	return r;
}


Number.prototype.formatMoney = function(places, decimal, thousand) {
	places = !isNaN(places = Math.abs(places)) ? places : 2;
	thousand = thousand || ",";
	decimal = decimal || ".";
	var number = this, 
	    negative = number < 0 ? "-" : "",
	    i = parseInt(number = Math.abs(+number || 0).toFixed(places), 10) + "",
	    j = (j = i.length) > 3 ? j % 3 : 0;
	return negative + (j ? i.substr(0, j) + thousand : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousand) + (places ? decimal + Math.abs(number - i).toFixed(places).slice(2) : "");
};