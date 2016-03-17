<tr>
  <th><?php echo __( 'Email Template Settings', FAKTURO_TEXT_DOMAIN ); ?></th>
</tr>
<tr>
  <td><?php echo __( 'Enable Email Template', FAKTURO_TEXT_DOMAIN ); ?></td>
  <td><input type="checkbox" name="enable_emails" value="1" <?php if (get_option('fakturo_enable_emails') == TRUE) { ?> checked <?php } ?> ></td>
</tr>
<tr>
  
  <table class="wp-list-table widefat fixed fakturo-setting">
    <tr>
      <p><?php echo __( 'Enter Email Template.', FAKTURO_TEXT_DOMAIN ); ?></p>
    </tr>
    <tr>
      <th class="manage-column column-title column-primary"><?php echo __( 'Subject', FAKTURO_TEXT_DOMAIN ); ?></th>
      <th class="manage-column column-title column-primary"><?php echo __( 'Description', FAKTURO_TEXT_DOMAIN ); ?></th>
      <th class="manage-column column-title column-primary"><?php echo __( 'Texto', FAKTURO_TEXT_DOMAIN ); ?></th>
      <th><?php echo __( 'Buttons', FAKTURO_TEXT_DOMAIN ); ?></th>
    </tr>
    <?php 
      $dataSetting = get_terms('fakturo_emails','hide_empty=0');
      printSettingRowEmailTaxonomy($dataSetting, 'extensions', 'emails', 'emails_delete'); 
    ?>
  </table>
  <br/>
  <a class="button" href="?page=fakturo%2Fview%2Ffakturo_settings.php&tab=extensions&section=emails&action=add"><?php echo __( 'Add Email Template', FAKTURO_TEXT_DOMAIN ); ?></a>
</tr>