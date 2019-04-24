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
    'SALE' => array(
        'tabtitle' => __('Sales screen', 'fakturo'),
        'item1' => array(
            'title' => __('Invoices List', 'fakturo'),
            'tip' => __('This screen displays the list of credit and debit invoices related to sales.', 'fakturo'),
            'plustip' => __('To create a new sale, you can click the button <b>"Add New"</b> and fill in the appropriate form. ', 'fakturo')
        )
    ),
    'quickactions' => array(
        'tabtitle' => __('Quick Actions', 'fakturo'),
        'item1' => array(
            'title' => __('Quick actions to each invoice', 'fakturo'),
            'tip' => __('When you hover the mouse over each invoice row, it will show below several actions that can be done by clicking on each one.', 'fakturo'),
            'plustip' => __('The actions available will change depending on whether the invoice is in Finished or Pending status.', 'fakturo')
        ),
        'item2' => array(
            'title' => __('Pending or Draft status', 'fakturo'),
            'tip' => __('Edit | Open invoice editing screen to make the necessary changes to finish the invoice.'.'<br />'.
                         'Delete | Remove the invoice from database permanently.  This action has no reverse.'.'<br />'.
                         'Preview | Opens a pop-up and shows a Demo of the pending invoice as it will look when finished.', 'fakturo')
        ),
        'item3' => array(
            'title' => __('Finished status', 'fakturo'),
            'tip' => __('View | Open invoice editing screen to see the data used in the invoice.'.'<br />'.
                         'Print | Opens a popup with a Print dialog box to send the invoice to the printer. Cancel the Print dialog to view the invoice on screen.'.'<br />'.
                         'email to Client | Send the email to the customer in "Sales Email Template" format with the invoice attached as a PDF.', 'fakturo'),
            'plustip' => __('The finished invoices can\'t be deleted or modified.  To null one a credit invoice should be created.', 'fakturo')
        ),
    ),
    'BulkActions' => array(
        'tabtitle' => __('Bulk Actions', 'fakturo'),
        'item1' => array(
            'title' => __('Editing in bulk', 'fakturo'),
            'tip' => __('Select who will receive the invoice from the customer list. If they are not found, then you must register the customer on the "customers" form. If the customer does exist and doesn’t appear, verify that the option "ACTIVE CUSTOMER" is selected in the customer form', 'fakturo')
        ),
    ),
    'INVOICE' => array(
        'tabtitle' => __('Columns', 'fakturo'),
        'item1' => array(
            'title' => __('Customer', 'fakturo'),
            'tip' => __('Select who will receive the invoice from the customer list. If they are not found, then you must register the customer on the "customers" form. If the customer does exist and doesn’t appear, verify that the option "ACTIVE CUSTOMER" is selected in the customer form', 'fakturo')),
        'item2' => array(
            'title' => __('Invoice Type', 'fakturo'),
            'tip' => __('Select the type of invoice to be made from the list; it can be INVOICE A, B, C or credit note.', 'fakturo')),
        'item3' => array(
            'title' => __('Invoice Number', 'fakturo'),
            'tip' => __('Invoice identifier which is randomly generated and is read-only. ', 'fakturo')),
        'item4' => array(
            'title' => __('Date', 'fakturo'),
            'tip' => __('This is according the date when the product was bought or sold', 'fakturo')),
        'item5' => array(
            'title' => __('Invoice Currency', 'fakturo'),
            'tip' => __('Currency which will be displayed on the invoice. It can be Argentinian peso, dollars or Euros', 'fakturo')),
        'item7' => array(
            'title' => __('Vendor', 'fakturo'),
            'tip' => __('Person who manages the invoice', 'fakturo')),
    ),
);
?>