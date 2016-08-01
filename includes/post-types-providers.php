<?php


// Exit if accessed directly
if (!defined('ABSPATH'))  {
	exit;
}


if ( ! class_exists('fktrPostTypeProviders') ) :
class fktrPostTypeProviders {
	function __construct() {
		
		add_action( 'init', array('fktrPostTypeProviders', 'setup'), 1 );
		add_action('transition_post_status', array('fktrPostTypeProviders', 'default_fields'), 10, 3);
		add_action('save_post', array('fktrPostTypeProviders', 'save'), 10, 2 );
		
		add_action( 'admin_print_scripts-post-new.php', array('fktrPostTypeProviders','scripts'), 11 );
		add_action( 'admin_print_scripts-post.php', array('fktrPostTypeProviders','scripts'), 11 );
		
		add_action('admin_print_styles-post-new.php', array('fktrPostTypeProviders','styles'));
		add_action('admin_print_styles-post.php', array('fktrPostTypeProviders','styles'));
		
		add_action('wp_ajax_get_provider_states', array('fktrPostTypeProviders', 'get_provider_states'));
		
	}
	public static function setup() {
		$labels = array( 
			'name' => __( 'Providers', FAKTURO_TEXT_DOMAIN ),
			'singular_name' => __( 'Provider', FAKTURO_TEXT_DOMAIN ),
			'add_new' => __( 'Add New', FAKTURO_TEXT_DOMAIN ),
			'add_new_item' => __( 'Add New Provider', FAKTURO_TEXT_DOMAIN ),
			'edit_item' => __( 'Edit Provider', FAKTURO_TEXT_DOMAIN ),
			'new_item' => __( 'New Provider', FAKTURO_TEXT_DOMAIN ),
			'view_item' => __( 'View Provider', FAKTURO_TEXT_DOMAIN ),
			'search_items' => __( 'Search Providers', FAKTURO_TEXT_DOMAIN ),
			'not_found' => __( 'No providers found', FAKTURO_TEXT_DOMAIN ),
			'not_found_in_trash' => __( 'No providers found in Trash', FAKTURO_TEXT_DOMAIN ),
			'parent_item_colon' => __( 'Parent Provider:', FAKTURO_TEXT_DOMAIN ),
			'menu_name' => __( 'Providers', FAKTURO_TEXT_DOMAIN ),
		);
		$capabilities = array(
			'publish_post' => 'publish_fakturo_provider',
			'publish_posts' => 'publish_fakturo_providers',
			'read_post' => 'read_fakturo_provider',
			'read_private_posts' => 'read_private_fakturo_providers',
			'edit_post' => 'edit_fakturo_provider',
			'edit_published_posts' => 'edit_published_fakturo_providers',
			'edit_private_posts' => 'edit_private_fakturo_providers',
			'edit_posts' => 'edit_fakturo_providers',
			'edit_others_posts' => 'edit_others_fakturo_providers',
			'delete_post' => 'delete_fakturo_provider',
			'delete_posts' => 'delete_fakturo_providers',
			'delete_published_posts' => 'delete_published_fakturo_providers',
			'delete_private_posts' => 'delete_private_fakturo_providers',
			'delete_others_posts' => 'delete_others_fakturo_providers',
			);

		$args = array( 
			'labels' => $labels,
			'hierarchical' => false,
			'description' => 'Fakturo Providers',
			'supports' => array( 'title', 'thumbnail',/* 'custom-fields' */),
			'register_meta_box_cb' => array('fktrPostTypeProviders', 'meta_boxes'),
			'public' => true,
			'show_ui' => true,
			'show_in_menu' => 'admin.php?page=fakturo/view/fakturo_admin.php',
			'menu_position' => 5,
			'menu_icon' => '/images/icon_20.png',
			'show_in_nav_menus' => false,
			'publicly_queryable' => false,
			'exclude_from_search' => false,
			'has_archive' => false,
			'query_var' => true,
			'can_export' => true,
			'rewrite' => true
		);

		register_post_type( 'fktr_provider', $args );
		
		add_filter('enter_title_here', array('fktrPostTypeProviders', 'name_placeholder'),10,2);

	}
	
