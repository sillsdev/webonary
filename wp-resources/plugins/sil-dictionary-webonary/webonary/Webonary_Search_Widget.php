<?php

class Webonary_Search_Widget extends WP_Widget {

    private $indexed_entries = [];
    private $sem_domains = [];
    private $language_options = [];
    private $parts_of_speech = [];
    private $last_edit_date = '';


	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			'webonary_search',
			'Webonary Search',
			['description' => __('Webonary Search Widget', 'sil_dictionary')]
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget($args, $instance) {

		global $search_cookie;

		echo $args['before_widget'];

		if (get_option('noSearch') == 1) {
			echo $args['after_widget'];
			return;
        }

		$taxonomy = filter_input(INPUT_GET, 'tax', FILTER_SANITIZE_STRING, ['options' => ['default' => '']]);
		$search_term = filter_input(INPUT_GET, 's', FILTER_UNSAFE_RAW, ['options' => ['default' => '']]);
		$selected_language = $_REQUEST['key'] ?? '';

		if(get_option('useCloudBackend'))
            $this->getCloudLists($taxonomy, $search_term, $selected_language);
		else
			$this->getMySqlLists($taxonomy, $search_term, $selected_language);

		$advanced_search = Webonary_Utility::GetInt('displayAdvancedSearchName');

        echo "<input type=\"hidden\" id=\"display-advanced\" value=\"$advanced_search\">";

        $substitutions = [
            '@url@' => get_bloginfo('url', 'display'),
            '@search@' => __('Search', 'sil_dictionary'),
            '@advanced_search@' => __('Advanced Search', 'sil_dictionary'),
            '@hide_advanced_search@' => __('Hide Advanced Search', 'sil_dictionary'),
            '@search_query@' => get_search_query(),
            '@language_dropdown@' => $this->GetLanguageDropdown(),
            '@parts_of_speech_dropdown@' => $this->GetPartsOfSpeechDropdown(),
            '@whole_words_checked@' => $search_cookie->match_whole_word ? 'checked' : '',
            '@match_whole_words@' => __('Match whole words', 'sil_dictionary'),
            '@accents_checked@' => $search_cookie->match_accents ? 'checked' : '',
            '@match_accents@' => __('Match accents and tones', 'sil_dictionary'),
            '@special_buttons@' => $this->GetSpecialButtons(),
            '@num_entries_title@' => __('Number of Entries', 'sil_dictionary'),
            '@num_entries@' => $this->GetNumberOfEntries(),
            '@date_published@' => $this->GetPublishedDate(),
            '@semantic_domains@' => $this->GetSemanticDomains($search_term)
        ];

		if (function_exists('qtrans_getLanguage'))
			$substitutions['@qtrans_language@'] = qtrans_getLanguage();
        else
	        $substitutions['@qtrans_language@'] = '';

        if (empty($this->last_edit_date))
	        $substitutions['@last_upload@'] = '';
        else
	        $substitutions['@last_upload@'] = __('Last upload:', 'sil_dictionary') . ' ' . Webonary_Utility::GetDateFormatter()->format(strtotime($this->last_edit_date));

		echo Webonary_Utility::includeTemplate('search-script.html');
        echo Webonary_Utility::includeTemplate('search-form.html', $substitutions);

		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {

        echo '<p>There are no settings for this widget</p>';
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ): array {

		return $new_instance;
	}

    private function getCloudLists($taxonomy, $search_term, $selected_language) {

	    $dictionaryId = Webonary_Cloud::getBlogDictionaryId();
	    $dictionary = Webonary_Cloud::getDictionary($dictionaryId);
	    $currentLanguage = Webonary_Cloud::getCurrentLanguage();
	    if(!is_null($dictionary))
	    {
		    // set up parts of speech dropdown
		    if(count($dictionary->partsOfSpeech))
		    {
			    foreach($dictionary->partsOfSpeech as $part)
			    {
				    if ($part->lang === $currentLanguage) {
					    $selected = ($part->abbreviation === $taxonomy) ? 'selected' : '';
					    $this->parts_of_speech[] = "<option value=\"$part->abbreviation\" $selected>$part->name</option>";
				    }
			    }


		    }

		    //set up semantic domains links
		    if($search_term !== '' && count($dictionary->semanticDomains))
		    {
			    // NOTE: Even though the current non-cloud search does not filter this by language, we should do so in the future
			    $sem_term = strtolower($search_term);
			    foreach($dictionary->semanticDomains as $item)
			    {
				    if(strpos($item->nameInsensitive, $sem_term) !== false)
				    {
					    $sem_domain = new stdClass();
					    $sem_domain->term_id = $item->name;
					    $sem_domain->slug = str_replace('.', '-', $item->abbreviation);
					    $sem_domain->description = $item->name;
					    $this->sem_domains[] = $sem_domain;
				    }
			    }
		    }

		    // set up dictionary info
		    $indexed = new stdClass();
		    $indexed->language_name = $dictionary->mainLanguage->title ?? $dictionary->mainLanguage->lang;
		    $indexed->totalIndexed = $dictionary->mainLanguage->entriesCount ?? 0;
		    $this->indexed_entries[] = $indexed;

		    $dictionary->reversalLanguages = array_values(array_filter($dictionary->reversalLanguages, function ($v) {
			    return !empty($v->lang);
		    }));

		    if (count($dictionary->reversalLanguages)) {
                $this->language_options[] = "<option value=\"{$dictionary->mainLanguage->lang}\" selected>$indexed->language_name</option>";

			    foreach($dictionary->reversalLanguages as $reversal)
			    {
				    $indexed = new stdClass();
				    $indexed->language_name = $reversal->title ?? $reversal->lang;
				    $indexed->totalIndexed = $reversal->entriesCount ?? 0;
				    $this->indexed_entries[] = $indexed;

				    // set up languages dropdown options
				    $selected = ($reversal->lang === $selected_language) ? 'selected' : '';
				    $this->language_options[] = "<option value=\"$reversal->lang\" $selected>$indexed->language_name</option>";
			    }
		    }

		    $this->last_edit_date = $dictionary->updatedAt;
	    }
    }

    private function getMySqlLists($taxonomy, $search_term, $selected_language) {

	    global $wpdb;

	    $languages = Webonary_Configuration::get_LanguageCodes();

	    if (!empty($languages)) {

		    $lang_code = get_option('languagecode');

		    $vernacular_languages = array_values(array_filter($languages, function($v) use($lang_code) {
			    return $v['language_code'] == $lang_code;
		    }));

		    if (!empty($vernacular_languages)) {

			    $vernacular_language_name = $vernacular_languages[0]['name'];
				$this->language_options[] = "<option value=\"{$lang_code}\" selected>{$vernacular_language_name}</option>";
				foreach ( $languages as $language ) {

				    if ( $language['name'] != $vernacular_language_name ) {

					    $selected = ($selected_language == $language['language_code']) ? 'selected' : '';
					    $this->language_options[] = "<option value=\"{$language['language_code']}\" $selected>{$language['name']}</option>";
				    }
			    }
		    }
	    }

	    // set up parts of speech dropdown
	    $parts_of_speech = get_terms('sil_parts_of_speech');
	    foreach($parts_of_speech as $part)
	    {
            $selected = ($part->term_id == $taxonomy) ? 'selected' : '';
            $this->parts_of_speech[] = "<option value=\"$part->term_id\" $selected>$part->name</option>";
	    }

	    // set up semantic domains links
        // $sem_domain->term_id . '">'. $sem_domain->slug . ' ' . $sem_domain->description
	    if($search_term !== '')
	    {
		    $escaped = Webonary_Utility::escapeSqlLike($search_term) ;
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
		    $this->sem_domains = $wpdb->get_results( $query );
	    }

	    // set up dictionary info
	    $this->indexed_entries = Webonary_Info::number_of_entries();
	    $this->last_edit_date = $wpdb->get_var("SELECT post_date FROM " . $wpdb->posts . " WHERE post_status = 'publish' AND post_type = 'post' ORDER BY post_date DESC");
    }

    private function GetLanguageDropdown(): string {

        if (empty($this->language_options))
            return '';

        $options = implode(PHP_EOL, $this->language_options);

        return <<<HTML
<select name="key" class="webonary_searchform_language_select form-select">
  $options
</select>
HTML;
    }

    private function GetPartsOfSpeechDropdown(): string {

        if (empty($this->parts_of_speech))
            return '';

        $all_parts_of_speech = __('All Parts of Speech', 'sil_dictionary');
	    $options = implode(PHP_EOL, $this->parts_of_speech);

        return <<<HTML
<select name="tax" id="tax" class="postform form-select" >
  <option value="">$all_parts_of_speech</option>
  $options
</select>
HTML;
    }

    private function GetSpecialButtons(): string {

	    $special_chars = array_filter(explode(',', get_option('special_characters') ?? ''), function($v) {
            return $v != '' && $v != 'empty';
        });

	    $special_buttons = [];
	    foreach($special_chars as $char) {
		    $special_buttons[] = "<button type='button' class='button btn btn-outline-default btn-special-char' value='$char' onClick='addChar(this)'>$char</button>";
	    }

        return implode(' ', $special_buttons);
    }

    private function GetNumberOfEntries(): string {

	    $return_val = '';
	    $reversals = [];

	    foreach($this->indexed_entries as $indexed)
	    {
		    if (empty($indexed->language_name) || in_array($indexed->language_name, $reversals))
			    continue;

		    $return_val .= $indexed->language_name . ':&nbsp;' . $indexed->totalIndexed . '<br>';
		    $reversals[] = $indexed->language_name;
	    }

        return $return_val;
    }

    private function GetPublishedDate(): string {

        global $wpdb;

	    $site_url_no_http = preg_replace('@https?://@m', '', get_bloginfo('wpurl'));

	    $published_date = $wpdb->get_var("SELECT link_updated FROM wp_links WHERE link_url LIKE 'http_://" . trim($site_url_no_http) . "' OR link_url LIKE 'http_://" . trim($site_url_no_http) . "/'");

	    if(!empty($published_date) && $published_date != '0000-00-00 00:00:00')
            return __('Date published:', 'sil_dictionary') . ' ' . Webonary_Utility::GetDateFormatter()->format(strtotime($published_date));

        return '';
    }

    private function GetSemanticDomains($search_term): string {

	    if($search_term == '')
            return '';

        $num_domains = count($this->sem_domains);

	    if($num_domains == 0 || $num_domains > 10)
            return '';

	    $items = [];
	    foreach ($this->sem_domains as $sem_domain ) {
		    $items[] = '<li><a href="?s=&partialsearch=1&tax=' . $sem_domain->term_id . '">'. $sem_domain->slug . ' ' . $sem_domain->description . '</a></li>';
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
}
