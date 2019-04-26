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
            'plustip' => __('The finished invoices can\'t be deleted or modified.  To null one should be create a credit invoice.', 'fakturo')
        ),
    ),
    'BulkActions' => array(
        'tabtitle' => __('Bulk Actions', 'fakturo'),
        'item1' => array(
            'title' => __('Doing in bulk', 'fakturo'),
            'tip' => __('Bulk actions are performed on the items selected by checking the first column of each row.', 'fakturo')
        ),
        'item2' => array(
            'title' => __('Send PDF to clients', 'fakturo'),
            'tip' => __('Send all the invoices at once attached as PDF by email to each client.', 'fakturo'),
            'plustip' => __('1. Select the (finished) invoices you want to send to each respective client.', 'fakturo').'<br />'.
                        __('2. Select "Send PDF to clients" in the "Bulk Actions" select field.', 'fakturo').'<br />'.
                        __('3. Click "Apply" button to send each invoice as PDF attached to emails sent to each client.', 'fakturo')
        ),
    ),
    'INVOICE' => array(
        'tabtitle' => __('Columns', 'fakturo'),
        'item0' => array(
            'title' => __('Columns Tips', 'fakturo'),
            'tip' => __('The columns can be hidden or showed in "Screen Options" Tab.', 'fakturo').'<br />'.
                     __('The list can be ordered by clicking in each column title available to order.', 'fakturo')
            ),
        'item1' => array(
            'title' => __('Title', 'fakturo'),
            'tip' => __('The invoice number created with the invoice fields with the format selected in System Settings.', 'fakturo')
            ),
        'item2' => array(
            'title' => __('Client', 'fakturo'),
            'tip' => __('The client of the invoice.', 'fakturo')
            ),
        'item3' => array(
            'title' => __('Payment Status', 'fakturo'),
            'tip' => __('Shows the receipts numbers with the amounts imputed to the invoices or only Unpaid if there are none.', 'fakturo')
            ),
        'item4' => array(
            'title' => __('Date', 'fakturo'),
            'tip' => __('This is the date when the invoice was created.', 'fakturo')
            ),
    ),
);
?>