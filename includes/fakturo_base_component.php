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

	public static function getCurrencies() {
		return apply_filters('FakturoSettingCurrency', array(
			'USD' => 'US Dollars ($)',
			'EUR' => 'Euros (€)',
			'GBP' => 'Pounds Sterling (£)',
			'AUD' => 'Australian Dollars ($)',
			'BRL' => 'Brazilian Real (R$)',
			'CAD' => 'Canadian Dollars ($)',
			'CZK' => 'Czech Koruna',
			'DKK' => 'Danish Krone',
			'HKD' => 'Hong Kong Dollar ($)',
			'HUF' => 'Hungarian Forint',
			'ILS' => 'Israeli Shekel (₪)',
			'JPY' => 'Japanese Yen (¥)',
			'MYR' => 'Malaysian Ringgits',
			'MXN' => 'Mexican Peso ($)',
			'NZD' => 'New Zealand Dollar ($)',
			'NOK' => 'Norwegian Krone',
			'PHP' => 'Philippine Pesos',
			'PLN' => 'Polish Zloty',
			'SGD' => 'Singapore Dollar ($)',
			'SEK' => 'Swedish Krona',
			'CHF' => 'Swiss Franc',
			'TWD' => 'Taiwan New Dollars',
			'THB' => 'Thai Baht (฿)',
			'INR' => 'Indian Rupee',
			'TRY' => 'Turkish Lira',
			'RIAL' => 'Iranian Rial',
			'RUB' => 'Russian Rubles'
		));
	}
}

?>