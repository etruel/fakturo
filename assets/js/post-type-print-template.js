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


    
});