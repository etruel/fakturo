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
	'PRINT FORMATS' => array(
		'tabtitle' => __('Print Formats', 'fakturo'),
		'item1' => array(
			'title' => __('Print Templates', 'fakturo'),
			'tip' => __('They are used to format the different documents to be printed, such as invoices, receipts, reports, etc.', 'fakturo'),
			'plustip' => __('Standard HTML is used with the system variables in the format used by the RainTPL library.', 'fakturo')
		),
		'item2' => array(
			'title' => __('Reset to Default', 'fakturo'),
			'tip' => __('Fakturo includes standard templates for almost all the documents handled by it.', 'fakturo') . '<br />' .
			__('You can add the default print templates after assigning it to a specific module and entering the template name and description.', 'fakturo')
		),
		'item3' => array(
			'title' => __('Preview & See PDF buttons', 'fakturo'),
			'tip' => __('They are used to preview the template with any randomly loaded document, in a browser window or in PDF format depending on the button.', 'fakturo'),
			'plustip' => __('There must be at least one document of the type of the print template to be previewed.', 'fakturo')
		),
		'item4' => array(
			'title' => __('Print Template Vars', 'fakturo'),
			'tip' => __('Displays the list of all variables available for use in printing the template.', 'fakturo')
		),
	),
	'RAINTPL EXAMPLES' => array(
		'tabtitle' => __('RainTPL Examples', 'fakturo'),
		'item1' => array(
			'title' => __('Functions, Variables & Keywords', 'fakturo'),
			'tip' => '          

			<div style="margin-bottom: 1.5em;">
				<p style="margin-bottom: .5em"><strong>Variable example</strong>
				{* all code between noparse tags is not compiled *}</p>
				<tt style="display: block; background-color: #dfe7f1; padding: 10px; max-width: max-content;">variable {noparse}{$variable}{/noparse} = <b>{$variable}</b></tt>
			</div>
			

			<div style="margin-bottom: 1.5em;">
				<p style="margin-bottom: .5em"><strong>Variable assignment</strong></p>
				<tt style="display: block; background-color: #dfe7f1; padding: 10px; max-width: max-content;">assignment {$number=10} and print {$number}</tt>
			</div>
			
			<div style="margin-bottom: 1.5em;">
				<p style="margin-bottom: .5em"><strong>Operation with strings</strong></p>
				<tt style="display: block; background-color: #dfe7f1; padding: 10px; max-width: max-content;">
				{$variable . $number}<br/>
				{$number + 20}<br/>
				</tt>
			</div>
			
			<div style="margin-bottom: 1.5em;">
				<p style="margin-bottom: .5em"><strong>Variable Modifiers</strong></p>
				<tt style="display: block; background-color: #dfe7f1; padding: 10px; max-width: max-content;">
					{$variable|substr:0,7}<br/>
					a modifier on string: {"hello world"|strtoupper}<br/>
				</tt>
			</div>
			
			<div style="margin-bottom: 1.5em;">
				<p style="margin-bottom: .5em"><strong>Global variables</strong></p>
				<tt style="display: block; background-color: #dfe7f1; padding: 10px; max-width: max-content;">The variable is declared as global into the PHP {$GLOBALS.global_variable}</tt>
			</div>
			
			<div style="margin-bottom: 1.5em;">
				<p style="margin-bottom: .5em"><strong>Show all declared variables</strong>
				To show all declared variable use {noparse}{$template_info}{/noparse}.</p>
				<tt style="display: block; background-color: #dfe7f1; padding: 10px; max-width: max-content;">
					{$template_info}<br/>
				</tt>
			</div>
			
			<div style="margin-bottom: 1.5em;">
				<p style="margin-bottom: .5em"><strong>Constant</strong></p>
				<tt style="display: block; background-color: #dfe7f1; padding: 10px; max-width: max-content;">Constant: {#true#}</tt>
			</div>
			
			<div style="margin-bottom: 1.5em;">
				<p style="margin-bottom: .5em"><strong>Modier on constant as follow</strong></p>
				<tt style="display: block; background-color: #dfe7f1; padding: 10px; max-width: max-content;">Negation of false is true: {PHP_VERSION|round}</tt>
			</div>

			<div style="margin-bottom: 1.5em;">
				<p style="margin-bottom: .5em"><strong>Simple loop example</strong></p>
				<tt style="display: block; background-color: #dfe7f1; padding: 10px; max-width: max-content;">
				{loop="week"}<br/>
					<span style="padding-left: 20px;">{$key} = {$value}</span><br/>
				{/loop}
				</tt>
			</div>

			<div style="margin-bottom: 1.5em;">
				<p style="margin-bottom: .5em"><strong>Loop example with associative array</strong></p>
				<tt style="display: block; background-color: #dfe7f1; padding: 10px; max-width: max-content;">
					ID _ Name _ Color<br/>
					{loop="user"}<br/>
						<span class="color{$counter%2+1}" style="padding-left: 20px;">{$key}) - {$value.name|strtoupper} - {$value.color}</span><br/>
					{/loop}<br/>
				</tt>
			</div>

			<div style="margin-bottom: 1.5em;">
				<p style="margin-bottom: .5em"><strong>Loop an empty array</strong></p>
				<tt style="display: block; background-color: #dfe7f1; padding: 10px; max-width: max-content;">
					{loop="empty_array"}<br/>
						<span class="color{$counter%2+1}" style="padding-left: 20px;">{$key}) - {$value.name} - {$value.color}</span><br/>
					{else}<br/>
						<b>The array is empty</b>
					{/loop}
				</tt>
			</div>
			
			<div style="margin-bottom: 1.5em;">
				<p style="margin-bottom: .5em"><strong>simple if example</strong></p>
				<tt style="display: block; background-color: #dfe7f1; padding: 10px; max-width: max-content;">
					{if="$number==10"}OK!<br/>
					{else}NO!{/if}
				</tt>
			</div>
			
			<div style="margin-bottom: 1.5em;">
				<p style="margin-bottom: .5em"><strong>example of if, elseif, else example</strong></p>
				<tt style="display: block; background-color: #dfe7f1; padding: 10px; max-width: max-content;">
					{if="substr($variable,0,1)==\'A\'"}<br>
					<span style="padding-left: 20px;">First character is A</span><br>
					{elseif="substr($variable,0,1)==\'B\'"}<br>
					<span style="padding-left: 20px;">First character is B</span><br>
					{else}<br>
					<span style="padding-left: 20px;">First character of variable is not A neither B</span><br>
					{/if}
				</tt>
			</div>
			
			<div style="margin-bottom: 1.5em;">
				<p style="margin-bottom: .5em"><strong>use of ? : operator (number==10?\'OK!\':\'no\')</strong>
				You can also use the ? operator instead of if</p>
				<tt style="display: block; background-color: #dfe7f1; padding: 10px; max-width: max-content;">{$number==10? \'OK!\' : \'no\'}</tt>
			</div>
			
			<div style="margin-bottom: 1.5em;">
				<p style="margin-bottom: .5em"><strong>Example of function: ucfirst(strtolower($title))</strong></p>
				<tt style="display: block; background-color: #dfe7f1; padding: 10px; max-width: max-content;">{function="ucfirst(strtolower($title))"}</tt>
			</div>
			',
		),
	),
);
?>
