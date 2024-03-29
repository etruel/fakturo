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
	'SCALE OF PRICES' => array( 
		'tabtitle' =>  __('Price Scales', 'fakturo' ),
		'feeds' => array( 
			'title' => __('Concept', 'fakturo' ),
			'tip' => __('the amount of commodity prices to take control when it comes to register products with its current price. To add price range must fill description along with your percentage after field click on the button <b>"Add new scale of prices"</b> which will be recorded and shown in the right list on the form.','fakturo'))
	),
	'FIELDS' => array( 
		'tabtitle' =>  __('Fields', 'fakturo' ),
		'Item1' => array( 
			'title' => __('Name','fakturo'),
			'tip' => __('The name is how it appears on your site.','fakturo')),
		'Item2' => array( 
			'title' => __('Percentage','fakturo'),
			'tip' => __('Percentage for price scales.','fakturo'))
	),
);


?>