<?php
add_action( 'admin_init', 'fakturo_product_init' );

function fakturo_product_init() {
	global $pagenow;
	$labels = array( 
		'name' => __( 'Products', FAKTURO_TEXT_DOMAIN ),
		'singular_name' => __( 'Product', FAKTURO_TEXT_DOMAIN ),
		'add_new' => __( 'Add New', FAKTURO_TEXT_DOMAIN ),
		'add_new_item' => __( 'Add New Product', FAKTURO_TEXT_DOMAIN ),
		'edit_item' => __( 'Edit Product', FAKTURO_TEXT_DOMAIN ),
		'new_item' => __( 'New Product', FAKTURO_TEXT_DOMAIN ),
		'view_item' => __( 'View Product', FAKTURO_TEXT_DOMAIN ),
		'search_items' => __( 'Search Products', FAKTURO_TEXT_DOMAIN ),
		'not_found' => __( 'No products found', FAKTURO_TEXT_DOMAIN ),
		'not_found_in_trash' => __( 'No products found in Trash', FAKTURO_TEXT_DOMAIN ),
		'parent_item_colon' => __( 'Parent Product:', FAKTURO_TEXT_DOMAIN ),
		'menu_name' => __( 'Products', FAKTURO_TEXT_DOMAIN ),
	);
	$capabilities = array(
		'publish_post' => 'publish_fakturo_product',
		'publish_posts' => 'publish_fakturo_products',
		'read_post' => 'read_fakturo_product',
		'read_private_posts' => 'read_private_fakturo_products',
		'edit_post' => 'edit_fakturo_product',
		'edit_published_posts' => 'edit_published_fakturo_products',
		'edit_private_posts' => 'edit_private_fakturo_products',
		'edit_posts' => 'edit_fakturo_products',
		'edit_others_posts' => 'edit_others_fakturo_products',
		'delete_post' => 'delete_fakturo_product',
		'delete_posts' => 'delete_fakturo_products',
		'delete_published_posts' => 'delete_published_fakturo_products',
		'delete_private_posts' => 'delete_private_fakturo_products',
		'delete_others_posts' => 'delete_others_fakturo_products',
		);

	$args = array( 
		'labels' => $labels,
		'hierarchical' => false,
		'description' => 'Fakturo Products',
		'supports' => array( 'title', 'thumbnail',/* 'custom-fields' */),
		'register_meta_box_cb' => 'fakturo_product_meta_boxes',
		'public' => true,
		'show_ui' => true,
		'show_in_menu' => 'admin.php?page=fakturo/view/fakturo_admin.php',
		'menu_position' => 5,
		'menu_icon' => '/images/icon_20.png',
		'show_in_nav_menus' => false,
		'publicly_queryable' => false,
		'exclude_from_search' => false,
		'has_archive' => false,
		'query_var' => true,
		'can_export' => true,
		'rewrite' => true
	);

	register_post_type( 'fakturo_product', $args );


	add_filter(	'fakturo_check_product', 'fakturo_check_product',10,1);
	add_action('save_post',  'fakturo_save_product_data');
	// Products list
	add_filter('manage_edit-fakturo_client_columns' ,  'set_edit_fakturo_client_columns');
	add_action('manage_fakturo_client_posts_custom_column','custom_fakturo_client_column',10,2);
	add_filter("manage_edit-fakturo_client_sortable_columns",  "sortable_columns" );

	if( ($pagenow == 'edit.php') && (isset($_GET['post_type']) && $_GET['post_type'] == 'fakturo_product') ) {
		add_filter('post_row_actions' ,  'fakturo_custom_post_quick_actions', 10, 2);
		add_action('pre_get_posts',  'column_orderby');
		add_action('pre_get_posts',  'query_set_only_author' );
	}	

	if( ($pagenow == 'post-new.php' || $pagenow == 'post.php') ) {
		add_action('parent_file',   'facturo_product_tax_menu_correction');
		add_filter('enter_title_here', 'fakturo_product_name_placeholder',10,2);
		add_action('admin_print_styles-post.php', 'fakturo_products_admin_styles');
		add_action('admin_print_styles-post-new.php', 'fakturo_products_admin_styles');
		add_action('admin_print_scripts-post.php', 'fakturo_products_admin_scripts');
		add_action('admin_print_scripts-post-new.php', 'fakturo_products_admin_scripts');
	}
		
}

