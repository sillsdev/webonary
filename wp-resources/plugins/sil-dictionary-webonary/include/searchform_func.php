<?php
/** @noinspection SqlResolve */
/** @noinspection HtmlUnknownTarget */
/**
 * A replacement for search box for dictionaries. To use, create searchform.php
 * in the theme, and make a call to this function, like so:
 */

function custom_query_vars_filter($vars) {
	$vars[] .= 'match_accents';
	$vars[] .= 'match_whole_words';
	return $vars;
}
add_filter( 'query_vars', 'custom_query_vars_filter' );

function webonary_searchform($use_li = false): void
{
	global $wpdb, $search_cookie;

	if(get_option('noSearch') == 1)
		return;

	$special_chars_class = get_option('special_characters_rtl') == '1' ? 'rtl' : 'ltr';

	$whole_words_checked = $search_cookie->match_whole_word ? 'checked' : '';
	$accents_checked = $search_cookie->match_accents ? 'checked' : '';

	$selected_parts_of_speech = Webonary_Parts_Of_Speech::GetPartsOfSpeechSelected();
	$search_term = Webonary_Utility::UnicodeTrim(filter_input(INPUT_GET, 's', FILTER_UNSAFE_RAW, ['options' => ['default' => '']]));

	$arrIndexed = array();
	$sem_domains = array();

	// set up language dropdown
	$selected_language = $_REQUEST['key'] ?? '';
	$language_dropdown_options = '';

	$parts_of_speech_dropdown = '';
	$lastEditDate = '';
	if(get_option('useCloudBackend'))
	{
		$dictionaryId = Webonary_Cloud::getBlogDictionaryId();
		$dictionary = Webonary_Cloud::getDictionary($dictionaryId);
		$currentLanguage = Webonary_Cloud::getCurrentLanguage();
		if(!is_null($dictionary))
		{
			// set up parts of speech dropdown
			if(count($dictionary->partsOfSpeech))
			{
				$parts_of_speech = new Webonary_Parts_Of_Speech($currentLanguage, $dictionary->partsOfSpeech, $selected_parts_of_speech);
				$parts_of_speech_dropdown = $parts_of_speech->GetDropdown();
			}

			//set up semantic domains links
			if($search_term !== '' && count($dictionary->semanticDomains))
			{
				// NOTE: Even though the current non-cloud search does not filter this by language, we should do so in the future
				$sem_term = strtolower($search_term);
				foreach($dictionary->semanticDomains as $item)
				{
					if (empty($item->nameInsensitive))
						continue;

					if(str_contains($item->nameInsensitive, $sem_term))
					{
						$sem_domain = new stdClass();
						$sem_domain->term_id = $item->name;
						$sem_domain->slug = str_replace('.', '-', $item->abbreviation);
						$sem_domain->description = $item->name;
						$sem_domains[] = $sem_domain;
					}
				}
			}

			// set up dictionary info
			$mainIndexed = new stdClass();
		    $mainIndexed->language_name = Webonary_Cloud::getLanguageName($dictionary->mainLanguage->lang, $dictionary->mainLanguage->title);
			$mainIndexed->totalIndexed = $dictionary->mainLanguage->entriesCount ?? 0;
			$arrIndexed[] = $mainIndexed;

            $dictionary->reversalLanguages = array_values(array_filter($dictionary->reversalLanguages, function ($v) {
	            return !empty($v->lang);
            }));

			foreach ($dictionary->reversalLanguages as $reversal) {

				$indexed = new stdClass();
				$indexed->language_name = Webonary_Cloud::getLanguageName($reversal->lang, $reversal->title);
				$indexed->totalIndexed = $reversal->entriesCount ?? 0;
				$arrIndexed[] = $indexed;
			}

			// set up languages dropdown options
			$other_search_languages = array_filter($dictionary->reversalLanguages, function($lang) use($dictionary) {
				return $lang->lang != $dictionary->mainLanguage->lang;
			});

			if (count($other_search_languages)) {

				/** @noinspection HtmlUnknownAttribute */
				$option_template = '<option value="%s" %s>%s</option>' . PHP_EOL;

				// add the main language
				$selected = ($dictionary->mainLanguage->lang === $selected_language) ? 'selected' : '';
				$language_dropdown_options .= sprintf($option_template, $dictionary->mainLanguage->lang, $selected, $mainIndexed->language_name);

				// add the reversal languages
				foreach ($other_search_languages as $lang) {

					$selected = ($lang->lang === $selected_language) ? 'selected' : '';
					$language_dropdown_options .= sprintf($option_template, $lang->lang, $selected, $lang->title);
				}
			}

			$lastEditDate = $dictionary->updatedAt;
		}
	} else {

		//$catalog_terms = get_terms('sil_writing_systems');
		$arrLanguages = Webonary_Configuration::get_LanguageCodes();
		if ( ! empty( $arrLanguages ) ) {

        	$lang_code = get_option('languagecode');

			$vernacularLanguages = array_values(array_filter($arrLanguages, function($v) use($lang_code) {
    			return $v['language_code'] == $lang_code;
            }));

			if ( ! empty( $vernacularLanguages ) ) {

				$vernacularLanguageName = $vernacularLanguages[0]['name'];
				$language_dropdown_options .= '<option value="' . $lang_code . '" selected>' . $vernacularLanguageName . '</option>';
				foreach ( $arrLanguages as $language ) {

					if ( $language['name'] != $vernacularLanguageName) {

						$language_dropdown_options .= '<option value="' . $language['language_code'] . '"';
						if ( $selected_language == $language['language_code'] ) {
							$language_dropdown_options .= ' selected';
						}
						$language_dropdown_options .= '>' . $language['name'] . '</option>';
					}
				}
			}
		}

		// set up parts of speech dropdown
		$parts_of_speech = new Webonary_Parts_Of_Speech('en', null, $selected_parts_of_speech);
		$parts_of_speech_dropdown = $parts_of_speech->GetDropdown();

		if($parts_of_speech->HasPartsOfSpeech())
			$parts_of_speech_dropdown = $parts_of_speech->GetDropdown();

		// set up semantic domains links
		if($search_term !== '')
		{
            $escaped = Webonary_Utility::escapeSqlLike($search_term) ;
			$query = <<<SQL
SELECT t.*, tt.* 
FROM $wpdb->terms AS t 
    INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id 
WHERE tt.taxonomy IN ('sil_semantic_domains') 
  AND t.name LIKE '%$escaped%' 
  AND tt.count > 0 
GROUP BY t.name 
ORDER BY t.name
SQL;
			$sem_domains = $wpdb->get_results( $query );
		}

		// set up dictionary info
		$arrIndexed = Webonary_Info::number_of_entries();
		$lastEditDate = $wpdb->get_var("SELECT post_date FROM " . $wpdb->posts . " WHERE post_status = 'publish' AND post_type = 'post' ORDER BY post_date DESC");
	}

	if(get_option('vernacularRightToLeft') == 1 || $special_chars_class == 'rtl')
		echo '<style> .spbutton { float: right; } </style>';

	$input_font = get_option('inputFont');
	if($input_font)
	{
	?>

	<style>
		input, textarea { font-family: "<?php echo $input_font; ?>" !important; }
		#s { font-family: "<?php echo $input_font; ?>" !important; }
	</style>
	<?php
	}

	if ($use_li)
		echo '<li id="search-2" class="widget widget_search">' . PHP_EOL;

	?>
	<form name="searchform" id="searchform" method="get" action="<?php bloginfo('url'); ?>">
		<div class="normalSearch">
			<!-- Search Bar Popups --> <?php !dynamic_sidebar( 'topsearchbar' ); ?><!-- end Search Bar Popups -->
			<!-- search text box -->
			<?php
			$special_characters = get_option('special_characters');
			$special_characters = str_replace('empty', '', $special_characters);
			if((trim($special_characters)) != '') {
			?>
<script type="text/javascript">

function addchar(button) {
	let searchfield = document.getElementById('s');
	let currentPos = theCursorPosition(searchfield);
	let origValue = searchfield.value;
	searchfield.value = origValue.substring(0, currentPos) + button.value.trim() + origValue.substring(currentPos);

	searchfield.focus();

	return true;
}

function theCursorPosition(ofThisInput) {
	// set a fallback cursor location
	let theCursorLocation = 0;

	// find the cursor location via IE method...
	if (document.selection) {
		ofThisInput.focus();
		let theSelectionRange = document.selection.createRange();
		theSelectionRange.moveStart('character', -ofThisInput.value.length);
		theCursorLocation = theSelectionRange.text.length;
	} else if (ofThisInput.selectionStart || ofThisInput.selectionStart === 0) {
		// or the FF way
		theCursorLocation = ofThisInput.selectionStart;
	}
	return theCursorLocation;
}
</script>
			<?php
				$arrChar = explode(",", $special_characters);
				$btn_html = '<input class="button spbutton %2$s" type="button" value="%1$s" onClick="addchar(this)">';
				foreach ($arrChar as $char) {
					printf($btn_html, trim($char), $special_chars_class);
				}
			}
			?>
			<div class="pos-container">
				<?php if (function_exists('qtrans_getLanguage')) {?>
					<!-- I'm not sure why qtrans_getLanguage() is here. It doesn't seem to do anything. -->
					<input type="hidden" id="lang" name="lang" value="<?php echo qtrans_getLanguage(); ?>"/>
				<?php }?>
				<input type="text" name="s" id="s" style="margin: 0 5px 0 0" value="<?php the_search_query(); ?>" size=40 title="">
				<!-- search button -->
				<input type="submit" id="searchsubmit" name="search" style="margin: 0 0 0 5px" value="<?php _e('Search', 'sil_dictionary'); ?>" />
			</div>

			<?php
			if ($language_dropdown_options !== '') {
				$language_dropdown = '<select name="key" class="webonary_searchform_language_select">';
				$language_dropdown .= $language_dropdown_options;
				$language_dropdown .= '</select>';
				echo '<div class="pos-container">' . $language_dropdown . '</div>';
			}
			echo $parts_of_speech_dropdown;
			?>
			<input type="hidden" name="search_options_set" value="1">
			<input id="match_whole_words" name="match_whole_words" value="1" <?php echo $whole_words_checked; ?> type="checkbox"> <label for="match_whole_words"><?php _e('Match whole words', 'sil_dictionary'); ?></label>
			<br>
			<input id="match_accents" name="match_accents" <?php echo $accents_checked; ?> type="checkbox"> <label for="match_accents"><?php _e('Match accents and tones', 'sil_dictionary'); ?></label>
		</div>
	</form>
	<?php

	if ($use_li) {
		echo '</li>' . PHP_EOL;
		echo '<li>' . PHP_EOL . webonary_status($arrIndexed, $lastEditDate) . '</li>' . PHP_EOL;
		echo '<li>' . PHP_EOL . found_semantic_domains($search_term, $sem_domains) . '</li>' . PHP_EOL;
	}
	else {
		echo webonary_status($arrIndexed, $lastEditDate);
		echo found_semantic_domains($search_term, $sem_domains);
	}
}

