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
			<p><strong>Variable example</strong>
			{* all code between noparse tags is not compiled *}<br/>
			<tt>variable {noparse}{$variable}{/noparse} = <b>{$variable}</b></tt>
			</p>

			<p><strong>Variable assignment</strong>
			<tt>assignment {$number=10} and print {$number}</tt>
			</p>
			
			<p><strong>Operation with strings</strong>
			<tt>
				{$variable . $number}<br/>
				{$number + 20}<br/>
			</tt>
			</p>
			
			<p><strong>Variable Modifiers</strong>
			<tt>
				{$variable|substr:0,7}<br/>
				a modifier on string: {"hello world"|strtoupper}<br/>
			</tt>
			</p>
			
			<p><strong>Global variables</strong>
			<tt>The variable is declared as global into the PHP {$GLOBALS.global_variable}</tt>
			</p>
			
			<p><strong>Show all declared variables</strong>
			To show all declared variable use {noparse}{$template_info}{/noparse}.<br/>
			<tt>
				{$template_info}<br/>
			</tt>
			</p>
			
			<p><strong>Constant</strong>
			<tt>Constant: {#true#}</tt>
			</p>
			
			<p><strong>Modier on constant as follow</strong>
			<tt>Negation of false is true: {PHP_VERSION|round}</tt>
			</p>

			<p><strong>Simple loop example</strong>
			<tt>
			<ul>
			{loop="week"}<br/>
				<li>{$key} = {$value}</li>
			{/loop}
			</ul>
			</tt>
			</p>

			<p><strong>Loop example with associative array</strong>
			<tt>
			<ul>
				<li>ID _ Name _ Color</li>
				{loop="user"}<br/>
					<li class="color{$counter%2+1}">{$key}) - {$value.name|strtoupper} - {$value.color}</li>
				{/loop}<br/>
			</ul>
			</tt>
			</p>

			<p><strong>Loop an empty array</strong>
			<tt>
			<ul>
				{loop="empty_array"}<br/>
					<li class="color{$counter%2+1}">{$key}) - {$value.name} - {$value.color}</li>
				{else}<br/>
					<b>The array is empty</b>
				{/loop}<br/>
			</ul>
			</tt>
			</p>
			
			<p><strong>simple if example</strong>
			<tt>
			{if="$number==10"}OK!<br/>
			{else}NO!{/if}<br/>
			</tt>
			</p>
			
			<p><strong>example of if, elseif, else example</strong>
			<tt>
			{if="substr($variable,0,1)==\'A\'"}First character is A<br>
			{elseif="substr($variable,0,1)==\'B\'"}First character is B<br>
			{else}First character of variable is not A neither B<br>
			{/if}
			</tt>
			</p>
			
			<p><strong>use of ? : operator (number==10?\'OK!\':\'no\')</strong>
			You can also use the ? operator instead of if<br/>
			<tt>{$number==10? \'OK!\' : \'no\'}</tt>
			</p>
			
			<p><strong>Example of function: ucfirst(strtolower($title))</strong>
			<tt>{function="ucfirst(strtolower($title))"}</tt>
			</p>
			',
		),
	),
);
?>
