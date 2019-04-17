jQuery(document).ready(function() {
	 config = {
        lineNumbers: true,
        mode: "htmlmixed",
        theme: 'monokai',
        indentWithTabs: false,
        htmlMode: true,
        readOnly: false,
    };
    
    editor = CodeMirror.fromTextArea(document.getElementById("content"), config);
    editor.setSize(null,700);
    editor.refresh();

    //jQuery('#publishing-action').prepend(print_template_object.pdf_button);
   // jQuery('#publishing-action').prepend(print_template_object.preview_button);
     
    jQuery(document).on("change", '#post', function(event) {
        jQuery('#preview_button').attr('disabled','disabled');
        jQuery('#preview_button').click(function(e) {
            alert(print_template_object.msg_save_before);
            e.preventDefault();
        });
        jQuery('#pdf_button').attr('disabled','disabled');
        jQuery('#pdf_button').click(function(e) {
            alert(print_template_object.msg_save_before);
            e.preventDefault();
        });
        jQuery('#reset_button').attr('disabled','disabled');
        jQuery('#reset_button').click(function(e) {
            alert(print_template_object.msg_before_reset);
            e.preventDefault();
        });


    });
    jQuery('#reset_button').click(function(e) {
        if (!confirm(print_template_object.msg_reset)) {
             e.preventDefault();
        }
    });
    

    jQuery("#assigned").change(function() {
        var data_sended = {
            action: 'get_vars_assigned_print',
            template_id: jQuery('#post_ID').val(),
            assigned: this.value,
        }
        jQuery('#vars_template_content').html(print_template_object.msg_loading_var);
        jQuery.post(print_template_object.ajax_url, data_sended, function(data) {
            jQuery('#vars_template_content').html(data);

        })
    });



});