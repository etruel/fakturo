jQuery(document).ready(function() {
	jQuery('#provider').select2();
	jQuery('#model').select2();
	jQuery('#category').select2();
	jQuery('#product_type').select2();
	jQuery('#tax').select2();
	
	
	jQuery('#cost').mask("#"+products_object.thousand+"##0"+products_object.decimal+"00", {reverse: true});
	jQuery('#currency').select2();
	
	
	
});
