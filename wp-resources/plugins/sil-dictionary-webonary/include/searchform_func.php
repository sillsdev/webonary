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

function webonary_searchform() {
	global $wpdb, $search_cookie;

	if(get_option('noSearch') == 1)
		return;

	$whole_words_checked = $search_cookie->match_whole_word ? 'checked' : '';
	$accents_checked = $search_cookie->match_accents ? 'checked' : '';

	$taxonomy = filter_input(INPUT_GET, 'tax', FILTER_SANITIZE_STRING, ['options' => ['default' => '']]);
	$search_term = filter_input(INPUT_GET, 's', FILTER_UNSAFE_RAW, ['options' => ['default' => '']]);

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
				$options = '';
				foreach($dictionary->partsOfSpeech as $part)
				{
					if ($part->lang === $currentLanguage) {
						$selected = ($part->abbreviation === $taxonomy) ? ' selected ' : '';
						$options .= "<option value=" . $part->abbreviation . $selected . ">" . $part->name . "</option>";
					}
				}

				if ($options !== '') {
					$options = "<option value=''>" . __('All Parts of Speech','sil_dictionary') ."</options>" . $options;
					$parts_of_speech_dropdown = "<select  name='tax' id='tax' class='postform' >" . $options . "</select>";
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
						$sem_domains[] = $sem_domain;
					}
				}
			}

			// set up dictionary info
			$indexed = new stdClass();
			$indexed->language_name = $dictionary->mainLanguage->title ?? $dictionary->mainLanguage->lang;
			$indexed->totalIndexed = $dictionary->mainLanguage->entriesCount ?? 0;
			$arrIndexed[] = $indexed;

            $dictionary->reversalLanguages = array_values(array_filter($dictionary->reversalLanguages, function ($v) {
	            return !empty($v->lang);
            }));

			if (count($dictionary->reversalLanguages)) {
				$selected = ($dictionary->mainLanguage->lang === $selected_language) ? ' selected' : '';
				$language_dropdown_options .= "<option value='" . $dictionary->mainLanguage->lang . "'" . $selected . ">" . $indexed->language_name . "</option>";
				foreach($dictionary->reversalLanguages as $reversal)
				{
					$indexed = new stdClass();
					$indexed->language_name = $reversal->title ?? $reversal->lang;
					$indexed->totalIndexed = $reversal->entriesCount ?? 0;
					$arrIndexed[] = $indexed;

					// set up languages dropdown options
					$selected = ($reversal->lang === $selected_language) ? ' selected' : '';
					$language_dropdown_options .= "<option value='" . $reversal->lang . "'" . $selected . ">" . $indexed->language_name . "</option>";
				}
			}

			$lastEditDate = $dictionary->updatedAt;
		}
	}
	else
	{
		//$catalog_terms = get_terms('sil_writing_systems');
		$arrLanguages = Webonary_Configuration::get_LanguageCodes();
		if ( ! empty( $arrLanguages ) ) {

            $lang_code = get_option('languagecode');

			$vernacularLanguages = array_values(array_filter($arrLanguages, function($v) use($lang_code) {
                return $v['language_code'] == $lang_code;
            }));

			if ( ! empty( $vernacularLanguages ) ) {

				$vernacularLanguageName = $vernacularLanguages[0]['name'];
				foreach ( $arrLanguages as $language ) {

					if ( $language['name'] != $vernacularLanguageName || $language['language_code'] == $lang_code ) {

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
		$parts_of_speech = get_terms('sil_parts_of_speech');
		if($parts_of_speech)
		{
			$parts_of_speech_dropdown = wp_dropdown_categories(
				"show_option_none=" .
				__('All Parts of Speech','sil_dictionary') .
				"&show_count=1&selected=" . $taxonomy .
				"&orderby=name&echo=0&name=tax&taxonomy=sil_parts_of_speech"
			);
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

	?>
	<script type="text/javascript">
	<!--
	window.onload = function()
	{
		<?php
		if(isset($_GET['displayAdvancedSearchName']) && $_GET['displayAdvancedSearchName'] == 1)
		{
		?>
		displayAdvancedSearch();
		<?php
		}
		?>
	}

	function displayAdvancedSearch()
	{
		document.getElementById("advancedSearch").style.display = 'block';
		document.getElementById("advancedSearchLink").style.display = 'none';
		document.getElementById("displayAdvancedSearchId").value = "1";
	}

	function hideAdvancedSearch()
	{
		document.getElementById("advancedSearch").style.display = 'none';
		document.getElementById("advancedSearchLink").style.display = 'block';
		document.getElementById("displayAdvancedSearchId").value = "0";
	}
	-->
	</script>
	<?php
		if(get_option('vernacularRightToLeft') == 1)
		{
		?>
		<style>
		#spbutton {
		 float: right;
		}
		</style>
		<?php
		}
		if(get_option('inputFont') != "")
		{
		?>
			<style>
			input, textarea {
				font-family: "<?php echo get_option('inputFont'); ?>";
			}
			#s {
			font-family: "<?php echo get_option('inputFont'); ?>";
			}
			</style>
		<?php
		}
		?>
		 <form name="searchform" id="searchform" method="get" action="<?php bloginfo('url'); ?>">
			<div class="normalSearch">
				<!-- Search Bar Popups --> <?php !dynamic_sidebar( 'topsearchbar' ); ?><!-- end Search Bar Popups -->
				<!-- search text box -->
				<?php
				$special_characters = get_option('special_characters');
				$special_characters = str_replace("empty", "", $special_characters);
				if((trim($special_characters)) != "")
				{
				?>
				<style>
				select {
					padding: 5px;
				}
				</style>
				<script type="text/javascript">

				function addchar(button)
				{
					let searchfield = document.getElementById('s');
                    let currentPos = theCursorPosition(searchfield);
                    let origValue = searchfield.value;
                    searchfield.value = origValue.substr(0, currentPos) + button.value.trim() + origValue.substr(currentPos);

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
					foreach($arrChar as $char)
					{
					?>
					<input
						id="spbutton" type="button" width="20" class="button"
						value="<?php echo $char; ?>" onClick="addchar(this)"
						style="padding: 5px">
								<?php
					}
				}
				echo "<br>";
				?>
				<input type="text" name="s" id="s" value="<?php the_search_query(); ?>" size=40 title="">

				<!-- I'm not sure why qtrans_getLanguage() is here. It doesn't seem to do anything. -->
				<?php if (function_exists('qtrans_getLanguage')) {?>
					<input type="hidden" id="lang" name="lang" value="<?php echo qtrans_getLanguage(); ?>"/>
				<?php }?>

				<!-- search button -->
				<input type="submit" id="searchsubmit" name="search" value="<?php _e('Search', 'sil_dictionary'); ?>" />
				<br>
				<a id=advancedSearchLink href="#" onclick="displayAdvancedSearch();" style="margin-left: 3px; font-size:14px; text-decoration: underline;"><?php _e('Advanced Search', 'sil_dictionary'); ?></a>
				<div id=advancedSearch style="display:none; border: 0; padding: 2px; font-size: 14px;">
				<a id=advancedSearchLink href="#" onclick="hideAdvancedSearch()" style="font-size:12px; text-decoration: underline;"><?php _e('Hide Advanced Search', 'sil_dictionary'); ?></a>
				<p style="margin-bottom: 6px;"></p>
					<?php
						if ($language_dropdown_options !== '') {
							$language_dropdown  = '<select name="key" class="webonary_searchform_language_select">';
							$language_dropdown .= '<option value="">' . __('All Languages','sil_dictionary') .'</option>';
							$language_dropdown .= $language_dropdown_options;
							$language_dropdown .= '</select>';
							echo $language_dropdown . '<br>';
						}
					?>
					<?php echo $parts_of_speech_dropdown; ?>
					<br>
                    <input type="hidden" name="search_options_set" value="1">
                    <input id="match_whole_words" name="match_whole_words" value="1" <?php echo $whole_words_checked; ?> type="checkbox"> <label for="match_whole_words"><?php _e('Match whole words', 'sil_dictionary'); ?></label>
					<br>
                    <input id="match_accents" name="match_accents" <?php echo $accents_checked; ?> type="checkbox"> <label for="match_accents"><?php _e('Match accents and tones', 'sil_dictionary'); ?></label>
					<input id=displayAdvancedSearchId name="displayAdvancedSearchName" type="hidden" value="0">
				</div>
			</div>
		</form>
		<br>
		<div style="padding:3px; border:none;">
		<h2 class="widgettitle"><?php _e('Number of Entries', 'sil_dictionary'); ?></h2>
		<?php
		$numberOfEntriesText = '';
		$reversals = [];
		foreach($arrIndexed as $indexed)
		{
            if (empty($indexed->language_name) || in_array($indexed->language_name, $reversals))
                continue;

            $numberOfEntriesText .= $indexed->language_name . ':&nbsp;'. $indexed->totalIndexed. '<br>';
			$reversals[] = $indexed->language_name;
		}
		echo $numberOfEntriesText;
		echo '<br>';

		if(!empty($lastEditDate) && $lastEditDate != '0000-00-00 00:00:00')
		{
			_e('Last update:', 'sil_dictionary');
            echo ' ' . Webonary_Utility::GetDateFormatter()->format(strtotime($lastEditDate));
		}

		$siteurlNoHttp = preg_replace('@https?://@m', '', get_bloginfo('wpurl'));

		$publishedDate = $wpdb->get_var("SELECT link_updated FROM wp_links WHERE link_url LIKE 'http_://" . trim($siteurlNoHttp) . "' OR link_url LIKE 'http_://" . trim($siteurlNoHttp) . "/'");

		if(isset($publishedDate) && $publishedDate != "0000-00-00 00:00:00")
		{
			echo '<br>';
			_e('Date published:', 'sil_dictionary');
            echo ' ' . Webonary_Utility::GetDateFormatter()->format(strtotime($publishedDate));
		}
		?>
		</div>
		<?php
		if($search_term !== '')
		{
			if(count($sem_domains) > 0 && count($sem_domains) <= 10)
			{
				echo "<p>&nbsp;</p>";
				echo "<strong>";
				 _e('Found in Semantic Domains:', 'sil_dictionary');
				echo "</strong>";
				echo "<ul>";
				foreach ($sem_domains as $sem_domain ) {
				  echo '<li><a href="?s=&partialsearch=1&tax=' . $sem_domain->term_id . '">'. $sem_domain->slug . ' ' . $sem_domain->description . '</a></li>';
				}
				echo "</ul>";
			}
		}
		?>
<?php
}

function add_header()
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

function add_footer()
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
