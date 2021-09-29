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
			'title' => __('Print Templates','fakturo'),
			'tip' => __('They are used to format the different documents to be printed, such as invoices, receipts, reports, etc.','fakturo'),
            'plustip' => __('Standard HTML is used with the system variables in the format used by the RainTPL library.', 'fakturo')
		),
		'item2' => array( 
			'title' => __('Reset to Default','fakturo'),
			'tip' => __('Fakturo includes standard templates for almost all the documents handled by it.','fakturo'),
            'plustip' => __('You can add the default print templates after assigning it to a specific module and entering the template name and description.', 'fakturo')
		),
	),
);

?>
