jQuery(document).ready(function() {
	jQuery('#fakturo_system_options_group_currency').select2();
	jQuery('#fakturo_system_options_group_invoice_type').select2();
	jQuery('#fakturo_system_options_group_price_scale').select2();
	jQuery('#upload_logo_button').click(function() {
		formfield = jQuery('#url').attr('name');
		tb_show('', 'media-upload.php?type=image&TB_iframe=true');
		return false;
	});
   window.send_to_editor = function(html) {
    var doc = document.createElement("html");
    doc.innerHTML = html;
    imgurl = jQuery('img',doc).attr('src');
	
	jQuery('#setting_img_log').attr("src", imgurl);
    jQuery('#url').val(imgurl);
    tb_remove(); 
	}
});