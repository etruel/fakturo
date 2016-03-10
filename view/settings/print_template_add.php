<tr>
  <th><?php echo __( 'Print Template Settings', FAKTURO_TEXT_DOMAIN ); ?></th>
</tr>
<tr>
  <td><?php echo __( 'Name', FAKTURO_TEXT_DOMAIN ); ?></td>
  <td><input type="text" name="print_template_name"></td>
</tr>
<tr>
  <td><?php echo __( 'Description', FAKTURO_TEXT_DOMAIN ); ?></td>
  <td><input type="text" size="40" name="print_template_desc"></td>
</tr>
<tr>
  <td><?php echo __( 'Content', FAKTURO_TEXT_DOMAIN ); ?></td>
  <td><textarea style="width: 99%" rows="20" name="print_template_content"></textarea>
</tr>
<tr>
  <td><?php echo __( 'Assigned To', FAKTURO_TEXT_DOMAIN ); ?></td>
  <td>
    <select name="print_template_assigned">
      <option></option>
      <option>Abonos</option>
      <option>Bank entities</option>
    </select>
  </td>
</tr>
<tr>
  <td>
    <button type="submit" name="Submit"  class="button" value="add_print_template"><?php echo __( 'Add Print Template', FAKTURO_TEXT_DOMAIN ); ?></button>
  </td>  
</tr>