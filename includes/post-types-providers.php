<?php


// Exit if accessed directly
if (!defined('ABSPATH'))  {
	exit;
}


if ( ! class_exists('fktrPostTypeProviders') ) :
class fktrPostTypeProviders {
	function __construct() {
		
		add_action( 'init', array('fktrPostTypeProviders', 'setup'), 1 );
		add_action( 'activated_plugin', array('fktrPostTypeProviders', 'setup'), 1 );
		add_action('transition_post_status', array('fktrPostTypeProviders', 'default_fields'), 10, 3);
		add_action('save_post', array('fktrPostTypeProviders', 'save'), 10, 2 );
		
	
		add_action( 'admin_print_scripts-post-new.php', array('fktrPostTypeProviders','scripts'), 11 );
		add_action( 'admin_print_scripts-post.php', array('fktrPostTypeProviders','scripts'), 11 );
		
		add_action('admin_print_styles-post-new.php', array('fktrPostTypeProviders','styles'));
		add_action('admin_print_styles-post.php', array('fktrPostTypeProviders','styles'));
		
		add_action('wp_ajax_get_provider_states', array('fktrPostTypeProviders', 'get_provider_states'));
		
		add_filter('fktr_clean_provider_fields', array('fktrPostTypeProviders', 'clean_fields'), 10, 1);
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
			'publish_post' => 'publish_fktr_provider',
			'publish_posts' => 'publish_fktr_providers',
			'read_post' => 'read_fktr_provider',
			'read_private_posts' => 'read_private_fktr_providers',
			'edit_post' => 'edit_fktr_provider',
			'edit_published_posts' => 'edit_published_fktr_providers',
			'edit_private_posts' => 'edit_private_fktr_providers',
			'edit_posts' => 'edit_fktr_providers',
			'edit_others_posts' => 'edit_others_fktr_providers',
			'delete_post' => 'delete_fktr_provider',
			'delete_posts' => 'delete_fktr_providers',
			'delete_published_posts' => 'delete_published_fktr_providers',
			'delete_private_posts' => 'delete_private_fktr_providers',
			'delete_others_posts' => 'delete_others_fktr_providers',
			);

		$args = array( 
			'labels' => $labels,
			'hierarchical' => false,
			'description' => 'Fakturo Providers',
			'supports' => array( 'title', 'thumbnail',/* 'custom-fields' */),
			'register_meta_box_cb' => array('fktrPostTypeProviders', 'meta_boxes'),
			'public' => false,
			'show_ui' => true,
			'show_in_menu' => false, //'fakturo_dashboard',
			'menu_position' => 27,
			'menu_icon' => 'dashicons-businessman',
			'show_in_nav_menus' => true,
			'publicly_queryable' => false,
			'exclude_from_search' => false,
			'has_archive' => false,
			'query_var' => true,
			'can_export' => true,
			'rewrite' => true,
//			'map_meta_cap' => true,
			'capabilities' => $capabilities
		);

		register_post_type( 'fktr_provider', $args );
		
