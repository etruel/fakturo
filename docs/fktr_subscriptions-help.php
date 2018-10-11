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
	'SUSCRIPTIONS' => array( 
		'tabtitle' =>  __('Subscriptions', 'fakturo' ),
		'feeds' => array( 
			'title' => __('Concept', 'fakturo' ),
			'tip' => __('The subscription is a business model in which a person is entitled to a service through a fee.', 'fakturo' ).
				'<br><br>'.__('Instead of selling products individually, with a subscription, the consumption of a product or access to a service it&#39;s marketed intermittently in variable periods (monthly, annually or seasonally). This way of negotiating has proven to be efficient in cases where a single sale becomes a repetitive sale.', 'fakturo' ),
		),
	),
	'INVOICE DETAILS' => array( 
		'tabtitle' =>  __('Invoice Details', 'fakturo' ),
		'item1' => array( 
			'title' => __('Customer','fakturo'),
			'tip' => __('Select who will receive the invoice from the customer list. If they are not found, then you must register the customer on the "customers" form. If the customer does exist and doesn’t appear, verify that the option "ACTIVE CUSTOMER" is selected in the customer form','fakturo')),
		'item2' => array( 
			'title' => __('Invoice Type','fakturo'),
			'tip' => __('Select the type of invoice to be made from the list; it can be INVOICE A, B, C or credit note.','fakturo')),
		'item3' => array( 
			'title' => __('Suscription Number','fakturo'),
			'tip' => __('Suscription identifier which is randomly generated and is read-only. ','fakturo')),
		'item4' => array( 
			'title' => __('Date','fakturo'),
			'tip' => __('This is according the date when the product was bought or sold','fakturo')),
		'item5' => array( 
			'title' => __('Invoice Currency','fakturo'),
			'tip' => __('Currency which will be displayed on the invoice. It can be Argentinian peso, dollars or Euros','fakturo')),
		'item6' => array( 
			'title' => __('Vendor','fakturo'),
			'tip' => __('Person who manages the invoice','fakturo')),	
	),
	'INVOICE' => array( 
		'tabtitle' =>  __('Invoice', 'fakturo' ),
		'item1' => array( 
			'title' => __('Concept','fakturo'),
			'tip' => __('Proof that is acquired through a purchase or sale. Search for the product to sell, which automatically comes with its unit price, reference and brief description. When choosing the quantity to buy, the total and subtotal fields will automatically be filled in.','fakturo')),
	),
	'INVOICING' => array( 
		'tabtitle' =>  __('Invoicing', 'fakturo' ),
		'item1' => array( 
			'title' => __('Concept','fakturo'),
			'tip' => __('Proof that is acquired through a purchase or sale. Search for the product to sell, which automatically comes with its unit price, reference and brief description. When choosing the quantity to buy, the total and subtotal fields will automatically be filled in.','fakturo')),
		'item2' => array( 
			'title' => __('Active creating invoices on','fakturo'),
			'tip' => __('Period of time in which the automatic creation of invoices will start, for example 01/01/2019 at 08:00 AM.','fakturo')),
		'item3' => array( 
			'title' => __('Finish subscription on','fakturo'),
			'tip' => __('Period of time in which the suscription will finish,it can be by date or by count.','fakturo')),
		'item4' => array( 
			'title' => __('Create invoice at the end of the month','fakturo'),
			'tip' => __('Create invoice only at the end of each month.','fakturo')),
		'item5' => array( 
			'title' => __('Period type','fakturo'),
			'tip' => __('You can choose between the types of periods to create the CRON job of your choice.','fakturo')),
	),
	'DISCOUNT' => array( 
		'tabtitle' =>  __('Discount', 'fakturo' ),
		'item1' => array( 
			'title' => __('Concept','fakturo'),
			'tip' => __('Amount of money to be discounted depending on the purchase.','fakturo')),
	)
);


?>