<div class="wrap">
<?php fakturo_admin_tabs_section(); ?>
	<form class="fakturo-settings-form" method="post" <?php if ($section == 'company_info') { echo 'action="options.php" enctype="multipart/form-data"'; } else { echo 'action=""';} ?> >
<?php
wp_nonce_field( "ilc-settings-page" ); 

if ( $_GET['page'] == 'fakturo/settings/fakturo_settings.php' ){
   echo '<table class="form-table fakturo-settings">';
   switch ( $section ){
      case 'user-template' :
         ?>
         <tr>
            <th><?php echo __( 'User Template Settings', FAKTURO_TEXT_DOMAIN ); ?></th>
         </tr>
         <tr>
            <td><?php echo __( 'Enable User Template', FAKTURO_TEXT_DOMAIN ); ?></td>
            <td><input type="checkbox" name="enable_user_template" value="1" <?php if (get_option('fakturo_enable_user_template') == TRUE) { ?> checked <?php } ?> ></td>
         </tr>
         <tr>            
            <table class="wp-list-table widefat fixed fakturo-setting">
              <tr>
                <p><?php echo __( 'Enter User Template.', FAKTURO_TEXT_DOMAIN ); ?></p>
              </tr>
              <tr>
                <th class="manage-column column-title column-primary"><?php echo __( 'Name', FAKTURO_TEXT_DOMAIN ); ?></th>
                <th><?php echo __( 'Remove', FAKTURO_TEXT_DOMAIN ); ?></th>
              </tr>
              <?php 
                $dataSetting = get_terms('fakturo_user_template','hide_empty=0');
                printSettingRowSimpleTaxonomy($dataSetting, 'tables' ,'user-template', 'user_template_delete'); 
              ?>
              <tr>
                <td><input type="text" name="user_template_name"></td>
              </tr>
            </table>
            <br/>
            <button type="submit" name="Submit"  class="button" value="add_user_template"><?php echo __( 'Add User Template', FAKTURO_TEXT_DOMAIN ); ?></button>
         </tr>
         <?php
      break;

      case 'print-template' :

        switch ($action) {
          case 'add':
            require_once('views/print_template_add.php');
            break;

          case 'edit':
            require_once('views/print_template_edit.php');
            break;
          
          default:
            require_once('views/print_template_list.php');
            break;
        }
         ?>          
         <?php
      break;

      case 'emails' :

        switch ($action) {
          case 'add':
            require_once('views/emails_add.php');
            break;

          case 'edit':
            require_once('views/emails_edit.php');
            break;
          
          default:
            require_once('views/emails_list.php');
            break;
        }
         ?>          
         <?php
      break;

      case 'currencies' :
         ?>
          <tr>
            <th><?php echo __( 'Currencies Settings', FAKTURO_TEXT_DOMAIN ); ?></th>
          </tr>
          <tr>
            <td><?php echo __( 'Enable Currencies', FAKTURO_TEXT_DOMAIN ); ?></td>
            <td><input type="checkbox" name="enable_currency" value="1" <?php if (get_option('fakturo_enable_currency') == TRUE) { ?> checked <?php } ?> ></td>
          </tr>
          <tr>
            
            <table class="wp-list-table widefat fixed fakturo-setting">
              <tr>
                <p><?php echo __( 'Enter Currency.', FAKTURO_TEXT_DOMAIN ); ?></p>
              </tr>
              <tr>
                <th class="manage-column column-title column-primary"><?php echo __( 'Name', FAKTURO_TEXT_DOMAIN ); ?></th>
                <th class="manage-column column-title column-primary"><?php echo __( 'Symbol', FAKTURO_TEXT_DOMAIN ); ?></th>
                <th class="manage-column column-title column-primary"><?php echo __( 'Rate', FAKTURO_TEXT_DOMAIN ); ?></th>
                <th class="manage-column column-title column-primary"><?php echo __( 'Reference', FAKTURO_TEXT_DOMAIN ); ?></th>
                <th class="manage-column column-title column-primary"><?php echo __( 'Default', FAKTURO_TEXT_DOMAIN ); ?></th>
                <th class="manage-column column-title column-primary"><?php echo __( 'Plural', FAKTURO_TEXT_DOMAIN ); ?></th>
                <th><?php echo __( 'Remove', FAKTURO_TEXT_DOMAIN ); ?></th>
              </tr>
              <?php 
                $dataSetting = get_terms('fakturo_currency','hide_empty=0');
                printSettingRowCurrencyTaxonomy($dataSetting, 'general', 'currencies', 'currency_delete'); 
              ?>
              <tr>
                <td><input type="text" name="currency_name"></td>
                <td><input type="text" name="currency_symbol"></td>
                <td><input type="text" name="currency_rate"></td>
                <td><input type="text" name="currency_reference"></td>
                <td>
                  <input type="checkbox" value="1" name="currency_default" />
                </td>
                <td><input type="text" name="currency_plural"></td>
              </tr>
            </table>
            <br/>
            <button type="submit" name="Submit"  class="button" value="add_currency"><?php echo __( 'Add Currency', FAKTURO_TEXT_DOMAIN ); ?></button>
          </tr>
         <?php
      break;

      case 'taxes' :
         ?>
          <tr>
            <th><?php echo __( 'Taxes Settings', FAKTURO_TEXT_DOMAIN ); ?></th>
          </tr>
          <tr>
            <td><?php echo __( 'Enable Taxes', FAKTURO_TEXT_DOMAIN ); ?></td>
            <td><input type="checkbox" name="enable_taxes" value="1" <?php if (get_option('fakturo_enable_taxes') == TRUE) { ?> checked <?php } ?> ></td>
          </tr>
          <tr>
            
            <table class="wp-list-table widefat fixed fakturo-setting">
              <tr>
                <p><?php echo __( 'Enter Taxes.', FAKTURO_TEXT_DOMAIN ); ?></p>
              </tr>
              <tr>
                <th class="manage-column column-title column-primary"><?php echo __( 'Name', FAKTURO_TEXT_DOMAIN ); ?></th>
                <th class="manage-column column-title column-primary"><?php echo __( 'Percent', FAKTURO_TEXT_DOMAIN ); ?></th>
                <th><?php echo __( 'Remove', FAKTURO_TEXT_DOMAIN ); ?></th>
              </tr>
              <?php 
                $dataSetting = get_terms('fakturo_taxes','hide_empty=0');
                printSettingRowTaxesTaxonomy($dataSetting, 'taxes','taxes', 'taxes_delete'); 
              ?>
              <tr>
                <td><input type="text" name="taxes_name"></td>
                <td><input type="text" name="taxes_percent"></td>
              </tr>
            </table>
            <br/>
            <button type="submit" name="Submit"  class="button" value="add_taxes"><?php echo __( 'Add Tax', FAKTURO_TEXT_DOMAIN ); ?></button>
          </tr>
         <?php
      break;

      case 'tax_condition' :
         ?>
          <tr>
            <th><?php echo __( 'Tax Condition Settings', FAKTURO_TEXT_DOMAIN ); ?></th>
          </tr>
          <tr>
            <td><?php echo __( 'Enable Tax Condition', FAKTURO_TEXT_DOMAIN ); ?></td>
            <td><input type="checkbox" name="enable_tax_condition" value="1" <?php if (get_option('fakturo_enable_tax_condition') == TRUE) { ?> checked <?php } ?> ></td>
          </tr>
          <tr>
            
            <table class="wp-list-table widefat fixed fakturo-setting">
              <tr>
                <p><?php echo __( 'Enter Tax Condition.', FAKTURO_TEXT_DOMAIN ); ?></p>
              </tr>
              <tr>
                <th class="manage-column column-title column-primary"><?php echo __( 'Name', FAKTURO_TEXT_DOMAIN ); ?></th>
                <th><?php echo __( 'Remove', FAKTURO_TEXT_DOMAIN ); ?></th>
              </tr>
              <?php 
                $dataSetting = get_terms('fakturo_tax_condition','hide_empty=0');
                printSettingRowSimpleTaxonomy($dataSetting, 'taxes', 'tax_condition', 'tax_condition_delete'); 
              ?>
              <tr>
                <td><input type="text" name="tax_condition_name"></td>
              </tr>
            </table>
            <br/>
            <button type="submit" name="Submit"  class="button" value="add_tax_condition"><?php echo __( 'Add Tax Condition', FAKTURO_TEXT_DOMAIN ); ?></button>
          </tr>
         <?php
      break;

      case 'invoice_type' :
         ?>
          <tr>
            <th><?php echo __( 'Invoice Type Settings', FAKTURO_TEXT_DOMAIN ); ?></th>
          </tr>
          <tr>
            <td><?php echo __( 'Enable Invoice Type', FAKTURO_TEXT_DOMAIN ); ?></td>
            <td><input type="checkbox" name="enable_invoice_type" value="1" <?php if (get_option('fakturo_enable_invoice_type') == TRUE) { ?> checked <?php } ?> ></td>
          </tr>
          <tr>
            
            <table class="wp-list-table widefat fixed fakturo-setting">
              <tr>
                <p><?php echo __( 'Enter Invoice Type.', FAKTURO_TEXT_DOMAIN ); ?></p>
              </tr>
              <tr>
                <th class="manage-column column-title column-primary"><?php echo __( 'Name', FAKTURO_TEXT_DOMAIN ); ?></th>
                <th class="manage-column column-title column-primary"><?php echo __( 'Short Name', FAKTURO_TEXT_DOMAIN ); ?></th>
                <th class="manage-column column-title column-primary"><?php echo __( 'Discriminates Taxes', FAKTURO_TEXT_DOMAIN ); ?></th>
                <th class="manage-column column-title column-primary"><?php echo __( 'Default', FAKTURO_TEXT_DOMAIN ); ?></th>
                <th class="manage-column column-title column-primary"><?php echo __( 'Sum', FAKTURO_TEXT_DOMAIN ); ?></th>
                <th><?php echo __( 'Remove', FAKTURO_TEXT_DOMAIN ); ?></th>
              </tr>
              <?php 
                $dataSetting = get_terms('fakturo_invoice_type','hide_empty=0');
                printSettingRowInvoiceTypeTaxonomy($dataSetting, 'general','invoice_type', 'invoice_type_delete'); 
              ?>
              <tr>
                <td><input type="text" name="invoice_type_name"></td>
                <td><input type="text" name="invoice_type_short_name"></td>
                <td>
                  <input type="checkbox" value="1" name="invoice_type_taxes" />
                </td>
                <td>
                  <input type="checkbox" value="1" name="invoice_type_default" />
                </td>
                <td>
                  <input type="checkbox" value="1" name="invoice_type_sum" />
                </td>
              </tr>
            </table>
            <br/>
            <button type="submit" name="Submit"  class="button" value="add_invoice_type"><?php echo __( 'Add Invoice Type', FAKTURO_TEXT_DOMAIN ); ?></button>
          </tr>
         <?php
      break;

      case 'bank_entities' :
         ?>
          <tr>
            <th><?php echo __( 'Bank Entity Settings', FAKTURO_TEXT_DOMAIN ); ?></th>
          </tr>
          <tr>
            <td><?php echo __( 'Enable Bank Entity', FAKTURO_TEXT_DOMAIN ); ?></td>
            <td><input type="checkbox" name="enable_bank_entities" value="1" <?php if (get_option('fakturo_enable_bank_entities') == TRUE) { ?> checked <?php } ?> ></td>
          </tr>
          <tr>
            
            <table class="wp-list-table widefat fixed fakturo-setting">
              <tr>
                <p><?php echo __( 'Enter Bank Entity.', FAKTURO_TEXT_DOMAIN ); ?></p>
              </tr>
              <tr>
                <th class="manage-column column-title column-primary"><?php echo __( 'Name', FAKTURO_TEXT_DOMAIN ); ?></th>
                <th><?php echo __( 'Remove', FAKTURO_TEXT_DOMAIN ); ?></th>
              </tr>
              <?php 
                $dataSetting = get_terms('fakturo_bank_entities','hide_empty=0');
                printSettingRowSimpleTaxonomy($dataSetting, 'tables','bank_entities', 'bank_entities_delete'); 
              ?>
              <tr>
                <td><input type="text" name="bank_entities_name"></td>
              </tr>
            </table>
            <br/>
            <button type="submit" name="Submit"  class="button" value="add_bank_entities"><?php echo __( 'Add Bank Entity', FAKTURO_TEXT_DOMAIN ); ?></button>
          </tr>
         <?php
      break;

      case 'payment_types' :
         ?>
          <tr>
            <th><?php echo __( 'Payment Types Settings', FAKTURO_TEXT_DOMAIN ); ?></th>
          </tr>
          <tr>
            <td><?php echo __( 'Enable Payment Types', FAKTURO_TEXT_DOMAIN ); ?></td>
            <td><input type="checkbox" name="enable_payment_types" value="1" <?php if (get_option('fakturo_enable_payment_types') == TRUE) { ?> checked <?php } ?> ></td>
          </tr>
          <tr>
            
            <table class="wp-list-table widefat fixed fakturo-setting">
              <tr>
                <p><?php echo __( 'Enter Payment Type.', FAKTURO_TEXT_DOMAIN ); ?></p>
              </tr>
              <tr>
                <th class="manage-column column-title column-primary"><?php echo __( 'Name', FAKTURO_TEXT_DOMAIN ); ?></th>
                <th><?php echo __( 'Remove', FAKTURO_TEXT_DOMAIN ); ?></th>
              </tr>
              <?php 
                $dataSetting = get_terms('fakturo_payment_types','hide_empty=0');
                printSettingRowSimpleTaxonomy($dataSetting, 'general','payment_types', 'payment_types_delete'); 
              ?>
              <tr>
                <td><input type="text" name="payment_types_name"></td>
              </tr>
            </table>
            <br/>
            <button type="submit" name="Submit"  class="button" value="add_payment_types"><?php echo __( 'Add Payment Type', FAKTURO_TEXT_DOMAIN ); ?></button>
          </tr>
         <?php
      break;

      case 'repairs_status' :
         ?>
          <tr>
            <th><?php echo __( 'Repairs Status Settings', FAKTURO_TEXT_DOMAIN ); ?></th>
          </tr>
          <tr>
            <td><?php echo __( 'Enable Repairs Status', FAKTURO_TEXT_DOMAIN ); ?></td>
            <td><input type="checkbox" name="enable_repairs_status" value="1" <?php if (get_option('fakturo_enable_repairs_status') == TRUE) { ?> checked <?php } ?> ></td>
          </tr>
          <tr>
            
            <table class="wp-list-table widefat fixed fakturo-setting">
              <tr>
                <p><?php echo __( 'Enter Repairs Status.', FAKTURO_TEXT_DOMAIN ); ?></p>
              </tr>
              <tr>
                <th class="manage-column column-title column-primary"><?php echo __( 'Name', FAKTURO_TEXT_DOMAIN ); ?></th>
                <th><?php echo __( 'Remove', FAKTURO_TEXT_DOMAIN ); ?></th>
              </tr>
              <?php 
                $dataSetting = get_terms('fakturo_repairs_status','hide_empty=0');
                printSettingRowSimpleTaxonomy($dataSetting, 'extensions','repairs_status', 'repairs_status_delete'); 
              ?>
              <tr>
                <td><input type="text" name="repairs_status_name"></td>
              </tr>
            </table>
            <br/>
            <button type="submit" name="Submit"  class="button" value="add_repairs_status"><?php echo __( 'Add Repairs Status', FAKTURO_TEXT_DOMAIN ); ?></button>
          </tr>
         <?php
      break;

      case 'packagings' :
         ?>
          <tr>
            <th><?php echo __( 'Packagings Settings', FAKTURO_TEXT_DOMAIN ); ?></th>
          </tr>
          <tr>
            <td><?php echo __( 'Enable Packagings', FAKTURO_TEXT_DOMAIN ); ?></td>
            <td><input type="checkbox" name="enable_packagings" value="1" <?php if (get_option('fakturo_enable_packagings') == TRUE) { ?> checked <?php } ?> ></td>
          </tr>
          <tr>
            
            <table class="wp-list-table widefat fixed fakturo-setting">
              <tr>
                <p><?php echo __( 'Enter Packagings.', FAKTURO_TEXT_DOMAIN ); ?></p>
              </tr>
              <tr>
                <th class="manage-column column-title column-primary"><?php echo __( 'Name', FAKTURO_TEXT_DOMAIN ); ?></th>
                <th><?php echo __( 'Remove', FAKTURO_TEXT_DOMAIN ); ?></th>
              </tr>
              <?php 
                $dataSetting = get_terms('fakturo_packagings','hide_empty=0');
                printSettingRowSimpleTaxonomy($dataSetting, 'tables','packagings', 'packagings_delete'); 
              ?>
              <tr>
                <td><input type="text" name="packagings_name"></td>
              </tr>
            </table>
            <br/>
            <button type="submit" name="Submit"  class="button" value="add_packagings"><?php echo __( 'Add Packagings', FAKTURO_TEXT_DOMAIN ); ?></button>
          </tr>
         <?php
      break;

      case 'price_scales' :
         ?>
          <tr>
            <th><?php echo __( 'Price Scales Settings', FAKTURO_TEXT_DOMAIN ); ?></th>
          </tr>
          <tr>
            <td><?php echo __( 'Enable Price Scales', FAKTURO_TEXT_DOMAIN ); ?></td>
            <td><input type="checkbox" name="enable_price_scales" value="1" <?php if (get_option('fakturo_enable_price_scales') == TRUE) { ?> checked <?php } ?> ></td>
          </tr>
          <tr>
            
            <table class="wp-list-table widefat fixed fakturo-setting">
              <tr>
                <p><?php echo __( 'Enter Price Scales.', FAKTURO_TEXT_DOMAIN ); ?></p>
              </tr>
              <tr>
                <th class="manage-column column-title column-primary"><?php echo __( 'Name', FAKTURO_TEXT_DOMAIN ); ?></th>
                <th class="manage-column column-title column-primary"><?php echo __( 'Percentage', FAKTURO_TEXT_DOMAIN ); ?></th>
                <th class="manage-column column-title column-primary"><?php echo __( 'Default', FAKTURO_TEXT_DOMAIN ); ?></th>
                <th><?php echo __( 'Remove', FAKTURO_TEXT_DOMAIN ); ?></th>
              </tr>
              <?php 
                $dataSetting = get_terms('fakturo_price_scales','hide_empty=0');
                printSettingRowPriceScalesTaxonomy($dataSetting, 'products','price_scales', 'price_scales_delete'); 
              ?>
              <tr>
                <td><input type="text" name="price_scales_name"></td>
                <td><input type="text" name="price_scales_percent"></td>
                <td>
                  <input type="checkbox" value="1" name="price_scales_default" />
                </td>
              </tr>
            </table>
            <br/>
            <button type="submit" name="Submit"  class="button" value="add_price_scales"><?php echo __( 'Add Price Scales', FAKTURO_TEXT_DOMAIN ); ?></button>
          </tr>
         <?php
      break;

      case 'product_types' :
         ?>
          <tr>
            <th><?php echo __( 'Product Types Settings', FAKTURO_TEXT_DOMAIN ); ?></th>
          </tr>
          <tr>
            <td><?php echo __( 'Enable Product Types', FAKTURO_TEXT_DOMAIN ); ?></td>
            <td><input type="checkbox" name="enable_product_types" value="1" <?php if (get_option('fakturo_enable_product_types') == TRUE) { ?> checked <?php } ?> ></td>
          </tr>
          <tr>
            
            <table class="wp-list-table widefat fixed fakturo-setting">
              <tr>
                <p><?php echo __( 'Enter Product Types.', FAKTURO_TEXT_DOMAIN ); ?></p>
              </tr>
              <tr>
                <th class="manage-column column-title column-primary"><?php echo __( 'Name', FAKTURO_TEXT_DOMAIN ); ?></th>
                <th><?php echo __( 'Remove', FAKTURO_TEXT_DOMAIN ); ?></th>
              </tr>
              <?php 
                $dataSetting = get_terms('fakturo_product_types','hide_empty=0');
                printSettingRowSimpleTaxonomy($dataSetting, 'tables','product_types', 'product_types_delete'); 
              ?>
              <tr>
                <td><input type="text" name="product_types_name"></td>
              </tr>
            </table>
            <br/>
            <button type="submit" name="Submit"  class="button" value="add_product_types"><?php echo __( 'Add Product Types', FAKTURO_TEXT_DOMAIN ); ?></button>
          </tr>
         <?php
      break;

      case 'locations' :
         ?>
          <tr>
            <th><?php echo __( 'Locations Settings', FAKTURO_TEXT_DOMAIN ); ?></th>
          </tr>
          <tr>
            <td><?php echo __( 'Enable Locations', FAKTURO_TEXT_DOMAIN ); ?></td>
            <td><input type="checkbox" name="enable_locations" value="1" <?php if (get_option('fakturo_enable_locations') == TRUE) { ?> checked <?php } ?> ></td>
          </tr>
          <tr>
            
            <table class="wp-list-table widefat fixed fakturo-setting">
              <tr>
                <p><?php echo __( 'Enter Locations.', FAKTURO_TEXT_DOMAIN ); ?></p>
              </tr>
              <tr>
                <th class="manage-column column-title column-primary"><?php echo __( 'Name', FAKTURO_TEXT_DOMAIN ); ?></th>
                <th><?php echo __( 'Remove', FAKTURO_TEXT_DOMAIN ); ?></th>
              </tr>
              <?php 
                $dataSetting = get_terms('fakturo_locations','hide_empty=0');
                printSettingRowSimpleTaxonomy($dataSetting, 'tables','locations', 'locations_delete'); 
              ?>
              <tr>
                <td><input type="text" name="locations_name"></td>
              </tr>
            </table>
            <br/>
            <button type="submit" name="Submit"  class="button" value="add_locations"><?php echo __( 'Add Locations', FAKTURO_TEXT_DOMAIN ); ?></button>
          </tr>
         <?php
      break;

      case 'origins' :
         ?>
          <tr>
            <th><?php echo __( 'Origins Settings', FAKTURO_TEXT_DOMAIN ); ?></th>
          </tr>
          <tr>
            <td><?php echo __( 'Enable Origins', FAKTURO_TEXT_DOMAIN ); ?></td>
            <td><input type="checkbox" name="enable_origins" value="1" <?php if (get_option('fakturo_enable_origins') == TRUE) { ?> checked <?php } ?> ></td>
          </tr>
          <tr>
            
            <table class="wp-list-table widefat fixed fakturo-setting">
              <tr>
                <p><?php echo __( 'Enter Origins.', FAKTURO_TEXT_DOMAIN ); ?></p>
              </tr>
              <tr>
                <th class="manage-column column-title column-primary"><?php echo __( 'Name', FAKTURO_TEXT_DOMAIN ); ?></th>
                <th><?php echo __( 'Remove', FAKTURO_TEXT_DOMAIN ); ?></th>
              </tr>
              <?php 
                $dataSetting = get_terms('fakturo_origins','hide_empty=0');
                printSettingRowSimpleTaxonomy($dataSetting, 'tables','origins', 'origins_delete'); 
              ?>
              <tr>
                <td><input type="text" name="origins_name"></td>
              </tr>
            </table>
            <br/>
            <button type="submit" name="Submit"  class="button" value="add_origins"><?php echo __( 'Add Origins', FAKTURO_TEXT_DOMAIN ); ?></button>
          </tr>
         <?php
      break;

      case 'countries' :
         ?>
          <tr>
            <th><?php echo __( 'Countries Settings', FAKTURO_TEXT_DOMAIN ); ?></th>
          </tr>
          <tr>
            <td><?php echo __( 'Enable Countries', FAKTURO_TEXT_DOMAIN ); ?></td>
            <td><input type="checkbox" name="enable_countries" value="1" <?php if (get_option('fakturo_enable_countries') == TRUE) { ?> checked <?php } ?> ></td>
          </tr>
          <tr>
            
            <table class="wp-list-table widefat fixed fakturo-setting">
              <tr>
                <p><?php echo __( 'Enter Countries.', FAKTURO_TEXT_DOMAIN ); ?></p>
              </tr>
              <tr>
                <th class="manage-column column-title column-primary"><?php echo __( 'Name', FAKTURO_TEXT_DOMAIN ); ?></th>
                <th><?php echo __( 'Remove', FAKTURO_TEXT_DOMAIN ); ?></th>
              </tr>
              <?php 
                $dataSetting = get_terms('fakturo_countries','hide_empty=0');
                printSettingRowSimpleTaxonomy($dataSetting, 'countries','countries', 'countries_delete'); 
              ?>
              <tr>
                <td><input type="text" name="countries_name"></td>
              </tr>
            </table>
            <br/>
            <button type="submit" name="Submit"  class="button" value="add_countries"><?php echo __( 'Add Countries', FAKTURO_TEXT_DOMAIN ); ?></button>
          </tr>
         <?php
      break;

      case 'states' :
         ?>
          <tr>
            <th><?php echo __( 'States Settings', FAKTURO_TEXT_DOMAIN ); ?></th>
          </tr>
          <tr>
            <td><?php echo __( 'Enable States', FAKTURO_TEXT_DOMAIN ); ?></td>
            <td><input type="checkbox" name="enable_states" value="1" <?php if (get_option('fakturo_enable_states') == TRUE) { ?> checked <?php } ?> ></td>
          </tr>
          <tr>
            
            <table class="wp-list-table widefat fixed fakturo-setting">
              <tr>
                <p><?php echo __( 'Enter States.', FAKTURO_TEXT_DOMAIN ); ?></p>
              </tr>
              <tr>
                <th class="manage-column column-title column-primary"><?php echo __( 'Name', FAKTURO_TEXT_DOMAIN ); ?></th>
                <th class="manage-column column-title column-primary"><?php echo __( 'Countries', FAKTURO_TEXT_DOMAIN ); ?></th>
                <th><?php echo __( 'Remove', FAKTURO_TEXT_DOMAIN ); ?></th>
              </tr>
              <?php 
                $dataSetting = get_terms('fakturo_states','hide_empty=0');
                printSettingRowStatesTaxonomy($dataSetting, 'countries','states', 'states_delete');
                $countries = get_terms('fakturo_countries','hide_empty=0');
              ?>
              <tr>
                <td><input type="text" name="states_name"></td>
                <td>
                  <select name="states_country">
                    <option></option>
                    <?php foreach ($countries as $key => $country) {
                      echo "<option>$country->name</option>";
                    } ?>
                  </select>
                </td>
              </tr>
            </table>
            <br/>
            <button type="submit" name="Submit"  class="button" value="add_states"><?php echo __( 'Add States', FAKTURO_TEXT_DOMAIN ); ?></button>
          </tr>
         <?php
      break;

      case 'company_info' :
         ?>
          <tr>
            <th><?php echo __( 'Company Info', FAKTURO_TEXT_DOMAIN ); ?></th>
          </tr>
          <tr>
            <?php settings_fields('fakturo_info_options_group');
             do_settings_sections('fakturo_info_options_group');
            ?>
          </tr>
         <?php
      break;

      case 'system_settings' :
         ?>
          <tr>
            <th><?php echo __( 'System Settings', FAKTURO_TEXT_DOMAIN ); ?></th>
          </tr>
          <tr>
            <th><?php _e( 'Currency', FAKTURO_TEXT_DOMAIN ); ?></th>
            <td class="italic-label">
              <select id="fakturo_system_currency" name="fakturo_system_currency">
                <?php 
                $currencies = FakturoSettingComponent::getCurrencies();
                $checkedCurrency = "";
                $currencyValue = isset($fakturoConfig['fakturo_system_currency']) ? $fakturoConfig['fakturo_system_currency'] : "";
                foreach ($currencies as $key => $value) {
                  if ($currencyValue == $key) {
                    $checkedCurrency = " selected ";
                  } else {
                    $checkedCurrency = "";
                  }
                  echo "<option $checkedCurrency value='$key'>$value</option>";
                } ?>
              </select>
              <label for="fakturo_system_currency">
                <?php _e(' Choose your currency. Note that some payment gateways have currency restrictions.', FAKTURO_TEXT_DOMAIN) ?>
              </label>
            </td>
          </tr>
          <tr>
            <th><?php _e( 'Currency Position', FAKTURO_TEXT_DOMAIN ); ?></th>
            <td class="italic-label">
              <select id="fakturo_system_position" name="fakturo_system_position">
                <option <?php if (isset($fakturoConfig['fakturo_system_position']) && $fakturoConfig['fakturo_system_position'] == 'before') echo "selected"; ?> value="before"><?php _e("Before", FAKTURO_TEXT_DOMAIN) ?> - $10</option>
                <option <?php if (isset($fakturoConfig['fakturo_system_position']) && $fakturoConfig['fakturo_system_position'] == 'after') echo "selected"; ?> value="after"><?php _e("After", FAKTURO_TEXT_DOMAIN) ?> - $10</option>
              </select>
              <label for="fakturo_system_position">
                <?php _e('Choose the location of the currency sign.', FAKTURO_TEXT_DOMAIN) ?>
              </label>
            </td>            
          </tr>
          <tr>
            <th><?php _e( 'Thousands Separator', FAKTURO_TEXT_DOMAIN ); ?></th>
            <td class="italic-label-inline">
              <input id="fakturo_system_thousand" name="fakturo_system_thousand" size="5" value="<?php echo isset($fakturoConfig['fakturo_system_thousand']) ? $fakturoConfig['fakturo_system_thousand'] : ''; ?>">
              <label for="fakturo_system_thousand">
                <?php _e('The symbol (usually , or .) to separate thousands', FAKTURO_TEXT_DOMAIN) ?>
              </label>
            </td>
          </tr>
          <tr>
            <th><?php _e( 'Decimal Separator', FAKTURO_TEXT_DOMAIN ); ?></th>
            <td class="italic-label-inline">
              <input id="fakturo_system_decimal" name="fakturo_system_decimal" size="5" value="<?php echo isset($fakturoConfig['fakturo_system_decimal']) ? $fakturoConfig['fakturo_system_decimal'] : ''; ?>">
              <label for="fakturo_system_decimal">
                <?php _e('The symbol (usually , or .) to separate decimal points', FAKTURO_TEXT_DOMAIN) ?>
              </label>
            </td>
          </tr>
         <?php
      break;
   }
   echo '</table>';
}

?>
    <?php if ($action == 'list') { ?>
	   <p class="submit" style="clear: both;">
        <button type="submit" name="Submit"  class="button-primary" value="update_setting"><?php echo __( 'Update Settings', FAKTURO_TEXT_DOMAIN ); ?></button>
	   </p>
    <?php } ?>
	</form>
</div>