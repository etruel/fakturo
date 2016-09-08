var count_products = 0;


var product_data = new Array();
var DefaultMaskNumbers = '';
jQuery(document).ready(function() {
	
	jQuery('#title-prompt-text').remove();
	jQuery("#title").attr("readonly","readonly");
	if (sales_object.post_status == 'publish') {
		return false;
	}
	jQuery("#title").focus(function(e){
		jQuery("#invoice_number").focus();
		
	});
	loadProductData();
	updateKeyPress();
	var numbers_ex = '';
	for (var i = 0; i < sales_object.digits_invoice_number; i++) {
		numbers_ex = numbers_ex+'0';
	}
	jQuery('#invoice_number').val(numbers_ex);
	jQuery('#invoice_number').mask(numbers_ex, {reverse: true});
	jQuery('#invoice_number').keyup(function(e){
		jQuery(this).val(padLeft(jQuery('#invoice_number').val(), sales_object.digits_invoice_number))
	});
	jQuery.datetimepicker.setLocale(sales_object.datetimepicker.lang);
	
	jQuery('#date').datetimepicker({
				lang: sales_object.datetimepicker.lang,
				dayOfWeekStart:  sales_object.datetimepicker.firstDay,
				formatTime: sales_object.datetimepicker.timeFormat,
				format: sales_object.datetimepicker.printFormat,
				formatDate: sales_object.datetimepicker.dateFormat,
				maxDate: sales_object.datetimepicker.dateFormat, 
				timepicker:false,
			});
	var decimal_numbers = parseInt(sales_object.decimal_numbers);
	var decimal_ex = '';
	for (var i = 0; i < decimal_numbers; i++) {
		decimal_ex = decimal_ex+'0';
	}
	DefaultMaskNumbers = "#"+sales_object.thousand+"##0"+sales_object.decimal+decimal_ex;
	jQuery('.invoice_currencies').mask(DefaultMaskNumbers, {reverse: true});
	jQuery('#invoice_discount').mask("##0"+sales_object.decimal+"00", {reverse: true});
	
	
	jQuery('#invoice_discount').keyup(function(){
		
		if (parseFloat(converMaskToStandar(jQuery(this).val(), sales_object)) > 100.00) {
			jQuery(this).val(100.00);
			jQuery(this).mask("##0"+sales_object.decimal+"00", {reverse: true});
		}
	});
	jQuery('#invoice_discount').change(function(){
		
		if (jQuery(this).val() == '') {
			jQuery(this).val(0);
		}
		updateSubTotals();		
	});
	jQuery("#client_id").select2();
	jQuery("#client_data_tax_condition").select2();
	jQuery("#client_data_payment_type").select2();
	jQuery("#sale_point").select2();
	jQuery("#invoice_type").select2();
	jQuery("#invoice_currency").select2();
	jQuery("#invoice_saleman").select2();
	
	
	jQuery("#client_id").change(function() {
		if (jQuery("#client_id").val() < 1) {
			jQuery(".client_data").fadeOut();
			
		} else {
			jQuery(".client_data").fadeIn();
			jQuery("#client_data_id").html('Loading...');
			jQuery("#client_name").html('Loading...');
			jQuery("#client_address").html('Loading...');
			jQuery("#client_city").html('Loading...');
			jQuery("#client_state").html('Loading...');
			jQuery("#client_country").html('Loading...');
			jQuery("#client_taxpayer").html('Loading...');
			jQuery("#client_tax_condition").fadeOut();
			jQuery("#client_payment_type").fadeOut();
			jQuery("#client_price_scale").html('Loading...');
			jQuery("#client_credit_limit").html('Loading...');
			
			
			var data = {
				action: 'get_client_data',
				client_id: this.value
			}
	
			jQuery.post(sales_object.ajax_url, data, function( data ) {
				var data_client = jQuery.parseJSON(data);
				console.log(data_client);
				jQuery("#client_data_id").html(data_client.ID);
				jQuery("#client_name").html(data_client.post_title+'<input type="hidden" name="client_data[name]" value="'+data_client.post_title+'" id="client_data_name"/>');
				jQuery("#client_address").html(data_client.address+'<input type="hidden" name="client_data[address]" value="'+data_client.address+'" id="client_data_address"/>');
				jQuery("#client_city").html(data_client.city+'<input type="hidden" name="client_data[city]" value="'+data_client.city+'" id="client_data_city"/>');
				jQuery("#client_state").html(data_client.selected_state_name+'<input type="hidden" name="client_data[state][id]" value="'+data_client.selected_state+'" id="client_data_state_id"/><input type="hidden" name="client_data[state][name]" value="'+data_client.selected_state_name+'" id="client_data_state_name"/>');
				jQuery("#client_country").html(data_client.selected_country_name+'<input type="hidden" name="client_data[country][id]" value="'+data_client.selected_country+'" id="client_data_country_id"/><input type="hidden" name="client_data[country][name]" value="'+data_client.selected_country_name+'" id="client_data_country_name"/>');
				jQuery("#client_taxpayer").html(data_client.taxpayer+'<input type="hidden" name="client_data[taxpayer]" value="'+data_client.taxpayer+'" id="client_data_taxpayer"/>');
				
				jQuery("#client_tax_condition").fadeIn();
				jQuery("#client_data_tax_condition").val(data_client.selected_tax_condition);
				jQuery("#client_data_tax_condition").select2();
				
				jQuery("#invoice_type").val(getInvoiceTypeFromTaxCondition());
				jQuery("#invoice_type").select2();
				
				jQuery("#client_payment_type").fadeIn();
				jQuery("#client_data_payment_type").val(data_client.selected_payment_type);
				jQuery("#client_data_payment_type").select2();
				
				jQuery("#client_price_scale").html(data_client.selected_price_scale_name+'<input type="hidden" name="client_data[price_scale][id]" value="'+data_client.selected_price_scale+'" id="client_data_price_scale_id"/><input type="hidden" name="client_data[price_scale][name]" value="'+data_client.selected_price_scale_name+'" id="client_data_price_scale_name"/>');
				jQuery("#client_credit_limit").html(data_client.credit_limit+'<input type="hidden" name="client_data[credit_limit]" value="'+data_client.credit_limit+'" id="client_data_credit_limit"/>');
				
				if (data_client.selected_currency > 0) {
					jQuery('#invoice_currency').val(data_client.selected_currency);
				} else {
					jQuery('#invoice_currency').val(sales_object.default_currency);
				}
				jQuery('#invoice_currency').select2();
			});
		}
		
		
		
	});
	jQuery("#client_data_tax_condition").change(function(){
		
		jQuery("#invoice_type").val(getInvoiceTypeFromTaxCondition());
		jQuery("#invoice_type").select2();
		updateProductsDatails();
	});
	jQuery("#invoice_type").change(function(){
		updateProductsDatails();
	});
	jQuery("#invoice_currency").change(function(){
		updateProductsDatails();
	});
	jQuery(".invoice_currencies").change(function(){
		updateProductsDatails();
	});
	jQuery("#sale_point").change(function(){
		updateTitle();
	});
	jQuery("#invoice_number").keyup(function(){
		updateTitle();
	});
	updateTitle();
	
	
	
	jQuery('#product_select').change(function(e){
		
		var identifier = add_selected_product();
		jQuery('#quality_'+identifier).focus();
		updateKeyPress();
		e.preventDefault();
		
		
		
		
	});
	
	jQuery('#addmoreuc').click(function(e) {
		
			add_selected_product();
			e.preventDefault();
			
		});
	jQuery('#uc_actions label').click(function() {
		//delete_user_contact('#uc_ID'+jQuery(this).attr('data-id'));
		//jQuery('#user_contacts').vSort();
	});
	jQuery('#invoice_type').change(function(){
		updateDiscriminatesTaxes();
	});
	activate_search_products();
	updateProductsDatails();
  
});
var addNewDataToTitle = function(val) {
	return val;
}
function updateTitle() {
	
	var newVal = '';
	var sale_point = getCurrentSalePoint();
	var invoice_type =getCurrentInvoiceType();
	var add_separator = true;
	for (var i = 0; i < sales_object.list_invoice_number.length; i++) {
		
		if ((i+1) == sales_object.list_invoice_number.length) {
			add_separator = false;
		}
		if (sales_object.list_invoice_number[i] == 'sale_point') {
			if (sale_point) {
				newVal = newVal+padLeft(sale_point.code, 4)+(add_separator?sales_object.list_invoice_number_separator:'');
			}
		}
		if (sales_object.list_invoice_number[i] == 'invoice_type_name') {
			if (invoice_type) {
				newVal = newVal+invoice_type.name+(add_separator?sales_object.list_invoice_number_separator:'');
			}
		}
		if (sales_object.list_invoice_number[i] == 'invoice_type_short_name') {
			if (invoice_type) {
				newVal = newVal+invoice_type.short_name+(add_separator?sales_object.list_invoice_number_separator:'');
			}
		}
		if (sales_object.list_invoice_number[i] == 'invoice_type_symbol') {
			if (invoice_type) {
				newVal = newVal+invoice_type.symbol+(add_separator?sales_object.list_invoice_number_separator:'');
			}
		}
		if (sales_object.list_invoice_number[i] == 'invoice_number') {
			newVal = newVal+padLeft(jQuery('#invoice_number').val(), sales_object.digits_invoice_number)+(add_separator?sales_object.list_invoice_number_separator:'');
		}
	}
	newVal = addNewDataToTitle(newVal);
	jQuery("#title").val(newVal);
}
function getCurrentSalePoint() {
	var r = false;
	var sale_points = sales_object.sale_points;
	for (var i = 0; i < sale_points.length; i++) {
		if (sale_points[i].term_id == jQuery("#sale_point").val()) {
			r = sale_points[i];
			break;
		}
	}
	return r;
	
}
function loadProductData() {
	for (var key in sales_object.product_data) {
    // skip loop if the property is from prototype
		if (!sales_object.product_data.hasOwnProperty(key)) continue;

		var obj = sales_object.product_data[key];
		product_data[obj.id] =  obj;
		count_products = count_products+999;
	}	
}


