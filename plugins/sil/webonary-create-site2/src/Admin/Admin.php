<?php

namespace SIL\WebonaryCreateSite2\Admin;

use SIL\WebonaryCreateSite2\Abstracts\NoticeType;

class Admin
{
	/**
	 * @param NoticeType $type
	 * @param string $msg Note: may contain some HTML
	 * @return void
	 */
	public static function AddAdminNotice(NoticeType $type, string $msg): void
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
