
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
	validateForm = function(b) {
		if(jQuery('#fktr_background_popup_taxonomy').length) {
			return false;
		}
		if (jQuery('#tag-name').val() == '') {

			jQuery('.term-name-wrap').addClass("form-invalid");
			jQuery('#tag-name').focus();
			jQuery('#tag-name').change(function(){
				jQuery('.form-invalid').removeClass("form-invalid")
			});
			return false; 
		}
		if (parseInt(jQuery('#term_meta_invoice_type').val()) < 1) {
			jQuery('#term_meta_invoice_type').select2('open');
			return false; 
		}
		return true;
	}
	jQuery(document).on('popup-tax-validate', function(e){
		validate_form(e);
	});
	jQuery('#edittag').submit(function(e){
		validate_form(e);
	});
	jQuery('#addtag').submit(function(e){
		validate_form(e);
	});
	

});

function validate_form(e) {
	if (parseInt(jQuery('#term_meta_invoice_type').val()) < 1) {
		jQuery('#term_meta_invoice_type').select2('open');
		e.preventDefault();
		return false;
	}
}