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
		/**
		 * Renders HTML to PDF with the options every Fakturo document uses (remote files only from the allowed hosts, HTML5 parser). Returns the Dompdf object to call output() or stream() on; throws if dompdf fails.
		 */
		public static function render($html, $paper = 'A4', $orientation = 'portrait') {
			$pdf = self::getInstance();
			$pdf->getOptions()->set('isRemoteEnabled', true);
			$pdf->getOptions()->set('isHtml5ParserEnabled', true);
			$pdf->setPaper($paper, $orientation);
			$pdf->loadHtml($html, 'UTF-8');
			$pdf->render();
			return $pdf;
		}
		/**
		 * Sends the PDF to the browser to be shown in place.
		 */
		public static function stream($html, $filename = 'pdf.pdf') {
			self::render($html)->stream($filename, array('Attachment' => 0));
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