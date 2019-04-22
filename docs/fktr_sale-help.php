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
	'SALE' => array( 
		'tabtitle' =>  __('Sale', 'fakturo' ),
		'item1' => array( 
			'title' => __('Concept','fakturo'),
			'tip' => __('Organized process aimed at enhancing the vendor/customer relationship through the purchase of products or services. ','fakturo'),
                        'plustip' =>  __('To create a new sale, you can click the button <b>"Add New"</b> and fill in the appropriate form. You will also be able to see the list of completed sales in the displayed information table. An automatic code is generated, which identifies the sale to be made.','fakturo')
                )
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
			'title' => __('Invoice Number','fakturo'),
			'tip' => __('Invoice identifier which is randomly generated and is read-only. ','fakturo')),
		'item4' => array( 
			'title' => __('Date','fakturo'),
			'tip' => __('This is according the date when the product was bought or sold','fakturo')),
		'item5' => array( 
			'title' => __('Invoice Currency','fakturo'),
			'tip' => __('Currency which will be displayed on the invoice. It can be Argentinian peso, dollars or Euros','fakturo')),
		'item7' => array( 
			'title' => __('Vendor','fakturo'),
			'tip' => __('Person who manages the invoice','fakturo')),	
	),
	'INVOICE' => array( 
		'tabtitle' =>  __('Invoice', 'fakturo' ),
		'item1' => array( 
			'title' => __('Concept','fakturo'),
			'tip' => __('Proof that is acquired through a purchase or sale. Search for the product to sell, which automatically comes with its unit price, reference and brief description. When choosing the quantity to buy, the total and subtotal fields will automatically be filled in.','fakturo')),
	),
	'DISCOUNT' => array( 
		'tabtitle' =>  __('Discount', 'fakturo' ),
		'item1' => array( 
			'title' => __('Concept','fakturo'),
			'tip' => __('Amount of money to be discounted depending on the purchase.','fakturo')),
	)
);


?>