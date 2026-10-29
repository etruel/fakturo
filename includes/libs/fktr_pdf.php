<?php
	// reference the Dompdf namespace
	use Dompdf\Dompdf;
	use Dompdf\Options;

	class fktr_pdf  {

		private static $instance = null;
		public static function includes() {
			require_once FAKTURO_PLUGIN_DIR.'includes/libs/vendor/autoload.php'; 
		}
		/**
		 * Remote images and styles are fetched only from the site itself (and the hosts added with the fktr_pdf_allowed_hosts filter), so a template or a client field cannot make the server request other addresses.
		 */
		public static function remote_hosts() {
			$hosts = array();
			$upload = wp_upload_dir(null, false);
			foreach (array(home_url(), site_url(), $upload['baseurl']) as $url) {
				$host = wp_parse_url($url, PHP_URL_HOST);
				if ($host) {
					$hosts[] = strtolower($host);
				}
			}
			return array_values(array_unique(array_filter((array) apply_filters('fktr_pdf_allowed_hosts', $hosts))));
		}
		public static function getInstance($options = null) {
			if (is_null(self::$instance)) {
				if(!class_exists('Dompdf\Dompdf'))
					self::includes();
				// instantiate and use the dompdf class
            	self::$instance = new Dompdf($options);
				self::$instance->getOptions()->setAllowedRemoteHosts(self::remote_hosts());
        	}
        	return self::$instance;
		}
	}
?>