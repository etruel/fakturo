<?php

/**
* 
*/
class FakturoBaseComponent
{	
	public static function fakturoGetAction() {
		if (isset($_GET['action'])) {
			return $_GET['action'];
		}
		return 'list';
	}

	public static function fakturoGetId() {
		if (isset($_GET['id'])) {
			return $_GET['id'];
		}
		return NULL;
	}
}

?>