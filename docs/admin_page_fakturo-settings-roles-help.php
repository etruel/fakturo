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
	'ACCESS BY ROLE' => array( 
		'tabtitle' =>  __('Access by Role', 'fakturo' ),
		'item1' => array( 
			'title' => __('Concept','fakturo'),
			'tip' => __('Each person who works with Fakturo has a role. Here you choose what each role can do on this site: each row is something people can do and each column is a role. Tick the boxes and press Save.', 'fakturo' ),
			'plustip' => __('The administrator of the site always has everything. A box outlined in yellow is different from the default. "Restore the default permissions" undoes all your changes. Only administrators see this screen.', 'fakturo' ),
		),
		'item2' => array( 
			'title' => __('Roles added by add-ons','fakturo'),
			'tip' => __('An add-on can bring a role of its own, for example an accountant who only sees the money reports. It appears here as a new column.','fakturo'),
		),
		'item3' => array( 
			'title' => __('Limits','fakturo'),
			'tip' => __('Things that could harm the site, like installing plugins, editing files or the licenses, can not be given from here. A person who can create users can only give roles that have no more permissions than they have.','fakturo'),
		),
	),
);
