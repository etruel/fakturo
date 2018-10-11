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
	'PRINT FORMATS' => array( 
		'tabtitle' =>  __('Print Formats', 'fakturo' ),
		'item1' => array( 
			'title' => __('Print Template','fakturo'),
			'tip' => __('You can add the default print templates format by assigning it to a specific module by entering the name of the template and the description, these formats will be used when printing invoices or reports.','fakturo'),
		),
	),
);

?>