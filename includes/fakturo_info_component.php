<?php

// company info
function register_my_logo() {
   register_setting( 'fakturo_info_options_group', 'fakturo_info_options_group'); 
}
add_action( 'admin_menu', 'register_my_logo' );

function add_logo_options_to_page(){
   add_settings_section(
     'fakturo_info_options',
     '',
     'fakturo_info_fields',
     'fakturo_info_options_group' 
   );
   $args=array();
   add_settings_field( 'name', __( 'Name', FAKTURO_TEXT_DOMAIN ), 'fakturo_info_name' , 'fakturo_info_options_group', 'fakturo_info_options', $args );
   add_settings_field( 'taxpayer', __( 'Taxpayer ID', FAKTURO_TEXT_DOMAIN ), 'fakturo_info_taxpayer' , 'fakturo_info_options_group', 'fakturo_info_options', $args );
   add_settings_field( 'tax', __( 'Gross income tax ID', FAKTURO_TEXT_DOMAIN ), 'fakturo_info_tax' , 'fakturo_info_options_group', 'fakturo_info_options', $args );
   add_settings_field( 'start', __( 'Start of activities', FAKTURO_TEXT_DOMAIN ), 'fakturo_info_start' , 'fakturo_info_options_group', 'fakturo_info_options', $args );
   add_settings_field( 'address', __( 'Address', FAKTURO_TEXT_DOMAIN ), 'fakturo_info_address' , 'fakturo_info_options_group', 'fakturo_info_options', $args );
   add_settings_field( 'telephone', __( 'Telephone', FAKTURO_TEXT_DOMAIN ), 'fakturo_info_telephone' , 'fakturo_info_options_group', 'fakturo_info_options', $args );
   add_settings_field( 'postcode', __( 'Postcode', FAKTURO_TEXT_DOMAIN ), 'fakturo_info_postcode' , 'fakturo_info_options_group', 'fakturo_info_options', $args );
   add_settings_field( 'city', __( 'City', FAKTURO_TEXT_DOMAIN ), 'fakturo_info_city' , 'fakturo_info_options_group', 'fakturo_info_options', $args );
   add_settings_field( 'state', __( 'State', FAKTURO_TEXT_DOMAIN ), 'fakturo_info_state' , 'fakturo_info_options_group', 'fakturo_info_options', $args );
   add_settings_field( 'country', __( 'Country', FAKTURO_TEXT_DOMAIN ), 'fakturo_info_country' , 'fakturo_info_options_group', 'fakturo_info_options', $args );
   add_settings_field( 'website', __( 'Website', FAKTURO_TEXT_DOMAIN ), 'fakturo_info_website' , 'fakturo_info_options_group', 'fakturo_info_options', $args );
   add_settings_field( 'tax_condition', __( 'Tax condition', FAKTURO_TEXT_DOMAIN ), 'fakturo_info_tax_condition' , 'fakturo_info_options_group', 'fakturo_info_options', $args );
   add_settings_field( 'url', __( 'Company Logo', FAKTURO_TEXT_DOMAIN ), 'fakturo_info_url' , 'fakturo_info_options_group', 'fakturo_info_options', $args );   
}
add_action('admin_menu','add_logo_options_to_page');

function fakturo_info_url($args){
   $options=get_option('fakturo_info_options_group') ;
   ?>
   <label for="upload_image">
   <input id="url" type="text" size="36" value="<?php if (isset($options['url'])) { echo $options['url']; } ?>" name="fakturo_info_options_group[url]" />
   <input id="upload_logo_button" type="button" value="Upload Image" />
   <br /><?php echo __( 'Enter an URL or upload an image for the company logo.', FAKTURO_TEXT_DOMAIN ); ?>
   </label>
   <script type="text/javascript">
   jQuery(document).ready(function() {
   jQuery('#upload_logo_button').click(function() {
    formfield = jQuery('#url').attr('name');
    tb_show('', 'media-upload.php?type=image&TB_iframe=true');
    return false;});
   window.send_to_editor = function(html) {
    var doc = document.createElement("html");
    doc.innerHTML = html;
    imgurl = jQuery('img',doc).attr('src');
    jQuery('#url').val(imgurl);
    tb_remove(); }});
   </script>
   <?php
   if($options['url']){
      echo "<p style='padding-top: 5px;'>" .  __( 'This is your current logo', FAKTURO_TEXT_DOMAIN ) . "</p><img src='". $options['url'] ."' style='padding:5px;' />";      
   }
}

