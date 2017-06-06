var count_products = 0;


var product_data = new Array();
var DefaultMaskNumbers = '';
jQuery(document).ready(function() {
		


	jQuery('#title-prompt-text').remove();
	jQuery("#title").attr("readonly","readonly");

	
	if (sales_object.post_status == 'publish') {
		return false;
	}
	
	loadProductData();
	updateKeyPress();
	var numbers_ex = '';
	for (var i = 0; i < sales_object.digits_invoice_number; i++) {
		numbers_ex = numbers_ex+'0';
	}
	jQuery('#invoice_number').val(padLeft(jQuery('#invoice_number').val(), sales_object.digits_invoice_number));
	jQuery('#invoice_number').mask(numbers_ex, {reverse: true});
	jQuery('#invoice_number').keyup(function(e){
		jQuery(this).val(padLeft(jQuery('#invoice_number').val(), sales_object.digits_invoice_number))
	});
	jQuery('.product_quality').mask('#0', {reverse: true});

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
				updateSuggestInvoiceNumber();
			});
		}
		
		
		
	});
	jQuery("#client_data_tax_condition").change(function(){
		
		jQuery("#invoice_type").val(getInvoiceTypeFromTaxCondition());
		jQuery("#invoice_type").select2();
		updateProductsDatails();
		updateSuggestInvoiceNumber();
	});
	jQuery("#invoice_type").change(function(){
		updateProductsDatails();
		updateStockMessages();
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
	updateStockMessages();
	jQuery('.delete').click(function(e){
		window.setTimeout (function(){ 
						   updateStockMessages();
						},300);
		
	});
	
	jQuery('#product_select').change(function(e){
		
		var identifier = add_selected_product();
		jQuery('#quality_'+identifier).focus(function(e) {
						var save_this = jQuery(this);
						window.setTimeout (function(){ 
						   save_this.select(); 
						},100);
					});
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
		updateSuggestInvoiceNumber();
		updateTitle();
	});
	jQuery('#sale_point').change(function(){
		updateSuggestInvoiceNumber();
		updateTitle();
	});
	activate_search_products();
	updateProductsDatailsOnLoad();
	
	jQuery('#post').submit(function(e) {  
   			
			if (jQuery('#invoice_type').val() == null || jQuery('#invoice_type').val() <= 0) {
				jQuery('#invoice_type').select2('open');
				e.preventDefault();
				return false;
			}
			if (jQuery('#client_id').val() == null || jQuery('#client_id').val() <= 0) {
				jQuery('#client_id').select2('open');
				e.preventDefault();
				return false;
			}
			
			
			addNoticeMessage('<img width="12" src="'+sales_object.url_loading_image+'" class="mt2"/> '+sales_object.txt_loading+'...', 'updated');
			
			
   			jQuery.ajaxSetup({async:false});
   			status = 'error';
			message = '';
			selector = '';
			functionEx = '';
			var data = {
   				action: 'validate_sale',
				inputs: jQuery("#post").serialize()
   			};
   			jQuery.post(sales_object.ajax_url, data, function(data){  //si todo ok devuelve 1 sino el error
				status = jQuery(data).find('response_data').text();
				message = jQuery(data).find('supplemental message').text();
				selector = jQuery(data).find('supplemental inputSelector').text();
				functionEx = jQuery(data).find('supplemental function').text();
   				if(status == 'success'){
					
   				} else {
					addNoticeMessage(message, 'error');
					if (selector != '') {
						jQuery(selector).focus();
					}
					
					if (typeof window[functionEx] === 'function' && functionEx!=''){
						formok = window[functionEx]();
						e.preventDefault();
					}
   				}
   			});
			if(status == 'error') {
				e.preventDefault();
   				return false; 
   			} else {
   				return true; 
   			}
		});

		/*CURRENCIES MORE VIEW*/
		jQuery("#fakturo-currencies-box").append('<center><input class="fak-seemore"  type="button" value="See More" ></center>');
		var $fak_seemore = 4;
		var $fak_morelimit = parseInt(jQuery("#fakturo-currencies-box").find('div.inside table tbody tr').length);
		jQuery(document).on('click','.fak-seemore',function(){
			$fak_seemore+=4;
			listCurrencieshiden($fak_seemore);
			if($fak_seemore>=$fak_morelimit){
				jQuery(this).val('View less').addClass('fak-viewless').removeClass('fak-seemore');
			}
		});
		jQuery(document).on('click','.fak-viewless',function(){
			$fak_seemore = 4;
			listCurrencieshiden($fak_seemore);
			jQuery(this).val('See More').addClass('fak-seemore').removeClass('fak-viewless');
		});
		listCurrencieshiden($fak_seemore);

});
function listCurrencieshiden(lim){
	jQuery("#fakturo-currencies-box").find('div.inside table tbody tr').each(function(i){
		/*hidden element*/
		if(i>=lim){jQuery(this).hide();}else{jQuery(this).show(500);}
	});

}


