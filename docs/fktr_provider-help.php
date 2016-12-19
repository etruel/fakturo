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
	'SUPPLIER INFORMATION' => array( 
		'item1' => array( 
			'title' => __('Taxpayer Identification Number.'),
			'tip' => __('ID required by people or companies to perform any relevant transaction. The number entered in this field will go through a real-time validation process to corroborate its existence'),
		),
		'item2' => array( 
			'title' => __('Address' ),
			'tip' => __('Place or address of the service provider.'),
		),
		'item3' => array( 
			'title' => __('Country' ),
			'tip' => __('Country of origin of the service provider together with state and city. '),
		),
		'item4' => array( 
			'title' => __('Banking Entity' ),
			'tip' => __('Bank of the provider.'),
		),
		'item5' => array( 
			'title' => __('Postal Code'),
			'tip' => __('Series of numbers associated with the place of origin. Enter the local postal code of their address.'),
		),
		'item6' => array( 
			'title' => __('Landline / Cell Phone' ),
			'tip' => __('Telephone number of business or personal contact.'),
		),
		'item7' => array( 
			'title' => __('Email' ),
			'tip' => __('Contact email address.'),
		),
		'item7' => array( 
			'title' => __('Website' ),
			'tip' => __('Website with information, portfolio and services.'),
		),
	),
	'SUPPLIER CONTACT DETAILS' => array( 
		'item1' => array( 
			'title' => __('CONTACT DETAILS'),
			'tip' => __('Register one or several contacts for the service provider with their corresponding personal contact information.'),
		),
	),
	'PROVIDER IMAGE' => array( 
		'item1' => array( 
			'title' => __('PROVIDER IMAGE'),
			'tip' => __('You can select an image from the WordPress media gallery or take a snapshot with your webcam (useful if you have the supplier in front of you and you want their photo).'),
		),
	),
	'ACTIVE PROVIDER' => array( 
		'item1' => array( 
			'title' => __('PROVIDER ACTIVE'),
			'tip' => __('Mark the supplier as active for them to appear in reports and if they are available for selection.'),
		),
	),
	'ASSIGN VENDOR' => array( 
		'item1' => array( 
			'title' => __('ASSIGN VENDOR'),
			'tip' => __('Select a registered vendor who represents the service provider face to face.'),
		),
	),
	
);


?>