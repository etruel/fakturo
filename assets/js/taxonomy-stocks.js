jQuery(document).ready(function() {

	jQuery('.term-name-wrap label').html('Order number');
	jQuery('.term-name-wrap p').html('Enter a Order number.');

	activate_search_products();

	var decimal_numbers = parseInt(system_setting.decimal_numbers);
	var decimal_ex = '';
	for (var i = 0; i < decimal_numbers; i++) {
		decimal_ex = decimal_ex + '0';
	}
	jQuery('#term_meta_cost').mask("#" + system_setting.thousand + "##0" + system_setting.decimal + decimal_ex, { reverse: true });



	jQuery('#term_meta_product').select2();
	jQuery('#term_meta_location').select2();
	system_setting.datetimepicker = jQuery.parseJSON(system_setting.datetimepicker);
	jQuery.datetimepicker.setLocale(system_setting.datetimepicker.lang);

	jQuery('#term_meta_date').datetimepicker({
		lang: system_setting.datetimepicker.lang,
		dayOfWeekStart: system_setting.datetimepicker.firstDay,
		formatTime: system_setting.datetimepicker.timeFormat,
		format: system_setting.datetimepicker.printFormat,
		formatDate: system_setting.datetimepicker.dateFormat,
		maxDate: system_setting.datetimepicker.dateFormat,
		timepicker: false,
	});

	validateForm = function(b) {
		if (jQuery('#tag-name').val() == '') {
			jQuery('.term-name-wrap').addClass("form-invalid");
			jQuery('#tag-name').focus();
			jQuery('#tag-name').change(function() {
				jQuery('.term-name-wrap').removeClass("form-invalid")
			});
			return false;
		}
		if (jQuery('#product_select').val() == null) {
			jQuery('.select2-search__field').focus();
			return false;
		}
		if (parseInt(jQuery('#term_meta_location').val()) < 1) {
			jQuery('#term_meta_location').select2('open');
			return false;
		}
		if (jQuery('#term_meta_quality').val() == '') {
			jQuery('#quality_div').addClass("form-invalid");
			jQuery('#term_meta_quality').focus();
			jQuery('#term_meta_quality').change(function() {
				jQuery('#quality_div').removeClass("form-invalid")
			});
			return false;
		}
		if (jQuery('#term_meta_cost').val() == '') {
			jQuery('#cost_div').addClass("form-invalid");
			jQuery('#term_meta_cost').focus();
			jQuery('#term_meta_cost').change(function() {
				jQuery('#cost_div').removeClass("form-invalid")
			});
			return false;
		}
		if (jQuery('#term_meta_date').val() == '') {
			jQuery('#date_div').addClass("form-invalid");
			jQuery('#term_meta_date').focus();
			jQuery('#term_meta_date').change(function() {
				jQuery('#date_div').removeClass("form-invalid")
			});
			return false;
		}


		return true;
	}

	jQuery('form').submit(function(e) {
		if (parseInt(jQuery('#term_meta_invoice_type').val()) < 1) {
			jQuery('#term_meta_invoice_type').select2('open');
			e.preventDefault();
			return false;
		}

	});


});

function activate_search_products() {
	jQuery("#product_select").select2({

		ajax: {
			url: system_setting.ajax_url,
			dataType: 'json',
			delay: 250,
			data: function(params) {
				return {
					action: 'get_products',
					s: params.term, // search term
					paged: params.page
				};
			},
			processResults: function(data, params) {
				// parse the results into the format expected by Select2
				// since we are using custom formatting functions we do not need to
				// alter the remote JSON data, except to indicate that infinite
				// scrolling can be used
				params.page = params.page || 1;
				//console.log(data);
				return {
					results: data.items,
					pagination: {
						more: (params.page * 30) < data.total_count
					}
				};
			},
			cache: true
		},
		escapeMarkup: function(markup) { return markup; },
		minimumInputLength: system_setting.characters_to_search,
		templateResult: formatRepo,
		templateSelection: formatRepoSelection,
		maximumSelectionLength: 1,
		placeholder: system_setting.txt_search_products,
	});

}

function getSymbolFromCurrencyId(term_id) {
	var r = '$';
	var currencies = jQuery.parseJSON(term_id);
	for (var i = 0; i < currencies.length; i++) {
		if (currencies[i].term_id == term_id) {
			r = currencies[i].symbol;
			break;
		}
	}
	return r;
}

Number.prototype.formatMoney = function(c, d, t) {
	var n = this,
		c = isNaN(c = Math.abs(c)) ? 2 : c,
		d = d == undefined ? "." : d,
		t = t == undefined ? "," : t,
		s = n < 0 ? "-" : "",
		i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "",
		j = (j = i.length) > 3 ? j % 3 : 0;
	return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
};

function formatRepo(repo) {
	if (repo.loading) {
		return repo.text;
	}

	var markup = "<div class='select2-result-product clearfix'>" +
		"<div class='select2-result-product__avatar'><img src='" + repo.img + "' /></div>" +
		"<div class='select2-result-product__meta'>" +
		"<div class='select2-result-product__title'>" + repo.title + "</div>";


	if (system_setting.default_code == 'internal_code' && repo.id) {
		code_search = '<strong>(' + repo.id + ')</strong> ';
	}
	if (repo.datacomplete) {

		if (system_setting.default_code == 'reference' && repo.datacomplete.reference) {
			code_search = '<strong>(' + repo.datacomplete.reference + ')</strong> ';
		}
		if (system_setting.default_code == 'manufacturers_code' && repo.datacomplete.manufacturers) {
			code_search = '<strong>(' + repo.datacomplete.manufacturers + ')</strong> ';
		}

		if (code_search == '' && repo.datacomplete.reference) {
			code_search = '<strong>(' + repo.datacomplete.reference + ')</strong> ';
		}
	}


	if (repo.description) {

		markup += "<div class='select2-result-product__description'>" + code_search + '' + repo.description + " </div>";
	}
	else {
		markup += "<div class='select2-result-product__description'>" + code_search + " </div>";
	}
	repo.datacomplete.cost = parseFloat(repo.datacomplete.cost);
	markup += "<div class='select2-result-product__statistics'>" +
		"<div class='select2-result-product__forks'>" + system_setting.txt_cost + ": " + ((system_setting.currency_position == 'before') ? getSymbolFromCurrencyId(repo.datacomplete.currency) : "") + " " + repo.datacomplete.cost.formatMoney(system_setting.decimal_numbers, system_setting.decimal, system_setting.thousand) + " " + ((system_setting.currency_position == 'after') ? getSymbolFromCurrencyId(repo.datacomplete.currency) : "") + "</div>" +

		"</div>" +
		"</div></div>";

	return markup;
}

function formatRepoSelection(repo) {
	return repo.title || repo.text;
}
