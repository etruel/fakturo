<?php
//https://github.com/feulf/raintpl3/wiki/Documentation-for-web-designers
//https://github.com/feulf/raintpl/blob/master/tpl/page.html
//
//include the RainTPL class
require_once FAKTURO_PLUGIN_DIR . 'includes/libs/rain.tpl.class.php';

class fktr_tpl extends RainTPL {

	public $error = '';

	/**
	 * Functions a template may call. Everything else is rejected before the template runs; add-ons extend the list with the fktr_tpl_allowed_functions filter.
	 */
	public static $allowed_functions = array(
		'get_money_format', 'fktr_number_to_letter_es', 'etruel_fktr_qrcode',
		'date_i18n', 'wp_date', 'date', 'strtotime',
		'number_format', 'round', 'floor', 'ceil', 'abs', 'max', 'min', 'intval', 'floatval', 'is_numeric',
		'strtoupper', 'strtolower', 'ucfirst', 'ucwords', 'mb_strtoupper', 'mb_strtolower',
		'trim', 'ltrim', 'rtrim', 'strlen', 'mb_strlen', 'substr', 'mb_substr', 'str_pad', 'str_replace', 'sprintf', 'nl2br', 'implode',
		'count', 'in_array',
		'esc_html', 'esc_attr', 'esc_url', 'wp_kses_post', 'wpautop',
	);

	public static function allowed_functions() {
		return array_map('strtolower', (array) apply_filters('fktr_tpl_allowed_functions', self::$allowed_functions));
	}

	/**
	 * Variables are HTML-escaped before the template runs, so a client name or an address cannot inject markup into the PDF or the e-mail. $html = false is for plain text (e-mail subjects).
	 * A template that uses anything outside the allowed functions does not run: it returns an empty string and leaves the reason in $error.
	 */
	function fromString($template_code, $html = true) {
		$compiled_code = $this->compile($template_code);
		if ($compiled_code === false) {
			return '';
		}
		ob_start();
		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- template variables, escaped just above
		extract(($html && apply_filters('fktr_tpl_escape_variables', true)) ? self::escape($this->var) : $this->var);
		// phpcs:ignore Squiz.PHP.Eval.Discouraged -- the template engine runs compiled templates; self::check() has accepted every token of $compiled_code against an allowlist
		eval('?>' . $compiled_code . '<?php ');
		$raintpl_contents = ob_get_clean();
		return $raintpl_contents;
	}

	/**
	 * Returns the error of a template, or an empty string when it is allowed to run.
	 */
	public static function validate($template_code) {
		$tpl = new self;
		$tpl->compile($template_code);
		return $tpl->error;
	}

	public function compile($template_code) {
		$this->tpl['source'] = $template_code;
		$this->tpl['tpl_filename'] = 'string';

		//the include tag is not supported: it would read any URL or file while compiling
		$template_code = str_replace('{include', '&#123;include', $template_code);

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
		try {
			$compiled_code = $this->compileCode($template_code);
		} catch (Exception $e) {
			$tag = ($e instanceof RainTpl_Exception) ? $e->getTag() : '';
			$this->error = self::not_allowed_message($tag ? $tag : $e->getMessage());
			return false;
		}
		$problem = self::check($compiled_code);
		if ($problem !== '') {
			$this->error = self::not_allowed_message($problem);
			return false;
		}
		return $compiled_code;
	}

	public static function not_allowed_message($what) {
		return sprintf(__('This template uses something that is not allowed: %s', 'fakturo'), $what);
	}

	/**
	 * Accepts the compiled template only if every PHP token is one the engine itself writes or the template may use: variables, literals, operators and calls to the allowed functions. Returns the first thing that is not allowed, or an empty string.
	 */
	public static function check($compiled_code) {
		try {
			$tokens = token_get_all('?>' . $compiled_code . '<?php ', TOKEN_PARSE);
		} catch (ParseError $e) {
			return __('PHP syntax error', 'fakturo');
		}

		$list = array();
		foreach ($tokens as $token) {
			if (is_array($token)) {
				if ($token[0] !== T_WHITESPACE) {
					$list[] = array($token[0], $token[1]);
				}
			} else {
				$list[] = array(null, $token);
			}
		}

		$ok_tokens = array(
			T_INLINE_HTML, T_OPEN_TAG, T_OPEN_TAG_WITH_ECHO, T_CLOSE_TAG, T_VARIABLE, T_CONSTANT_ENCAPSED_STRING, T_ENCAPSED_AND_WHITESPACE,
			T_LNUMBER, T_DNUMBER, T_STRING, T_OBJECT_OPERATOR, T_DOUBLE_ARROW, T_IF, T_ELSEIF, T_ELSE, T_FOREACH, T_AS, T_ECHO, T_BREAK,
			T_ARRAY, T_ISSET, T_EMPTY, T_INC, T_DEC, T_COALESCE, T_BOOLEAN_AND, T_BOOLEAN_OR, T_LOGICAL_AND, T_LOGICAL_OR, T_LOGICAL_XOR,
			T_IS_EQUAL, T_IS_NOT_EQUAL, T_IS_IDENTICAL, T_IS_NOT_IDENTICAL, T_IS_SMALLER_OR_EQUAL, T_IS_GREATER_OR_EQUAL,
		);
		$ok_chars = array('(', ')', '[', ']', '{', '}', ',', ';', '.', '+', '-', '*', '/', '%', '!', '<', '>', '=', '?', ':', '"');
		$engine_functions = array('is_null', 'is_array', 'sizeof');
		$functions = array_merge(self::allowed_functions(), $engine_functions);

		$n = count($list);
		for ($i = 0; $i < $n; $i++) {
			list($id, $text) = $list[$i];
			$prev = $i ? $list[$i - 1] : array(null, '');
			$next = ($i + 1 < $n) ? $list[$i + 1] : array(null, '');

			if ($id === null) {
				if (!in_array($text, $ok_chars, true)) {
					return $text;
				}
				if ($text === '(') {
					$after_value = in_array($prev[0], array(T_VARIABLE, T_CONSTANT_ENCAPSED_STRING, T_ENCAPSED_AND_WHITESPACE, T_LNUMBER, T_DNUMBER), true)
						|| ($prev[0] === null && in_array($prev[1], array(')', ']', '}', '"'), true));
					if ($after_value) {
						return '(…)()';
					}
				}
				continue;
			}

			if (!in_array($id, $ok_tokens, true)) {
				return $text;
			}

			if ($id === T_VARIABLE) {
				if ($text === '$this') {
					if ($next[0] !== T_OBJECT_OPERATOR || !isset($list[$i + 2]) || strtolower($list[$i + 2][1]) !== 'var') {
						return $text;
					}
				} elseif ($text === '$GLOBALS' || strpos($text, '$_') === 0) {
					return $text;
				}
			} elseif ($id === T_OBJECT_OPERATOR) {
				if ($next[0] !== T_STRING) {
					return $text;
				}
			} elseif ($id === T_STRING) {
				$name = strtolower($text);
				if ($prev[0] === T_OBJECT_OPERATOR) {
					if ($next[1] === '(') {
						return $text . '()';
					}
				} elseif ($next[1] === '(' && $next[0] === null) {
					if (!in_array($name, $functions, true)) {
						return $text . '()';
					}
				} elseif (!in_array($name, array('true', 'false', 'null'), true)) {
					return $text;
				}
			}
		}
		return '';
	}

	public static function escape($value) {
		if (is_array($value)) {
			return array_map(array(__CLASS__, 'escape'), $value);
		}
		return is_string($value) ? esc_html($value) : $value;
	}

}

?>