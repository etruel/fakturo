<?php
//https://github.com/feulf/raintpl3/wiki/Documentation-for-web-designers
//https://github.com/feulf/raintpl/blob/master/tpl/page.html
//
//include the RainTPL class
require_once FAKTURO_PLUGIN_DIR . 'includes/libs/rain.tpl.class.php';

class fktr_tpl extends RainTPL {

	function fromString($template_code) {
		//tag list
		$template_code = preg_replace("/<\?xml(.*?)\?>/s", "##XML\\1XML##", $template_code);

		//disable php tag
		if (!self::$php_enabled)
			$template_code = str_replace(array("<?", "?>"), array("&lt;?", "?&gt;"), $template_code);

		//xml re-substitution
		$template_code = preg_replace_callback("/##XML(.*?)XML##/s", array($this, 'xml_reSubstitution'), $template_code);

		$tag_regexp = array(
			'loop' => '(\{loop(?: name){0,1}="\${0,1}[^"]*"\})',
			'break' => '(\{break\})',
			'loop_close' => '(\{\/loop\})',
			'if' => '(\{if(?: condition){0,1}="[^"]*"\})',
			'elseif' => '(\{elseif(?: condition){0,1}="[^"]*"\})',
			'else' => '(\{else\})',
			'if_close' => '(\{\/if\})',
			'function' => '(\{function="[^"]*"\})',
			'noparse' => '(\{noparse\})',
			'noparse_close' => '(\{\/noparse\})',
			'ignore' => '(\{ignore\}|\{\*)',
			'ignore_close' => '(\{\/ignore\}|\*\})',
			'include' => '(\{include="[^"]*"(?: cache="[^"]*")?\})',
			'template_info' => '(\{\$template_info\})',
			'function' => '(\{function="(\w*?)(?:.*?)"\})'
		);

		$tag_regexp = "/" . join("|", $tag_regexp) . "/";
		$tag_regexp = apply_filters('fktr_tpl_tag_regexp', $tag_regexp);
		//path replace (src of img, background and href of link)
		//$template_code = $this->path_replace( $template_code, $tpl_basedir );
		//split the code with the tags regexp
		$template_code = preg_split($tag_regexp, $template_code, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
		//compile the code
		$compiled_code = $this->compileCode($template_code);
		//return the compiled code
		ob_start();
		extract($this->var);
		eval('?>' . $compiled_code . '<?php ');
		$raintpl_contents = ob_get_clean();
		return $raintpl_contents;
	}

}

?>