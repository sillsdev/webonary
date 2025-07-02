<?php

class Webonary_Infrastructure
{
	public static function InstallInfrastructure(): void
	{
		if (is_admin()) {
			self::CreateCustomRelevance();
			self::CreateSearchTables();
			self::CreateReversalTables();
			self::SetOptions();
			self::RemoveDoubleTerms();
			self::SetFieldSortOrder();
		}

		self::RegisterSemanticDomainsTaxonomy();
		self::RegisterPartOfSpeechTaxonomy();
		self::RegisterLanguageTaxonomy();
		self::RegisterWebStringsTaxonomy();
		self::RegisterCustomPostType();
	}

	private static function CreateCustomRelevance(): void
	{
		global $wpdb;

		$tableCustomRelevance = $wpdb->prefix . "custom_relevance";
		$char_set = MYSQL_CHARSET;
		$collate = MYSQL_COLLATION;

		$sql = <<<SQL
CREATE TABLE IF NOT EXISTS $tableCustomRelevance (
  class VARCHAR(50) NOT NULL PRIMARY KEY,
  relevance TINYINT
) DEFAULT CHARSET=$char_set COLLATE=$collate;
SQL;

		include_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta($sql);
	}

	public static function CreateSearchTables(): void
	{
		global $wpdb;

		$table = SEARCHTABLE;
		$char_set = MYSQL_CHARSET;
		$collate = MYSQL_COLLATION;

		// if the table doesn't exist, create it now
		$sql = "SHOW TABLES LIKE '$table';";
		$results = $wpdb->get_results($sql);
		if (empty($results)) {

			$sql = <<<SQL
CREATE TABLE $table (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  post_id bigint(20) NOT NULL,
  language_code varchar(30),
  relevance tinyint,
  search_strings longtext,
  class varchar(50),
  subid INT NOT NULL DEFAULT  '0',
  sortorder INT NOT NULL DEFAULT '0',
  UNIQUE INDEX idx_unique_row (post_id, language_code, relevance, search_strings (150), class (50)),
  INDEX relevance_idx (relevance)
) DEFAULT CHARSET=$char_set COLLATE=$collate;
SQL;
			$wpdb->query($sql);
			return;
		}


		// remove the old primary key with multiple fields
		$sql = "SHOW KEYS FROM $table WHERE Key_name = 'PRIMARY';";
		$results = $wpdb->get_results($sql);
		if (!empty($results) && count($results) > 1) {
			$sql = "ALTER TABLE $table DROP PRIMARY KEY";
			$wpdb->query($sql);
		}

		// make sure the `class` field exists
		$sql = "SHOW COLUMNS FROM $table LIKE 'class';";
		$results = $wpdb->get_results($sql);
		if (empty($results)) {

			$sql = "ALTER TABLE $table ADD `class` VARCHAR(50) NULL;";
			$wpdb->query($sql);
		}

		// add a unique constraint to replace the removed primary key
		$sql = "SHOW KEYS FROM $table WHERE Key_name = 'idx_unique_row';";
		$results = $wpdb->get_results($sql);
		if (empty($results)) {
			/** @noinspection SqlResolve */
			$sql = "CREATE UNIQUE INDEX idx_unique_row ON $table (post_id, language_code, relevance, search_strings (150), class);";
			$wpdb->query($sql);
		}

		// add a new auto-increment field for the new primary key
		$sql = "SHOW FIELDS FROM $table WHERE Field = 'id';";
		$results = $wpdb->get_results($sql);
		if (empty($results)) {

			// just in case there is an existing primary key
			$sql = "SHOW KEYS FROM $table WHERE Key_name = 'PRIMARY';";
			$results = $wpdb->get_results($sql);
			if (!empty($results) && count($results) > 1) {
				$sql = "ALTER TABLE $table DROP PRIMARY KEY";
				$wpdb->query($sql);
			}

			// add the new primary key field
			$sql = "ALTER TABLE $table ADD id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY FIRST;";
			$wpdb->query($sql);
		}
	}

