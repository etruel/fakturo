<?php


// Exit if accessed directly
if (!defined('ABSPATH'))  {
	exit;
}

if ( ! class_exists('fktr_tax_currency') ) :
class fktr_tax_currency {
	
	function __construct() {
		add_action('fktr_currencies_edit_form_fields', array('fktr_tax_currency', 'edit_form_fields'));
		add_action('fktr_currencies_add_form_fields',  array('fktr_tax_currency', 'add_form_fields'));
		add_action('edited_fktr_currencies', array('fktr_tax_currency', 'save_fields'), 10, 2);
		add_action('created_fktr_currencies', array('fktr_tax_currency','save_fields'), 10, 2);
		
		add_filter('manage_edit-fktr_currencies_columns', array('fktr_tax_currency', 'columns'), 10, 3);
		add_filter('manage_fktr_currencies_custom_column',  array('fktr_tax_currency', 'theme_columns'), 10, 3);
		
		
	}
	public static function add_form_fields() {
		$echoHtml = '
		<style type="text/css">.form-field.term-parent-wrap,.form-field.term-slug-wrap, .form-field label[for="parent"], .form-field #parent {display: none;}  .form-field.term-description-wrap { display:none;} .inline.hide-if-no-js{ display:none;}</style>
		<div class="form-field" id="plural_div">
			<label for="term_meta[plural]">'.__( 'Plural', FAKTURO_TEXT_DOMAIN ).'</label>
			<input type="text" name="term_meta[plural]" id="term_meta[plural]" value="">
			<p class="description">'.__( 'Enter name plural of the currency', FAKTURO_TEXT_DOMAIN ).'</p>
		</div>
		<div class="form-field" id="symbol_div">
			<label for="term_meta[symbol]">'.__( 'Symbol', FAKTURO_TEXT_DOMAIN ).'</label>
			<input style="width: 60px;text-align: right; padding-right: 0px; " type="text" name="term_meta[symbol]" id="term_meta[symbol]" value="">
			<p class="description">'.__( 'Enter a symbol like $', FAKTURO_TEXT_DOMAIN ).'</p>
		</div>
		<div class="form-field" id="rate_div">
			<label for="term_meta[rate]">'.__( 'Rate', FAKTURO_TEXT_DOMAIN ).'</label>
			<input style="width: 60px;text-align: right; padding-right: 0px; " type="number" name="term_meta[rate]" id="term_meta[rate]" value="0">
			<p class="description">'.__( 'Enter a rate', FAKTURO_TEXT_DOMAIN ).'</p>
		</div>
		<div class="form-field" id="reference_div">
			<label for="term_meta[reference]">'.__( 'Reference', FAKTURO_TEXT_DOMAIN ).'</label>
			<input type="text" name="term_meta[reference]" id="term_meta[reference]" value="">
			<p class="description">'.__( 'Enter a reference', FAKTURO_TEXT_DOMAIN ).'</p>
		</div>
		
		';
		echo $echoHtml;
	}
	public static function edit_form_fields($term) {
	
		$t_id = $term->term_id;
		$term->description = trim($term->description);
		$term->description = utf8_encode($term->description);
		$term->description = str_replace('&quot;', '"', $term->description);
		$term_meta = json_decode($term->description);
	
		
		$echoHtml = '<style type="text/css">.form-field.term-parent-wrap, .form-field.term-slug-wrap {display: none;} .form-field.term-description-wrap { display:none;}  </style>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="term_meta[plural]">'.__( 'Plural', FAKTURO_TEXT_DOMAIN ).'</label>
			</th>
			<td>
				<input type="text" name="term_meta[plural]" id="term_meta[plural]" value="'.$term_meta->plural.'">
				<p class="description">'.__( 'Enter name plural of the currency', FAKTURO_TEXT_DOMAIN ).'</p>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="term_meta[symbol]">'.__( 'Symbol', FAKTURO_TEXT_DOMAIN ).'</label>
			</th>
			<td>
				<input type="text" style="width: 60px;text-align: right; padding-right: 0px; " name="term_meta[symbol]" id="term_meta[symbol]" value="'.$term_meta->symbol.'">
				<p class="description">'.__( 'Enter a symbol like $', FAKTURO_TEXT_DOMAIN ).'</p>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="term_meta[rate]">'.__( 'Rate', FAKTURO_TEXT_DOMAIN ).'</label>
			</th>
			<td>
				<input style="width: 60px;text-align: right; padding-right: 0px; " type="number" name="term_meta[rate]" id="term_meta[rate]" value="'.$term_meta->rate.'">
				<p class="description">'.__( 'Enter a rate', FAKTURO_TEXT_DOMAIN ).'</p>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="term_meta[reference]">'.__( 'Reference', FAKTURO_TEXT_DOMAIN ).'</label>
			</th>
			<td>
				<input type="text" name="term_meta[reference]" id="term_meta[reference]" value="'.$term_meta->reference.'">
				<p class="description">'.__( 'Enter a reference', FAKTURO_TEXT_DOMAIN ).'</p>
			</td>
		</tr>
		';
		echo $echoHtml;
		
	}
	public static function columns($columns) {
		$new_columns = array(
			'cb' => '<input type="checkbox" />',
			'name' => __('Name', FAKTURO_TEXT_DOMAIN),
			'symbol' => __('Symbol', FAKTURO_TEXT_DOMAIN),
			'rate' => __('Rate', FAKTURO_TEXT_DOMAIN)
		);
		return $new_columns;
	}
	public static function theme_columns($out, $column_name, $term_id) {
		$term = get_term( $term_id, 'fktr_currencies' );
		$term->description = trim($term->description);
		$term->description = utf8_encode($term->description);
		$term->description = str_replace('&quot;', '"', $term->description);
		$term_meta = json_decode($term->description);
		switch ($column_name) {
			case 'symbol': 
				$out = esc_attr( $term_meta->symbol);
				break;

			case 'rate': 
				$out = esc_attr( $term_meta->rate);
				break;

			default:
				break;
		}
		return $out;    
	}
	public static function save_fields($term_id, $tt_id) {
		global $wpdb;
		if (isset( $_POST['term_meta'])) {
			
			$wpdb->update( $wpdb->term_taxonomy, array('description' => json_encode($_POST['term_meta'])), array( 'term_taxonomy_id' => $tt_id ) );
			unset($_POST['term_meta']);
		}
		
	}
	
}
endif;

$fktr_tax_currency = new fktr_tax_currency();

?>