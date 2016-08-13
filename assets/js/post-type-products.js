jQuery(document).ready(function() {
	jQuery('#provider').select2();
	jQuery('#model').select2();
	jQuery('#category').select2();
	jQuery('#product_type').select2();
	jQuery('#tax').select2();
	jQuery('#packaging').select2();
	jQuery('#origin').select2();
	
	var decimal_numbers = parseInt(products_object.decimal_numbers);
	var decimal_ex = '';
	for (var i = 0; i < decimal_numbers; i++) {
		decimal_ex = decimal_ex+'0';
	}
	jQuery('#cost').mask("#"+products_object.thousand+"##0"+products_object.decimal+decimal_ex, {reverse: true});
	jQuery('#currency').select2();
	
	
	
});
