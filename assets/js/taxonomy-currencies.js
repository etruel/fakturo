jQuery(document).ready(function() {
	var decimal_numbers = parseInt(setting_system.decimal_numbers);
	var decimal_ex = '';
	for (var i = 0; i < decimal_numbers; i++) {
		decimal_ex = decimal_ex+'0';
	}
	jQuery('#term_meta_rate').mask("#"+setting_system.thousand+"##0"+setting_system.decimal+decimal_ex, {reverse: true});
	
	validateForm = function(b){
		if (jQuery('#tag-name').val() == '') {
			jQuery('.term-name-wrap').addClass("form-invalid");
			jQuery('#tag-name').focus();
			jQuery('#tag-name').change(function(){
				jQuery('.form-invalid').removeClass("form-invalid")
			});
			return false; 
		}
		if (jQuery('#term_meta_symbol').val() == '') {
			alert('Symbol empty');
			jQuery('#symbol_div').addClass("form-invalid");
			jQuery('#term_meta_symbol').focus();
			jQuery('#term_meta_symbol').change(function(){
				jQuery('#symbol_div').removeClass("form-invalid")
			});
			return false; 
		}
		if (validateReference(jQuery('#term_meta_reference').val()) == false) {
			alert('Invalid reference');
			jQuery('#reference_div').addClass("form-invalid");
			jQuery('#term_meta_reference').focus();
			jQuery('#term_meta_reference').change(function(){
				jQuery('#reference_div').removeClass("form-invalid")
			});
			return false; 
		}
		
		return true;
	}
	
	jQuery('#edittag').submit(function(e){
		validate_form();
	});
	jQuery('#addtag').submit(function(e){
		validate_form();
	});
	
});
function validate_form() {
	if (jQuery('#term_meta_symbol').val() == '') {
			alert('Symbol empty');
			jQuery('#term_meta_symbol').focus();
			e.preventDefault();
			return false;
		}
		
		if (validateReference(jQuery('#term_meta_reference').val()) == false) {
			alert('Invalid reference');
			jQuery('#term_meta_reference').focus();
			e.preventDefault();
			return false;
		}
}

function validateReference(textval) {
	if (textval == '') {
		return true;
	}
    var urlregex = /^(https?|ftp):\/\/([a-zA-Z0-9.-]+(:[a-zA-Z0-9.&%$-]+)*@)*((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9][0-9]?)(\.(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]?[0-9])){3}|([a-zA-Z0-9-]+\.)*[a-zA-Z0-9-]+\.(com|edu|gov|int|mil|net|org|biz|arpa|info|name|pro|aero|coop|museum|[a-zA-Z]{2}))(:[0-9]+)*(\/($|[a-zA-Z0-9.,?'\\+&%$#=~_-]+))*$/;
    return urlregex.test(textval);
}