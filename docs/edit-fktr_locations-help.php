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
	'LOCATIONS' => array( 
		'tabtitle' =>  __('Locations', 'fakturo' ),
		'feeds' => array( 
			'title' => __('Concept', 'fakturo'),
			'tip' => __('Main form where you added all the locations used in the system at the time of registering people, products etc. Once filled the fields you must click on <b>"Add new location"</b> to save your changes. These will be added to the list that is located on the right side of the form. ','fakturo'))
	),
	'FIELDS' => array( 
		'tabtitle' =>  __('Fields', 'fakturo' ),
		'Item1' => array( 
			'title' => __('Name','fakturo'),
			'tip' => __('The name is how it appears on your site.','fakturo'))
	),
);
?>