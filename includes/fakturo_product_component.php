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

	// category taxonomy
	$labels_model = array(
		'name'                       => _x( 'Categories', 'Categories' ),
		'singular_name'              => _x( 'Category', 'Category' ),
		'search_items'               => __( 'Search Categories' ),
		'popular_items'              => __( 'Popular Categories' ),
		'all_items'                  => __( 'All Categories' ),
		'parent_item'                => __( 'Parent Category' ),
		'parent_item_colon'          => null,
		'edit_item'                  => __( 'Edit Category' ),
		'update_item'                => __( 'Update Category' ),
		'add_new_item'               => __( 'Add New Category' ),
		'new_item_name'              => __( 'New Category Name' ),
		'separate_items_with_commas' => __( 'Separate categories with commas' ),
		'add_or_remove_items'        => __( 'Add or remove categories' ),
		'choose_from_most_used'      => __( 'Choose from the most used categories' ),
		'not_found'                  => __( 'No categories found.' ),
		'menu_name'                  => __( 'Categories' ),
	);

	$args_model = array(
		'hierarchical'          => true,
		'labels'                => $labels_model,
		'show_ui'               => true,
		'show_admin_column'     => true,
		'query_var'             => true,
		'rewrite'               => array( 'slug' => 'fakturo-category' ),
	);

	register_taxonomy(
		'fakturo_category',
		'fakturo_product',
		$args_model
	);

	// model taxonomy
	$labels_model = array(
		'name'                       => _x( 'Models', 'Models' ),
		'singular_name'              => _x( 'Model', 'Model' ),
		'search_items'               => __( 'Search Models' ),
		'popular_items'              => __( 'Popular Models' ),
		'all_items'                  => __( 'All Models' ),
		'parent_item'                => null,
		'parent_item_colon'          => null,
		'edit_item'                  => __( 'Edit Model' ),
		'update_item'                => __( 'Update Model' ),
		'add_new_item'               => __( 'Add New Model' ),
		'new_item_name'              => __( 'New Model Name' ),
		'separate_items_with_commas' => __( 'Separate models with commas' ),
		'add_or_remove_items'        => __( 'Add or remove models' ),
		'choose_from_most_used'      => __( 'Choose from the most used models' ),
		'not_found'                  => __( 'No models found.' ),
		'menu_name'                  => __( 'Models' ),
	);

	$args_model = array(
		'hierarchical'          => false,
		'labels'                => $labels_model,
		'show_ui'               => true,
		'show_admin_column'     => true,
		'query_var'             => true,
		'rewrite'               => array( 'slug' => 'fakturo-model' ),
	);

	register_taxonomy(
		'fakturo_model',
		'fakturo_product',
		$args_model
	);

	$labels_stock = array(
		'name'                       => _x( 'Stocks', 'Stocks' ),
		'singular_name'              => _x( 'Stock', 'Stock' ),
		'search_items'               => __( 'Search Stocks' ),
		'popular_items'              => __( 'Popular Stocks' ),
		'all_items'                  => __( 'All Stocks' ),
		'parent_item'                => null,
		'parent_item_colon'          => null,
		'edit_item'                  => __( 'Edit Stock' ),
		'update_item'                => __( 'Update Stock' ),
		'add_new_item'               => __( 'Add New Stock' ),
		'new_item_name'              => __( 'New Stock Name' ),
		'separate_items_with_commas' => __( 'Separate stocks with commas' ),
		'add_or_remove_items'        => __( 'Add or remove stocks' ),
		'choose_from_most_used'      => __( 'Choose from the most used stocks' ),
		'not_found'                  => __( 'No stocks found.' ),
		'menu_name'                  => __( 'Stocks' ),
	);

	$args_stock = array(
		'hierarchical'          => false,
		'labels'                => $labels_stock,
		'show_ui'               => true,
		'show_admin_column'     => true,
		'query_var'             => true,
		'rewrite'               => array( 'slug' => 'fakturo-stock' ),
	);

	register_taxonomy(
		'fakturo_stock',
		'',
		$args_stock
	);

	add_filter(	'fakturo_check_product', 'fakturo_check_product',10,1);
	add_action('save_post',  'fakturo_save_product_data');
	// Products list
	add_filter('manage_edit-fakturo_client_columns' ,  'set_edit_fakturo_client_columns');
	add_action('manage_fakturo_client_posts_custom_column','custom_fakturo_client_column',10,2);
	add_filter("manage_edit-fakturo_client_sortable_columns",  "sortable_columns" );

	if( ($pagenow == 'edit.php') && (isset($_GET['post_type']) && $_GET['post_type'] == 'fakturo_product') ) {
		add_filter('post_row_actions' ,  'fakturo_custom_post_quick_actions', 10, 2);
		add_action('pre_get_posts',  'column_orderby');
		// add_action('pre_get_posts',  'query_set_only_author' );
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

	add_meta_box( 'fakturo-price-box', __('Price', FAKTURO_TEXT_DOMAIN ), 'Fakturo_product_price_box','fakturo_product','side', 'default' );
	add_meta_box( 'fakturo-stock-box', __('Stock', FAKTURO_TEXT_DOMAIN ), 'Fakturo_product_stock_box','fakturo_product','normal', 'default' );
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

		var taxCount = jQuery('table.price-product tr').length - 1;

		for (var i = 0; i < taxCount; i++) {
			jQuery('input[name="price_final[' + i + ']"]').change(function() {debugger;				
				var percent = parseFloat(jQuery("#tax option:selected").attr('percent'));
				var price = Math.round($(this).val() / (1 + percent/100) * 10) / 10;
				var index = $(this).attr('name').replace(/^.*(\d+).*$/i,'$1');
				jQuery('input[name="price_value[' + index + ']"]').val(price);
			});
		}

		jQuery("#tax").change(function() {			
			var percent = parseFloat(jQuery("#tax option:selected").attr('percent'));
			for (var i = 0; i < taxCount; i++) {
				var currentPrice = jQuery('input[name="price_value[' + i + ']"]').val();
				var priceFinal = Math.round(currentPrice * (1 + percent/100) * 10) / 10;
				jQuery('input[name="price_final[' + i + ']"]').val(priceFinal);
			};			
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
		'min_alert', 'packaging', 'unit', 'note', 'origin', 'provider', 'origin', 'currency', 'product_type', 'tax', 'price_scale', 'stock');
	if (isset($options['stock_location']) && is_array($options['stock_location'])) {
		$countStock = count($options['stock_location']);
		$stock = array();

		if (isset($_POST['ID']) && $_POST['ID'] != NULL) {
			$productMetas = get_post_meta($_POST['ID'], 'stock');
			if (isset($productMetas[0])) {
				$productMetas = json_decode($productMetas[0], TRUE);
				foreach ($productMetas as $key => $productMeta) {
					wp_delete_term((int)$productMeta, 'fakturo_stock');
				}
			}
		}

		for ($i = 0; $i < $countStock; $i++) {
			if ($options['stock_location'][$i] != NULL && $options['stock_quantity'][$i] != NULL && is_numeric($options['stock_quantity'][$i])) {
				$term = wp_insert_term( "fakturo_stock_" . microtime(), 'fakturo_stock', $args = array('description' => $options['stock_desc'][$i]) );
				$stock[] = $term['term_id'];
				if ($options['stock_location'][$i] != NULL) {
	      			add_term_meta ($term['term_id'], 'location',  $options['stock_location'][$i]);
	      		}
	      		if ($options['stock_quantity'][$i] != NULL) {
	      			add_term_meta ($term['term_id'], 'quantity',  $options['stock_quantity'][$i]);
	      		}
	      		if ($options['stock_order'][$i] != NULL) {
	      			add_term_meta ($term['term_id'], 'order',  $options['stock_order'][$i]);
	      		}
	      		if ($options['stock_cost'][$i] != NULL) {
	      			add_term_meta ($term['term_id'], 'cost',  $options['stock_cost'][$i]);
	      		}
	      		if ($options['stock_date'][$i] != NULL) {
	      			add_term_meta ($term['term_id'], 'date',  $options['stock_date'][$i]);
	      		}
	      		if (isset($_POST['ID']) && $_POST['ID'] != NULL) {
	      			add_term_meta ($term['term_id'], 'product',  $_POST['ID']);
	      		}
			}			
		}
		$options['stock'] = json_encode($stock);
	}

	// store price
	if (isset($options['price_id'])) {
		$priceStore = array();
		foreach ($options['price_id'] as $key => $priceId) {
			$priceStore[] = array('price_id' => $priceId, 'price_value' => $options['price_value'][$key], 'price_final' => $options['price_final'][$key]);
		}
		$options['price_scale'] = json_encode($priceStore);
	}	

	foreach ($fieldsArray as $field) {
		$product_data[$field]	= (!isset($options[$field])) ? '' : $options[$field];
	}
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
	<tr class="user-facebook-wrap">
		<th><label for="product_type"><?php _e("Product Type", FAKTURO_TEXT_DOMAIN ) ?>	</label></th>
		<td><?php fakturo_client_select_data('fakturo_product_types', 'product_type', $product_data); ?></td>
	</tr>
	<tr class="user-facebook-wrap">
		<th><label for="tax"><?php _e("Tax", FAKTURO_TEXT_DOMAIN ) ?>	</label></th>
		<td><?php 
		$dataSetting = get_terms('fakturo_taxes', 'hide_empty=0');
		FakturoBaseComponent::showTaxSelect($dataSetting, 'tax', $product_data['tax']); ?></td>
	</tr>
	<tr class="user-address-wrap">
		<th><label for="currency"><?php _e("Currency", FAKTURO_TEXT_DOMAIN ) ?></label></th>
		<td>
		<?php 
			$currencyValue = isset($product_data['currency']) ? $product_data['currency'] : NULL;
			FakturoBaseComponent::showCurrencySelect($currencyValue); 
		?>
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
	$dataSetting = get_terms('fakturo_price_scales', 'hide_empty=0');
	$priceData = json_decode($product_data['price_scale'], true);
	?>
	<table class="price-product">
		<tr><th></th><th><?php _e("Price", FAKTURO_TEXT_DOMAIN ) ?></th><th><?php _e("Final", FAKTURO_TEXT_DOMAIN ) ?></th></tr>
		<?php foreach ($dataSetting as $key => $data) { 
			$percent = get_term_meta( $data->term_id, 'percent');
    		$percent = isset($percent[0])?$percent[0]:'';
    		$showPrice = array();

    		if ($priceData != NULL && is_array($priceData)) {
    			foreach ($priceData as $priceItem) {
    				if ($priceItem['price_id'] == $data->term_id) {
    					$showPrice = $priceItem;
    					break;
    				}
    			}
    		}
		?>
			<tr>
				<td><?php 
				echo $data->name;
				echo "($percent%):"; ?>
				</td>
				<td>
					<input type="hidden" name="price_id[<?php echo $key; ?>]" value="<?php echo $data->term_id; ?>">
					<input type="text" name="price_value[<?php echo $key; ?>]" value="<?php if (isset($showPrice['price_value'])) {	echo $showPrice['price_value']; } ?>" />
				</td>
				<td>
					<input type="text" name="price_final[<?php echo $key; ?>]" value="<?php if (isset($showPrice['price_final'])) {	echo $showPrice['price_final']; } ?>"  />
				</td>
			</tr>
		<?php } ?>
	</table>
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

	if (is_array($items) && count($items) > 0) {
		foreach ($items as $key => $value) {
			$term = get_term($value, 'fakturo_stock');			
			$description = $term->description;
			$location = get_term_meta($value, 'location');
			$location = isset($location[0])?$location[0]:'';
			$quantity = get_term_meta($value, 'quantity');
			$quantity = isset($quantity[0])?$quantity[0]:'';
			$order = get_term_meta($value, 'order');
			$order = isset($order[0])?$order[0]:'';
			$cost = get_term_meta($value, 'cost');
			$cost = isset($cost[0])?$cost[0]:'';
			$date = get_term_meta($value, 'date');
			$date = isset($date[0])?$date[0]:'';

			$item = array('id' => $value, 'description' => $description, 'location' => $location, 'quantity' => $quantity, 'order' => $order,
				'cost' => $cost, 'date' => $date);
			$items[$key] = $item;
		}
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
					if ($value->term_id == $item['location']) {
						$selected = " selected ";
					} else {
						$selected = "";
					}
					echo '<option '. $selected .' value="' . $value->term_id . '">' . $value->name . '</option>';
				}
				echo '</select></div>';

				echo '<div class="stock-item"><input type="text" value="' . $item['quantity'] . '" name="stock_quantity[' . $key . ']"></div>';
				echo '<div class="stock-item"><input type="text" value="' . $item['order'] . '" name="stock_order[' . $key . ']"></div>';
				echo '<div class="stock-item"><input type="text" value="' . $item['description'] . '" name="stock_desc[' . $key . ']"></div>';
				echo '<div class="stock-item"><input type="text" value="' . $item['cost'] . '" name="stock_cost[' . $key . ']"></div>';
				echo '<div class="stock-item"><input type="date" value="' . $item['date'] . '" name="stock_date[' . $key . ']"></div>';
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

// Add model page
function fakturo_model_add_new_meta_field() {
	?>
	<div class="form-field">
		<label for="short"><?php _e( 'Short Description', FAKTURO_TEXT_DOMAIN ); ?></label>
		<input type="text" name="short" id="short">
	</div>
	<div class="form-field">
		<label for="provider"><?php _e( 'Provider', FAKTURO_TEXT_DOMAIN ); ?></label>
		<select name="provider" id="provider">
			<option></option>
			<?php
			$args=array(
				'post_type' => 'fakturo_provider',
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'caller_get_posts'=> 1
			 );

			$my_query = null;
			$my_query = new WP_Query($args);
			if( $my_query->have_posts() ) {
				while ($my_query->have_posts()) : $my_query->the_post(); ?>
				<option value="<?php the_ID(); ?>"><?php the_title(); ?></option>
				<?php
				endwhile;
			}
			wp_reset_query();
			?>
		</select>
	</div>
	<div class="form-field">
		<?php $dataSetting = get_terms('category','hide_empty=0'); ?>
		<label for="category"><?php _e( 'Category', FAKTURO_TEXT_DOMAIN ); ?></label>
		<?php FakturoBaseComponent::showTaxonomySelectOnTaxonomy($dataSetting, 'category'); ?>
	</div>
	<div class="form-field">
		<label for="reference"><?php _e( 'Reference', FAKTURO_TEXT_DOMAIN ); ?></label>
		<input type="text" name="reference" id="reference">
	</div>	
	<div class="form-field">
		<label for="internal"><?php _e( 'Internal Code', FAKTURO_TEXT_DOMAIN ); ?></label>
		<input type="text" name="internal" id="internal" style="width:20%">
	</div>
	<div class="form-field">
		<label for="manu"><?php _e( 'Manufacturers Code', FAKTURO_TEXT_DOMAIN ); ?></label>
		<input type="text" name="manu" id="manu">
	</div>	
	<div class="form-field">
		<label for="note"><?php _e( 'Note', FAKTURO_TEXT_DOMAIN ); ?></label>
		<textarea name="note" id="note"></textarea>
	</div>
	<div class="form-field">
		<?php $dataSetting = get_terms('fakturo_origins','hide_empty=0'); ?>
		<label for="origin"><?php _e( 'Origin', FAKTURO_TEXT_DOMAIN ); ?></label>
		<?php FakturoBaseComponent::showTaxonomySelectOnTaxonomy($dataSetting, 'origin'); ?>
	</div>
<?php
}

add_action( 'fakturo_model_add_form_fields', 'fakturo_model_add_new_meta_field', 10, 2 );

function fakturo_model_edit_meta_field($term) { 
	// put the term ID into a variable
	$provider = get_term_meta( $term->term_id, 'provider');
	$provider = isset($provider[0])?$provider[0]:'';
	$category = get_term_meta( $term->term_id, 'category');
	$category = isset($category[0])?$category[0]:'';
	$short = get_term_meta( $term->term_id, 'short');
	$short = isset($short[0])?$short[0]:'';
	$reference = get_term_meta( $term->term_id, 'reference');
	$reference = isset($reference[0])?$reference[0]:'';
	$internal = get_term_meta( $term->term_id, 'internal');
	$internal = isset($internal[0])?$internal[0]:'';
	$manu = get_term_meta( $term->term_id, 'manu');
	$manu = isset($manu[0])?$manu[0]:'';
	$note = get_term_meta( $term->term_id, 'note');
	$note = isset($note[0])?$note[0]:'';
	$origin = get_term_meta( $term->term_id, 'origin');
	$origin = isset($origin[0])?$origin[0]:'';
?>
	<tr class="form-field">
		<th scope="row" valign="top"><label for="short"><?php _e( 'Short Description', FAKTURO_TEXT_DOMAIN ); ?></label></th>
		<td>
			<input type="text" name="short" id="short" value="<?php echo $short; ?>">
		</td>
	</tr>
	<tr class="form-field">
		<th scope="row" valign="top"><label for="provider"><?php _e( 'Provider', FAKTURO_TEXT_DOMAIN ); ?></label></th>
		<td>
			<select name="provider" id="provider">
				<option></option>
				<?php
				$args=array(
					'post_type' => 'fakturo_provider',
					'post_status' => 'publish',
					'posts_per_page' => -1,
					'caller_get_posts'=> 1
				 );

				$my_query = null;
				$my_query = new WP_Query($args);
				if( $my_query->have_posts() ) {
					while ($my_query->have_posts()) : $my_query->the_post(); ?>
					<option <?php if (get_the_ID() == $provider) {
				    	echo " selected ";
				    } ?> value="<?php the_ID(); ?>"><?php the_title(); ?></option>
					<?php
					endwhile;
				}
				wp_reset_query();
				?>
			</select>
		</td>
	</tr>
	<tr class="form-field">
		<th scope="row" valign="top"><label for="category"><?php _e( 'Category', FAKTURO_TEXT_DOMAIN ); ?></label></th>
		<td>
			<?php $dataSetting = get_terms('category','hide_empty=0'); ?>
			<?php FakturoBaseComponent::showTaxonomySelectOnTaxonomy($dataSetting, 'category', $category); ?>
		</td>
	</tr>
	<tr class="form-field">
		<th scope="row" valign="top"><label for="reference"><?php _e( 'Reference', FAKTURO_TEXT_DOMAIN ); ?></label></th>
		<td>
			<input type="text" name="reference" id="reference" value="<?php echo $reference; ?>">
		</td>
	</tr>
	<tr class="form-field">
		<th scope="row" valign="top"><label for="internal"><?php _e( 'Internal Code', FAKTURO_TEXT_DOMAIN ); ?></label></th>
		<td>
			<input type="text" name="internal" id="internal" value="<?php echo $internal; ?>" style="width:20%">
		</td>
	</tr>
	<tr class="form-field">
		<th scope="row" valign="top"><label for="manu"><?php _e( 'Manufacturers Code', FAKTURO_TEXT_DOMAIN ); ?></label></th>
		<td>
			<input type="text" name="manu" id="manu" value="<?php echo $manu; ?>">
		</td>
	</tr>
	<tr class="form-field">
		<th scope="row" valign="top"><label for="reference"><?php _e( 'Note', FAKTURO_TEXT_DOMAIN ); ?></label></th>
		<td>
			<textarea name="note" id="note"><?php echo $note; ?></textarea>
		</td>
	</tr>
	<tr class="form-field">
		<th scope="row" valign="top"><label for="origin"><?php _e( 'Origin', FAKTURO_TEXT_DOMAIN ); ?></label></th>
		<td>
			<?php $dataSetting = get_terms('fakturo_origins','hide_empty=0'); ?>
			<?php FakturoBaseComponent::showTaxonomySelectOnTaxonomy($dataSetting, 'origin', $origin); ?>
		</td>
	</tr>
<?php
}
add_action( 'fakturo_model_edit_form_fields', 'fakturo_model_edit_meta_field', 10, 2 );

function save_fakturo_model_custom_meta( $term_id ) {
	if ( isset( $_POST['provider'] ) ) {
		update_term_meta($term_id, 'provider', $_POST['provider']);
	}
	if ( isset( $_POST['category'] ) ) {
		update_term_meta($term_id, 'category', $_POST['category']);
	}
	if ( isset( $_POST['short'] ) ) {
		update_term_meta($term_id, 'short', $_POST['short']);
	}
	if ( isset( $_POST['reference'] ) ) {
		update_term_meta($term_id, 'reference', $_POST['reference']);
	}
	if ( isset( $_POST['internal'] ) ) {
		update_term_meta($term_id, 'internal', $_POST['internal']);
	}
	if ( isset( $_POST['manu'] ) ) {
		update_term_meta($term_id, 'manu', $_POST['manu']);
	}
	if ( isset( $_POST['note'] ) ) {
		update_term_meta($term_id, 'note', $_POST['note']);
	}
	if ( isset( $_POST['origin'] ) ) {
		update_term_meta($term_id, 'origin', $_POST['origin']);
	}
}  
add_action( 'edited_fakturo_model', 'save_fakturo_model_custom_meta', 10, 2 );  
add_action( 'create_fakturo_model', 'save_fakturo_model_custom_meta', 10, 2 );

// Add term page
function fakturo_stock_add_new_meta_field() {
	?>
	<div class="form-field">
		<label for="product"><?php _e( 'Product', FAKTURO_TEXT_DOMAIN ); ?></label>
		<select name="product" id="product">
			<?php
			$args=array(
				'post_type' => 'fakturo_product',
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'caller_get_posts'=> 1
			 );

			$my_query = null;
			$my_query = new WP_Query($args);
			if( $my_query->have_posts() ) {
				while ($my_query->have_posts()) : $my_query->the_post(); ?>
				<option value="<?php the_ID(); ?>"><?php the_title(); ?></option>
				<?php
				endwhile;
			}
			wp_reset_query();
			?>
		</select>
	</div>
	<div class="form-field">
		<?php $dataSetting = get_terms('fakturo_locations','hide_empty=0'); ?>
		<label for="location"><?php _e( 'Location', FAKTURO_TEXT_DOMAIN ); ?></label>
		<?php FakturoBaseComponent::showTaxonomySelectOnTaxonomy($dataSetting, 'location', NULL, FALSE); ?>
	</div>
	<div class="form-field">
		<label for="quantity"><?php _e( 'Quantity', FAKTURO_TEXT_DOMAIN ); ?></label>
		<input type="text" name="quantity" id="quantity">
	</div>
	<div class="form-field">
		<label for="order"><?php _e( 'Order Number', FAKTURO_TEXT_DOMAIN ); ?></label>
		<input type="text" name="order" id="order">
	</div>
	<div class="form-field">
		<label for="cost"><?php _e( 'Cost', FAKTURO_TEXT_DOMAIN ); ?></label>
		<input type="text" name="cost" id="cost">
	</div>
	<div class="form-field">
		<label for="date"><?php _e( 'Date', FAKTURO_TEXT_DOMAIN ); ?></label>
		<input type="date" name="date" id="date">
	</div>
<?php
}

add_action( 'fakturo_stock_add_form_fields', 'fakturo_stock_add_new_meta_field', 10, 2 );

function fakturo_stock_edit_meta_field($term) {
	$location = get_term_meta( $term->term_id, 'location');
	$location = isset($location[0])?$location[0]:'';
	$quantity = get_term_meta( $term->term_id, 'quantity');
	$quantity = isset($quantity[0])?$quantity[0]:'';
	$order = get_term_meta( $term->term_id, 'order');
	$order = isset($order[0])?$order[0]:'';
	$cost = get_term_meta( $term->term_id, 'cost');
	$cost = isset($cost[0])?$cost[0]:'';
	$date = get_term_meta( $term->term_id, 'date');
	$date = isset($date[0])?$date[0]:'';
	$product = get_term_meta( $term->term_id, 'product');
	$product = isset($product[0])?$product[0]:'';
?>
	<tr class="form-field">
		<th scope="row" valign="top"><label for="product"><?php _e( 'Product', FAKTURO_TEXT_DOMAIN ); ?></label></th>
		<td>
			<?php
			$productPost = get_post($product);
			echo $productPost->post_title;
			 ?>
		</td>
	</tr>
	<tr class="form-field">
		<th scope="row" valign="top"><label for="location"><?php _e( 'Location', FAKTURO_TEXT_DOMAIN ); ?></label></th>
		<td>
			<?php $dataSetting = get_terms('fakturo_locations','hide_empty=0'); ?>
			<?php FakturoBaseComponent::showTaxonomySelectOnTaxonomy($dataSetting, 'location', $location, FALSE); ?>
		</td>
	</tr>
	<tr class="form-field">
		<th scope="row" valign="top"><label for="quantity"><?php _e( 'Quantity', FAKTURO_TEXT_DOMAIN ); ?></label></th>
		<td>
			<input type="text" name="quantity" id="quantity" value="<?php echo $quantity; ?>">
		</td>
	</tr>
	<tr class="form-field">
		<th scope="row" valign="top"><label for="order"><?php _e( 'Order', FAKTURO_TEXT_DOMAIN ); ?></label></th>
		<td>
			<input type="text" name="order" id="order" value="<?php echo $order; ?>">
		</td>
	</tr>
	<tr class="form-field">
		<th scope="row" valign="top"><label for="cost"><?php _e( 'Cost', FAKTURO_TEXT_DOMAIN ); ?></label></th>
		<td>
			<input type="text" name="cost" id="cost" value="<?php echo $cost; ?>">
		</td>
	</tr>
	<tr class="form-field">
		<th scope="row" valign="top"><label for="date"><?php _e( 'Date', FAKTURO_TEXT_DOMAIN ); ?></label></th>
		<td>
			<input type="date" name="date" id="date" value="<?php echo $date; ?>">
		</td>
	</tr>
<?php
}
add_action( 'fakturo_stock_edit_form_fields', 'fakturo_stock_edit_meta_field', 10, 2 );

function save_fakturo_stock_custom_meta( $term_id ) {
	if (isset($_POST['action']) && $_POST['action'] == 'editedtag' && $_POST['action'] == 'add-tag') {
		if ( isset( $_POST['location'] ) ) {
			update_term_meta($term_id, 'location', $_POST['location']);
		}
		if ( isset( $_POST['quantity'] ) ) {
			update_term_meta($term_id, 'quantity', $_POST['quantity']);
		}
		if ( isset( $_POST['order'] ) ) {
			update_term_meta($term_id, 'order', $_POST['order']);
		}
		if ( isset( $_POST['cost'] ) ) {
			update_term_meta($term_id, 'cost', $_POST['cost']);
		}
		if ( isset( $_POST['date'] ) ) {
			update_term_meta($term_id, 'date', $_POST['date']);
		}
		if ( isset( $_POST['product'] ) ) {
			update_term_meta($term_id, 'product', $_POST['product']);
		}
	}	
}  
add_action( 'edited_fakturo_stock', 'save_fakturo_stock_custom_meta', 10, 2 );  
add_action( 'create_fakturo_stock', 'save_fakturo_stock_custom_meta', 10, 2 );