function fakturo_info_name() { 
   $options=get_option('fakturo_info_options_group') ; ?>
   <input type="text" size="36" value="<?php if (isset($options['name'])) { echo $options['name']; } ?>" name="fakturo_info_options_group[name]" />
<?php }

function fakturo_info_taxpayer() { 
   $options=get_option('fakturo_info_options_group') ; ?>
   <input type="text" size="36" value="<?php if (isset($options['taxpayer'])) { echo $options['taxpayer']; } ?>" name="fakturo_info_options_group[taxpayer]" />
<?php }

function fakturo_info_tax() { 
   $options=get_option('fakturo_info_options_group') ; ?>
   <input type="text" size="36" value="<?php if (isset($options['tax'])) { echo $options['tax']; } ?>" name="fakturo_info_options_group[tax]" />
<?php }

function fakturo_info_start() { 
   $options=get_option('fakturo_info_options_group') ; ?>
   <input type="text" size="36" value="<?php if (isset($options['start'])) { echo $options['start']; } ?>" name="fakturo_info_options_group[start]" />
<?php }

function fakturo_info_address() { 
   $options=get_option('fakturo_info_options_group') ; ?>
   <textarea name="fakturo_info_options_group[address]" cols="36" rows="4" ><?php if (isset($options['address'])) { echo $options['address']; } ?></textarea>
<?php }

function fakturo_info_telephone() { 
   $options=get_option('fakturo_info_options_group') ; ?>
   <input type="text" size="36" value="<?php if (isset($options['telephone'])) { echo $options['telephone']; } ?>" name="fakturo_info_options_group[telephone]" />
<?php }

function fakturo_info_postcode() { 
   $options=get_option('fakturo_info_options_group') ; ?>
   <input type="text" size="36" value="<?php if (isset($options['postcode'])) { echo $options['postcode']; } ?>" name="fakturo_info_options_group[postcode]" />
<?php }

function fakturo_info_city() { 
   $options=get_option('fakturo_info_options_group') ; ?>
   <input type="text" size="36" value="<?php if (isset($options['city'])) { echo $options['city']; } ?>" name="fakturo_info_options_group[city]" />
<?php }

function fakturo_info_state() { 
   $options=get_option('fakturo_info_options_group') ; ?>
   <input type="text" size="36" value="<?php if (isset($options['state'])) { echo $options['state']; } ?>" name="fakturo_info_options_group[state]" />
<?php }

function fakturo_info_country() { 
   $options=get_option('fakturo_info_options_group') ; ?>
   <input type="text" size="36" value="<?php if (isset($options['country'])) { echo $options['country']; } ?>" name="fakturo_info_options_group[country]" />
<?php }

function fakturo_info_website() { 
   $options=get_option('fakturo_info_options_group') ; ?>
   <input type="text" size="36" value="<?php if (isset($options['website'])) { echo $options['website']; } ?>" name="fakturo_info_options_group[website]" />
<?php }

function fakturo_info_tax_condition() { 
   $options=get_option('fakturo_info_options_group') ; ?>
   <input type="text" size="36" value="<?php if (isset($options['tax_condition'])) { echo $options['tax_condition']; } ?>" name="fakturo_info_options_group[tax_condition]" />
<?php }

function fakturo_info_fields() {
}

function my_admin_scripts() {
   wp_enqueue_script('media-upload');
   wp_enqueue_script('thickbox');
}

function my_admin_styles() {
   wp_enqueue_style('thickbox');
}
   
if (isset($_GET['page']) && $_GET['page'] == 'fakturo/settings/fakturo_settings.php') {
   add_action('admin_print_scripts', 'my_admin_scripts');
   add_action('admin_print_styles', 'my_admin_styles');
}

?>