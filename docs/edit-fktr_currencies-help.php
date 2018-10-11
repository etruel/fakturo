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
	'CURRENCIES' => array( 
		'tabtitle' =>  __('Currencies', 'fakturo' ),
		'item1' => array( 
			'title' => __('Concept','fakturo'),
			'tip' => __('Main form to add currencies to your system. To do this, simply click <b>"Add New Currency"</b> after the fields are already filled in. You need to enter the name of the currency, its symbol, for example $ or Euros, and a reference website to find the conversion rate. ','fakturo'),
		),
	),
	'FIELDS' => array( 
		'tabtitle' =>  __('Fields', 'fakturo' ),
		'Item1' => array( 
			'title' => __('Name','fakturo'),
			'tip' => __('The name is how it appears on your site.','fakturo')),
		'Item2' => array( 
			'title' => __('Plural','fakturo'),
			'tip' => __("Plural name of the currency. ",'fakturo')),
		'Item3' => array( 
			'title' => __('Symbol','fakturo'),
			'tip' => __('Currency symbol. ','fakturo')),
		'Item4' => array( 
			'title' => __('Rate','fakturo'),
			'tip' => __('Currency rate.','fakturo')),
		'Item5' => array( 
			'title' => __('Reference','fakturo'),
			'tip' => __('Website to find the conversion rate.','fakturo'))
	),

);

?>