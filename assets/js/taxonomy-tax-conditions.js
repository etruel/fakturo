jQuery(document).ready(function() {
	jQuery('#term_meta_invoice_type').select2();
	jQuery('#term_meta_tax_percentage').mask('##0'+system_setting.decimal+'00', {reverse: true});
	jQuery('#term_meta_overwrite_taxes').change(function() {
		
		if (jQuery('#term_meta_overwrite_taxes').is(':checked')) {
			jQuery('#tax_percentage_div').fadeIn();
		} else {
			jQuery('#tax_percentage_div').fadeOut();
		}
	});
	
});