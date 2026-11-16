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
	'ADD-ONS' => array( 
		'tabtitle' =>  __('Add-ons', 'fakturo' ),
		'item1' => array( 
			'title' => __('Concept','fakturo'),
			'tip' => __('Add-ons are extra plugins that give Fakturo more abilities, such as recurring subscriptions or electronic invoicing. This list shows the ones you have installed and the ones you can get.', 'fakturo' ),
		),
		'item2' => array( 
			'title' => __('The button of each add-on','fakturo'),
			'tip' => __('<b>Activate</b> if it is installed but stopped. <b>License</b> if it needs a license key to receive updates: it takes you to the Licenses screen. Otherwise a switch to let WordPress update it by itself.','fakturo'),
		),
		'item3' => array( 
			'title' => __('If the list is empty','fakturo'),
			'tip' => __('The list of available add-ons is downloaded from etruel.com. If that site does not answer, Fakturo waits 15 minutes before asking again; the add-ons you already have keep working.','fakturo'),
		),
	),
);
