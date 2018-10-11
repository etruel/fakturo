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
	'STOCKS' => array( 
		'tabtitle' =>  __('Stocks', 'fakturo' ),
		'feeds' => array( 
			'title' => __('Concept', 'fakturo' ),
			'tip' => __('Stock indicates the quantity of products or raw materials that a company has in its warehouse pending its sale or commercialization.', 'fakturo' ).
				'<br><br>'.__('The management of stocks in a company is always important. The profitability depends on the management of the concept that the company has, since they will obtain more or less benefits if their storage is optimal.', 'fakturo' ),
		),
	),
);


?>