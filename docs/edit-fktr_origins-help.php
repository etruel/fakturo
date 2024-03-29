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
	'ORIGINS' => array( 
		'tabtitle' =>  __('Origins', 'fakturo' ),
		'feeds' => array( 
			'title' => __('Concept', 'fakturo'),
			'tip' => __('Define the origin of the products to register, know where come from taking into account as important for evaluating the same reference. To register origins important to insert the name of the origin for then click on the button "Add new origin" where is recorded by showing up in the right list on the form. Note that in this list you can make a search the origins reported through their filters fast.','fakturo'))
	),
	'FIELDS' => array( 
		'tabtitle' =>  __('Fields', 'fakturo' ),
		'Item1' => array( 
			'title' => __('Name','fakturo'),
			'tip' => __('The name is how it appears on your site.','fakturo'))
	),
);


?>