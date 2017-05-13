jQuery(document).ready(function() {

	validateForm = function(b){
		if (jQuery('#tag-name').val() == '') {
			jQuery('.term-name-wrap').addClass("form-invalid");
			jQuery('#tag-name').focus();
			jQuery('#tag-name').change(function(){
				jQuery('.term-name-wrap').removeClass("form-invalid")
			});
			return false; 
		}
		if (jQuery('#term_meta_short_name').val() == '') {
			jQuery('#short_name_div').addClass("form-invalid");
			jQuery('#term_meta_short_name').focus();
			jQuery('#term_meta_short_name').change(function(){
				jQuery('#short_name_div').removeClass("form-invalid")
			});
			return false; 
		}

		if (jQuery('#term_meta_symbol').val() == '') {
			jQuery('#symbol_div').addClass("form-invalid");
			jQuery('#term_meta_symbol').focus();
			jQuery('#term_meta_symbol').change(function(){
				jQuery('#symbol_div').removeClass("form-invalid")
			});
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
	if (jQuery('#term_meta_short_name').val() == '') {
		jQuery('#term_meta_short_name').addClass("form-invalid");
		jQuery('#term_meta_short_name').focus();
		e.preventDefault();
		return false;
	}
	jQuery('#term_meta_short_name').removeClass("form-invalid");

	if (jQuery('#term_meta_symbol').val() == '') {
		jQuery('#term_meta_symbol').addClass("form-invalid");
		jQuery('#term_meta_symbol').focus();
		e.preventDefault();
		return false;
	}
	jQuery('#term_meta_symbol').removeClass("form-invalid");
}