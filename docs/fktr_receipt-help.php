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
	'DATA RECEPTION' => array( 
		'tabtitle' =>  __('Data reception', 'fakturo' ),
		'item1' => array( 
			'title' => __('Customer','fakturo'),
			'tip' => __('Select who will receive the receipt from the customer list. If they are not found, then you must register the customer on the <b>"customers"</b> form. If the customer does exist and doesn’t appear, verify that the option <b>"ACTIVE CUSTOMER"</b> is selected in the customer form.','fakturo'),
		),
		'item2' => array( 
			'title' => __('Payment Method','fakturo'),
			'tip' => __('Choose the type of payment to be used, whether cash, check or bank transfer','fakturo'),
		),
		'item3' => array( 
			'title' => __('Receipt Currency','fakturo'),
			'tip' => __('Currency which will be displayed on the receipt. It can be Argentinian peso, dollars or Euros.','fakturo'),
		),
		'item4' => array( 
			'title' => __('Cash','fakturo'),
			'tip' => __('Money in the form of paper bills or banknotes, not saved on debit or credit card.','fakturo'),
		),
		'item5' => array( 
			'title' => __('Check','fakturo'),
			'tip' => __('Accounting document authorized by the customer to extract money due to some purchase.  You can enter one or more checks onto the receipt by clicking <b>"Add Check".</b> Only the checks originating from the customer will appear, and you will be able to search for it through the bank, the banking entity and serial number, to know the amount, the type of currency and the date of collection.','fakturo'),
		),
		'item6' => array( 
			'title' => __('Invoices','fakturo'),
			'tip' => __('Invoices are added to the customer automatically, calculating the total amount to be paid according to the invoice number, which is associated with the receipt to be created (the invoice number must be created first).','fakturo'),
		),
	)
);

?>