		add_filter('enter_title_here', array('fktrPostTypeProviders', 'name_placeholder'),10,2);

	}
	
	public static function meta_boxes() {
		
		add_action('wp_ajax_webcam_shot', 'fakturo_ajax_webcam_shot');
	
		add_meta_box('fakturo-active-box', __('Active provider', FAKTURO_TEXT_DOMAIN ), array('fktrPostTypeProviders', 'active'), 'fktr_provider', 'side', 'high');
		// Remove Custom Fields Metabox
		remove_meta_box('postimagediv', 'fktr_provider', 'side' );
		
		
		add_meta_box('postimagediv', __('Provider Image', FAKTURO_TEXT_DOMAIN ), array('fktrPostTypeProviders', 'thumbnail_box'), 'fktr_provider', 'side', 'high');
		add_meta_box('fakturo-seller-box', __('Assign Seller', FAKTURO_TEXT_DOMAIN ), array('fktrPostTypeProviders', 'seller_box'),'fktr_provider','side', 'high' );
		
		add_meta_box('fakturo-data-box', __('Complete Provider Data', FAKTURO_TEXT_DOMAIN ), array('fktrPostTypeProviders', 'data_box'),'fktr_provider','normal', 'default' );
		add_meta_box('fakturo-options-box', __('Provider Contacts', FAKTURO_TEXT_DOMAIN ), array('fktrPostTypeProviders', 'options_box'),'fktr_provider','normal', 'default' );
		
		remove_meta_box('fktr_locationsdiv', 'fktr_provider', 'side');
		remove_meta_box('tagsdiv-fktr_bank_entities', 'fktr_provider', 'side');
		
		do_action('add_ftkr_provider_meta_boxes');
	}
	public static function active() {
		global $post;
		
		$provider_data = self::get_provider_data($post->ID);
		$echoHtml = '<table class="form-table">
					<tbody>
					<tr class="tr_fktr">
						<th rowspan="2">
						<input id="active" type="checkbox" class="slidercheck" name="active" value="1" '.(($provider_data['active'])?'checked="checked"':'').'><label for="active"><span class="ui"></span>'.__('Active', FAKTURO_TEXT_DOMAIN ).'	</label>
						</th>
					</tr>
					</tbody>
				</table>';
		$echoHtml = apply_filters('fktr_provider_active_box', $echoHtml);
		echo $echoHtml;
		do_action('add_fktr_provider_active_box', $echoHtml);
		
	}
	public static function thumbnail_box() {
		global $post;
		$thumbnail_id = get_post_meta($post->ID, '_thumbnail_id', true );
		$echoHtml = '
			<div id="snapshot_container_wrapper">
				<div id="snapshot_container_buttons">
					<a id="snapshot_btn" href="javascript:showSnapshot()" class="nobutton">' . __( 'Take a snapshot', FAKTURO_TEXT_DOMAIN ) . '</a>
					<div id="my_camera" style="display:none;">				
					</div>
					<img src="" id="snap_image" style="display:none;">
					<input type="hidden" name="webcam_image">
					<a href="javascript:take_snapshot()" class="button" id="take_snapshot" style="display:none;">'.__( 'Snapshot').'</a>
					<a href="javascript:reset_webcam()" class="button" id="snapshot_reset" style="display:none;">'.__( 'Reset').'</a>
					<a href="javascript:snapshot_cancel()" class="button" id="snapshot_cancel" style="display:none;">'.__( 'Cancel').'</a>
				</div>
			</div>
			<div class="featured-image-client">
							'._wp_post_thumbnail_html( $thumbnail_id, $post->ID ).'
				</div>
			';
			
		$echoHtml = apply_filters('fktr_provider_thumbnail_box', $echoHtml);
		echo $echoHtml;
		do_action('add_fktr_provider_thumbnail_box', $echoHtml);
		
	}
	public static function seller_box() {
		global $post;
		$provider_data = self::get_provider_data($post->ID);
		$user_aseller = $provider_data['user_aseller'];
		
		$inTD = '';
		if(!current_user_can('fakturo_seller'))	 {
			$allsellers = get_users( array( 'role' => 'fakturo_seller' ) );
			
			$inTD .= '<select name="user_aseller" id="user_aseller">';
			$inTD .= '<option value="'.(( $user_aseller == 0)?' selected="selected"':'').'">'. __('Choose a Salesman', FAKTURO_TEXT_DOMAIN  ) . '</option>';
			foreach ( $allsellers as $suser ) {
				$inTD .= '<option value="' . $suser->ID . '" ' . selected($user_aseller, $suser->ID, false) . '>' . esc_html( $suser->display_name ) . '</option>';
			}
			$inTD .= '</select>';
		} else  {
			$inTD .= '<input type="hidden" name="user_aseller" id="user_aseller" value="'. get_current_user_id() .'" class="regular-text ltr">';
		}	
		$echoHtml = '<table class="form-table">
						<tbody>
						<tr class="user-display-name-wrap" id="row_user_aseller">
							<td>
								'.$inTD.'
							</td>
						</tr>
					</tbody>
				</table>';
				
		$echoHtml = apply_filters('fktr_provider_seller_box', $echoHtml);
		echo $echoHtml;
		do_action('add_fktr_provider_seller_box', $echoHtml);		
		
		
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
			'taxonomy'           => 'fktr_countries',
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
			'taxonomy'           => 'fktr_countries',
			'hide_if_empty'      => false
		));
		
		if ($provider_data['selected_country'] == 0 || strlen($selectState) < 90) {
			
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
					<tr class="tr_fktr">
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
					<tr class="tr_fktr">
						<th><label for="country">'. __('Country', FAKTURO_TEXT_DOMAIN ).'	</label></th>
						<td>'.$selectCountry.'</td>
					</tr>
					<tr class="tr_fktr">
						<th><label for="states">'. __('States', FAKTURO_TEXT_DOMAIN ) .'	</label></th>
						<td id="td_select_state">
							'.$selectState.'
						</td>
					</tr>
					<tr class="tr_fktr">
						<th><label for="city">'.__('City', FAKTURO_TEXT_DOMAIN ) .'	</label></th>
						<td><input id="city" type="text" name="city" value="'.$provider_data['city'].'" class="regular-text"></td>
					</tr>
					<tr class="tr_fktr">
						<th><label for="bank_entity">'.__('Bank Entity', FAKTURO_TEXT_DOMAIN ).'	</label></th>
						<td>'.$selectBankEntities.'</td>
					</tr>
					<tr class="tr_fktr">
						<th><label for="bank_account">'.__('Bank Account', FAKTURO_TEXT_DOMAIN ) .'	</label></th>
						<td><input id="bank_account" type="text" name="bank_account" value="'.$provider_data['bank_account'].'" class="regular-text"></td>
					</tr>
					<tr class="tr_fktr">
						<th><label for="postcode">'.__('Postcode', FAKTURO_TEXT_DOMAIN ).'	</label></th>
						<td><input id="postcode" type="text" name="postcode" value="'.$provider_data['postcode'].'" class="regular-text"></td>
					</tr>
					<tr class="tr_fktr">
						<th><label for="phone">'.__('Phone', FAKTURO_TEXT_DOMAIN ) .'	</label></th>
						<td><input id="phone" type="text" name="phone" value="'.$provider_data['phone'].'" class="regular-text"></td>
					</tr>
					<tr class="tr_fktr">
						<th><label for="cell_phone">'.__('Cell phone', FAKTURO_TEXT_DOMAIN ) .'	</label></th>
						<td><input id="cell_phone" type="text" name="cell_phone" value="'.$provider_data['cell_phone'].'" class="regular-text"></td>
					</tr>
					<tr class="user-email-wrap">
						<th><label for="email">'.__('E-mail', FAKTURO_TEXT_DOMAIN ) .'</label></th>
						<td><input type="email" name="email" id="email" value="'.$provider_data['email'].'" class="regular-text ltr"></td>
					</tr>
					<tr class="tr_fktr">
						<th><label for="web">'.__('Web', FAKTURO_TEXT_DOMAIN ).'</label></th>
						<td><input id="web" type="text" name="web" value="'.$provider_data['web'].'" class="regular-text"></td>
					</tr>
					</tbody>
				</table>';
		$echoHtml = apply_filters('fktr_provider_data_box', $echoHtml);
		echo $echoHtml;
		do_action('add_fktr_provider_data_box', $echoHtml);
		
	}
	public static function options_box() {
		global $post;
		$provider_data = self::get_provider_data($post->ID);
		$user_contacts = $provider_data;
		
		
		$echoContacts = '';
		$a = 0;
		foreach ($user_contacts['uc_description'] as $i => $desc) {
	
			$lastitem = $i==count(@$user_contacts['uc_description']); 
			$echoContacts .= '<div id="uc_ID'.$i.'" class="sortitem '.((($i % 2) == 0) ? 'bw' :  'lightblue').' '.(($lastitem)?'uc_new_field':'').'" '.(($lastitem)?'style="display:none;"':'').' > 
						<div class="sorthandle"> </div> <!-- sort handle -->
						<div class="uc_column" id="">
							<input name="uc_description[]" type="text" value="'.stripslashes(@$user_contacts['uc_description'][$i]).'" class="large-text"/>
						</div>
						<div class="uc_column" id="">
							<input name="uc_phone[]" type="text" value="'.stripslashes(@$user_contacts['uc_phone'][$i]).'" class="large-text"/>
						</div>
						<div class="uc_column" id="">
							<input name="uc_email[]" type="text" value="'.stripslashes(@$user_contacts['uc_email'][$i]).'" class="large-text"/>
						</div>
						<div class="uc_column" id="">
							<input name="uc_position[]" type="text" value="'.stripslashes(@$user_contacts['uc_position'][$i]).'" class="large-text"/>
						</div>
						<div class="uc_column" id="">
							<input name="uc_address[]" type="text" value="'.stripslashes(@$user_contacts['uc_address'][$i]).'" class="large-text"/>
						</div>
						<div class="" id="uc_actions">
							<label title="'. __('Delete this item',  FAKTURO_TEXT_DOMAIN  ).'" data-id="'.$i.'" class="delete"></label>
						</div>
					</div>';
			$a=$i;
		}
		
		
		$echoHtml = '<table class="form-table">
					<tbody>
					<tr class="user-display-name-wrap">
						<td>
							<div class="uc_header">
							<div class="uc_column">'.__('Description', FAKTURO_TEXT_DOMAIN  ).'</div>
							<div class="uc_column">'.__('Phone', FAKTURO_TEXT_DOMAIN  ) .'</div>
							<div class="uc_column">'. __('Email', FAKTURO_TEXT_DOMAIN  ) .'</div>
							<div class="uc_column">'. __('Position', FAKTURO_TEXT_DOMAIN  ) .'</div>
							<div class="uc_column">'. __('Address', FAKTURO_TEXT_DOMAIN  ) .'</div>
							</div>
							<br />
			
							<div id="user_contacts"> 
								'.$echoContacts.'
							</div>
							<input id="ucfield_max" value="'.$a.'" type="hidden" name="ucfield_max"/>
							<div id="paging-box">		  
								<a href="#" class="button-primary add" id="addmoreuc" style="font-weight: bold; text-decoration: none;"> '.__('Add User Contact', FAKTURO_TEXT_DOMAIN  ).'</a>
								<label id="msgdrag"></label>
							</div>
						</td>
						</tr>
					</tbody>
					</table>';
		$echoHtml = apply_filters('fktr_provider_options_box', $echoHtml);
		echo $echoHtml;
		do_action('add_fktr_provider_options_box', $echoHtml);
		
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
			'taxonomy'           => 'fktr_countries',
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
			wp_enqueue_script('webcam', FAKTURO_PLUGIN_URL .'assets/js/webcamjs-master/webcam.min.js', array('jquery'), WPE_FAKTURO_VERSION, true);
			wp_enqueue_script( 'jquery-snapshot', FAKTURO_PLUGIN_URL . 'assets/js/snapshot.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			wp_enqueue_script( 'jquery-select2', FAKTURO_PLUGIN_URL . 'assets/js/jquery.select2.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			wp_enqueue_script( 'jquery-vsort', FAKTURO_PLUGIN_URL . 'assets/js/jquery.vSort.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			
			wp_enqueue_script( 'post-type-providers', FAKTURO_PLUGIN_URL . 'assets/js/post-type-providers.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			wp_localize_script('post-type-providers', 'providers_object',
				array('ajax_url' => admin_url( 'admin-ajax.php' ),
					'loading_states_text' => __('Loading states...', FAKTURO_TEXT_DOMAIN ),
					'update_provider_contacts' => __('Update Provider to save changes.', FAKTURO_TEXT_DOMAIN ),
					'privider_delete_this_item' => __('Delete this item',  FAKTURO_TEXT_DOMAIN  )
				) );
			
			
		}
   
	}
	public static function styles() {
		global $post_type;
		if($post_type == 'fktr_provider') {
			wp_enqueue_style('style-select2',FAKTURO_PLUGIN_URL .'assets/css/select2.min.css');	
			wp_enqueue_style('post-type-providers',FAKTURO_PLUGIN_URL .'assets/css/post-type-providers.css');	
			
		}
   
	}
	public static function clean_fields($fields) {
		
		if (!isset($fields['taxpayer'])) {
			$fields['taxpayer'] = '';
		}
		if (!isset($fields['address'])) {
			$fields['address'] = '';
		}
		if (!isset($fields['selected_country'])) {
			$fields['selected_country'] = 0;
		}
		if (!isset($fields['selected_state'])) {
			$fields['selected_state'] = 0;
		}
		if (!isset($fields['city'])) {
			$fields['city'] = '';
		}
		if (!isset($fields['selected_bank_entity'])) {
			$fields['selected_bank_entity'] = 0;
		}
		if (!isset($fields['bank_account'])) {
			$fields['bank_account'] = '';
		}
		if (!isset($fields['postcode'])) {
			$fields['postcode'] = '';
		}
		if (!isset($fields['phone'])) {
			$fields['phone'] = '';
		}
		if (!isset($fields['cell_phone'])) {
			$fields['cell_phone'] = '';
		}
		if (!isset($fields['email'])) {
			$fields['email'] = '';
		}
		if (!isset($fields['web'])) {
			$fields['web'] = '';
		}
		if (!isset($fields['active'])) {
			$fields['active'] = 0;
		}

		if (!isset($fields['uc_description']) || !is_array($fields['uc_description'])) {
			$fields['uc_description'] = array();
		}

		if (!isset($fields['user_aseller'])) {
			$fields['user_aseller'] = 0;
		}
		
		
		return $fields;
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
			$fields['active'] = 1;
			$fields['uc_description'] = array();
			$fields['user_aseller'] = 0;
			

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

		if ( isset( $post->post_type ) &&  $post->post_type == 'revision'  || $post->post_type != 'fktr_provider') {
			return false;
		}

		if ( ! current_user_can( 'manage_options', $post_id ) ) {
			return false;
		}
		if ( ( defined( 'FKTR_STOP_PROPAGATION') && FKTR_STOP_PROPAGATION ) ) {
			return false;
		}
		$fields = apply_filters('fktr_clean_provider_fields',$_POST);
		$fields = apply_filters('fktr_provider_before_save',$fields);
		
		if (isset($fields['webcam_image']) && $fields['webcam_image'] != NULL ) {
			delete_post_meta($post_id, '_thumbnail_id');
			$filename = 'webcam_image_'.microtime().'.jpg';
			$file = wp_upload_bits($filename, null, base64_decode(substr($fields['webcam_image'], 23)));
			if ($file['error'] == FALSE) {
				$wp_filetype = wp_check_filetype($filename, null );
				$attachment = array(
					'post_mime_type' => $wp_filetype['type'],
					'post_parent' => $post_id,
					'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
					'post_content' => '',
					'post_status' => 'inherit'
				);
				$attachment_id = wp_insert_attachment( $attachment, $file['file'], $post_id );
				if (!is_wp_error($attachment_id)) {
					require_once(ABSPATH . "wp-admin" . '/includes/image.php');
					$attachment_data = wp_generate_attachment_metadata( $attachment_id, $file['file'] );
					wp_update_attachment_metadata( $attachment_id,  $attachment_data );
				}
				$new = apply_filters('fktr_provider_thumbnail_id', $attachment_id);
				add_post_meta($post_id, '_thumbnail_id', $new);
				unset($fields['webcam_image']);
			}
		}
		
		
		
		

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