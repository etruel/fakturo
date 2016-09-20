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
		if (file_exists($doc_path)) {
			include $doc_path;
			/**
			 * filter 'fktr_help_'.$screen->id
			 * Parses the help array to add help strings 
			 * 
			 * Description of Help Texts Array
			 * -------------------------------
			 * array('Text for left tab link' => array(
			 * 	'field_name' => array( 
			 * 		'title' => 'Text showed as bold in right side' , 
			 * 		'tip' => 'Text html shown below the title in right side and also can be used for mouse over tips.' , 
			 * 		'plustip' => 'Text html added below "tip" in right side in a new paragraph.',
			 * )));
			 */
			$helptexts = apply_filters('fktr_help_'.$screen->id, $helptexts);
			foreach($helptexts as $key => $section){
				$tabcontent = '';
				foreach($section as $section_key => $sdata){
				 $helptip[$section_key] = htmlentities($sdata['tip']);
					$tabcontent .= '<p><strong>' . $sdata['title'] . '</strong><br />'.
						 $sdata['tip'] . '</p>';
					$tabcontent .= (isset($sdata['plustip'])) ?    '<p>' . $sdata['plustip'] . '</p>' : '';
				}
				$screen->add_help_tab(array(
					'id'    	=> $key,
					'title' 	=> $key,
					'content'	=> $tabcontent,
				));
			}
		}
	}
}
$fktr_helps = new fktr_helps();
?>
