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
	'DASHBOARD' => array( 
		'tabtitle' =>  __('Dashboard', 'fakturo' ),
		'item1' => array( 
			'title' => __('Concept','fakturo'),
			'tip' => __('The starting page of Fakturo. At the top there are big buttons that take you to what you use most; below them, the panels that Fakturo and its add-ons show.', 'fakturo' ),
		),
		'item2' => array( 
			'title' => __('Buttons','fakturo'),
			'tip' => __('You only see the buttons that your role may use. The administrator chooses which buttons there are, and in which order, in Settings &rarr; System &rarr; Dashboard Setup.','fakturo'),
		),
		'item3' => array( 
			'title' => __('Where to start','fakturo'),
			'tip' => __('If this is a new site, open Settings and run the configuration wizard first: the invoices need a currency, an invoice type and a sale point.','fakturo'),
		),
	),
);
