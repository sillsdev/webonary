<?php

namespace SIL\WebonaryCreateSite2\Admin;

class Admin
{
	/**
	 * @param string $type Values: "success", "warning", "error, "info"
	 * @param string $msg Note: may contain some HTML
	 * @return void
	 */
	public static function AddAdminNotice(string $type, string $msg): void
	{
		new AdminNotice($type, $msg);
	}

	public static function DoAdminNotices(): string
	{
		ob_start();
		do_action('copier_notices');
		return ob_get_clean();
	}
}
