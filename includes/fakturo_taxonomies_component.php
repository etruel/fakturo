<?php

add_action( 'admin_init', 'fakturo_admin_init' );

function fakturo_admin_init() {
	register_taxonomy(
	    'fakturo_user_template',
	    '',
    	array(
			'label' => 'User Template',
			'rewrite' => array(
				'slug' => 'fakturo-user-template'
			)
		)
	);
	register_taxonomy(
	    'fakturo_print_template',
	    '',
    	array(
			'label' => 'Print Template',
			'rewrite' => array(
				'slug' => 'fakturo-print-template'
			)
		)
	);
	register_taxonomy(
	    'fakturo_currency',
	    '',
    	array(
			'label' => 'Currency',
			'rewrite' => array(
				'slug' => 'fakturo-currency'
			)
		)
	);
   register_taxonomy(
       'fakturo_taxes',
       '',
      array(
         'label' => 'Taxes',
         'rewrite' => array(
            'slug' => 'fakturo-taxes'
         )
      )
   );
   register_taxonomy(
       'fakturo_tax_condition',
       '',
      array(
         'label' => 'Tax Condition',
         'rewrite' => array(
            'slug' => 'fakturo-tax-condition'
         )
      )
   );
   register_taxonomy(
       'fakturo_invoice_type',
       '',
      array(
         'label' => 'Invoice Type',
         'rewrite' => array(
            'slug' => 'fakturo-invoice-type'
         )
      )
   );
   register_taxonomy(
       'fakturo_bank_entities',
       '',
      array(
         'label' => 'Bank Entities',
         'rewrite' => array(
            'slug' => 'fakturo-bank-entities'
         )
      )
   );
   register_taxonomy(
       'fakturo_payment_types',
       '',
      array(
         'label' => 'Payment Types',
         'rewrite' => array(
            'slug' => 'fakturo-payment-types'
         )
      )
   );
   register_taxonomy(
       'fakturo_repairs_status',
       '',
      array(
         'label' => 'Repairs Status',
         'rewrite' => array(
            'slug' => 'fakturo-repairs-status'
         )
      )
   );
   register_taxonomy(
       'fakturo_packagings',
       '',
      array(
         'label' => 'Packagings',
         'rewrite' => array(
            'slug' => 'fakturo-packagings'
         )
      )
   );
   register_taxonomy(
       'fakturo_price_scales',
       '',
      array(
         'label' => 'Price Scales',
         'rewrite' => array(
            'slug' => 'fakturo-price-scales'
         )
      )
   );
   register_taxonomy(
       'fakturo_product_types',
       '',
      array(
         'label' => 'Product Types',
         'rewrite' => array(
            'slug' => 'fakturo-product-types'
         )
      )
   );
   register_taxonomy(
       'fakturo_locations',
       '',
      array(
         'label' => 'Locations',
         'rewrite' => array(
            'slug' => 'fakturo-locations'
         )
      )
   );
   register_taxonomy(
       'fakturo_origins',
       '',
      array(
         'label' => 'Origins',
         'rewrite' => array(
            'slug' => 'fakturo-origins'
         )
      )
   );
   register_taxonomy(
       'fakturo_countries',
       '',
      array(
         'label' => 'Countries',
         'rewrite' => array(
            'slug' => 'fakturo-countries'
         )
      )
   );
   register_taxonomy(
       'fakturo_states',
       '',
      array(
         'label' => 'States',
         'rewrite' => array(
            'slug' => 'fakturo-states'
         )
      )
   );
   register_taxonomy(
       'fakturo_emails',
       '',
      array(
         'label' => 'Email',
         'rewrite' => array(
            'slug' => 'fakturo-emails'
         )
      )
   );
}

?>