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

	public static function selectCustomPostType($type, $name, $custom_post_data) {
		$args=array(
		  'post_type' => $type,
		  'post_status' => 'publish',
		  'posts_per_page' => -1,
		  'caller_get_posts'=> 1
		 );

		echo "<select id='$name' name='$name'><option></option>";
		$my_query = null;
		$my_query = new WP_Query($args);
		if( $my_query->have_posts() ) {
		  while ($my_query->have_posts()) : $my_query->the_post(); ?>
		    <option <?php if (get_the_ID() == $custom_post_data[$name]) {
		    	echo " selected ";
		    } ?> value="<?php the_ID(); ?>"><?php the_title(); ?></option>
		    <?php
		  endwhile;
		}
		wp_reset_query();
		echo "</select>";
	}

	public static function selectArrayValue($array, $name, $custom_post_data) {
		echo "<select id='$name' name='$name'><option></option>";
		foreach ($array as $key => $value) {echo "string";
			echo "<option value='$key' ";
			if (isset($custom_post_data[$name]) && $custom_post_data[$name] != NULL && $key == $custom_post_data[$name]) {
				echo " selected ";
			}
			echo " >$value</option>";
		}
		echo "</select>";
	}
}

?>