	public static function CreateReversalTables(): void
	{
		$table = REVERSALTABLE;
		$char_set = MYSQL_CHARSET;
		$collate = MYSQL_COLLATION;

		$sql = <<<SQL
CREATE TABLE $table (
  id VARCHAR(50) NOT NULL,
  language_code VARCHAR(30),
  reversal_head LONGTEXT,
  reversal_content LONGTEXT,
  sortorder INT NOT NULL DEFAULT '0',
  browseletter VARCHAR(5),
  UNIQUE KEY idx_{$table}_id (id)
) DEFAULT CHARSET=$char_set COLLATE=$collate;
SQL;

		include_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta($sql);
	}

	private static function SetOptions(): void
	{
		global $wpdb;

		/** @noinspection SqlResolve */
		$sql = <<<SQL
UPDATE {$wpdb->prefix}options
SET option_value = 0
WHERE option_name = 'uploads_use_yearmonth_folders'
SQL;

		include_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta($sql);
	}

	public static function RemoveDoubleTerms(): void
	{
		global $wpdb;

		//This deals specifically with the problem that languages (sil_writing_systems) sometimes get inserted several times
		/** @noinspection SqlResolve */
		$sql = <<<SQL
DELETE t1 FROM $wpdb->terms t1, $wpdb->terms t2
WHERE t1.term_id < t2.term_id AND t1.slug = t2.slug
SQL;

		$wpdb->query($sql);
	}

	private static function SetFieldSortOrder(): void
	{
		global $wpdb;

		$sql = 'SHOW columns FROM ' . $wpdb->prefix . 'sil_search WHERE Field = \'sortorder\'';
		if ($wpdb->get_row($sql))
			return;

		/** @noinspection SqlResolve */
		$sql = " ALTER TABLE " . $wpdb->prefix . "sil_search ADD sortorder INT NOT NULL DEFAULT  '0'";

		$wpdb->query($sql);
	}

	public static function RegisterSemanticDomainsTaxonomy(): void
	{
		$labels = array(
			'name' => _x('Semantic Domains', 'taxonomy general name'),
			'singular_name' => _x('Semantic Domain', 'taxonomy singular name'),
			'search_items' => __('Search Domains'),
			'all_items' => __('All Semantic Domains'),
			'parent_item' => __('Parent Semantic Domain'),
			'parent_item_colon' => __('Parent Semantic Domain:'),
			'edit_item' => __('Edit Semantic Domain'),
			'update_item' => __('Update Semantic Domain'),
			'add_new_item' => __('Add New Semantic Domain'),
			'new_item_name' => __('New Semantic Domain Name'),
			'menu_name' => __('Semantic Domain'),
		);

		register_taxonomy(
			'sil_semantic_domains',
			'post',
			array(
				'hierarchical' => false,
				'labels' => $labels,
				'update_count_callback' => '_update_post_term_count',
				'query_var' => true,
				'rewrite' => true,
				'public' => true,
				'show_ui' => true
			)
		);
	}

	public static function RegisterPartOfSpeechTaxonomy(): void
	{
		$labels = array(
			'name' => _x('Part of Speech', 'taxonomy general name'),
			'singular_name' => _x('Part of Speech', 'taxonomy singular name'),
			'search_items' => __('Parts of Speech'),
			'all_items' => __('All Parts of Speech'),
			'parent_item' => __('Parent Part of Speech'),
			'parent_item_colon' => __('Parent Part of Speech:'),
			'edit_item' => __('Edit Part of Speech'),
			'update_item' => __('Update Part of Speech'),
			'add_new_item' => __('Add New Part of Speech'),
			'new_item_name' => __('New Part of Speech Name'),
			'menu_name' => __("Parts of Speech"),
		);

		register_taxonomy(
			'sil_parts_of_speech',
			'post',
			array(
				'hierarchical' => false,
				'labels' => $labels,
				'update_count_callback' => '_update_post_term_count',
				'query_var' => true,
				'rewrite' => true,
				'public' => true,
				'show_ui' => true
			)
		);
	}

