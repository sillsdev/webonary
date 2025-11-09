<?php

namespace SIL\Webonary;

use WP_Admin_Bar;
use wpdb;

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

	public static function SetAdminMenu(): void
	{
		if (!self::IsAdminAllowed())
			return;

		remove_submenu_page('edit.php', 'sil-dictionary-webonary/include/configuration.php');

		if (!Admin::IsAdminAllowed())
			return;

		$menu_icon_svg = file_get_contents(dirname(__DIR__) . '/images/menu-icon.svg');
		add_menu_page(
			'Webonary',
			'Webonary',
			'edit_pages',
			'webonary',
			'webonary_conf_dashboard',
			'data:image/svg+xml;base64,' . base64_encode($menu_icon_svg),
			76
		);
	}

	public static function SetAdminBar(): void
	{
		if (!self::IsAdminAllowed())
			return;

		/** @var WP_Admin_Bar $wp_admin_bar */
		global $wp_admin_bar;

		$wp_admin_bar->add_menu([
			'id' => 'Webonary',
			'title' => 'Webonary',
			'parent' => 'site-name',
			'href' => admin_url('/admin.php?page=webonary')
		]);
	}

	public static function AddLanguageProblemMenuItem(): void
	{
		add_submenu_page(
			'sites.php',
			'Sites With Language Issues',
			'Language Issues',
			'manage_sites',
			'language-issues',
			[Admin::class, 'LanguageIssueReport']
		);
	}

	public static function LanguageIssueReport(): void
	{
		/** @var wpdb $wpdb */
		global $wpdb;

		echo <<<HTML
<div class="wrap">
    <h2>Sites With Language Issues</h2>
</div>
HTML;

		// Step 1: Get all site IDs from wp_blogs
		$sites = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");

		foreach ($sites as $site_id) {

			// Step 2: Build dynamic table names for each site
			$terms_table = $wpdb->prefix . intval($site_id) . '_terms';
			$taxonomy_table = $wpdb->prefix . intval($site_id) . '_term_taxonomy';

			// Step 3: Check if tables exist (avoid errors)
			$sql = 'SHOW TABLES LIKE %s';
			$check_terms = $wpdb->get_var($wpdb->prepare($sql, $terms_table));
			$check_taxonomy = $wpdb->get_var($wpdb->prepare($sql, $taxonomy_table));

			if (!$check_terms || !$check_taxonomy)
				continue;

			// Step 4: Find mismatches between name (terms) and description (taxonomy)
			$sql = <<<SQL
SELECT t.term_id, t.name AS term_name, t.slug, tt.description
FROM $terms_table AS t
  INNER JOIN $taxonomy_table AS tt ON t.term_id = tt.term_id
WHERE tt.taxonomy = 'sil_writing_systems'
  AND TRIM(t.name) <> TRIM(tt.description)
SQL;
			$results = $wpdb->get_results($sql);

			foreach ($results as $row) {
				echo <<<HTML
<div style="display: flex; gap: 8px; font-size: 1.1rem">
	<div style="padding-top: 4px">⚠️</div>
	<div>
		<p style="margin-top: 0; font-size: inherit">Site $site_id - Term ID $row->term_id Mismatch:<br>
		&emsp;Name: $row->term_name | Slug: $row->slug | Description: $row->description</p>
	</div>
</div>
HTML;
				ob_flush();
			}
		}

		echo <<<HTML
<div style="display: flex; gap: 8px; font-size: 1.1rem">
	<div style="padding-top: 4px">✅</div>
	<div>
		<p style="margin-top: 0; font-size: inherit">Finished.</p>
	</div>
</div>
HTML;
	}
}
