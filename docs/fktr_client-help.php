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
		'tabtitle' =>  __('Merchant information', 'fakturo' ),
		'item1' => array( 
			'title' => __('Taxpayer Identification Number','fakturo'),
			'tip' => __('Identification required of people or companies to perform any relevant transaction; may be an ID or passport.','fakturo'),
		),
		'item2' => array( 
			'title' => __('Payment Method','fakturo'),
			'tip' => __('Choose type of payment to be used, whether cash, check or bank transfer (this option is used by default but can be changed when managing an invoice).','fakturo'),
		),
		'item3' => array( 
			'title' => __('Banking Entity' ),
			'tip' => __('Bank of the merchant.','fakturo'),
		),
		'item4' => array( 
			'title' => __('Currency' ),
			'tip' => __('Type of currency used by the merchant.','fakturo'),
		),
		'item5' => array( 
			'title' => __('Price Control','fakturo'),
			'tip' => __('Specific amounts for prices of goods and services within the given market.','fakturo'),
		),
		'item6' => array( 
			'title' => __('Credit Limit' ),
			'tip' => __('Enter the merchant’s credit limit according to their bank entity.','fakturo'),
		),
	),
	'CUSTOMER INFORMATION' => array( 
		'tabtitle' =>  __('Customer information', 'fakturo' ),
		'item1' => array( 
			'title' => __('Address','fakturo'),
			'tip' => __('Place or address of the customer.','fakturo'),
		),
		'item2' => array( 
			'title' => __('Country','fakturo'),
			'tip' => __('Country of origin of the customer together with state and city.','fakturo'),
		),
		'item3' => array( 
			'title' => __('Postal Code','fakturo'),
			'tip' => __('Series of numbers associated with the place of origin. Enter the local postal code of their address.','fakturo'),
		),
		'item4' => array( 
			'title' => __('Landline / Cell Phone','fakturo'),
			'tip' => __('Telephone number of business or personal contact.','fakturo'),
		),
		'item5' => array( 
			'title' => __('Emai','fakturo'),
			'tip' => __('Contact email address. ','fakturo'),
		),
		'item6' => array( 
			'title' => __('Website','fakturo'),
			'tip' => __('Website with information, portfolio and services.','fakturo'),
		),
		'item7' => array( 
			'title' => __('Facebook Profile URL','fakturo'),
			'tip' => __('Enter the customer’s personal Facebook address.','fakturo'),
		),
	),
	'CUSTOMER CONTACT DETAILS' => array( 
		'tabtitle' =>  __('Customer contact details', 'fakturo' ),
		'item1' => array( 
			'title' => __('','fakturo'),
			'tip' => __('Register one or several contacts for the customer with their corresponding personal contact information.','fakturo'),
		),
	),
	'ACTIVE CUSTOMERS' => array( 
		'tabtitle' =>  __('Active customer', 'fakturo' ),
		'item1' => array( 
			'title' => __('','fakturo'),
			'tip' => __('Mark the customer as active for them to appear in reports and if they are available for selection.','fakturo'),
		),
	),
);



?>