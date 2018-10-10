<?php
class fktr_helps {
	function __construct() {
		add_action( 'current_screen', array(__CLASS__, 'init'));
	}
	public static function init() {
		global $current_screen;
		$helptexts = array();
		$screen = get_current_screen();
		$doc_path = FAKTURO_PLUGIN_DIR.'docs/'.$screen->id .'-help.php';
		if ($screen->post_type != '' && !file_exists($doc_path)) {
			if (file_exists(FAKTURO_PLUGIN_DIR.'docs/'.$screen->post_type .'-help.php')) {
				$doc_path = FAKTURO_PLUGIN_DIR.'docs/'.$screen->post_type .'-help.php';
			}
		}
		$doc_path = apply_filters('fktr_help_doc_path', $doc_path);
		if (file_exists($doc_path)) {
			include $doc_path;

			$screen->set_help_sidebar(
				'<p><strong>' . sprintf( __( 'For more information:', 'fakturo' ) . '</strong></p>' .
				'<p>' . sprintf( __( 'Visit the <a href="%s">documentation</a> on the Fakturo website.', 'fakturo' ), esc_url( 'http://fakturo.org/' ) ) ) . '</p>' .
				'<p>' . sprintf(
					__( '<a href="%s" target="_blank">Post an issue</a> on our <a href="%s" target="_blank">website</a>.<br />View useful <a href="%s" target="_blank">extensions</a>.', 'fakturo' ),
					esc_url( 'https://etruel.com/support' ),
					esc_url( 'https://etruel.com' ),
					esc_url( 'https://etruel.com/downloads/fakturo-addons&utm_campaign=ContextualHelp' ),
					esc_url( 'https://easydigitaldownloads.com/themes/?utm_source=plugin-downloads-page&utm_medium=contextual-help-sidebar&utm_term=themes&utm_campaign=ContextualHelp' )
				) . '</p>'
			);			
			/**
			 * filter 'fktr_help_'.$screen->id
			 * Parses the help array to add help strings 
			 * 
			 * Description of Help Texts Array
			 * -------------------------------
			 * array('ID for left tab link' => array(
			 *  'tabtitle' =>  __('Text for left TAB link', 'fakturo' ),  //if not exist takes the ID as text.
			 * 	'field_name' => array( 
			 * 		'title' => 'Text showed as bold in right side' , 
			 * 		'tip' => 'Text html shown below the title in right side and also can be used for mouse over tips.' , 
			 * 		'plustip' => 'Text html added below "tip" in right side in a new paragraph.',
			 * )));
			 */
			$helptexts = apply_filters('fktr_help_'.$screen->id, $helptexts);
			foreach($helptexts as $key => $section){
				$tabcontent = '';
				$tabtitle = (!empty($section['tabtitle']) ? $section['tabtitle'] : $key ); //if not defined $tabtitle takes $key
				foreach($section as $section_key => $sdata) {
					if($section_key=='tabtitle'){
						continue;
					}
					$tip = (!empty($sdata['tip']) ? $sdata['tip'] : '');
					$title = (!empty($sdata['title']) ? $sdata['title'] : '');
					$helptip[$section_key] = htmlentities($tip);
					$tabcontent .= '<p><strong>' . $title . '</strong><br />'.
						 $tip . '</p>';
					$tabcontent .= (isset($sdata['plustip'])) ?    '<p>' . $sdata['plustip'] . '</p>' : '';
				}
				$screen->add_help_tab(array(
					'id'    	=> $key,
					'title' 	=> $tabtitle,
					'content'	=> $tabcontent,
				));
			}
		}
	}
}
$fktr_helps = new fktr_helps();
?>
