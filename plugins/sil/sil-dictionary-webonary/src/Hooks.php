<?php

namespace SIL\Webonary;

use SIL\Webonary\Helpers\EmailHelper;
use SIL\Webonary\Helpers\GA4Helper;
use Webonary_API_MyType;
use Webonary_Cloud;
use Webonary_Infrastructure;
use Webonary_SearchCookie;
use Webonary_Utility;
use WP_Error;

class Hooks
{
	private static string $host;
	private static array $stylesheet_uri = [];
	private static array $theme_uri = [];
	private static array $plugin_uri = [];
	private static array $includes_uri = [];

	public static function SetHooks(): int
	{
		self::$host = get_home_url(1);

		if (wp_installing())
			return 0;

		$hooks_set = self::SetAllPageHooks();

		if (is_admin())
			$hooks_set += self::SetAdminHooks();
		else
			$hooks_set += self::SetDictionaryHooks();

		return $hooks_set;
	}

	private static function SetAdminHooks(): int
	{
		$hooks_set = 0;

		$hooks_set += (int)add_action('admin_enqueue_scripts', [Admin::class, 'EnqueueAdminScripts']);
		$hooks_set += (int)add_action('admin_menu', [Admin::class, 'SetAdminMenu']);

		$hooks_set += (int)add_action('admin_bar_menu', [Admin::class, 'SetAdminBar'], 35);
//		$hooks_set += (int)add_action('wp_dashboard_setup', 'SIL\Webonary\DashboardWidget::AddWidget');
		$hooks_set += (int)add_action('in_admin_header', [Admin::class, 'AddSvgIcons']);
		$hooks_set += (int)add_action('wp_ajax_getReportExcel', [AdminWidget::class, 'DisplayReports']);

		$hooks_set += (int)add_action('network_admin_menu', [Admin::class, 'AddLanguageProblemMenuItem']);

		/**
		 * see: https://www.monsterinsights.com/docs/how-to-disable-the-monsterinsights-dashboard-widget/
		 * Had to put this here rather than in the theme because the MonsterInsights plugin is already
		 * loaded before the theme loads.
		 */
		$hooks_set += (int)add_filter('monsterinsights_show_dashboard_widget', '__return_false');

		$hooks_set += (int)add_action('admin_enqueue_scripts', [Webonary_Utility::class, 'EnqueueFinalJs'], 999995);

		return $hooks_set;
	}

	private static function SetAllPageHooks(): int
	{
		$hooks_set = 0;

		$hooks_set += (int)add_action('init', [self::class, 'LoadAdditionalTextDomains']);
		$hooks_set += (int)add_action('init', [Webonary_Infrastructure::class, 'InstallInfrastructure'], 0);
		$hooks_set += (int)add_filter('posts_request', 'replace_default_search_filter', 10, 2);

		// be sure these style sheets are loaded last, after the theme
		$hooks_set += (int)add_action('wp_enqueue_scripts', [Webonary_Utility::class, 'EnqueueJsAndCss'], 999991);

		// this executes just before wordpress determines which template page to load
		$hooks_set += (int)add_action('after_setup_theme', [Webonary_SearchCookie::class, 'GetSearchCookie']);

		// comments
		$hooks_set += (int)add_action('preprocess_comment' , 'preprocess_comment_add_type');
		$hooks_set += (int)add_filter('comment_notification_headers', [EmailHelper::class, 'SetCommentNotificationReplyTo'], 10, 2);
		$hooks_set += (int)add_filter('comment_moderation_headers', [EmailHelper::class, 'SetCommentNotificationReplyTo'], 10, 2);

		// REST API
		$hooks_set += (int)add_action('rest_api_init', [Webonary_API_MyType::class, 'Register_New_Routes']);
		$hooks_set += (int)add_action('rest_api_init', [Webonary_Cloud::class, 'registerApiRoutes']);

		// Block all API requests from users not logged in, with exceptions
		// See https://developer.wordpress.org/rest-api/frequently-asked-questions/#require-authentication-for-all-requests
		$hooks_set += (int)add_filter('rest_authentication_errors', [self::class, 'ApplyRestAuthenticationExceptions']);

		if (IS_CLOUD_BACKEND) {
			$hooks_set += (int)add_filter('posts_pre_query', [Webonary_Cloud::class, 'searchEntries'], 10, 2);
			$hooks_set += (int)add_filter('comment_post_redirect', [Webonary_Cloud::class, 'commentRedirect']);
		}

		$hooks_set += (int)add_filter('post_rewrite_rules', [self::class, 'AddRewriteRules']);
		$hooks_set += (int)add_filter('query_vars', [self::class, 'AddQueryVars']);
		$hooks_set += (int)add_action('widgets_init', [self::class, 'RegisterCustomWidgets']);
		$hooks_set += (int)add_filter('shortcode_atts_audio', [self::class, 'FixAudioFileNames']);

//		$hooks_set += (int)add_action('switch_blog', 'SIL\Webonary\Dictionaries::BlogWasSwitched');

		$hooks_set += (int)add_action('wp_enqueue_scripts', [Webonary_Utility::class, 'EnqueueFinalJs'], 999995);
		$hooks_set += (int)add_action('stylesheet_directory_uri', [self::class, 'OptimizeStylesheetUri'], 1000, 3);
		$hooks_set += (int)add_action('template_directory_uri', [self::class, 'OptimizeThemeUri'], 1000, 3);
		$hooks_set += (int)add_action('plugins_url', [self::class, 'OptimizePluginUri'], 1000, 3);
		$hooks_set += (int)add_action('includes_url', [self::class, 'OptimizeIncludesUri'], 1000, 2);

		return $hooks_set;
	}

