<?php


class Webonary_API_MyType
{
	public static function Register_New_Routes(): void
	{
		$namespace = 'webonary';

		register_rest_route($namespace, '/import', array(
				'methods' => WP_REST_Server::CREATABLE,
				'callback' => 'Webonary_API_MyType::Import',
				'permission_callback' => function() {
					$data = get_userdata(get_current_user_id());
					$role = $data->roles;
					return (is_super_admin() || (isset($role[0]) && $role[0] == "administrator"));
				}
			)
		);

		//this allows one to make a query like this:
		//http://webonary.localhost/lubwisi/wp-json/webonary/query/dog/en
		//language parameter is optional
		register_rest_route($namespace, '/query/(?P<term>\w+)(?:/(?P<lang>\w+))?', array(
				'methods' => WP_REST_Server::READABLE,
				'callback' => 'Webonary_API_MyType::Query',
				'args' => array(),
				'permission_callback' => '__return_true'
			)
		);
	}

	public static function Query($request): WP_REST_Response
	{
		$data = get_indexed_entries($request['term'], $request['lang']);

		return new WP_REST_Response($data, 200);
	}

	/**
	 * @param WP_REST_Request $_headers
	 * @param bool $newAPI
	 * @throws Exception
	 */
	public static function Import(WP_REST_Request $_headers, bool $newAPI = true): void
	{
		echo 'This functionality has been disabled.';
	}
}