// component

// highlight the proper top level menu
function facturo_product_tax_menu_correction($parent_file) {
	global $current_screen;
	if ($current_screen->post_type == "fakturo_product") {
		$parent_file = 'fakturo/view/fakturo_admin.php';
	}
	return $parent_file;
}

function fakturo_product_name_placeholder( $title_placeholder , $post ) {
	if($post->post_type == 'fakturo_product')
		$title_placeholder = __('Enter Product name here', FAKTURO_TEXT_DOMAIN );
	return $title_placeholder;
}

function fakturo_save_product_data( $post_id ) {
	return fakturo_save_custom_post_data($post_id, 'fakturo_product', 'fakturo_check_product', 'fakturo_contact_nonce', 'edit-contact');
}

function fakturo_product_meta_boxes() {
	global $post,$product_data;
	$product_data = fakturo_get_custom_post_data($post->ID, 'fakturo_check_product');
	
	add_action('wp_ajax_webcam_shot', 'fakturo_ajax_webcam_shot');
	
	// Remove Custom Fields Metabox
	remove_meta_box( 'postimagediv', 'fakturo_product', 'side' );
	add_meta_box('postimagediv', __('Product Image', FAKTURO_TEXT_DOMAIN ), 'Fakturo_post_thumbnail_meta_box', 'fakturo_product', 'side', 'high');
	add_meta_box( 'fakturo-seller-box', __('Assign Seller', FAKTURO_TEXT_DOMAIN ), 'Fakturo_seller_box','fakturo_product','side', 'high' );
	add_meta_box( 'fakturo-data-box', __('Complete Product Data', FAKTURO_TEXT_DOMAIN ), 'Fakturo_product_data_box','fakturo_product','normal', 'default' );

	add_meta_box( 'fakturo-price-box', __('Price', FAKTURO_TEXT_DOMAIN ), 'Fakturo_product_price_box','fakturo_product','normal', 'default' );
	add_meta_box( 'fakturo-stock-box', __('Stock', FAKTURO_TEXT_DOMAIN ), 'Fakturo_product_stock_box','fakturo_product','normal', 'default' );
	// add_meta_box( 'fakturo-options-box', __('Product Contacts', FAKTURO_TEXT_DOMAIN ), 'Fakturo_client_options_box','fakturo_product','normal', 'default' );
}

function fakturo_products_admin_styles(){
	global $post;
	if($post->post_type != 'fakturo_product') return $post->ID;
	wp_enqueue_style('fakturo-sprite',FAKTURO_URI .'css/sprite.css');	
	add_action('admin_head', 'fakturo_custom_post_head_style');
}

function fakturo_products_admin_scripts(){
	global $post;
	if($post->post_type != 'fakturo_product') return $post->ID;
	wp_register_script('jquery-vsort', FAKTURO_URI .'js/jquery.vSort.min.js', array('jquery'));
	wp_enqueue_script('jquery-vsort');
	wp_register_script('webcam', FAKTURO_URI .'libraries/webcamjs-master/webcam.min.js', array('jquery'));
	wp_enqueue_script('webcam');
	add_action('admin_head', 'fakturo_custom_post_head_scripts');
	add_action('admin_head', 'fakturo_products_head_scripts');
}

