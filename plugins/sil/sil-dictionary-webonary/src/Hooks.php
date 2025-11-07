<?php

namespace SIL\Webonary;

class Hooks
{
	public static function SetHooks(): int
	{
		if (wp_installing())
			return 0;

		$hooks_set = self::SetAdminHooks();
		$hooks_set += self::SetAllPageHooks();
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

		// only show these for dictionary sites, not the main site
		if ($blog_id > 1) {
			$hooks_set += (int)add_action('admin_menu', 'SIL\Webonary\Admin::SetAdminMenu');
			$hooks_set += (int)add_action('admin_bar_menu', 'SIL\Webonary\Admin::SetAdminBar', 35);

//			$hooks_set += (int)add_action('wp_dashboard_setup', 'SIL\Webonary\DashboardWidget::AddWidget');
		}

//		$hooks_set += (int)add_action('network_admin_menu', 'SIL\Webonary\Admin::SetAdminMultisiteMenu');

		/**
		 * see: https://www.monsterinsights.com/docs/how-to-disable-the-monsterinsights-dashboard-widget/
		 * Had to put this here rather than in the theme because the MonsterInsights plugin is already
		 * loaded before the theme loads.
		 */
		$hooks_set += (int)add_filter('monsterinsights_show_dashboard_widget', '__return_false');

		return $hooks_set;
	}

	private static function SetAllPageHooks(): int
	{
		$hooks_set = 0;

		$hooks_set += (int)add_action('init', 'SIL\Webonary\Hooks::LoadAdditionalTextDomains');
		$hooks_set += (int)add_action('init', 'Webonary_Infrastructure::InstallInfrastructure', 0);
		$hooks_set += (int)add_filter('posts_request', 'replace_default_search_filter', 10, 2);

		// be sure these style sheets are loaded last, after the theme
		$hooks_set += (int)add_action('wp_enqueue_scripts', 'Webonary_Utility::EnqueueJsAndCss', 999991);

		// this executes just before wordpress determines which template page to load
		$hooks_set += (int)add_action('after_setup_theme', 'Webonary_SearchCookie::GetSearchCookie');

		$hooks_set += (int)add_action('preprocess_comment' , 'preprocess_comment_add_type');
		$hooks_set += (int)add_action('rest_api_init', 'Webonary_API_MyType::Register_New_Routes');
		$hooks_set += (int)add_action('rest_api_init', 'Webonary_Cloud::registerApiRoutes');

//		$hooks_set += (int)add_action('switch_blog', 'SIL\Webonary\Dictionaries::BlogWasSwitched');

		return $hooks_set;
	}

	/**
	 * @return void
	 */
	public static function LoadAdditionalTextDomains(): void
	{
		$include_dir = 'sil-dictionary-webonary/include';
		load_plugin_textdomain('sil_domains', false, $include_dir . '/sem-domains');
	}
}
