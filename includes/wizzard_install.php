<?php
	// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class fktr_wizzard {

	public static $steps = array();
	public static $current_request = array();
	/**
	* Static function hooks
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function hooks() {
		add_action('admin_post_fktr_wizzard', array(__CLASS__, 'page'));
		add_action('fktr_wizzard_install_step_1', array(__CLASS__, 'page_step_one'));
		add_action('fktr_wizzard_install_step_2', array(__CLASS__, 'page_step_two'));
		add_action('fktr_wizzard_install_step_3', array(__CLASS__, 'page_step_three'));
		
	}
	/**
	* Static function get_steps
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function get_steps() {
		if (empty($steps)) {
			$steps = array('Load Countries', 'Company Info', 'Receipts', 'Other 4', 'Other 5');
			$steps = apply_filters('fktr_steps_setup_array', $steps);
		}
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
							'action' => 'fktr_wizzard',
							'step' => 1
						);
		return $default_values;
	}
	/**
	* Static function page
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function page() {
		self::$current_request = wp_parse_args($_GET, self::default_request_values());
		do_action('fktr_wizzard_install_step_'.self::$current_request['step']);
	}
	/**
	* Static function page_step_one
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function page_step_one() {
		$print_html = '<h1>'.__('Load Countries and States', FAKTURO_TEXT_DOMAIN).'</h1>
					<p>'.__('Do you want load all countries and states by default?', FAKTURO_TEXT_DOMAIN).'</p>
					
					<table class="form-table">
						<tr valign="top">
							<th scope="row"><input type="radio" name="load_contries_states" value="yes" checked/></th>
							<td>
								'. __( 'Yes', FAKTURO_TEXT_DOMAIN ) .'
	                        </td>
	                    </tr>
	                    <tr valign="top">
	                        <th scope="row"><input type="radio" name="load_contries_states" value="no"/></th>
							<td>
								'. __( 'No', FAKTURO_TEXT_DOMAIN ) .'
	                        </td>
	                    </tr>
	                </table>

					<div id="buttons_container">
						<input type="submit" class="button button-large button-orange" style="padding-left:30px; padding-right:30px;" value="Next"/>
					</div>
					';
		self::ouput($print_html, __('Load Countries and States', FAKTURO_TEXT_DOMAIN));
		
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
		update_option('fakturo_info_options_group' , $options);
		$selectCountry = wp_dropdown_categories( array(
			'show_option_all'    => '',
			'show_option_none'   => __('Choose a country', FAKTURO_TEXT_DOMAIN ),
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
			'show_option_none'   => __('Choose a state', FAKTURO_TEXT_DOMAIN ),
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
		$print_html = '<h1>'.__('Company Info', FAKTURO_TEXT_DOMAIN).'</h1>
					<table class="form-table">
						<tr valign="top">
							<th scope="row">'. __( 'Name', FAKTURO_TEXT_DOMAIN ) .'</th>
							<td>
								<input type="text" size="36" name="fakturo_info_options_group[name]" value="'.$options['name'].'"/>
	                        </td>
	                    </tr>
						<tr valign="top">
							<th scope="row">'. __( 'Taxpayer ID', FAKTURO_TEXT_DOMAIN ) .'</th>
							<td>
								<input type="text" size="36" id="fakturo_info_options_group_taxpayer" name="fakturo_info_options_group[taxpayer]" value="'.$options['taxpayer'].'"/>
	                        </td>
	                    </tr>
						<tr valign="top">
							<th scope="row">'. __( 'Gross income tax ID', FAKTURO_TEXT_DOMAIN ) .'</th>
							<td>
								<input type="text" size="36" name="fakturo_info_options_group[tax]" value="'.$options['tax'].'"/>
	                        </td>
	                    </tr>
						<tr valign="top">
							<th scope="row">'. __( 'Start of activities', FAKTURO_TEXT_DOMAIN ) .'</th>
							<td>
								<input type="text" size="36" name="fakturo_info_options_group[start]" id="start" value="'.$options['start'].'"/>
	                        </td>
	                    </tr>
						<tr valign="top">
							<th scope="row">'. __( 'Address', FAKTURO_TEXT_DOMAIN ) .'</th>
							<td>
								<textarea name="fakturo_info_options_group[address]" cols="36" rows="4">'.$options['address'].'</textarea>
							</td>
	                    </tr>
						<tr valign="top">
							<th scope="row">'. __( 'Telephone', FAKTURO_TEXT_DOMAIN ) .'</th>
							<td>
								<input type="text" size="36" name="fakturo_info_options_group[telephone]" value="'.$options['telephone'].'"/>
							</td>
	                    </tr>
						
						
	                    <tr valign="top">
							<th scope="row">'. __( 'City', FAKTURO_TEXT_DOMAIN ) .'</th>
							<td>
								<input type="text" size="36" name="fakturo_info_options_group[city]" value="'.$options['city'].'"/>
							</td>
	                    </tr>
	                    <tr valign="top">
							<th scope="row">'. __( 'Postcode', FAKTURO_TEXT_DOMAIN ) .'</th>
							<td>
								<input type="text" size="36" name="fakturo_info_options_group[postcode]" value="'.$options['postcode'].'"/>
							</td>
	                    </tr>
						<tr valign="top">
							<th scope="row">'. __( 'Website', FAKTURO_TEXT_DOMAIN ) .'</th>
							<td>
								<input type="text" size="36" name="fakturo_info_options_group[website]" value="'.$options['website'].'"/>
							</td>
	                    </tr>
					</table>
					<div id="buttons_container">
						<input type="submit" class="button button-large button-orange" style="padding-left:30px; padding-right:30px;" value="Next"/>
					</div>
					';
		self::ouput($print_html, __('Company Info', FAKTURO_TEXT_DOMAIN));
		
	}
	/**
	* Static function page_step_three
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function page_step_three() {
		
		$print_html = '<h1>'.__('Company Info', FAKTURO_TEXT_DOMAIN).'</h1>
					<p>Install content</p>
					<div id="buttons_container">
						<input type="submit" class="button button-large" value="Previus"/>
						<input type="submit" class="button button-large button-orange" style="padding-left:30px; padding-right:30px;" value="Next"/>
					</div>
					';
		self::ouput($print_html, __('Company Info', FAKTURO_TEXT_DOMAIN));
	
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
					echo '<div'.$class.' style="width:'.$porcent_per_steep.'%">'.$st.'</div>';
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
fktr_wizzard::hooks();
?>