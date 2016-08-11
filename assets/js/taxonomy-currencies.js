jQuery(document).ready(function() {
	var decimal_numbers = parseInt(setting_system.decimal_numbers);
	var decimal_ex = '';
	for (var i = 0; i < decimal_numbers; i++) {
		decimal_ex = decimal_ex+'0';
	}
	jQuery('#term_meta_rate').mask("#"+setting_system.thousand+"##0"+setting_system.decimal+decimal_ex, {reverse: true});

});