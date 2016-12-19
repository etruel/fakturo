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
	'PRODUCTS' => array( 
		'item1' => array( 
			'title' => __('Concept'),
			'tip' => __('To register products that will go in the inventory for sales, you can click on the "Add New Product" button, which will save the name alongside all other corresponding information in the main form.' )
		),
	),
	'PRODUCT INFORMATION' => array( 
		'item1' => array( 
			'title' => __('Provider'),
			'tip' => __('Select the service provider that supplies the product.'),
		),
		'item2' => array( 
			'title' => __('Product Type'),
			'tip' => __('Select the type of product; it can be supply, final product, component or spare.'),
		),
		'item3' => array( 
			'title' => __('Tax'),
			'tip' => __('Value added to the purchase price of the product. You can select general VAT, reduced VAT or extensive VAT.'),
		),
		'item4' => array( 
			'title' => __('References'),
			'tip' => __('Enter business references about the product in this field.'),
		),
		'item5' => array( 
			'title' => __('Description'),
			'tip' => __('A detailed description of the function of this product.'),
		),
		'item6' => array( 
			'title' => __('Packaging'),
			'tip' => __('Choose the packaging of the product and how it will be stored in inventory; it can be individual, box of 6 units or blister pack of 6 units.'),
		),
		'item7' => array( 
			'title' => __('Units per package'),
			'tip' => __('Enter the number of units that come in the package of this product.'),
		),
		'item8' => array( 
			'title' => __('Notes'),
			'tip' => __('Enter the characteristics of the product, warnings, expiration date, etc.'),
		),
		'item9' => array( 
			'title' => __('Origin'),
			'tip' => __('Country of product manufacture.'),
		),
	),
	'ITEM PRICE' => array( 
		'item1' => array( 
			'title' => __('Currency'),
			'tip' => __('Select the type of currency used for the purchase of this product (this value can change on the invoice). It can be Argentinian peso, dollars or Euros.'),
		),
		'item2' => array( 
			'title' => __('Price'),
			'tip' => __('Price of the product according to the currency selected.'),
		),
	),
	'PRODUCT IMAGE' => array( 
		'item1' => array( 
			'title' => __('Concept'),
			'tip' => __('You can select an image from the WordPress media gallery or take a snapshot with your webcam (useful if you have the supplier in front of you and you want their photo).'),
		),
	),
);


?>