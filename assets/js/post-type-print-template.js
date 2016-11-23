jQuery(document).ready(function() {
	 config = {
        lineNumbers: true,
        mode: "htmlmixed",
        theme: 'monokai',
        indentWithTabs: false,
        htmlMode: true,
        readOnly: false,
    };
    var widthEditor = jQuery('#fakturo-invoice-data-box .inside').width()-(jQuery('#fakturo-invoice-data-box .inside').innerWidth()-jQuery('#fakturo-invoice-data-box .inside').width());
    console.log(widthEditor);
    editor = CodeMirror.fromTextArea(document.getElementById("content"), config);
    editor.setSize(widthEditor, 400);
    editor.refresh();


    jQuery('#publishing-action').prepend(print_template_object.preview_button);
    jQuery(document).on("change", '#post', function(event) {
        jQuery('#preview_button').attr('disabled','disabled');
        jQuery('#preview_button').click(function(e) {
            alert(print_template_object.msg_save_before);
            e.preventDefault();
        });
    });
});