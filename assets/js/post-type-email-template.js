jQuery(document).ready(function() {
	 

   // jQuery('#publishing-action').prepend(email_template_object.preview_button);
     
    jQuery(document).on("change", '#post', function(event) {
        jQuery('#preview_button').attr('disabled','disabled');
        jQuery('#preview_button').click(function(e) {
            alert(email_template_object.msg_save_before);
            e.preventDefault();
        });

        jQuery('#reset_button').attr('disabled','disabled');
        jQuery('#reset_button').click(function(e) {
            alert(email_template_object.msg_before_reset);
            e.preventDefault();
        });
    });

    jQuery('#reset_button').click(function(e) {
        if (!confirm(email_template_object.msg_reset)) {
             e.preventDefault();
        }
    });
    jQuery("#assigned").change(function() {
        var data_sended = {
            action: 'get_vars_assigned',
            template_id: jQuery('#post_ID').val(),
            assigned: this.value,
        }
        jQuery('#vars_template_content').html(email_template_object.msg_loading_var);
        jQuery.post(email_template_object.ajax_url, data_sended, function(data) {
            jQuery('#vars_template_content').html(data);

        })
    });
});