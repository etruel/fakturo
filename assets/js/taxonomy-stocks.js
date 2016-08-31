
jQuery(document).ready(function() {
	
	var decimal_numbers = parseInt(system_setting.decimal_numbers);
	var decimal_ex = '';
	for (var i = 0; i < decimal_numbers; i++) {
		decimal_ex = decimal_ex+'0';
	}
	jQuery('#term_meta_cost').mask("#"+system_setting.thousand+"##0"+system_setting.decimal+decimal_ex, {reverse: true});
	
	
	
	jQuery('#term_meta_product').select2();
	jQuery('#term_meta_location').select2();
	system_setting.datetimepicker = jQuery.parseJSON(system_setting.datetimepicker)
	jQuery.datetimepicker.setLocale(system_setting.datetimepicker.lang);
	
	jQuery('#term_meta_date').datetimepicker({
				lang: system_setting.datetimepicker.lang,
				dayOfWeekStart:  system_setting.datetimepicker.firstDay,
				formatTime: system_setting.datetimepicker.timeFormat,
				format: system_setting.datetimepicker.printFormat,
				formatDate: system_setting.datetimepicker.dateFormat,
				maxDate: system_setting.datetimepicker.dateFormat, 
				timepicker:false,
			});
	
	validateForm = function(b){
		
		if (parseInt(jQuery('#term_meta_product').val()) < 1) {
			jQuery('#term_meta_product').select2('open');
			return false; 
		}
		if (parseInt(jQuery('#term_meta_location').val()) < 1) {
			jQuery('#term_meta_location').select2('open');
			return false; 
		}
		if (jQuery('#term_meta_quality').val() == '') {
			jQuery('#quality_div').addClass("form-invalid");
			jQuery('#term_meta_quality').focus();
			jQuery('#term_meta_quality').change(function(){
				jQuery('#quality_div').removeClass("form-invalid")
			});
			return false; 
		}
		if (jQuery('#term_meta_cost').val() == '') {
			jQuery('#cost_div').addClass("form-invalid");
			jQuery('#term_meta_cost').focus();
			jQuery('#term_meta_cost').change(function(){
				jQuery('#cost_div').removeClass("form-invalid")
			});
			return false; 
		}
		if (jQuery('#term_meta_date').val() == '') {
			jQuery('#date_div').addClass("form-invalid");
			jQuery('#term_meta_date').focus();
			jQuery('#term_meta_date').change(function(){
				jQuery('#date_div').removeClass("form-invalid")
			});
			return false; 
		}
		if (jQuery('#tag-name').val() == '') {
			jQuery('#tag-name').val('fktr-stock');
		}

		return true;
	}
	jQuery('form').submit(function(e){
		if (parseInt(jQuery('#term_meta_invoice_type').val()) < 1) {
			jQuery('#term_meta_invoice_type').select2('open');
			e.preventDefault();
			return false;
		}
		
	});
	

});
