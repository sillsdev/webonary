<?php
function webonary_add_dashboard_widgets() {

	//has to have at least role of editor
	if(current_user_can('delete_others_pages'))
	{
		wp_add_dashboard_widget(
				'webonary_dashboard_widget',         // Widget slug.
				'Webonary',         // Title.
				'webonary_dashboard_widget_function' // Display function.
		);
	}
}
add_action( 'wp_dashboard_setup', 'webonary_add_dashboard_widgets' );
function webonary_dashboard_widget_function() {
	webonary_conf_widget();
}

