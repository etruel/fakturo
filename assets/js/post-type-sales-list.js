
jQuery(document).ready(function() {
	
	jQuery('.btn_print_invoice').click(function(e) {
		var printWin = window.open(jQuery(this).attr('href'), "PrintWindow", "width=700,height=500");
		
		jQuery(printWin.document).ready(function() {
			
		    printWin.print();
		//    setTimeout(function () { printWin.close(); }, 3500);
		});
		
		e.preventDefault();
	});
	jQuery('.btn_print_demo_invoice').click(function(e) {
		var printWin = window.open(jQuery(this).attr('href'), "PrintWindow", "width=700,height=500");
		
		e.preventDefault();
	});
	jQuery('.btn_send_invoice').click(function(e) {
		if (!confirm('Are sure send pdf to client?')) {
			e.preventDefault();
		}
	});
	
});