var fist_time = false;
function updateKeyPress() {
	
	jQuery('.product_quality').on('keypress', function(e) {
		if (e.which == 13) {
			if (jQuery(this).data('focused') !== undefined) {
				var identifier = this.id.replace('quality_', '');
				jQuery('#unit_price_'+identifier).focus();
			}
			jQuery(this).data('focused', true);
			e.preventDefault();
			return false;
		}
	});
	jQuery('.unit_price_products').on('keypress', function(e) {
		if (e.which == 13) {
			var identifier = this.id.replace('unit_price_', '');
			jQuery('#amount_'+identifier).focus();
			e.preventDefault();
			return false;
		}
	});
	
	jQuery('.products_amounts').on('keypress', function(e) {
		if (e.which == 13) {
			
			jQuery('.select2-search__field').focus();
			e.preventDefault();
			return false;
		}
	});
	
	jQuery('form input').on('keypress', function(e) {
		if (e.which == 13) {
			e.preventDefault();
			return false;
		}
	});
	
	
}

function updateDiscriminatesTaxes() {
	var invoice_type = getCurrentInvoiceType();
	if (invoice_type) {
		
		if (invoice_type.discriminates_taxes == 1) {
			jQuery(".taxes_column").fadeIn();
		} else {
			jQuery(".taxes_column").fadeOut();
		}
	}
	
}
function getCurrentInvoiceType() {
	var r = false;
	var invoice_types = jQuery.parseJSON(sales_object.invoice_types);
	for (var i = 0; i < invoice_types.length; i++) {
		if (invoice_types[i].term_id == jQuery("#invoice_type").val()) {
			r = invoice_types[i];
			break;
		}
	}
	return r;
	
}
function getDescriminateTaxes() {
	var discriminates_taxes = 0;
	var invoice_type = getCurrentInvoiceType();
	if (invoice_type) {
		if (invoice_type.discriminates_taxes == 1) {
			discriminates_taxes = 1;
		} 
	}		
	return discriminates_taxes;
}
function add_selected_product() {
	if (jQuery('#product_select').val() == null) {
		jQuery('#product_select').focus();
		return false;
	}
		
	count_products = count_products+1;
	
	
	var current_product = product_data[jQuery('#product_select').val()[0]];
	
	var price = getPriceProduct(current_product).formatMoney(sales_object.decimal_numbers, sales_object.decimal,  sales_object.thousand);
	var code = getCodeProduct(current_product);
	var description = getDescriptionProduct(current_product);
	
	var percentage_tax = getPorcentTaxProduct(current_product);
	var tax_with_mask = percentage_tax.formatMoney(2, sales_object.decimal,  sales_object.thousand);
	var discriminates_taxes = getDescriminateTaxes();
	
	var newHTML = '<div id="uc_ID'+count_products+'" class="sortitem" data-identifier="'+count_products+'"><div class="sorthandle"> </div> <div class="uc_column" id=""><label class="code_product" id="label_code_'+count_products+'">'+code+'</label><input name="uc_code[]" type="hidden" id="code_'+count_products+'" value="'+code+'" class="large-text"/> <input name="uc_id[]" type="hidden" id="id_'+count_products+'" value="'+current_product.id+'"/></div><div class="uc_column" id=""><input name="uc_description[]" type="text" id="description_'+count_products+'" value="'+description+'" class="large-text"/></div><div class="uc_column" id=""><input name="uc_quality[]" class="product_quality" id="quality_'+count_products+'" type="text" value="1" class="large-text"/></div><div class="uc_column" id=""><input name="uc_unit_price[]" id="unit_price_'+count_products+'" type="text" value="'+price+'" class="unit_price_products large-text"/></div><div class="uc_column taxes_column" '+((discriminates_taxes == 1)?'':'style="display:none;"')+'><label class="code_product" id="label_tax_product_'+count_products+'">'+tax_with_mask+'%</label><input name="uc_tax[]" type="hidden" id="tax_product_'+count_products+'" value="'+current_product.datacomplete.tax+'" class="product_taxs large-text"/><input name="uc_tax_porcent[]" type="hidden" id="tax_porcent_product_'+count_products+'" value="'+percentage_tax+'" class="product_tax_porcent"/></div><div class="uc_column" id=""><input name="uc_amount[]" id="amount_'+count_products+'" type="text" value="'+price+'" class="products_amounts large-text"/></div><div class="" id="uc_actions"><label title="" data-id="'+count_products+'" class="delete"></label></div></div>';
			
	jQuery('#invoice_products').append(newHTML);
	jQuery('#uc_actions label').click(function() {
		delete_product('#uc_ID'+jQuery(this).attr('data-id'));
		jQuery('#invoice_products').vSort();
		updateSubTotals();
	});
	jQuery('#invoice_products').vSort();
			
	jQuery('.unit_price_products').mask(DefaultMaskNumbers, {reverse: true});
	jQuery('.products_amounts').mask(DefaultMaskNumbers, {reverse: true});
	jQuery('#product_select').val(null);
	jQuery(".product_quality").change(function(){
		if (jQuery(this).val() == '') {
			jQuery(this).val(1); 
		}
		var identifier = this.id.replace('quality_', '');
		var price_standar = converMaskToStandar(jQuery('#unit_price_'+identifier).val(), sales_object);
		var quality = parseInt(jQuery('#quality_'+identifier).val());
		var amount_standar = quality*price_standar;
		var amount = amount_standar.formatMoney(sales_object.decimal_numbers, sales_object.decimal,  sales_object.thousand);
		jQuery('#amount_'+identifier).val(amount);
		updateSubTotals();
	});
	
	jQuery(".unit_price_products").keyup(function(){
		if (jQuery(this).val() == '') {
			jQuery(this).val(0);
		}
		
		var identifier = this.id.replace('unit_price_', '');
		var price_standar = converMaskToStandar(jQuery(this).val(), sales_object);
		var quality = parseInt(jQuery('#quality_'+identifier).val());
		var amount_standar = quality*price_standar;
		var amount = amount_standar.formatMoney(sales_object.decimal_numbers, sales_object.decimal,  sales_object.thousand);
		jQuery('#amount_'+identifier).val(amount);
		updateSubTotals();
		
	});
	jQuery(".products_amounts").change(function(){
		if (jQuery(this).val() == '') {
			jQuery(this).val(0);
		}
		updateSubTotals();
		
	});
	
	
	
	
	updateDiscriminatesTaxes();
	activate_search_products();
	updateSubTotals();
	
	return count_products;
	
}
function updateProductFromIdentifier(identifier) {
	var productId = parseInt(jQuery('#id_'+identifier).val());
	
	var current_product = product_data[productId];
	
	var price_standar = getPriceProduct(current_product);	
	var price = price_standar.formatMoney(sales_object.decimal_numbers, sales_object.decimal,  sales_object.thousand);
	var quality = parseInt(jQuery('#quality_'+identifier).val());
	var amount_standar = quality*price_standar;
	var amount = amount_standar.formatMoney(sales_object.decimal_numbers, sales_object.decimal,  sales_object.thousand);
	
	var code = getCodeProduct(current_product);
	var description = getDescriptionProduct(current_product);
		
	var percentage_tax = getPorcentTaxProduct(current_product);
	var tax_with_mask = percentage_tax.formatMoney(2, sales_object.decimal,  sales_object.thousand);
	var discriminates_taxes = getDescriminateTaxes();
	
	jQuery('#label_code_'+identifier).html(code);
	jQuery('#code_'+identifier).val(code);
	jQuery('#id_'+identifier).val(current_product.id);
	
	jQuery('#description_'+identifier).val(description);
	jQuery('#quality_'+identifier).val(quality);
	jQuery('#unit_price_'+identifier).val(price);
	jQuery('#label_tax_product_'+identifier).html(''+tax_with_mask+'%');
	jQuery('#tax_porcent_product_'+identifier).val(percentage_tax);
	jQuery('#tax_product_'+identifier).val(current_product.datacomplete.tax);
	jQuery('#amount_'+identifier).val(amount);
	

	//var newHTML = '<div class="sorthandle"> </div> <div class="uc_column" id=""><label class="code_product" id="label_code_'+identifier+'">'+code+'</label><input name="uc_code[]" type="hidden" id="code_'+identifier+'" value="'+code+'" class="large-text"/> <input name="uc_id[]" type="hidden" id="id_'+identifier+'" value="'+current_product.id+'"/></div><div class="uc_column" id=""><input name="uc_description[]" type="text" id="description_'+identifier+'" value="'+description+'" class="large-text"/></div><div class="uc_column" id=""><input name="uc_quality[]" class="product_quality" id="quality_'+identifier+'" type="text" value="'+quality+'" class="large-text"/></div><div class="uc_column" id=""><input name="uc_unit_price[]" id="unit_price_'+identifier+'" type="text" value="'+price+'" class="products_unit_prices large-text"/></div><div class="uc_column taxes_column" '+((discriminates_taxes == 1)?'':'style="display:none;"')+'><label class="code_product" id="label_tax_product_'+identifier+'">'+tax_with_mask+'%</label><input name="uc_tax[]" type="hidden" id="tax_product_'+identifier+'" value="'+current_product.datacomplete.tax+'" class="large-text"/></div><div class="uc_column" id=""><input name="uc_amount[]" id="amount_'+identifier+'" type="text" value="'+amount+'" class="products_amounts large-text"/></div><div class="" id="uc_actions"><label title="" data-id="'+identifier+'" class="delete"></label></div>';
	//jQuery('#uc_ID'+identifier).html(newHTML);
	jQuery('#uc_actions label').click(function() {
		delete_product('#uc_ID'+jQuery(this).attr('data-id'));
		jQuery('#invoice_products').vSort();
		updateSubTotals();
	});
	
}
function updateProductsDatails() {
	jQuery('.sortitem').map(function () {
		var identifier = jQuery(this).data('identifier');
		updateProductFromIdentifier(identifier);
		
	})
	jQuery('#invoice_products').vSort();
	jQuery('.products_unit_prices').mask(DefaultMaskNumbers, {reverse: true});
	jQuery('.products_amounts').mask(DefaultMaskNumbers, {reverse: true});
	jQuery('#product_select').val(null);
	
	updateDiscriminatesTaxes();
	activate_search_products();
	updateSubTotals();
}
var sub_total = 0;
var money_taxs = 0;
function updateSubTotals() {
	sub_total = 0;
	jQuery('.products_amounts').map(function() {
		var identifier = this.id.replace('amount_', '');
		var amount_standar = parseFloat(converMaskToStandar(jQuery(this).val(), sales_object));
		sub_total = sub_total+amount_standar;
		
	});
	
	jQuery('#in_sub_total').val(sub_total);
	jQuery('#label_sub_total').html(((sales_object.currency_position == 'before')?getSymbolFromCurrencyId(getCurrentCurrencyId())+' ':'')+''+sub_total.formatMoney(sales_object.decimal_numbers, sales_object.decimal,  sales_object.thousand)+''+((sales_object.currency_position == 'after')?' '+getSymbolFromCurrencyId(getCurrentCurrencyId()):''))
	
	var discount_porcent = parseFloat(converMaskToStandar(jQuery('#invoice_discount').val(), sales_object));
	
	var total_discount = 0;
	if (discount_porcent > 0.00) {
		
		total_discount = (sub_total/100)*discount_porcent;
		jQuery('#in_discount').val(total_discount);
		jQuery('#label_discount').html(((sales_object.currency_position == 'before')?getSymbolFromCurrencyId(getCurrentCurrencyId())+' ':'')+''+total_discount.formatMoney(sales_object.decimal_numbers, sales_object.decimal,  sales_object.thousand)+''+((sales_object.currency_position == 'after')?' '+getSymbolFromCurrencyId(getCurrentCurrencyId()):''))
		jQuery('#discount_total').fadeIn();
		
	} else {
		jQuery('#discount_total').fadeOut();
	}
	var total = sub_total;
	if (total_discount > 0) {
		total = total-total_discount;
	}
	money_taxs = 0;
	if (getDescriminateTaxes() == 1) {
		var current_tax_codition = getCurrentTaxConditions();
		var over_write = true;
		if (current_tax_codition) {
			if (current_tax_codition.overwrite_taxes) {
				money_taxs = (total/100)*parseFloat(current_tax_codition.tax_percentage);
				var html_taxs = '<label id="label_tax_in_0">Tax '+parseFloat(current_tax_codition.tax_percentage).formatMoney(2, sales_object.decimal,  sales_object.thousand)+'%:'+((sales_object.currency_position == 'before')?getSymbolFromCurrencyId(getCurrentCurrencyId())+' ':'')+''+money_taxs.formatMoney(sales_object.decimal_numbers, sales_object.decimal,  sales_object.thousand)+''+((sales_object.currency_position == 'after')?' '+getSymbolFromCurrencyId(getCurrentCurrencyId()):'')+'</label> <input type="hidden" name="taxes_in_products[0]" value="'+money_taxs+'"/>';
				jQuery('#tax_total').html(html_taxs);
				jQuery('#tax_total').fadeIn();
			} else {
				over_write = false;
			}
		} else {
			over_write = false;
		}
		if (!over_write) {
			jQuery('#tax_total').html('');
			jQuery('.product_taxs').map(function() {
				var identifier = this.id.replace('tax_product_', '');
				
				var tax = getTaxFromId(parseInt(jQuery(this).val()));
				var porcent = parseFloat(tax.percentage);
				
				var amount_standar = parseFloat(converMaskToStandar(jQuery('#amount_'+identifier).val(), sales_object));
				var newTaxMoney = ((amount_standar/100)*porcent);
				
				money_taxs = money_taxs+newTaxMoney;
				if(jQuery('#label_tax_in_'+tax.term_id).length ) {
					
					var old_val = parseFloat(jQuery('#taxes_in_products_'+tax.term_id).val());
					newTaxMoney = newTaxMoney+old_val;
					jQuery('#taxes_in_products_'+tax.term_id).val(newTaxMoney);
					jQuery('#label_tax_in_'+tax.term_id).html(''+tax.name+' '+parseFloat(porcent).formatMoney(2, sales_object.decimal,  sales_object.thousand)+'%:'+((sales_object.currency_position == 'before')?getSymbolFromCurrencyId(getCurrentCurrencyId())+' ':'')+''+newTaxMoney.formatMoney(sales_object.decimal_numbers, sales_object.decimal,  sales_object.thousand)+''+((sales_object.currency_position == 'after')?' '+getSymbolFromCurrencyId(getCurrentCurrencyId()):'')+'');
				} else {
					var html_taxs = '<label id="label_tax_in_'+tax.term_id+'">'+tax.name+' '+parseFloat(porcent).formatMoney(2, sales_object.decimal,  sales_object.thousand)+'%:'+((sales_object.currency_position == 'before')?getSymbolFromCurrencyId(getCurrentCurrencyId())+' ':'')+''+newTaxMoney.formatMoney(sales_object.decimal_numbers, sales_object.decimal,  sales_object.thousand)+''+((sales_object.currency_position == 'after')?' '+getSymbolFromCurrencyId(getCurrentCurrencyId()):'')+'</label> <input type="hidden" id="taxes_in_products_'+tax.term_id+'" name="taxes_in_products['+tax.term_id+']" value="'+newTaxMoney+'"/><br/>';
					jQuery('#tax_total').append(html_taxs);		
				}
						
			});
			jQuery('#tax_total').fadeIn();
		} 
	} else {
		jQuery('#tax_total').fadeOut();
		jQuery('#tax_total').html('');
	}
	
	total = total+money_taxs;
	jQuery('#in_total').val(total);
	jQuery('#label_total').html(((sales_object.currency_position == 'before')?getSymbolFromCurrencyId(getCurrentCurrencyId())+' ':'')+''+total.formatMoney(sales_object.decimal_numbers, sales_object.decimal,  sales_object.thousand)+''+((sales_object.currency_position == 'after')?' '+getSymbolFromCurrencyId(getCurrentCurrencyId()):''))
	
}

