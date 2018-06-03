<?php
	// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class fktr_wizard {

	public static $steps = array();
	public static $current_request = array();
	public static $steps_data = array();
	/**
	* Static function hooks
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function hooks() {

		add_action('admin_init', array(__CLASS__, 'force_redirect'));

		add_action('admin_post_fktr_wizard', array(__CLASS__, 'page'));
		add_action('admin_post_nopriv_fktr_wizard', array(__CLASS__, 'redirect_login'));

		
		add_action('fktr_wizard_output_print_scripts', array(__CLASS__, 'scripts'));
		add_action('fktr_wizard_output_print_styles', array(__CLASS__, 'styles'));
		
		add_action('admin_post_fktr_wizard_post', array(__CLASS__, 'post_actions'));

		self::add_step(1, __( 'Load Countries', 'fakturo' ), array(__CLASS__, 'page_step_one'), array(__CLASS__, 'action_one'));
		add_action('wp_ajax_fktr_load_countries_states', array(__CLASS__, 'load_countries_ajax'));
		add_action('wp_ajax_fktr_load_selected_countries_states', array(__CLASS__, 'load_countries_states_ajax'));
		
		self::add_step(2, __( 'Company Info', 'fakturo' ), array(__CLASS__, 'page_step_two'), array(__CLASS__, 'action_two'));

		self::add_step(3, __( 'Load Currencies', 'fakturo' ), array(__CLASS__, 'page_step_three'), null);
		add_action('wp_ajax_fktr_load_currencies', array(__CLASS__, 'load_currencies_ajax'));

		self::add_step(4, __( 'Money Format', 'fakturo' ), array(__CLASS__, 'page_step_four'), array(__CLASS__, 'action_four'));
		self::add_step(5, __( 'Invoice Details and Formats', 'fakturo' ), array(__CLASS__, 'page_invoice_format'), array(__CLASS__, 'action_invoice_format'));
		
		self::add_step(6, __( 'Products', 'fakturo' ), array(__CLASS__, 'page_products'), array(__CLASS__, 'action_products'));
		self::add_step(7, __( 'Payments', 'fakturo' ), array(__CLASS__, 'page_payments'), array(__CLASS__, 'action_payments'));
		
		
	}
	/**
	* Static function redirect_login
	* Redirect to login page when is user not logged.
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function redirect_login() {
		wp_redirect(wp_login_url(admin_url('admin-post.php?'.http_build_query($_GET)), true));
		die();
	}
	/**
	* Static function force_redirect
	* Force redirect to wizard page if exist a const FORCE_REDIRECT_FKTR_WIZARD.
	* @access public
	* @return void
	* @since version
	*/
	public static function force_redirect() {
		if (defined('FORCE_REDIRECT_FKTR_WIZARD')) {
			if (current_user_can('fktr_manage_wizard')) {
				$first_time_wizard = get_option('fktr_first_time_wizard', false);
				if (!$first_time_wizard) {
					wp_redirect(admin_url('admin-post.php?action=fktr_wizard'));
					die();
				}
			}
		}
	}
	/**
	* Static function add_steep
	* @access public
	* @param int $step with current step number to add.
	* @param string $title with current step title to add.
	* @param callback $callback_page with current step callback_page to add.
	* @param callback $callback_action with current step callback_action to add.
	* @return true on success or false on failure.
	* @since 0.7
	*/
	public static function add_step($step, $title, $callback_page, $callback_action) {
		$step_offset = $step-1;
		$new_step = array(
				'step' => $step, 
				'title' => $title, 
				'callback_page' => $callback_page, 
				'callback_action' => $callback_action
			);
		$new_step = apply_filters('fktr_new_step_array_data', $new_step);
		if (empty($new_step)) {
			return false;
		}
		if (!isset(self::$steps_data[$step_offset])) {
			self::$steps_data[$step_offset] = $new_step;
		} else {
			remove_action('fktr_wizard_install_step_'.$step, self::$steps_data[$step_offset]['callback_page']);
			if (!empty(self::$steps_data[$step_offset]['callback_action'])) {
				remove_action('fktr_wizard_action_'.$step, self::$steps_data[$step_offset]['callback_action']);
			}
			self::$steps_data[$step_offset] = $new_step;
		}
		add_action('fktr_wizard_install_step_'.$step, self::$steps_data[$step_offset]['callback_page']);
		if (!empty(self::$steps_data[$step_offset]['callback_action'])) {
			add_action('fktr_wizard_action_'.$step, self::$steps_data[$step_offset]['callback_action']);
		}
		return true;
	}
	
	/**
	* Static function get_steps
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function get_steps() {
		$steps = array();
		foreach (self::$steps_data as $step_data) {
			$steps[] = $step_data['title'];
		}
		$steps = apply_filters('fktr_steps_setup_array', $steps);
		return $steps;
	}
	/**
	* Static function default_request_values
	* @access public
	* @return Array of default values on GET requests.
	* @since 0.7
	*/
	public static function default_request_values() {
		$default_values = array(
							'action' => 'fktr_wizard',
							'step' => 1
						);
		return $default_values;
	}
	/**
	* Static function post_actions
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function post_actions() {
		if (!wp_verify_nonce($_POST['_wpnonce'], 'fktr_wizard_nonce' ) ) {
		    wp_die(__( 'Security check', 'fakturo' )); 
		}
		
		if (!empty($_POST['step_action']) && is_numeric($_POST['step_action'])) {
			do_action('fktr_wizard_action_'.$_POST['step_action']);
		} else {
			$output = ''.__('A problem please try again.').'<br/><br/> <a href="'.admin_url('admin-post.php?action=fktr_wizard').'" class="button">'.__('Back.').'</a>';
			wp_die($output); 
		}
		$all_steps = self::get_steps();
		if (count($all_steps) > $_POST['step_action']) {
			//redirect to next steep
			wp_redirect(admin_url('admin-post.php?action=fktr_wizard&step='.($_POST['step_action']+1)));
		} else {
			// redirect to dashboard
			wp_redirect(admin_url('admin.php?page=fakturo_dashboard'));
		}
	}
	/**
	* Static function scripts
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function scripts() {
		wp_enqueue_script( 'jquery-select2', FAKTURO_PLUGIN_URL . 'assets/js/jquery.select2.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
		wp_enqueue_script( 'jquery-mask', FAKTURO_PLUGIN_URL . 'assets/js/jquery.mask.min.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
		wp_enqueue_script( 'jquery-wizard-main', FAKTURO_PLUGIN_URL . 'assets/js/wizard_main.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
		wp_enqueue_script( 'jquery-fktr-new-terms-popup', FAKTURO_PLUGIN_URL . 'assets/js/new-terms-popup.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
		$steps = self::get_steps();
		$porcent_per_steep = ((100-count($steps))/count($steps));
		wp_localize_script('jquery-wizard-main', 'backend_object',
			array('ajax_url' => admin_url( 'admin-ajax.php' ),
				'loading_states_text' => __('Loading countries and states...', 'fakturo' ),
				'porcent_per_steep' => $porcent_per_steep,
				'loading_image' => admin_url('images/spinner.gif'), 
				'loading_states_text' => __('Loading states...', 'fakturo' ),
				'loading_currencies_text' => __('Loading currencies...', 'fakturo' ),
				'ajax_nonce' => wp_create_nonce('fktr_wizard_ajax_nonce'),
				'loading_text' => __('Loading...', 'fakturo' ),
			)
		);

		if (self::$current_request['step'] == 1) {
			wp_enqueue_script( 'jquery-wizard-countries-states', FAKTURO_PLUGIN_URL . 'assets/js/wizard_countries_states.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
		} 

		if (self::$current_request['step'] == 2) {
			wp_enqueue_script('media-upload');
			wp_enqueue_script('thickbox');
			wp_enqueue_script( 'jquery-settings', FAKTURO_PLUGIN_URL . 'assets/js/wizard_company_info.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			
		} 
		if (self::$current_request['step'] == 3) {
			wp_enqueue_script( 'jquery-wizard-currencies', FAKTURO_PLUGIN_URL . 'assets/js/wizard_currencies.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
		} 

		if (self::$current_request['step'] == 4) {
			wp_enqueue_script( 'jquery-wizard-money', FAKTURO_PLUGIN_URL . 'assets/js/wizard_money.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			$currencies = get_fakturo_terms(array(
							'taxonomy' => 'fktr_currencies',
							'hide_empty' => false,
				));
			wp_localize_script('jquery-wizard-money', 'money_object',
				array(
					'currencies' => $currencies ,
				)
			);
			
		} 
		if (self::$current_request['step'] == 5) {
			wp_enqueue_script( 'jquery-wizard-invoices', FAKTURO_PLUGIN_URL . 'assets/js/wizard_invoices_format.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			$invoices_types = get_fakturo_terms(array(
							'taxonomy' => 'fktr_invoice_types',
							'hide_empty' => false,
				));
			$sale_points = get_fakturo_terms(array(
							'taxonomy' => 'fktr_sale_points',
							'hide_empty' => false,
				));
			wp_localize_script('jquery-wizard-invoices', 'invoices_object',
				array(
					'invoices_types' => $invoices_types,
					'sale_points' => $sale_points,
				)
			);
			wp_enqueue_script( 'jquery-wizard-receipts', FAKTURO_PLUGIN_URL . 'assets/js/wizard_receipt_format.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			wp_enqueue_script('jquery-wizard-date-format', FAKTURO_PLUGIN_URL . 'assets/js/wizard_date_format.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			wp_localize_script('jquery-wizard-date-format', 'date_object',
				array(
					'date_one' => date_i18n('d/m/Y', time()),
					'date_two' => date_i18n('m/d/Y', time()),
				)
			);
			
		} 
		

		if (self::$current_request['step'] == 6) {
			wp_enqueue_script('jquery-wizard-products', FAKTURO_PLUGIN_URL . 'assets/js/wizard_products.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			
		}

		if (self::$current_request['step'] == 7) {
			wp_enqueue_script('jquery-wizard-payments', FAKTURO_PLUGIN_URL . 'assets/js/wizard_payments.js', array( 'jquery' ), WPE_FAKTURO_VERSION, true );
			
		} 
		 
		
	}
	/**
	* Static function styles
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function styles() {

		wp_enqueue_style('main',FAKTURO_PLUGIN_URL .'assets/css/main.css');	
		wp_enqueue_style('fktr_icons',FAKTURO_PLUGIN_URL .'assets/css/icons.css');	
		wp_enqueue_style('main-wizard',FAKTURO_PLUGIN_URL .'assets/css/main-wizard.css');	
		
		wp_enqueue_style('style-select2',FAKTURO_PLUGIN_URL .'assets/css/select2.min.css');	
		wp_enqueue_style('fktr-new-terms-popup',FAKTURO_PLUGIN_URL .'assets/css/new-terms-popup.css');	
		if (self::$current_request['step'] == 2) {
			wp_enqueue_style('thickbox');
		}


		
	}
	/**
	* Static function page
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function page() {
		if (current_user_can('fktr_manage_wizard')) {
			update_option('fktr_first_time_wizard', true);
			self::$current_request = wp_parse_args($_GET, self::default_request_values());
			do_action('fktr_wizard_install_step_'.self::$current_request['step']);
		} else {
			wp_die(__('A security problem occurred, Please contact the admin.', 'fakturo'));
		}
	}
	/**
	* Static function get_form
	* @access public
	* @return HTML of form and actions to work.
	* @since 0.7
	*/
	public static function get_form() {
		$ret = '<form method="post" action="'.admin_url('admin-post.php').'">
					'.wp_nonce_field('fktr_wizard_nonce').'
					<input type="hidden" name="action" value="fktr_wizard_post"/>
					<input type="hidden" name="step_action" value="'.self::$current_request['step'].'"/>';
		return $ret;
	}
	/**
	* Static function get_buttons
	* @access public
	* @return HTML of buttons previous next and skip.
	* @since 0.7
	*/
	public static function get_buttons() {
		$steps = self::get_steps();
		$ret = '<div id="buttons_container" class="buttons_container">';
		if ((self::$current_request['step']-1) >= 1) {
			$ret .= '<a href="'.admin_url('admin-post.php?action=fktr_wizard&step='.(self::$current_request['step']-1)).'" class="button button-large">'. __( 'Previous', 'fakturo' ) .'</a>	';
		}

		if (self::$current_request['step'] == count($steps)) {
			$ret .= '<input type="submit" class="button button-large button-orange" style="padding-left:30px; padding-right:30px;" value="'. __( 'Finish', 'fakturo' ) .'"/>';
		} else {
			$ret .= '<input type="submit" class="button button-large button-orange" style="padding-left:30px; padding-right:30px;" value="'. __( 'Next', 'fakturo' ) .'"/>';
		}	

		if ((self::$current_request['step']) < count($steps)) {
			$ret .= '  <a href="'.admin_url('admin-post.php?action=fktr_wizard&step='.(self::$current_request['step']+1)).'" class="button button-large">'. __( 'Skip', 'fakturo' ) .'</a>';
		} else {
			$ret .= '  <a href="'.admin_url('admin.php?page=fakturo_dashboard').'" class="button button-large">'. __( 'Skip and Finish', 'fakturo' ) .'</a>';
		}
						
		$ret .= '</div>';
		return $ret;
	}
	/**
	* Static function page_step_one
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function page_step_one() {
		
		require_once FAKTURO_PLUGIN_DIR . 'includes/libs/country-states.php';
		$html_select_countries = '<select name="selected_country" id="selected_country">';
		foreach ($countries as $kc => $country) {
			$html_select_countries .= '<option value="' . $kc . '">' . esc_html($country[2]) . '</option>';
		}
		$html_select_countries .= '</select>';

		$print_html = '
						'.self::get_form().'
						<div id="header_title"> 
							<h1>'.__('Load Countries and States', 'fakturo').'</h1>
							'.self::get_buttons().'
					   </div>
					
					<div id="content_step">
					<p>'.__('Do you want load all countries and states by default?', 'fakturo').'</p>
					
					<table class="form-table">
						<tr valign="top">
							<th scope="row"><input type="radio" name="load_contries_states" value="yes" checked/></th>
							<td>
								'. __( 'Yes', 'fakturo' ) .'
	                        </td>
	                    </tr>
	                    <tr valign="top">
							<th scope="row"><input type="radio" name="load_contries_states" value="yes_only_a_country"/></th>
							<td>
								'. __( 'Yes, but only some countries.', 'fakturo' ) .'
								<div id="container_select_countries" style="display:none;">
									<table id="selected_countries" style="width:330px; margin-bottom:5px;">

									</table> 
									'.$html_select_countries.'
									<input type="button" id="btn_add_select_country" class="button button-orange" value="'. __( 'Add', 'fakturo' ) .'"/>
								</div>
	                        </td>
	                    </tr>
	                    <tr valign="top">
	                        <th scope="row"><input type="radio" name="load_contries_states" value="no"/></th>
							<td>
								'. __( 'No, you can add them later.', 'fakturo' ) .'
	                        </td>
	                    </tr>
	                </table>
	                </div>
					'.self::get_buttons().'
					</form>
					';
		self::ouput($print_html, __('Load Countries and States', 'fakturo'));
		
	}
	/**
	* Static function load_countries_states_ajax
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function load_countries_states_ajax() {
		check_ajax_referer('fktr_wizard_ajax_nonce', 'nonce');
		$country_id = 0;
		if (!empty($_REQUEST['country_id']) && is_numeric($_REQUEST['country_id'])) {
			$country_id = $_REQUEST['country_id'];
		}
		if (empty($country_id)) {
			wp_die('error');
		}
		$state_index = 0;
		if (isset($_REQUEST['state_index']) && is_numeric($_REQUEST['state_index'])) {
			$state_index = $_REQUEST['state_index'];
		}

		require_once FAKTURO_PLUGIN_DIR . 'includes/libs/country-states.php';
		$count_insert = 0;
		foreach ($countries as $kc => $country) {
			if ($country[0] != $country_id) {
				continue; 
			}
			$count_insert++;
			$termc = term_exists($country[2], 'fktr_countries');
			if ($termc !== 0 && $termc !== null) {
				// exist this term
			} else {
				$termc = fktr_insert_country_states($country[2], 'fktr_countries');
				if (is_wp_error($termc)){
										
				}
				
				// don't exist term
			}
			$country_term = $termc['term_id'];
			$total_count_states = 0;
			foreach ($states as $ks => $state) {	
				if ($state[2] == $country[0]) {
					$total_count_states++;
				}
			}

			
			$count_states = 0;
			foreach ($states as $ks => $state) {	
				if ($state[2] == $country[0]) {
					$count_states++;
					if ($count_states > $state_index) {
						$term_s = term_exists($state[1], 'fktr_countries', $country_term);
						if ($term_s !== 0 && $term_s !== null) {
									// exist state
						} else {
							
							$term_s = fktr_insert_country_states($state[1], 'fktr_countries', array('parent' => $country_term));
							if (is_wp_error($term_s)){
								$term_s = fktr_insert_country_states($state[1], 'fktr_countries', array('parent' => $country_term));
							} else {
								$state_term = $term_s['term_id'];
							}
								
						}
						break;
					}
				}
			}
				
			
			echo $total_count_states;
			die();
		}
	}
	/**
	* Static function load_countries_ajax
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function load_countries_ajax() {
		check_ajax_referer('fktr_wizard_ajax_nonce', 'nonce');
		$r_countries = array();
		if (!empty($_REQUEST['countries'])) {
			$r_countries = $_REQUEST['countries'];
		}
		if (!is_array($r_countries)) {
			$r_countries = array();
		}
		if (empty($r_countries)) {
			wp_die('error');
		}
		ignore_user_abort(true);
		set_time_limit(0);
		$country_id = 999;
		$start_time = microtime(true);
		require_once FAKTURO_PLUGIN_DIR . 'includes/libs/country-states.php';
		$count_insert = 0;
		foreach ($r_countries as $kc => $key_country) {
			
			if (isset($countries[(int)$key_country]) || array_key_exists($key_country, $countries)) {
				$country = $countries[(int)$key_country];
			} else {
				continue;
			}


			$country_id = $country[0];
			$count_insert++;
			$termc = term_exists($country[2], 'fktr_countries');
			if ($termc !== 0 && $termc !== null) {
				// exist this term
				continue;
			} else {
				// don't exist term
				$termc = fktr_insert_country_states($country[2], 'fktr_countries');
				if (is_wp_error($termc)){
										
				}

				
				$country_term = $termc['term_id'];
				foreach ($states as $ks => $state) {
						
					if ($state[2] == $country[0]) {
						$count_insert++;
						if ($count_insert >= 100 ) {
							$count_insert = 0;
							wp_cache_flush();
						}
						$term_s = null;//term_exists($state[1], 'fktr_countries', $country_term);
						if ($term_s !== 0 && $term_s !== null) {
								// exist state
						} else {

							$term_s = fktr_insert_country_states($state[1], 'fktr_countries', array('parent' => $country_term));

							if (is_wp_error($term_s)){
							}
							$state_term = $term_s['term_id'];
						}
					}	
				}
			}
		}
		$end_time = microtime(true);
		if ($country_id == count($countries)) {
			die('last_country');
		} else {
			die('not_last_country');
		}
	}
	/**
	* Static function action_one
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function action_one() {
		$load_contries_states = 'no';
		if (!empty($_POST['load_contries_states'])) {
			$load_contries_states = $_POST['load_contries_states'];
		}
		/* Delete all countries.
		DELETE FROM
		wp_terms
		WHERE term_id IN
		( SELECT * FROM (
		    SELECT wp_terms.term_id
		    FROM wp_terms
		    JOIN wp_term_taxonomy
		    ON wp_term_taxonomy.term_id = wp_terms.term_id
		    WHERE taxonomy = 'fktr_countries'
		    AND count = 0
		) as T
		);
		*/
		
		
	}
	/**
	* Static function page_step_two
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function page_step_two() {

		$options = get_option('fakturo_info_options_group');
		if (empty($options['url'])) {
			$options['url'] = FAKTURO_PLUGIN_URL . 'assets/images/etruel-logo.png';
		}
		update_option('fakturo_info_options_group', $options);
		$selectCountry = wp_dropdown_categories( array(
			'show_option_all'    => '',
			'show_option_none'   => __('Choose a country', 'fakturo' ),
			'orderby'            => 'name', 
			'order'              => 'ASC',
			'show_count'         => 0,
			'hide_empty'         => 0, 
			'child_of'           => 0,
			'exclude'            => '',
			'echo'               => 0,
			'selected'           => $options['country'],
			'hierarchical'       => 1, 
			'name'               => 'fakturo_info_options_group[country]',
			'id' 				 => 'fakturo_info_options_group_country',
			'class'              => 'form-no-clear',
			'depth'              => 1,
			'tab_index'          => 0,
			'taxonomy'           => 'fktr_countries',
			'hide_if_empty'      => false
		));
		
		$selectState = wp_dropdown_categories( array(
			'show_option_all'    => '',
			'show_option_none'   => __('Choose a state', 'fakturo' ),
			'orderby'            => 'name', 
			'order'              => 'ASC',
			'show_count'         => 0,
			'hide_empty'         => 0, 
			'child_of'           => $options['country'],
			'exclude'            => '',
			'echo'               => 0,
			'selected'           => $options['state'],
			'hierarchical'       => 1, 
			'name'               => 'fakturo_info_options_group[state]',
			'id' 				 => 'fakturo_info_options_group_state',
			'class'              => 'form-no-clear',
			'depth'              => 1,
			'tab_index'          => 0,
			'taxonomy'           => 'fktr_countries',
			'hide_if_empty'      => false
		));

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
			'selected'           => $options['tax_condition'],
			'hierarchical'       => 1, 
			'name'               => 'fakturo_info_options_group[tax_condition]',
			'class'              => '',
			'id'				 => 'fakturo_info_options_group_tax_condition',
			'depth'              => 1,
			'tab_index'          => 0,
			'taxonomy'           => 'fktr_tax_conditions',
			'hide_if_empty'      => false
		));
		$print_html = '
						'.self::get_form().'
						<div id="header_title"> 
							<h1>'.__('Company Info', 'fakturo').'</h1>
							'.self::get_buttons().'
					    </div>
					
					<table class="form-table">
						<tr valign="top">
							<th scope="row">'. __( 'Name', 'fakturo' ) .'</th>
							<td>
								<input type="text" size="36" name="fakturo_info_options_group[name]" value="'.$options['name'].'"/>
	                        </td>
	                    </tr>
						<tr valign="top">
							<th scope="row">'. __( 'Taxpayer ID', 'fakturo' ) .'</th>
							<td>
								<input type="text" size="36" id="fakturo_info_options_group_taxpayer" name="fakturo_info_options_group[taxpayer]" value="'.$options['taxpayer'].'"/>
	                        </td>
	                    </tr>
						<tr valign="top">
							<th scope="row">'. __( 'Gross income tax ID', 'fakturo' ) .'</th>
							<td>
								<input type="text" size="36" name="fakturo_info_options_group[tax]" value="'.$options['tax'].'"/>
	                        </td>
	                    </tr>
						<tr valign="top">
							<th scope="row">'. __( 'Start of activities', 'fakturo' ) .'</th>
							<td>
								<input type="text" size="36" name="fakturo_info_options_group[start]" id="start" value="'.$options['start'].'"/>
	                        </td>
	                    </tr>
						<tr valign="top">
							<th scope="row">'. __( 'Address', 'fakturo' ) .'</th>
							<td>
								<textarea name="fakturo_info_options_group[address]" cols="36" rows="4">'.$options['address'].'</textarea>
							</td>
	                    </tr>
						<tr valign="top">
							<th scope="row">'. __( 'Telephone', 'fakturo' ) .'</th>
							<td>
								<input type="text" size="36" name="fakturo_info_options_group[telephone]" value="'.$options['telephone'].'"/>
							</td>
	                    </tr>
						
						<tr valign="top">
							<th scope="row">'. __( 'Country', 'fakturo' ) .'</th>
							<td>
								'.$selectCountry.' '.fktr_popup_taxonomy::button(
																	array(
																		'taxonomy' => 'fktr_countries',
																		'echo' => 0,
																		'class' => 'button',
																		'selector' => '#fakturo_info_options_group_country',
																	)
																).'
							</td>
	                    </tr>
	                    <tr valign="top">
							<th scope="row">'. __( 'State', 'fakturo' ) .'</th>
							<td>
								<table style="border-spacing: 0px;">
									<tr>
										<td id="td_select_state">
											'.$selectState.'  
										</td>
										<td>
											 '.fktr_popup_taxonomy::button(
																array(
																	'taxonomy' => 'fktr_countries',
																	'echo' => 0,
																	'class' => 'button',
																	'opcional_add_new_item'	=> __( 'Add New State', 'fakturo' ),
																	'selector_parent_select' => '#fakturo_info_options_group_country',
																	'selector' => '#fakturo_info_options_group_state',
																)
															).'
									    </td>
									</tr>
								</table>
							</td>
	                    </tr>

	                    <tr valign="top">
							<th scope="row">'. __( 'City', 'fakturo' ) .'</th>
							<td>
								<input type="text" size="36" name="fakturo_info_options_group[city]" value="'.$options['city'].'"/>
							</td>
	                    </tr>
	                    <tr valign="top">
							<th scope="row">'. __( 'Postcode', 'fakturo' ) .'</th>
							<td>
								<input type="text" size="36" name="fakturo_info_options_group[postcode]" value="'.$options['postcode'].'"/>
							</td>
	                    </tr>
						<tr valign="top">
							<th scope="row">'. __( 'Website', 'fakturo' ) .'</th>
							<td>
								<input type="text" size="36" name="fakturo_info_options_group[website]" value="'.$options['website'].'"/>
							</td>
	                    </tr>
	                    <tr valign="top">
							<th scope="row">'. __( 'Tax condition', 'fakturo' ) .'</th>
							<td>
								'.$selectTaxCondition.' '.fktr_popup_taxonomy::button(
																	array(
																		'taxonomy' => 'fktr_tax_conditions',
																		'echo' => 0,
																		'class' => 'button',
																		'selector' => '#fakturo_info_options_group_tax_condition',
																	)
																).'
							</td>
	                    </tr>
						<tr valign="top">
							<th scope="row">'. __( 'Company Logo', 'fakturo' ) .'</th>
							<td>
								<label for="upload_image">
									<input id="url" type="text" size="36" value="'.$options['url'].'" name="fakturo_info_options_group[url]" />
									<input id="upload_logo_button" class="button button-orange" type="button" value="Upload Image" />
									<br />'.__( 'Enter an URL or upload an image for the company logo.', 'fakturo' ).'
								</label>
								
								<p style="padding-top: 5px;">'. __( 'This is your current logo', 'fakturo' ) .'</p><img id="setting_img_log" src="'. $options['url'] .'" style="padding:5px;" />
							</td>
	                    </tr>
					</table>
					'.self::get_buttons().'
					</form>
					';
		self::ouput($print_html, __('Company Info', 'fakturo'));
		
	}
	/**
	* Static function action_two
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function action_two() {
		update_option('fakturo_info_options_group', $_POST['fakturo_info_options_group']);
	}
	/**
	* Static function page_step_three
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function page_step_three() {
		require_once FAKTURO_PLUGIN_DIR . 'includes/libs/currencies.php';
		$html_select_currencies = '<select name="selected_currency" id="selected_currency">';
		foreach ($currencies as $kc => $currency) {
			$html_select_currencies .= '<option value="' . $kc . '">' . esc_html($currency['name']) . '</option>';
		}
		$html_select_currencies .= '</select>';
		$print_html = '
					'.self::get_form().'
						<div id="header_title"> 
							<h1>'.__('Load Currencies', 'fakturo').'</h1>
							'.self::get_buttons().'
					   </div>
					<p>Do you want load all currencies by default?</p>
					<table class="form-table">
						<tr valign="top">
							<th scope="row"><input type="radio" name="load_currencies" value="yes" checked/></th>
							<td>
								'. __( 'Yes', 'fakturo' ) .'
	                        </td>
	                    </tr>
	                    <tr valign="top">
							<th scope="row"><input type="radio" name="load_currencies" value="yes_only_a_currency"/></th>
							<td>
								'. __( 'Yes, but only some currencies.', 'fakturo' ) .'
								<div id="container_select_currency" style="display:none;">
									<table id="selected_currencies" style="width:330px; margin-bottom:5px;">

									</table> 
									'.$html_select_currencies.'
									<input type="button" id="btn_add_select_currency" class="button button-orange" value="'. __( 'Add', 'fakturo' ) .'"/> 
								</div>
	                        </td>
	                    </tr>
	                    <tr valign="top">
	                        <th scope="row"><input type="radio" name="load_currencies" value="no"/></th>
							<td>
								'. __( 'No, you can add them later.', 'fakturo' ) .'
	                        </td>
	                    </tr>
	                </table>
					'.self::get_buttons().'
					</form>
					';
		self::ouput($print_html, __('Load Currencies', 'fakturo'));
	
	}
	/**
	* Static function load_countries_ajax
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function load_currencies_ajax() {
		check_ajax_referer('fktr_wizard_ajax_nonce', 'nonce');
		$currency_id = 0;
		if (isset($_REQUEST['currency_id']) && is_numeric($_REQUEST['currency_id'])) {
			$currency_id = $_REQUEST['currency_id'];
		}
		
		require_once FAKTURO_PLUGIN_DIR . 'includes/libs/currencies.php';
		$count_insert = 0;
		

		foreach ($currencies as $kc => $currency) {
			if ($kc != $currency_id) {
				continue; 
			}
			$count_insert++;
			$termc = term_exists($currency['name'], 'fktr_currencies');
			if ($termc !== 0 && $termc !== null) {
				// exist this term
			} else {
				// don't exist term
				$termc = wp_insert_term($currency['name'], 'fktr_currencies');
				if (is_wp_error($termc)){
										
				}
				$newcurrency = get_fakturo_term($termc['term_id'], 'fktr_currencies');
				$term_taxonomy_id = $newcurrency->term_taxonomy_id;
				$newcurrency->plural = $currency['name_plural'];
				$newcurrency->symbol = $currency['symbol'];
				$newcurrency->rate = 1;
				$newcurrency->reference = '';
				
				unset($newcurrency->term_id);
				unset($newcurrency->name);
				unset($newcurrency->slug);
				unset($newcurrency->term_group);
				unset($newcurrency->term_taxonomy_id);
				unset($newcurrency->taxonomy);
				unset($newcurrency->parent);
				unset($newcurrency->count);
				set_fakturo_term($termc['term_id'], $term_taxonomy_id,  (array)$newcurrency);
				
			}
			break;
		}
		if ($kc == (count($currencies)-1)) {
			die('last_currency');
		} else {
			die('not_last_currency');
		}
	}
	/**
	* Static function page_step_four
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function page_step_four() {
		$options = get_option('fakturo_system_options_group');
		if (empty($options['decimal_numbers'])) {
			$options['decimal_numbers'] = 2;
		}
		if (empty($options['thousand'])) {
			$options['thousand'] = ',';
		}
		if (empty($options['decimal'])) {
			$options['decimal'] = '.';
		}
		$selectCurrency = wp_dropdown_categories( array(
										'show_option_all'    => '',
										'show_option_none'   => __('Choose a Currency', 'fakturo' ),
										'orderby'            => 'name', 
										'order'              => 'ASC',
										'show_count'         => 0,
										'hide_empty'         => 0, 
										'child_of'           => 0,
										'exclude'            => '',
										'echo'               => 0,
										'selected'           => $options['currency'],
										'hierarchical'       => 1, 
										'name'               => 'fakturo_system_options_group[currency]',
										'id'                 => 'fakturo_system_options_group_currency',
										'class'              => 'form-no-clear',
										'depth'              => 1,
										'tab_index'          => 0,
										'taxonomy'           => 'fktr_currencies',
										'hide_if_empty'      => false
									));
		$print_html = '
						'.self::get_form().'
						<div id="header_title"> 
							<h1>'.__('Money Format', 'fakturo').'</h1>
							'.self::get_buttons().'
					    </div>
					<table class="form-table">
						<tr>
							<td>
								<table class="form-table">
									<tr>
										<td>'. __( 'Default Currency', 'fakturo' ) .'</td>
										<td>
											'.$selectCurrency.'	 '.fktr_popup_taxonomy::button(
																	array(
																		'taxonomy' => 'fktr_currencies',
																		'echo' => 0,
																		'class' => 'button',
																		'selector' => '#fakturo_system_options_group_currency',
																	)
																).'
										</td>
								 	</tr>
									<tr>
										<td>'. __( 'Currency Position', 'fakturo' ) .'</td>
										<td>
											<select id="fakturo_system_options_group_currency_position" name="fakturo_system_options_group[currency_position]">
												<option value="before"'.selected('before', $options['currency_position'], false).'>Before - $10</option>
												<option value="after"'.selected('after', $options['currency_position'], false).'>After - 10$</option>
											</select>
											
										</td>
										
									  </tr>
									  
									  <tr>
											<td>'. __( 'Thousands Separator', 'fakturo' ) .'</td>
											<td>
												<input id="fakturo_system_options_group_thousand" name="fakturo_system_options_group[thousand]" type="text" size="5" value="'.$options['thousand'].'">
												
									
											</td>
										
									  </tr>
									  
									  <tr>
											<td>'. __( 'Decimal Separator', 'fakturo' ) .'</td>
											<td>
												<input id="fakturo_system_options_group_decimal" name="fakturo_system_options_group[decimal]" type="text" size="5" value="'.$options['decimal'].'">
												
									
											</td>
										
									  </tr>
									  
									  <tr>
											<td>'. __( 'Decimal numbers', 'fakturo' ) .'</td>
											<td>
												<input id="fakturo_system_options_group_decimal_numbers" type="number" min="0" max="9" maxlength="1" name="fakturo_system_options_group[decimal_numbers]" value="'.$options['decimal_numbers'].'">
												
											</td>
										
									  </tr>
				                </table>
							</td>
							<td id="money_format_test" style="text-align: center; width: 40%; font-size: 34px;">
								9,999,999.99 $
							</td>
					 	</tr>

					</table>	
					
					'.self::get_buttons().'
					</form>
					';
		self::ouput($print_html, __('Money Format', 'fakturo'));
	
	}
	/**
	* Static function action_four
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function action_four() {
		$new_options = get_option('fakturo_system_options_group', array());
		$new_options['currency'] = $_POST['fakturo_system_options_group']['currency'];
		$new_options['currency_position'] = $_POST['fakturo_system_options_group']['currency_position'];
		$new_options['thousand'] = $_POST['fakturo_system_options_group']['thousand'];
		$new_options['decimal'] = $_POST['fakturo_system_options_group']['decimal'];
		$new_options['decimal_numbers'] = $_POST['fakturo_system_options_group']['decimal_numbers'];
		update_option('fakturo_system_options_group', $new_options);
	}
	/**
	* Static function invoice_format
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function page_invoice_format() {
		$options = get_option('fakturo_system_options_group');
		$selectInvoiceType = wp_dropdown_categories( array(
										'show_option_all'    => '',
										'show_option_none'   => __('Choose a Invoice Type', 'fakturo' ),
										'orderby'            => 'name', 
										'order'              => 'ASC',
										'show_count'         => 0,
										'hide_empty'         => 0, 
										'child_of'           => 0,
										'exclude'            => '',
										'echo'               => 0,
										'selected'           => $options['invoice_type'],
										'hierarchical'       => 1, 
										'name'               => 'fakturo_system_options_group[invoice_type]',
										'id'            	 => 'fakturo_system_options_group_invoice_type',
										'class'              => 'form-no-clear',
										'depth'              => 1,
										'tab_index'          => 0,
										'taxonomy'           => 'fktr_invoice_types',
										'hide_if_empty'      => false
									));
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
										'selected'           => $options['sale_point'],
										'hierarchical'       => 1, 
										'name'               => 'fakturo_system_options_group[sale_point]',
										'id'                 => 'fakturo_system_options_group_sale_point',
										'class'              => 'form-no-clear',
										'depth'              => 1,
										'tab_index'          => 0,
										'taxonomy'           => 'fktr_sale_points',
										'hide_if_empty'      => false
									));

		$selectListInvoiceNumber = array();
		$selectListInvoiceNumber['sale_point'] = __( 'Sale point', 'fakturo' );
		$selectListInvoiceNumber['invoice_number'] = __('Invoice number', 'fakturo' );
		$selectListInvoiceNumber['invoice_type_name'] = __('Invoice Type name', 'fakturo' );
		$selectListInvoiceNumber['invoice_type_short_name'] = __('Invoice Type Short-name', 'fakturo' );
		$selectListInvoiceNumber['invoice_type_symbol'] = __('Invoice Type symbol', 'fakturo' );
		
		$selectListInvoiceNumber = apply_filters('fktr_list_invoice_number_array', $selectListInvoiceNumber);
		
		//echo print_r($options['search_code'], true);
		//
		$echoSelectListInvoiceNumber = '<select id="fakturo_system_options_group_list_invoice_number" name="fakturo_system_options_group[list_invoice_number][]" multiple="multiple" style="width:400px;">';
		foreach ($options['list_invoice_number'] as $k => $key) {
			if (isset($selectListInvoiceNumber[$key])) {
				$txt = $selectListInvoiceNumber[$key];
				$echoSelectListInvoiceNumber .= '<option value="'.$key.'"'.selected($key, (array_search($key, $options['list_invoice_number'])!==false) ? $key : '' , false).'>'.$txt.'</option>';
				unset($selectListInvoiceNumber[$key]);
			}
			
		}

		foreach ($selectListInvoiceNumber as $key => $txt) {
			$echoSelectListInvoiceNumber .= '<option value="'.$key.'"'.selected($key, (array_search($key, $options['list_invoice_number'])!==false) ? $key : '' , false).'>'.$txt.'</option>';
		}
		$echoSelectListInvoiceNumber .= '</select>';	

		if (empty($options['digits_receipt_number'])) {
			$options['digits_receipt_number'] = 8;
		}
		if (empty($options['dateformat'])) {
			$options['dateformat'] = 'd/m/Y';
		}
		$selectDefaultDate = array();
		$selectDefaultDate['d/m/Y'] = __( 'dd/mm/YYYY', 'fakturo' );
		$selectDefaultDate['m/d/Y'] = __( 'mm/dd/YYYY', 'fakturo' );						
		$selectDefaultDate = apply_filters('fktr_default_format_date_array', $selectDefaultDate);
		
		$echoSelectDefaultDate = '<select id="fakturo_system_options_group_dateformat" name="fakturo_system_options_group[dateformat]">';
		foreach ($selectDefaultDate as $key => $txt) {
			$echoSelectDefaultDate .= '<option value="'.$key.'"'.selected($key, $options['dateformat'], false).'>'.$txt.'</option>';
		}
		$echoSelectDefaultDate .= '</select>';		

		$print_html = '
						'.self::get_form().'
						<div id="header_title"> 
							<h1>'.__('Invoice Details and Formats', 'fakturo').'</h1>
							'.self::get_buttons().'
					    </div>
				<table class="form-table">
				<tr>
				<td>
					<table class="form-table">
						<tr>
							<td>'. __( 'Default Invoice Type', 'fakturo' ) .'<br/><br/> </td>
							<td class="italic-label">
								  '.$selectInvoiceType.' '.fktr_popup_taxonomy::button(
																	array(
																		'taxonomy' => 'fktr_invoice_types',
																		'echo' => 0,
																		'class' => 'button',
																		'selector' => '#fakturo_system_options_group_invoice_type',
																	)
															).'	
								  <p class="description">
								  	'. __( 'Choose the default Invoice Type used in the system', 'fakturo' ) .' 
							      </p>
							</td>
						
					  </tr>
					  <tr>
						<td>'. __( 'Sale Point', 'fakturo' ) .'</td>
						<td class="italic-label">
								  '.$selectSalePoint.' '.fktr_popup_taxonomy::button(
																	array(
																		'taxonomy' => 'fktr_sale_points',
																		'echo' => 0,
																		'class' => 'button',
																		'selector' => '#fakturo_system_options_group_sale_point',
																	)
															).'
								  <p class="description">
								  	'. __( 'Choose your sale point.', 'fakturo' ) .' 
							      </p>
						</td>
					  </tr>
					 
					   <tr>
							<td style="width: 200px;">'. __( 'Number of digits of the invoice number', 'fakturo' ) .'</td>
							<td class="italic-label">
								<input id="fakturo_system_options_group_digits_invoice_number" name="fakturo_system_options_group[digits_invoice_number]" type="number" maxlength="2" min=2 max=20 value="'.$options['digits_invoice_number'].'">
								<p class="description">
									'. __( 'Choose the default number of digits of the invoice number.', 'fakturo' ) .'           
								</p>
					
							</td>
						
					  </tr>
					  <tr>
							<td style="width: 200px;">'. __( 'Format invoice numbers in lists and reports', 'fakturo' ) .'</td>
							<td class="italic-label">
									'.$echoSelectListInvoiceNumber.'
									<p class="description">
										'. __( '', 'fakturo' ) .'             
									</p>
							</td>
						
					  </tr>
					   <tr>
							<td style="width: 200px;">'. __( 'Individual numeration by Invoice Type', 'fakturo' ) .'</td>
							<td class="italic-label">
								<input id="fakturo_system_options_group_individual_numeration_by_invoice_type" class="slidercheck" type="checkbox" name="fakturo_system_options_group[individual_numeration_by_invoice_type]" value="1" '.(($options['individual_numeration_by_invoice_type'])?'checked="checked"':'').'>
								<label for="fakturo_system_options_group_individual_numeration_by_invoice_type"><span class="ui"></span>  </label>
								<p class="description">'
									. __( 'Activate for use individual numeration by Invoice Type', 'fakturo' ).'
								</p>
							
							</td>
						
					  </tr>
					   <tr>
							<td style="width: 200px;">'. __( 'Individual numeration by Sale Point', 'fakturo' ) .'</td>
							<td class="italic-label">
								<input id="fakturo_system_options_group_individual_numeration_by_sale_point" class="slidercheck" type="checkbox" name="fakturo_system_options_group[individual_numeration_by_sale_point]" value="1" '.(($options['individual_numeration_by_sale_point'])?'checked="checked"':'').'>
								<label for="fakturo_system_options_group_individual_numeration_by_sale_point"><span class="ui"></span>	</label>
								<p class="description">'
									. __( 'Activate for use individual numeration by Sale Point', 'fakturo' ).'
								</p>
							</td>
						
					  </tr>
					</table>
				</td>
				<td style="text-align: center; width: 40%;">
					Example the current format to invoice numbers
					<div id="invoice_format_test" style="text-align: center;  font-size: 34px;"> 
						0002 00123456
					</div>
					
				</td>
				</tr>

				</table>
				<hr/>
				<table class="form-table" style="width:100%;">
						<tr>
							<td>
								<table class="form-table">
									<tr>
										<td style="width: 200px;">'. __( 'Number of digits of the receipt number', 'fakturo' ) .'<br/><br/></td>
										<td class="italic-label">
											<input id="fakturo_system_options_group_digits_receipt_number" name="fakturo_system_options_group[digits_receipt_number]" type="number" maxlength="2" min=2 max=20 value="'.$options['digits_receipt_number'].'">
											 <p class="description">
												'. __( 'Choose the default number of digits of the receipt number.', 'fakturo' ) .'           
											</p>
								
										</td>
								  </tr>
								</table>
							</td>
							<td style="text-align: center; width: 40%;">
								Example the current format to receipt numbers
								<div id="receipt_format_test" style="text-align: center;  font-size: 34px;"> 
									00000
								</div>
								
							</td>
						</tr>
					</table>
					<hr/>
					<table class="form-table" style="width:100%;">
						<tr>
							<td>
								<table class="form-table">
									<tr>
										<td style="width: 200px;">'. __( 'Default date format', 'fakturo' ) .'<br/></td>
										<td class="italic-label">
												'.$echoSelectDefaultDate.'
												<p class="description">
													'. __( '', 'fakturo' ) .'             
												</p>
										</td>
											
								 	</tr>

								</table>	
							</td>
							<td style="text-align: center; width: 40%;">
								Example the current date format
								<div id="date_format_test" style="text-align: center;  font-size: 34px;"> 
									12/31/1992
								</div>
								
							</td>
						</tr>
					</table>	
					<br/><br/>

					'.self::get_buttons().'
					</form>
					';
		self::ouput($print_html, __('Invoices', 'fakturo'));
	
	}
	/**
	* Static function action_invoice_format
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function action_invoice_format() {
		$new_options = get_option('fakturo_system_options_group', array());
		$new_options['invoice_type'] = $_POST['fakturo_system_options_group']['invoice_type'];
		$new_options['sale_point'] = $_POST['fakturo_system_options_group']['sale_point'];
		$new_options['digits_invoice_number'] = $_POST['fakturo_system_options_group']['digits_invoice_number'];
		
		if (!isset($_POST['fakturo_system_options_group']['list_invoice_number'])){
			$_POST['fakturo_system_options_group']['list_invoice_number'] = array();
		}
		$new_options['list_invoice_number'] = $_POST['fakturo_system_options_group']['list_invoice_number'];
		if (!isset($_POST['fakturo_system_options_group']['individual_numeration_by_invoice_type'])){
			$_POST['fakturo_system_options_group']['individual_numeration_by_invoice_type'] = 0;
		}
		if (!isset($_POST['fakturo_system_options_group']['individual_numeration_by_sale_point'])){
			$_POST['fakturo_system_options_group']['individual_numeration_by_sale_point'] = 0;
		}
		$new_options['individual_numeration_by_invoice_type'] = $_POST['fakturo_system_options_group']['individual_numeration_by_invoice_type'];
		$new_options['individual_numeration_by_sale_point'] = $_POST['fakturo_system_options_group']['individual_numeration_by_sale_point'];
		$new_options['digits_receipt_number'] = $_POST['fakturo_system_options_group']['digits_receipt_number'];
		$new_options['dateformat'] = $_POST['fakturo_system_options_group']['dateformat'];
		update_option('fakturo_system_options_group', $new_options);
	}
	
	
	/**
	* Static function page_products
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function page_products() {
		$options = get_option('fakturo_system_options_group');
		$selectCategories = wp_dropdown_categories( array(
										'show_option_all'    => '',
										'show_option_none'   => __('Show categories', 'fakturo' ),
										'orderby'            => 'name', 
										'order'              => 'ASC',
										'show_count'         => 0,
										'hide_empty'         => 0, 
										'child_of'           => 0,
										'exclude'            => '',
										'echo'               => 0,
										'selected'           => 0,
										'hierarchical'       => 1, 
										'name'               => 'fakturo_system_options_group[categories]',
										'id'            	 => 'fakturo_system_options_group_categories',
										'class'              => 'form-no-clear',
										'depth'              => 1,
										'tab_index'          => 0,
										'taxonomy'           => 'fktr_category',
										'hide_if_empty'      => false
									));
		$selectModels = wp_dropdown_categories( array(
										'show_option_all'    => '',
										'show_option_none'   => __('Show models', 'fakturo' ),
										'orderby'            => 'name', 
										'order'              => 'ASC',
										'show_count'         => 0,
										'hide_empty'         => 0, 
										'child_of'           => 0,
										'exclude'            => '',
										'echo'               => 0,
										'selected'           => 0,
										'hierarchical'       => 1, 
										'name'               => 'fakturo_system_options_group[models]',
										'id'            	 => 'fakturo_system_options_group_models',
										'class'              => 'form-no-clear',
										'depth'              => 1,
										'tab_index'          => 0,
										'taxonomy'           => 'fktr_model',
										'hide_if_empty'      => false
									));

		$selectProductTypes = wp_dropdown_categories( array(
										'show_option_all'    => '',
										'show_option_none'   => __('Show product types', 'fakturo' ),
										'orderby'            => 'name', 
										'order'              => 'ASC',
										'show_count'         => 0,
										'hide_empty'         => 0, 
										'child_of'           => 0,
										'exclude'            => '',
										'echo'               => 0,
										'selected'           => 0,
										'hierarchical'       => 1, 
										'name'               => 'fakturo_system_options_group[product_type]',
										'id'            	 => 'fakturo_system_options_group_product_type',
										'class'              => 'form-no-clear',
										'depth'              => 1,
										'tab_index'          => 0,
										'taxonomy'           => 'fktr_product_type',
										'hide_if_empty'      => false
									));

		
		$selectPackagings = wp_dropdown_categories( array(
										'show_option_all'    => '',
										'show_option_none'   => __('Show Packagings', 'fakturo' ),
										'orderby'            => 'name', 
										'order'              => 'ASC',
										'show_count'         => 0,
										'hide_empty'         => 0, 
										'child_of'           => 0,
										'exclude'            => '',
										'echo'               => 0,
										'selected'           => 0,
										'hierarchical'       => 1, 
										'name'               => 'fakturo_system_options_group[packaging]',
										'id'            	 => 'fakturo_system_options_group_packaging',
										'class'              => 'form-no-clear',
										'depth'              => 1,
										'tab_index'          => 0,
										'taxonomy'           => 'fktr_packaging',
										'hide_if_empty'      => false
									));
		$selectOrigins = wp_dropdown_categories( array(
										'show_option_all'    => '',
										'show_option_none'   => __('Show Origins', 'fakturo' ),
										'orderby'            => 'name', 
										'order'              => 'ASC',
										'show_count'         => 0,
										'hide_empty'         => 0, 
										'child_of'           => 0,
										'exclude'            => '',
										'echo'               => 0,
										'selected'           => 0,
										'hierarchical'       => 1, 
										'name'               => 'fakturo_system_options_group[origins]',
										'id'            	 => 'fakturo_system_options_group_origins',
										'class'              => 'form-no-clear',
										'depth'              => 1,
										'tab_index'          => 0,
										'taxonomy'           => 'fktr_origins',
										'hide_if_empty'      => false
									));

		$selectPriceScales = wp_dropdown_categories( array(
										'show_option_all'    => '',
										'show_option_none'   => __('Show price scales', 'fakturo' ),
										'orderby'            => 'name', 
										'order'              => 'ASC',
										'show_count'         => 0,
										'hide_empty'         => 0, 
										'child_of'           => 0,
										'exclude'            => '',
										'echo'               => 0,
										'selected'           => 0,
										'hierarchical'       => 1, 
										'name'               => 'fakturo_system_options_group[price_scales]',
										'id'            	 => 'fakturo_system_options_group_price_scales',
										'class'              => 'form-no-clear',
										'depth'              => 1,
										'tab_index'          => 0,
										'taxonomy'           => 'fktr_price_scales',
										'hide_if_empty'      => false
									));

		$selectTax = wp_dropdown_categories( array(
										'show_option_all'    => '',
										'show_option_none'   => __('Show taxes', 'fakturo' ),
										'orderby'            => 'name', 
										'order'              => 'ASC',
										'show_count'         => 0,
										'hide_empty'         => 0, 
										'child_of'           => 0,
										'exclude'            => '',
										'echo'               => 0,
										'selected'           => 0,
										'hierarchical'       => 1, 
										'name'               => 'fakturo_system_options_group[tax]',
										'id'                 => 'fakturo_system_options_group_tax',
										'class'              => 'form-no-clear',
										'depth'              => 1,
										'tab_index'          => 0,
										'taxonomy'           => 'fktr_tax',
										'hide_if_empty'      => false
									));

		$selectLocations = wp_dropdown_categories( array(
										'show_option_all'    => '',
										'show_option_none'   => __('Show locations', 'fakturo' ),
										'orderby'            => 'name', 
										'order'              => 'ASC',
										'show_count'         => 0,
										'hide_empty'         => 0, 
										'child_of'           => 0,
										'exclude'            => '',
										'echo'               => 0,
										'selected'           => 0,
										'hierarchical'       => 1, 
										'name'               => 'fakturo_system_options_group[locations]',
										'id'               => 'fakturo_system_options_group_locations',
										'class'              => 'form-no-clear',
										'depth'              => 1,
										'tab_index'          => 0,
										'taxonomy'           => 'fktr_locations',
										'hide_if_empty'      => false
									));
		$selectSearchCode = array();
		$selectSearchCode['reference'] = __( 'Reference', 'fakturo' );
		$selectSearchCode['internal_code'] = __( 'Internal code', 'fakturo' );
		$selectSearchCode['manufacturers_code'] = __( 'Manufacturers code', 'fakturo' );							
		$selectSearchCode = apply_filters('fktr_search_code_array', $selectSearchCode);
		

		$echoSelectSearchCode = '<select id="fakturo_system_options_group_search_code" name="fakturo_system_options_group[search_code][]" multiple="multiple" >';
		foreach ($selectSearchCode as $key => $txt) {
			$echoSelectSearchCode .= '<option value="'.$key.'"'.selected($key, (array_search($key, $options['search_code'])!==false) ? $key : '' , false).'>'.$txt.'</option>';
		}
		$echoSelectSearchCode .= '</select>';	


		$selectDefaultCode = array();
		$selectDefaultCode['reference'] = __( 'Reference', 'fakturo' );
		$selectDefaultCode['internal_code'] = __( 'Internal code', 'fakturo' );
		$selectDefaultCode['manufacturers_code'] = __( 'Manufacturers code', 'fakturo' );							
		$selectDefaultCode = apply_filters('fktr_default_code_array', $selectDefaultCode);
		
		$echoSelectDefaultCode = '<select id="fakturo_system_options_group_default_code" name="fakturo_system_options_group[default_code]">';
		foreach ($selectDefaultCode as $key => $txt) {
			$echoSelectDefaultCode .= '<option value="'.$key.'"'.selected($key, $options['default_code'], false).'>'.$txt.'</option>';
		}
		$echoSelectDefaultCode .= '</select>';		
		
		
		$selectDefaultDescription = array();
		$selectDefaultDescription['short_description'] = __( 'Short Description', 'fakturo' );
		$selectDefaultDescription['description'] = __( 'Description', 'fakturo' );						
		$selectDefaultDescription = apply_filters('fktr_default_description_array', $selectDefaultDescription);
		
		$echoSelectDefaultDescription = '<select id="fakturo_system_options_group_default_description" name="fakturo_system_options_group[default_description]">';
		foreach ($selectDefaultDescription as $key => $txt) {
			$echoSelectDefaultDescription .= '<option value="'.$key.'"'.selected($key, $options['default_description'], false).'>'.$txt.'</option>';
		}
		$echoSelectDefaultDescription .= '</select>';	

		$print_html = '
						'.self::get_form().'
						<div id="header_title"> 
							<h1>'.__('Products', 'fakturo').'</h1>
							'.self::get_buttons().'
					    </div>

					<table class="form-table">
					    <tr>
							<td style="width:275px;">'. __( 'Categories', 'fakturo' ) .'<br/><br/></td>
							<td class="italic-label">
								'.$selectCategories.' '.fktr_popup_taxonomy::button(
																	array(
																		'taxonomy' => 'fktr_category',
																		'echo' => 0,
																		'class' => 'button',
																		'selector' => '#fakturo_system_options_group_categories',
																	)
															).'
								  <p class="description">
								  	'. __( 'Like Tools, electric tools, manual tools, etc.', 'fakturo' ) .' 
							      </p>
							</td>
						
					  </tr>
					  <tr>
							<td>'. __( 'Models', 'fakturo' ) .'<br/><br/></td>
							<td class="italic-label">
								'.$selectModels.' '.fktr_popup_taxonomy::button(
																	array(
																		'taxonomy' => 'fktr_model',
																		'echo' => 0,
																		'class' => 'button',
																		'selector' => '#fakturo_system_options_group_models',
																	)
															).'
								  <p class="description">
								  	'. __( 'As a tag of the products you want filter in the future.', 'fakturo' ) .' 
							      </p>
							</td>
						
					  </tr>
					  <tr>
							<td>'. __( 'Product Types', 'fakturo' ) .'<br/><br/></td>
							<td class="italic-label">
								'.$selectProductTypes.' '.fktr_popup_taxonomy::button(
																	array(
																		'taxonomy' => 'fktr_product_type',
																		'echo' => 0,
																		'class' => 'button',
																		'selector' => '#fakturo_system_options_group_product_type',
																	)
															).'
								  <p class="description">
								  	'. __( 'Like Service, spare, supplies, etc.', 'fakturo' ) .' 
							      </p>
							</td>
						
					  </tr>
					  <tr>
							<td>'. __( 'Origins', 'fakturo' ) .'<br/><br/></td>
							<td class="italic-label">
								'.$selectOrigins.' '.fktr_popup_taxonomy::button(
																	array(
																		'taxonomy' => 'fktr_origins',
																		'echo' => 0,
																		'class' => 'button',
																		'selector' => '#fakturo_system_options_group_origins',
																	)
															).'
								  <p class="description">
								  	'. __( 'Choose your sale point.', 'fakturo' ) .' 
							      </p>
							</td>
						
					  </tr>
					  <tr>
							<td>'. __( 'Price Scales', 'fakturo' ) .'<br/><br/></td>
							<td class="italic-label">
								'.$selectPriceScales.' '.fktr_popup_taxonomy::button(
																	array(
																		'taxonomy' => 'fktr_price_scales',
																		'echo' => 0,
																		'class' => 'button',
																		'selector' => '#fakturo_system_options_group_price_scales',
																	)
															).'
								  <p class="description">
								  	'. __( 'Like wholesaler and retailer.', 'fakturo' ) .' 
							      </p>
							</td>
						
					  </tr>
					  <tr>
							<td>'. __( 'Taxes', 'fakturo' ) .'<br/><br/> </td>
							<td class="italic-label">
									  '.$selectTax.'  '.fktr_popup_taxonomy::button(
																		array(
																			'taxonomy' => 'fktr_tax',
																			'echo' => 0,
																			'class' => 'button',
																			'selector' => '#fakturo_system_options_group_tax',
																		)
																).'
									  <p class="description">
									 	 '. __( 'The name and percentage of taxes applied to the products.', 'fakturo' ) .' 
								      </p>
							</td>
						</tr>
					    <tr>
							<td>'. __( 'Packagings', 'fakturo' ) .'<br/><br/></td>
							<td class="italic-label">
								'.$selectPackagings.' '.fktr_popup_taxonomy::button(
																	array(
																		'taxonomy' => 'fktr_packaging',
																		'echo' => 0,
																		'class' => 'button',
																		'selector' => '#fakturo_system_options_group_packaging',
																	)
															).'
								  <p class="description">
								  	
							      </p>
							</td>
						
					    </tr>

					    <tr>
							<td>'. __( 'Locations', 'fakturo' ) .'<br/><br/></td>
							<td class="italic-label">
								'.$selectLocations.' '.fktr_popup_taxonomy::button(
																	array(
																		'taxonomy' => 'fktr_locations',
																		'echo' => 0,
																		'class' => 'button',
																		'selector' => '#fakturo_system_options_group_locations',
																	)
															).'
								  <p class="description">
								  	'. __( 'Where the products are physically.', 'fakturo' ) .' 
							      </p>
							</td>
						
					    </tr>
					  </table>	
					  <hr/>
					  <table class="form-table">  
					  <tr>
							<td>'. __( 'Default code for invoice', 'fakturo' ) .'</td>
							<td class="italic-label">
									'.$echoSelectDefaultCode.'
									<p class="description">
										'. __( '', 'fakturo' ) .'             
									</p>
							</td>
					  </tr>
					   <tr>
							<td>'. __( 'Default description for invoice', 'fakturo' ) .'</td>
							<td class="italic-label">
									'.$echoSelectDefaultDescription.'
									<p class="description">
										'. __( '', 'fakturo' ) .'             
									</p>
							</td>
					  </tr>
					  <tr>
							<td>'. __( 'Search code on invoices, budgets, etc..', 'fakturo' ) .'</td>
							<td class="italic-label">
									'.$echoSelectSearchCode.'
									<p class="description">
										'. __( '', 'fakturo' ) .'             
									</p>
							</td>
						
					  </tr>
					  </table>	
					  <hr/>
					  <table class="form-table">
					  <tr>
							<td style="width:275px;">'. __( 'Use stock for products', 'fakturo' ) .'<br/><br/></td>
							<td class="italic-label">
								<input id="fakturo_system_options_group_use_stock_product" class="slidercheck" type="checkbox" name="fakturo_system_options_group[use_stock_product]" value="1" '.(($options['use_stock_product'])?'checked="checked"':'').'>
								<label for="fakturo_system_options_group_use_stock_product"><span class="ui"></span></label>
								<p class="description">'. __( 'Activate for use stock for products', 'fakturo' ).'</p>
							</td>
						
					  </tr>
					  <tr>
							<td>'. __( 'Allow negative stocks', 'fakturo' ) .'<br/><br/></td>
							<td class="italic-label">
								<input id="fakturo_system_options_group_stock_less_zero" class="slidercheck" type="checkbox" name="fakturo_system_options_group[stock_less_zero]" value="1" '.(($options['stock_less_zero'])?'checked="checked"':'').'>
								<label for="fakturo_system_options_group_stock_less_zero"><span class="ui"></span></label>
								<p class="description">'. __( 'Activate for use stock less than zero.', 'fakturo' ).'</p>
							
							</td>
					  </tr>

					</table>	
					
					'.self::get_buttons().'
					</form>
					';
		self::ouput($print_html, __('Products', 'fakturo'));
	
	}
	/**
	* Static function action_products
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function action_products() {
		$new_options = get_option('fakturo_system_options_group', array());
		if (!isset($_POST['fakturo_system_options_group']['use_stock_product'])){
			$_POST['fakturo_system_options_group']['use_stock_product'] = 0;
		}
		if (!isset($_POST['fakturo_system_options_group']['stock_less_zero'])){
			$_POST['fakturo_system_options_group']['stock_less_zero'] = 0;
		}
		if (!isset($_POST['fakturo_system_options_group']['search_code'])){
			$_POST['fakturo_system_options_group']['search_code'] = array();
		}
		

		$new_options['default_description'] = $_POST['fakturo_system_options_group']['default_description'];
		$new_options['default_code'] = $_POST['fakturo_system_options_group']['default_code'];
		$new_options['search_code'] = $_POST['fakturo_system_options_group']['search_code'];
		
		$new_options['use_stock_product'] = $_POST['fakturo_system_options_group']['use_stock_product'];
		$new_options['stock_less_zero'] = $_POST['fakturo_system_options_group']['stock_less_zero'];
		update_option('fakturo_system_options_group', $new_options);
	}
	/**
	* Static function page_payments
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function page_payments() {
		$options = get_option('fakturo_system_options_group');
		$selectPaymentType = wp_dropdown_categories( array(
										'show_option_all'    => '',
										'show_option_none'   => __('Choose a Payment Type', 'fakturo' ),
										'orderby'            => 'name', 
										'order'              => 'ASC',
										'show_count'         => 0,
										'hide_empty'         => 0, 
										'child_of'           => 0,
										'exclude'            => '',
										'echo'               => 0,
										'selected'           => $options['payment_type'],
										'hierarchical'       => 1, 
										'name'               => 'fakturo_system_options_group[payment_type]',
										'id'               => 'fakturo_system_options_group_payment_type',
										'class'              => 'form-no-clear',
										'depth'              => 1,
										'tab_index'          => 0,
										'taxonomy'           => 'fktr_payment_types',
										'hide_if_empty'      => false
									));

		

		$print_html = '
						'.self::get_form().'
						<div id="header_title"> 
							<h1>'.__('Payments', 'fakturo').'</h1>
							'.self::get_buttons().'
					    </div>
					<table class="form-table">
						<tr>
							<td>'. __( 'Default Payment Type', 'fakturo' ) .'<br/><br/> </td>
							<td class="italic-label">
									  '.$selectPaymentType.'  '.fktr_popup_taxonomy::button(
																		array(
																			'taxonomy' => 'fktr_payment_types',
																			'echo' => 0,
																			'class' => 'button',
																			'selector' => '#fakturo_system_options_group_payment_type',
																		)
																).'
									  <p class="description">
									 	 '. __( 'Choose your default Payment Type, You can add more payment types to use in payments.', 'fakturo' ) .' 
								      </p>
							</td>
						</tr>
						
					</table>	
					
					'.self::get_buttons().'
					</form>
					';
		self::ouput($print_html, __('Payments', 'fakturo'));
	
	}
	/**
	* Static function action_payments
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function action_payments() {
		$new_options = get_option('fakturo_system_options_group', array());
		$new_options['payment_type'] = $_POST['fakturo_system_options_group']['payment_type'];
		update_option('fakturo_system_options_group', $new_options);
	}

	
	
	/**
	* Static function ouput
	* @access public
	* @param string $message Ouput message or WP_Error object.
	* @param string          $title   Optional. Output title. Default empty.
	* @param string|array    $args    Optional. Arguments to control behavior. Default empty array.
	* @return void
	* @since 0.7
	*/
	public static function ouput($message, $title = '', $args = array()) {
		$defaults = array( 'response' => 200 );
		$r = wp_parse_args($args, $defaults);

		$have_gettext = function_exists('__');

		$steps = self::get_steps();
		$porcent_per_steep = ((100-count($steps))/count($steps));
		if ( function_exists( 'is_wp_error' ) && is_wp_error( $message ) ) {
			if ( empty( $title ) ) {
				$error_data = $message->get_error_data();
				if ( is_array( $error_data ) && isset( $error_data['title'] ) )
					$title = $error_data['title'];
			}
			$errors = $message->get_error_messages();
			switch ( count( $errors ) ) {
			case 0 :
				$message = '';
				break;
			case 1 :
				$message = "<p>{$errors[0]}</p>";
				break;
			default :
				$message = "<ul>\n\t\t<li>" . join( "</li>\n\t\t<li>", $errors ) . "</li>\n\t</ul>";
				break;
			}
		} elseif ( is_string( $message ) ) {
			$message = "<p>$message</p>";
		}

		if ( isset( $r['back_link'] ) && $r['back_link'] ) {
			$back_text = $have_gettext? __('&laquo; Back') : '&laquo; Back';
			$message .= "\n<p><a href='javascript:history.back()'>$back_text</a></p>";
		}

		if ( ! did_action( 'admin_head' ) ) :
			if ( !headers_sent() ) {
				status_header( $r['response'] );
				nocache_headers();
				header( 'Content-Type: text/html; charset=utf-8' );
			}

			if ( empty($title) )
				$title = $have_gettext ? __('WordPress &rsaquo; Error') : 'WordPress &rsaquo; Error';

			$text_direction = 'ltr';
			if ( isset($r['text_direction']) && 'rtl' == $r['text_direction'] )
				$text_direction = 'rtl';
			elseif ( function_exists( 'is_rtl' ) && is_rtl() )
				$text_direction = 'rtl';
	?>
	<!DOCTYPE html>
	<!-- Ticket #11289, IE bug fix: always pad the error page with enough characters such that it is greater than 512 bytes, even after gzip compression abcdefghijklmnopqrstuvwxyz1234567890aabbccddeeffgghhiijjkkllmmnnooppqqrrssttuuvvwwxxyyzz11223344556677889900abacbcbdcdcededfefegfgfhghgihihjijikjkjlklkmlmlnmnmononpopoqpqprqrqsrsrtstsubcbcdcdedefefgfabcadefbghicjkldmnoepqrfstugvwxhyz1i234j567k890laabmbccnddeoeffpgghqhiirjjksklltmmnunoovppqwqrrxsstytuuzvvw0wxx1yyz2z113223434455666777889890091abc2def3ghi4jkl5mno6pqr7stu8vwx9yz11aab2bcc3dd4ee5ff6gg7hh8ii9j0jk1kl2lmm3nnoo4p5pq6qrr7ss8tt9uuvv0wwx1x2yyzz13aba4cbcb5dcdc6dedfef8egf9gfh0ghg1ihi2hji3jik4jkj5lkl6kml7mln8mnm9ono
	-->
	<html xmlns="http://www.w3.org/1999/xhtml" <?php if ( function_exists( 'language_attributes' ) && function_exists( 'is_rtl' ) ) language_attributes(); else echo "dir='$text_direction'"; ?>>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width">
		<?php
		if ( function_exists( 'wp_no_robots' ) ) {
			wp_no_robots();
		}
		?>
		<title><?php echo $title ?></title>
		<style type="text/css">
			html {
				background: #f1f1f1;
			}
			hr {
				border-style: solid;
			    border: 0px;
			    border-bottom: 1px solid #dadada;
			}
			input[type=text], input[type=search], input[type=radio], input[type=tel], input[type=time], input[type=url], input[type=week], input[type=password], input[type=checkbox], input[type=color], input[type=date], input[type=datetime], input[type=datetime-local], input[type=email], input[type=month], input[type=number], select, textarea {
			    border: 1px solid #ddd;
			    -webkit-box-shadow: inset 0 1px 2px rgba(0,0,0,.07);
			    box-shadow: inset 0 1px 2px rgba(0,0,0,.07);
			    background-color: #fff;
			    color: #32373c;
			    outline: 0;
			    -webkit-transition: 50ms border-color ease-in-out;
			    transition: 50ms border-color ease-in-out;
			}
			input, select {
			    margin: 1px;
			    padding: 3px 5px;
			}
			input, select, textarea {
			    font-size: 14px;
			    -webkit-border-radius: 0;
			    border-radius: 0;
			}
			input, textarea {
			    box-sizing: border-box;
			   
			}
			input[type=text], textarea, select {
				width: 300px;
			}
			.form-table td p {
			    margin-top: 4px !important;
			    margin-bottom: 0 !important;
			}
			p.description, p.help, span.description {
			    font-size: 13px;
			    font-style: italic;
			}

			#error-page {
				background: #fff;
				color: #444;
				font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
				margin: 2em auto;
				padding: 1em 2em;
				min-width: 700px;
				-webkit-box-shadow: 0 1px 3px rgba(0,0,0,0.13);
				box-shadow: 0 1px 3px rgba(0,0,0,0.13);
				margin-top: 20px;
				margin-left: 60px;
				margin-right: 60px;
			}
			
			#error-page p {
				font-size: 14px;
				line-height: 1.5;
				margin: 25px 0 20px;
			}
			#error-page code {
				font-family: Consolas, Monaco, monospace;
			}
			#icon-header {
				margin: 0px auto;
				width: 100px;
				height: 100px;
				display: block;
			}
			.stepwizard-row {
				background: #fff;
				height: 4px;
				margin: 0px auto;
				margin-top: 20px;
				min-width: 700px;
				-webkit-box-shadow: 0 1px 3px rgba(0,0,0,0.13);
   				box-shadow: 0 1px 3px rgba(0,0,0,0.13);
   				background-image: -webkit-linear-gradient(top,#626161 0,#b9b8b7 100%);
				background-image: linear-gradient(to bottom,#626161 0,#b9b8b7 100%);
				margin-left: 60px;
				margin-right: 60px;
			}
			.stepwizard-row-bar {
				background-color: #f7b63e;
				width: 40%;
				height: 100%;
				background-image: -webkit-linear-gradient(top,#f7b63e 0,#816414 100%);
				background-image: linear-gradient(to bottom,#f7b63e 0,#816414 100%);
			}
			.wizard-steps-circles {
				height: auto;
				margin: 0px auto;
				min-width: 700px;
				margin-left: 60px;
				margin-right: 60px;
			}
			.wizard-steps-circles div {
				width: 20px;
    			height: 20px;
                border-radius: 10px;
                float: left;
                text-align: center;
                color: #333;
    			background-color: #fff;
    			border-color: #ccc;
    			line-height: 1.428571429;
    			margin-top: -12px;
    			opacity: 0.7;
    			margin-right: <?php echo $porcent_per_steep; ?>%;;
    			margin-left: -12px;
    			border: 1px solid #4d5154;
			}
			.wizard-steps-circles div.active {
				width: 20px;
    			height: 20px;
                border-radius: 10px;
                float: left;
                text-align: center;
                color: #fff;
    			background-color: #f7b63e;
    		    background-image: -webkit-linear-gradient(top,#f7b63e 0,#816414 100%);
    			background-image: linear-gradient(to bottom,#f7b63e 0,#816414 100%);
    			line-height: 1.428571429;
    			margin-top: -12px;
    			margin-right: <?php echo $porcent_per_steep; ?>%;
    			opacity: 1;
    			border: 1px solid #4d5154;
			}
			.wizard-steps-circles div:last-child {
				margin-right: 0px;
			}
			.wizard-steps-circles div:first-child {
				margin-left: 0px;
			}

			.wizard-steps {
				height: auto;
				margin: 0px auto;
				min-width: 700px;
				margin-left: 60px;
				margin-right: 60px;
				clear: both;
			}
			.wizard-steps div {
				display: inline-block;
				text-align: left;
				width: 19%;
				visibility: hidden;
				vertical-align: top;
			}
			.wizard-steps div.active {
				display: inline-block;
				text-align: left;
				width: 19%;
				visibility:visible;
			}
			
			h1 {
				border-bottom: 1px solid #dadada;
				clear: both;
				color: #666;
				font-size: 24px;
				margin: 30px 0 0 0;
				padding: 0;
				padding-bottom: 7px;
			}
			#header_title h1 {
				display: inline;
				border-bottom: 0px;
			}
			#header_title #buttons_container {
				float: right;
				
			}
			#header_title {
				padding-bottom: 6px;
				border-bottom: 1px solid #dadada;
			}
			
			ul li {
				margin-bottom: 10px;
				font-size: 14px ;
			}
			a {
				color: #0073aa;
			}
			a:hover,
			a:active {
				color: #00a0d2;
			}
			a:focus {
				color: #124964;
			    -webkit-box-shadow:
			    	0 0 0 1px #5b9dd9,
					0 0 2px 1px rgba(30, 140, 190, .8);
			    box-shadow:
			    	0 0 0 1px #5b9dd9,
					0 0 2px 1px rgba(30, 140, 190, .8);
				outline: none;
			}
			#buttons_container {
				text-align: right;
			}
			.button-large {
				line-height: 30px;
    			height: 32px;
    			padding: 0 20px 1px;
			}
			.button {
				background: #f7f7f7;
				border: 1px solid #ccc;
				color: #555;
				display: inline-block;
				text-decoration: none;
				font-size: 13px;
				line-height: 26px;
				height: 28px;
				margin: 0;
				padding: 0 10px 1px;
				cursor: pointer;
				-webkit-border-radius: 3px;
				-webkit-appearance: none;
				border-radius: 3px;
				white-space: nowrap;
				-webkit-box-sizing: border-box;
				-moz-box-sizing:    border-box;
				box-sizing:         border-box;

				-webkit-box-shadow: 0 1px 0 #ccc;
				box-shadow: 0 1px 0 #ccc;
			 	vertical-align: top;
			}

			.button-orange {
				background: #f2b340;
    			border: 1px solid #bd8827;
    			color: #fffcfc;
			}
			.button-orange:hover {
				background: #f2db40 !important;
				color: #fffcfc !important;
			}
			.button-orange:focus {
				background: #bd8827 !important;
				color: #fffcfc !important;
			}
			

			.button-large {
				height: 32px;
    			line-height: 30px;
    			padding: 0 20px 2px;
			}

			.button:hover,
			.button:focus {
				background: #fafafa;
				border-color: #999;
				color: #23282d;
			}

			.button:focus  {
				border-color: #5b9dd9;
				-webkit-box-shadow: 0 0 3px rgba( 0, 115, 170, .8 );
				box-shadow: 0 0 3px rgba( 0, 115, 170, .8 );
				outline: none;
			}

			.button:active {
				background: #eee;
				border-color: #999;
			 	-webkit-box-shadow: inset 0 2px 5px -3px rgba( 0, 0, 0, 0.5 );
			 	box-shadow: inset 0 2px 5px -3px rgba( 0, 0, 0, 0.5 );
			 	-webkit-transform: translateY(1px);
			 	-ms-transform: translateY(1px);
			 	transform: translateY(1px);
			}

			<?php
			if ( 'rtl' == $text_direction ) {
				echo 'body { font-family: Tahoma, Arial; }';
			}
			?>
		</style>
		<?php
			do_action('fktr_wizard_output_print_styles');
			wp_print_styles();
			do_action('fktr_wizard_output_print_scripts');
			wp_print_scripts();
		?>
	</head>
	<body>

	<?php endif; // ! did_action( 'admin_head' ) ?>
		<img id="icon-header" src="<?php echo FAKTURO_PLUGIN_URL.'assets/images/icon-256x256.png'; ?>"/>
		<div class="stepwizard-row">
			<div class="stepwizard-row-bar" style="width:<?php echo (self::$current_request['step']-1)*($porcent_per_steep+1); ?>%">
			</div>
		</div>
		<div class="wizard-steps-circles">
			<?php 
				foreach (self::get_steps() as $k => $st) {
					$key_step = $k+1;
					$class = '';
					if ($key_step <= self::$current_request['step']) {
						$class = ' class="active"';
					}
					echo '<div'.$class.'>'.$key_step.'</div>';
				}
			?>
      	</div>
		<div class="wizard-steps">
			<?php 
				foreach (self::get_steps() as $k => $st) {
					$key_step = $k+1;
					$class = '';
					if ($key_step == self::$current_request['step']) {
						$class = ' class="active"';
					}
					echo '<div'.$class.' style="width:'.$porcent_per_steep.'%; margin-right: 21px; margin-left: -12px;">'.$st.'</div>';
				}
			?>
        	
      	</div>
		<div id="error-page">
			
			
			<?php echo $message; ?>
		</div>
		
	</body>
	</html>
	<?php
	die();

	}

}
fktr_wizard::hooks();
?>