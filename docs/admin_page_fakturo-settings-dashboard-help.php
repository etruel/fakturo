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
	'DASHBOARD SETUP' => array( 
		'tabtitle' =>  __('Dashboard Setup', 'fakturo' ),
		'item1' => array( 
			'title' => __('Concept','fakturo'),
			'tip' => __('Fakturo shows a row of big shortcut buttons (invoices, receipts, clients, reports...). Here you choose which buttons it shows, in which order and where.', 'fakturo' ),
		),
		'item2' => array( 
			'title' => __('Show dashboard menu','fakturo'),
			'tip' => __('Shows the row of buttons at the top of every Fakturo screen. If it is off, the buttons only appear on the Fakturo dashboard.','fakturo'),
		),
		'item3' => array( 
			'title' => __('Show dashboard menu in all WordPress admin screens','fakturo'),
			'tip' => __('Shows the same row at the top of the other WordPress screens too (posts, users...). It needs the previous switch to be on.','fakturo'),
		),
		'item4' => array( 
			'title' => __('Dialer 1 to 7','fakturo'),
			'tip' => __('The seven places of the row, from left to right. Choose what each one opens, or "Select a option" to leave it empty. Each person only sees the buttons they have permission for, so the same row can look different for a salesman and for a manager.','fakturo'),
		),
	),
);
