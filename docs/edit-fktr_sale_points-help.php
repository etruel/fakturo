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
	'POINTS OF SALE' => array( 
		'tabtitle' =>  __('Sale Points', 'fakturo' ),
		'item1' => array( 
			'title' => __('Concept','fakturo'),
			'tip' => __('Register your company or business’ points of sale. This can be done by clicking on the <b>"Add New Point of Sale"</b> after having filled in the name and identification code fields.  Upon saving them, they will automatically be added to the list of points of sale that appears on the right side of the form..','fakturo'),
		),

	),
	'FIELDS' => array( 
		'tabtitle' =>  __('Fields', 'fakturo' ),
		'Item1' => array( 
			'title' => __('Name','fakturo'),
			'tip' => __('The name is how it appears on your site.','fakturo')),
		'Item2' => array( 
			'title' => __('Code','fakturo'),
			'tip' => __("It's the code that belongs to the point of sale.",'fakturo'))
	),
);


?>