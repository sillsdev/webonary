<?php

use SIL\Webonary\Admin;

class Webonary_Configuration
{
	//region Table and taxonomy attributes
	public static string $search_table_name = SEARCHTABLE;
	public static string $reversal_table_name = REVERSALTABLE;
	//endregion

	/**
	 * Set up the SIL Dictionary in WordPress Dashboard Tools
	 */
	public static function add_admin_menu(): void
	{
		remove_submenu_page('edit.php', 'sil-dictionary-webonary/include/configuration.php');

		if (!Admin::IsAdminAllowed())
			return;

		add_menu_page('Webonary', 'Webonary', 'edit_pages', 'webonary', 'webonary_conf_dashboard', get_bloginfo('wpurl') . '/wp-content/plugins/sil-dictionary-webonary/images/webonary-icon.png', 76);
	}

	public static function on_admin_bar(): void
	{
		/** @var WP_Admin_Bar $wp_admin_bar */
		global $wp_admin_bar;

		$wp_admin_bar->remove_menu( 'themes' );
		$wp_admin_bar->remove_menu( 'widgets' );
		$wp_admin_bar->remove_menu( 'menus' );

		if (!Admin::IsAdminAllowed())
			return;

		$wp_admin_bar->add_menu([
			'id' => 'Webonary',
			'title' => 'Webonary',
			'parent' => 'site-name',
			'href' => admin_url('/admin.php?page=webonary')
		]);
	}

	public static function get_admin_sections(): array
	{
		$sections = [
			'import' => __('Data (Upload)', 'sil_dictionary'),
			'search' => __('Search', 'sil_dictionary'),
			'browse' => __('Browse Views', 'sil_dictionary'),
			'fonts' => __('Fonts', 'sil_dictionary')
		];

		if(is_super_admin())
			$sections['superadmin'] = __('Super Admin', 'sil_dictionary');

		return $sections;
	}

	public static function get_LanguageCodes($language_code = null): array|null|object
	{
		global $wpdb;

		if (is_null($language_code)) {

			/** @noinspection SqlResolve */
			$sql = <<<SQL
SELECT s.language_code, IFNULL(MAX(t.`name`), s.language_code) AS `name`
FROM {$wpdb->prefix}sil_search AS s
LEFT JOIN $wpdb->terms AS t ON t.slug = s.language_code
WHERE IFNULL(s.language_code, '') <> ''
GROUP BY s.language_code
ORDER BY s.language_code
SQL;
		}
		else {
			$language_code = trim($language_code);

			/** @noinspection SqlResolve */
			$sql = <<<SQL
SELECT s.language_code, IFNULL(MAX(t.`name`), s.language_code) AS `name`
FROM {$wpdb->prefix}sil_search AS s
LEFT JOIN $wpdb->terms AS t ON t.slug = s.language_code
WHERE IF(%s = '', s.language_code, %s) = s.language_code
GROUP BY s.language_code
ORDER BY s.language_code
SQL;
			$sql = $wpdb->prepare($sql, array($language_code, $language_code));
		}

		return $wpdb->get_results($sql, 'ARRAY_A');
	}

	public static function use_pinyin($language_code): bool
	{
		return in_array($language_code, array('zh-CN', 'zh-Hans-CN'));
	}
}
