var DefaultMaskNumbers = '';
var previusValue = 0;
jQuery(document).ready(function($) {


	// This code prevents the URL from being filled with the wp-post-new-reload value
	$('#publish').click(function(e) {
		if ($('#original_post_status').val() === 'auto-draft' && window.history.replaceState) {
			var location;
			location = window.location.href;
			if ((location.split('wp-post-new-reload').length - 1) > 1) {
				location = location.replace('?wp-post-new-reload=true', '');
				location = location.replace('&wp-post-new-reload=true', '');
				window.history.replaceState(null, null, location);
			}
		}
	});
	jQuery('#provider').select2();
	jQuery('#model').select2();
	jQuery('#category').select2();
	jQuery('#product_type').select2();
	jQuery('#tax').select2();
	jQuery('#packaging').select2();
	jQuery('#origin').select2();
	jQuery('#td_internal_code').html(jQuery('#post_ID').val());


	var decimal_numbers = parseInt(products_object.decimal_numbers);
	var decimal_ex = '';
	for (var i = 0; i < decimal_numbers; i++) {
		decimal_ex = decimal_ex + '0';
	}
	DefaultMaskNumbers = "#" + products_object.thousand + "##0" + products_object.decimal + decimal_ex;
	jQuery('#cost').mask(DefaultMaskNumbers, { reverse: true });
	jQuery('.prices').mask(DefaultMaskNumbers, { reverse: true });
	jQuery('.prices_final').mask(DefaultMaskNumbers, { reverse: true });
	jQuery('#currency').select2();

	

	jQuery('#cost').change(function() {
		jQuery('.pricestr').each(function(i, obj) {
			var porcent = parseFloat(jQuery(this).data('porcentage'));
			var cost = parseFloat(converMaskToStandar(jQuery('#cost').val(), products_object));
			var porcentTax = parseFloat(getPorcentOfTaxSelected());
	
			
			var currentPriceVal = parseFloat(jQuery('#prices_' + jQuery(this).data('id')).val()); 
			var currentFinalPriceVal = parseFloat(jQuery('#prices_final_' + jQuery(this).data('id')).val()); 
	
			 newPrice = cost + ((cost / 100) * porcent);
			jQuery('#suggested_' + jQuery(this).data('id')).html(newPrice.formatMoney(products_object.decimal_numbers, products_object.decimal, products_object.thousand));
			
		if (currentPriceVal === 0 || newPrice === 0) {
			newPrice = cost + ((cost / 100) * porcent);
		} else if (newPrice !== currentPriceVal) {
			newPrice = currentPriceVal;
		}
	
			if (newPrice !== currentPriceVal) {
				jQuery('#prices_' + jQuery(this).data('id')).val(newPrice.formatMoney(products_object.decimal_numbers, products_object.decimal, products_object.thousand));
				jQuery('#suggested_' + jQuery(this).data('id')).html(newPrice.formatMoney(products_object.decimal_numbers, products_object.decimal, products_object.thousand));
			}
	
			newPrice = newPrice + ((newPrice / 100) * porcentTax);
	
			
		if (currentFinalPriceVal === 0 || newPrice === 0) {
			newPrice = cost + ((cost / 100) * porcent) + ((newPrice / 100) * porcentTax);
		} else if (newPrice !== currentFinalPriceVal) {
			newPrice = currentFinalPriceVal;
		}
	
			if (newPrice !== currentFinalPriceVal) {
				jQuery('#prices_final_' + jQuery(this).data('id')).val(newPrice.formatMoney(products_object.decimal_numbers, products_object.decimal, products_object.thousand));
			}
		});
	});

	
	
	jQuery('.pricestr').each(function(i, obj) {

		var porcent = parseFloat(jQuery(this).data('porcentage'));
			var cost = parseFloat(converMaskToStandar(jQuery('#cost').val(), products_object));

		previusValue = parseFloat(converMaskToStandar(jQuery('#prices_final_' + jQuery(this).data('id')).val(), products_object));
		var newPrice = cost + ((cost / 100) * porcent);
		jQuery('#suggested_' + jQuery(this).data('id')).html(newPrice.formatMoney(products_object.decimal_numbers, products_object.decimal, products_object.thousand));
	});


	jQuery('#tax').change(function() {
		
		jQuery('.pricestr').each(function(i, obj) {
			var porcent = parseFloat(jQuery(this).data('porcentage'));
			var cost = parseFloat(converMaskToStandar(jQuery('#cost').val(), products_object));
			var porcentTax = parseFloat(getPorcentOfTaxSelected());
	
			var currentPriceVal = parseFloat(converMaskToStandar(jQuery('#prices_' + jQuery(this).data('id')).val(), products_object));
			var currentFinalPriceVal = parseFloat(converMaskToStandar(jQuery('#prices_final_' + jQuery(this).data('id')).val(), products_object));
	
			
			var newPrice = cost + ((cost / 100) * porcent);
			
			if (currentPriceVal === 0 || newPrice === 0) {
				newPrice = cost + ((cost / 100) * porcent);
			} else if (newPrice !== currentPriceVal) {
				newPrice = currentPriceVal;
			}
	
			if (newPrice !== currentPriceVal) {
				jQuery('#prices_' + jQuery(this).data('id')).val(newPrice.formatMoney(products_object.decimal_numbers, products_object.decimal, products_object.thousand));
				jQuery('#suggested_' + jQuery(this).data('id')).html(newPrice.formatMoney(products_object.decimal_numbers, products_object.decimal, products_object.thousand));
			}
	
			if (porcentTax !== 0){
				newPrice = currentFinalPriceVal + ((currentFinalPriceVal / 100) * porcentTax);
			}else {
			newPrice = previusValue - ((previusValue / 100) * porcentTax);
			}
			
			if (currentFinalPriceVal === 0 || newPrice === 0) {
				newPrice = cost + ((cost / 100) * porcent) + ((newPrice / 100) * porcentTax);
			} 
	
			// Actualizar el valor en el input de precio final si es diferente
			if (newPrice !== currentFinalPriceVal) {
				jQuery('#prices_final_' + jQuery(this).data('id')).val(newPrice.formatMoney(products_object.decimal_numbers, products_object.decimal, products_object.thousand));
			}
		});
	});
	
	
});

function getPorcentOfTaxSelected() {
	var r = 0;
	var selected = jQuery('#tax').val();
	var taxes = jQuery.parseJSON(products_object.taxes);
	for (var i = 0; i < taxes.length; i++) {
		if (taxes[i].term_id == selected) {
			r = taxes[i].percentage;
			break;
		}
	}
	return r;
}


function converMaskToStandar(valueMasked, maskObject) {
	if (valueMasked == '') {
		return valueMasked;
	}
	if (valueMasked.indexOf(maskObject.decimal) !== -1) {
		var pieceNumber = valueMasked.split(maskObject.decimal);
		pieceNumber[0] = pieceNumber[0].replace(maskObject.thousand, '');
		valueMasked = pieceNumber.join('.');
	}
	return valueMasked;
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
