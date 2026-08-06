<?php

namespace SIL\WebonaryCreateSite2;

use SIL\WebonaryCreateSite2\Controllers\Copier;
use SIL\WebonaryCreateSite2\Controllers\NewSite;

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

		$hooks_set = (int)add_action('network_admin_menu', [__CLASS__, 'MultiSiteAddPage']);
		$hooks_set += (int)add_action('admin_enqueue_scripts', [__CLASS__, 'EnqueueAdminScripts']);
		$hooks_set += (int)add_action('wp_ajax_createNewSite', [NewSite::class, 'AjaxCreateSite']);

		return $hooks_set;
	}

	/**
	 * @codeCoverageIgnore
	 */
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

	/**
	 * @codeCoverageIgnore
	 */
	public static function EnqueueAdminScripts(): void
	{
		wp_register_script(
			'wcs2_script',
			WCS2_PLUGIN_URL . 'js/wcs2-script.js',
			[],
			false,
			true
		);
		wp_enqueue_script('wcs2_script');
		wp_localize_script(
			'wcs2_script',
			'webonary_ajax_obj',
			['ajax_url' => admin_url('admin-ajax.php')]
		);

		wp_register_style(
			'wcs2_style',
			WCS2_PLUGIN_URL . 'css/wcs2-styles.css'
		);
		wp_enqueue_style('wcs2_style');
	}
}
