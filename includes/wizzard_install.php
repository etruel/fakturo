<?php
	// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class fktr_wizzard {

	public static $steps = array();
	/**
	* Static function hooks
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function hooks() {
		add_action('admin_post_fktr_wizzard', array(__CLASS__, 'page'));
	}
	/**
	* Static function get_steps
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function get_steps() {
		if (empty($steps)) {
			$steps = array('Minium Settings', 'Invoicing', 'Receipts');
			$steps = apply_filters('fktr_steps_setup_array', $steps);
		}
		return $steps;
	}
	/**
	* Static function page
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function page() {
		$print_html = '<h1>'.__('Fakturo - Setup', FAKTURO_TEXT_DOMAIN).'</h1>
					<p>Install content</p>';
		self::ouput($print_html, __('Fakturo - Setup', FAKTURO_TEXT_DOMAIN));
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
			#error-page {
				background: #fff;
				color: #444;
				font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
				margin: 2em auto;
				padding: 1em 2em;
				max-width: 700px;
				-webkit-box-shadow: 0 1px 3px rgba(0,0,0,0.13);
				box-shadow: 0 1px 3px rgba(0,0,0,0.13);
			}
			#error-page {
				margin-top: 20px;
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
				max-width: 700px;
				-webkit-box-shadow: 0 1px 3px rgba(0,0,0,0.13);
   				box-shadow: 0 1px 3px rgba(0,0,0,0.13);
   				background-image: -webkit-linear-gradient(top,#626161 0,#b9b8b7 100%);
				background-image: linear-gradient(to bottom,#626161 0,#b9b8b7 100%);
			}
			.stepwizard-row-bar {
				background-color: #f7b63e;
				width: 10%;
				height: 100%;
				background-image: -webkit-linear-gradient(top,#f7b63e 0,#816414 100%);
				background-image: linear-gradient(to bottom,#f7b63e 0,#816414 100%);
			}
			.wizard-steps {
				height: auto;
				margin: 0px auto;
				max-width: 700px;
			}
			.wizard-steps p {
				display: inline-block;
				text-align: center;
				width: 19%;
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

			.button.button-large {
				height: 30px;
				line-height: 28px;
				padding: 0 12px 2px;
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
			<div class="stepwizard-row-bar">
			</div>
		</div>
		<div class="wizard-steps">
        	<p>Step 1</p>
        	<p>Step 2</p>
        	<p>Step 3</p>
        	<p>Step 4</p>
        	<p>Step 5</p>
      	</div>
		<div id="error-page">
			
			<?php print_r($porcent_per_steep); ?>
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