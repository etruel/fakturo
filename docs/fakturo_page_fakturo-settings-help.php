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
	'COMPANY INFO ' => array( 
		'item1' => array( 
			'title' => __('CONFIGURATION OF THE COMPANY','fakturo'),
			'tip' => __('','fakturo'),
		),

	),
	'FIELDS ' => array( 
		'item1' => array( 
			'title' => __('Taxpayer Identification Number','fakturo'),
			'tip' => __('ID required of companies to perform any relevant transaction. For example, the company’s TIN.','fakturo'),
		),
		'item2' => array( 
			'title' => __('Income Tax','fakturo'),
			'tip' => __('Taxes to be paid to the government on profits obtained during the fiscal year.','fakturo'),
		),
		'item3' => array( 
			'title' => __('Address','fakturo'),
			'tip' => __('Place or address of the company.','fakturo'),
		),
		'item4' => array( 
			'title' => __('Country','fakturo'),
			'tip' => __(' Country of origin of the company together with state and city.','fakturo'),
		),
		'item5' => array( 
			'title' => __('Postal Code','fakturo'),
			'tip' => __('Series of numbers associated with the place of origin. Enter the local postal code of the address.','fakturo'),
		),
		'item6' => array( 
			'title' => __('Landline / Cell Phone','fakturo'),
			'tip' => __('Telephone number of business or personal contact.','fakturo'),
		),
		'item7' => array( 
			'title' => __('Company Logo','fakturo'),
			'tip' => __('You can select an image from the WordPress media gallery or take a snapshot with your webcam (useful if you have the supplier in front of you and you want their photo).','fakturo').'<br>'.__('<b>NOTE:</b> Click on "Save Changes" to save your settings. ','fakturo'),
		),
	),	
);


?>