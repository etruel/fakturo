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
		if (jQuery('#term_meta_code').val() == '') {
			jQuery('#code_div').addClass("form-invalid");
			jQuery('#term_meta_code').focus();
			jQuery('#term_meta_code').change(function(){
				jQuery('#code_div').removeClass("form-invalid")
			});
			return false; 
		}
		return true;
	}
	
	jQuery('form').submit(function(e){
		if (jQuery('#term_meta_code').val() == '') {
			jQuery('#term_meta_code').focus();
			e.preventDefault();
			return false;
		}
		
	});

});