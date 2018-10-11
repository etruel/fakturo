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
	'SYSTEM SETTINGS ' => array( 
		'tabtitle' =>  __('System Settings', 'fakturo' ),
		'item1' => array( 
			'title' => __('Concept','fakturo'),
			'tip' => __('In this section you can choose the default system values that will be used during the process. Some examples of the values you can choose are: the currency, the default invoice type, the point of sale, the price scale, the default code for the invoices, the type of payment and even the entity bankary and the tax of your preference.', 'fakturo' ).
				'<br><br>'.__('It is very important to clarify that some of these values may be modified in the different stages of the process.', 'fakturo' ),
		),

	),
	'FIELDS ' => array( 
		'tabtitle' =>  __('Fields', 'fakturo' ),
		'item1' => array( 
			'title' => __('Currency Symbol Position','fakturo'),
			'tip' => __('Select the location of the currency symbol to either before or after the number value.','fakturo'),
		),
		'item2' => array( 
			'title' => __('Thousands Separator','fakturo'),
			'tip' => __('Enter the symbol with which you want to separate the thousands place; it is usually either a comma (,) or a period (.).','fakturo'),
		),
		'item3' => array( 
			'title' => __('Decimal Separator','fakturo'),
			'tip' => __(' Symbol used to separate decimals; it is usually either a comma (,) or a period (.).','fakturo'),
		),
		'item4' => array( 
			'title' => __('Decimal Places','fakturo'),
			'tip' => __('Maximum number of decimal places that the money value to be managed will have.','fakturo'),
		),
		'item5' => array( 
			'title' => __('Default Invoice Type','fakturo'),
			'tip' => __('When selecting the invoice, choose the option used in your billing system.','fakturo'),
		),
		'item6' => array( 
			'title' => __('Default Scale Price','fakturo'),
			'tip' => __('Default scale for prices used in the system.','fakturo'),
		),
		'item7' => array( 
			'title' => __('Point of Sale','fakturo'),
			'tip' => __('Select one of the registered points of sale, which will be used in your billing system.','fakturo'),
		),
		'item8' => array( 
			'title' => __('Receipt Number Digits: ','fakturo'),
			'tip' => __('Number of digits that the receipt code will have in the system.','fakturo'),
		),
		'item9' => array( 
			'title' => __('Invoice Number Digits','fakturo'),
			'tip' => __('Number of digits that the invoice code will have in the system.','fakturo'),
		),
		'item10' => array( 
			'title' => __('Default Invoice Code','fakturo'),
			'tip' => __("Select a default code that the invoice will carry when issued. It may be the reference code, internal code or manufacturer's code.",'fakturo'),
		),
		'item11' => array( 
			'title' => __('Default Date Format','fakturo'),
			'tip' => __('Select the format for the date on the invoice. For example: Day-Month-Year or Day/Month/Year.','fakturo').'<br>'.__('Click on <b>"'.__('Save Changes').'"</b> to save your settings.','fakturo')
		),
	),
);

?>