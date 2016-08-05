<?php


// Exit if accessed directly
if (!defined('ABSPATH'))  {
	exit;
}


if ( ! class_exists('fktrSettings') ) :
class fktrSettings {

	function __construct() {
		add_action( 'init', array('fktrSettings', 'load_taxonomies'), 1, 99 );
		//add_action( 'in_admin_header', array('fktrSettings', 'probandoarriba'), 1, 0 );
		add_action( 'all_admin_notices', array('fktrSettings', 'add_setting_tabs'), 1, 0 );
	}

	
	public static function add_setting_tabs() {
		global $current_screen;
		if( ($current_screen->id == "edit-fktr_locations") 
			|| ($current_screen->id == "edit-fktr_bank_entities") 
			|| ($current_screen->id == "edit-fktr_payment_types") 
//			|| ($current_screen->id == "fktr_settings_screen") 
		) {
			//echo "Agregar tabs aca<br>--------------------------------------";
			
			$fktr_taxonomies = array(
				'fktr_locations',
				'fktr_bank_entities',
				'fktr_payment_types',
				);
			$url = $_SERVER['REQUEST_URI'];
			foreach($fktr_taxonomies as $fktr_tax) {
				if( strpos($url, $fktr_tax) ){
					$url = admin_url("edit-tags.php?taxonomy=".$fktr_tax);
					break;
				}
			}

			$tabs = self::get_Fakturo_Setting_Tabs();
			//echo '<div id="icon-themes" class="icon32"><br></div>';
			echo '<h2 class="nav-tab-wrapper fktr-settings-tabs">';
			if (isset($_GET['tab'])) {
			  $current_tab = $_GET['tab'];
			} else {
			  $current_tab = key($tabs);
			}
			
			foreach( $tabs as $tab_id => $tab_name ){
				$tab_url = add_query_arg( array('tab' => $tab_id), $url );

				// Remove the section from the tabs so we always end up at the main section
				$tab_url = remove_query_arg( 'section', $tab_url );
//				$tab_url = remove_query_arg( array('section'), $tab_url );

				$active = $current_tab == $tab_id ? ' nav-tab-active' : '';
				echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab_name ) . '" class="nav-tab' . $active . '">' . esc_html( $tab_name ) . '</a>';

//				$class = ( $tab_id == $current_tab ) ? ' nav-tab-active' : '';
//				echo "<a class='nav-tab$class' href='?page=fakturo%2Fsettings%2Ffakturo_settings.php&tab=$tab_id'>$tab_name</a>";
			}
			echo '</h2>';

			// sections
			$sections = self::get_Fakturo_Setting_Section($current_tab);

			echo '<div class="fktr-sections"><ul class="subsubsub">';
			if (isset($_GET['section'])) {
			  $current_section = $_GET['section'];
			} else {
			  $current_section = key($sections);
			}
			$endSection = end($sections);
			foreach ($sections as $section_id => $section_name) {
				$section_url = add_query_arg( array(
					'tab' => $current_tab,
					'section' => $section_id
				), $tab_url);
/*				$class = ( $section_id == $current_section ) ? ' current' : '';
				$delimiter = ($section_name != $endSection) ? ' | ' : '';
				echo "<li><a class='$class' href='?page=fakturo%2Fsettings%2Ffakturo_settings.php&tab=$current_tab&section=$section_id'>$section_name</a>$delimiter</li>";
*/
				$active = $current_section == $section_id ?  ' current' : '';
				$delimiter = ($section_name != $endSection) ? ' | ' : '';
				echo '<li><a href="' . esc_url( $section_url ) . '" title="' . esc_attr( $section_name ) . '" class="' . $active . '">' . esc_html( $section_name ) . '</a>' . $delimiter . '</li>';
			}
			echo '</ul></div>';
			?>
			<div id="tab_container">
			<form method="post" action="options.php">
				<table class="form-table">
				<?php
				do_action( 'fktr_settings_tab_' . $current_tab . '_' . $current_section );
				?>
				</table>
				<?php submit_button(); ?>
			</form>
		</div><!-- #tab_container-->
		<?php
		}
	}
	
	public static function get_Fakturo_Setting_tabs() {
		$tabs = array( 
			'general' => __( '​​General Settings', FAKTURO_TEXT_DOMAIN ), 
			'tables' => __( 'Tables', FAKTURO_TEXT_DOMAIN ), 
			'products' => __( '​​Products', FAKTURO_TEXT_DOMAIN ), 
			'taxes' => __( 'Taxes', FAKTURO_TEXT_DOMAIN ), 
			'extensions' => __( '​​Extensions', FAKTURO_TEXT_DOMAIN )
			);
		return apply_filters( 'Fakturo_Setting_Tabs', $tabs );
	}
	
	public static function get_Fakturo_Setting_sections() {
		$sections = array(
			'general' => array( 
				'company_info' => __( 'Company Info', FAKTURO_TEXT_DOMAIN ), 
				'system_settings' => __( 'System Settings', FAKTURO_TEXT_DOMAIN ), 
				'invoice_type' => __( 'Invoice Types', FAKTURO_TEXT_DOMAIN ),
				'payment_types' => __( 'Payment Types', FAKTURO_TEXT_DOMAIN ), 
	//			'user_preferences' => __( 'User Preferences', FAKTURO_TEXT_DOMAIN ), 
	//			'users' => __( 'Users', FAKTURO_TEXT_DOMAIN ),
			),
			'tables' => array( 
	//			'user-template' => __( 'User Template', FAKTURO_TEXT_DOMAIN ),
				'print-template' => __( 'Print Template', FAKTURO_TEXT_DOMAIN ), 
				'currencies' => __( 'Currencies', FAKTURO_TEXT_DOMAIN ),
				'bank_entities' => __( 'Bank Entities', FAKTURO_TEXT_DOMAIN ),
				'countries' => __( 'Countries', FAKTURO_TEXT_DOMAIN ),
				'states' => __( 'States', FAKTURO_TEXT_DOMAIN ),
			),
			'products' => array( 
				'product_types' => __( 'Product Types', FAKTURO_TEXT_DOMAIN ),
				'locations' => __( 'Locations', FAKTURO_TEXT_DOMAIN ),
				'packagings' => __( 'Packagings', FAKTURO_TEXT_DOMAIN ), 
				'price_scales' => __( 'Price Scales', FAKTURO_TEXT_DOMAIN ),
				'origins' => __( 'Origins', FAKTURO_TEXT_DOMAIN ),
			),
			'taxes' => array( 
				'taxes' => __( 'Taxes', FAKTURO_TEXT_DOMAIN ),
				'tax_condition' => __( 'Tax Conditions', FAKTURO_TEXT_DOMAIN ),
			),
			'extensions' => array( 
				'repairs_status' => __( 'Repairs Status', FAKTURO_TEXT_DOMAIN ),
				'emails' => __( 'Emails', FAKTURO_TEXT_DOMAIN ), 
			)
		);
		return apply_filters( 'Fakturo_Setting_Sections', $sections);
	}

	public static function get_Fakturo_Setting_Section($tab_name = 'general') {
		return self::get_Fakturo_Setting_sections()[$tab_name];
	}

	public static function getFakturoCurrentSection() {
		if (isset($_GET['section'])) {
			return $_GET['section'];
		}

		if (isset($_GET['tab'])) {
			return key(self::get_Fakturo_Setting_Section($_GET['tab']));
		}
		return key(self::get_Fakturo_Setting_Section( key(self::get_Fakturo_Setting_Tabs()) ));
	}
	
	
	
	public static function load_taxonomies() {
		$labels_model = array(
			'name'                       => _x( 'Locations', 'Locations', FAKTURO_TEXT_DOMAIN ),
			'singular_name'              => _x( 'Location', 'Location', FAKTURO_TEXT_DOMAIN ),
			'search_items'               => __( 'Search Locations', FAKTURO_TEXT_DOMAIN ),
			'popular_items'              => __( 'Popular Locations', FAKTURO_TEXT_DOMAIN ),
			'all_items'                  => __( 'All Locations', FAKTURO_TEXT_DOMAIN ),
			'parent_item'                => __( 'Country', FAKTURO_TEXT_DOMAIN ),
			'parent_item_colon'          => __( 'Country:', FAKTURO_TEXT_DOMAIN ),
			'edit_item'                  => __( 'Edit Location', FAKTURO_TEXT_DOMAIN ),
			'update_item'                => __( 'Update Location', FAKTURO_TEXT_DOMAIN ),
			'add_new_item'               => __( 'Add New Location', FAKTURO_TEXT_DOMAIN ),
			'new_item_name'              => __( 'New Location Name', FAKTURO_TEXT_DOMAIN ),
			'separate_items_with_commas' => __( 'Separate location with commas', FAKTURO_TEXT_DOMAIN ),
			'add_or_remove_items'        => __( 'Add or remove locations', FAKTURO_TEXT_DOMAIN ),
			'choose_from_most_used'      => __( 'Choose from the most used locations', FAKTURO_TEXT_DOMAIN ),
			'not_found'                  => __( 'No locations found.', FAKTURO_TEXT_DOMAIN ),
			'menu_name'                  => __( 'Locations', FAKTURO_TEXT_DOMAIN ),
		);

		$args_model = array(
			'hierarchical'          => true,
			'labels'                => $labels_model,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'fktr-locations' ),
		);

		register_taxonomy(
			'fktr_locations',
			'fktr_provider',
			$args_model
		);
		
		
		
		$labels_model = array(
			'name'                       => _x( 'Bank Entities', 'Bank Entities', FAKTURO_TEXT_DOMAIN ),
			'singular_name'              => _x( 'Bank Entity', 'Bank Entity', FAKTURO_TEXT_DOMAIN ),
			'search_items'               => __( 'Search Bank Entities', FAKTURO_TEXT_DOMAIN ),
			'popular_items'              => __( 'Popular Bank Entities', FAKTURO_TEXT_DOMAIN ),
			'all_items'                  => __( 'All Bank Entities', FAKTURO_TEXT_DOMAIN ),
			'parent_item'                => __( 'Bank', FAKTURO_TEXT_DOMAIN ),
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Bank Entity', FAKTURO_TEXT_DOMAIN ),
			'update_item'                => __( 'Update Bank Entity', FAKTURO_TEXT_DOMAIN ),
			'add_new_item'               => __( 'Add New Bank Entity', FAKTURO_TEXT_DOMAIN ),
			'new_item_name'              => __( 'New Bank Entity Name', FAKTURO_TEXT_DOMAIN ),
			'separate_items_with_commas' => __( 'Separate Bank Entity with commas', FAKTURO_TEXT_DOMAIN ),
			'add_or_remove_items'        => __( 'Add or remove Bank Entities', FAKTURO_TEXT_DOMAIN ),
			'choose_from_most_used'      => __( 'Choose from the most used Bank Entities', FAKTURO_TEXT_DOMAIN ),
			'not_found'                  => __( 'No Bank Entities found.', FAKTURO_TEXT_DOMAIN ),
			'menu_name'                  => __( 'Bank Entities', FAKTURO_TEXT_DOMAIN ),
		);

		$args_model = array(
			'hierarchical'          => false,
			'labels'                => $labels_model,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'fktr-bank-entities' ),
		);

		register_taxonomy(
			'fktr_bank_entities',
			'fktr_provider',
			$args_model
		);
		
		$labels_model = array(
			'name'                       => _x( 'Payment Types', 'Payment Types', FAKTURO_TEXT_DOMAIN ),
			'singular_name'              => _x( 'Payment Type', 'Payment Type', FAKTURO_TEXT_DOMAIN ),
			'search_items'               => __( 'Search Payment Types', FAKTURO_TEXT_DOMAIN ),
			'popular_items'              => __( 'Popular Payment Types', FAKTURO_TEXT_DOMAIN ),
			'all_items'                  => __( 'All Payment Types', FAKTURO_TEXT_DOMAIN ),
			'parent_item'                => __( 'Bank', FAKTURO_TEXT_DOMAIN ),
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Payment Type', FAKTURO_TEXT_DOMAIN ),
			'update_item'                => __( 'Update Payment Type', FAKTURO_TEXT_DOMAIN ),
			'add_new_item'               => __( 'Add New Payment Type', FAKTURO_TEXT_DOMAIN ),
			'new_item_name'              => __( 'New Payment Type Name', FAKTURO_TEXT_DOMAIN ),
			'separate_items_with_commas' => __( 'Separate Payment Type with commas', FAKTURO_TEXT_DOMAIN ),
			'add_or_remove_items'        => __( 'Add or remove Payment Types', FAKTURO_TEXT_DOMAIN ),
			'choose_from_most_used'      => __( 'Choose from the most used Payment Types', FAKTURO_TEXT_DOMAIN ),
			'not_found'                  => __( 'No Payment Types found.', FAKTURO_TEXT_DOMAIN ),
			'menu_name'                  => __( 'Payment Types', FAKTURO_TEXT_DOMAIN ),
		);

		$args_model = array(
			'hierarchical'          => false,
			'labels'                => $labels_model,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'fktr-payment-types' ),
		);
		register_taxonomy(
			'fktr_payment_types',
			'',
			$args_model
		);
	}
	
	
	
} 

endif;

$fktrSettings = new fktrSettings();

?>