	public static function meta_boxes() {
		
		add_action('wp_ajax_webcam_shot', 'fakturo_ajax_webcam_shot');
	
		// Remove Custom Fields Metabox
		remove_meta_box('postimagediv', 'fktr_provider', 'side' );
		add_meta_box('postimagediv', __('Provider Image', FAKTURO_TEXT_DOMAIN ), array('fktrPostTypeProviders', 'thumbnail_box'), 'fktr_provider', 'side', 'high');
		add_meta_box('fakturo-seller-box', __('Assign Seller', FAKTURO_TEXT_DOMAIN ), array('fktrPostTypeProviders', 'seller_box'),'fktr_provider','side', 'high' );
		add_meta_box('fakturo-data-box', __('Complete Provider Data', FAKTURO_TEXT_DOMAIN ), array('fktrPostTypeProviders', 'data_box'),'fktr_provider','normal', 'default' );
		add_meta_box('fakturo-options-box', __('Provider Contacts', FAKTURO_TEXT_DOMAIN ), array('fktrPostTypeProviders', 'options_box'),'fktr_provider','normal', 'default' );
		
		remove_meta_box('fktr_locationsdiv', 'fktr_provider', 'side');
		remove_meta_box('tagsdiv-fktr_bank_entities', 'fktr_provider', 'side');
		
		//add_meta_box('fakturo-locations-box', 'Location Provider', 'post_categories_meta_box', 'fktr_provider', 'normal', 'core', array( 'taxonomy' => 'fktr_locations' ));
		
		do_action('add_ftkr_provider_meta_boxes');
	}
	
