<?php

namespace SIL\Webonary;

class Admin
{
	private static array $allowed_roles = ['editor', 'editorplus', 'administrator'];
	private static ?bool $is_allowed = null;

	/**
	 * Is the current user allowed to use admin functions?
	 *
	 * @return bool
	 */
	public static function IsAdminAllowed(): bool
	{
		if (!is_null(self::$is_allowed))
			return self::$is_allowed;

		// super admin is always allowed
		if (is_super_admin()) {
			self::$is_allowed = true;
			return true;
		}

		// get the current user
		$user = get_userdata(get_current_user_id());

		// if not found or no roles, return false
		if ($user === false || empty($user->roles)) {
			self::$is_allowed = false;
			return false;
		}

		// does the user have one of the allowed roles?
		self::$is_allowed = !empty(array_intersect(self::$allowed_roles, $user->roles));

		return self::$is_allowed;
	}

	public static function EnqueueAdminScripts(): void
	{
		wp_register_script(
			'webonary_admin_script',
			WBNY_PLUGIN_URL . 'js/webonary-admin.js',
			[],
			false,
			true
		);
		wp_enqueue_script('webonary_admin_script');
		wp_localize_script(
			'webonary_admin_script',
			'webonary_ajax_obj',
			['ajax_url' => admin_url('admin-ajax.php')]
		);

		wp_register_style(
			'webonary_admin_style',
			WBNY_PLUGIN_URL . 'css/admin_styles.css'
		);
		wp_enqueue_style('webonary_admin_style');

		wp_register_script(
			'webonary_toastr_script',
			'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js',
			[],
			false,
			true
		);
		wp_enqueue_script('webonary_toastr_script');

		wp_register_style(
			'webonary_toastr_style',
			'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css'
		);
		wp_enqueue_style('webonary_toastr_style');
	}
}
