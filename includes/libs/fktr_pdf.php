<?php

	class fktr_pdf  {
		private static $instance = null;
		public static function includes() {
			require_once FAKTURO_PLUGIN_DIR.'includes/libs/dompdf/dompdf_config.inc.php'; 
		}
		public static function getInstance() {
			if (is_null(self::$instance)) {
				self::includes();
            	self::$instance = new DOMPDF();
        	}
        	return self::$instance;
		}
	}


	

?>