<?php
/*
 Plugin Name: Fakturo
 Plugin URI: http://www.wpematico.com
 Description: Make invoices with products and clients.  If you like it, please rate it 5 stars.
 Version: 1.0.0
 Author: etruel <esteban@netmdp.com>
 Author URI: http://www.netmdp.com
 */



add_action( 'admin_menu', 'fakturo_admin_menu' );
add_action('admin_enqueue_scripts', 'fakturo_admin_style');

const FAKTURO_TEXT_DOMAIN = 'fakturo';
if(!defined( 'FAKTURO_URI' ) ) define( 'FAKTURO_URI', plugin_dir_url( __FILE__ ) );
if(!defined( 'FAKTURO_DIR' ) ) define( 'FAKTURO_DIR', plugin_dir_path( __FILE__ ) );

require_once('component/fakturo_base_component.php');
require_once('component/fakturo_taxonomies_component.php');
require_once('component/fakturo_info_component.php');


function fakturo_admin_menu() {
	if (current_user_can('manage_options')) {
		add_menu_page( __( 'Fakturo', FAKTURO_TEXT_DOMAIN ), __( 'Fakturo', FAKTURO_TEXT_DOMAIN ), 'manage_options', 'fakturo/view/fakturo_admin.php', '', 'dashicons-tickets', 81  );
		add_submenu_page( 'fakturo/view/fakturo_admin.php', __( 'Settings', FAKTURO_TEXT_DOMAIN ), __( 'Settings', FAKTURO_TEXT_DOMAIN ), 'manage_options', 'fakturo/view/fakturo_settings.php', 'fakturo_update_settings_controller' ); 
	}	
}

function fakturo_admin_style() {
  wp_enqueue_style('admin-styles', plugin_dir_url( __FILE__ ) . 'css/fakturo_settings.css');
}

