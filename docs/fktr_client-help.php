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
	'MERCHANT INFORMATION' => array( 
		'item1' => array( 
			'title' => __('Taxpayer Identification Number'),
			'tip' => __('Identification required of people or companies to perform any relevant transaction; may be an ID or passport.'),
		),
		'item2' => array( 
			'title' => __('Payment Method'),
			'tip' => __('Choose type of payment to be used, whether cash, check or bank transfer (this option is used by default but can be changed when managing an invoice).'),
		),
		'item3' => array( 
			'title' => __('Banking Entity' ),
			'tip' => __('Bank of the merchant.'),
		),
		'item4' => array( 
			'title' => __('Currency' ),
			'tip' => __('Type of currency used by the merchant.'),
		),
		'item5' => array( 
			'title' => __('Price Control'),
			'tip' => __('Specific amounts for prices of goods and services within the given market.'),
		),
		'item6' => array( 
			'title' => __('Credit Limit' ),
			'tip' => __('Enter the merchant’s credit limit according to their bank entity.'),
		),
	),
	'CUSTOMER INFORMATION' => array( 
		'item1' => array( 
			'title' => __('Address'),
			'tip' => __('Place or address of the customer.'),
		),
		'item2' => array( 
			'title' => __('Country'),
			'tip' => __('Country of origin of the customer together with state and city.'),
		),
		'item3' => array( 
			'title' => __('Postal Code'),
			'tip' => __('Series of numbers associated with the place of origin. Enter the local postal code of their address.'),
		),
		'item4' => array( 
			'title' => __('Landline / Cell Phone'),
			'tip' => __('Telephone number of business or personal contact.'),
		),
		'item5' => array( 
			'title' => __('Emai'),
			'tip' => __('Contact email address. '),
		),
		'item6' => array( 
			'title' => __('Website'),
			'tip' => __('Website with information, portfolio and services.'),
		),
		'item7' => array( 
			'title' => __('Facebook Profile URL'),
			'tip' => __('Enter the customer’s personal Facebook address.'),
		),
	),
	'CUSTOMER CONTACT DETAILS' => array( 
		'item1' => array( 
			'title' => __(''),
			'tip' => __('Register one or several contacts for the customer with their corresponding personal contact information.'),
		),
	),
	'ACTIVE CUSTOMERS' => array( 
		'item1' => array( 
			'title' => __(''),
			'tip' => __('Mark the customer as active for them to appear in reports and if they are available for selection.'),
		),
	),
);



?>