<?php

namespace SIL\Webonary\Widgets;

use SIL\Webonary\Helpers\LanguageHelper;
use SIL\Webonary\Models\Language;
use stdClass;
use Webonary_Cloud;
use Webonary_Parts_Of_Speech;
use Webonary_SemanticDomains;
use Webonary_Utility;
use WP_Widget;

class SearchWidget extends WP_Widget
{
	/** @var Language[]  */
	private array $indexed_entries = [];
	private array $sem_domains = [];
	private string $last_edit_date = '';
	private bool $use_li;

	/**
	 * Register widget with WordPress.
	 */
	public function __construct(bool $use_li = false)
	{
		$this->use_li = $use_li;

		parent::__construct(
			'webonary_search',
			'Webonary Search',
			['description' => __('Webonary Search Widget', 'sil_dictionary')]
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Saved values from database.
	 * @see WP_Widget::widget()
	 *
	 */
	public function widget($args, $instance): string
	{
		if (post_password_required())
			return 'Password required.';

		wp_register_script('webonary_special_chars_script', WBNY_PLUGIN_URL . 'js/special_characters.js', [], false, true);
		wp_enqueue_script('webonary_special_chars_script');

		$lines = [];

		if ($this->use_li)
			$lines[] = '<li id="search-2" class="widget widget_search">' . PHP_EOL;

		$lines[] = $args['before_widget'] ?? '';

		if (get_option('noSearch') != 1) {

			$search_term = filter_input(INPUT_GET, 's', FILTER_UNSAFE_RAW, ['options' => ['default' => '']]);
			$this->indexed_entries = LanguageHelper::GetVisibleLanguages();

			if (IS_CLOUD_BACKEND)
				$this->getCloudLists($search_term);
			else
				$this->getMySqlLists($search_term);

			$lines[] = $this->GetHTML($search_term);
		}

		$lines[] = $args['after_widget'] ?? '';

		if ($this->use_li)
			$lines[] = '</li>' . PHP_EOL;

		$return_val = implode(PHP_EOL, $lines);

		// @codeCoverageIgnoreStart
		if (!defined('PHP_UNIT'))
			echo $return_val;
		// @codeCoverageIgnoreEnd

		return $return_val;
	}

	/**
	 * Back-end widget form.
	 *
	 * @param array $instance Previously saved values from database.
	 * @see WP_Widget::form()
	 *
	 */
	public function form($instance): string
	{
		$return_val = '<p>There are no settings for this widget</p>';

		// @codeCoverageIgnoreStart
		if (!defined('PHP_UNIT'))
			echo $return_val;
		// @codeCoverageIgnoreEnd

		return $return_val;
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 * @see WP_Widget::update()
	 *
	 * @codeCoverageIgnore
	 */
	public function update($new_instance, $old_instance): array
	{
		return $new_instance;
	}

	private function getCloudLists($search_term): void
	{
		$dictionary = Webonary_Cloud::getDictionary();
		$cloud_domains = Webonary_Cloud::getSemanticDomains();

		if (is_null($dictionary))
			return;

		//set up semantic domains links
		if ($search_term !== '' && count($cloud_domains)) {
			// NOTE: Even though the current non-cloud search does not filter this by language, we should do so in the future
			$sem_term = strtolower($search_term);
			foreach ($cloud_domains as $item) {
				if (str_contains(strtolower($item->name), $sem_term)) {
					$sem_domain = new stdClass();
					$sem_domain->term_id = $item->name;
					$sem_domain->slug = str_replace('.', '-', $item->abbreviation);
					$sem_domain->description = $item->name;
					$this->sem_domains[] = $sem_domain;
				}
			}
		}

		$this->last_edit_date = $dictionary->updatedAt;
	}

	private function getMySqlLists($search_term): void
	{
		global $wpdb;

		// set up semantic domains links
		// $sem_domain->term_id . '">'. $sem_domain->slug . ' ' . $sem_domain->description
		if ($search_term !== '') {
			$escaped = Webonary_Utility::escapeSqlLike($search_term);
			/** @noinspection SqlResolve */
			$query = <<<SQL
SELECT t.term_id, t.slug, tt.description
FROM $wpdb->terms AS t
    INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id
WHERE tt.taxonomy = 'sil_semantic_domains'
  AND t.name LIKE '%$escaped%'
  AND tt.count > 0
GROUP BY t.name
ORDER BY t.name
SQL;
			$this->sem_domains = $wpdb->get_results($query);
		}

		/** @noinspection SqlResolve */
		$post_date = $wpdb->get_var("SELECT post_date FROM " . $wpdb->posts . " WHERE post_status = 'publish' AND post_type = 'post' ORDER BY post_date DESC");
		$this->last_edit_date = $post_date ?? '';
	}

	private function GetSpecialButtons(): string
	{
		$special_chars = array_filter(explode(',', get_option('special_characters') ?? ''), function ($v) {
			return $v != '' && $v != 'empty';
		});

		$special_buttons = [];
		foreach ($special_chars as $char) {
			$special_buttons[] = "<button type='button' class='spbutton' value='$char' onClick='addChar(this)'>$char</button>";
		}

		return implode(' ', $special_buttons);
	}

	private function GetNumberOfEntries(): string
	{
		$return_val = '';
		$reversals = [];

		foreach ($this->indexed_entries as $indexed) {

			if ($indexed->Hidden ?? false)
				continue;

			if (empty($indexed->Name) || in_array($indexed->Name, $reversals))
				continue;

			$localized_name = __($indexed->Name);
			$return_val .= $localized_name . ':&nbsp;' . $indexed->TotalIndexed . '<br>';
			$reversals[] = $indexed->Name;
		}

		return $return_val;
	}

	private function GetPublishedDate(): string
	{
		global $wpdb;

		$site_url_no_http = preg_replace('@https?://@m', '', get_bloginfo('wpurl'));

		$published_date = $wpdb->get_var("SELECT link_updated FROM {$wpdb->prefix}links WHERE link_url LIKE 'http_://" . trim($site_url_no_http) . "' OR link_url LIKE 'http_://" . trim($site_url_no_http) . "/'");

		if (!empty($published_date) && $published_date != '0000-00-00 00:00:00')
			return __('Date published:', 'sil_dictionary') . ' ' . Webonary_Utility::FormatLongDate(strtotime($published_date));

		return '';
	}

	private function GetSemanticDomains($search_term): string
	{
		if (IS_CLOUD_BACKEND && !Webonary_Cloud::HasSemanticDomains())
			return '';

		if ($search_term == '')
			return '';

		$num_domains = count($this->sem_domains);

		if ($num_domains == 0 || $num_domains > 10)
			return '';

		$items = [];
		foreach ($this->sem_domains as $sem_domain) {
			$items[] = '<li><a href="?s=&partialsearch=1&tax=' . $sem_domain->term_id . '">' . $sem_domain->slug . ' ' . $sem_domain->description . '</a></li>';
		}

		$heading = __('Found in Semantic Domains:', 'sil_dictionary');
		$item_str = implode(PHP_EOL, $items);

		return <<<HTML
<strong>$heading</strong>
<ul>
  $item_str
</ul>
HTML;
	}

	private function GetHTML($search_term): string
	{
		global $search_cookie;

		$url = get_bloginfo('url', 'display');
		$lang = !function_exists('qtrans_getLanguage') ? '' : qtrans_getLanguage();
		$buttons = $this->GetSpecialButtons();
		$query = get_search_query();
		$search = __('Search', 'sil_dictionary');
		$match_whole_words = __('Match whole words', 'sil_dictionary');
		$whole_words_checked = $search_cookie->match_whole_word ? 'checked' : '';
		$match_accents = __('Match accents and tones', 'sil_dictionary');
		$accents_checked = $search_cookie->match_accents ? 'checked' : '';
		$num_entries_title = __('Number of Entries', 'sil_dictionary');
		$num_entries = $this->GetNumberOfEntries();
		$date_published = $this->GetPublishedDate();
		$semantic_domains = $this->GetSemanticDomains($search_term);
		$parts_of_speech_dropdown = Webonary_Parts_Of_Speech::GetDropdown();
		$semantic_domains_dropdown = Webonary_SemanticDomains::GetDropdown();
		$language_dropdown = LanguageHelper::GetLanguageDropdown();

		if (empty($this->last_edit_date))
			$last_upload = '';
		else
			$last_upload = __('Last upload:', 'sil_dictionary') . ' ' . Webonary_Utility::FormatLongDate(strtotime($this->last_edit_date));

		$html = <<<HTML
<form name="searchform" id="searchform" method="get" action="$url">
    <input type="hidden" id="lang" name="lang" value="$lang" />
    <input type="hidden" name="search_options_set" value="1" />

    <div class="flex-column" style="gap: 0.5rem">
        <div class="spbutton-div">$buttons</div>
        <div class="flex-row no-border no-margin no-padding" style="gap:0.5rem">
        	<input type="text" name="s" id="s" value="$query" title="" class="no-margin flex-grow">
        	<button type="submit" id="webonary-search-submit" name="search" value="$search" class="spbutton no-wrap no-margin">$search</button>
		</div>

		$language_dropdown
		$parts_of_speech_dropdown
		$semantic_domains_dropdown
		<div class="flex-row no-border no-margin no-padding" style="gap:0.3rem">
			<input id="match_whole_words" name="match_whole_words" class="form-check form-check-input m-0" value="1" $whole_words_checked type="checkbox" />
			<label for="match_whole_words">$match_whole_words</label>
		</div>

		<div class="flex-row no-border no-margin no-padding" style="gap:0.3rem">
			<input id="match_accents" name="match_accents" class="form-check form-check-input m-0" $accents_checked type="checkbox" />
			<label for="match_accents">$match_accents</label>
		</div>
    </div>
</form>
HTML;

		$divs = [
			$num_entries,
			$last_upload
		];

		if (!empty($date_published))
			$divs[] = $date_published;

		$divs_combined = implode("</div>\n<div>", $divs);

		$html .= <<<HTML
<div class="dictionary-stats">
    <h2>$num_entries_title</h2>
    <div>
    	$divs_combined
    </div>
</div>
HTML;

		if (!empty($semantic_domains))
			$html .= <<<HTML
<div class="dictionary-stats">$semantic_domains</div>
HTML;

		return $html;
	}

}