	public static function thumbnail_box() {
		
		
	}
	public static function seller_box() {
		
		
	}
	public static function data_box() {
		global $post;
		$provider_data = self::get_provider_data($post->ID);
		
		$selectCountry = wp_dropdown_categories( array(
			'show_option_all'    => __('Choose a country', FAKTURO_TEXT_DOMAIN ),
			'show_option_none'   => '',
			'orderby'            => 'name', 
			'order'              => 'ASC',
			'show_count'         => 0,
			'hide_empty'         => 0, 
			'child_of'           => 0,
			'exclude'            => '',
			'echo'               => 0,
			'selected'           => $provider_data['selected_country'],
			'hierarchical'       => 1, 
			'name'               => 'selected_country',
			'class'              => 'form-no-clear',
			'depth'              => 1,
			'tab_index'          => 0,
			'taxonomy'           => 'fktr_locations',
			'hide_if_empty'      => false
		));
		if (strlen($selectCountry) < 95) {
			
			$selectCountry = '<select name="selected_country" id="selected_country">
								<option value="0">'. __('Choose a country', FAKTURO_TEXT_DOMAIN ) .'</option>
							</select>';
		}
		
		$selectState = wp_dropdown_categories( array(
			'show_option_all'    => __('Choose a state', FAKTURO_TEXT_DOMAIN ),
			'show_option_none'   => '',
			'orderby'            => 'name', 
			'order'              => 'ASC',
			'show_count'         => 0,
			'hide_empty'         => 0, 
			'child_of'           => $provider_data['selected_country'],
			'exclude'            => '',
			'echo'               => 0,
			'selected'           => $provider_data['selected_state'],
			'hierarchical'       => 1, 
			'name'               => 'selected_state',
			'class'              => 'form-no-clear',
			'depth'              => 1,
			'tab_index'          => 0,
			'taxonomy'           => 'fktr_locations',
			'hide_if_empty'      => false
		));
		
		if (strlen($selectState) < 90) {
			
			$selectState = '<select name="selected_state" id="selected_state">
								<option value="0">'. __('Choose a country before', FAKTURO_TEXT_DOMAIN ) .'</option>
							</select>';
		}
		
		
		$selectBankEntities = wp_dropdown_categories( array(
			'show_option_all'    => __('Choose a Bank Entity', FAKTURO_TEXT_DOMAIN ),
			'show_option_none'   => '',
			'orderby'            => 'name', 
			'order'              => 'ASC',
			'show_count'         => 0,
			'hide_empty'         => 0, 
			'child_of'           => 0,
			'exclude'            => '',
			'echo'               => 0,
			'selected'           => $provider_data['selected_bank_entity'],
			'hierarchical'       => 1, 
			'name'               => 'selected_bank_entity',
			'class'              => 'form-no-clear',
			'depth'              => 1,
			'tab_index'          => 0,
			'taxonomy'           => 'fktr_bank_entities',
			'hide_if_empty'      => false
		));
		if (strlen($selectBankEntities) < 95) {
			
			$selectBankEntities = '<select name="selected_bank_entity" id="selected_bank_entity">
								<option value="0">'. __('Choose a Bank Entity', FAKTURO_TEXT_DOMAIN ) .'</option>
							</select>';
		}
		
		
		$echoHtml = '<table class="form-table">
					<tbody>
					<tr class="user-facebook-wrap">
						<th><label for="taxpayer">'.__('Taxpayer ID', FAKTURO_TEXT_DOMAIN ).'	</label></th>
						<td>
							<input id="taxpayer" type="text" name="taxpayer" value="'.$provider_data['taxpayer'].'" class="regular-text">
							<span id="cuit_validation"></span>
							<div style="font-size:0.85em;" id="cuit_validation_note">'.__("Cuit number's validation only. Check www.afip.gov.ar", FAKTURO_TEXT_DOMAIN ).'</div>
						</td>
					</tr>
					<tr class="user-address-wrap">
						<th><label for="address">'.__('Address', FAKTURO_TEXT_DOMAIN ).'	</label></th>
						<td><input type="text" name="address" id="address" value="'.$provider_data['address'].'" class="regular-text"></td>
					</tr>
					<tr class="user-facebook-wrap">
						<th><label for="country">'. __('Country', FAKTURO_TEXT_DOMAIN ).'	</label></th>
						<td>'.$selectCountry.'</td>
					</tr>
					<tr class="user-facebook-wrap">
						<th><label for="states">'. __('States', FAKTURO_TEXT_DOMAIN ) .'	</label></th>
						<td id="td_select_state">
							'.$selectState.'
						</td>
					</tr>
					<tr class="user-facebook-wrap">
						<th><label for="city">'.__('City', FAKTURO_TEXT_DOMAIN ) .'	</label></th>
						<td><input id="city" type="text" name="city" value="'.$provider_data['city'].'" class="regular-text"></td>
					</tr>
					<tr class="user-facebook-wrap">
						<th><label for="bank_entity">'.__('Bank Entity', FAKTURO_TEXT_DOMAIN ).'	</label></th>
						<td>'.$selectBankEntities.'</td>
					</tr>
					<tr class="user-facebook-wrap">
						<th><label for="bank_account">'.__('Bank Account', FAKTURO_TEXT_DOMAIN ) .'	</label></th>
						<td><input id="bank_account" type="text" name="bank_account" value="'.$provider_data['bank_account'].'" class="regular-text"></td>
					</tr>
					<tr class="user-facebook-wrap">
						<th><label for="postcode">'.__('Postcode', FAKTURO_TEXT_DOMAIN ).'	</label></th>
						<td><input id="postcode" type="text" name="postcode" value="'.$provider_data['postcode'].'" class="regular-text"></td>
					</tr>
					<tr class="user-facebook-wrap">
						<th><label for="phone">'.__('Phone', FAKTURO_TEXT_DOMAIN ) .'	</label></th>
						<td><input id="phone" type="text" name="phone" value="'.$provider_data['phone'].'" class="regular-text"></td>
					</tr>
					<tr class="user-facebook-wrap">
						<th><label for="cell_phone">'.__('Cell phone', FAKTURO_TEXT_DOMAIN ) .'	</label></th>
						<td><input id="cell_phone" type="text" name="cell_phone" value="'.$provider_data['cell_phone'].'" class="regular-text"></td>
					</tr>
					<tr class="user-email-wrap">
						<th><label for="email">'.__('E-mail', FAKTURO_TEXT_DOMAIN ) .'</label></th>
						<td><input type="email" name="email" id="email" value="'.$provider_data['email'].'" class="regular-text ltr"></td>
					</tr>
					<tr class="user-facebook-wrap">
						<th><label for="web">'.__('Web', FAKTURO_TEXT_DOMAIN ).'</label></th>
						<td><input id="web" type="text" name="web" value="'.$provider_data['web'].'" class="regular-text"></td>
					</tr>
					<tr class="user-facebook-wrap">
						<th><label for="active">'.__('Active', FAKTURO_TEXT_DOMAIN ).'	</label></th>
						<td><input id="active" type="checkbox" name="active" value="1" '.(($provider_data['active'])?'checked="checked"':'').'></td>
					</tr>
					</tbody>
				</table>';
		$echoHtml = apply_filters('fktr_provider_data_box', $echoHtml);
		echo $echoHtml;
		do_action('add_fktr_provider_data_box', $echoHtml);
		
	}
	public static function options_box() {
		
		
	}
	
