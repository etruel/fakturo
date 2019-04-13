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
                        fktrAdminMenu::print_fakturo_dashboard_items();
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


