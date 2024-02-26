<?php
	// reference the Dompdf namespace
	use Dompdf\Dompdf;
	use Dompdf\Options;

	class fktr_pdf  {

		private static $instance = null;
		public static function includes() {
			require_once FAKTURO_PLUGIN_DIR.'includes/libs/vendor/autoload.php'; 
		}
		public static function getInstance($options = null) {
			if (is_null(self::$instance)) {
				self::includes();
				// instantiate and use the dompdf class
            	self::$instance = new Dompdf($options);
        	}
        	return self::$instance;
		}
	}
?>