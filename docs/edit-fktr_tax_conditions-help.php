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
	'TAX CONDITIONS' => array( 
		'tabtitle' =>  __('Tax Conditions', 'fakturo' ),
		'item1' => array( 
			'title' => __('Concept','fakturo'),
			'tip' => __('Tax conditions are important when adding a tax because it allows us to record the indicators to be met on a specific tax. Fill in the fields <b>"Name"</b> and the type of bill in which the condition will be valid.  Click the <b>"Add New Tax Condition"</b> button, which will appear in the list to the right of the form so you can search for the registered conditions quickly.','fakturo'),
		),
	),
	'FIELDS' => array( 
		'tabtitle' =>  __('Fields', 'fakturo' ),
		'Item1' => array( 
			'title' => __('Name','fakturo'),
			'tip' => __('The name is how it appears on your site.','fakturo')),
		'Item2' => array( 
			'title' => __('Invoice Types','fakturo'),
			'tip' => __('Default Invoice Type for this Tax Condition.','fakturo'))
	),

);


?>