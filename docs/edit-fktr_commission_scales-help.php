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
	'WHAT ARE COMMISSIONS' => array( 
		'tabtitle' =>  __('What is this?', 'fakturo' ),
		'item1' => array( 
			'title' => __('Commission scales','fakturo'),
			'tip' => __('A commission is the part of the sales that a salesperson earns. Here you write the rules: how much each salesperson earns according to how much they sold. Fakturo applies those rules and shows the result in <b>Reports &rarr; Salesmen</b>.', 'fakturo' ),
			'plustip' => __('Fakturo only calculates. It does not pay anybody and it does not save the result: every time you open the report it is calculated again with the invoices of the period you choose. You can export it to PDF or CSV.', 'fakturo' ),
		),
		'item2' => array( 
			'title' => __('Before you start','fakturo'),
			'tip' => __('The switch <b>Activate commissions for salespeople</b> in Settings &rarr; System must be on, otherwise this screen is not shown. The salesperson of an invoice is chosen when the invoice is made.', 'fakturo' ),
		),
	),
	'HOW IT IS CALCULATED' => array( 
		'tabtitle' =>  __('How it is calculated', 'fakturo' ),
		'item1' => array( 
			'title' => __('Step by step','fakturo'),
			'tip' => __('1. You choose the period in the report (this month, last year, custom dates...).<br>2. For each salesperson Fakturo counts the <b>finished invoices</b> of that period (drafts do not count) and adds up their totals, less the credit notes.<br>3. It looks for the scale of that salesperson: the one with their name, or, if they have none, the <b>Base scale</b>.<br>4. Inside that scale it looks for the range that fits the <b>number of invoices</b> the salesperson made in the period.<br>5. It applies the percentage of that range to the <b>whole</b> total sold in the period.', 'fakturo' ),
		),
		'item2' => array( 
			'title' => __('Example','fakturo'),
			'tip' => __('Base scale: from 1 to 10 invoices = 3%, from 11 to 20 = 5%, from 21 with no end = 8%.<br>Ana made 12 invoices in the month and sold 24,000 in total. 12 is in the range 11 to 20, so she earns 5% of <b>all</b> 24,000 = 1,200. It is not 3% on the first 10 invoices and 5% on the rest: the percentage of the range applies to everything.<br>If Ana made 25 invoices she would earn 8% of everything she sold.', 'fakturo' ),
		),
		'item3' => array( 
			'title' => __('The warning sign in the report','fakturo'),
			'tip' => __('A salesperson shown with the commission in 0 and a warning sign has no scale, or their number of invoices is not inside any range. Add a scale or fix the ranges.', 'fakturo' ),
		),
	),
	'FIELDS' => array( 
		'tabtitle' =>  __('Fields', 'fakturo' ),
		'item1' => array( 
			'title' => __('Name','fakturo'),
			'tip' => __('A name for you to recognize the scale, for example "Base" or "Ana - special".','fakturo'),
		),
		'item2' => array( 
			'title' => __('Scale Type','fakturo'),
			'tip' => __('<b>Base Scale</b>: it is used for every salesperson that has no scale of their own. Only one is allowed: Fakturo does not let you create a second one.<br><b>Exception Scale</b>: for one particular salesperson. Choose their name in <i>Assigned Salesperson</i> and they will use this scale instead of the base one.','fakturo'),
		),
		'item3' => array( 
			'title' => __('Assigned Salesperson','fakturo'),
			'tip' => __('Who this scale is for. It appears when the type is Exception Scale (the Base Scale is for everybody). Choose the salesperson: if you leave it empty the scale is never used. Only people who can make invoices appear in the list.','fakturo'),
		),
		'item4' => array( 
			'title' => __('Category','fakturo'),
			'tip' => __('It is kept for commissions by product category, but at the moment the report does not use it. Leave it empty.','fakturo'),
		),
		'item5' => array( 
			'title' => __('Commission Ranges','fakturo'),
			'tip' => __('Each row is a range: <b>From</b> and <b>To</b> are a number of invoices in the period and <b>%</b> is the percentage of the sales that the salesperson earns. Press <b>+</b> to add a row and <b>-</b> to remove it.<br>Leave <b>To</b> empty in the last row: it means "or more".<br>Start the first range in 1 and make each range start right after the previous one ends (1 to 10, 11 to 20, 21 and more). If there is a gap, a salesperson whose number of invoices falls in it earns nothing.','fakturo'),
		),
	),
	'GOOD TO KNOW' => array( 
		'tabtitle' =>  __('Good to know', 'fakturo' ),
		'item1' => array( 
			'title' => __('What is added up','fakturo'),
			'tip' => __('The total of each invoice with its taxes, converted to the default currency of the system with the rates of your currencies. A credit note is not an invoice sold: it is not counted in the number of invoices and its total is subtracted from what the salesperson sold.','fakturo'),
		),
		'item2' => array( 
			'title' => __('Changing a scale','fakturo'),
			'tip' => __('Since the report is calculated when you open it, a change in a scale also changes the commissions of past periods when you look at them again. Export the report if you need to keep the figures of a closed month.','fakturo'),
		),
	),
);
