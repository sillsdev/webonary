<?php
/** @noinspection SqlResolve */
/** @noinspection HtmlUnknownTarget */
/**
 * A replacement for search box for dictionaries. To use, create searchform.php
 * in the theme, and make a call to this function, like so:
 */

function custom_query_vars_filter($vars) {
	$vars[] = 'match_accents';
	$vars[] = 'match_whole_words';
	$vars[] = 'semantic_domain';
	return $vars;
}
add_filter( 'query_vars', 'custom_query_vars_filter' );

function webonary_searchform($use_li = false): void
{
	global $wpdb, $search_cookie;

	if(get_option('noSearch') == 1)
		return;

	$whole_words_checked = $search_cookie->match_whole_word ? 'checked' : '';
	$accents_checked = $search_cookie->match_accents ? 'checked' : '';

	$search_term = Webonary_Utility::UnicodeTrim(filter_input(INPUT_GET, 's', FILTER_UNSAFE_RAW, ['options' => ['default' => '']]));

	/** @var ILanguageEntryCount[] $arrIndexed */
	$arrIndexed = array();
	$sem_domains = array();

	// set up language dropdown
	$selected_language = $_REQUEST['key'] ?? '';
	$language_dropdown_options = '';

	$lastEditDate = '';
	if (IS_CLOUD_BACKEND) {

		$dictionary = Webonary_Cloud::getDictionary();
		$cloud_domains = Webonary_Cloud::getSemanticDomains();

		if(!is_null($dictionary))
		{
			//set up semantic domains links
			if($search_term !== '' && count($cloud_domains))
			{
				// NOTE: Even though the current non-cloud search does not filter this by language, we should do so in the future
				$sem_term = strtolower($search_term);
				foreach($cloud_domains as $item)
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
			$languages = Webonary_Cloud::GetLanguageList($dictionary);

			/** @noinspection HtmlUnknownAttribute */
			$option_template = '<option value="%s" %s>%s</option>' . PHP_EOL;

			foreach ($languages as $language) {

				if ($language->hidden)
					continue;

				$selected = ($language->language_code == $selected_language) ? 'selected' : '';
				$language_dropdown_options .= sprintf($option_template, $language->language_code, $selected, $language->language_name);
			}

			// get a list of the unique language codes used
			$lang_codes = array_values(array_unique(array_column($languages, 'language_code')));

			// clean up list of languages
			Webonary_Cloud::cleanLanguageList($lang_codes);

			$lastEditDate = $dictionary->updatedAt;

			$arrIndexed = array_filter($languages, fn($lang) => $lang->is_main || $lang->is_reversal);
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

						$localized_name = __($language['name']);

						$language_dropdown_options .= '<option value="' . $language['language_code'] . '"';
						if ( $selected_language == $language['language_code'] ) {
							$language_dropdown_options .= ' selected';
						}
						$language_dropdown_options .= '>' . $localized_name . '</option>';
					}
				}
			}
		}

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
			<?php echo get_special_char_buttons(); ?>
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
			echo Webonary_Parts_Of_Speech::GetDropdown();
			echo Webonary_SemanticDomains::GetDropdown();
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

function get_special_char_buttons(): string
{
	$special_characters = get_option('special_characters');
	$special_characters = str_replace('empty', '', $special_characters);
	if(trim($special_characters) == '')
		return '';

	$css_class = (get_option('special_characters_rtl') == 1 || get_option('vernacularRightToLeft') == 1) ? 'rtl' : 'ltr';

	$arr_char = array_filter(explode(',', $special_characters));
	$btn_html = '<input class="button spbutton %2$s" type="button" value="%1$s" onClick="addChar(this)">';
	$buttons = '';
	foreach ($arr_char as $char) {
		$buttons .= sprintf($btn_html, trim($char), $css_class) . PHP_EOL;
	}

	wp_register_script('webonary_special_chars_script', plugin_dir_url(__DIR__) . 'js/special_characters.js', [], false, true);
	wp_enqueue_script('webonary_special_chars_script');

	return <<<HTML
<div class="special-chars-div $css_class">
$buttons
</div>
HTML;

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

		$localized_name = __($indexed->language_name);
		$num_entries_text .= $localized_name . ':&nbsp;'. $indexed->total_indexed. '<br>';
		$reversals[] = $indexed->language_name;
	}

	if(!empty($lastEditDate) && $lastEditDate != '0000-00-00 00:00:00')
		$last_edit = __('Last upload:', 'sil_dictionary') . '&nbsp;' . Webonary_Utility::FormatLongDate(strtotime($lastEditDate)) . '<br>';
	else
		$last_edit = '';

	$site_url_no_http = preg_replace('@https?://@m', '', get_bloginfo('wpurl'));

	$published_date = $wpdb->get_var("SELECT link_updated FROM wp_links WHERE link_url LIKE '%://" . trim($site_url_no_http) . "' OR link_url LIKE '%://" . trim($site_url_no_http) . "/'");

	if(isset($published_date) && $published_date != "0000-00-00 00:00:00")
		$published = __('Date published:', 'sil_dictionary') . '&nbsp;'. Webonary_Utility::FormatLongDate(strtotime($published_date)) . '<br>';
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
	if (IS_CLOUD_BACKEND && !Webonary_Cloud::HasSemanticDomains())
		return '';

	$domain_count = count($sem_domains);
	if ($search_term === '' || $domain_count == 0 || $domain_count > 10)
		return '';

	$found_header = __('Found in Semantic Domains:', 'sil_dictionary');
	$found_text = '';
	foreach ($sem_domains as $sem_domain) {
		$found_text .= '<li><a href="?s=&partialsearch=1&tax=' . $sem_domain->term_id . '">' . $sem_domain->slug . ' ' . $sem_domain->description . '</a></li>' . PHP_EOL;
	}

	return <<<HTML
<strong>$found_header</strong>
<ul>
$found_text
</ul>
HTML;
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


/**
 * @throws Exception
 */
function add_footer(): void
{
	global $post, $wpdb;

	// for new themes this is implemented through a widget
	$template = wp_get_theme()->get_template();
	if ($template == 'bootscore')
		return;

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

			$alphabetDisplay = Webonary_ShortCodes::VernacularAlphabet($letter);

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

				echo Webonary_Published_Widget::getDictStageFlex( $publicationStatus );
			}
		}
	}
	?>
	<div id="ubaPlayer"></div>
<?php
}

add_action('wp_footer', 'add_footer');
