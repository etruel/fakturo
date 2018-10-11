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
	'AFIP SYSTEM' => array( 
		'tabtitle' =>  __('Afip System', 'fakturo' ),
		'item1' => array( 
			'title' => __('Homologation','fakturo'),
			'tip' => __('Is a setup of software and hardware for the testing teams to execute test cases. In other words, it supports test execution with hardware, software and network configured.','fakturo')),
		'item2' => array( 
			'title' => __('Exchange rate','fakturo'),
			'tip' => __('Exchange rate of currencies from AFIP web service.','fakturo')),	
	)
);


?>