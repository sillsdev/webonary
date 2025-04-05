<?php


class Webonary_Admin
{
	public static function AddLanguageProblemMenuItem(): void
	{
		add_submenu_page(
			'sites.php',
			'Sites With Language Issues',
			'Language Issues',
			'manage_sites',
			'language-issues',
			'Webonary_Admin::LanguageIssueReport'
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