function fakturo_update_settings_controller() {
   require_once('component/fakturo_setting_component.php');
	$section = FakturoSettingComponent::getFakturoCurrentSection();
   $action = FakturoBaseComponent::fakturoGetAction();
   $id = FakturoBaseComponent::fakturoGetId();

   switch ( $section ) {
      case 'user-template' :
      	if (isset($_POST['Submit']) && $_POST['Submit'] == 'add_user_template' && isset($_POST['user_template_name']) && $_POST['user_template_name'] != NULL) {
      		wp_insert_term( $_POST['user_template_name'], 'fakturo_user_template', $args = array() );
      	}
      	if (isset($_GET['user_template_delete']) && $_GET['user_template_delete'] != NULL) {
      		wp_delete_term( $_GET['user_template_delete'], 'fakturo_user_template' );
      	}
      	if (isset($_POST['Submit']) && $_POST['Submit'] == 'update_setting') {
      		if (isset($_POST['enable_user_template']) && $_POST['enable_user_template'] == TRUE) {
      			update_option('fakturo_enable_user_template', TRUE);
      		} else {
      			update_option('fakturo_enable_user_template', FALSE);
      		}
      	}
      	break;
      case 'print-template' :
      	if ($action == 'add' && isset($_POST['Submit']) && $_POST['Submit'] == 'add_print_template' && isset($_POST['print_template_name']) && $_POST['print_template_name'] != NULL) {
      		$term = wp_insert_term( $_POST['print_template_name'], 'fakturo_print_template', $args = array() );
      		if (isset($_POST['print_template_desc'])) {
      			add_term_meta ($term['term_id'], 'description', $_POST['print_template_desc']);
      		}
      		if (isset($_POST['print_template_content'])) {
      			add_term_meta ($term['term_id'], 'content', addslashes($_POST['print_template_content']));
      		}
      		if (isset($_POST['print_template_assigned'])) {
      			add_term_meta ($term['term_id'], 'assigned_to', $_POST['print_template_assigned']);
      		}
            print('<script>window.location.href="admin.php?page=fakturo%2Fview%2Ffakturo_settings.php&tab=tables&section=print-template"</script>');
      	}
         if ($action == 'edit') {
            $term = NULL;
            if ($id != NULL) {               
               if (isset($_POST['print_template_name'])) {
                  wp_update_term($id, 'fakturo_print_template', array('name' => $_POST['print_template_name'], 'slug' => sanitize_title($_POST['print_template_name'])));
               }
               if (isset($_POST['print_template_desc'])) {
                  update_term_meta($id, 'description', $_POST['print_template_desc']);
               }
               if (isset($_POST['print_template_content'])) {
                  update_term_meta($id, 'content', addslashes($_POST['print_template_content']));
               }
               if (isset($_POST['print_template_assigned'])) {
                  update_term_meta($id, 'assigned_to', $_POST['print_template_assigned']);
                  print('<script>window.location.href="admin.php?page=fakturo%2Fview%2Ffakturo_settings.php&tab=tables&section=print-template"</script>');
               }
               $term = get_term($id, 'fakturo_print_template');
            }
         }
      	if (isset($_GET['print_template_delete']) && $_GET['print_template_delete'] != NULL) {
      		wp_delete_term( $_GET['print_template_delete'], 'fakturo_print_template' );
      	}
      	if (isset($_POST['Submit']) && $_POST['Submit'] == 'update_setting') {
      		if (isset($_POST['enable_print_template']) && $_POST['enable_print_template'] == TRUE) {
      			update_option('fakturo_enable_print_template', TRUE);
      		} else {
      			update_option('fakturo_enable_print_template', FALSE);
      		}
      	}
      	break;

      case 'emails' :
         if ($action == 'add' && isset($_POST['Submit']) && $_POST['Submit'] == 'add_email' && isset($_POST['email_subject']) && $_POST['email_subject'] != NULL) {
            $term = wp_insert_term( $_POST['email_subject'], 'fakturo_emails', $args = array() );
            if (isset($_POST['email_desc'])) {
               add_term_meta ($term['term_id'], 'description', $_POST['email_desc']);
            }
            if (isset($_POST['email_text'])) {
               add_term_meta ($term['term_id'], 'text', addslashes($_POST['email_text']));
            }
            print('<script>window.location.href="admin.php?page=fakturo%2Fview%2Ffakturo_settings.php&tab=extensions&section=emails"</script>');
         }
         if ($action == 'edit') {
            $term = NULL;
            if ($id != NULL) {               
               if (isset($_POST['email_subject'])) {
                  wp_update_term($id, 'fakturo_emails', array('name' => $_POST['email_subject'], 'slug' => sanitize_title($_POST['email_subject'])));
               }
               if (isset($_POST['email_desc'])) {
                  update_term_meta($id, 'description', $_POST['email_desc']);
               }
               if (isset($_POST['email_text'])) {
                  update_term_meta($id, 'text', addslashes($_POST['email_text']));
                  print('<script>window.location.href="admin.php?page=fakturo%2Fview%2Ffakturo_settings.php&tab=extensions&section=emails"</script>');
               }
               $term = get_term($id, 'fakturo_emails');
            }
         }
         if ($action == 'preview') {
            $term = get_term($id, 'fakturo_emails');
            if ($term != NULL) {
               wp_mail(get_bloginfo('admin_email'), $term->name, get_term_meta($term->term_id, 'text', true), get_term_meta($term->term_id, 'description', true));
            }            
            print('<script>window.location.href="admin.php?page=fakturo%2Fview%2Ffakturo_settings.php&tab=extensions&section=emails"</script>');
         }
         if (isset($_GET['emails_delete']) && $_GET['emails_delete'] != NULL) {
            wp_delete_term( $_GET['emails_delete'], 'fakturo_emails' );
         }
         if (isset($_POST['Submit']) && $_POST['Submit'] == 'update_setting') {
            if (isset($_POST['enable_emails']) && $_POST['enable_emails'] == TRUE) {
               update_option('fakturo_enable_emails', TRUE);
            } else {
               update_option('fakturo_enable_emails', FALSE);
            }
         }
         break;

      case 'currencies' :
      	if (isset($_POST['Submit']) && $_POST['Submit'] == 'add_currency' && isset($_POST['currency_name']) && $_POST['currency_name'] != NULL) {
      		$term = wp_insert_term( $_POST['currency_name'], 'fakturo_currency', $args = array() );
      		if (isset($_POST['currency_symbol'])) {
      			add_term_meta ($term['term_id'], 'symbol', $_POST['currency_symbol']);
      		}
      		if (isset($_POST['currency_rate'])) {
      			add_term_meta ($term['term_id'], 'rate', $_POST['currency_rate']);
      		}
      		if (isset($_POST['currency_reference'])) {
      			add_term_meta ($term['term_id'], 'reference', $_POST['currency_reference']);
      		}
      		if (isset($_POST['currency_default'])) {
      			add_term_meta ($term['term_id'], 'default', $_POST['currency_default']);
      		} else {
               add_term_meta ($term['term_id'], 'default', 0);
            }
            if (isset($_POST['currency_plural'])) {
               add_term_meta ($term['term_id'], 'plural', $_POST['currency_plural']);
            }
      	}
      	if (isset($_GET['currency_delete']) && $_GET['currency_delete'] != NULL) {
      		wp_delete_term( $_GET['currency_delete'], 'fakturo_currency' );
      	}
      	if (isset($_POST['Submit']) && $_POST['Submit'] == 'update_setting') {
      		if (isset($_POST['enable_currency']) && $_POST['enable_currency'] == TRUE) {
      			update_option('fakturo_enable_currency', TRUE);
      		} else {
      			update_option('fakturo_enable_currency', FALSE);
      		}
      	}
      	break;
      case 'taxes' :
         if (isset($_POST['Submit']) && $_POST['Submit'] == 'add_taxes' && isset($_POST['taxes_name']) && $_POST['taxes_name'] != NULL) {
            $term = wp_insert_term( $_POST['taxes_name'], 'fakturo_taxes', $args = array() );
            if (isset($_POST['taxes_percent'])) {
               add_term_meta ($term['term_id'], 'percent', $_POST['taxes_percent']);
            }
         }
         if (isset($_GET['taxes_delete']) && $_GET['taxes_delete'] != NULL) {
            wp_delete_term( $_GET['taxes_delete'], 'fakturo_taxes' );
         }
         if (isset($_POST['Submit']) && $_POST['Submit'] == 'update_setting') {
            if (isset($_POST['enable_taxes']) && $_POST['enable_taxes'] == TRUE) {
               update_option('fakturo_enable_taxes', TRUE);
            } else {
               update_option('fakturo_enable_taxes', FALSE);
            }
         }
         break;
      case 'tax_condition' :
         if (isset($_POST['Submit']) && $_POST['Submit'] == 'add_tax_condition' && isset($_POST['tax_condition_name']) && $_POST['tax_condition_name'] != NULL) {
            $term = wp_insert_term( $_POST['tax_condition_name'], 'fakturo_tax_condition', $args = array() );
         }
         if (isset($_GET['tax_condition_delete']) && $_GET['tax_condition_delete'] != NULL) {
            wp_delete_term( $_GET['tax_condition_delete'], 'fakturo_tax_condition' );
         }
         if (isset($_POST['Submit']) && $_POST['Submit'] == 'update_setting') {
            if (isset($_POST['enable_tax_condition']) && $_POST['enable_tax_condition'] == TRUE) {
               update_option('fakturo_enable_tax_condition', TRUE);
            } else {
               update_option('fakturo_enable_tax_condition', FALSE);
            }
         }
         break;
      case 'invoice_type' :
         if (isset($_POST['Submit']) && $_POST['Submit'] == 'add_invoice_type' && isset($_POST['invoice_type_name']) && $_POST['invoice_type_name'] != NULL) {
            $term = wp_insert_term( $_POST['invoice_type_name'], 'fakturo_invoice_type', $args = array() );
            if (isset($_POST['invoice_type_short_name'])) {
               add_term_meta ($term['term_id'], 'short_name', $_POST['invoice_type_short_name']);
            }
            if (isset($_POST['invoice_type_taxes'])) {
               add_term_meta ($term['term_id'], 'taxes', $_POST['invoice_type_taxes']);
            } else {
               add_term_meta ($term['term_id'], 'taxes', 0);
            }
            if (isset($_POST['invoice_type_default'])) {
               add_term_meta ($term['term_id'], 'default', $_POST['invoice_type_default']);
            } else {
               add_term_meta ($term['term_id'], 'default', 0);
            }
            if (isset($_POST['invoice_type_sum'])) {
               add_term_meta ($term['term_id'], 'sum', $_POST['invoice_type_sum']);
            } else {
               add_term_meta ($term['term_id'], 'sum', 0);
            }
         }
         if (isset($_GET['invoice_type_delete']) && $_GET['invoice_type_delete'] != NULL) {
            wp_delete_term( $_GET['invoice_type_delete'], 'fakturo_invoice_type' );
         }
         if (isset($_POST['Submit']) && $_POST['Submit'] == 'update_setting') {
            if (isset($_POST['enable_invoice_type']) && $_POST['enable_invoice_type'] == TRUE) {
               update_option('fakturo_enable_invoice_type', TRUE);
            } else {
               update_option('fakturo_enable_invoice_type', FALSE);
            }
         }
         break;

      case 'bank_entities' :
         if (isset($_POST['Submit']) && $_POST['Submit'] == 'add_bank_entities' && isset($_POST['bank_entities_name']) && $_POST['bank_entities_name'] != NULL) {
            $term = wp_insert_term( $_POST['bank_entities_name'], 'fakturo_bank_entities', $args = array() );
         }
         if (isset($_GET['bank_entities_delete']) && $_GET['bank_entities_delete'] != NULL) {
            wp_delete_term( $_GET['bank_entities_delete'], 'fakturo_bank_entities' );
         }
         if (isset($_POST['Submit']) && $_POST['Submit'] == 'update_setting') {
            if (isset($_POST['enable_bank_entities']) && $_POST['enable_bank_entities'] == TRUE) {
               update_option('fakturo_enable_bank_entities', TRUE);
            } else {
               update_option('fakturo_enable_bank_entities', FALSE);
            }
         }
         break;

      case 'payment_types' :
         if (isset($_POST['Submit']) && $_POST['Submit'] == 'add_payment_types' && isset($_POST['payment_types_name']) && $_POST['payment_types_name'] != NULL) {
            $term = wp_insert_term( $_POST['payment_types_name'], 'fakturo_payment_types', $args = array() );
         }
         if (isset($_GET['payment_types_delete']) && $_GET['payment_types_delete'] != NULL) {
            wp_delete_term( $_GET['payment_types_delete'], 'fakturo_payment_types' );
         }
         if (isset($_POST['Submit']) && $_POST['Submit'] == 'update_setting') {
            if (isset($_POST['enable_payment_types']) && $_POST['enable_payment_types'] == TRUE) {
               update_option('fakturo_enable_payment_types', TRUE);
            } else {
               update_option('fakturo_enable_payment_types', FALSE);
            }
         }
         break;

      case 'repairs_status' :
         if (isset($_POST['Submit']) && $_POST['Submit'] == 'add_repairs_status' && isset($_POST['repairs_status_name']) && $_POST['repairs_status_name'] != NULL) {
            $term = wp_insert_term( $_POST['repairs_status_name'], 'fakturo_repairs_status', $args = array() );
         }
         if (isset($_GET['repairs_status_delete']) && $_GET['repairs_status_delete'] != NULL) {
            wp_delete_term( $_GET['repairs_status_delete'], 'fakturo_repairs_status' );
         }
         if (isset($_POST['Submit']) && $_POST['Submit'] == 'update_setting') {
            if (isset($_POST['enable_repairs_status']) && $_POST['enable_repairs_status'] == TRUE) {
               update_option('fakturo_enable_repairs_status', TRUE);
            } else {
               update_option('fakturo_enable_repairs_status', FALSE);
            }
         }
         break;

      case 'packagings' :
         if (isset($_POST['Submit']) && $_POST['Submit'] == 'add_packagings' && isset($_POST['packagings_name']) && $_POST['packagings_name'] != NULL) {
            $term = wp_insert_term( $_POST['packagings_name'], 'fakturo_packagings', $args = array() );
         }
         if (isset($_GET['packagings_delete']) && $_GET['packagings_delete'] != NULL) {
            wp_delete_term( $_GET['packagings_delete'], 'fakturo_packagings' );
         }
         if (isset($_POST['Submit']) && $_POST['Submit'] == 'update_setting') {
            if (isset($_POST['enable_packagings']) && $_POST['enable_packagings'] == TRUE) {
               update_option('fakturo_enable_packagings', TRUE);
            } else {
               update_option('fakturo_enable_packagings', FALSE);
            }
         }
         break;

      case 'price_scales' :
         if (isset($_POST['Submit']) && $_POST['Submit'] == 'add_price_scales' && isset($_POST['price_scales_name']) && $_POST['price_scales_name'] != NULL) {
            $term = wp_insert_term( $_POST['price_scales_name'], 'fakturo_price_scales', $args = array() );
            if (isset($_POST['price_scales_percent'])) {
               add_term_meta ($term['term_id'], 'percent', $_POST['price_scales_percent']);
            }
            if (isset($_POST['price_scales_default'])) {
               add_term_meta ($term['term_id'], 'default', $_POST['price_scales_default']);
            } else {
               add_term_meta ($term['term_id'], 'default', 0);
            }
         }
         if (isset($_GET['price_scales_delete']) && $_GET['price_scales_delete'] != NULL) {
            wp_delete_term( $_GET['price_scales_delete'], 'fakturo_price_scales' );
         }
         if (isset($_POST['Submit']) && $_POST['Submit'] == 'update_setting') {
            if (isset($_POST['enable_price_scales']) && $_POST['enable_price_scales'] == TRUE) {
               update_option('fakturo_enable_price_scales', TRUE);
            } else {
               update_option('fakturo_enable_price_scales', FALSE);
            }
         }
         break;

      case 'product_types' :
         if (isset($_POST['Submit']) && $_POST['Submit'] == 'add_product_types' && isset($_POST['product_types_name']) && $_POST['product_types_name'] != NULL) {
            $term = wp_insert_term( $_POST['product_types_name'], 'fakturo_product_types', $args = array() );
         }
         if (isset($_GET['product_types_delete']) && $_GET['product_types_delete'] != NULL) {
            wp_delete_term( $_GET['product_types_delete'], 'fakturo_product_types' );
         }
         if (isset($_POST['Submit']) && $_POST['Submit'] == 'update_setting') {
            if (isset($_POST['enable_product_types']) && $_POST['enable_product_types'] == TRUE) {
               update_option('fakturo_enable_product_types', TRUE);
            } else {
               update_option('fakturo_enable_product_types', FALSE);
            }
         }
         break;

      case 'locations' :
         if (isset($_POST['Submit']) && $_POST['Submit'] == 'add_locations' && isset($_POST['locations_name']) && $_POST['locations_name'] != NULL) {
            $term = wp_insert_term( $_POST['locations_name'], 'fakturo_locations', $args = array() );
         }
         if (isset($_GET['locations_delete']) && $_GET['locations_delete'] != NULL) {
            wp_delete_term( $_GET['locations_delete'], 'fakturo_locations' );
         }
         if (isset($_POST['Submit']) && $_POST['Submit'] == 'update_setting') {
            if (isset($_POST['enable_locations']) && $_POST['enable_locations'] == TRUE) {
               update_option('fakturo_enable_locations', TRUE);
            } else {
               update_option('fakturo_enable_locations', FALSE);
            }
         }
         break;

      case 'origins' :
         if (isset($_POST['Submit']) && $_POST['Submit'] == 'add_origins' && isset($_POST['origins_name']) && $_POST['origins_name'] != NULL) {
            $term = wp_insert_term( $_POST['origins_name'], 'fakturo_origins', $args = array() );
         }
         if (isset($_GET['origins_delete']) && $_GET['origins_delete'] != NULL) {
            wp_delete_term( $_GET['origins_delete'], 'fakturo_origins' );
         }
         if (isset($_POST['Submit']) && $_POST['Submit'] == 'update_setting') {
            if (isset($_POST['enable_origins']) && $_POST['enable_origins'] == TRUE) {
               update_option('fakturo_enable_origins', TRUE);
            } else {
               update_option('fakturo_enable_origins', FALSE);
            }
         }
         break;

      case 'countries' :
         if (isset($_POST['Submit']) && $_POST['Submit'] == 'add_countries' && isset($_POST['countries_name']) && $_POST['countries_name'] != NULL) {
            $term = wp_insert_term( $_POST['countries_name'], 'fakturo_countries', $args = array() );
         }
         if (isset($_GET['countries_delete']) && $_GET['countries_delete'] != NULL) {
            wp_delete_term( $_GET['countries_delete'], 'fakturo_countries' );
         }
         if (isset($_POST['Submit']) && $_POST['Submit'] == 'update_setting') {
            if (isset($_POST['enable_countries']) && $_POST['enable_countries'] == TRUE) {
               update_option('fakturo_enable_countries', TRUE);
            } else {
               update_option('fakturo_enable_countries', FALSE);
            }
         }
         break;

      case 'states' :
         if (isset($_POST['Submit']) && $_POST['Submit'] == 'add_states' && isset($_POST['states_name']) && $_POST['states_name'] != NULL) {
            $term = wp_insert_term( $_POST['states_name'], 'fakturo_states', $args = array() );
            if (isset($_POST['states_country'])) {
               add_term_meta($term['term_id'], 'country', $_POST['states_country']);
            }
         }
         if (isset($_GET['states_delete']) && $_GET['states_delete'] != NULL) {
            wp_delete_term( $_GET['states_delete'], 'fakturo_states' );
         }
         if (isset($_POST['Submit']) && $_POST['Submit'] == 'update_setting') {
            if (isset($_POST['enable_states']) && $_POST['enable_states'] == TRUE) {
               update_option('fakturo_enable_states', TRUE);
            } else {
               update_option('fakturo_enable_states', FALSE);
            }
         }
         break;

      case 'company_info' :
         $fakturoConfig = json_decode(get_option('fakturo_setting_config'), TRUE);
         if (isset($_POST['Submit']) && $_POST['Submit'] == 'update_setting') {
            $fakturoConfig = array();
            $fakturoConfig['fakturo_config_name'] = isset($_POST['fakturo_config_name']) ? $_POST['fakturo_config_name'] : NULL;
            $fakturoConfig['fakturo_config_taxpayer'] = isset($_POST['fakturo_config_taxpayer']) ? $_POST['fakturo_config_taxpayer'] : NULL;
            $fakturoConfig['fakturo_config_tax_id'] = isset($_POST['fakturo_config_tax_id']) ? $_POST['fakturo_config_tax_id'] : NULL;
            $fakturoConfig['fakturo_config_activities'] = isset($_POST['fakturo_config_activities']) ? $_POST['fakturo_config_activities'] : NULL;
            $fakturoConfig['fakturo_config_address'] = isset($_POST['fakturo_config_address']) ? $_POST['fakturo_config_address'] : NULL;
            $fakturoConfig['fakturo_config_telephone'] = isset($_POST['fakturo_config_telephone']) ? $_POST['fakturo_config_telephone'] : NULL;
            $fakturoConfig['fakturo_config_postcode'] = isset($_POST['fakturo_config_postcode']) ? $_POST['fakturo_config_postcode'] : NULL;
            $fakturoConfig['fakturo_config_city'] = isset($_POST['fakturo_config_city']) ? $_POST['fakturo_config_city'] : NULL;
            $fakturoConfig['fakturo_config_state'] = isset($_POST['fakturo_config_state']) ? $_POST['fakturo_config_state'] : NULL;
            $fakturoConfig['fakturo_config_country'] = isset($_POST['fakturo_config_country']) ? $_POST['fakturo_config_country'] : NULL;
            $fakturoConfig['fakturo_config_web'] = isset($_POST['fakturo_config_web']) ? $_POST['fakturo_config_web'] : NULL;
            $fakturoConfig['fakturo_config_tax'] = isset($_POST['fakturo_config_tax']) ? $_POST['fakturo_config_tax'] : NULL;
            $fakturoConfig['fakturo_config_logo'] = isset($_POST['fakturo_config_logo']) ? $_POST['fakturo_config_logo'] : NULL;

            update_option('fakturo_setting_config', json_encode($fakturoConfig));
         }
         break;
   }

   require_once('helper/fakturo_setting_helper.php');
	require_once('view/fakturo_settings.php');
}

?>
