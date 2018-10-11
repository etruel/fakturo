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
	'CHECKS' => array( 
		'tabtitle' =>  __('Checks', 'fakturo' ),
		'Item1' => array( 
			'title' => __('Concept','fakturo'),
			'tip' => __('Register the checks which will be visible on the receipts. This can be done by clicking on the <b>"Add New Check"</b> button, which will be saved and will appear in the list on the right side of the form.','fakturo'))
	),
	'FIELDS' => array( 
		'tabtitle' =>  __('Fields', 'fakturo' ),
		'Item1' => array( 
			'title' => __('Serial Number','fakturo'),
			'tip' => __('Combination of numbers with which the check is identified.','fakturo')),
		'Item2' => array( 
			'title' => __('Customer','fakturo'),
			'tip' => __("Select the customer who owns the check. If they don't exist, you will have to register them in the customers form. ",'fakturo')),
		'Item3' => array( 
			'title' => __('Bank','fakturo'),
			'tip' => __('Banking entity from which the check is issued. ','fakturo')),
		'Item4' => array( 
			'title' => __('Currency','fakturo'),
			'tip' => __('Type of currency of the check. It can be Argentinian peso, dollars or Euros.','fakturo')),
		'Item5' => array( 
			'title' => __('Amount','fakturo'),
			'tip' => __('Amount of the check.','fakturo')),
		'Item6' => array( 
			'title' => __('Date / Collection Date','fakturo'),
			'tip' => __("Date of the check's creation as well as the date to collect from it.",'fakturo')),
		'Item7' => array( 
			'title' => __('Status','fakturo'),
			'tip' => __('Select the status in which is the check is found; it can be for deposit, payment charges or rejected.','fakturo'))
	),

);



?>