	private static function SetDictionaryHooks(): int
	{
		$hooks_set = 0;

		$hooks_set += (int)add_action('wp_footer', [GA4Helper::class, 'HookFooter']);
		$hooks_set += (int)add_action('wp_headers', [self::class, 'ModifyResponseHeaders'], 100);

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

	public static function ApplyRestAuthenticationExceptions($result)
	{
		// If a previous authentication check was applied, pass that result along without modification.
		if (true === $result || is_wp_error($result)) {
			return $result;
		}

		if (is_user_logged_in()) {
			return $result;
		}

		// exceptions, by path
		global $wp;
		$path = add_query_arg(array(), $wp->request);

		if (str_starts_with($path, 'wp-json/wordfence')) {
			return $result;
		}

		if ($path === 'wp-json/webonary/import'
			|| str_starts_with($path, 'wp-json/' . Webonary_Cloud::$apiNamespace)) {
			return $result;
		}

		return new WP_Error(
			'rest_not_logged_in',
			__('This API can only be called if you are logged in first.'),
			array('status' => 401)
		);
	}

	public static function AddRewriteRules($rules): array
	{
		$new_rule = ['^/([^/]+)/?$' => 'index.php?clean=$matches[1]'];
		return $new_rule + $rules;
	}

	public static function AddQueryVars($query_vars): array
	{
		if (!in_array('clean', $query_vars))
			$query_vars[] = 'clean';

		if (!in_array('semdomain', $query_vars))
			$query_vars[] = 'semdomain';

		if (!in_array('semnumber', $query_vars))
			$query_vars[] = 'semnumber';

		return $query_vars;
	}

	public static function RegisterCustomWidgets(): void
	{
		register_widget('Webonary_Search_Widget');
		register_widget('Webonary_Published_Widget');
	}

	public static function FixAudioFileNames($out)
	{
		// the array key we need is either a file extension or "src"
		$audio_types = wp_get_audio_extensions();
		$audio_types[] = 'src';

		// check if this audio short code contains a file name
		foreach ($audio_types as $type) {

			if (empty($out[$type]))
				continue;

			if (!is_string($out[$type]))
				continue;

			// check if the file name contains HTML encoded text
			if (str_contains($out[$type], '&#')) {

				$parts = explode('/', $out[$type]);
				$changed = false;

				foreach ($parts as $key => $part) {

					// decode the text into unicode characters
					if (str_contains($part, '&#')) {
						$parts[$key] = html_entity_decode($part);
						$changed = true;
					}
				}

				// the browser will URL encode the file name if needed
				if ($changed)
					$out[$type] = implode('/', $parts);
			}
		}

		return $out;
	}

	public static function ModifyResponseHeaders(array $headers): array
	{
		// 345600 seconds is 4 days
		$age = defined('CACHE_CONTROL_MAX_AGE') ? CACHE_CONTROL_MAX_AGE : 345600;
		$headers['Cache-Control'] = 'public, must-revalidate, max-age=' . $age;

		return $headers;
	}

	/**
	 * @noinspection PhpUnusedParameterInspection
	 */
	public static function OptimizeStylesheetUri($stylesheet_dir_uri, $stylesheet, $theme_root_uri): string
	{
		if (isset(self::$stylesheet_uri[$theme_root_uri]))
			return self::$stylesheet_uri[$theme_root_uri];

		self::$stylesheet_uri[$theme_root_uri] = self::RemoveSiteSlug($stylesheet_dir_uri);

		return self::$stylesheet_uri[$theme_root_uri];
	}

	/**
	 * @noinspection PhpUnusedParameterInspection
	 */
	public static function OptimizeThemeUri($template_dir_uri, $template, $theme_root_uri): string
	{
		if (isset(self::$theme_uri[$theme_root_uri]))
			return self::$theme_uri[$theme_root_uri];

		self::$theme_uri[$theme_root_uri] = self::RemoveSiteSlug($template_dir_uri);

		return self::$theme_uri[$theme_root_uri];
	}

	/**
	 * @noinspection PhpUnusedParameterInspection
	 */
	public static function OptimizePluginUri($url, $path, $plugin): string
	{
		$key = explode('/', explode('/plugins/', $plugin, 2)[1], 2)[0];

		if (isset(self::$plugin_uri[$key]))
			return self::$plugin_uri[$key];

		self::$plugin_uri[$key] = self::RemoveSiteSlug($url);

		return self::$plugin_uri[$key];
	}

	public static function OptimizeIncludesUri($url, $path): string
	{
		if (isset(self::$includes_uri[$path]))
			return self::$includes_uri[$path];

		self::$includes_uri[$path] = self::RemoveSiteSlug($url);

		return self::$includes_uri[$path];
	}

	private static function RemoveSiteSlug($url): string
	{
		$host = self::$host;
		$re = '/^(.*)(\/)(wp-content|wp-includes)(\/)(.*)$/';
		return preg_replace($re, "$host$2$3$4$5", $url);
	}
}
