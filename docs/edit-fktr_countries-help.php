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
	'COUNTRIES AND STATES' => array( 
		'tabtitle' =>  __('Countries and States', 'fakturo' ),
		'item1' => array( 
			'title' => __('Concept','fakturo'),
			'tip' => __('Main form to add countries and states that will be used in the main forms of the system. To add a new country, just click on the <b>"Add New Country"</b> button and fill in the fields. Upon saving it, the list will be found on the right side of the form.','fakturo'),
		),
	),
	'FIELDS' => array( 
		'tabtitle' =>  __('Fields', 'fakturo' ),
		'Item1' => array( 
			'title' => __('Name','fakturo'),
			'tip' => __('The name is how it appears on your site.','fakturo')),
		'Item2' => array( 
			'title' => __('Country','fakturo'),
			'tip' => __('Assign a parent term to create a hierarchy. The term Jazz, for example, would be the parent of Bebop and Big Band.','fakturo'))
	),
);
?>