<tr>
  <th><?php echo __( 'Email Template Settings', FAKTURO_TEXT_DOMAIN ); ?></th>
</tr>
<tr>
  <td><?php echo __( 'Subject', FAKTURO_TEXT_DOMAIN ); ?></td>
  <td><input type="text" size="40" name="email_subject" ></td>
</tr>
<tr>
  <td><?php echo __( 'Description', FAKTURO_TEXT_DOMAIN ); ?></td>
  <td><input type="text" name="email_desc"></td>
</tr>
<tr>
  <td><?php echo __( 'Texto', FAKTURO_TEXT_DOMAIN ); ?></td>
  <td><textarea style="width: 99%" rows="20" name="email_text"></textarea></td>
</tr>
<tr>
  <td>
    <button type="submit" name="Submit"  class="button" value="add_email"><?php echo __( 'Add Email Template', FAKTURO_TEXT_DOMAIN ); ?></button>
  </td>  
</tr>