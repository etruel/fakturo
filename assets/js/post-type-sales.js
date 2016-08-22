var count_products = 0;
var product_data = new Array();
var DefaultMaskNumbers = '';
jQuery(document).ready(function() {
	
	var decimal_numbers = parseInt(sales_object.decimal_numbers);
	var decimal_ex = '';
	for (var i = 0; i < decimal_numbers; i++) {
		decimal_ex = decimal_ex+'0';
	}
	DefaultMaskNumbers = "#"+sales_object.thousand+"##0"+sales_object.decimal+decimal_ex;
	jQuery('.invoice_currencies').mask(DefaultMaskNumbers, {reverse: true});
	jQuery('#invoice_discount').mask("##0"+sales_object.decimal+"00", {reverse: true});
	
	jQuery("#client_id").select2();
	jQuery("#client_data_tax_condition").select2();
	jQuery("#client_data_payment_type").select2();
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
				
			

			});
		}
		
		
		
	});
	jQuery("#client_data_tax_condition").change(function(){
		
		jQuery("#invoice_type").val(getInvoiceTypeFromTaxCondition());
		jQuery("#invoice_type").select2();
	});
	
	
	jQuery('#addmoreuc').click(function(e) {
		
			if (jQuery('#product_select').val() == null) {
				jQuery('#product_select').focus();
				return false;
			}
			
			count_products = count_products+1;
			
			var current_product = product_data[jQuery('#product_select').val()[0]];
			
			
			var newHTML = '<div id="uc_ID'+count_products+'" class="sortitem"><div class="sorthandle"> </div> <div class="uc_column" id=""><input name="uc_code[]" type="text" value="'+current_product.id+'" class="large-text"/></div><div class="uc_column" id=""><input name="uc_description[]" type="text" value="'+current_product.description+'" class="large-text"/></div><div class="uc_column" id=""><input name="uc_quality[]" type="text" value="1" class="large-text"/></div><div class="uc_column" id=""><input name="uc_unit_price[]" type="text" value="'+current_product.datacomplete.cost+'" class="products_unit_prices large-text"/></div><div class="uc_column" id=""><input name="uc_tax[]" type="text" value="'+current_product.datacomplete.tax+'" class="large-text"/></div><div class="uc_column" id=""><input name="uc_amount[]" type="text" value="'+current_product.datacomplete.cost+'" class="products_amounts large-text"/></div><div class="" id="uc_actions"><label title="" data-id="'+count_products+'" class="delete"></label></div></div>';
			
			jQuery('#invoice_products').append(newHTML);
			jQuery('#uc_actions label').click(function() {
				delete_product('#uc_ID'+jQuery(this).attr('data-id'));
				jQuery('#invoice_products').vSort();
			});
			jQuery('#invoice_products').vSort();
			
			jQuery('.products_unit_prices').mask(DefaultMaskNumbers, {reverse: true});
			jQuery('.products_amounts').mask(DefaultMaskNumbers, {reverse: true});
			
			jQuery('#product_select').val(null);
			activate_search_products();
			e.preventDefault();
			
		});
	jQuery('#uc_actions label').click(function() {
		//delete_user_contact('#uc_ID'+jQuery(this).attr('data-id'));
		//jQuery('#user_contacts').vSort();
	});
	
	activate_search_products();
	
  
});

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
function delete_product(row_id){
	jQuery(row_id).fadeOut(); 
	jQuery(row_id).remove();
	jQuery('#msgdrag').html(providers_object.update_provider_contacts).fadeIn();
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

function formatRepo (repo) {
		if (repo.loading) return repo.text; 
		product_data[repo.id] = repo;
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