
jQuery(document).ready(function() {
	
	jQuery('.btn_print_invoice').click(function(e) {
		var printWin = window.open(jQuery(this).attr('href'), "PrintWindow", "width=400,height=400");
		jQuery(printWin.document).ready(function() {
			printWin.print();
			printWin.close();
		});

		e.preventDefault();
	});

});