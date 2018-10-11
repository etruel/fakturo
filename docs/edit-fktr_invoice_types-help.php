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
	'INVOICE TYPE' => array( 
		'tabtitle' =>  __('Invoice Type', 'fakturo' ),
		'item1' => array( 
			'title' => __('Concept','fakturo'),
			'tip' => __('Register the invoice types to be taken into account when billing a customer.  For example, it can be INVOICE A, B, C or credit note. To add a new type of invoice, you must click on the <b>"Add New Invoice Type"</b> button, keeping in mind the other fields must already be filled. These will automatically be shown in the table to the right.','fakturo'),
		),
	),
	'FIELDS' => array( 
		'tabtitle' =>  __('Fields', 'fakturo' ),
		'Item1' => array( 
			'title' => __('Name','fakturo'),
			'tip' => __('The name is how it appears on your site.','fakturo')),
		'Item2' => array( 
			'title' => __('Short Name','fakturo'),
			'tip' => __("Short name of the invoice types.",'fakturo')),
		'Item3' => array( 
			'title' => __('Symbol','fakturo'),
			'tip' => __('Invoice type symbol. ','fakturo'))
	),
);


?>