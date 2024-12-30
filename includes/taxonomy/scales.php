<?php
// Exit if accessed directly
if (!defined('ABSPATH'))  {
	exit;
}

if ( ! class_exists('fktr_tax_commission_scales') ) :
class fktr_tax_commission_scales {
	
	function __construct() {
		add_action( 'init', array('fktr_tax_commission_scales', 'init'), 1, 99 );
		add_action( 'fakturo_activation', array(__CLASS__, 'init'), 1 );
		add_action('fktr_commission_scales_edit_form_fields', array('fktr_tax_commission_scales', 'edit_form_fields'));
		add_action('fktr_commission_scales_add_form_fields',  array('fktr_tax_commission_scales', 'add_form_fields'));
		add_action('edited_fktr_commission_scales', array('fktr_tax_commission_scales', 'save_fields'), 10, 2);
		add_action('created_fktr_commission_scales', array('fktr_tax_commission_scales','save_fields'), 10, 2);
		add_action('admin_enqueue_scripts', array(__CLASS__, 'before_content'));
		add_filter('parent_file',  array( __CLASS__, 'tax_menu_correction'));
		add_filter('submenu_file',  array( __CLASS__, 'tax_submenu_correction'));
		
		add_filter('manage_edit-fktr_commission_scales_columns', array('fktr_tax_commission_scales', 'columns'), 10, 3);
		add_filter('manage_fktr_commission_scales_custom_column',  array('fktr_tax_commission_scales', 'theme_columns'), 10, 3);
		
		add_filter('before_save_tax_fktr_commission_scales', array(__CLASS__, 'before_save'), 10, 1);
		add_filter('redirect_term_location', array(__CLASS__, 'redirect_term_location'), 0, 2);
	}

    public static function before_content($request) {
		wp_enqueue_script('jquery-select2', FAKTURO_PLUGIN_URL . 'assets/js/jquery.select2.js', array('jquery'), WPE_FAKTURO_VERSION, true);
            wp_enqueue_script('fakturo_taxonomy_scales', FAKTURO_PLUGIN_URL . 'assets/js/taxonomy-scales.js', array('jquery'), WPE_FAKTURO_VERSION, true);
	}
	
	static function redirect_term_location($location, $tax ){
		if($tax->name == 'fktr_commission_scales'){
			$location = admin_url('edit-tags.php?taxonomy=fktr_commission_scales');
		}
		return $location;
	}
	
