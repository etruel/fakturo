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
	'TAXES' => array( 
		'tabtitle' =>  __('Taxes', 'fakturo' ),
		'item1' => array( 
			'title' => __('Concept','fakturo'),
			'tip' => __('Tribute paid in a purchase or sale to support the expenses incurred. These mandatory payments are required from individuals and legal entities. ','fakturo').'<br>'.__('To register taxes, it is important to fill in the field "Tax Name" and "Tax Percentage". Once this is done, click on "Add New Tax", which will be recorded and displayed in the table to the right of the registration form. NOTE: As an example of taxes, we have "Income Tax", "Estate Tax" or "Property Tax".','fakturo'),
		),
	),
	'FIELDS' => array( 
		'tabtitle' =>  __('Fields', 'fakturo' ),
		'Item1' => array( 
			'title' => __('Name','fakturo'),
			'tip' => __('The name is how it appears on your site.','fakturo')),
		'Item2' => array( 
			'title' => __('Percentage','fakturo'),
			'tip' => __('Percentage for taxes.','fakturo'))
	),

);

?>