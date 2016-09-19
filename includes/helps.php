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
