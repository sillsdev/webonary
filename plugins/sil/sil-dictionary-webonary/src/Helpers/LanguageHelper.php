<?php

namespace SIL\Webonary\Helpers;

use SIL\Webonary\Models\Language;
use Webonary_Cloud;
use WP_Term;

class LanguageHelper
{
	public static string $languageCategory = 'sil_writing_systems';

	/** @var Language[]|null  */
	private static ?array $languages = null;

	/** @var Language[]|null  */
	private static ?array $visible_languages = null;

	/**
	 * @return Language[]
	 */
	public static function GetLanguages(): array
	{
		if (!is_null(self::$languages))
			return self::$languages;

		// just use English for the home site
		if (get_current_blog_id() < 2) {
			self::$languages = [new Language('en', 'English', 0, true)];
			return self::$languages;
		}

		// check the cache next
		self::$languages = Cache::Get('all_languages');
		if (!is_null(self::$languages))
			return self::$languages;

		// get the list of languages from the data source
		if (get_option('useCloudBackend'))
			self::$languages = self::GetCloudLanguages();
		else
			self::$languages = self::GetWordPressLanguages();

		// sync the list with the list of languages is the terms table
		foreach (self::$languages as $language) {
			self::GetLanguageName($language);
		}

		self::$languages = array_values(self::$languages);

		// mark hidden languages
		foreach (self::$languages as $lang) {

			$term = get_term_by('slug', $lang->Code, self::$languageCategory);
			$text_field_hidden = get_term_meta($term->term_id, 'hide_language', true);
			if (!empty($text_field_hidden))
				$lang->Hidden = true;
		}

		// sort the list
		usort(self::$languages, function(Language $a, Language $b) {

			// main language should be first
			if ($a->IsMain != $b->IsMain)
				return $b->IsMain - $a->IsMain;

			return strnatcasecmp($a->Name, $b->Name);
		});

		self::RemoveDeprecatedLanguages(self::$languages);

		Cache::Save('all_languages', self::$languages);

		return self::$languages;
	}

	/**
	 * @return Language[]
	 */
	public static function GetVisibleLanguages(): array
	{
		if (!is_null(self::$visible_languages))
			return self::$visible_languages;

		// check the cache
		self::$visible_languages = Cache::Get('visible_languages');
		if (!is_null(self::$visible_languages))
			return self::$visible_languages;

		// get the list and remove hidden languages
		$languages = self::GetLanguages();
		$languages = array_filter($languages, fn($l) => !$l->Hidden);

		self::$visible_languages = array_values($languages);

		Cache::Save('visible_languages', self::$visible_languages);

		return self::$visible_languages;
	}

	/**
	 * Gets a list of the languages for sites with a WordPress backend.
	 *
	 * @return Language[]
	 */
	private static function GetWordPressLanguages(): array
	{
		global $wpdb;

		$sql = <<<SQL
SELECT s.language_code AS `Code`, IFNULL(MAX(t.`name`), s.language_code) AS `Name`
FROM {$wpdb->prefix}sil_search AS s
LEFT JOIN $wpdb->terms AS t ON t.slug = s.language_code
WHERE IFNULL(s.language_code, '') <> ''
GROUP BY s.language_code
ORDER BY s.language_code
SQL;
		$rows = $wpdb->get_results($sql) ?? [];
		$return_val = [];

		foreach ($rows as $row) {
			$return_val[] = new Language($row->Code, $row->Name);
		}

		return $return_val;
	}

	/**
	 * Gets a list of the languages for sites with a Cloud backend.
	 *
	 * @return Language[]
	 */
	private static function GetCloudLanguages(): array
	{
		$dictionary = Webonary_Cloud::getDictionary();

		if (is_null($dictionary))
			return [];

		// prepend the main language to the list
		$main_entry = new Language( $dictionary->mainLanguage->lang,  $dictionary->mainLanguage->title, $dictionary->mainLanguage->entriesCount ?? 0, true);
		$languages = [$main_entry->Code => $main_entry];

		// get the reversal languages
		$reversal_languages = array_map(
			fn($lang) => new Language($lang->lang, $lang->title, $lang->entriesCount ?? 0, false, true),
			$dictionary->reversalLanguages
		);

		foreach ($reversal_languages as $lang) {
			if (!isset($languages[$lang->Code]))
				$languages[$lang->Code] = $lang;
		}

		// get the languages used in senses
		$def_gloss_languages = array_map(
			fn($code) => new Language($code, ''),
			$dictionary->definitionOrGlossLangs
		);

		foreach ($def_gloss_languages as $lang) {
			if (!isset($languages[$lang->Code]))
				$languages[$lang->Code] = $lang;
		}

		return $languages;
	}

	/**
	 * @param Language $language
	 * @return void
	 */
	public static function GetLanguageName(Language $language): void
	{
		// first look in the saved terms
		$term = get_term_by('slug', $language->Code, self::$languageCategory);
		if (!empty($term)) {
			$language->Name = $term->name;
			return;
		}

		// if there is no name, try to find one
		if (empty($language->Name)) {

			// Check if this is a major language code
			$language->Name = locale_get_display_language($language->Code, 'en');

			// locale_get_display_language returns the locale code if it doesn't know the name
			if ($language->Name != $language->Code)
				$description = locale_get_display_name($language->Code, 'en');
		}

		if (empty($description))
			$description = $language->Name;

		wp_insert_term(
			$language->Name,
			self::$languageCategory,
			array('description' => $description, 'slug' => $language->Code)
		);
	}

	/**
	 * Removes languages that are no longer used.
	 *
	 * @param Language[] $languages
	 * @return void
	 */
	private static function RemoveDeprecatedLanguages(array $languages): void
	{
		$lang_codes = array_values(array_unique(array_column($languages, 'Code')));
		$lower_slugs = array_map('strtolower', $lang_codes);

		/** @var WP_Term[] $terms */
		$terms = get_terms(
			[
				'get' => 'all',
				'taxonomy' => self::$languageCategory,
				'orderby' => 'none',
				'suppress_filter' => 1
			]
		);

		$terms = array_filter($terms, fn($term) => !in_array(strtolower($term->slug), $lower_slugs));

		foreach ($terms as $term) {
			wp_delete_term($term->term_id, self::$languageCategory);
		}
	}

	public static function GetLanguageDropdown(): string
	{
		$entries = self::GetLanguages();

		if (empty($entries))
			return '';

		/** @noinspection HtmlUnknownAttribute */
		$template = '<option value="%s" %s>%s</option>';
		$selected_language = $_REQUEST['key'] ?? '';
		$options = [];

		foreach ($entries as $entry) {

			$selected = ($entry->Code === $selected_language) ? 'selected' : '';
			$localized_name = __($entry->Name);
			$options[] = sprintf($template, $entry->Code, $selected, $localized_name);
		}

		$option_str = implode(PHP_EOL, $options);

		return <<<HTML
<div class="pos-container">
	<div class="pos-select">
		<select name="key" class="webonary_language_select">
			$option_str
		</select>
	</div>
</div>
HTML;
	}
}
