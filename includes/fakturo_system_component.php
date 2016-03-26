<?php

// system setting
function register_fakturo_system() {
   register_setting( 'fakturo_system_options_group', 'fakturo_system_options_group'); 
}
add_action( 'admin_menu', 'register_fakturo_system' );

function add_fakturo_system_options_to_page(){
   add_settings_section(
     'fakturo_system_options',
     '',
     'fakturo_system_fields',
     'fakturo_system_options_group' 
   );
   $args=array();
   add_settings_field( 'currency', __( 'Currency', FAKTURO_TEXT_DOMAIN ), 'fakturo_system_currency' , 'fakturo_system_options_group', 'fakturo_system_options', $args );
}
add_action('admin_menu','add_fakturo_system_options_to_page');

function fakturo_system_fields() {
}

function fakturo_system_currency() { 
   $options=get_option('fakturo_system_options_group') ; ?>
   <input type="text" size="36" value="<?php if (isset($options['currency'])) { echo $options['currency']; } ?>" name="fakturo_system_options_group[currency]" />
<?php }