function activate_search_products() {
	jQuery("#product_select").select2({
	  
	  ajax: {
		url: sales_object.ajax_url,
		dataType: 'json',
		delay: 250,
		data: function (params) {
		  return {
			action: 'get_products',
			s: params.term, // search term
			paged: params.page
		  };
		},
		processResults: function (data, params) {
		  // parse the results into the format expected by Select2
		  // since we are using custom formatting functions we do not need to
		  // alter the remote JSON data, except to indicate that infinite
		  // scrolling can be used
		  params.page = params.page || 1;

		  return {
			results: data.items,
			pagination: {
			  more: (params.page * 30) < data.total_count
			}
		  };
		},
		cache: true
	  },
		escapeMarkup: function (markup) { return markup; }, 
		minimumInputLength: sales_object.characters_to_search,
		templateResult: formatRepo, 
		templateSelection: formatRepoSelection,
		maximumSelectionLength: 1,
		placeholder: sales_object.txt_search_products,
	});
	
}


function getInvoiceTypeFromTaxCondition() {
	var r = 0;
	var tax_conditions = jQuery.parseJSON(sales_object.tax_coditions);
	for (var i = 0; i < tax_conditions.length; i++) {
		if (tax_conditions[i].term_id == jQuery("#client_data_tax_condition").val()) {
			r = tax_conditions[i].invoice_type;
			break;
		}
		
	}
	return parseInt(r);
}
function getCodeProduct(current_product) {
	var retorno = current_product.id;
	if (current_product.datacomplete[sales_object.code_meta_post_key] != undefined)  {
		retorno = current_product.datacomplete[sales_object.code_meta_post_key];
	}
	return retorno;
}
function getDescriptionProduct(current_product) {
	var retorno = current_product.title;
	if (current_product.datacomplete[sales_object.description_meta_post_key] != undefined)  {
		retorno = current_product.datacomplete[sales_object.description_meta_post_key];
	}
	return retorno;
}