function webonary_status($indexed_languages, $lastEditDate): string
{
	global $wpdb;

	$num_entries_header = __('Number of Entries', 'sil_dictionary');

	$num_entries_text = '';
	$reversals = [];

	foreach($indexed_languages as $indexed) {
		if (empty($indexed->language_name) || in_array($indexed->language_name, $reversals))
			continue;

		$num_entries_text .= $indexed->language_name . ':&nbsp;'. $indexed->totalIndexed. '<br>';
		$reversals[] = $indexed->language_name;
	}

	if(!empty($lastEditDate) && $lastEditDate != '0000-00-00 00:00:00')
		$last_edit = __('Last upload:', 'sil_dictionary') . '&nbsp;' . Webonary_Utility::GetDateFormatter()->format(strtotime($lastEditDate)) . '<br>';
	else
		$last_edit = '';

	$site_url_no_http = preg_replace('@https?://@m', '', get_bloginfo('wpurl'));

	$published_date = $wpdb->get_var("SELECT link_updated FROM wp_links WHERE link_url LIKE '%://" . trim($site_url_no_http) . "' OR link_url LIKE '%://" . trim($site_url_no_http) . "/'");

	if(isset($published_date) && $published_date != "0000-00-00 00:00:00")
		$published = __('Date published:', 'sil_dictionary') . ':&nbsp;'. Webonary_Utility::GetDateFormatter()->format(strtotime($published_date)) . '<br>';
	else
		$published = '';

	return <<<HTML
	<div class="dictionary-stats">
		<h2 class="widgettitle">$num_entries_header</h2>
		<div class="dictionary-stats" style="padding:5px">
			$num_entries_text
			<br>
			$last_edit
			$published
        </div>
	</div>
HTML;
}

