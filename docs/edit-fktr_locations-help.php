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
	'TYPE OF PRODUCT' => array( 
		'feeds' => array( 
			'title' => __('Concept', '' ),
			'tip' => __('Main form where you added the types of products used in the system at the time of registering products to sell. Adds a new type of product by clicking on the button <b>"Add New Product type"</b> once filled all the fields. Will appear in the list that is located on the right side of the form.'))
	),
	'LOCATIONS' => array( 
		'feeds' => array( 
			'title' => __('Concept', '' ),
			'tip' => __('Main form where you added all the locations used in the system at the time of registering people, products etc. Once filled the fields you must click on <b>"Add new location"</b> to save your changes. These will be added to the list that is located on the right side of the form. '))
	),
	'PACKING' => array( 
		'feeds' => array( 
			'title' => __('Concept', '' ),
			'tip' => __('Box or wrappers which allow you to provide products that will put on sale. To register the enbalajes it is necessary to place the same description and click on the button <b>"Add new packing"</b> which to save your changes will appear in the right list of the registration form. '))
	),
	'SCALE OF PRICES' => array( 
		'feeds' => array( 
			'title' => __('Concept', '' ),
			'tip' => __('the amount of commodity prices to take control when it comes to register products with its current price. To add price range must fill description along with your percentage after field click on the button <b>"Add new scale of prices"</b> which will be recorded and shown in the right list on the form.'))
	),
	'ORIGINS' => array( 
		'feeds' => array( 
			'title' => __('Concept', '' ),
			'tip' => __('Define the origin of the products to register, know where come from taking into account as important for evaluating the same reference. To register sources important to insert the name of the source for then click on the button "Add new source" where is recorded by showing up in the right list on the form. Note that in this list you can make a search the origins reported through their filters fast.'))
	),
	
);

?>