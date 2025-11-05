<?php

namespace SIL\Webonary;

class Hooks
{
	public static function SetHooks(): int
	{
		if (wp_installing())
			return 0;

		$hooks_set = self::SetAdminHooks();
//		$hooks_set += self::SetAllPageHooks();
//		$hooks_set += self::SetDictionaryHooks();

		return $hooks_set;
	}

	private static function SetAdminHooks(): int
	{
		if (!is_admin())
			return 0;

		$hooks_set = 0;
		$blog_id = get_current_blog_id();

		$hooks_set += (int)add_action('admin_enqueue_scripts', 'SIL\Webonary\Admin::EnqueueAdminScripts');

//		// only show these for dictionary sites, not the main site
//		if ($blog_id > 1) {
//			$hooks_set += (int)add_action('admin_menu', 'SIL\Webonary\Admin::SetAdminMenu');
//			$hooks_set += (int)add_action('admin_bar_menu', 'SIL\Webonary\Admin::SetAdminBar', 35);
////			$hooks_set += (int)add_action('wp_dashboard_setup', 'SIL\Webonary\DashboardWidget::AddWidget');
//		}
////
////		$hooks_set += (int)add_action('network_admin_menu', 'SIL\Webonary\Admin::SetAdminMultisiteMenu');
//
//		/**
//		 * see: https://www.monsterinsights.com/docs/how-to-disable-the-monsterinsights-dashboard-widget/
//		 * Had to put this here rather than in the theme because the MonsterInsights plugin is already
//		 * loaded before the theme loads.
//		 */
//		$hooks_set += (int)add_filter('monsterinsights_show_dashboard_widget', '__return_false');

		return $hooks_set;
	}
}