	public static function get_provider_states() {
		
		if (!is_numeric($_POST['country_id'])) {
			$_POST['country_id']= 0;
		}
		
		$selectState = wp_dropdown_categories( array(
			'show_option_all'    => __('Choose a state', FAKTURO_TEXT_DOMAIN ),
			'show_option_none'   => '',
			'orderby'            => 'name', 
			'order'              => 'ASC',
			'show_count'         => 0,
			'hide_empty'         => 0, 
			'child_of'           => $_POST['country_id'],
			'exclude'            => '',
			'echo'               => 0,
			'selected'           => -1,
			'hierarchical'       => 1, 
			'name'               => 'selected_state',
			'class'              => 'form-no-clear',
			'depth'              => 1,
			'tab_index'          => 0,
			'taxonomy'           => 'fktr_locations',
			'hide_if_empty'      => false
		));
		if ($_POST['country_id'] == 0 || strlen($selectState) < 90) {
			
			$selectState = '<select name="selected_state" id="selected_state">
								<option value="0">'. __('Choose a country before', FAKTURO_TEXT_DOMAIN ) .'</option>
							</select>';
		}
		wp_die($selectState);
		
	}
	public static function name_placeholder( $title_placeholder , $post ) {
		if($post->post_type == 'fktr_provider') {
			$title_placeholder = __('Enter Provider name here', FAKTURO_TEXT_DOMAIN );
			
		}
		return $title_placeholder;
	}
	public static function scripts() {
		global $post_type;
		if($post_type == 'fktr_provider') {
			wp_enqueue_script( 'jquery-select2', FAKTURO_PLUGIN_URL . 'assets/js/jquery.select2.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			wp_enqueue_script( 'post-type-providers', FAKTURO_PLUGIN_URL . 'assets/js/post-type-providers.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			wp_localize_script('post-type-providers', 'providers_object',
				array('ajax_url' => admin_url( 'admin-ajax.php' ),
					'loading_states_text' => __('Loading states...', FAKTURO_TEXT_DOMAIN )
				) );
			
			
		}
   
	}
	public static function styles() {
		global $post_type;
		if($post_type == 'fktr_provider') {
			wp_enqueue_style('style-select2',FAKTURO_PLUGIN_URL .'assets/css/select2.min.css');	
			
		}
   
	}
	
	
	public static function default_fields($new_status, $old_status, $post ) {
		
		if( $post->post_type == 'fktr_provider' && $old_status == 'new'){		
			
			$fields = array();
			$fields['taxpayer'] = '';
			$fields['address'] = '';
			$fields['selected_country'] = 0;
			$fields['selected_state'] = 0;
			$fields['city'] = '';
			$fields['selected_bank_entity'] = 0;
			$fields['bank_account'] = '';
			$fields['postcode'] = '';
			$fields['phone'] = '';
			$fields['cell_phone'] = '';
			$fields['email'] = '';
			$fields['web'] = '';
			$fields['active'] = true;

			$fields = apply_filters('fktr_clean_provider_fields', $fields);

			foreach ( $fields as $field => $value ) {
				if ( !is_null( $value ) ) {
					
					$new = apply_filters( 'fktr_provider_metabox_save_' . $field, $value );  //filtra cada campo antes de grabar
					update_post_meta( $post->ID, $field, $new );
				}
			}
		}
	}
	public static function get_provider_data($provider_id) {
		$custom_field_keys = get_post_custom($provider_id);
		foreach ( $custom_field_keys as $key => $value ) {
			$custom_field_keys[$key] = maybe_unserialize($value[0]);
		}
		$custom_field_keys = apply_filters('fktr_clean_provider_fields', $custom_field_keys );
		return $custom_field_keys;
	}
	public static function save($post_id, $post) {
		
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX') && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) {
			return false;
		}

		if ( isset( $post->post_type ) && 'revision' == $post->post_type ) {
			return false;
		}

		if ( ! current_user_can( 'manage_options', $post_id ) ) {
			return false;
		}
		
		$fields = apply_filters('fktr_clean_provider_fields',$_POST);
		$fields = apply_filters('fktr_provider_before_save',$fields);
		
		foreach ($fields as $field => $value ) {
			
			if ( !is_null( $value ) ) {
				$new = apply_filters('fktr_provider_metabox_save_' . $field, $value );  //filtra cada campo antes de grabar
				update_post_meta( $post_id, $field, $new );
				
			}
			
		}
		do_action( 'fktr_save_provider', $post_id, $post );
	}
	
	
} 

endif;

$fktrPostTypeProviders = new fktrPostTypeProviders();

?>