function found_semantic_domains($search_term, $sem_domains): string
{
	if($search_term !== '')
	{
		if(count($sem_domains) > 0 && count($sem_domains) <= 10)
		{
			$found_header = __('Found in Semantic Domains:', 'sil_dictionary');

			$found_text = '';
			foreach ($sem_domains as $sem_domain ) {
				$found_text .= '<li><a href="?s=&partialsearch=1&tax=' . $sem_domain->term_id . '">'. $sem_domain->slug . ' ' . $sem_domain->description . '</a></li>' . PHP_EOL;
			}

			return <<<HTML
<strong>$found_header</strong>
<ul>
	$found_text
</ul>
HTML;
		}
	}

	return '';
}

function add_header(): void
{
	 if(!is_front_page()) {
?>
	<link rel="stylesheet" href="<?php echo get_bloginfo('wpurl'); ?>/wp-content/plugins/sil-dictionary-webonary/audiolibs/css/styles.css" />
	<script src="<?php echo get_bloginfo('wpurl'); ?>/wp-content/plugins/sil-dictionary-webonary/js/jquery.ubaplayer.js" type="text/javascript"></script>
	<script>
	jQuery(function(){
		jQuery("#ubaPlayer").ubaPlayer({
				codecs: [{name:"MP3", codec: 'audio/mpeg'}]
			});
         });
     </script>
<?php
	 }
}

