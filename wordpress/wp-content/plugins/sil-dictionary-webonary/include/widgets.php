<?php
function webonary_add_dashboard_widgets() {

	$data = get_userdata( get_current_user_id() );
	$role = ( array ) $data->roles;

	if ( $role[0] == "editor" || $role[0] == "administrator")
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

