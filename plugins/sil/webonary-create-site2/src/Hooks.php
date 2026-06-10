<?php

namespace SIL\WebonaryCreateSite2;

use SIL\WebonaryCreateSite2\Controllers\Copier;

class Hooks
{
	public static function SetHooks(): int
	{
		if (wp_installing())
			return 0;

		return self::SetAdminHooks();
	}

	private static function SetAdminHooks(): int
	{
		if (!is_admin())
			return 0;

		$hooks_set = add_action('network_admin_menu', [self::class, 'MultiSiteAddPage']);

		return $hooks_set;
	}

	public static function MultiSiteAddPage(): void
	{
		$title = __('Create Webonary Site', 'webonary-create-site2');
		add_submenu_page(
			'sites.php',
			$title,
			$title,
			'manage_sites',
			'webonary-create-site-2',
			[Copier::class, 'DoPage']
		);
	}
}
