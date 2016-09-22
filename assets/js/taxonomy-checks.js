
jQuery(document).ready(function() {
	
	jQuery('.term-name-wrap label').html('Serial Number');
	jQuery('.term-name-wrap p').html('Enter a Serial Number.');
	
	
	jQuery('#term_meta_client_id').select2();
	jQuery('#term_meta_provider_id').select2();
	jQuery('#term_meta_bank_id').select2();
	jQuery('#term_meta_currency_id').select2();
	
	jQuery('#term_meta_status').change(function(e){
		if (jQuery(this).val() == 'P') {
			jQuery('#provider_div').fadeIn();
		} else {
			jQuery('#provider_div').fadeOut();
		}
		if (jQuery(this).val() != 'C') {
			jQuery('#date_status_div').fadeIn();
		} else {
			jQuery('#date_status_div').fadeOut();
		}
		
	});
	
	
	var decimal_numbers = parseInt(system_setting.decimal_numbers);
	var decimal_ex = '';
	for (var i = 0; i < decimal_numbers; i++) {
		decimal_ex = decimal_ex+'0';
	}
	DefaultMaskNumbers = "#"+system_setting.thousand+"##0"+system_setting.decimal+decimal_ex;
	jQuery('#term_meta_value').mask(DefaultMaskNumbers, {reverse: true});
	
	system_setting.datetimepicker = jQuery.parseJSON(system_setting.datetimepicker);
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
	jQuery('#term_meta_cashing_date').datetimepicker({
				lang: system_setting.datetimepicker.lang,
				dayOfWeekStart:  system_setting.datetimepicker.firstDay,
				formatTime: system_setting.datetimepicker.timeFormat,
				format: system_setting.datetimepicker.printFormat,
				formatDate: system_setting.datetimepicker.dateFormat,
				timepicker:false,
			});
	jQuery('#term_meta_date_status').datetimepicker({
				lang: system_setting.datetimepicker.lang,
				dayOfWeekStart:  system_setting.datetimepicker.firstDay,
				formatTime: system_setting.datetimepicker.timeFormat,
				format: system_setting.datetimepicker.printFormat,
				formatDate: system_setting.datetimepicker.dateFormat,
				maxDate: system_setting.datetimepicker.dateFormat, 
				timepicker:false,
			});
			
			
			
	validateForm = function(b){
		if (jQuery('#tag-name').val() == '') {
			jQuery('.term-name-wrap').addClass("form-invalid");
			jQuery('#tag-name').focus();
			jQuery('#tag-name').change(function(){
				jQuery('.term-name-wrap').removeClass("form-invalid")
			});
			return false; 
		}
		if (parseInt(jQuery('#term_meta_client_id').val()) < 1) {
			jQuery('#term_meta_client_id').select2('open');
			return false; 
		}
		if (parseInt(jQuery('#term_meta_bank_id').val()) < 1) {
			jQuery('#term_meta_bank_id').select2('open');
			return false; 
		}
		if (parseInt(jQuery('#term_meta_currency_id').val()) < 1) {
			jQuery('#term_meta_currency_id').select2('open');
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
		if (jQuery('#term_meta_value').val() == '') {
			jQuery('#value_div').addClass("form-invalid");
			jQuery('#term_meta_value').focus();
			jQuery('#term_meta_value').change(function(){
				jQuery('#value_div').removeClass("form-invalid")
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
		if (jQuery('#term_meta_cashing_date').val() == '') {
			jQuery('#cashing_date_div').addClass("form-invalid");
			jQuery('#term_meta_cashing_date').focus();
			jQuery('#term_meta_cashing_date').change(function(){
				jQuery('#cashing_date_div').removeClass("form-invalid")
			});
			return false; 
		}
		
		

		return true;
	}
	jQuery('form').submit(function(e){
		if (parseInt(jQuery('#term_meta_client_id').val()) < 1) {
			jQuery('#term_meta_client_id').select2('open');
			e.preventDefault();
			return false; 
		}
		if (parseInt(jQuery('#term_meta_bank_id').val()) < 1) {
			jQuery('#term_meta_bank_id').select2('open');
			e.preventDefault();
			return false; 
		}
		if (parseInt(jQuery('#term_meta_currency_id').val()) < 1) {
			jQuery('#term_meta_currency_id').select2('open');
			e.preventDefault();
			return false; 
		}
		if (jQuery('#term_meta_cost').val() == '') {
			jQuery('#term_meta_cost').focus();
			e.preventDefault();
			return false; 
		}
		if (jQuery('#term_meta_value').val() == '') {
			jQuery('#term_meta_value').focus();
			e.preventDefault();
			return false; 
		}
		if (jQuery('#term_meta_date').val() == '') {
			jQuery('#term_meta_date').focus();
			e.preventDefault();
			return false; 
		}
		if (jQuery('#term_meta_cashing_date').val() == '') {
			jQuery('#term_meta_cashing_date').focus();
			e.preventDefault();
			return false; 
		}	
		
			
	});
});
