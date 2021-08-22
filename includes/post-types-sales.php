<?php


// Exit if accessed directly
if (!defined('ABSPATH'))  {
	exit;
}


if ( ! class_exists('fktrPostTypeSales') ) :
class fktrPostTypeSales {
	function __construct() {
		
		add_action( 'init', array('fktrPostTypeSales', 'setup'), 1 );
		add_action( 'fakturo_activation', array('fktrPostTypeSales', 'setup'), 1 );
		
		add_action('transition_post_status', array('fktrPostTypeSales', 'default_fields'), 10, 3);
                
                add_filter('display_post_states',  array('fktrPostTypeSales', 'display_fktr_sale_state'), 10, 1 );
                add_filter('views_edit-fktr_sale',  array('fktrPostTypeSales', 'custom_draft_translation'), 10, 1 );

		add_action('save_post', array('fktrPostTypeSales', 'save'), 99, 2 );
		
		add_action( 'admin_print_scripts-post-new.php', array('fktrPostTypeSales','scripts'), 11 );
		add_action( 'admin_print_scripts-post.php', array('fktrPostTypeSales','scripts'), 11 );
		
		add_action('admin_print_styles-post-new.php', array('fktrPostTypeSales','styles'));
		add_action('admin_print_styles-post.php', array('fktrPostTypeSales','styles'));
		add_action('admin_print_scripts', array('fktrPostTypeSales','scripts_list'));
		add_action('get_delete_post_link', array('fktrPostTypeSales', 'set_delete_post_link'), 10, 3);
		
		add_filter('fktr_clean_sale_fields', array('fktrPostTypeSales', 'clean_fields'), 10, 1);
		add_filter('fktr_sale_before_save', array('fktrPostTypeSales', 'before_save'), 10, 1);
		add_filter('fktr_save_sale_summary', array('fktrPostTypeSales', 'fktr_save_sale_summary'), 10, 2);
		
		add_action('wp_ajax_get_client_data', array('fktrPostTypeSales', 'get_client_data'));
		add_action('wp_ajax_get_products', array('fktrPostTypeSales', 'get_products'));
		add_action('wp_ajax_get_suggest_invoice_number', array('fktrPostTypeSales', 'get_suggest_invoice_number'));
		add_action('wp_ajax_validate_sale', array('fktrPostTypeSales', 'ajax_validate_sale'));
		
		
		add_filter( 'post_updated_messages', array('fktrPostTypeSales', 'updated_messages') );
		
		add_filter('fktr_text_code_product_reference', array('fktrPostTypeSales', 'text_code_product_reference'), 10, 1);
		add_filter('fktr_text_code_product_internal_code', array('fktrPostTypeSales', 'text_code_product_internal_code'), 10, 1);
		add_filter('fktr_text_code_product_manufacturers_code', array('fktrPostTypeSales', 'text_code_product_manufacturers_code'), 10, 1);
		
		add_filter('fktr_meta_key_code_product_reference', array('fktrPostTypeSales', 'meta_key_code_product_reference'), 10, 1);
		add_filter('fktr_meta_key_code_product_internal_code', array('fktrPostTypeSales', 'meta_key_code_product_internal_code'), 10, 1);
		add_filter('fktr_meta_key_code_product_manufacturers_code', array('fktrPostTypeSales', 'meta_key_code_product_manufacturers_code'), 10, 1);
		
		
		add_filter('fktr_text_description_product_short_description', array('fktrPostTypeSales', 'text_description_product_short_description'), 10, 1);
		add_filter('fktr_text_description_product_description', array('fktrPostTypeSales', 'text_description_product_description'), 10, 1);
		
		add_filter('fktr_meta_key_description_product_short_description', array('fktrPostTypeSales', 'meta_key_description_product_short_description'), 10, 1);
		add_filter('fktr_meta_key_description_product_description', array('fktrPostTypeSales', 'meta_key_description_product_description'), 10, 1);
		
		add_filter('fktr_search_product_parameter_reference', array('fktrPostTypeSales', 'product_parameter_reference'), 10, 3);
		add_filter('fktr_search_product_parameter_internal_code', array('fktrPostTypeSales', 'product_parameter_internal_code'), 10, 3);
		add_filter('fktr_search_product_parameter_manufacturers_code', array('fktrPostTypeSales', 'product_parameter_manufacturers_code'), 10, 3);
		add_filter('post_row_actions', array('fktrPostTypeSales', 'action_row'), 10, 2);

		add_filter('attribute_escape', array('fktrPostTypeSales', 'change_button_texts'), 10, 2);
		add_action('before_delete_post', array('fktrPostTypeSales', 'before_delete'), 10, 1);
		add_action('admin_post_print_invoice', array(__CLASS__, 'print_invoice'));
		add_action('admin_post_print_demo_invoice', array(__CLASS__, 'print_demo_invoice'));
		add_filter( 'bulk_actions-edit-fktr_sale', array(__CLASS__, 'bulk_actions') );
		add_filter( 'handle_bulk_actions-edit-fktr_sale', array(__CLASS__, 'bulk_action_handler'), 10, 3 );
                
		add_filter('manage_fktr_sale_posts_columns' , array(__CLASS__, 'columns' ));
                //add_filter('manage_edit-fktr_sale_columns', array( __CLASS__, 'columns'), 10);  // Nueva
		add_filter('manage_fktr_sale_posts_custom_column' , array(__CLASS__, 'manage_columns' ), 10, 2 );
                
		add_filter('manage_edit-fktr_sale_sortable_columns',  array(__CLASS__, 'sortable_columns'));
                //add_action('pre_get_posts', array(__CLASS__, 'column_orderby') );
                add_action('parse_query', array(__CLASS__, 'column_orderby') );
	}

        // Make these columns sortable
        public static function sortable_columns($columns) {
            $custom = array(
                'client' 	=> 'client',
                'saledate' 	=> 'saledate'
            );
            return wp_parse_args($custom, $columns);
        }

       	public static function column_orderby($query) {
            global $pagenow, $post_type;
            $orderby = $query->get('orderby');
            if ('edit.php' != $pagenow || empty($orderby) || $post_type != 'fktr_sale')
                return;
            switch ($orderby) {
                case 'client':
                    $meta_group = array('key' => 'client_id', 'type' => 'numeric');
                    $query->set('meta_query', array('sort_column' => 'client', $meta_group));
                    $query->set('meta_key', 'client_id');
                    $query->set('orderby', 'meta_value_num');

                    break;
                case 'saledate':
                    $meta_group = array('key' => 'date', 'type' => 'numeric');
                    $query->set('meta_query', array('sort_column' => 'saledate', $meta_group));
                    $query->set('meta_key', 'date');
                    $query->set('orderby', 'meta_value_num');

                    break;

                default:
                    break;
            }
        }

        public static function columns($columns) {

            return array(
                'cb' => '<input type="checkbox" />',
                'title' => __('Title'),
                'client' => __('Client', 'fakturo'),
                'payment_status' => __('Payment Status', 'fakturo'),
                'saledate' => __('Date'),
            );
        }

        public static function manage_columns($column, $post_id) {
            $sale_data = self::get_sale_data($post_id);
            $setting_system = get_option('fakturo_system_options_group', false);
            if (empty($sale_data['invoice_currency'])) {
                $currencyDefault = get_fakturo_term($setting_system['currency'], 'fktr_currencies');
            } else {
                $currencyDefault = get_fakturo_term($sale_data['invoice_currency'], 'fktr_currencies');
            }
            switch ($column) {
                case 'payment_status':
                    if (empty($sale_data['receipts'])) {
                        _e('Unpaid', 'fakturo');
                    } else {
                        _e('Receipts Numbers: Amount', 'fakturo');
                        echo '<br/>';
                        foreach ($sale_data['receipts'] as $receipt_id => $affected) {
                            if (is_wp_error($currencyDefault)) {
                                return false;
                            }
                            $receipt_data = fktrPostTypeReceipts::get_receipt_data($receipt_id);
                            echo $receipt_data['post_title'] . ': ' . (($setting_system['currency_position'] == 'before') ? $currencyDefault->symbol . ' ' : '') . '' . number_format($affected, $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']) . '' . (($setting_system['currency_position'] == 'after') ? ' ' . $currencyDefault->symbol : '') . '';
                        }
                    }
                    break;
                    
                case 'client':
                    if ($sale_data['client_id']=="0") {
                        _e('No Client', 'fakturo');
                    } else {
                        echo $sale_data['client_data']['name'];
                    }
                    break;

                case 'saledate':
                    if(isset($sale_data['date']) && !is_numeric($sale_data['date'])) {
                        echo $sale_data['date'];
                    }else{
                        echo date_i18n($setting_system['dateformat'], $sale_data['date'] );
                    }
                    break;
            }
        }

        public static function print_invoice() {
            if (!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'fktr_sale_action_nonce')) {
                wp_die('A security issue has occurred.');
            }
            $object = new stdClass();
            $object->type = 'post';
            $object->id = $_REQUEST['id'];
            $object->assgined = 'fktr_sale';
            if ($object->id) {
                $id_print_template = fktrPostTypePrintTemplates::get_id_by_assigned($object->assgined);
                if ($id_print_template) {
                    $print_template = fktrPostTypePrintTemplates::get_print_template_data($id_print_template);
                } else {
                    wp_die(__('No print template assigned to sales invoices', 'fakturo'));
                }
                $tpl = new fktr_tpl;
                $tpl = apply_filters('fktr_print_template_assignment', $tpl, $object, false);
                $html = $tpl->fromString($print_template['content']);
                if (isset($_REQUEST['pdf'])) {
                    $pdf = fktr_pdf::getInstance();
                    $pdf->set_paper("A4", "portrait");
                    $pdf->load_html(utf8_decode($html));
                    $pdf->render();
                    $pdf->stream('pdf.pdf', array('Attachment' => 0));
                } else {
                    echo $html;
                }

                exit();
            }
        }

        public static function print_demo_invoice() {

            if (!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'fktr_sale_action_nonce')) {
                wp_die('A security issue has occurred.');
            }
            $object = new stdClass();
            $object->type = 'post';
            $object->id = $_REQUEST['id'];
            $object->assgined = 'fktr_sale';
            if ($object->id) {
                $id_print_template = fktrPostTypePrintTemplates::get_id_by_assigned($object->assgined);
                if ($id_print_template) {
                    $print_template = fktrPostTypePrintTemplates::get_print_template_data($id_print_template);
                } else {
                    wp_die(__('No print template assigned to sales invoices', 'fakturo'));
                }
                $tpl = new fktr_tpl;
                $tpl = apply_filters('fktr_print_template_assignment', $tpl, $object, false);
                $html = $tpl->fromString($print_template['content']);
                $html .= '<style>
			#demo {
				position:absolute;
				left:235px;
				top:220px;
				width:290px;
				opacity: 0.5;
				text-align:center;
			}
			</style>
			<img id="demo" src="' . FAKTURO_PLUGIN_URL . 'assets/images/demo.png">
			';

