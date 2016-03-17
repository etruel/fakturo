<tr>
  <th><?php echo __( 'Email Template Settings', FAKTURO_TEXT_DOMAIN ); ?></th>
</tr>
<tr>
  <td><?php echo __( 'Subject', FAKTURO_TEXT_DOMAIN ); ?></td>
  <td><input type="text" size="40" name="email_subject" value="<?php if ($term != NULL) { echo $term->name; } ?>"></td>
</tr>
<tr>
  <td><?php echo __( 'Description', FAKTURO_TEXT_DOMAIN ); ?></td>
  <td><input type="text" name="email_desc" value="<?php if ($term != NULL) { echo get_term_meta($term->term_id, 'description', true); } ?>" ></td>
</tr>
<tr>
  <td><?php echo __( 'Texto', FAKTURO_TEXT_DOMAIN ); ?></td>
  <td><textarea style="width: 99%" rows="20" name="email_text"><?php if ($term != NULL) { echo stripcslashes(get_term_meta($term->term_id, 'text', true)); } ?></textarea></td>
</tr>
<tr>
  <td>
    <button type="submit" name="Submit"  class="button" value="add_email"><?php echo __( 'Update Email Template', FAKTURO_TEXT_DOMAIN ); ?></button>
  </td>
  <td>
    <a class="button" href="?page=fakturo%2Fview%2Ffakturo_settings.php&tab=extensions&section=emails&action=preview&id=<?php echo $term->term_id; ?>"><?php echo __( 'Test', FAKTURO_TEXT_DOMAIN ) ?></a>
  </td>  
</tr>