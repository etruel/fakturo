<tr>
  <th><?php echo __( 'Print Template Settings', FAKTURO_TEXT_DOMAIN ); ?></th>
</tr>
<tr>
  <td><?php echo __( 'Enable Print Template', FAKTURO_TEXT_DOMAIN ); ?></td>
  <td><input type="checkbox" name="enable_print_template" value="1" <?php if (get_option('fakturo_enable_print_template') == TRUE) { ?> checked <?php } ?> ></td>
</tr>
<tr>
  
  <table class="wp-list-table widefat fixed fakturo-setting">
    <tr>
      <p><?php echo __( 'Enter Print Template.', FAKTURO_TEXT_DOMAIN ); ?></p>
    </tr>
    <tr>
      <th class="manage-column column-title column-primary"><?php echo __( 'Name', FAKTURO_TEXT_DOMAIN ); ?></th>
      <th class="manage-column column-title column-primary"><?php echo __( 'Description', FAKTURO_TEXT_DOMAIN ); ?></th>
      <th class="manage-column column-title column-primary"><?php echo __( 'Content', FAKTURO_TEXT_DOMAIN ); ?></th>
      <th class="manage-column column-title column-primary"><?php echo __( 'Assigned To', FAKTURO_TEXT_DOMAIN ); ?></th>
      <th><?php echo __( 'Buttons', FAKTURO_TEXT_DOMAIN ); ?></th>
    </tr>
    <?php 
      $dataSetting = get_terms('fakturo_print_template','hide_empty=0');
      printSettingRowPrintTemplateTaxonomy($dataSetting, 'tables', 'print-template', 'print_template_delete'); 
    ?>
  </table>
  <br/>
  <a class="button" href="?page=fakturo%2Fview%2Ffakturo_settings.php&tab=tables&section=print-template&action=add"><?php echo __( 'Add Print Template', FAKTURO_TEXT_DOMAIN ); ?></a>
</tr>