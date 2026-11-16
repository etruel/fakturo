<?php
/**
 * Fakturo description of Help Texts Array
 * -------------------------------
 * array('Text for left tab link' => array(
 * 	'field_name' => array( 
 * 		'title' => 'Text showed as bold in right side' , 
 * 		'tip' => 'Text html shown below the title in right side and also can be used for mouse over tips.' , 
 * 		'plustip' => 'Text html added below "tip" in right side in a new paragraph.',
 * )));
 */

$helptexts = array( 
	'REPORTS' => array( 
		'tabtitle' =>  __('Reports', 'fakturo' ),
		'item1' => array( 
			'title' => __('Concept','fakturo'),
			'tip' => __('Reports show what happened in your business during a period. Choose the report in the tabs, set the dates and press the filter button. Only finished invoices and receipts are counted, and the amounts are shown in the default currency of the system.', 'fakturo' ),
		),
		'item2' => array( 
			'title' => __('Who sees what','fakturo'),
			'tip' => __('Each report has its own permission, so you can give someone only the money reports. The administrator sets it in Settings &rarr; System &rarr; Access by Role.','fakturo'),
		),
	),
	'THE REPORTS' => array( 
		'tabtitle' =>  __('The reports', 'fakturo' ),
		'item1' => array( 
			'title' => __('Sales','fakturo'),
			'tip' => __('A chart with the total invoiced in the period.','fakturo'),
		),
		'item2' => array( 
			'title' => __('Clients','fakturo'),
			'tip' => __('<b>Summary</b>: the invoices and receipts of a client in the period. <b>Incomes</b>: invoices and receipts of the period with the taxes by type of document. <b>Client account</b>: every movement of a client with debit (invoices), credit (receipts) and the balance after each one.','fakturo'),
		),
		'item3' => array( 
			'title' => __('Products','fakturo'),
			'tip' => __('The inventory: each product with its stock, its price scale, its cost and its price.','fakturo'),
		),
		'item4' => array( 
			'title' => __('Salesmen','fakturo'),
			'tip' => __('How many invoices each salesperson made, how much they sold and the commission they earn according to your Commission Scales. See the help of that screen (Settings &rarr; Tables &rarr; Scales) to understand how it is calculated.','fakturo'),
		),
	),
	'EXPORT' => array( 
		'tabtitle' =>  __('Export', 'fakturo' ),
		'item1' => array( 
			'title' => __('PDF and CSV','fakturo'),
			'tip' => __('The client reports and the Salesmen report have buttons to download the table as a PDF to print or as a CSV file to open in a spreadsheet. The file has what you see on the screen, with the filters you chose.','fakturo'),
		),
	),
);