function addNoticeMessage(msg, classN) {
	if (jQuery('#fieldNotice').length) {
		jQuery('#fieldNotice').html('<p>'+msg+'</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Descartar este aviso.</span></button>');
		jQuery('#fieldNotice').attr('class', ''+classN+' fade he20 notice is-dismissible');
		jQuery('#fieldNotice').fadeIn();
	} else {
		jQuery('#poststuff').prepend('<div id="fieldNotice" class="'+classN+' fade he20 notice is-dismissible"><p>'+msg+'</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Descartar este aviso.</span></button></div>');
	}
	jQuery('.notice-dismiss').click(function(){
		jQuery(this).parent().fadeOut();
	});
}

function addStockMessageLila(productId, min) {
	
	if (jQuery('#error_lila_stock_'+productId).length == 0) {
		jQuery('#errors_stocks').prepend('<div id="error_lila_stock_'+productId+'" class="error_stock_lila">'+sales_object.txt_product_alert_min+' ('+productId+' - '+sales_object.txt_min+': '+min+') </div>');
	}
	jQuery('#errors_stocks').fadeIn();
	
}
function addStockMessageRed(productId, location_id, max) {
	
	if (jQuery('#error_stock_'+productId+'_'+location_id).length == 0) {
		if (location_id == 0) {
			jQuery('#errors_stocks').prepend('<div id="error_stock_'+productId+'_'+location_id+'" class="error_stock_red">'+sales_object.txt_exc_stock+' ('+productId+' - '+sales_object.txt_max+': '+max+') </div>');
		} else {
			productLocation = getLocation(location_id);
			jQuery('#errors_stocks').prepend('<div id="error_stock_'+productId+'_'+location_id+'" class="error_stock_red">'+sales_object.txt_no_stock+' ( '+productId+' / '+productLocation.name+' - '+sales_object.txt_max+': '+max+') </div>');
		}
	}
	jQuery('#errors_stocks').fadeIn();
	
}
function updateStockMessages() {
	jQuery('#errors_stocks').html('');
	var invoice_type = getCurrentInvoiceType();
	if (invoice_type) {
		if (invoice_type.sum == 0) {
			jQuery('.product_stock_input').map(function() {
				
				if (jQuery(this).val() != '') {
					var pieces_data = this.id.replace('product_stock_', '').split('_');
				
					var identifier = pieces_data[0];
					var location_id = pieces_data[1];
					var productId = jQuery('#id_'+identifier).val();
					var current_product = product_data[productId];
					var current_stock_location = getCurrentProductStock(productId, location_id)
					if (current_stock_location < 0) {
						addStockMessageRed(productId, location_id, getTotalStockDefault(productId, location_id));
					}
					
					var current_stock_location = getCurrentProductStock(productId, 0)
					if (current_stock_location < 0) {
						addStockMessageRed(productId, 0, getTotalStockDefault(productId, 0));
					}
					
					if (current_product.datacomplete.min_alert) {
						if (current_product.datacomplete.min != '' && current_product.datacomplete.min>=0) {
							totalProductStock = getTotalStockDefault(productId, 0);
							if (totalProductStock <= current_product.datacomplete.min) {
								addStockMessageLila(productId, current_product.datacomplete.min);
							}
						}
					}
				}
				
				
			});
		}
	}
	if (jQuery('#errors_stocks').html() == '') {
		jQuery('#errors_stocks').fadeOut();
	} else {
		jQuery('#errors_stocks').fadeIn();
	}
		
	
}
function getTotalStockDefault(productId, location_id) {
	var current_product = product_data[productId];
	var totalDefault = 0;
	for (var location_id_product in current_product.datacomplete.stocks) {
		productLocation = getLocation(location_id_product);
		if (productLocation) {
			if (location_id == 0) {
				totalDefault = totalDefault+current_product.datacomplete.stocks[location_id_product];
			} else if (location_id == location_id_product) {
				totalDefault = totalDefault+current_product.datacomplete.stocks[location_id_product];
			}
		}
	}
	return totalDefault;
}
function productHaveStockLocation(identifier) {
	var retorno = false;
	var locations = sales_object.locations;
	for (var i = 0; i < locations.length; i++) {
		var valStock = jQuery('#product_stock_'+identifier+'_'+locations[i].term_id).val();
		if (valStock != '') {
			retorno = true;
			break;
		}
	}
	return retorno;
}

