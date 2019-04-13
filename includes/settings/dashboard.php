<?php
$fktr_widgets_dashboard = array();
function fktr_add_dashboard_widget($slug = '', $title = '', $hook) {
	global $fktr_widgets_dashboard;
	if (!is_array($fktr_widgets_dashboard)) {
		$fktr_widgets_dashboard = array();
	}
	$new_widget = new stdClass();
	$new_widget->title = $title;
	$new_widget->hook = $hook;
	$fktr_widgets_dashboard[$slug] = $new_widget;
	return true;
}
function fktr_dashboard_widgets() {
	global $fktr_widgets_dashboard;
	if (!is_array($fktr_widgets_dashboard)) {
		$fktr_widgets_dashboard = array();
	}
	do_action('fktr_before_dashboard_widgets');
	$int = 1;
	foreach ($fktr_widgets_dashboard as $slug => $widget) {
		if ($int > 4) {
			$int = 1;
		}
		fktr_print_widget($slug, $widget);
		echo '<div class="clear_'.$int.'"></div>';
		$int++;
	}

	do_action('fktr_after_dashboard_widgets');
} 

function fktr_print_widget($slug, $widget) {
	echo '<div class="dashboard_widget" id="widget_'.$slug.'">
			<div class="seccion" id="sec_'.$slug.'">
				<h3 id="w_title_'.$slug.'">'.$widget->title.'</h3>';
				add_action('fktr_dashboard_widget_'.$slug, $widget->hook, 10, 1);
				do_action('fktr_dashboard_widget_'.$slug);
			echo '</div>

		</div>
		';
}         

?>
	<h1 class="_title_dash"><?php _e( 'Dashboard', 'fakturo' ); ?></h1>
	<!--html5 desing-->
	<div>
		
	<section class="_menu_items_metro">
		<ul>
			<?php 
				$dashboard_options = get_option('fakturo_dashboard_options_group');
				$dialer_options = fktr_get_dialer_options();
				for($d=0; $d < 7; $d++) {
					if (!empty($dashboard_options['dialer'][$d]) && !empty($dialer_options[$dashboard_options['dialer'][$d]])) {
						$item = $dialer_options[$dashboard_options['dialer'][$d]];
						
						if (!current_user_can($item->caps)) {
							continue;
						}
						$class_color = 'color'.($d+1);
						if ($d == 5) {
							$class_color = 'colordefault';
						}
						$menu_link = '';
						if ($item->type == 'post') {
							$menu_link = admin_url('edit.php?post_type='.$dashboard_options['dialer'][$d]);
						} else if ($item->type == 'taxonomy') {
							$menu_link = admin_url('edit-tags.php?taxonomy='.$dashboard_options['dialer'][$d]);
						} else if ($item->type == 'setting') {
							$menu_link = admin_url('admin.php?page=fakturo-settings');
								
						}
						
						echo '<a href="'.$menu_link.'">
						<li class="'.$class_color.'">
							<div class="_menu_dashicon">
								<span class="dashicons '.$item->icon.'"></span>
							</div>
							<div class="_descripcion_items_metro">
								<p>'.$item->text.'</p>
							</div>
						</li>
						</a>'; 

					}


					

				
				}


			?>
			
		</ul>
	</section>


	<!--section widgets front-end-->
	<section class="element_widget_dashboard">
		<?php 
			do_action('fktr_dashboard_setup'); 
			fktr_dashboard_widgets();
		?>
	</section>
</div>


