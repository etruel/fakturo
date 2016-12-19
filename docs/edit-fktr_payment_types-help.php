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
	'PAYMENT METHODS' => array( 
		'item1' => array( 
			'title' => __('Concept','fakturo'),
			'tip' => __('Register the payment methods which will be visible on the invoice of a purchase. This can be done by clicking on the <b>"Add New Payment Type"</b> button, which will be saved and will appear in the list on the right side of the form. The payment method can be: cash, check, etc.','fakturo'),
		),

	),
);


?>