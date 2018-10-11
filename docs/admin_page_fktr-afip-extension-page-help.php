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
	'AFIP SETTINGS' => array( 
		'tabtitle' =>  __('Afip Settings', 'fakturo' ),
		'item1' => array( 
			'title' => __('CUIT','fakturo'),
			'tip' => __('ID required of companies to perform any relevant transaction. For example, the company’s TIN.','fakturo')),
		'item2' => array( 
			'title' => __('Passphrase','fakturo'),
			'tip' => __('A passphrase is similar to a password in terms of its use, but is generally longer to add security and must be related to the cert and key.','fakturo')),
		'item3' => array( 
			'title' => __('Enable logs','fakturo'),
			'tip' => __('Enable sequential registration in a file or a database of all events (events or actions) that affect a particular process. ','fakturo')),
		'item4' => array( 
			'title' => __('Sales Points','fakturo'),
			'tip' => __('Set registered points of sale (separated with commas), which will be used in the Afip system.','fakturo')),	
	)
);


?>