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
    function fktrEditorContent() {
        var mce = (typeof tinyMCE !== 'undefined') ? tinyMCE.get('content') : null;
        return (mce && !mce.isHidden()) ? mce.getContent() : jQuery('#content').val();
    }
    function fktrSetEditorContent(html) {
        var mce = (typeof tinyMCE !== 'undefined') ? tinyMCE.get('content') : null;
        if (mce && !mce.isHidden()) {
            mce.setContent(html);
        }
        jQuery('#content').val(html);
    }

    jQuery("#assigned").change(function() {
        var assigned = this.value;
        if (assigned != '-1') {
            jQuery.post(email_template_object.ajax_url, {
                action: 'fktr_default_template',
                kind: fktr_default_template.kind,
                nonce: fktr_default_template.nonce,
                assigned: assigned
            }, function(response) {
                if (!response.success) {
                    return;
                }
                if (jQuery.trim(fktrEditorContent()) == '' || confirm(fktr_default_template.msg_overwrite)) {
                    fktrSetEditorContent(response.data.content);
                    jQuery('#subject').val(response.data.subject);
                } else if (jQuery.trim(jQuery('#subject').val()) == '') {
                    jQuery('#subject').val(response.data.subject);
                }
            });
        }
        var data_sended = {
            fktr_nonce: fktr_ajax_nonce,
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