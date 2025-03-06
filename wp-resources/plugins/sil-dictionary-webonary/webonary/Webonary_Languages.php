<?php

class Webonary_Languages
{
	private static ?array $language_entries = null;

	/**
	 * @return string
	 */
	public static function GetLanguageDropdown(): string
	{
		$entries = self::GetLanguageEntries();

		if (empty($entries))
			return '';

		/** @noinspection HtmlUnknownAttribute */
		$template = '<option value="%s" %s>%s</option>';
		$selected_language = $_REQUEST['key'] ?? '';
		$options = [];

		foreach ($entries as $entry) {

			$selected = ($entry->language_code === $selected_language) ? 'selected' : '';
			$localized_name = __($entry->language_name);
			$options[] = sprintf($template, $entry->language_code, $selected, $localized_name);
		}

		$option_str = implode(PHP_EOL, $options);

		return <<<HTML
<div class="pos-container">
	<div class="pos-select">
		<select name="key" class="webonary_searchform_language_select">
			$option_str
		</select>
	</div>
</div>
HTML;
	}

	/**
	 * @return ILanguageEntryCount[]
	 */
	public static function GetLanguageEntries(): array
	{
		if (get_option('useCloudBackend'))
			return self::GetCloudLanguageEntries();
		else
			return self::GetWordpressLanguageEntries();
	}

	private static function GetCloudLanguageEntries(): array
	{
		if (!is_null(self::$language_entries))
			return self::$language_entries;

		$languages = get_option('cloud_languages');
		if (!empty($languages['timestamp'])) {
			$time_diff = time() - intval($languages['timestamp']);

			if ($time_diff < (86400 * 2)) {
				self::$language_entries = $languages['entries'];
				return self::$language_entries;
			}
		}

		$dictionary = Webonary_Cloud::getDictionary(Webonary_Cloud::getBlogDictionaryId());

		// get the languages used in senses
		$def_gloss_langs = array_map(
			function($lang) {
				/** @var ILanguageEntryCount $lang_entry */
				$lang_entry = new stdClass();
				$lang_entry->language_code = $lang;
				$lang_entry->language_name = Webonary_Cloud::getLanguageName($lang);
				$lang_entry->total_indexed = 0;
				return $lang_entry;
			},
			array_filter(
				$dictionary->definitionOrGlossLangs,
				function ($lang) use($dictionary) {
					return $lang != $dictionary->mainLanguage->lang;
				}
			)
		);

		// get the reversal languages
		$reversal_langs = array_map(
			function($lang) {
				/** @var ILanguageEntryCount $lang_entry */
				$lang_entry = new stdClass();
				$lang_entry->language_code = $lang->lang;
				$lang_entry->language_name = Webonary_Cloud::getLanguageName($lang->lang, $lang->title);
				$lang_entry->total_indexed = $lang->entriesCount ?? 0;
				return $lang_entry;
			},
			array_filter($dictionary->reversalLanguages, function ($v) use ($dictionary) {
				return !empty($v->lang) && $v->lang != $dictionary->mainLanguage->lang;
			})
		);

		// prepend the main language to the list
		/** @var ILanguageEntryCount $main_entry */
		$main_entry = new stdClass();
		$main_entry->language_code = $dictionary->mainLanguage->lang;
		$main_entry->language_name = Webonary_Cloud::getLanguageName($dictionary->mainLanguage->lang, $dictionary->mainLanguage->title);
		$main_entry->total_indexed = $dictionary->mainLanguage->entriesCount ?? 0;

		self::$language_entries = [$main_entry->language_code => $main_entry];

		foreach ($def_gloss_langs as $lang) {
			if (!isset(self::$language_entries[$lang->language_code]))
				self::$language_entries[$lang->language_code] = $lang;
		}

		foreach ($reversal_langs as $lang) {
			if (!isset(self::$language_entries[$lang->language_code]))
				self::$language_entries[$lang->language_code] = $lang;
		}

		$languages = [
			'timestamp' => time(),
			'entries' => self::$language_entries
		];
		update_option('cloud_languages', $languages);

		return self::$language_entries;
	}

	private static function GetWordpressLanguageEntries(): array
	{
		if (!is_null(self::$language_entries))
			return self::$language_entries;

		self::$language_entries = Webonary_Info::number_of_entries();

		return self::$language_entries;
	}
}