add_action('wp_head', 'add_header');


function getDictStageFlex($status): string
{
	$header = __('Publication Status', 'sil_dictionary');
	$rough = __('Rough draft', 'sil_dictionary');
	$self = __('Self-reviewed draft', 'sil_dictionary');
	$community = __('Community-reviewed draft', 'sil_dictionary');
	$consultant = __('Consultant approved', 'sil_dictionary');
	$no_formal = __('Finished (no formal publication)', 'sil_dictionary');
	$formal = __('Formally published', 'sil_dictionary');

	$status = (int)$status - 1;

    if ($status < 0 || $status > 5)
        $status = 0;

    $active = ['', '', '', '', '', ''];
    $active[$status] = 'active';


	return <<<HTML
<div class="status">
    <p class="center">$header</p>
    
    <div class="status-flex">
        <div class="stage">
            <div class="stage-inner">
                <div class="arrow $active[0]"></div>
                <div class="right-line purple-line"><span class="dot"></span></div>
                <p class="stage-text">$rough</p>
            </div>
        </div>
        <div class="stage">
            <div class="stage-inner">
                <div class="arrow $active[1]"></div>
                <div class="purple-line"><span class="dot"></span></div>
                <p class="stage-text">$self</p>
            </div>
        </div>
        <div class="stage">
            <div class="stage-inner">
                <div class="arrow $active[2]"></div>
                <div class="purple-line"><span class="dot"></span></div>
                <p class="stage-text">$community</p>
            </div>
        </div>    
        <div class="stage">
            <div class="stage-inner">
                <div class="arrow $active[3]"></div>
                <div class="purple-line"><span class="dot"></span></div>
                <p class="stage-text">$consultant</p>
            </div>
        </div>    
        <div class="stage">
            <div class="stage-inner">
                <div class="arrow $active[4]"></div>
                <div class="purple-line"><span class="dot"></span></div>
                <p class="stage-text">$no_formal</p>
            </div>
        </div>
        <div class="stage">
            <div class="stage-inner">
                <div class="arrow $active[5]"></div>
                <div class="left-line purple-line"><span class="dot"></span></div>
                <p class="stage-text">$formal</p>
            </div>
        </div>
    </div>
</div>
HTML;

}

function add_footer(): void
{
	global $post, $wpdb;
	$post_slug = is_null($post) ? '' : $post->post_name;
	if(is_front_page() || $post_slug == 'browse')
	{
		if(get_option('noSearch') != 1)
		{
			$letter = 'frontpage';
			if(isset($_GET['letter']))
			{
				$letter = $_GET['letter'];
			}

			$sql = "SELECT post_title FROM $wpdb->posts WHERE post_content LIKE '%[vernacularalphabet]%'";

			$browse_title = $wpdb->get_var($sql);

			$alphabetDisplay = vernacularalphabet_func($letter);

			if(strlen($alphabetDisplay) > 0)
			{
			?>
			<div style="padding-left: 20px; padding-right: 20px; padding-bottom: 10px;">
				<div style="width: 100%; height: 12px; border-bottom: 1px solid black; text-align: center">
				  <span style="font-size: 16px; background-color: #FFFFFF; padding: 0 10px;">
				    <?php _e($browse_title); ?>
				  </span>
				</div>
				<?php echo $alphabetDisplay; ?>
			</div>

			<?php
			}
		}
		if ( get_option( 'publicationStatus' ) && $post_slug != 'browse' ) {

			$publicationStatus = get_option( 'publicationStatus' );

			if ( $publicationStatus > 0 ) {

				echo getDictStageFlex( $publicationStatus );
			}
		}
	}
	?>
	<div id="ubaPlayer"></div>
<?php
}

add_action('wp_footer', 'add_footer');
