
jQuery(document).ready(function() {
	
	jQuery('.btn_print_receipt').click(function(e) {
		var printWin = window.open(jQuery(this).attr('href'), "PrintWindow", "width=400,height=400");
		jQuery(printWin.document).ready(function() {
			printWin.print();
			//setTimeout(function () { printWin.close(); }, 3500);
		});
		e.preventDefault();
	});
	jQuery('#print_receipt_button').click(function(e) {
		var printWin = window.open(jQuery(this).attr('href'), "PrintWindow", "width=400,height=400");
		jQuery(printWin.document).ready(function() {
			printWin.print();
		});
		e.preventDefault();
	});
	
	jQuery('.btn_send_receipt').click(function(e) {
		if (!confirm('Are sure send pdf to client?')) {
			e.preventDefault();
		}
	});
	
});