function fakturo_products_head_scripts() {
	global $post, $wp_locale, $locale;
	if($post->post_type != 'fakturo_product') return $post->ID;
	$post->post_password = '';
	$visibility = 'public';
	$visibility_trans = __('Public');

	?>
	<script type="text/javascript" language="javascript">
	jQuery(document).ready(function($){
		$('#publish').val('<?php _e('Save Product', FAKTURO_TEXT_DOMAIN ); ?>');
		$('#submitdiv h3 span').text('<?php _e('Update', FAKTURO_TEXT_DOMAIN ); ?>');
		// remove visibility
		$('#visibility').hide();

		// remove channels Most used box
		$('#channel-tabs').remove();
		$('#channel-pop').remove();
		// remove channels Ajax Quick Add 
		$('#channel-adder').remove();
		//-----Click on channel  (Allows just one)
		$(document).on("click", '#channelchecklist input[type=checkbox]', function(event) { 
			var $current = $(this).prop('checked') ; //true or false
			$('#channelchecklist input[type=checkbox]').prop('checked', false);
			$(this).prop('checked', $current );
			//if( $current ){ }
		});

		// remove segments Most used box
		$('#segment-tabs').remove();
		$('#segment-pop').remove();
		// remove segments Ajax Quick Add 
		$('#segment-adder').remove();
		//-----Click on segment (Allows just one)
		$(document).on("click", '#segmentchecklist input[type=checkbox]', function(event) { 
			var $current = $(this).prop('checked') ; //true or false
			$('#segmentchecklist input[type=checkbox]').prop('checked', false);
			$(this).prop('checked', $current );
			//if( $current ){ }
		});

		// remove interests Most used box
		$('#interest-tabs').remove();
		$('#interest-pop').remove();
		// remove interests Ajax Quick Add 
		$('#interest-adder').remove();

		$('#addmoreuc').click(function() {
			oldval = $('#ucfield_max').val();
			jQuery('#ucfield_max').val( parseInt(jQuery('#ucfield_max').val(),10) + 1 );
			newval = $('#ucfield_max').val();
			uc_new= $('.uc_new_field').clone();
			$('div.uc_new_field').removeClass('uc_new_field');
			$('div#uc_ID'+oldval).fadeIn();
			$('input[name="uc_description['+oldval+']"]').focus();
			uc_new.attr('id','uc_ID'+newval);
			$('input', uc_new).eq(0).attr('name','uc_description['+ newval +']');
			$('input', uc_new).eq(1).attr('name','uc_phone['+ newval +']');
			$('.delete', uc_new).eq(0).attr('onclick', "delete_user_contact('#uc_ID"+ newval +"');");
			$('#user_contacts').append(uc_new);
			$('#user_contacts').vSort();
		});

		$('#addmoreprice').click(function() {
			var newrow = $('.price-row:eq(0)').clone();
			$('select', newrow).attr('name','price_scale['+ $('.price-row').length +']');
			$('select', newrow).val([]);
			$('#price-table').append(newrow);
		});	
		
		$('#addmorestock').click(function() {
			var newrow = $('.stock-row:eq(0)').clone();
			var rowNumber = $('.stock-row').length;
			$('select', newrow).attr('name','stock_location['+ rowNumber +']');
			$('select', newrow).val($('select option:first', newrow).val());
			$('input:eq(0)', newrow).attr('name','stock_quantity['+ rowNumber +']');
			$('input:eq(1)', newrow).attr('name','stock_order['+ rowNumber +']');
			$('input:eq(2)', newrow).attr('name','stock_desc['+ rowNumber +']');
			$('input:eq(3)', newrow).attr('name','stock_cost['+ rowNumber +']');
			$('input:eq(4)', newrow).attr('name','stock_date['+ rowNumber +']');
			$('input', newrow).val('');
			$('#stock-table').append(newrow);
		});	
		
		jQuery( "form#post #publish" ).hide();
		jQuery( "form#post #publish" ).after("<input type=\'button\' value=\'<?php _e('Save Product', FAKTURO_TEXT_DOMAIN ); ?>\' class=\'sb_publish button-primary\' /><span class=\'sb_js_errors\'></span>");

		jQuery( ".sb_publish" ).click(function() {
			if ($('#cuit_validation').hasClass('cuit_err')) {
				jQuery(".sb_js_errors").text("<?php _e('There was an error on the page and therefore this page can not yet be published.', FAKTURO_TEXT_DOMAIN ); ?>");
			} else {
				jQuery( "form#post #publish" ).click();
			}
		});

	});		// jQuery
	function delete_user_contact(row_id){
		jQuery(row_id).fadeOut(); 
		jQuery(row_id).remove();
		jQuery('#msgdrag').html('<?php _e('Update Product to save changes.', FAKTURO_TEXT_DOMAIN ); ?>').fadeIn();
	}
	
	</script>
	<?php
}