function getTaxFromId(id) {
	var r = false;
	var taxes = jQuery.parseJSON(sales_object.taxes);
	for (var i = 0; i < taxes.length; i++) {
		if (taxes[i].term_id == id) {
			r = taxes[i];
			break;
		}
	}
	return r;
	
}

function getPorcentTaxProduct(current_product) {
	var r = 0;
	var taxes = jQuery.parseJSON(sales_object.taxes);
	for (var i = 0; i < taxes.length; i++) {
		if (taxes[i].term_id == current_product.datacomplete.tax) {
			r = taxes[i].percentage;
			break;
		}
	}
	var current_tax_codition = getCurrentTaxConditions();
	if (current_tax_codition) {
		if (current_tax_codition.overwrite_taxes) {
			r = current_tax_codition.tax_percentage;
		}
	}
	return parseFloat(r);
}

function getCurrentTaxConditions() {
	var r = false;
	var tax_conditions = jQuery.parseJSON(sales_object.tax_coditions);
	for (var i = 0; i < tax_conditions.length; i++) {
		if (tax_conditions[i].term_id == jQuery("#client_data_tax_condition").val()) {
			r = tax_conditions[i];
			break;
		}
	}
	return r;
}

function getPriceProduct(current_product) {
	var retorno = current_product.datacomplete.cost;
	var discriminates_taxes = getDescriminateTaxes();
	
	if (current_product.datacomplete.prices[parseInt(jQuery("#client_data_price_scale_id").val())] != undefined)  {
		retorno = current_product.datacomplete.prices[parseInt(jQuery("#client_data_price_scale_id").val())];
	}
	
	var productCurrency = current_product.datacomplete.currency;

	retorno = parseFloat(retorno);
	if (productCurrency != sales_object.default_currency) {
		var rate = getCurrentRateFromCurrencies(current_product.datacomplete.currency);
		retorno = retorno*rate;
		productCurrency = sales_object.default_currency;
	}
	
	if (productCurrency != jQuery("#invoice_currency").val()) {
		var rate = getCurrentRateFromCurrencies(jQuery("#invoice_currency").val());
		retorno = retorno/rate;
	}
	
	if (discriminates_taxes == 0) {
		var porcent_tax = getPorcentTaxProduct(current_product);
		retorno = retorno+((retorno/100)*porcent_tax);
	}
	
	return retorno;
}
function getCurrentRateFromCurrencies(term_id) {
	var r = 1;
	if(jQuery('#invoice_currencies_'+term_id).length ) {
		r = converMaskToStandar(jQuery('#invoice_currencies_'+term_id).val(), sales_object);
	} else {
		alert("Currency no found. This can cause a problem in the transaction.");
	}
	return parseFloat(r);
}

