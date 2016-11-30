<?php
// don't load directly 
if ( !defined('ABSPATH') ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

/**
 *  PLUGINS PAGES ADDONS
 *  Experimental.  Uses worpdress plugins.php file filtered
 */
class fktr_admin_page {
	function __construct() {

		add_action('admin_init', array(__CLASS__, 'redirect_to_addon_page'),0  );
		add_action('admin_menu', array(__CLASS__, 'admin_menu'),99 );
		add_action('admin_print_styles', array(__CLASS__,'styles'));
		add_filter('all_plugins', array(__CLASS__, 'showhide_addons'));
		add_filter('manage_plugins_page_fakturo_columns', array(__CLASS__, 'addons_get_columns'));
		add_action('manage_plugins_custom_column', array(__CLASS__, 'addons_custom_columns') ,10,3);

	}

	public static function redirect_to_addon_page() {
		global $pagenow;
		$getpage = (isset($_REQUEST['page']) && !empty($_REQUEST['page']) ) ? $_REQUEST['page'] : '';
		if ($pagenow != 'admin-ajax.php' || $getpage == 'fakturo')
		if ($pagenow == 'plugins.php' && ($getpage=='')  ){
			$plugin = isset($_REQUEST['plugin']) ? $_REQUEST['plugin'] : '';
	
			$s = isset($_REQUEST['s']) ? urlencode($_REQUEST['s']) : '';

			$location = '';

			$actioned = array_multi_key_exists( array('error', 'deleted', 'activate', 'activate-multi', 'deactivate', 'deactivate-multi', '_error_nonce' ), $_REQUEST, false );
			if( ( isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'page=fakturo') ) && $actioned ) {
				$location = add_query_arg('page','fakturo', $location);
				wp_redirect($location);
			}
		}
	}
	public static function admin_menu() {
		$page = add_submenu_page(
		'plugins.php',
			__( 'Add-ons', FAKTURO_TEXT_DOMAIN),
			__( 'Fakturo Add-ons', FAKTURO_TEXT_DOMAIN),
			'manage_options',
			'fakturo',
			array(__CLASS__, 'plugins_page')
		);
		add_action('admin_print_scripts-' . $page, array(__CLASS__, 'addons_scripts'));
		$page = add_submenu_page(
			'fakturo_dashboard',
			__( 'Add-ons', FAKTURO_TEXT_DOMAIN),
			__( 'Extensions', FAKTURO_TEXT_DOMAIN),
			'manage_options',
			'plugins.php?page=fakturo'
		);
	
	}
	public static function styles() {
		wp_dequeue_style('icons');
	}
	public static function addons_scripts() {
		
		wp_enqueue_script('jquery');
		wp_enqueue_script('plupload-all');
		wp_enqueue_style('plugin-install');
		wp_enqueue_script('plugin-install');
		add_thickbox();
		wp_enqueue_script( 'jquery-addons-page', FAKTURO_PLUGIN_URL . 'assets/js/addons_page.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );

	}
	public static function plugins_page() {
		if (!defined('WPEM_ADMIN_DIR')) {
			define('WPEM_ADMIN_DIR' , ABSPATH . basename(admin_url()));
		}
		if ( ! class_exists( 'WP_List_Table' ) ) {
			require_once WPEM_ADMIN_DIR . '/includes/class-wp-list-table.php';
		}
		
		if ( ! class_exists( 'WP_Plugins_List_Table' ) ) {
			require WPEM_ADMIN_DIR .'/includes/class-wp-plugins-list-table.php';
		}
		
		global $plugins, $status, $wp_list_table;
		$status ='all'; 
		$page=  (!isset($page) or is_null($page))? 1 : $page;
		$plugins['all']=get_plugins();

		require WPEM_ADMIN_DIR . '/plugins.php' ;
		exit;

	}
	public static function showhide_addons($plugins) {
		global $current_screen;
		if ($current_screen->id == 'plugins_page_fakturo'){
			$plugins = apply_filters('fktr_addons_array', self::read_addons($plugins), 10, 1 );
			foreach ($plugins as $key => $value) {
				if(strpos($key, 'fakturo_')===FALSE) {		
					unset( $plugins[$key] );
				} else {
					if(isset($plugins[$key]['Remote'])){
						add_filter( "plugin_action_links_{$key}", array(__CLASS__, 'addons_row_actions'),15,4);	
					}
				}
			}		
		} else {
			foreach ($plugins as $key => $value) {
				if(strpos($key, 'fakturo_')!==FALSE) {
					unset( $plugins[$key] );
				}
			}
		}
		
		return $plugins;
	}
	public static function addons_row_actions($actions, $plugin_file, $plugin_data, $context) {
		$actions = array();
		$actions['buynow'] =  '<a target="_Blank" class="edit" aria-label="' . esc_attr( sprintf( __( 'Go to %s WebPage',FAKTURO_TEXT_DOMAIN), $plugin_data['Name'] ) ) . '" title="' . esc_attr( sprintf( __( 'Open %s WebPage in new window.',FAKTURO_TEXT_DOMAIN), $plugin_data['Name'] ) ) . '" href="'.$plugin_data['PluginURI'].'">' . __('Details',FAKTURO_TEXT_DOMAIN) . '</a>';
		return $actions;
	}

	public static function read_addons($plugins){
	
		$cached = get_transient( 'fakturo_addons_data' );
		if ( !is_array( $cached ) ) { // If no cache read source feed
			$addonitems = WPeMatico::fetchFeed('https://etruel.com/downloads/category/fakturo/feed/', true, 10);
			$addon = array();
			foreach($addonitems->get_items() as $item) {
				$itemtitle = $item->get_title();
				$versions = $item->get_item_tags('', 'version');
				$version = (is_array($versions)) ? $versions[0]['data'] : '';
				$guid = $item->get_item_tags('', 'guid');
				$guid = (is_array($guid)) ? $guid[0]['data'] : '';
				wp_parse_str($guid, $query ); 
				if(isset($query ) && !empty($query ) ) {
					if(isset($query['p'])){
						$download_id = $query['p'];
					}
				}
			
				$plugindirname = str_replace('-','_', strtolower( sanitize_file_name( $itemtitle )));
				$addon[ $plugindirname ] = Array (
					'Name'		  => $itemtitle,
					'PluginURI'	  => $item->get_permalink(),
					'buynowURI'	  => 'https://etruel.com/checkout?edd_action=add_to_cart&download_id='.$download_id.'&edd_options[price_id]=1',
					'Version'	  => $version,	
					'Description' => $item->get_description(),
					'Author'	  => 'etruel', 
					'AuthorURI'   => 'https://etruel.com',
					'TextDomain'  => '',
					'DomainPath'  => '',
					'Network'	  => '',
					'Title'		  => $itemtitle,
					'AuthorName'  => 'etruel', 
					'Remote'	  => true 
				);
			}
			$addons = apply_filters( 'fktr_addons_array', array_filter( $addon ) );
			$length = apply_filters( 'fakturo_addons_transient_length', DAY_IN_SECONDS );
			set_transient( 'fakturo_addons_data', $addons, $length );
			$cached = $addons;
		}
		
		$plugins = array_merge( $plugins, $cached );
		
		return $plugins;	
	}
	
	public static function addons_get_columns() {
		global $status;

		return array(
			'cb'          => !in_array( $status, array( 'mustuse', 'dropins' ) ) ? '<input type="checkbox" />' : '',
			'name'        => __( 'Add On' ),
			'description' => __( 'Description' ),
			'test' => __( 'Adquire' ),
		);
	}
	
	public static function addons_custom_columns($column_name, $plugin_file, $plugin_data ) {
		if (strpos($plugin_data['Name'], 'Fakturo ') === false ) {
			return true;
		}
		$caption = ( (isset($plugin_data['installed']) && ($plugin_data['installed']) ) || !isset($plugin_data['Remote'])) ? __('Installed', FAKTURO_TEXT_DOMAIN) : __('Purchase', FAKTURO_TEXT_DOMAIN);
		if (isset($plugin_data['installed']) && ($plugin_data['installed']) ) {
			if(!isset($plugin_data['Remote'])) {
				$caption = __('Installed', FAKTURO_TEXT_DOMAIN);
				$title = __('See details and prices on etruel\'s store', FAKTURO_TEXT_DOMAIN);
				$url   = 'https'.strstr( $plugin_data['PluginURI'], '://');
			}
		}else {
			if(!isset($plugin_data['Remote'])) {
				$caption = __('Locally', FAKTURO_TEXT_DOMAIN);
				$title = __('Go to plugin URI', FAKTURO_TEXT_DOMAIN);
				$url   = '#'.$plugin_data['Name'];
			}else{
				$caption = __('Purchase', FAKTURO_TEXT_DOMAIN); //**** 
				$title = __('Go to purchase on the etruel\'s store', FAKTURO_TEXT_DOMAIN);
				$url   = 'https'.strstr( $plugin_data['buynowURI'], '://');
			}
		}
				
		$target = ( $caption == __('Locally', FAKTURO_TEXT_DOMAIN) ) ? '_self' : '_blank';
		$class = ( $caption == __('Purchase', FAKTURO_TEXT_DOMAIN) ) ? 'button-primary' : '';
		
		echo '<a target="'.$target.'" class="button '.$class.'" title="'.$title.'" href="'.$url.'">' . $caption . '</a>';
		return true;
	}

}
$fktr_admin_page = new fktr_admin_page();



?>