function getCurrentProductStock(productId, location_id) {
	var current_product = product_data[productId];
	var total = 0;
	jQuery('.product_stock_input').map(function() {
		var pieces_data = this.id.replace('product_stock_', '').split('_');
		var identifier = pieces_data[0];
		var productIdMap = jQuery('#id_'+identifier).val();
		if (productIdMap == productId) {
			var location_stock = pieces_data[1];
			if (location_id == 0) {
				var val = 0;
				if (jQuery(this).val() == '') {
					val = 0;
				} else {
					val = jQuery(this).val();
				}
				total = total+parseInt(val);
			} else if (location_id == location_stock) {
				var val = 0;
				if (jQuery(this).val() == '') {
					val = 0;
				} else {
					val = jQuery(this).val();
				}
				total = total+parseInt(val);
			}
		}
		
	});
	var totalDefault = getTotalStockDefault(productId, location_id);
	
	total = totalDefault-total;
	return total;
}

function updateStockProduct(identifier) {
	var productId = jQuery('#id_'+identifier).val();
	var current_product = product_data[productId];
	var invoice_type = getCurrentInvoiceType();
	
	
	jQuery('.product_stock_input_popup').map(function() {
		var location_id = this.id.replace('product_stock_input_popup_', '');
		jQuery('#product_stock_'+identifier+'_'+location_id).val(jQuery(this).val());
		
		
	});
	updateStockMessages();
	
}
function updatePopUpStockRemaining() {
	total_remaining = 0;
	jQuery('.product_stock_input_popup').map(function() {
		var count = 0;
		if (jQuery(this).val() == '') {
			count = 0;
		} else {
			count = parseInt(jQuery(this).val());
		}
		total_remaining = total_remaining+count;
		
	});
	var remaining = parseInt(jQuery('#stock_quantity_total').html())-total_remaining;
	if (remaining == 0) {
		jQuery('#stock_remaining').removeClass('remaining_stock_red');
		jQuery('#stock_remaining').addClass('remaining_stock_green');
	} else {
		jQuery('#stock_remaining').removeClass('remaining_stock_green');
		jQuery('#stock_remaining').addClass('remaining_stock_red');
	}
		
	jQuery('#stock_remaining').html(remaining);
	
}
var first_time_focus = 0;
var total_remaining = 0;
function openPopPupStockProduct(identifier) {
	var quantity_total = jQuery('#quality_'+identifier).val();
	var productId = jQuery('#id_'+identifier).val();
	var current_product = product_data[productId];
	var productLocation = false; 
	var length_stocks_locations = 0;
	var first_location = 0;
	var stocksHtml = '<table class="stock_table">';
	for (var location_id in current_product.datacomplete.stocks) {
		productLocation = getLocation(location_id);
		if (productLocation) {
			if (first_location == 0) {
				first_location = location_id;
			}
			var valStock = jQuery('#product_stock_'+identifier+'_'+location_id).val();
			var valReal = 0;
			if (valStock !='') {
				valReal = parseInt(valStock); 
			}
			length_stocks_locations++;
			stocksHtml += '<tr><td class="stock_td_name">'+productLocation.name+' (max:'+(getCurrentProductStock(current_product.id, location_id)+valReal)+'): </td><td class="stock_td_input"><input type="text" name="product_stock_location_popup[]" value="'+valStock+'" id="product_stock_input_popup_'+location_id+'" class="product_stock_input_popup"/> </td></tr>';
		}
	}
	stocksHtml += '</table>';
	var invoice_type = getCurrentInvoiceType();
	if (invoice_type) {
		if (invoice_type.sum == 1) {
			length_stocks_locations = 0;
			first_location = 0;
			var stocksHtml = '<table class="stock_table">';
			var locations = sales_object.locations;
			for (var i = 0; i < locations.length; i++) {
				if (first_location == 0) {
					first_location = locations[i].term_id;
				}
				var valStock = jQuery('#product_stock_'+identifier+'_'+locations[i].term_id).val();
				length_stocks_locations++;
				stocksHtml += '<tr><td class="stock_td_name">'+locations[i].name+' (max:0): </td><td class="stock_td_input"><input type="text" name="product_stock_location_popup[]" value="'+valStock+'" id="product_stock_input_popup_'+locations[i].term_id+'" class="product_stock_input_popup"/> </td></tr>';
			}
			stocksHtml += '</table>';
			
		}
	}
	if (length_stocks_locations<=1) {
		if (length_stocks_locations == 1) {
			jQuery('#product_stock_'+identifier+'_'+first_location).val(quantity_total);
			updateStockMessages();
			jQuery('#unit_price_'+identifier).focus(function(e) {
						var save_this = jQuery(this);
						window.setTimeout (function(){ 
						   save_this.select(); 
						},100);
					});
			jQuery('#unit_price_'+identifier).focus();
			return false;
		} else {
			length_stocks_locations = 0;
			first_location = 0;
			var stocksHtml = '<table class="stock_table">';
			var locations = sales_object.locations;
			for (var i = 0; i < locations.length; i++) {
				if (first_location == 0) {
					first_location = locations[i].term_id;
				}
				var valStock = jQuery('#product_stock_'+identifier+'_'+locations[i].term_id).val();
				length_stocks_locations++;
				stocksHtml += '<tr><td class="stock_td_name">'+locations[i].name+' (max:0): </td><td class="stock_td_input"><input type="text" name="product_stock_location_popup[]" value="'+valStock+'" id="product_stock_input_popup_'+locations[i].term_id+'" class="product_stock_input_popup"/> </td></tr>';
			}
			stocksHtml += '</table>';
		}
		
	}
	
	var newHtml = '<div id="content_popup_stock"><div style="text-align:center">'+sales_object.txt_total_quantity+': <strong id="stock_quantity_total">'+quantity_total+'</strong></div><div style="text-align:center">'+sales_object.txt_remaining+': <strong id="stock_remaining" class="remaining_stock_red">'+quantity_total+'</strong></div>'+stocksHtml+'</div><div id="buttons_stock_popup"><a href="#" class="button" id="btn_cancel_stock_popup" style="margin:3px;">Cancelar</a></div>';
	jQuery('#product_stock_popup').html(newHtml);
	
	
	jQuery('#product_stock_popup').fadeIn();
	jQuery('#popup_stock_background').fadeIn();
	jQuery('#product_stock_input_popup_'+first_location).focus(function(e) {
						var save_this = jQuery(this);
						window.setTimeout (function(){ 
						   save_this.select(); 
						},100);
					});
	jQuery('#product_stock_input_popup_'+first_location).focus();
	jQuery('#btn_cancel_stock_popup').click(function(e){
		first_time_focus = 0;
		total_remaining = 0;
		jQuery('#product_stock_popup').fadeOut();
		jQuery('#popup_stock_background').fadeOut();
		jQuery('#quality_'+identifier).focus();
		
		e.preventDefault();
		return false;
	});
	jQuery('form input').on('keypress', function(e) {
		if (e.which == 13) {
			e.preventDefault();
			return false;
		}
	});
	jQuery('.product_stock_input_popup').mask('#0', {reverse: true});
	jQuery('.product_stock_input_popup').keyup(function(e){
		
		if (e.which == 13) {
			if (parseInt(jQuery('#stock_remaining').html()) == 0) {
				first_time_focus = 0;
				total_remaining = 0;
				jQuery('#product_stock_popup').fadeOut();
				jQuery('#popup_stock_background').fadeOut();
				jQuery('#unit_price_'+identifier).focus(function(e) {
						var save_this = jQuery(this);
						window.setTimeout (function(){ 
						   save_this.select(); 
						},100);
					});
				jQuery('#unit_price_'+identifier).focus();
				updateStockProduct(identifier);
				e.preventDefault();
				return false;
			}
			if (first_time_focus == 1) {
				jQuery(this).closest('tr').next().find('.product_stock_input_popup').focus(function(e) {
						var save_this = jQuery(this);
						window.setTimeout (function(){ 
						   save_this.select(); 
						},100);
					});
				jQuery(this).closest('tr').next().find('.product_stock_input_popup').focus();
			}
			first_time_focus = 1;
			e.preventDefault();
			return false;
		}
		updatePopUpStockRemaining();
		
	});
	setTimeout(function(){ updatePopUpStockRemaining(); }, 500);
	
}
function getLocation(location_id) {
	var r = false;
	var locations = sales_object.locations;
	for (var i = 0; i < locations.length; i++) {
		if (locations[i].term_id == location_id) {
			r = locations[i];
			break;
		}
	}
	return r;
}