function getRateFromCurrencyId(term_id) {
	var r = 1;
	var currencies = jQuery.parseJSON(sales_object.currencies);
	for (var i = 0; i < currencies.length; i++) {
		if (currencies[i].term_id == term_id) {
			r = currencies[i].rate;
			break;
		}
	}
	return parseFloat(r);
}

function getSymbolFromCurrencyId(term_id) {
	var r = '$';
	var currencies = jQuery.parseJSON(sales_object.currencies);
	for (var i = 0; i < currencies.length; i++) {
		if (currencies[i].term_id == term_id) {
			r = currencies[i].symbol;
			break;
		}
	}
	return r;
}

function getCurrentCurrencyId() {
	if (jQuery("#invoice_currency").val() > 0) {
		return jQuery("#invoice_currency").val();
	}
	return sales_object.default_currency;
}



function delete_product(row_id){
	jQuery(row_id).fadeOut(); 
	jQuery(row_id).remove();
	
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
function padLeft(nr, n, str) {
    return Array(n-String(nr).length+1).join(str||'0')+nr;
}

function formatRepo (repo) {
		if (repo.loading) {
			return repo.text; 
		}
		if (product_data[repo.id] == undefined) {
			product_data[repo.id] = repo;
		}
		
		var markup = "<div class='select2-result-product clearfix'>" +
			"<div class='select2-result-product__avatar'><img src='" + repo.img + "' /></div>" +
			"<div class='select2-result-product__meta'>" +
			"<div class='select2-result-product__title'>" + repo.title + "</div>";

		if (repo.description) {
			markup += "<div class='select2-result-product__description'>" + repo.description + "</div>";
		}
		repo.datacomplete.cost = parseFloat(repo.datacomplete.cost);
		markup += "<div class='select2-result-product__statistics'>" +
			"<div class='select2-result-product__forks'>"+sales_object.txt_cost+": "+((sales_object.currency_position == 'before') ? getSymbolFromCurrencyId(repo.datacomplete.currency) : "")+" " + repo.datacomplete.cost.formatMoney(sales_object.decimal_numbers, sales_object.decimal,  sales_object.thousand) + " "+((sales_object.currency_position == 'after') ? getSymbolFromCurrencyId(repo.datacomplete.currency) : "")+"</div>" +
      
			"</div>" +
			"</div></div>";

    return markup;
}
 function formatRepoSelection (repo) {
    return repo.title || repo.text;
}