	public static function init() {
		$labels = array(
			'name'                       => __( 'Commission Scales', 'fakturo' ),
			'singular_name'              => __( 'Commission Scale', 'fakturo' ),
			'search_items'               => __( 'Search Commission Scales', 'fakturo' ),
			'popular_items'              => __( 'Popular Commission Scales', 'fakturo' ),
			'all_items'                  => __( 'All Commission Scales', 'fakturo' ),
			'parent_item'                => __( 'Parent Scale', 'fakturo' ),
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Commission Scale', 'fakturo' ),
			'update_item'                => __( 'Update Commission Scale', 'fakturo' ),
			'add_new_item'               => __( 'Add New Commission Scale', 'fakturo' ),
			'new_item_name'              => __( 'New Commission Scale Name', 'fakturo' ),
			'separate_items_with_commas' => __( 'Separate Commission Scales with commas', 'fakturo' ),
			'add_or_remove_items'        => __( 'Add or remove Commission Scales', 'fakturo' ),
			'choose_from_most_used'      => __( 'Choose from the most used Commission Scales', 'fakturo' ),
			'not_found'                  => __( 'No Commission Scales found.', 'fakturo' ),
			'menu_name'                  => __( 'Commission Scales', 'fakturo' ),
		);

		$args = array(
			'public'				=> false,
			'hierarchical'          => false,
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'fktr-commission-scales' ),
			'capabilities' => array(
				'manage_terms' => 'manage_fktr_commission_scales',
				'edit_terms' => 'edit_fktr_commission_scales',
				'delete_terms' => 'delete_fktr_commission_scales',
				'assign_terms' => 'assign_fktr_commission_scales'
			)
		);
		register_taxonomy(
			'fktr_commission_scales',
			'',
			$args
		);
	}

	// highlight the proper top level menu
	static function tax_menu_correction($parent_file) {
		global $current_screen;
		if ($current_screen->id == "edit-fktr_commission_scales") {
			$parent_file = 'fakturo_dashboard';
		}
		return $parent_file;
	}
	
	// highlight the proper sub level menu
	static function tax_submenu_correction($submenu_file) {
		global $current_screen;
		if ($current_screen->id == "edit-fktr_commission_scales") {
			$submenu_file = 'fakturo-settings';
		}
		return $submenu_file;
	}
	
	public static function add_form_fields() {
        // Get all sellers
        $allsellers = get_users(array('role' => 'fakturo_seller'));
		$allmanagers = get_users(array('role' => 'fakturo_manager'));
		$alladmins = get_users(array('role' => 'administrator'));
		$allsellers = array_merge($allsellers, $allmanagers, $alladmins);
        
        
        // Get all categories
        $categories = get_terms(array(
            'taxonomy' => 'fktr_category',
            'hide_empty' => false,
        ));
        
        $echoHtml = '
        <style type="text/css">
            .form-field.term-parent-wrap,
            .form-field.term-slug-wrap,
            .form-field label[for="parent"],
            .form-field #parent,
            .form-field.term-description-wrap,
            .inline.hide-if-no-js,
            .view {
                display: none;
            }
            .ranges-container {
                margin-bottom: 15px;
            }
            .ranges-container .range-row {
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 10px;
            }
            .ranges-container .range-row input {
                width: 100px;
            }
        </style>
        <div class="form-field" id="scale-seller-div">
            <label for="term_meta[seller_id]">'.__('Assigned Salesperson', 'fakturo').'</label>
            <select name="term_meta[seller_id]" id="term_meta_seller">
                <option value="0">'.__('Choose a Salesman', 'fakturo').'</option>';
        
        foreach ($allsellers as $seller) {
            $echoHtml .= '<option value="'.esc_attr($seller->ID).'">'.esc_html($seller->display_name).'</option>';
        }
        
        $echoHtml .= '</select>
            <p class="description">'.__('Select the salesperson for this commission scale', 'fakturo').'</p>
        </div>
        
        <div class="form-field" id="scale-category-div">
            <label for="term_meta[category_id]">'.__('Category', 'fakturo').'</label>
            <select name="term_meta[category_id]" id="term_meta_category">
                <option value="0">'.__('Choose a Category', 'fakturo').'</option>';
        
        foreach ($categories as $category) {
            $echoHtml .= '<option value="'.esc_attr($category->term_id).'">'.esc_html($category->name).'</option>';
        }
        
        $echoHtml .= '</select>
            <p class="description">'.__('Select the category for this commission scale', 'fakturo').'</p>
        </div>
        
        <div class="form-field" id="scale-type-div">
            <label for="term_meta[type]">'.__('Scale Type', 'fakturo').'</label>
            <select name="term_meta[type]" id="term_meta_type">
                <option value="base">'.__('Base Scale', 'fakturo').'</option>
                <option value="exception">'.__('Exception Scale', 'fakturo').'</option>
            </select>
            <p class="description">'.__('Select the type of commission scale', 'fakturo').'</p>
        </div>
        
        <div class="form-field" id="scale-ranges-div">
            <label>'.__('Commission Ranges', 'fakturo').'</label>
            <div class="ranges-container">
                <div class="range-row">
                    <input type="number" name="term_meta[ranges][0][from]" placeholder="'.__('From', 'fakturo').'" class="small-text">
                    <input type="number" name="term_meta[ranges][0][to]" placeholder="'.__('To', 'fakturo').'" class="small-text">
                    <input type="number" name="term_meta[ranges][0][percentage]" placeholder="%" class="small-text">
                    <button type="button" class="button button-secondary add-range">+</button>
                </div>
            </div>
            <p class="description">'.__('Define commission percentage ranges', 'fakturo').'</p>
        </div>';
    
        $echoHtml .= self::get_ranges_script();
        echo $echoHtml;
    }
    
    
    public static function edit_form_fields($term) {
        $term_meta = get_fakturo_term($term->term_id, 'fktr_commission_scales');
        
        // Get all sellers
        $allsellers = get_users(array('role' => 'fakturo_seller'));
		$allmanagers = get_users(array('role' => 'fakturo_manager'));
		$alladmins = get_users(array('role' => 'administrator'));
		$allsellers = array_merge($allsellers, $allmanagers, $alladmins);
        
        // Get all categories
        $categories = get_terms(array(
            'taxonomy' => 'fktr_category',
            'hide_empty' => false,
        ));
        
        $echoHtml = '
        <style type="text/css">
            .form-field.term-parent-wrap,
            .form-field.term-slug-wrap,
            .form-field.term-description-wrap {
                display: none;
            }
            .ranges-container {
                margin-bottom: 15px;
            }
            .ranges-container .range-row {
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 10px;
            }
            .ranges-container .range-row input {
                width: 100px;
            }
        </style>
        
        <tr class="form-field">
            <th scope="row" valign="top">
                <label for="term_meta[seller_id]">'.__('Assigned Salesperson', 'fakturo').'</label>
            </th>
            <td>
                <select name="term_meta[seller_id]" id="term_meta_seller">
                    <option value="0">'.__('Choose a Salesman', 'fakturo').'</option>';
    
        foreach ($allsellers as $seller) {
            $selected = selected($term_meta->seller_id, $seller->ID, false);
            $echoHtml .= '<option value="'.esc_attr($seller->ID).'" '.$selected.'>'.esc_html($seller->display_name).'</option>';
        }
    
        $echoHtml .= '</select>
                <p class="description">'.__('Select the salesperson for this commission scale', 'fakturo').'</p>
            </td>
        </tr>
    
        <tr class="form-field">
            <th scope="row" valign="top">
                <label for="term_meta[category_id]">'.__('Category', 'fakturo').'</label>
            </th>
            <td>
                <select name="term_meta[category_id]" id="term_meta_category">
                    <option value="0">'.__('Choose a Category', 'fakturo').'</option>';
    
        foreach ($categories as $category) {
            $selected = selected($term_meta->category_id, $category->term_id, false);
            $echoHtml .= '<option value="'.esc_attr($category->term_id).'" '.$selected.'>'.esc_html($category->name).'</option>';
        }
    
        $echoHtml .= '</select>
                <p class="description">'.__('Select the category for this commission scale', 'fakturo').'</p>
            </td>
        </tr>
    
        <tr class="form-field">
            <th scope="row" valign="top">
                <label for="term_meta[type]">'.__('Scale Type', 'fakturo').'</label>
            </th>
            <td>
                <select name="term_meta[type]" id="term_meta_type">
                    <option value="base" '.selected($term_meta->type, 'base', false).' selected >'.__('Base Scale', 'fakturo').'</option>
                    <option value="exception" '.selected($term_meta->type, 'exception', false).'>'.__('Exception Scale', 'fakturo').'</option>
                </select>
                <p class="description">'.__('Select the type of commission scale', 'fakturo').'</p>
            </td>
        </tr>
    
        <tr class="form-field">
            <th scope="row" valign="top">
                <label>'.__('Commission Ranges', 'fakturo').'</label>
            </th>
            <td>
                <div class="ranges-container">';
    
        if (!empty($term_meta->ranges)) {
            $total_ranges = count($term_meta->ranges);
            foreach ($term_meta->ranges as $index => $range) {
                $is_last = ($index === $total_ranges - 1);
                $echoHtml .= '<div class="range-row">
                    <input type="number" name="term_meta[ranges]['.$index.'][from]" value="'.esc_attr($range->from).'" placeholder="'.__('From', 'fakturo').'" class="small-text">
                    <input type="number" name="term_meta[ranges]['.$index.'][to]" value="'.esc_attr($range->to).'" placeholder="'.__('To', 'fakturo').'" class="small-text">
                    <input type="number" name="term_meta[ranges]['.$index.'][percentage]" value="'.esc_attr($range->percentage).'" placeholder="%" class="small-text">
                    '.($is_last ? '<button type="button" class="button button-secondary add-range">+</button>' : '').'
                    '.($index > 0 ? '<button type="button" class="button button-secondary remove-range">-</button>' : '').'
                </div>';
            }
        } else {
            $echoHtml .= '<div class="range-row">
                <input type="number" name="term_meta[ranges][0][from]" placeholder="'.__('From', 'fakturo').'" class="small-text">
                <input type="number" name="term_meta[ranges][0][to]" placeholder="'.__('To', 'fakturo').'" class="small-text">
                <input type="number" name="term_meta[ranges][0][percentage]" placeholder="%" class="small-text">
                <button type="button" class="button button-secondary add-range">+</button>
            </div>';
        }
    
        $echoHtml .= '</div>
                <p class="description">'.__('Define commission percentage ranges', 'fakturo').'</p>
            </td>
        </tr>';
    
        $echoHtml .= self::get_ranges_script();
        echo $echoHtml;
    }
    
    private static function get_ranges_script() {
        return '
        <script>
        document.addEventListener("DOMContentLoaded", function() {
        
            const rangesContainer = document.querySelector(".ranges-container");
            
            function updateButtons() {
                const rows = rangesContainer.querySelectorAll(".range-row");
                
                // Remove all add buttons first
                rows.forEach(row => {
                    const addButton = row.querySelector(".add-range");
                    if (addButton) addButton.remove();
                });
                
                // Add the + button only to the last row
                const lastRow = rows[rows.length - 1];
                if (!lastRow.querySelector(".add-range")) {
                    const addButton = document.createElement("button");
                    addButton.type = "button";
                    addButton.className = "button button-secondary add-range";
                    addButton.textContent = "+";
                    lastRow.appendChild(addButton);
                }
            }
            
            rangesContainer.addEventListener("click", function(event) {
                if (event.target.classList.contains("add-range")) {
                    const currentRows = rangesContainer.querySelectorAll(".range-row").length;
                    
                    const newRangeRow = document.createElement("div");
                    newRangeRow.classList.add("range-row");
                    
                    newRangeRow.innerHTML = `
                        <input type="number" name="term_meta[ranges][${currentRows}][from]" placeholder="'.__('From', 'fakturo').'" class="small-text">
                        <input type="number" name="term_meta[ranges][${currentRows}][to]" placeholder="'.__('To', 'fakturo').'" class="small-text">
                        <input type="number" name="term_meta[ranges][${currentRows}][percentage]" placeholder="%" class="small-text">
                        <button type="button" class="button button-secondary remove-range">-</button>
                    `;
                    
                    rangesContainer.appendChild(newRangeRow);
                    updateButtons();
                }
                
                if (event.target.classList.contains("remove-range")) {
                    event.target.closest(".range-row").remove();
                    updateButtons();
                }
            });
        });
        </script>';
    }
	
	public static function columns($columns) {
        $new_columns = array(
            'cb' => '<input type="checkbox" />',
            'name' => __('Name', 'fakturo'),
            'Salesman' => __('Salesman', 'fakturo'),
            'category' => __('Category', 'fakturo'),
            'type' => __('Scale Type', 'fakturo'),
            'ranges' => __('Ranges', 'fakturo')
        );
        return $new_columns;
    }
	
	public static function theme_columns($out, $column_name, $term_id) {
        $term = get_fakturo_term($term_id, 'fktr_commission_scales');
        
        switch ($column_name) {
            case 'type': 
                $out = $term->type === 'base' ? __('Base Scale', 'fakturo') : __('Exception Scale', 'fakturo');
                break;
    
            case 'Salesman':
                if (!empty($term->seller_id)) {
                    $seller = get_user_by('id', $term->seller_id);
                    $out = $seller ? esc_html($seller->display_name) : __('Unknown', 'fakturo');
                } else {
                    $out = __('all Salesman', 'fakturo');
                }
                break;
    
            case 'category':
                if (!empty($term->category_id)) {
                    $category = get_term($term->category_id, 'fktr_category');
                    $out = $category ? esc_html($category->name) : __('Unknown', 'fakturo');
                } else {
                    $out = __('all Category', 'fakturo');
                }
                break;
    
            case 'ranges': 
                $ranges_str = '';
                if (!empty($term->ranges)) {
                    foreach ($term->ranges as $range) {
                        $ranges_str .= sprintf('%d-%d: %d%%, ', $range->from, $range->to, $range->percentage);
                    }
                    $out = rtrim($ranges_str, ', ');
                }
                break;
    
            default:
                break;
        }
        return $out;    
    }
	
	public static function before_save($fields)  {
		// Any preprocessing of fields before saving
		return $fields;
	}

	public static function save_fields($term_id, $tt_id) {
		if (isset( $_POST['term_meta'])) {
			$_POST['term_meta'] = apply_filters('before_save_tax_fktr_commission_scales', $_POST['term_meta']);
			set_fakturo_term($term_id, $tt_id, $_POST['term_meta']);
		}
	}
}
endif;

$fktr_tax_commission_scales = new fktr_tax_commission_scales();
?>