<?php

function webonary_add_dashboard_widgets(): void
{
	$data = get_userdata(get_current_user_id());
	$role = $data->roles;

	if (is_super_admin() || (isset($role[0]) && ($role[0] == "editor" || $role[0] == "administrator"))) {
		wp_add_dashboard_widget(
			'webonary_dashboard_widget',  // Widget slug.
			'Webonary',  // Title.
			['Webonary_Dashboard_Widget', 'OutputDashboardWidget']  // Display function.
		);
	}
}

add_action('wp_dashboard_setup', 'webonary_add_dashboard_widgets');
