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
	'SUPPLIERS' => array( 
		'feeds' => array( 
			'title' => __('Provider.', '' ),
			'tip' => __('Those in charge of supplying products, items or tangent articles. '))
	),
	'Add New' => array( 
		'feed_url' => array( 
			'title' => __('Add New Provider'),
			'tip' => __('To register new providers, you can click on the "Add new" button, which will take you to the corresponding supplier registration form to fill in the fields')
		),
	),

);


?>