function fakturo_check_product($options) {
	$fieldsArray = array('cost', 'reference', 'internal', 'manufacturers', 'description', 'short', 'min',
		'min_alert', 'packaging', 'unit', 'note', 'origin', 'provider', 'familia', 'origin', 'moneda', 'product_type', 'tax', 'price_scale', 'stock');
	if (isset($options['price_scale']) && is_array($options['price_scale'])) {
		$options['price_scale'] = json_encode($options['price_scale']);
	}
	if (isset($options['stock_location']) && is_array($options['stock_location'])) {
		$countStock = count($options['stock_location']);
		$stock = array();

		for ($i = 0; $i < $countStock; $i++) {
			if ($options['stock_location'][$i] != NULL && $options['stock_quantity'][$i] != NULL && is_numeric($options['stock_quantity'][$i])) {
				$stock[] = array('location' => $options['stock_location'][$i], 'quantity' => $options['stock_quantity'][$i], 'order' => $options['stock_order'][$i],
				 'desc' => $options['stock_desc'][$i], 'cost' => $options['stock_cost'][$i], 'date' => $options['stock_date'][$i]);
			}			
		}
		$options['stock'] = json_encode($stock);
	}
	foreach ($fieldsArray as $field) {
		$product_data[$field]	= (!isset($options[$field])) ? '' : $options[$field];
	}

	$user_contacts = (!isset($options['user_contacts']))? Array() : $options['user_contacts'];
	if(isset($options['uc_description'])) {
		foreach($options['uc_description'] as $id => $cf_value) {       
			$uc_description = esc_attr($options['uc_description'][$id]);
			$uc_phone = esc_attr($options['uc_phone'][$id]);
			$uc_email = esc_attr($options['uc_email'][$id]);
			$uc_position = esc_attr($options['uc_position'][$id]);
			$uc_address = esc_attr($options['uc_address'][$id]);
			if(!empty($uc_description))  {
				$user_contacts['description'][]=$uc_description ;
				$user_contacts['phone'][]=$uc_phone ;
				$user_contacts['email'][]=$uc_email ;
				$user_contacts['position'][]=$uc_position ;
				$user_contacts['address'][]=$uc_address ;
			}
		}
	}
	if(!isset($user_contacts['description'])) {
		$user_contacts = array(
			'description'=>array(''),
			'phone'=>array(''),
			'email'=>array(''),
			'position'=>array(''),
			'address'=>array(''),	
		);
	}
	$product_data['user_contacts']= $user_contacts;
	$product_data['user_aseller']= (isset($options['user_aseller']) && !empty($options['user_aseller']) ) ? $options['user_aseller'] : 0 ;

	return $product_data;
}

