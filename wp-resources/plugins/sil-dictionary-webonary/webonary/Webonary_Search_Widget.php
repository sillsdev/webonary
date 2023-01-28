<?php

class Webonary_Search_Widget extends WP_Widget
{
	private array $indexed_entries = [];
	private array $sem_domains = [];
	private string $last_edit_date = '';


	/**
	 * Register widget with WordPress.
	 */
	public function __construct()
	{
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
	public function widget($args, $instance)
	{
		echo $args['before_widget'] ?? '';

		if (get_option('noSearch') == 1) {
			echo $args['after_widget'] ?? '';
			return;
		}

		$search_term = filter_input(INPUT_GET, 's', FILTER_UNSAFE_RAW, ['options' => ['default' => '']]);

		if (IS_CLOUD_BACKEND)
			$this->getCloudLists($search_term);
		else
			$this->getMySqlLists($search_term);

		echo $this->GetHTML($search_term);

		echo $args['after_widget'] ?? '';
	}

	/**
	 * Back-end widget form.
	 *
	 * @param array $instance Previously saved values from database.
	 * @see WP_Widget::form()
	 *
	 */
	public function form($instance)
	{
		echo '<p>There are no settings for this widget</p>';
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
	 */
	public function update($new_instance, $old_instance): array
	{
		return $new_instance;
	}

	private function getCloudLists($search_term)
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

		// set up dictionary info
		$mainIndexed = new stdClass();
		$mainIndexed->language_name = Webonary_Cloud::getLanguageName($dictionary->mainLanguage->lang, $dictionary->mainLanguage->title);
		$mainIndexed->totalIndexed = $dictionary->mainLanguage->entriesCount ?? 0;
		$this->indexed_entries[] = $mainIndexed;

		$dictionary->reversalLanguages = array_values(array_filter($dictionary->reversalLanguages, function ($v) {
			return !empty($v->lang);
		}));

		foreach ($dictionary->reversalLanguages as $reversal) {
			$indexed = new stdClass();
			$indexed->language_name = Webonary_Cloud::getLanguageName($reversal->lang, $reversal->title);
			$indexed->totalIndexed = $reversal->entriesCount ?? 0;
			$this->indexed_entries[] = $indexed;
		}


		$this->last_edit_date = $dictionary->updatedAt;
	}

	private function getMySqlLists($search_term)
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

		// set up dictionary info
		$this->indexed_entries = Webonary_Info::number_of_entries();
		/** @noinspection SqlResolve */
		$this->last_edit_date = $wpdb->get_var("SELECT post_date FROM " . $wpdb->posts . " WHERE post_status = 'publish' AND post_type = 'post' ORDER BY post_date DESC");
	}

	private function GetSpecialButtons(): string
	{
		$special_chars = array_filter(explode(',', get_option('special_characters') ?? ''), function ($v) {
			return $v != '' && $v != 'empty';
		});

		$special_buttons = [];
		foreach ($special_chars as $char) {
			$special_buttons[] = "<button type='button' class='button btn btn-outline-default btn-special-char' value='$char' onClick='addChar(this)'>$char</button>";
		}

		return implode(' ', $special_buttons);
	}

	private function GetNumberOfEntries(): string
	{
		$return_val = '';
		$reversals = [];

		foreach ($this->indexed_entries as $indexed) {
			if (empty($indexed->language_name) || in_array($indexed->language_name, $reversals))
				continue;

			$return_val .= $indexed->language_name . ':&nbsp;' . $indexed->totalIndexed . '<br>';
			$reversals[] = $indexed->language_name;
		}

		return $return_val;
	}

	private function GetPublishedDate(): string
	{
		global $wpdb;

		$site_url_no_http = preg_replace('@https?://@m', '', get_bloginfo('wpurl'));

		$published_date = $wpdb->get_var("SELECT link_updated FROM wp_links WHERE link_url LIKE 'http_://" . trim($site_url_no_http) . "' OR link_url LIKE 'http_://" . trim($site_url_no_http) . "/'");

		if (!empty($published_date) && $published_date != '0000-00-00 00:00:00')
			return __('Date published:', 'sil_dictionary') . ' ' . Webonary_Utility::GetDateFormatter()->format(strtotime($published_date));

		return '';
	}

	private function GetSemanticDomains($search_term): string
	{
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
		$language_dropdown = Webonary_Languages::GetLanguageDropdown();

		if (empty($this->last_edit_date))
			$last_upload = '';
		else
			$last_upload = __('Last upload:', 'sil_dictionary') . ' ' . Webonary_Utility::GetDateFormatter()->format(strtotime($this->last_edit_date));

		return <<<HTML
<form name="searchform" id="searchform" method="get" action="$url">
    <input type="hidden" id="lang" name="lang" value="$lang" />
    <input type="hidden" name="search_options_set" value="1" />

    <div class="normalSearch">
        <div>$buttons</div>
        <div class="pos-container">
        	<input type="text" name="s" id="s" value="$query" title="" class="form-control">
        	<button type="submit" id="webonary-search-submit" name="search" value="$search" class="btn btn-secondary no-wrap">
                        $search
                    </button>
		</div>
        <div id="advancedSearch" style="display: block">
            $language_dropdown 
            $parts_of_speech_dropdown
            $semantic_domains_dropdown
            <div class="pos-container">
                <input id="match_whole_words" name="match_whole_words" class="form-check form-check-input m-0" value="1" $whole_words_checked type="checkbox" />
                <label for="match_whole_words">$match_whole_words</label>
            </div>

            <div class="pos-container">
                <input id="match_accents" name="match_accents" class="form-check form-check-input m-0" $accents_checked type="checkbox" />
                <label for="match_accents">$match_accents</label>
            </div>

        </div>
    </div>
</form>
<div class="mt-3">
    <h2>$num_entries_title</h2>
    $num_entries
</div>
<div>$last_upload</div>
<div>$date_published</div>
<div>$semantic_domains</div>
HTML;
	}
}
