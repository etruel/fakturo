<tr>
  <th><?php echo __( 'Print Template Settings', FAKTURO_TEXT_DOMAIN ); ?></th>
</tr>
<tr>
  <td><?php echo __( 'Name', FAKTURO_TEXT_DOMAIN ); ?></td>
  <td><input type="text" name="print_template_name" value="<?php if ($term != NULL) { echo $term->name; } ?>"></td>
</tr>
<tr>
  <td><?php echo __( 'Description', FAKTURO_TEXT_DOMAIN ); ?></td>
  <td><input type="text" size="40" name="print_template_desc" value="<?php if ($term != NULL) { echo get_term_meta($term->term_id, 'description', true); } ?>"></td>
</tr>
<tr>
  <td><?php echo __( 'Content', FAKTURO_TEXT_DOMAIN ); ?></td>
  <td><textarea style="width: 99%" rows="20" name="print_template_content"><?php if ($term != NULL) { echo stripcslashes(get_term_meta($term->term_id, 'content', true)); } ?></textarea>
</tr>
<tr>
  <td><?php echo __( 'Assigned To', FAKTURO_TEXT_DOMAIN ); ?></td>
  <td>
    <select name="print_template_assigned">
      <option></option>
      <option <?php if ($term != NULL && get_term_meta($term->term_id, 'assigned_to', true) == 'Abonos') { echo "selected"; } ?>>Abonos</option>
      <option <?php if ($term != NULL && get_term_meta($term->term_id, 'assigned_to', true) == 'Bank entities') { echo "selected"; } ?>>Bank entities</option>
    </select>
  </td>
</tr>
<tr>
  <td>
    <button type="submit" name="Submit"  class="button print-template-btn" value="add_print_template"><?php echo __( 'Update Print Template', FAKTURO_TEXT_DOMAIN ); ?></button>
  </td>
  <td>
    <a class="button" href="?page=fakturo%2Fview%2Ffakturo_settings.php&tab=tables&section=print-template&action=preview&id=$term->term_id"><?php echo __( 'Preview', FAKTURO_TEXT_DOMAIN ) ?></a>
  </td>
</tr>