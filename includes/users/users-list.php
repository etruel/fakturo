<?php
/**
 * Description of users-list: CLASE PARA FUNCIONES DE USUARIOS
 * @author esteban
 */

// don't load directly 
if ( !defined('ABSPATH') ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

//$fktruserslist = new fktrUsersList();

if (!class_exists( 'fktrUsersList' ) ):
class fktrUsersList {

	public function __construct( ) {
		//*************************************** User Custom Fields
//		add_action('show_user_profile',			array(__CLASS__, 'user_custom_fields'));
//		add_action('edit_user_profile',			array(__CLASS__, 'user_custom_fields'));
//		add_action('user_new_form',			array(__CLASS__, 'user_custom_fields'));
//		add_action('personal_options_update',	array(__CLASS__, 'save_user_custom_fields'));
//		add_action('edit_user_profile_update',	array(__CLASS__, 'save_user_custom_fields'));
//		add_action('user_register',	array(__CLASS__, 'save_user_custom_fields'));

		add_filter('user_contactmethods', array(__CLASS__, 'modify_contact_methods'),30 );
		
		/****** Users list tweaks, filters, views and columns ***************/
		add_filter( 'manage_users_columns', array(__CLASS__, 'manage_users_columns'),90 );
		//add_filter( 'views_users', array(__CLASS__, 'views_plugin_users'),90 );
		add_filter( 'pre_user_query', array(__CLASS__, 'users_role_filter'),90 );
		
		add_filter( 'editable_roles', array(__CLASS__, 'just_plugin_roles'),90 );

		/*
		add_action('admin_print_styles-user-edit.php', array( __CLASS__, 'only_profile_user_styles') );
		add_action('admin_print_styles-user-new.php', array( __CLASS__, 'only_profile_user_styles') );
		add_action('admin_print_styles-profile.php', array( __CLASS__, 'only_profile_user_styles') );
		add_action('admin_print_scripts-user-edit.php', array( __CLASS__, 'only_profile_user_scripts') );
		add_action('admin_print_scripts-user-new.php', array( __CLASS__, 'only_profile_user_scripts') );
		add_action('admin_print_scripts-profile.php', array( __CLASS__, 'only_profile_user_scripts') );
		*/
	}
	

	/******  Users List columns  ***************/
	public static function manage_users_columns($columns){
		unset( $columns['posts'] );
		return $columns;
	}
	/******  Role Filters  ***************/
	public static function views_plugin_users($views){
		global $pagenow,$wp_user_query,$current_user,$wpdb;
		if ( !is_admin() || (!$pagenow=='user-new.php' && !$pagenow=='user-edit.php') ) {
			return $views;
		}
			
		$fakturo_role = current_user_can('fakturo_manager') || current_user_can('fakturo_seller') || current_user_can('fakturo_customer');
		if($fakturo_role){
			$fakturo_roles = array('fakturo_manager','fakturo_seller','fakturo_customer');
			$views= array_intersect_key( $views, array_flip($fakturo_roles) );
			$args = array(
				'meta_query' => array(
					'key'     => 'wp_capabilities',
					'value'   => $fakturo_roles,
					'compare' => 'IN'
				),
				'count_total' => true,
				'fields'	  => 'ID',
			);
			$fakturo_users = new WP_User_Query($args);
			$total_users = $fakturo_users->get_total();
			$class = empty($_GET['role']) ? ' class="current"' : '';
			$total_users_all = "<a href='users.php'$class>" . sprintf( _nx( 'All <span class="count">(%s)</span>', 'All <span class="count">(%s)</span>', $total_users, 'users' ), number_format_i18n( $total_users ) ) . '</a>';
			$views = array('all'=> $total_users_all) + $views ;
		}	
		return $views;
	}

	public static function just_plugin_roles($editable_roles){
		global $pagenow,$wp_query,$current_user;
		if ( !is_admin() || (!$pagenow=='user-new.php' && !$pagenow=='user-edit.php') ) 
			return $editable_roles;
		$fakturo_role=current_user_can('fakturo_manager') || current_user_can('fakturo_seller');
		if($fakturo_role){
			$fakturo_roles = array('fakturo_manager','fakturo_seller');
			$editable_roles= array_intersect_key( $editable_roles, array_flip($fakturo_roles) );
		}
		return $editable_roles;
	}

	public static function users_role_filter( $query ){
		global $pagenow,$wp_query,$current_user;

		if ( is_admin() && $pagenow=='users.php' ) {
			$fakturo_role=current_user_can('fakturo_manager') || current_user_can('fakturo_seller');
			if($fakturo_role){
				global $wpdb;
				$fakturo_roles = array('fakturo_manager','fakturo_seller');
				if( $query->get('role')=="" ) {
					$rolefilter = "(";
					$i=0;
					foreach($fakturo_roles as $role) {
						$role=  esc_sql($wpdb->esc_like($role));
						$rolefilter.= (1<=$i++) ? " OR " : "";
						$rolefilter.=' CAST('.$wpdb->usermeta.'.meta_value AS CHAR) LIKE \'%\"'.$role.'\"%\' ';
					}
					$rolefilter .= " ) ";
					$query->query_from .= " INNER JOIN {$wpdb->usermeta} ON " . 
						"({$wpdb->users}.ID={$wpdb->usermeta}.user_id) " ;

					$query->query_where .= " AND ( {$wpdb->usermeta}.meta_key = 'wp_capabilities' AND " . $rolefilter . " ) "; 
						//"CAST( {$wpdb->usermeta}.meta_value AS CHAR) LIKE '%\"fakturo\\_seller\"%' ";
						
				}
			}
		}
	}
	
	/****** Funciones para estilos y javascripts en perfil de usuario   ***************/
	public static function only_profile_user_scripts(){
		global $pagenow;
		//wp_die($pagenow);
		if (! empty($pagenow) && ('user-edit.php' === $pagenow || 'profile.php' === $pagenow))
			add_action('admin_head', array( __CLASS__, 'only_profile_user_javascript') ); 
//		add_action('admin_head', array( __CLASS__, 'user_custom_js') );
	}
	public static function only_profile_user_javascript() {
		global $post;
		?><script type="text/javascript">jQuery(document).ready(function($){
		<?php ?>
			// personal options
			var $po_title = $('form#your-profile h3:eq(0)'); 
			var $po_table = $('form#your-profile h3:eq(0) ~ table:eq(0)');
			//var $po_html = $po_title.prop('outerHTML') + $po_table.prop('outerHTML');
			var $po_html = $po_title[0].outerHTML + $po_table[0].outerHTML;
			// Name
			var $name_title = $('form#your-profile h3:eq(1)'); 
			var $name_table = $('form#your-profile h3:eq(1) ~ table:eq(0)');
			var $name_html = $name_title[0].outerHTML + $name_table[0].outerHTML;
			// Contact Info
			var $cf_title = $('form#your-profile h3:eq(2)'); 
			var $cf_table = $('form#your-profile h3:eq(2) ~ table:eq(0)');
			//$('.user-twitter-wrap',$cf_table).remove(); //remove por php
			var $cf_html = $cf_title[0].outerHTML + $cf_table[0].outerHTML;
			// About the user
			var $atu_title = $('form#your-profile h3:eq(3)'); 
			var $atu_table = $('form#your-profile h3:eq(3) ~ table:eq(0)');
			$('.user-description-wrap',$atu_table).remove();
			var $atu_html = $atu_title[0].outerHTML + $atu_table[0].outerHTML;
			// user taxonomies
			//var $tax_title = $('form#your-profile h3.user_taxonomies'); 
//			var $tax_table = $('form#your-profile .user_taxonomies');
//			var $tax_html = $tax_table[0].outerHTML;
			// wordpress-seo
			var $seo_title = $('form#your-profile h3#wordpress-seo'); 
			var $seo_table = $('form#your-profile h3#wordpress-seo ~ table:eq(0)');
			try{
				var $seo_html = $seo_title[0].outerHTML + $seo_table[0].outerHTML;
			}catch(err){
				var $seo_html = "";
			}
			$po_table.remove();
			$po_title.remove();
//				$name_table.remove();
//				$name_title.remove();
			$cf_table.remove();
			$cf_title.remove();
			$atu_table.remove();
			$atu_title.remove();
//			$tax_table.remove();
			$seo_table.remove();
			$seo_title.remove();

			$('p.submit').before($atu_html);
			$('p.submit').before($cf_html);
//			$('p.submit').before($tax_html);
			$('p.submit').before($po_html);
			$('form#your-profile').show();
		});			
		</script><?php
	}

/*	public static function user_custom_js() {
		global $post, $pagenow;
		if (!empty($pagenow) && ('user-edit.php' === $pagenow || 'profile.php' === $pagenow)) 
			$newser = false;
		else $newser = true;
		?><script type="text/javascript">jQuery(document).ready(function($){
		});		// jQuery
		</script><?php
	}*/

	public static function only_profile_user_styles() {
		global $post;
		add_action('admin_head', array( __CLASS__, 'only_profile_user_css') );
		//wp_enqueue_style('thickbox');
	}

	public static function only_profile_user_css() {
		global $post;
		?><style type="text/css">
			form#your-profile {display: none;}
			h3 {background-color: #6EDA67;padding: 10px;}
			.hide {display: none;}
			#msgdrag {display:none;color:red;padding: 0 0 0 20px;font-weight: 600;font-size: 1em;}
			.uc_header {padding: 0 0 0 30px;font-weight: 600;font-size: 0.9em;}
			div.uc_column {float: left;width: 19%;}
			.uc_actions{margin-left: 5px;}
			.delete{color: #F88;font-size: 1.6em;}
			.delete:hover{color: red;}
			.delete:before { content: "\2718";}
			.add:before { content: "\271A";}
			/*
			label[for=rich_editing] input { display: none; }
			label[for=rich_editing]:before { content: 'This option has been disabled (Formerly: ' }
			label[for=rich_editing]:after { content: ')'; } */
			/* form#your-profile h3#wordpress-seo,
			form#your-profile h3#wordpress-seo ~ table {display: none;	} */
		</style><?php
	}
	/****** Fin Funciones para estilos y javascripts en perfil de usuario   ***************/

	
	/**
	 * 
	 */
	public static function modify_contact_methods($profile_fields) {
		$new_fields['address'] = __("Address", FAKTURO_TEXT_DOMAIN );
		$new_fields['phone'] = __("Telephone", FAKTURO_TEXT_DOMAIN );
		$new_fields['cellular'] = __("Cellular", FAKTURO_TEXT_DOMAIN );
		$new_fields['facebook'] = __("Facebook URL", FAKTURO_TEXT_DOMAIN );
				
//		$profile_fields['twitter'] = 'Twitter Username';
//		$profile_fields['facebook'] = 'Facebook URL';
//		$profile_fields['googleplus'] = 'Google+ URL';
//		// Remove old fields
//		unset($profile_fields['aim']);

		return $new_fields;
	}

	
}
endif;
$fktrUsersList = new fktrUsersList();
