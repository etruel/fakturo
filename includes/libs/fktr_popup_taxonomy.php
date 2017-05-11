<?php
class fktr_popup_taxonomy {
	/**
	* Static function hooks
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function hooks() {
		add_action('wp_ajax_fktr_popup_taxonomy', array(__CLASS__, 'get_add_form'));
		add_action('admin_post_fktr_popup_taxonomy_save', array(__CLASS__, 'save'));
		
	}
	/**
	* Static function save
	* @access public
	* @return void
	* @since 0.7
	*/
	public static function save() {

		$request_tax = '';
		if (!empty($_REQUEST['taxonomy'])) {
			$request_tax = $_REQUEST['taxonomy'];
		}
		if (empty($_POST['tag-name'])) {
			$return = new stdClass();
			$return->code = 3; 
			die(json_encode($return));
		}
		$taxnow = $request_tax;
		$taxonomy = $request_tax;
		$tax = get_taxonomy( $taxnow );
		if ( ! $tax )
			wp_die( __( 'Invalid taxonomy.' ) );

		if ( ! in_array( $tax->name, get_taxonomies( array( 'show_ui' => true ) ) ) ) {
		   wp_die( __( 'Sorry, you are not allowed to edit terms in this taxonomy.' ) );
		}

		check_admin_referer( 'fktr_popup_taxonomy_save_nonce', '_wpnonce_add-tag' );

		if ( ! current_user_can( $tax->cap->edit_terms ) ) {
			wp_die(
				'<h1>' . __( 'Cheatin&#8217; uh?' ) . '</h1>' .
				'<p>' . __( 'Sorry, you are not allowed to create terms in this taxonomy.' ) . '</p>',
				403
			);
		}

		$ret = wp_insert_term( $_POST['tag-name'], $taxonomy, $_POST );
		if ( $ret && !is_wp_error( $ret ) ) {
			$return = new stdClass();
			$return->code = 1; 
			$return->term = $ret; 
			$return->name = $_POST['tag-name']; 
			die(json_encode($return));
		}
		else {
			$return = new stdClass();
			$return->code = 2;
			if( is_wp_error( $ret ) ) {
			   $return->message = $ret->get_error_message();
			} 
			die(json_encode($return));
		}

	}
	/**
	* Static function fktr_button_new_term
	* @access public
	* @param $args Array of args used by create the popup
	* @return String with HTML with button add new term | Void with echo html.
	* @since 0.7
	*/
	public static function button($args) {
	 	$defaults = array(
		 	'taxonomy' => 'category', 
		 	'echo' => 0,
		 	'class' => 'button',
		 	'opcional_add_new_item' => '',
		 	'selector_parent_select' => '',
		 	'selector' => '',
	 	);
		if (!isset($args) || !is_array($args)) {
			$args = array();
		} 
		$r = wp_parse_args( $args, $defaults );
		$r['class'] .= ' fktr_btn_taxonomy';
		$tax_name = esc_attr($r['taxonomy']);
		$taxonomy = get_taxonomy($r['taxonomy']);

		$add_new_item_text = $taxonomy->labels->add_new_item;
		if (!empty($r['opcional_add_new_item'])) {
			$add_new_item_text = $r['opcional_add_new_item'];
		}
		$parent_selector = '';
		if (!empty($r['selector_parent_select'])) {
			$parent_selector = ' data-selectorparent="'.$r['selector_parent_select'].'"';
		}
		$data_selector = '';
		if (!empty($r['selector'])) {
			$data_selector = ' data-selector="'.$r['selector'].'"';
		}
		
		//print_r($taxonomy);
		$button_html = '<input type="button" class="'.$r['class'].'" value="'.$add_new_item_text.'" data-taxonomy="'.$r['taxonomy'].'"'.$parent_selector.$data_selector.'/>';
		if ($r['echo']) {
			echo $button_html;
		} else {
			return $button_html;
		}
	}
	/**
	* Static function get_add_form
	* @access public
	* @param $taxnow String with name of taxonomy
	* @return void
	* @since 0.7
	*/
	public static function get_add_form() {

		$request_tax = '';
		if (!empty($_REQUEST['taxonomy'])) {
			$request_tax = $_REQUEST['taxonomy'];
		}

		$taxnow = $request_tax;
		$taxonomy = $request_tax;
		$tax = get_taxonomy( $taxnow );
		if ( ! $tax )
			wp_die( __( 'Invalid taxonomy.' ) );

		if ( ! in_array( $tax->name, get_taxonomies( array( 'show_ui' => true ) ) ) ) {
		   wp_die( __( 'Sorry, you are not allowed to edit terms in this taxonomy.' ) );
		}

		if ( ! current_user_can( $tax->cap->manage_terms ) ) {
			wp_die(
				'<h1>' . __( 'Cheatin&#8217; uh?' ) . '</h1>' .
				'<p>' . __( 'Sorry, you are not allowed to manage terms in this taxonomy.' ) . '</p>',
				403
			);
		}
		do_action('fktr_popup_tax_'.$taxnow.'_print_styles');
		wp_print_styles();
		do_action('fktr_popup_tax_'.$taxnow.'_print_scripts');
		wp_print_scripts();
		?>
		<div class="form-wrap">
		<h2><?php echo $tax->labels->add_new_item; ?></h2>
		<form id="fktr_form_popup_taxonomy" method="post" action="<?php echo admin_url('admin-post.php'); ?>" class="validate"<?php
		/**
		 * Fires inside the Add Tag form tag.
		 *
		 * The dynamic portion of the hook name, `$taxonomy`, refers to the taxonomy slug.
		 *
		 * @since 3.7.0
		 */
		do_action( "{$taxonomy}_term_new_form_tag" );
		?>>
		<input type="hidden" name="action" value="fktr_popup_taxonomy_save"/>
		<input type="hidden" name="taxonomy" value="<?php echo esc_attr($taxonomy); ?>" />
		<?php wp_nonce_field('fktr_popup_taxonomy_save_nonce', '_wpnonce_add-tag'); ?>

		<div class="form-field form-required term-name-wrap">
			<label for="tag-name"><?php _ex( 'Name', 'term name' ); ?></label>
			<input name="tag-name" id="tag-name" type="text" value="" size="40" aria-required="true" />
			<p><?php _e('The name is how it appears on your site.'); ?></p>
		</div>
		<?php if ( ! global_terms_enabled() ) : ?>
		<div class="form-field term-slug-wrap">
			<label for="tag-slug"><?php _e( 'Slug' ); ?></label>
			<input name="slug" id="tag-slug" type="text" value="" size="40" />
			<p><?php _e('The &#8220;slug&#8221; is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.'); ?></p>
		</div>
		<?php endif; // global_terms_enabled() ?>
		<?php if ( is_taxonomy_hierarchical($taxonomy) ) : ?>
		<div class="form-field term-parent-wrap">
			<label for="parent"><?php _ex( 'Parent', 'term parent' ); ?></label>
			<?php
			$dropdown_args = array(
				'hide_empty'       => 0,
				'hide_if_empty'    => false,
				'taxonomy'         => $taxonomy,
				'name'             => 'parent',
				'orderby'          => 'name',
				'hierarchical'     => true,
				'show_option_none' => __( 'None' ),
			);

			/**
			 * Filters the taxonomy parent drop-down on the Edit Term page.
			 *
			 * @since 3.7.0
			 * @since 4.2.0 Added `$context` parameter.
			 *
			 * @param array  $dropdown_args {
			 *     An array of taxonomy parent drop-down arguments.
			 *
			 *     @type int|bool $hide_empty       Whether to hide terms not attached to any posts. Default 0|false.
			 *     @type bool     $hide_if_empty    Whether to hide the drop-down if no terms exist. Default false.
			 *     @type string   $taxonomy         The taxonomy slug.
			 *     @type string   $name             Value of the name attribute to use for the drop-down select element.
			 *                                      Default 'parent'.
			 *     @type string   $orderby          The field to order by. Default 'name'.
			 *     @type bool     $hierarchical     Whether the taxonomy is hierarchical. Default true.
			 *     @type string   $show_option_none Label to display if there are no terms. Default 'None'.
			 * }
			 * @param string $taxonomy The taxonomy slug.
			 * @param string $context  Filter context. Accepts 'new' or 'edit'.
			 */
			$dropdown_args = apply_filters( 'taxonomy_parent_dropdown_args', $dropdown_args, $taxonomy, 'new' );

			wp_dropdown_categories( $dropdown_args );
			?>
			<?php if ( 'category' == $taxonomy ) : // @todo: Generic text for hierarchical taxonomies ?>
				<p><?php _e('Categories, unlike tags, can have a hierarchy. You might have a Jazz category, and under that have children categories for Bebop and Big Band. Totally optional.'); ?></p>
			<?php endif; ?>
		</div>
		<?php endif; // is_taxonomy_hierarchical() ?>
		<div class="form-field term-description-wrap">
			<label for="tag-description"><?php _e( 'Description' ); ?></label>
			<textarea name="description" id="tag-description" rows="5" cols="40"></textarea>
			<p><?php _e('The description is not prominent by default; however, some themes may show it.'); ?></p>
		</div>

		<?php
		if ( ! is_taxonomy_hierarchical( $taxonomy ) ) {
			/**
			 * Fires after the Add Tag form fields for non-hierarchical taxonomies.
			 *
			 * @since 3.0.0
			 *
			 * @param string $taxonomy The taxonomy slug.
			 */
			do_action( 'add_tag_form_fields', $taxonomy );
		}

		/**
		 * Fires after the Add Term form fields.
		 *
		 * The dynamic portion of the hook name, `$taxonomy`, refers to the taxonomy slug.
		 *
		 * @since 3.0.0
		 *
		 * @param string $taxonomy The taxonomy slug.
		 */
		do_action( "{$taxonomy}_add_form_fields", $taxonomy );

		submit_button( $tax->labels->add_new_item );

		?> 
		<div id="fktr_popup_taxomy_loading"></div>
		<?php

		if ( 'category' == $taxonomy ) {
			/**
			 * Fires at the end of the Edit Category form.
			 *
			 * @since 2.1.0
			 * @deprecated 3.0.0 Use {$taxonomy}_add_form instead.
			 *
			 * @param object $arg Optional arguments cast to an object.
			 */
			do_action( 'edit_category_form', (object) array( 'parent' => 0 ) );
		} elseif ( 'link_category' == $taxonomy ) {
			/**
			 * Fires at the end of the Edit Link form.
			 *
			 * @since 2.3.0
			 * @deprecated 3.0.0 Use {$taxonomy}_add_form instead.
			 *
			 * @param object $arg Optional arguments cast to an object.
			 */
			do_action( 'edit_link_category_form', (object) array( 'parent' => 0 ) );
		} else {
			/**
			 * Fires at the end of the Add Tag form.
			 *
			 * @since 2.7.0
			 * @deprecated 3.0.0 Use {$taxonomy}_add_form instead.
			 *
			 * @param string $taxonomy The taxonomy slug.
			 */
			do_action( 'add_tag_form', $taxonomy );
		}

		/**
		 * Fires at the end of the Add Term form for all taxonomies.
		 *
		 * The dynamic portion of the hook name, `$taxonomy`, refers to the taxonomy slug.
		 *
		 * @since 3.0.0
		 *
		 * @param string $taxonomy The taxonomy slug.
		 */
		do_action( "{$taxonomy}_add_form", $taxonomy );
		?>
		</form></div>
	<?php
		die();
	}
}

fktr_popup_taxonomy::hooks();
?>