function Fakturo_product_data_box( $post ) {  
	global $post, $product_data;
	wp_nonce_field( 'edit-contact', 'fakturo_contact_nonce' ); 
	?>
	<table class="form-table">
	<tbody>
	<tr class="user-address-wrap">
		<th><label for="cost"><?php _e("Cost", FAKTURO_TEXT_DOMAIN ) ?>	</label></th>
		<td><input type="text" name="cost" id="cost" value="<?php echo $product_data['cost'] ?>" class="regular-text"></td>
	</tr>
	<tr class="user-facebook-wrap">
		<th><label for="provider"><?php _e("Provider", FAKTURO_TEXT_DOMAIN ) ?>	</label></th>
		<td><?php FakturoBaseComponent::selectCustomPostType('fakturo_provider', 'provider', $product_data); ?></td>
	</tr>
	<tr class="user-address-wrap">
		<th><label for="familia"><?php _e("Familia", FAKTURO_TEXT_DOMAIN ) ?></label></th>
		<td>
			<?php FakturoBaseComponent::selectArrayValue(array('Servicios', 'Suscripción a Pagina Web', 'Gestiones Online'), 'familia', $product_data); ?>
		</td>
	</tr>
	<tr class="user-facebook-wrap">
		<th><label for="product_type"><?php _e("Product Type", FAKTURO_TEXT_DOMAIN ) ?>	</label></th>
		<td><?php fakturo_client_select_data('fakturo_product_types', 'product_type', $product_data); ?></td>
	</tr>
	<tr class="user-facebook-wrap">
		<th><label for="tax"><?php _e("Tax", FAKTURO_TEXT_DOMAIN ) ?>	</label></th>
		<td><?php fakturo_client_select_data('fakturo_taxes', 'tax', $product_data); ?></td>
	</tr>
	<tr class="user-address-wrap">
		<th><label for="moneda"><?php _e("Moneda", FAKTURO_TEXT_DOMAIN ) ?></label></th>
		<td><?php FakturoBaseComponent::selectArrayValue(array('Peso Argentino', 'Dólar'), 'moneda', $product_data); ?>
		</td>
	</tr>
	<tr class="user-address-wrap">
		<th><label for="reference"><?php _e("Reference", FAKTURO_TEXT_DOMAIN ) ?></label></th>
		<td><input type="text" name="reference" id="reference" value="<?php echo $product_data['reference'] ?>" class="regular-text"></td>
	</tr>
	<tr class="user-address-wrap">
		<th><label for="internal"><?php _e("Internal code", FAKTURO_TEXT_DOMAIN ) ?></label></th>
		<td><input type="text" name="internal" id="internal" value="<?php echo $product_data['internal'] ?>" class="regular-text"></td>
	</tr>
	<tr class="user-address-wrap">
		<th><label for="manufacturers"><?php _e("Manufacturers code", FAKTURO_TEXT_DOMAIN ) ?></label></th>
		<td><input type="text" name="manufacturers" id="manufacturers" value="<?php echo $product_data['manufacturers'] ?>" class="regular-text"></td>
	</tr>
	<tr class="user-address-wrap">
		<th><label for="description"><?php _e("Description", FAKTURO_TEXT_DOMAIN ) ?></label></th>
		<td>
			<textarea style="width:95%;" rows="4" name="description" id="description"><?php echo $product_data['description'] ?></textarea>
		</td>
	</tr>
	<tr class="user-address-wrap">
		<th><label for="short"><?php _e("Short description", FAKTURO_TEXT_DOMAIN ) ?></label></th>
		<td><input type="text" name="short" id="short" value="<?php echo $product_data['short'] ?>" class="regular-text"></td>
	</tr>
	<tr class="user-address-wrap">
		<th><label for="min"><?php _e("Minimal stock", FAKTURO_TEXT_DOMAIN ) ?></label></th>
		<td><input type="number" name="min" id="min" value="<?php echo $product_data['min'] ?>" class="regular-text"></td>
	</tr>
	<tr class="user-facebook-wrap">
		<th><label for="min_alert"><?php _e("Minimal stock alert", FAKTURO_TEXT_DOMAIN ) ?>	</label></th>
		<td><input id="min_alert" type="checkbox" name="min_alert" value="1" <?php if ($product_data['min_alert']) { echo 'checked="checked"'; } ?>></td>
	</tr>
	<tr class="user-facebook-wrap">
		<th><label for="tax"><?php _e("Packaging", FAKTURO_TEXT_DOMAIN ) ?>	</label></th>
		<td><?php fakturo_client_select_data('fakturo_packagings', 'packaging', $product_data); ?></td>
	</tr>
	<tr class="user-address-wrap">
		<th><label for="unit"><?php _e("Units per package", FAKTURO_TEXT_DOMAIN ) ?></label></th>
		<td><input type="text" name="unit" id="unit" value="<?php echo $product_data['unit'] ?>" class="regular-text"></td>
	</tr>
	<tr class="user-address-wrap">
		<th><label for="note"><?php _e("Notes", FAKTURO_TEXT_DOMAIN ) ?></label></th>
		<td><textarea style="width:95%;" rows="4" name="note" id="note"><?php echo $product_data['note'] ?></textarea></td>
	</tr>
	<tr class="user-address-wrap">
		<th><label for="origin"><?php _e("Origin", FAKTURO_TEXT_DOMAIN ) ?></label></th>
		<td><?php FakturoBaseComponent::selectArrayValue(array('China', 'Brasil', 'Japón', 'Taiwán', 'Estados Unidos', 'Argentina'), 'origin', $product_data); ?>
	</tr>
	</tbody></table>
	<?php
}

function Fakturo_product_price_box() {
	global $post, $product_data;
	?>
	<table class="form-table">
		<tbody id="price-table">
			<?php FakturoBaseComponent::showSelectTaxonomiesDataArrayValues('fakturo_price_scales', 'price_scale', $product_data);  ?>
		</tbody>
	</table>
	<div id="paging-box">		  
		<a href="JavaScript:void(0);" class="button-primary add" id="addmoreprice" style="font-weight: bold; text-decoration: none;"> <?php _e('Add Price', FAKTURO_TEXT_DOMAIN  ); ?>.</a>
		<label id="msgdrag"></label>
	</div>
	<?php
}