                if (isset($_REQUEST['pdf'])) {
                    $pdf = fktr_pdf::getInstance();
                    $pdf->set_paper("A4", "portrait");
                    $pdf->load_html(utf8_decode($html));
                    $pdf->render();
                    $pdf->stream('pdf.pdf', array('Attachment' => 0));
                } else {
                    echo $html;
                }

                exit();
            }
        }

        public static function change_button_texts($safe_text, $text) {
            global $post, $current_screen, $screen;

            if (isset($post) && $post->post_type == 'fktr_sale') {
                switch ($safe_text) {
                    case __('Save Draft');
                        $safe_text = __('Save as Pendient', 'fakturo');
                        break;

                    case __('Publish');
                        $safe_text = __('Finish Invoice', 'fakturo');
                        break;

                    default:
                        $safe_text = str_replace(__('Drafts'), __('Pendings', 'fakturo'), $safe_text);
                        $safe_text = str_replace(__('Draft'), __('Pending', 'fakturo'), $safe_text);
                        break;
                }
            }
            return $safe_text;
        }

        /**
         * Change Draft by Pending status texts.
         *
         */
        public static function display_fktr_sale_state($states) {
            global $post;
            if( isset($states['draft'])) {
                //PS: first replace plurals Drafts by Pendings as if found, then singular will not be found later.
                $states['draft']= str_replace(__('Drafts'), __('Pendings', 'fakturo'), $states['draft']); 
                $states['draft']= str_replace(__('Draft'), __('Pending', 'fakturo'), $states['draft']);
            }        
            return $states;
        }
        
        public static function custom_draft_translation( $views ) {
            $views['draft'] = str_replace(__('Draft'), __('Pending', 'fakturo'), $views['draft']);
            return $views;
        }


        public static function set_delete_post_link($delink, $post_id, $force_delete) {
            global $post;
            if ($post->post_type == 'fktr_sale') {
                $setting_system = get_option('fakturo_system_options_group', false);
                if ($post->post_status == 'publish') { // don't allow delete
                    $delink = "javascript:alert('" . __('Cannot delete finished Invoices', 'fakturo') . "');";
                }
                if ($setting_system['use_stock_product'] && $post->post_status == 'draft') {
                    //$delink = str_replace('trash', 'delete', $delink);
                    $action = 'delete';
                    $post_type_object = get_post_type_object($post->post_type);
                    $delete_link = add_query_arg('action', $action, admin_url(sprintf($post_type_object->_edit_link, $post_id)));
                    $delete_link = wp_nonce_url($delete_link, "$action-post_{$post->ID}");

                    $delink = "$delete_link\" onclick=\"return confirm('" . __('Delete this item permanently ?', 'fakturo') . "')";
                }
            }
            return $delink;
        }

        public static function bulk_actions($actions){
            $actions['send_pdf_client'] = __( 'Send PDF to Clients', 'fakturo');
            unset($actions['edit']);
            unset($actions['trash']);

            return $actions;
        }
        public static function bulk_action_handler($redirect_to, $doaction, $post_ids) {
            if ($doaction !== 'send_pdf_client') {
                return $redirect_to;
            }
            foreach ($post_ids as $post_id) {
                fktr_mail::send_sale_invoice_pdf_to_client($post_id, false);
                // Perform action for each post.
            }
            $redirect_to = add_query_arg('bulk_emailed_posts', count($post_ids), $redirect_to);
            return $redirect_to;
        }

        public static function action_row($actions, $post) {
            if ($post->post_type == 'fktr_sale') {
                $action_nonce = wp_create_nonce('fktr_sale_action_nonce');
                $setting_system = get_option('fakturo_system_options_group', false);
                if ($post->post_status == 'publish') {
                    unset($actions['trash']);
                    // Replace the original but translated WP text: 'Edit' by 'View' also translated
                    $actions['edit'] = str_replace(__('Edit'), __('View', 'fakturo'), $actions['edit']);
                    $actions['print_invoice'] = '<a href="' . admin_url('admin-post.php?id=' . $post->ID . '&action=print_invoice&nonce=' . $action_nonce) . '" class="btn_print_invoice" target="_new">' . __('Print', 'fakturo') . '</a>';

                    if (empty($actions['send_invoice_to_client'])) {
                        $sale_data = self::get_sale_data($post->ID);
                        $client_data = fktrPostTypeClients::get_client_data($sale_data['client_id']);
                        if (!empty($client_data['email'])) {
                            $url = admin_url('admin-post.php?id=' . $post->ID . '&action=send_invoice_to_client');
                            $url = wp_nonce_url($url, 'send_invoice_to_client', '_wpnonce');
                            $actions['send_invoice_to_client'] = '<a href="' . $url . '" class="btn_send_invoice">' . __('email to Client', 'fakturo') . '</a>';
                        }
                    }
                }
                if ($post->post_status != 'trash') {
                    if ($setting_system['use_stock_product'] && $post->post_status == 'draft') {
                        unset($actions['trash']);
                        $actions['delete'] = '<a class="submitdelete" title="' . esc_attr(__('Delete this item permanently', 'fakturo')) . '" href="' . get_delete_post_link($post->ID, '', true) . '">' . __('Delete', 'fakturo') . '</a>';
                    }
                }

                if ($post->post_status == 'draft') {
                    $actions['invoice_demo'] = '<a href="' . admin_url('admin-post.php?id=' . $post->ID . '&action=print_demo_invoice&nonce=' . $action_nonce) . '" class="btn_print_demo_invoice" target="_new">' . __('Preview', 'fakturo') . '</a>';
                }

                unset($actions['inline hide-if-no-js']);
                $actions = apply_filters('fktr_sales_quick_actions', $actions, $post);
            }
            return $actions;
        }

        public static function text_description_product_short_description($txt) {
		return __( 'Short Description', 'fakturo' );
	}
	public static function text_description_product_description($txt) {
		return __( 'Description', 'fakturo' );
	}
	public static function meta_key_description_product_short_description($txt) {
		return 'title';
	}
	public static function meta_key_description_product_description($txt) {
		return 'description';
	}
	public static function text_code_product_reference($txt) {
		return __( 'Reference', 'fakturo' );
	}
	public static function text_code_product_internal_code($txt) {
		return __( 'Internal code', 'fakturo' );
	}
	public static function text_code_product_manufacturers_code($txt) {
		return __( 'Manufacturers code', 'fakturo' );
	}
	public static function meta_key_code_product_reference($txt) {
		return 'reference';
	}
	public static function meta_key_code_product_internal_code($txt) {
		return 'ID';
	}
	public static function meta_key_code_product_manufacturers_code($txt) {
		return 'manufacturers';
	}
	public static function product_parameter_reference($search, $innerJoin, $where) {
		$where = $where." OR (meta_value LIKE '%".$search."%' AND meta_key = 'reference')";
		return array($innerJoin, $where);
	}
	public static function product_parameter_internal_code($search, $innerJoin, $where) {
		if (is_numeric($search)) {
			$where = $where." OR ID = ".$search."";
		}
		return array($innerJoin, $where);
	}
	public static function product_parameter_manufacturers_code($search, $innerJoin, $where) {
		$where = $where." OR (meta_value LIKE '%".$search."%' AND meta_key = 'manufacturers')";
		return array($innerJoin, $where);
	}
	public static function get_products() {
		global $wpdb;
		$search = addslashes($_GET['s']);
		$setting_system = get_option('fakturo_system_options_group', false);
		$prefix = $wpdb->prefix;
		$innerJoin = " INNER JOIN {$prefix}postmeta ON {$prefix}postmeta.post_id = {$prefix}posts.ID ";
		$descriptionWhere = "post_title LIKE '%".$search."%'";
		if ($setting_system['default_description'] == 'short_description') {
			$descriptionWhere = "post_title LIKE '%".$search."%'";
		} else if ($setting_system['default_description']=='description') {
			$descriptionWhere = "(meta_value LIKE '%".$search."%' AND meta_key = 'description')";
		}
		$where = " {$prefix}posts.post_status = 'publish' AND {$prefix}posts.post_type ='fktr_product' AND (".$descriptionWhere."";
		
		foreach ($setting_system['search_code'] as $k => $val) {
			$values = apply_filters('fktr_search_product_parameter_'.$val, $search, $innerJoin, $where);
			$innerJoin = $values[0];
			$where = $values[1];
		}
		$where = $where.")";
		$sqlSearch = "SELECT * FROM {$prefix}posts".$innerJoin." WHERE".$where." GROUP BY {$prefix}posts.ID LIMIT 10";
	
		$sqlSearch = apply_filters('fktr_search_product_sql_query', $sqlSearch);
		$results = $wpdb->get_results($sqlSearch, OBJECT);
		
		
		
		$return = new stdClass();
		$return->total_count = 1;
		$return->incomplete_results = false;
		$return->items = array();
		$dataProduct = array();
		foreach ($results as $post) {
			$newProduct = new stdClass();
			$dataProduct = fktrPostTypeProducts::get_product_data($post->ID);
			$newProduct->id = $post->ID;
			$newProduct->title = $post->post_title;
			$newProduct->description = $dataProduct['description'];
			$newProduct->img = FAKTURO_PLUGIN_URL . 'assets/images/default_product.png';
			$newProduct->datacomplete = $dataProduct;
			
			if (isset($dataProduct['_thumbnail_id']) && $dataProduct['_thumbnail_id'] > 0) {
				$newProduct->img = wp_get_attachment_url( get_post_thumbnail_id($post->ID));
			} 
			$return->items[] = $newProduct;
			
		}
		
		echo json_encode($return);
		wp_die();
	}

        public static function setup() {
		
		$labels = array( 
			'name' => __( 'Sales', 'fakturo' ),
			'singular_name' => __( 'Sale', 'fakturo' ),
			'add_new' => __( 'Add New', 'fakturo' ),
			'add_new_item' => __( 'Add New Sale', 'fakturo' ),
			'edit_item' => __( 'Edit Sale', 'fakturo' ),
			'new_item' => __( 'New Sale', 'fakturo' ),
			'view_item' => __( 'View Sale', 'fakturo' ),
			'search_items' => __( 'Search Sales', 'fakturo' ),
			'not_found' => __( 'No Sales found', 'fakturo' ),
			'not_found_in_trash' => __( 'No Sales found in Trash', 'fakturo' ),
			'parent_item_colon' => __( 'Parent Sale:', 'fakturo' ),
			'menu_name' => __( 'Sales', 'fakturo' ),
		);
		$capabilities = array(
			'publish_post' => 'publish_fktr_sale',
			'publish_posts' => 'publish_fktr_sales',
			'read_post' => 'read_fktr_sale',
			'read_private_posts' => 'read_private_fktr_sales',
			'edit_post' => 'edit_fktr_sale',
			'edit_published_posts' => 'edit_published_fktr_sales',
			'edit_private_posts' => 'edit_private_fktr_sales',
			'edit_posts' => 'edit_fktr_sales',
			'edit_others_posts' => 'edit_others_fktr_sales',
			'delete_post' => 'delete_fktr_sale',
			'delete_posts' => 'delete_fktr_sales',
			'delete_published_posts' => 'delete_published_fktr_sales',
			'delete_private_posts' => 'delete_private_fktr_sales',
			'delete_others_posts' => 'delete_others_fktr_sales',
		);

		$args = array( 
			'labels' => $labels,
			'hierarchical' => false,
			'description' => 'Fakturo Sales',
			'supports' => array( 'title',/* 'custom-fields' */),
			'register_meta_box_cb' => array('fktrPostTypeSales','meta_boxes'),
			'public' => false,
			'show_ui' => true,
			'show_in_menu' => false, 
			'menu_position' => 26,
			'menu_icon' => 'dashicons-cart', 
			'show_in_nav_menus' => false,
			'publicly_queryable' => false,
			'exclude_from_search' => false,
			'has_archive' => false,
			'query_var' => true,
			'can_export' => true,
			'rewrite' => true,
			'capabilities' => $capabilities
		);

		register_post_type( 'fktr_sale', $args );

		//Placeholder for the title field
		add_filter('enter_title_here', array('fktrPostTypeSales', 'name_placeholder'),10,2);
		
	}
	
        public static function updated_messages($messages) {
            global $post, $post_ID;
		$messages['fktr_sale'] = array(
			 0 => '', 
			 1 => __('Sale invoice updated.', 'fakturo' ),
			 2 => '',
			 3 => '',
			 4 => __( 'Sale invoice updated.', 'fakturo' ),
			 5 => '',
			 6 => __('Sale invoice published.', 'fakturo' ),
			 7 => __('Sale invoice saved.', 'fakturo' ),
			 8 => __('Sale invoice submitted.', 'fakturo' ),
			 9 => sprintf(__('Sale scheduled for: <strong>%1$s</strong>.', 'fakturo' ), date_i18n( __( 'M j, Y @ G:i', 'fakturo' ), strtotime( $post->post_date ) )),
			10 => __('Pending invoice updated.', 'fakturo' ),
		);
		return $messages;
	}
	public static function name_placeholder( $title_placeholder , $post ) {
		if($post->post_type == 'fktr_sale') {
			$title_placeholder = __('Your invoice number', 'fakturo' );
			
		}
		return $title_placeholder;
	}
	public static function get_client_data() {
		if (!is_numeric($_POST['client_id'])) {
			$_POST['client_id'] = 0;
		}
	
		$client_data = fktrPostTypeClients::get_client_data($_POST['client_id']);
		
		
		$country_name = __('No country', 'fakturo' );
		$country_data = get_fakturo_term($client_data['selected_country'], 'fktr_countries');
		if(!is_wp_error($country_data)) {
			$country_name = $country_data->name;
		}
		$client_data['selected_country_name'] = $country_name;
		
		$state_name = __('No state', 'fakturo' );
		$state_data = get_fakturo_term($client_data['selected_state'], 'fktr_countries');
		if(!is_wp_error($state_data)) {
			$state_name = $state_data->name;
		}
		$client_data['selected_state_name'] = $state_name;
		
		$price_scale_name = __('No price scale', 'fakturo' );
		$price_scale_data = get_fakturo_term($client_data['selected_price_scale'], 'fktr_price_scales');
		if(!is_wp_error($price_scale_data)) {
			$price_scale_name = $price_scale_data->name;
		}
		$client_data['selected_price_scale_name'] = $price_scale_name;
		
		echo json_encode($client_data);
		wp_die();
	}
	public static function styles() {
		global $post_type;
		if($post_type == 'fktr_sale') {
			wp_enqueue_style('style-select2',FAKTURO_PLUGIN_URL .'assets/css/select2.min.css');	
			wp_enqueue_style('post-type-sales',FAKTURO_PLUGIN_URL .'assets/css/post-type-sales.css');	
			wp_enqueue_style('style-datetimepicker',FAKTURO_PLUGIN_URL .'assets/css/jquery.datetimepicker.css');	
		}
	}
	public static function scripts_list() {
		global $current_screen;
		if ($current_screen->post_type == 'fktr_sale') {
			wp_enqueue_script( 'post-type-sales-list', FAKTURO_PLUGIN_URL . 'assets/js/post-type-sales-list.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
		}
		
	}
	public static function scripts() {
            global $post_type, $post, $wp_locale, $locale;
            if ($post_type == 'fktr_sale') {
                wp_dequeue_script('autosave');
                wp_enqueue_script('jquery-select2', FAKTURO_PLUGIN_URL . 'assets/js/jquery.select2.js', array('jquery'), WPE_FAKTURO_VERSION, true);
                wp_enqueue_script('jquery-datetimepicker', FAKTURO_PLUGIN_URL . 'assets/js/jquery.datetimepicker.js', array('jquery'), WPE_FAKTURO_VERSION, true);
                wp_enqueue_script('jquery-vsort', FAKTURO_PLUGIN_URL . 'assets/js/jquery.vSort.js', array('jquery'), WPE_FAKTURO_VERSION, true);
                wp_enqueue_script('jquery-mask', FAKTURO_PLUGIN_URL . 'assets/js/jquery.mask.min.js', array('jquery'), WPE_FAKTURO_VERSION, true);
                wp_enqueue_script('post-type-sales', FAKTURO_PLUGIN_URL . 'assets/js/post-type-sales.js', array('jquery'), WPE_FAKTURO_VERSION, true);
                $sale_data = self::get_sale_data($post->ID);
                $product_data = array();
                if (!empty($sale_data['uc_id'])) {
                    foreach ($sale_data['uc_id'] as $key => $product_id) {
                        $newProduct = new stdClass();
                        $dataProduct = fktrPostTypeProducts::get_product_data($product_id);
                        $newProduct->id = $product_id;
                        $newProduct->title = $dataProduct['post_title'];
                        $newProduct->description = $dataProduct['description'];
                        $newProduct->img = FAKTURO_PLUGIN_URL . 'assets/images/default_product.png';
                        $newProduct->datacomplete = $dataProduct;

                        if (isset($dataProduct['_thumbnail_id']) && $dataProduct['_thumbnail_id'] > 0) {
                            $newProduct->img = wp_get_attachment_url(get_post_thumbnail_id($product_id));
                        }
                        $product_data[$product_id] = $newProduct;
                    }
                }
                $setting_system = get_option('fakturo_system_options_group', false);

                $objectL10n = (object) array(
                            'lang' => substr($locale, 0, 2),
                            'UTC' => get_option('gmt_offset'),
                            'timeFormat' => get_option('time_format'),
                            'dateFormat' => self::date_format_php_to_js($setting_system['dateformat']),
                            'printFormat' => self::date_format_php_to_js($setting_system['dateformat']),
                            'firstDay' => get_option('start_of_week'),
                );

                $tax_coditions = get_fakturo_terms(array(
                    'taxonomy' => 'fktr_tax_conditions',
                    'hide_empty' => false,
                ));

                $currencies = get_fakturo_terms(array(
                    'taxonomy' => 'fktr_currencies',
                    'hide_empty' => false,
                ));
                $taxes = get_fakturo_terms(array(
                    'taxonomy' => 'fktr_tax',
                    'hide_empty' => false,
                ));
                $invoice_types = get_fakturo_terms(array(
                    'taxonomy' => 'fktr_invoice_types',
                    'hide_empty' => false,
                ));
                $sale_points = get_fakturo_terms(array(
                    'taxonomy' => 'fktr_sale_points',
                    'hide_empty' => false,
                ));
                $locations = get_fakturo_terms(array(
                    'taxonomy' => 'fktr_locations',
                    'hide_empty' => false,
                ));

                wp_localize_script('post-type-sales', 'sales_object',
                        array('ajax_url' => admin_url('admin-ajax.php'),
                            'thousand' => $setting_system['thousand'],
                            'decimal' => $setting_system['decimal'],
                            'decimal_numbers' => $setting_system['decimal_numbers'],
                            'currency_position' => $setting_system['currency_position'],
                            'default_currency' => $setting_system['currency'],
                            'default_code' => $setting_system['default_code'],
                            'digits_invoice_number' => $setting_system['digits_invoice_number'],
                            'list_invoice_number' => $setting_system['list_invoice_number'],
                            'list_invoice_number_separator' => apply_filters('fktr_invoice_number_separator', $setting_system['list_invoice_number_separator']),
                            'use_stock_product' => $setting_system['use_stock_product'],
                            'post_status' => $post->post_status,
                            'product_data' => $product_data,
                            'datetimepicker' => $objectL10n,
                            'code_meta_post_key' => apply_filters('fktr_meta_key_code_product_' . $setting_system['default_code'], 'internal'),
                            'description_meta_post_key' => apply_filters('fktr_meta_key_description_product_' . $setting_system['default_description'], 'title'),
                            'characters_to_search' => apply_filters('fktr_sales_characters_to_search_product', 3),
                            'url_loading_image' => get_bloginfo('wpurl') . '/wp-admin/images/wpspin_light.gif',
                            'txt_cost' => __('Cost', 'fakturo'),
                            'txt_search_products' => __('Search products...', 'fakturo'),
                            'txt_total_quantity' => __('Total quantity', 'fakturo'),
                            'txt_remaining' => __('Remaining', 'fakturo'),
                            'txt_loading' => __('Loading', 'fakturo'),
                            'txt_no_stock' => __('Stock not available', 'fakturo'),
                            'txt_exc_stock' => __('Stock exceeded', 'fakturo'),
                            'txt_max' => __('Max', 'fakturo'),
                            'txt_min' => __('Min', 'fakturo'),
                            'txt_product_alert_min' => __('Alert of minimal stock', 'fakturo'),
                            'txt_cancel' => __('Cancel', 'fakturo'),
                            'tax_coditions' => json_encode($tax_coditions),
                            'currencies' => json_encode($currencies),
                            'taxes' => json_encode($taxes),
                            'invoice_types' => json_encode($invoice_types),
                            'sale_points' => $sale_points,
                            'locations' => $locations,
                ));
            }
        }

        public static function meta_boxes() {

		add_meta_box('fakturo-currencies-box', __('Currencies', 'fakturo' ), array('fktrPostTypeSales', 'currencies_box'),'fktr_sale','side', 'high' );
		add_meta_box('fakturo-discount-box', __('Discount', 'fakturo' ), array('fktrPostTypeSales', 'discount_box'),'fktr_sale','side', 'high' );
		
		add_meta_box('fakturo-invoice-data-box', __('Invoice Data', 'fakturo' ), array('fktrPostTypeSales', 'invoice_data_box'),'fktr_sale','normal', 'high' );
		add_meta_box('fakturo-invoice-box', __('Invoice', 'fakturo' ), array('fktrPostTypeSales', 'invoice_box'),'fktr_sale','normal', 'high' );
		do_action('add_ftkr_sale_meta_boxes');
	}

        public static function invoice_box() {
		global $post;
		$sale_data = self::get_sale_data($post->ID);
		
		
		$setting_system = get_option('fakturo_system_options_group', false);
		if (empty($sale_data['invoice_currency'])) {
			$currencyDefault = get_fakturo_term($setting_system['currency'], 'fktr_currencies');
		} else {
			$currencyDefault = get_fakturo_term($sale_data['invoice_currency'], 'fktr_currencies');		
		}
		$discriminates_taxes = false;
		if ($sale_data['invoice_type'] > 0) {
			$term_invoice_type = get_fakturo_term($sale_data['invoice_type'], 'fktr_invoice_types');		
			if (!is_wp_error($term_invoice_type)) {
				if ($term_invoice_type->discriminates_taxes) {
					$discriminates_taxes = true;
				}
			}
		}
		
		$selectProducts = '<select name="product_select" id="product_select" class="js-example-basic-multiple" multiple="multiple" style="width:65%;"></select>';					
		$echoInvoiceProducts = '';
		if (!empty($sale_data['uc_id'])) {
			foreach ($sale_data['uc_id'] as $key => $product_id) {
				
				
				$htmlStocksLocations = '';
				$locations = get_fakturo_terms(array(
								'taxonomy' => 'fktr_locations',
								'hide_empty' => false,
							));
							
				foreach ($locations as $loc) {
					$value = '';
					if (isset($sale_data['product_stock_location'][$product_id][$loc->term_id])) {
						if (is_array($sale_data['product_stock_location'][$product_id][$loc->term_id])) {
							$value = array_shift($sale_data['product_stock_location'][$product_id][$loc->term_id]);
						}
					}
					$htmlStocksLocations .= '<input type="hidden" name="product_stock_location['.$product_id.']['.$loc->term_id.'][]" value="'.$value.'" id="product_stock_'.$key.'_'.$loc->term_id.'" class="product_stock_input">';
				}
				
				
				$codeProduct = $sale_data['uc_code'][$key]; 
				$descriptionProduct = $sale_data['uc_description'][$key];
				$quantityProduct = $sale_data['uc_quality'][$key];
				$unitPriceProduct = number_format($sale_data['uc_unit_price'][$key], $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']);
				$taxProduct = $sale_data['uc_tax'][$key];
				$taxPorcentProduct = $sale_data['uc_tax_porcent'][$key];
				$amountProduct = number_format($sale_data['uc_amount'][$key], $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']);



				$echoInvoiceProducts .= '
				<div id="uc_ID'.$key.'" class="sortitem" data-identifier="'.$key.'">
					<div class="sorthandle"></div> 
					<div class="uc_column" id="">
						<label class="code_product" id="label_code_'.$key.'">'.$codeProduct.'</label>
						'.$htmlStocksLocations.'
						<input name="uc_code[]" type="hidden" id="code_'.$key.'" value="'.$codeProduct.'" class="large-text"/> 
						<input name="uc_id[]" type="hidden" id="id_'.$key.'" value="'.$product_id.'"/>
					</div>
					<div class="uc_column" id="">
					'.(($post->post_status != 'publish')?'<input name="uc_description[]" type="text" id="description_'.$key.'" value="'.$descriptionProduct.'" class="large-text"/>':'<label class="code_product">'.$descriptionProduct.'</label>').'
						
					</div>
					<div class="uc_column" id="">
						'.(($post->post_status != 'publish')?'<input name="uc_quality[]" class="product_quality" id="quality_'.$key.'" type="text" value="'.$quantityProduct.'" class="large-text"/>':'<label class="code_product">'.$quantityProduct.'</label>').'
						
					</div>
					<div class="uc_column" id="">
						'.(($post->post_status != 'publish')?'<input name="uc_unit_price[]" id="unit_price_'.$key.'" type="text" value="'.$unitPriceProduct.'" class="unit_price_products large-text"/>':'<label class="code_product">'.$unitPriceProduct.'</label>').'
						
					</div>
					<div class="uc_column taxes_column"'.(($discriminates_taxes)?'':' style="display:none;"').'>
						<label class="code_product" id="label_tax_product_'.$key.'">'.$taxPorcentProduct.'%</label>
						<input name="uc_tax_porcent[]" type="hidden" id="tax_porcent_product_'.$key.'" value="'.$taxPorcentProduct.'" class="product_tax_porcent"/>
						<input name="uc_tax[]" type="hidden" id="tax_product_'.$key.'" value="'.$taxProduct.'" class="product_taxs large-text"/>
					</div>
					<div class="uc_column" id="">
						'.(($post->post_status != 'publish')?'<input name="uc_amount[]" id="amount_'.$key.'" type="text" value="'.$amountProduct.'" class="products_amounts large-text"/>':'<label class="code_product">'.$amountProduct.'</label>').'
						
					
					</div>
					'.(($post->post_status != 'publish')?'<div class="" id="uc_actions"><label title="" data-id="'.$key.'" class="delete"></label></div>':'').'
						
					
				</div>';
	
			
			
			}
			
		}
		
		$sub_total = 0;
		if (!empty($sale_data['in_sub_total'])) {
			$sub_total = $sale_data['in_sub_total']; 
		}
		$discount = 0;
		if (!empty($sale_data['in_discount'])) {
			$discount = $sale_data['in_discount']; 
		}
		$total = 0;
		if (!empty($sale_data['in_total'])) {
			$total = $sale_data['in_total']; 
		}
		
		$taxes = false;
		$htmltaxes = '';
		if (!empty($sale_data['taxes_in_products'])) {
			foreach ($sale_data['taxes_in_products'] as $key => $value) {
				$taxPorcent = 0;
				$taxName = 'Tax';
				if ($key > 0) {
					$term_tax = get_fakturo_term($key, 'fktr_tax');
					if(!is_wp_error($term_tax)) {
						$taxPorcent = $term_tax->percentage;
						$taxName = $term_tax->name;
					} 
				} else {
					if ($sale_data['client_data']['tax_condition'] > 0) {
						$term_tax_condition = get_fakturo_term($sale_data['client_data']['tax_condition'], 'fktr_tax_conditions');
						if(!is_wp_error($term_tax_condition)) {
							if ($term_tax_condition->overwrite_taxes) {
								$taxPorcent = $term_tax_condition->tax_percentage;
							}
						} 
					}
				}
				
				$htmltaxes .= '<label id="label_tax_in_'.$key.'">'.$taxName.' '.fakturo_porcent_to_mask($taxPorcent).'%:'.(($setting_system['currency_position'] == 'before')?$currencyDefault->symbol.' ':'').''.number_format($value, $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']).''.(($setting_system['currency_position'] == 'after')?' '.$currencyDefault->symbol:'').'</label> <input type="hidden" name="taxes_in_products['.$key.']" value="'.$value.'"/>';
			}
		}
		
		$textCodeForProduct = apply_filters('fktr_text_code_product_'.$setting_system['default_code'], '');
		$textDescriptionForProduct = apply_filters('fktr_text_description_product_'.$setting_system['default_description'], '');
		$echoHtml = '<div id="popup_stock_background" style="display:none;"></div> <div id="product_stock_popup" style="display:none;"></div><table class="form-table">
					<tbody>
						<tr class="user-display-name-wrap">
						<td>
							<div class="uc_header">
								<div class="uc_column"></div>
								<div class="uc_column">'.$textCodeForProduct.'</div>
								<div class="uc_column">'.$textDescriptionForProduct.'</div>
								<div class="uc_column">'. __('Quantity', 'fakturo'  ) .'</div>
								<div class="uc_column">'. __('Unit price', 'fakturo'  ) .'</div>
								<div class="uc_column taxes_column"'.(($discriminates_taxes)?'':' style="display:none;"').'>'. __('Tax', 'fakturo'  ) .'</div>
								<div class="uc_column">'. __('Amount', 'fakturo'  ) .'</div>
								
							</div>
							<br />
			
							<div id="invoice_products"> 
								'.$echoInvoiceProducts.'
							</div>
							
							<div id="paging-box">
								'.(($post->post_status != 'publish')?''.$selectProducts.' <a href="#" class="button-primary add" id="addmoreuc" style="font-weight: bold; text-decoration: none; height: 31px;line-height: 29px;"> '.__('Add product', 'fakturo'  ).'</a>':'').'
								<div id="errors_stocks" style="display:none;">
									
								</div>
							</div>
							<div id="totals-box">
								<div id="sub_total">Subtotal: <label id="label_sub_total">'.(($setting_system['currency_position'] == 'before')?$currencyDefault->symbol.' ':'').''.number_format($sub_total, $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']).''.(($setting_system['currency_position'] == 'after')?' '.$currencyDefault->symbol:'').'</label><input type="hidden" name="in_sub_total" id="in_sub_total" value="'.$sub_total.'"/>  </div>
								<div id="discount_total"'.(($discount>0)?'':' style="display:none;"').'>Discount: <label id="label_discount">'.(($setting_system['currency_position'] == 'before')?$currencyDefault->symbol.' ':'').''.number_format($discount, $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']).''.(($setting_system['currency_position'] == 'after')?' '.$currencyDefault->symbol:'').'</label><input type="hidden" name="in_discount" id="in_discount" value="'.$discount.'"/> </div>
								<div id="tax_total"'.(($discriminates_taxes)?'':' style="display:none;"').'>'.$htmltaxes.'</div>
								<div id="total">Total: <label id="label_total">'.(($setting_system['currency_position'] == 'before')?$currencyDefault->symbol.' ':'').''.number_format($total, $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand']).''.(($setting_system['currency_position'] == 'after')?' '.$currencyDefault->symbol:'').'</label><input type="hidden" name="in_total" id="in_total" value="'.$total.'"/>  </div>
							</div>
						</td>
						</tr>
					</tbody>
				</table>';
	
		$echoHtml = apply_filters('fktr_sale_invoice_box', $echoHtml);
		echo $echoHtml;
		do_action('add_fktr_sale_invoice_box', $echoHtml);
		
	}
	public static function invoice_data_box() {
		global $post;
		$sale_data = self::get_sale_data($post->ID);
		
		$setting_system = get_option('fakturo_system_options_group', false);
		$selectInvoiceTypes = 'No invoice type';
		
		if ($post->post_status == 'draft') {
			$term_invoice_type = get_fakturo_term($sale_data['invoice_type'], 'fktr_invoice_types');
			if(!is_wp_error($term_invoice_type)) {
				
				$invoice_types = get_fakturo_terms(array(
							'taxonomy' => 'fktr_invoice_types',
							'hide_empty' => false,
				));
				$selectInvoiceTypes = '<select name="invoice_type" id="invoice_type">';
				foreach ($invoice_types as $invT) {
					if ($invT->sum == $term_invoice_type->sum) {
						$selectInvoiceTypes .= '<option value="' . $invT->term_id . '" ' . selected($sale_data['invoice_type'], $invT->term_id, false) . '>' . esc_html($invT->name) . '</option>';
					}
				}
				$selectInvoiceTypes .= '</select>';
			}
		} else if ($post->post_status != 'publish') {
			$selectInvoiceTypes = wp_dropdown_categories( array(
				'show_option_all'    => '',
				'show_option_none'   => __('Choose a Invoice Type', 'fakturo' ),
				'orderby'            => 'name', 
				'order'              => 'ASC',
				'show_count'         => 0,
				'hide_empty'         => 0, 
				'child_of'           => 0,
				'exclude'            => '',
				'echo'               => 0,
				'selected'           => $sale_data['invoice_type'],
				'hierarchical'       => 1, 
				'name'               => 'invoice_type',
				'class'              => 'form-no-clear',
				'id'				 => 'invoice_type',
				'depth'              => 1,
				'tab_index'          => 0,
				'taxonomy'           => 'fktr_invoice_types',
				'hide_if_empty'      => false
			));
		} else {
			$term_invoice_type = get_fakturo_term($sale_data['invoice_type'], 'fktr_invoice_types');
			if(!is_wp_error($term_invoice_type)) {
				$selectInvoiceTypes = $term_invoice_type->name;
			}
		}
		
		$selectSalePoint = 'No Sale Point';
		if ($post->post_status != 'publish') {
			$selectSalePoint = wp_dropdown_categories( array(
				'show_option_all'    => '',
				'show_option_none'   => __('Choose a Sale Point', 'fakturo' ),
				'orderby'            => 'name', 
				'order'              => 'ASC',
				'show_count'         => 0,
				'hide_empty'         => 0, 
				'child_of'           => 0,
				'exclude'            => '',
				'echo'               => 0,
				'selected'           => $sale_data['sale_point'],
				'hierarchical'       => 1, 
				'name'               => 'sale_point',
				'class'              => 'form-no-clear',
				'id'				 => 'sale_point',
				'depth'              => 1,
				'tab_index'          => 0,
				'taxonomy'           => 'fktr_sale_points',
				'hide_if_empty'      => false
			));
		} else {
			$term_sale_point = get_fakturo_term($sale_data['sale_point'], 'fktr_sale_points');
			if(!is_wp_error($term_sale_point)) {
				$selectSalePoint = $term_sale_point->name;
			}
		}
		
		
		$selected_currency = $setting_system['currency'];
		if ($sale_data['invoice_currency'] > 0) {
			$selected_currency = $sale_data['invoice_currency'];
		}
		$selectCurrencies = 'No currency';
		if ($post->post_status != 'publish') {
			$selectCurrencies = wp_dropdown_categories( array(
				'show_option_all'    => '',
				'show_option_none'   => __('Choose a Currency', 'fakturo' ),
				'orderby'            => 'name', 
				'order'              => 'ASC',
				'show_count'         => 0,
				'hide_empty'         => 0, 
				'child_of'           => 0,
				'exclude'            => '',
				'echo'               => 0,
				'selected'           => $selected_currency,
				'hierarchical'       => 1, 
				'name'               => 'invoice_currency',
				'class'              => 'form-no-clear',
				'id'				 => 'invoice_currency',
				'depth'              => 1,
				'tab_index'          => 0,
				'taxonomy'           => 'fktr_currencies',
				'hide_if_empty'      => false
			));
		} else {
			$term_currency = get_fakturo_term($selected_currency, 'fktr_currencies');
			if(!is_wp_error($term_currency)) {
				$selectCurrencies = $term_currency->name;
			}
		}
		$select_sale_mans = 'No Saleman';
		if ($post->post_status != 'publish') {
			$allsellers = get_users( array( 'role' => 'fakturo_seller' ) );
			$allmanagers = get_users( array( 'role' => 'fakturo_manager' ) );	
			$alladmins = get_users( array( 'role' => 'administrator' ) );
			$allsellers = array_merge($allsellers, $allmanagers, $alladmins);
			$select_sale_mans = '<select name="invoice_saleman" id="invoice_saleman">';
			$select_sale_mans .= '<option value="'.(($sale_data['invoice_saleman'] == 0)?' selected="selected"':'').'">'. __('Choose a Salesman', 'fakturo'  ) . '</option>';
			foreach ( $allsellers as $suser ) {
				$select_sale_mans .= '<option value="' . $suser->ID . '" ' . selected($sale_data['invoice_saleman'], $suser->ID, false) . '>' . esc_html( $suser->display_name ) . '</option>';
			}
			$select_sale_mans .= '</select>';
		} else {
			$user_obj = get_user_by('id', $sale_data['invoice_saleman']);
			if ($user_obj) {
				$select_sale_mans = $user_obj->display_name;
			}
			
		}
		
		if ($post->post_status != 'publish') {
			$selectClients = fakturo_get_select_post(array(
											'echo' => 0,
											'post_type' => 'fktr_client',
											'show_option_none' => __('Choose a Client', 'fakturo' ),
											'name' => 'client_id',
											'id' => 'client_id',
											'class' => '',
											'selected' => $sale_data['client_id']
										));
		} else {
			$selectClients = '';
		}
		
										
										
		
		$show_client_data = true;
		if ($sale_data['client_id'] < 1) {
			$show_client_data = false;
		}
		$selectTaxCondition = 'No tax condition';
		if ($post->post_status != 'publish') {
			$selectTaxCondition = wp_dropdown_categories( array(
				'show_option_all'    => '',
				'show_option_none'   => __('Choose a Tax Condition', 'fakturo' ),
				'orderby'            => 'name', 
				'order'              => 'ASC',
				'show_count'         => 0,
				'hide_empty'         => 0, 
				'child_of'           => 0,
				'exclude'            => '',
				'echo'               => 0,
				'selected'           => $sale_data['client_data']['tax_condition'],
				'hierarchical'       => 1, 
				'name'               => 'client_data[tax_condition]',
				'class'              => '',
				'id'				 => 'client_data_tax_condition',
				'depth'              => 1,
				'tab_index'          => 0,
				'taxonomy'           => 'fktr_tax_conditions',
				'hide_if_empty'      => false
			));
		} else {
			$term_tax_codition = get_fakturo_term($sale_data['client_data']['tax_condition'], 'fktr_tax_conditions');
			if(!is_wp_error($term_tax_codition)) {
				$selectTaxCondition = $term_tax_codition->name;
			}
		}
		$selectPaymentTypes = 'No payment type';
		$payment_type = ( ! empty($sale_data['client_data']['payment_type']) ? $sale_data['client_data']['payment_type'] : -1 );
		
		if ($post->post_status != 'publish') {
			$selectPaymentTypes = wp_dropdown_categories( array(
				'show_option_all'    => '',
				'show_option_none'   => __('Choose a Payment Type', 'fakturo' ),
				'orderby'            => 'name', 
				'order'              => 'ASC',
				'show_count'         => 0,
				'hide_empty'         => 0, 
				'child_of'           => 0,
				'exclude'            => '',
				'echo'               => 0,
				'selected'           => $payment_type,
				'hierarchical'       => 1, 
				'name'               => 'client_data[payment_type]',
				'class'              => 'form-no-clear',
				'id'				 => 'client_data_payment_type',
				'depth'              => 1,
				'tab_index'          => 0,
				'taxonomy'           => 'fktr_payment_types',
				'hide_if_empty'      => false
			));
		} else {
			$term_payment_type = get_fakturo_term($sale_data['client_data']['payment_type'], 'fktr_payment_types');
			if(!is_wp_error($term_payment_type)) {
				$selectPaymentTypes = $term_payment_type->name;
			}
		}
		
         	
                if(isset($sale_data['date']) && !is_numeric($sale_data['date'])) {
                    $date = strtotime($sale_data['date']);
                }else{
                    $date = $sale_data['date'];
                }
		$sale_data['invoice_number'] = (($post->post_status != 'publish')? str_pad(self::suggestInvoiceNumber($sale_data['sale_point'], $sale_data['invoice_type']), $setting_system['digits_invoice_number'], '0', STR_PAD_LEFT) : $sale_data['invoice_number'] );
		$echoHtml = '<table class="w-sm-100">
					<tbody>
						<tr>
							<td class="mw-50" valign="top">
								<table>
									<tbody>
									<tr class="user-address-wrap"'.(($selectClients == '')?'style="display:none;"':'').'>
										<th style="text-align:left;"><label for="client">'.__('Client', 'fakturo' ).'</label></th>
										<td style="text-align:right;">
											'.$selectClients.'
										</td>		
									</tr>	
									
									<tr class="client_data"'.($show_client_data?'':' style="display:none;"').'>
										<th style="text-align:left;">'.__('Client ID', 'fakturo' ).'</th>
										<td style="text-align:right;" id="client_data_id">
											'.$sale_data['client_id'].'
										</td>		
									</tr>
									<tr class="client_data"'.($show_client_data?'':' style="display:none;"').'>
										<th style="text-align:left;">'.__('Client name', 'fakturo' ).'</th>
										<td style="text-align:right;" id="client_name">
											'.$sale_data['client_data']['name'].'
											<input type="hidden" name="client_data[name]" value="'.$sale_data['client_data']['name'].'" id="client_data_name"/>
										</td>		
									</tr>
									<tr class="client_data"'.($show_client_data?'':' style="display:none;"').'>
										<th style="text-align:left;">'.__('Client address', 'fakturo' ).'</th>
										<td style="text-align:right;" id="client_address">
											'.$sale_data['client_data']['address'].'
											<input type="hidden" name="client_data[address]" value="'.$sale_data['client_data']['address'].'" id="client_data_address"/>
										</td>		
									</tr>
									<tr class="client_data"'.($show_client_data?'':' style="display:none;"').'>
										<th style="text-align:left;">'.__('City', 'fakturo' ).'</th>
										<td style="text-align:right;" id="client_city">
											'.$sale_data['client_data']['city'].'
											<input type="hidden" name="client_data[city]" value="'.$sale_data['client_data']['city'].'" id="client_data_city"/>
										</td>		
									</tr>
									<tr class="client_data"'.($show_client_data?'':' style="display:none;"').'>
										<th style="text-align:left;">'.__('State', 'fakturo' ).'</th>
										<td style="text-align:right;" id="client_state">
											'.$sale_data['client_data']['state']['name'].'
											<input type="hidden" name="client_data[state][id]" value="'.$sale_data['client_data']['state']['id'].'" id="client_data_state_id"/>
											<input type="hidden" name="client_data[state][name]" value="'.$sale_data['client_data']['state']['name'].'" id="client_data_state_name"/>
										</td>		
									</tr>
									<tr class="client_data"'.($show_client_data?'':' style="display:none;"').'>
										<th style="text-align:left;">'.__('Country', 'fakturo' ).'</th>
										<td style="text-align:right;" id="client_country">
											'.$sale_data['client_data']['country']['name'].'
											<input type="hidden" name="client_data[country][id]" value="'.$sale_data['client_data']['country']['id'].'" id="client_data_country_id"/>
											<input type="hidden" name="client_data[country][name]" value="'.$sale_data['client_data']['country']['name'].'" id="client_data_country_name"/>
										</td>		
									</tr>
									<tr class="client_data"'.($show_client_data?'':' style="display:none;"').'>
										<th style="text-align:left;">'.__('Taxpayer ID', 'fakturo' ).'</th>
										<td style="text-align:right;" id="client_taxpayer">
											'.$sale_data['client_data']['taxpayer'].'
											<input type="hidden" name="client_data[taxpayer]" value="'.$sale_data['client_data']['taxpayer'].'" id="client_data_taxpayer"/>
										</td>		
									</tr>
									<tr class="client_data"'.($show_client_data?'':' style="display:none;"').'>
										<th style="text-align:left;">'.__('Tax condition', 'fakturo' ).'</th>
										<td style="text-align:right;" id="client_tax_condition">
											'.$selectTaxCondition.'
										</td>		
									</tr>
									<tr class="client_data"'.($show_client_data?'':' style="display:none;"').'>
										<th style="text-align:left;">'.__('Payment Type', 'fakturo' ).'</th>
										<td style="text-align:right;" id="client_payment_type">
											'.$selectPaymentTypes.'
										</td>		
									</tr>
									<tr class="client_data"'.($show_client_data?'':' style="display:none;"').'>
										<th style="text-align:left;">'.__('Price scale', 'fakturo' ).'</th>
										<td style="text-align:right;" id="client_price_scale">
											'.$sale_data['client_data']['price_scale']['name'].'
											<input type="hidden" name="client_data[price_scale][id]" value="'.$sale_data['client_data']['price_scale']['id'].'" id="client_data_price_scale_id"/>
											<input type="hidden" name="client_data[price_scale][name]" value="'.$sale_data['client_data']['price_scale']['name'].'" id="client_data_price_scale_name"/>
										</td>		
									</tr>
									<tr class="client_data"'.($show_client_data?'':' style="display:none;"').'>
										<th style="text-align:left;">'.__('Credit limit', 'fakturo' ).'</th>
										<td style="text-align:right;" id="client_credit_limit">
											'.$sale_data['client_data']['credit_limit'].'
											<input type="hidden" name="client_data[credit_limit]" value="'.$sale_data['client_data']['credit_limit'].'" id="client_data_credit_limit"/>
										</td>		
									</tr>
									</tbody>
								</table>
							</td>
							<td class="mw-50">
								<table class="form-table">
									<tbody>
										<tr>
											<th><label for="invoice_type">'.__('Invoice Type', 'fakturo' ).'</label></th>
											<td>
												'.$selectInvoiceTypes.'
											</td>		
										</tr>
										<tr>
											<th><label for="sale_point">'.__('Sale Point', 'fakturo' ).'</label></th>
											<td>
												'.$selectSalePoint.'
											</td>		
										</tr>
										
										<tr>
											<th><label for="invoice_number">'.__('Invoice Number', 'fakturo' ).'</label></th>
											<td>
												'.(($post->post_status != 'publish')?'<input type="text" name="invoice_number" id="invoice_number" value="'.$sale_data['invoice_number'].'"/>':$sale_data['invoice_number']).'
											</td>		
										</tr>
										
										<tr>
											<th><label for="date">'.__('Date', 'fakturo' ).'</label></th>
											<td>
												'.(($post->post_status != 'publish')?'<input type="text" name="date" id="date" value="'.date_i18n($setting_system['dateformat'], $date ).'"/>':date_i18n($setting_system['dateformat'], $date )).'
											</td>		
										</tr>
										<tr>
											<th><label for="invoice_currency">'.__('Invoice Currency', 'fakturo' ).'</label></th>
											<td>
												'.$selectCurrencies.'
											</td>		
										</tr>
										<tr>
											<th><label for="invoice_saleman">'.__('Salesman', 'fakturo' ).'</label></th>
											<td>
												'.$select_sale_mans.'
											</td>		
										</tr>
									</tbody>
								</table>
								
							</td>		
						</tr>
			
				
			</tbody>
		</table>';
	
		$echoHtml = apply_filters('fktr_sale_client_box', $echoHtml);
		echo $echoHtml;
		do_action('add_fktr_sale_client_box', $echoHtml);
		
	}
	
	public static function currencies_box() {
            global $post;
            $sale_data = self::get_sale_data($post->ID);
            $setting_system = get_option('fakturo_system_options_group', false);

            $currencies = get_fakturo_terms(array(
                'taxonomy' => 'fktr_currencies',
                'hide_empty' => false,
                'exclude' => $setting_system['currency']
            ));

            $echoHtml = '<table><tbody>';

            foreach ($currencies as $cur) {
                $echoHtml .= '<tr>
                        <td>' . ((empty($cur->reference)) ? '' : '<a href="' . $cur->reference . '" target="_blank">') . '' . $cur->name . '' . ((empty($cur->reference)) ? '' : '</a>') . '</td>' . (($setting_system['currency_position'] == 'before') ? '<td><label for="invoice_currencies_' . $cur->term_id . '">' . $cur->symbol . '</label></td>' : '') . '<td>' . (($post->post_status != 'publish') ? '<input type="text" style="text-align: right; width: 120px;" value="' . $cur->rate . '" name="invoice_currencies[' . $cur->term_id . ']" id="invoice_currencies_' . $cur->term_id . '" class="invoice_currencies"/> ' : number_format($cur->rate, $setting_system['decimal_numbers'], $setting_system['decimal'], $setting_system['thousand'])) . '' . (($setting_system['currency_position'] == 'after') ? '<td><label for="invoice_currencies_' . $cur->term_id . '">' . $cur->symbol . '</label></td>' : '') . '</td>
                </tr>';
            }

            $echoHtml .= '</tbody></table>';

            $echoHtml = apply_filters('fktr_sale_currencies_box', $echoHtml);
            echo $echoHtml;
            do_action('add_fktr_sale_currencies_box', $echoHtml);
        }

        public static function discount_box() {
            global $post;
            $sale_data = self::get_sale_data($post->ID);
            $echoHtml = '<table>
                    <tbody>
                            <td>%</td>
                            <td>
                                    ' . (($post->post_status != 'publish') ? '<input type="text" name="invoice_discount" value="' . $sale_data['invoice_discount'] . '" id="invoice_discount"/>' : $sale_data['invoice_discount']) . '
                            </td>
                    </tbody>
            </table>';

            $echoHtml = apply_filters('fktr_sale_discount_box', $echoHtml);
            echo $echoHtml;
            do_action('add_fktr_sale_discount_box', $echoHtml);
        }

        public static function date_format_php_to_js($sFormat) {
            switch ($sFormat) {
                //Predefined WP date formats
                case 'F j, Y':
                case 'Y/m/d':
                case 'm/d/Y':
                case 'd/m/Y':
                    return $sFormat;
                    break;

                default :
                    return( 'm/d/Y' );
                    break;
            }
        }

        public static function updateReceiptsAffected($sale_id, $receipt_id, $affected, $currency_receipt) {
		$sale_data = self::get_sale_data($sale_id);
		$affected_standar = fakturo_mask_to_float($affected);
		$affected_in_sale = fakturo_transform_money($currency_receipt, $sale_data['invoice_currency'], $affected_standar);
		if (!is_array($sale_data['receipts'])) {
			$sale_data['receipts'] = array();
		}
		$sale_data['receipts'][$receipt_id] = $affected_in_sale;
		$new = apply_filters( 'fktr_sale_metabox_save_receipts', $sale_data['receipts']); 
		update_post_meta($sale_id, 'receipts', $new );
	}
	public static function getProductStock($productId, $sale_id, $reserved = false) {
		$stocks = array();
		$stocks = fktrPostTypeProducts::getStocks($productId);
		
		if ($reserved) {
			$sale_data = self::get_sale_data($sale_id);
			foreach ($sale_data['product_stock_location'] as $product_id => $arr_locations) {
				if ($product_id != $productId) {
					continue;
				}
				foreach ($arr_locations as $location_id => $array_quantity) {
					foreach ($array_quantity as $key => $quantity) {
						if (!empty($quantity)) {
							$stocks[$location_id] = $stocks[$location_id]+$quantity;
						} else {
							$stocks[$location_id] = 0;
						}
					}
				}
			} 
		}
		return $stocks;
	}
	public static function ajax_validate_sale() {
            $setting_system = get_option('fakturo_system_options_group', false);
            $response = new WP_Ajax_Response;
            $fields = array();
            parse_str($_POST['inputs'], $fields);
            $fields = apply_filters('fktr_clean_sale_fields', $fields);

            $invoiceNumberExiste = self::exist_a_invoice_number($fields['invoice_number'], $fields['sale_point'], $fields['invoice_type']);
            if ($invoiceNumberExiste) {
                $response->add(array(
                    'data' => 'error',
                    'supplemental' => array(
                        'message' => __('This invoice number is already in use, Please try again.', 'fakturo'),
                        'inputSelector' => '#invoice_number',
                        'function' => 'updateSuggestInvoiceNumber',
                    ),
                ));
                $response->send();
            }

            $invoice_type = get_fakturo_term($fields['invoice_type'], 'fktr_invoice_types');
            if (is_wp_error($invoice_type)) {
                $response->add(array(
                    'data' => 'error',
                    'supplemental' => array(
                        'message' => __('This invoice type does not exist, try another.', 'fakturo'),
                        'inputSelector' => '#invoice_type',
                        'function' => '',
                    ),
                ));
                $response->send();
            }

            if ($setting_system['use_stock_product'] && $invoice_type->sum == 0 && $setting_system['stock_less_zero'] == 0) {
                $reserved = false;
                if ($fields['original_post_status'] == 'draft') {
                    $reserved = true;
                }
                $error = 0;
                $productStocks = array();
                foreach ($fields['product_stock_location'] as $product_id => $arr_locations) {
                    foreach ($arr_locations as $location_id => $array_quantity) {
                        foreach ($array_quantity as $key => $quantity) {
                            if (!empty($quantity)) {

                                if (empty($productStocks[$product_id])) {

                                    $productStocks[$product_id] = self::getProductStock($product_id, $fields['post_ID'], $reserved);
                                }

                                $productStocks[$product_id][$location_id] = $productStocks[$product_id][$location_id] - $quantity;
                                if ($productStocks[$product_id][$location_id] < 0) {
                                    $error = $product_id;
                                    break 3;
                                }
                            }
                        }
                    }
                }

                if ($error > 0) {

                    $response->add(array(
                        'data' => 'error',
                        'supplemental' => array(
                            'message' => sprintf(__('The product (%s) does not have enough stock.', 'fakturo'), $error),
                            'inputSelector' => '',
                            'function' => '',
                        ),
                    ));
                    $response->send();
                }
            }

            do_action('fktr_validate_sale', $fields);

            // Everything fine, go to save invoice :D
            $response->add(array(
                'data' => 'success',
                'supplemental' => array(
                    'message' => '',
                    'inputSelector' => '',
                    'function' => '',
                ),
            ));
            $response->send(); //		wp_die();
        }

        public static function exist_a_invoice_number($invoice_number, $sale_point, $invoice_type) {
            global $wpdb;
            $return = false;
            $setting_system = get_option('fakturo_system_options_group', false);
            if ($setting_system['individual_numeration_by_invoice_type'] && $setting_system['individual_numeration_by_sale_point']) {
                $sql = sprintf("SELECT p.ID, pm.meta_key, pm.meta_value as invoice_number, sale.meta_value as sale_point, invoicet.meta_value as invoice_type FROM {$wpdb->posts} as p
				 LEFT JOIN {$wpdb->postmeta} as pm ON p.ID = pm.post_id
                 LEFT JOIN {$wpdb->postmeta} as sale ON p.ID = pm.post_id
                 LEFT JOIN {$wpdb->postmeta} as invoicet ON p.ID = pm.post_id
                 WHERE 
                 pm.meta_key = 'invoice_number'
				 AND p.post_status = 'publish'
				 AND p.post_type = 'fktr_sale'
                 AND sale.meta_key = 'sale_point'
                 AND invoicet.meta_key = 'invoice_type'
				 AND pm.meta_value = '%s'
                 AND sale.meta_value = '%s'
				 AND invoicet.meta_value = '%s'
				 AND invoicet.post_id = sale.post_id 
				 AND sale.post_id = p.ID
                 GROUP BY p.ID 
				 LIMIT 1
			 ", $invoice_number, $sale_point, $invoice_type);
            } else if ($setting_system['individual_numeration_by_sale_point']) {
                $sql = sprintf("SELECT p.ID, pm.meta_key, pm.meta_value as invoice_number, sale.meta_value as sale_point FROM {$wpdb->posts} as p
				 LEFT JOIN {$wpdb->postmeta} as pm ON p.ID = pm.post_id
                 LEFT JOIN {$wpdb->postmeta} as sale ON p.ID = pm.post_id
                 WHERE 
                 pm.meta_key = 'invoice_number'
				 AND p.post_status = 'publish'
				 AND p.post_type = 'fktr_sale'
                 AND sale.meta_key = 'sale_point'
				 AND pm.meta_value = '%s'
                 AND sale.meta_value = '%s'
                 AND sale.post_id = p.ID
                 GROUP BY p.ID 
				 LIMIT 1
			 ", $invoice_number, $sale_point);
            } else if ($setting_system['individual_numeration_by_invoice_type']) {
                $sql = sprintf("SELECT p.ID, pm.meta_key, pm.meta_value as invoice_number, invoicet.meta_value as invoice_type FROM {$wpdb->posts} as p
				 LEFT JOIN {$wpdb->postmeta} as pm ON p.ID = pm.post_id
                 LEFT JOIN {$wpdb->postmeta} as invoicet ON p.ID = pm.post_id
                 WHERE 
                 pm.meta_key = 'invoice_number'
				 AND p.post_status = 'publish'
				 AND p.post_type = 'fktr_sale'
                 AND invoicet.meta_key = 'invoice_type'
				 AND pm.meta_value = '%s'
				 AND invoicet.meta_value = '%s'
				 AND invoicet.post_id = p.ID
                 GROUP BY p.ID 
				 LIMIT 1
			 ", $invoice_number, $invoice_type);
            } else {
                $sql = sprintf("SELECT pm.meta_value FROM {$wpdb->postmeta} pm
				 LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
				 WHERE pm.meta_key = 'invoice_number'
				 AND pm.meta_value = '%s'
				 AND p.post_status = 'publish'
				 AND p.post_type = 'fktr_sale'
				 LIMIT 1
			 ", $invoice_number);
            }

            $r = $wpdb->get_results($sql);
            if (!empty($r)) {
                $return = true;
            }
            return $return;
        }

        public static function get_suggest_invoice_number() {
            $setting_system = get_option('fakturo_system_options_group', false);
            if (!isset($_POST['sale_point'])) {
                $_POST['sale_point'] = $setting_system['sale_point'];
            }
            if (!isset($_POST['invoice_type'])) {
                $_POST['invoice_type'] = $setting_system['invoice_type'];
                ;
            }
            $suggested = self::suggestInvoiceNumber($_POST['sale_point'], $_POST['invoice_type']);
            wp_die($suggested);
        }

        public static function suggestInvoiceNumber($sale_point = 0, $invoice_type = 0) {
            $retorno = 0;
            if ($sale_point < 0) {
                $sale_point = 0;
            }
            if ($invoice_type < 0) {
                $invoice_type = 0;
            }

            $setting_system = get_option('fakturo_system_options_group', false);
            $last_invoice_numbers = get_option('last_invoice_number', false);
            if (!$last_invoice_numbers) {
                $last_invoice_numbers = array();
                $last_invoice_numbers[0] = array();
                update_option('last_invoice_number', $last_invoice_numbers);
            }
            $keySale = 0;
            $keyInvNum = 0;
            if ($setting_system['individual_numeration_by_invoice_type'] && $setting_system['individual_numeration_by_sale_point']) {
                $keySale = $sale_point;
                $keyInvNum = $invoice_type;
            } else if ($setting_system['individual_numeration_by_sale_point']) {
                $keySale = $sale_point;
            } else if ($setting_system['individual_numeration_by_invoice_type']) {
                $keyInvNum = $invoice_type;
            }

            // 0 - 0 is the invoice number general
            if (!empty($last_invoice_numbers[$keySale][$keyInvNum])) {
                $retorno = $last_invoice_numbers[$keySale][$keyInvNum];
            }
            $retorno = $retorno + 1;
            return $retorno;
        }

        public static function updateSuggestInvoiceNumber($sale_point = 0, $invoice_type = 0, $invoice_number = 0) {

            if ($sale_point < 0) {
                $sale_point = 0;
            }
            if ($invoice_type < 0) {
                $invoice_type = 0;
            }
            $setting_system = get_option('fakturo_system_options_group', false);
            $last_invoice_numbers = get_option('last_invoice_number', false);
            if (!$last_invoice_numbers) {
                $last_invoice_numbers = array();
                $last_invoice_numbers[0] = array();
            }
            $keySale = 0;
            $keyInvNum = 0;
            if ($setting_system['individual_numeration_by_invoice_type'] && $setting_system['individual_numeration_by_sale_point']) {
                $keySale = $sale_point;
                $keyInvNum = $invoice_type;
            } else if ($setting_system['individual_numeration_by_sale_point']) {
                $keySale = $sale_point;
            } else if ($setting_system['individual_numeration_by_invoice_type']) {
                $keyInvNum = $invoice_type;
            }

            if (empty($last_invoice_numbers[$keySale])) {
                $last_invoice_numbers[$keySale] = array();
            }
            if (empty($last_invoice_numbers[$keySale][$keyInvNum])) {
                $last_invoice_numbers[$keySale][$keyInvNum] = $invoice_number;
            } else {
                if ($last_invoice_numbers[$keySale][$keyInvNum] < $invoice_number) {
                    $last_invoice_numbers[$keySale][$keyInvNum] = $invoice_number;
                }
            }

            update_option('last_invoice_number', $last_invoice_numbers);
        }

        public static function getTitleInvoiceNumber($id) {
            $setting_system = get_option('fakturo_system_options_group', false);
            $sale_data = self::get_sale_data($id);

            $invoice_type = get_fakturo_term($sale_data['invoice_type'], 'fktr_invoice_types');
            $sale_point = get_fakturo_term($sale_data['sale_point'], 'fktr_sale_points');

            $newVal = '';
            $setting_system['list_invoice_number_separator'] = apply_filters('fktr_invoice_number_separator', $setting_system['list_invoice_number_separator']);
            $add_separator = true;
            foreach ($setting_system['list_invoice_number'] as $k => $invn) {

                if (($k + 1) == count($setting_system['list_invoice_number'])) {
                    $add_separator = false;
                }

                if ($invn == 'sale_point') {
                    if (!is_wp_error($sale_point)) {
                        $newVal .= str_pad($sale_point->code, 4, '0', STR_PAD_LEFT) . ($add_separator ? $setting_system['list_invoice_number_separator'] : '');
                    }
                }
                if ($invn == 'invoice_type_name') {
                    if (!is_wp_error($invoice_type)) {
                        $newVal .= $invoice_type->name . ($add_separator ? $setting_system['list_invoice_number_separator'] : '');
                    }
                }
                if ($invn == 'invoice_type_short_name') {
                    if (!is_wp_error($invoice_type)) {
                        $newVal .= $invoice_type->short_name . ($add_separator ? $setting_system['list_invoice_number_separator'] : '');
                    }
                }
                if ($invn == 'invoice_type_symbol') {
                    if (!is_wp_error($invoice_type)) {
                        $newVal .= $invoice_type->symbol . ($add_separator ? $setting_system['list_invoice_number_separator'] : '');
                    }
                }
                if ($invn == 'invoice_number') {
                    $newVal .= str_pad($sale_data['invoice_number'], $setting_system['digits_invoice_number'], '0', STR_PAD_LEFT) . ($add_separator ? $setting_system['list_invoice_number_separator'] : '');
                }
            }

            return $newVal;
        }

        public static function before_delete($post_id) {  // just permanent delete (when uses stock)
            $post_type = get_post_type($post_id);
            if ($post_type == 'fktr_sale') {
                $setting_system = get_option('fakturo_system_options_group', false);
                if ($setting_system['use_stock_product']) {
                    $sale_data = self::get_sale_data($post_id);

                    $invoice_type = get_fakturo_term($sale_data['invoice_type'], 'fktr_invoice_types');
                    if (!is_wp_error($invoice_type)) {
                        if ($invoice_type->sum == 0) {
                            foreach ($sale_data['product_stock_location'] as $product_id => $arr_locations) {
                                foreach ($arr_locations as $location_id => $array_quantity) {
                                    foreach ($array_quantity as $key => $quantity) {
                                        if (!empty($quantity)) {
                                            fktrPostTypeProducts::addStock($product_id, $quantity, $location_id);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        public static function updateStock($fields, $post) {
            $setting_system = get_option('fakturo_system_options_group', false);
            if (!$setting_system['use_stock_product']) {
                return false;
            }
            $old_fields = self::get_sale_data($post->ID);

            $invoice_type = get_fakturo_term($fields['invoice_type'], 'fktr_invoice_types');
            if (!is_wp_error($invoice_type)) {
                if ($invoice_type->sum) {
                    if ($post->post_status == 'publish') {
                        foreach ($fields['product_stock_location'] as $product_id => $arr_locations) {
                            foreach ($arr_locations as $location_id => $array_quantity) {
                                foreach ($array_quantity as $key => $quantity) {
                                    if (!empty($quantity)) {
                                        fktrPostTypeProducts::addStock($product_id, $quantity, $location_id);
                                    }
                                }
                            }
                        }
                    }
                } else {

                    $locations = get_fakturo_terms(array(
                        'taxonomy' => 'fktr_locations',
                        'hide_empty' => false,
                    ));

                    foreach ($old_fields['product_stock_location'] as $product_id => $arr_locations) {
                        foreach ($arr_locations as $location_id => $array_quantity) {
                            foreach ($array_quantity as $key => $quantity) {
                                if (!empty($quantity)) {
                                    fktrPostTypeProducts::addStock($product_id, $quantity, $location_id);
                                }
                            }
                        }
                    }
                    foreach ($fields['product_stock_location'] as $product_id => $arr_locations) {
                        foreach ($arr_locations as $location_id => $array_quantity) {
                            foreach ($array_quantity as $key => $quantity) {
                                if (!empty($quantity)) {
                                    fktrPostTypeProducts::removeStock($product_id, $quantity, $location_id);
                                }
                            }
                        }
                    }
                }
            } else {
                return false;
            }
            return true;
        }

        public static function clean_fields($fields) {
		$setting_system = get_option('fakturo_system_options_group', false);
		if (!isset($fields['client_id'])) {
			$fields['client_id'] = 0;
		}
		if (!isset($fields['client_data']) || !is_array($fields['client_data'])) {
			$fields['client_data'] = array();
			$fields['client_data']['name'] = __('No name', 'fakturo' );
			$fields['client_data']['address'] = __('No address', 'fakturo' );
			$fields['client_data']['city'] = __('No city', 'fakturo' );
			$fields['client_data']['state'] = array();
			$fields['client_data']['state']['id'] = 0;
			$fields['client_data']['state']['name'] = __('No state', 'fakturo' );
			$fields['client_data']['country'] = array();
			$fields['client_data']['country']['id'] = 0;
			$fields['client_data']['country']['name'] = __('No country', 'fakturo' );
			$fields['client_data']['taxpayer'] = __('No Taxpayer', 'fakturo' );
			$fields['client_data']['tax_condition'] = 0;
			$fields['client_data']['payment_type'] = 0;
			$fields['client_data']['price_scale'] = array();
			$fields['client_data']['price_scale']['id'] = 0;
			$fields['client_data']['price_scale']['name'] = __('No price scale', 'fakturo' );
			$fields['client_data']['credit_limit'] = 0;
			
			
		}
		if (!isset($fields['client_data']['tax_condition'])) {
			$fields['client_data']['tax_condition'] = 0;
		}
		
		if (!isset($fields['invoice_type'])) {
			$fields['invoice_type'] = $setting_system['invoice_type'];
		}
		if (!isset($fields['sale_point'])) {
			$fields['sale_point'] = $setting_system['sale_point'];
		}
		
		if (!isset($fields['invoice_number'])) {
			$fields['invoice_number'] = '';
		}
		if (!isset($fields['date'])) {
			$fields['date'] = current_time('timestamp');
		}
		if (!isset($fields['invoice_currency'])) {
			$fields['invoice_currency'] = 0;
		}
		if (!isset($fields['invoice_saleman'])) {
			$fields['invoice_saleman'] = get_current_user_id();
		}
		if (!isset($fields['invoice_discount'])) {
			$fields['invoice_discount'] = 0;
		}
		if (!isset($fields['in_sub_total'])) {
			$fields['in_sub_total'] = 0;
		}
		if (!isset($fields['in_total'])) {
			$fields['in_total'] = 0;
		}
		if (!isset($fields['product_stock_location'])) {
			$fields['product_stock_location'] = array();
		}
		if (!isset($fields['uc_id'])) {
			$fields['uc_id'] = array();
		}
		if (!isset($fields['receipts'])) {
			$fields['receipts'] = array();
		}
		return $fields;
	}
	public static function before_save($fields) {
		$setting_system = get_option('fakturo_system_options_group', false);
		//
		if (!empty($fields['uc_id'])) {
			foreach ($fields['uc_id'] as $key => $product_id) {
				$fields['uc_unit_price'][$key] = fakturo_mask_to_float($fields['uc_unit_price'][$key]);
				$fields['uc_amount'][$key] = fakturo_mask_to_float($fields['uc_amount'][$key]);
			}
		}
		return $fields;
	}
	public static function default_fields($new_status, $old_status, $post ) {
		
		if( $post->post_type == 'fktr_sale' && $old_status == 'new'){		
			$setting_system = get_option('fakturo_system_options_group', false);
			$fields = array();
			$fields['client_id'] = 0;
			$fields['client_data'] = array();
			$fields['client_data']['name'] = __('No name', 'fakturo' );
			$fields['client_data']['address'] = __('No address', 'fakturo' );
			$fields['client_data']['city'] = __('No city', 'fakturo' );
			$fields['client_data']['state'] = array();
			$fields['client_data']['state']['id'] = 0;
			$fields['client_data']['state']['name'] = __('No state', 'fakturo' );
			$fields['client_data']['country'] = array();
			$fields['client_data']['country']['id'] = 0;
			$fields['client_data']['country']['name'] = __('No country', 'fakturo' );
			$fields['client_data']['taxpayer'] = __('No Taxpayer', 'fakturo' );
			$fields['client_data']['tax_condition'] = 0;
			$fields['client_data']['payment_type'] = 0;
			$fields['client_data']['price_scale'] = array();
			$fields['client_data']['price_scale']['id'] = 0;
			$fields['client_data']['price_scale']['name'] = __('No price scale', 'fakturo' );
			$fields['client_data']['credit_limit'] = 0;
			
			
			$fields['invoice_type'] = $setting_system['invoice_type'];
			$fields['sale_point'] = $setting_system['sale_point'];
			$fields['invoice_number'] = '';
			$fields['date'] = current_time('timestamp');

			$fields['invoice_currency'] = 0;
			$fields['invoice_saleman'] = get_current_user_id();
			$fields['invoice_discount'] = 0;
			
			$fields['in_sub_total'] = 0;
			$fields['in_total'] = 0;
			
			$fields['uc_id'] = array();
			$fields['product_stock_location'] = array();
			 
			$fields = apply_filters('fktr_clean_sale_fields', $fields);

			foreach ( $fields as $field => $value ) {
				if ( !is_null( $value ) ) {
					$new = apply_filters( 'fktr_sale_metabox_save_' . $field, $value );  //filtra cada campo antes de grabar
					update_post_meta( $post->ID, $field, $new );
				}
			}
		}
	}
	public static function get_sale_data($sale_id) {
		$custom_field_keys = get_post_custom($sale_id);
		foreach ( $custom_field_keys as $key => $value ) {
			$custom_field_keys[$key] = maybe_unserialize($value[0]);
		}
		$client_data = array();
		$client_data['client_data'] = json_decode(get_post_field('post_content', $sale_id), true);
		$custom_field_keys = array_merge($custom_field_keys, $client_data);

		$custom_field_keys = apply_filters('fktr_clean_sale_fields', $custom_field_keys );
		return $custom_field_keys;
	}
	
	
	
	public static function fktr_save_sale_summary($sale_summary, $fields) {
		$sale_summary = __('Client', 'fakturo' ).': '.$fields['client_data']['name'].'<br>';
		$sale_summary .= __('SubTotal', 'fakturo' ).': '.$fields['in_sub_total'].' - ' .
			__('Total', 'fakturo' ).': '.$fields['in_total'].'<br>';
		return $sale_summary;
	}
	
	
	public static function save($post_id, $post) {
		global $wpdb;
		if (isset($_POST['original_post_status']) && $_POST['original_post_status'] == 'publish') {
			return false;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX') && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) {
			return false;
		}

		if ( isset( $post->post_type ) && $post->post_type == 'revision' || $post->post_type!= 'fktr_sale') {
			return false;
		}
		
		if ( ! current_user_can( 'edit_fakturo_settings', $post_id ) ) {
			return false;
		}
		if ( ( defined( 'FKTR_STOP_PROPAGATION') && FKTR_STOP_PROPAGATION ) ) {
			return false;
		}
	
		$setting_system = get_option('fakturo_system_options_group', false);
		$fields = apply_filters('fktr_clean_sale_fields',$_POST);
		$fields = apply_filters('fktr_sale_before_save',$fields, $post);
		
		self::updateStock($fields, $post);
		
		if(isset($fields['date']) && !is_numeric($fields['date'])) {

			$fields['date'] = fakturo_date2time($fields['date'], $setting_system['dateformat'] );
		}
		
		if (isset($fields['client_data'])) {
			$sale_summary = apply_filters('fktr_save_sale_summary', '', $fields); // string
			$wpdb->update( 
				$wpdb->posts, 
				array(
					'post_excerpt' => $sale_summary,
					'post_content' => json_encode($fields['client_data']),
				), array('ID' => $post_id ) );
			unset($fields['client_data']);
		}
		
		
		foreach ($fields as $field => $value ) {
			
			if ( !is_null( $value ) ) {
				$new = apply_filters('fktr_sale_metabox_save_' . $field, $value );  //filtra cada campo antes de grabar
				update_post_meta( $post_id, $field, $new );
				
			}
			
		}
		if ($post->post_status == 'publish') {
		   self::updateSuggestInvoiceNumber($fields['sale_point'], $fields['invoice_type'], $fields['invoice_number']);
		}
		
		do_action( 'fktr_save_sale', $post_id, $post, $fields );
		
	}
	
	
} 

endif;

$fktrPostTypeSales = new fktrPostTypeSales();

?>