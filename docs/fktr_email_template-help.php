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
			'title' => __('Email Template','fakturo'),
			'tip' => __('You can add the default email format assigning it to a specific module by entering the name of the template, the description and the subject, these formats will be used when sending reports to customers or provers.','fakturo'),
		),
	),
);

?>