function fakturoCountStockLocation($items, $id) {
	$count = 0;
	if (is_array($items) && count($items) > 0) {
		foreach ($items as $key => $item) {
			if ($item['location'] == $id) {
				$count += $item['quantity'];
			}
		}
	}
	return $count;
}

function Fakturo_product_stock_box() {
	global $post, $product_data;

	$data = get_terms('fakturo_locations', 'hide_empty=0');
	$items = array();
	if (isset($product_data['stock'])) {
		$items = json_decode($product_data['stock'], true);
	}
	?>
	<style>
		#stock-table .stock-item {width: 16%;display: inline-block;}
		#stock-table .stock-item input {width: 100%;}
		.stock-label .stock-item.label {font-weight: bold;}
		.stock-row .stock-item input {width: 100%;}
	</style>
	<table class="form-table">
		<tbody>
			<?php foreach ($data as $key => $value) { ?>
				<tr class="user-address-wrap">
					<th><label><?php echo $value->name; ?></label></th>
					<td><?php echo fakturoCountStockLocation($items, $value->term_id); ?></td>
				</tr>
			<?php } ?>
		</tbody>
	</table>

	<div id="stock-table">
		<div class="stock-label">
			<div class="stock-item label"><?php _e('Location', FAKTURO_TEXT_DOMAIN) ?></div>
			<div class="stock-item label"><?php _e('Quantity', FAKTURO_TEXT_DOMAIN) ?></div>
			<div class="stock-item label"><?php _e('Order Number', FAKTURO_TEXT_DOMAIN) ?></div>
			<div class="stock-item label"><?php _e('Description', FAKTURO_TEXT_DOMAIN) ?></div>
			<div class="stock-item label"><?php _e('Cost', FAKTURO_TEXT_DOMAIN) ?></div>
			<div class="stock-item label"><?php _e('Date', FAKTURO_TEXT_DOMAIN) ?></div>
		</div>
		<?php 		
		if (is_array($items) && count($items) > 0) {
			foreach ($items as $key => $item) {
				echo '<div class="stock-row">';
				echo '<div class="stock-item">';
				echo '<select name="stock_location[' . $key . ']">';
				foreach ($data as $key2 => $value) {
					if (isset($item['location']) && $value->term_id == $item['location']) {
						$selected = " selected ";
					} else {
						$selected = "";
					}
					echo '<option '. $selected .' value="' . $value->term_id . '">' . $value->name . '</option>';
				}
				echo '</select></div>';

				echo '<div class="stock-item"><input type="text" value="' . $item['quantity'] . '" name="stock_quantity[' . $key . ']"></div>';
				echo '<div class="stock-item"><input type="text" value="' . $item['order'] . '" name="stock_order[' . $key . ']"></div>';
				echo '<div class="stock-item"><input type="text" value="' . $item['desc'] . '" name="stock_desc[' . $key . ']"></div>';
				echo '<div class="stock-item"><input type="text" value="' . $item['cost'] . '" name="stock_cost[' . $key . ']"></div>';
				echo '<div class="stock-item"><input type="text" value="' . $item['date'] . '" name="stock_date[' . $key . ']"></div>';
				echo '</div>';
			}
		} else { ?>			
			<div class="stock-row">
				<div class="stock-item">
					<select name="stock_location[0]">
						<?php foreach ($data as $key => $value) { ?>
							<option value="<?php echo $value->term_id; ?>"><?php echo $value->name; ?></option>
						<?php } ?>
					</select>
				</div>
				<div class="stock-item"><input type="text" name="stock_quantity[0]"></div>
				<div class="stock-item"><input type="text" name="stock_order[0]"></div>
				<div class="stock-item"><input type="text" name="stock_desc[0]"></div>
				<div class="stock-item"><input type="text" name="stock_cost[0]"></div>
				<div class="stock-item"><input type="date" name="stock_date[0]"></div>
			</div>
		<?php
		}
		?>
	</div>

	<div id="paging-box">		  
		<a href="JavaScript:void(0);" class="button-primary add" id="addmorestock" style="font-weight: bold; text-decoration: none;"> <?php _e('Add Stock', FAKTURO_TEXT_DOMAIN  ); ?>.</a>
		<label id="msgdrag"></label>
	</div>
	<?php
}