function updateSuggestInvoiceNumber() {
	var sale_point = getCurrentSalePoint();
	var invoice_type = getCurrentInvoiceType();
	var data = {
				action: 'get_suggest_invoice_number',
				sale_point: sale_point.term_id,
				invoice_type: invoice_type.term_id,
			}
	
	jQuery.post(sales_object.ajax_url, data, function(data) {
		jQuery('#invoice_number').val(padLeft(data, sales_object.digits_invoice_number));
		updateTitle();
	});
	
}
var addNewDataToTitle = function(val) {
	return val;
}
function updateTitle() {
	
	var newVal = '';
	var sale_point = getCurrentSalePoint();
	var invoice_type = getCurrentInvoiceType();
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
				if (sales_object.use_stock_product == 1) {
					openPopPupStockProduct(identifier);
				} else {
					jQuery('#unit_price_'+identifier).focus(function(e) {
						var save_this = jQuery(this);
						window.setTimeout (function(){ 
						   save_this.select(); 
						},100);
					});
					jQuery('#unit_price_'+identifier).focus();
				}
				
			}
			jQuery(this).data('focused', true);
			e.preventDefault();
			return false;
		}
	});
	jQuery('.product_quality').focusout(function(e) {

		if (!jQuery('#product_stock_popup').is(':visible') && jQuery('#product_stock_popup').is(':hidden') && sales_object.use_stock_product == 1) {
			var identifier = this.id.replace('quality_', '');
			if (!productHaveStockLocation(identifier)) {
				jQuery(this).focus();
			}
		} 
 
	});
	
	
	jQuery('.unit_price_products').on('keypress', function(e) {
		if (e.which == 13) {
			var identifier = this.id.replace('unit_price_', '');
			jQuery('#amount_'+identifier).focus(function(e) {
				var save_this = jQuery(this);
				window.setTimeout (function(){ 
				   save_this.select(); 
				},100);
			});
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
	
	var stocksHtml = '';
	/*
	for (var location_id in current_product.datacomplete.stocks) {
		productLocation = getLocation(location_id);
		if (productLocation) {
			stocksHtml += '<input type="hidden" name="product_stock_location['+current_product.id+']['+location_id+'][]" value="" id="product_stock_'+count_products+'_'+location_id+'" class="product_stock_input"/>';
		}
	}
	*/
	var locations = sales_object.locations;
	for (var i = 0; i < locations.length; i++) {
		stocksHtml += '<input type="hidden" name="product_stock_location['+current_product.id+']['+locations[i].term_id+'][]" value="" id="product_stock_'+count_products+'_'+locations[i].term_id+'" class="product_stock_input"/>';
		
	}
	
	var price = getPriceProduct(current_product).formatMoney(sales_object.decimal_numbers, sales_object.decimal,  sales_object.thousand);
	var code = getCodeProduct(current_product);
	var description = getDescriptionProduct(current_product);
	
	var percentage_tax = getPorcentTaxProduct(current_product);
	var tax_with_mask = percentage_tax.formatMoney(2, sales_object.decimal,  sales_object.thousand);
	var discriminates_taxes = getDescriminateTaxes();
	
	var newHTML = '<div id="uc_ID'+count_products+'" class="sortitem" data-identifier="'+count_products+'"><div class="sorthandle"> </div> <div class="uc_column" id=""><label class="code_product" id="label_code_'+count_products+'">'+code+'</label>'+stocksHtml+'<input name="uc_code[]" type="hidden" id="code_'+count_products+'" value="'+code+'" class="large-text"/> <input name="uc_id[]" type="hidden" id="id_'+count_products+'" value="'+current_product.id+'"/></div><div class="uc_column" id=""><input name="uc_description[]" type="text" id="description_'+count_products+'" value="'+description+'" class="large-text"/></div><div class="uc_column" id=""><input name="uc_quality[]" class="product_quality" id="quality_'+count_products+'" type="text" value="1" class="large-text"/></div><div class="uc_column" id=""><input name="uc_unit_price[]" id="unit_price_'+count_products+'" type="text" value="'+price+'" class="unit_price_products large-text"/></div><div class="uc_column taxes_column" '+((discriminates_taxes == 1)?'':'style="display:none;"')+'><label class="code_product" id="label_tax_product_'+count_products+'">'+tax_with_mask+'%</label><input name="uc_tax[]" type="hidden" id="tax_product_'+count_products+'" value="'+current_product.datacomplete.tax+'" class="product_taxs large-text"/><input name="uc_tax_porcent[]" type="hidden" id="tax_porcent_product_'+count_products+'" value="'+percentage_tax+'" class="product_tax_porcent"/></div><div class="uc_column" id=""><input name="uc_amount[]" id="amount_'+count_products+'" type="text" value="'+price+'" class="products_amounts large-text"/></div><div class="" id="uc_actions"><label title="" data-id="'+count_products+'" class="delete"></label></div></div>';
			
	jQuery('#invoice_products').append(newHTML);
	jQuery('#uc_actions label').click(function() {
		delete_product('#uc_ID'+jQuery(this).attr('data-id'));
		jQuery('#invoice_products').vSort();
		updateSubTotals();
	});
	jQuery('#invoice_products').vSort();
	
	jQuery('.product_quality').mask('#0', {reverse: true});		
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
	var invoice_type = getCurrentInvoiceType();
	if (invoice_type) {
		if (invoice_type.sum == 0) {
			if (current_product.datacomplete.min_alert) {
				if (current_product.datacomplete.min != '' && current_product.datacomplete.min>=0) {
					var totalProductStock = 0;
					for (var location_id in current_product.datacomplete.stocks) {
						productLocation = getLocation(location_id);
						if (productLocation) {
							totalProductStock = totalProductStock+current_product.datacomplete.stocks[location_id];
						}
					}
					if (totalProductStock <= current_product.datacomplete.min) {
						addStockMessageLila(current_product.id, current_product.datacomplete.min);
					}
				}
			}
		}
	}
	jQuery('.delete').click(function(e){
		window.setTimeout (function(){ 
						   updateStockMessages();
						},300);
		
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
function updateProductFromIdentifierOnLoad(identifier) {
	var productId = parseInt(jQuery('#id_'+identifier).val());
	
	var current_product = product_data[productId];
	
	var price_standar = parseFloat(converMaskToStandar(jQuery('#unit_price_'+identifier).val(), sales_object));

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
	
	//jQuery('#description_'+identifier).val(description);
	jQuery('#quality_'+identifier).val(quality);
	jQuery('#unit_price_'+identifier).val(price);
	jQuery('#label_tax_product_'+identifier).html(''+tax_with_mask+'%');
	jQuery('#tax_porcent_product_'+identifier).val(percentage_tax);
	jQuery('#tax_product_'+identifier).val(current_product.datacomplete.tax);
	jQuery('#amount_'+identifier).val(amount);



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

	
}
function updateProductsDatailsOnLoad() {
	jQuery('.sortitem').map(function () {
		var identifier = jQuery(this).data('identifier');
		updateProductFromIdentifierOnLoad(identifier);
		
	})
	jQuery('#uc_actions label').click(function() {
		delete_product('#uc_ID'+jQuery(this).attr('data-id'));
		jQuery('#invoice_products').vSort();
		updateSubTotals();
	});
	
	jQuery('#invoice_products').vSort();
	jQuery('.unit_price_products').mask(DefaultMaskNumbers, {reverse: true});
	jQuery('.products_amounts').mask(DefaultMaskNumbers, {reverse: true});
	jQuery('#product_select').val(null);
	
	updateDiscriminatesTaxes();
	activate_search_products();
	updateSubTotals();
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