	public static function RegisterLanguageTaxonomy(): void
	{
		$labels = array(
			'name' => _x('Languages', 'taxonomy general name'),
			'singular_name' => _x('Language', 'taxonomy singular name'),
			'search_items' => __('Language'),
			'all_items' => __('All Languages'),
			'parent_item' => __('Parent Language'),
			'parent_item_colon' => __('Parent Language:'),
			'edit_item' => __('Edit Language'),
			'update_item' => __('Update Language'),
			'add_new_item' => __('Add New Language'),
			'new_item_name' => __('New Language Name'),
			'menu_name' => __('Language'),
		);

		register_taxonomy(
			'sil_writing_systems',
			'post',
			array(
				'hierarchical' => false,
				'labels' => $labels,
				'update_count_callback' => '_update_post_term_count',
				'query_var' => true,
				'rewrite' => true,
				'public' => true,
				'show_ui' => true
			)
		);

		self::RegisterLanguageCustomFields();
	}

	public static function RegisterWebStringsTaxonomy(): void
	{
		$labels = array(
			'name' => _x('Website strings', 'taxonomy general name'),
			'singular_name' => _x('Website strings', 'taxonomy singular name'),
			'search_items' => __('Website strings'),
			'all_items' => __('All Website strings'),
			'parent_item' => __('Parent Website strings'),
			'parent_item_colon' => __('Parent Website strings:'),
			'edit_item' => __('Edit Website strings'),
			'update_item' => __('Update Website strings'),
			'add_new_item' => __('Add New Website strings'),
			'new_item_name' => __('New Website strings Name'),
			'menu_name' => __('Website strings'),
		);

		register_taxonomy(
			'sil_webstrings',
			'post',
			array(
				'hierarchical' => false,
				'labels' => $labels,
				'update_count_callback' => '_update_post_term_count',
				'query_var' => true,
				'rewrite' => true,
				'public' => true,
				'show_ui' => true
			)
		);
	}

	private static function RegisterCustomPostType(): void
	{
		register_post_type('webonary_cloud',
			[
				'labels' => [
					'name' => __('Cloud Entries'),
					'singular_name' => __('Cloud Entry'),
				],
				'public' => false,
				'rewrite' => false
			]
		);
	}

	public static function RegisterLanguageCustomFields(): void
	{
		add_action('sil_writing_systems_add_form_fields', 'Webonary_Infrastructure::language_add_form');
		add_action('sil_writing_systems_edit_form_fields', 'Webonary_Infrastructure::language_edit_form', 10, 1);
		add_action('created_sil_writing_systems', 'Webonary_Infrastructure::language_save_form');
		add_action('edited_sil_writing_systems', 'Webonary_Infrastructure::language_save_form');
		add_filter('manage_edit-sil_writing_systems_columns', 'Webonary_Infrastructure::add_language_hidden_columns');
		add_filter('manage_sil_writing_systems_custom_column', 'Webonary_Infrastructure::add_language_hidden_column_content', 10, 3);
	}

	public static function language_add_form(): void
	{
		echo <<<HTML
<div class="form-field">
	<label for="hide_language">Hide Language</label>
	<input type="checkbox" name="hide_language" id="hide_language" class="new-language">
	<p>When checked, this language will not be shown in drop-down lists.</p>
</div>
HTML;
	}

	public static function language_edit_form(WP_Term $term): void
	{
		$text_field_hidden = get_term_meta($term->term_id, 'hide_language', true);
		$checked = checked(1, $text_field_hidden, false);
		echo <<<HTML
<tr class="form-field">
	<th><label for="hide_language">Hide Language</label></th>
	<td>
		<input type="checkbox" name="hide_language" id="hide_language" class="new-language" $checked>
		<p>When checked, this language will not be shown in drop-down lists.</p>
	</td>
</tr>
HTML;
	}

	public static function language_save_form($term_id): void
	{
		update_term_meta(
			$term_id,
			'hide_language',
			!empty($_POST['hide_language'])
		);
	}

	public static function add_language_hidden_columns($columns)
	{
		// add Hide as the next-to-last column
		return array_slice($columns, 0, count($columns) - 1) + ['hide_language' => 'Hide'] + $columns;
	}

	public static function add_language_hidden_column_content($content, $column_name, $term_id)
	{
		$text_field_hidden = get_term_meta($term_id, 'hide_language', true);
		switch ($column_name) {
			case 'hide_language':
				// Do your stuff here with $term or $term_id
				$content = !empty($text_field_hidden) ? '<span style="font-size: x-large;color: darkgreen">&check;</span>' : '';
				break;
			default:
				break;
		}

		return $content;
	}
}
