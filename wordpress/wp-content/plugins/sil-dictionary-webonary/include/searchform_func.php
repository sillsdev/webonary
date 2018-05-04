<?php
/**
 * A replacement for search box for dictionaries. To use, create searchform.php
 * in the theme, and make a call to this function, like so:
 */
function searchform_init() {
	/*
	 * Load the translated strings for the plugin.
	 */
    load_plugin_textdomain('sil_dictionary', false, dirname(plugin_basename(__FILE__ )).'/lang/');
}

function webonary_searchform() {
	global $wpdb;

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
				<script LANGUAGE="JavaScript">
				<!--
				window.onload = function(e)
				{
					<?php
					if($_GET['displayAdvancedSearch'] == 1)
					{
					?>
					displayAdvancedSearch();
					<?php
					}
					?>
				}

				function addchar(button)
				{
					var searchfield = document.getElementById('s');
					var currentPos = theCursorPosition(searchfield);
					var origValue = searchfield.value;
					var newValue = origValue.substr(0, currentPos) + button.value.trim() + origValue.substr(currentPos);

					searchfield.value = newValue;

					searchfield.focus();

				    return true;
				}

				function theCursorPosition(ofThisInput) {
					// set a fallback cursor location
					var theCursorLocation = 0;

					// find the cursor location via IE method...
					if (document.selection) {
						ofThisInput.focus();
						var theSelectionRange = document.selection.createRange();
						theSelectionRange.moveStart('character', -ofThisInput.value.length);
						theCursorLocation = theSelectionRange.text.length;
					} else if (ofThisInput.selectionStart || ofThisInput.selectionStart == '0') {
						// or the FF way
						theCursorLocation = ofThisInput.selectionStart;
					}
					return theCursorLocation;
				}

				function displayAdvancedSearch()
				{
					document.getElementById("advancedSearch").style.display = 'block';
					document.getElementById("advancedSearchLink").style.display = 'none';
					document.getElementById("displayAdvancedSearch").value = "1";
				}

				function hideAdvancedSearch()
				{
					document.getElementById("advancedSearch").style.display = 'none';
					document.getElementById("advancedSearchLink").style.display = 'block';
					document.getElementById("displayAdvancedSearch").value = "0";
				}
				-->
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
				<input type="text" name="s" id="s" value="<?php the_search_query(); ?>" size=40>

				<!-- I'm not sure why qtrans_getLanguage() is here. It doesn't seem to do anything. -->
				<?php if (function_exists('qtrans_getLanguage')) {?>
					<input type="hidden" id="lang" name="lang" value="<?php echo qtrans_getLanguage(); ?>"/>
				<?php }?>

				<!-- search button -->
				<input type="submit" id="searchsubmit" name="search" value="<?php _e('Search', 'sil_dictionary'); ?>" />
				<br>
				<a id=advancedSearchLink href="#" onclick="displayAdvancedSearch()" style="margin-left: 3px; font-size:14px; text-decoration: underline;"><?php echo _e('Advanced Search', 'sil_dictionary'); ?></a>
				<div id=advancedSearch style="display:none; border: 0px; padding: 2px; font-size: 14px;">
				<a id=advancedSearchLink href="#" onclick="hideAdvancedSearch()" style="font-size:12px; text-decoration: underline;"><?php echo _e('Hide Advanced Search', 'sil_dictionary'); ?></a>
				<br style="margin-bottom: 6px;">
					<?php
					$key = $_POST['key'];
					if(!isset($_POST['key']))
					{
						$key = $_GET['key'];
					}


					//$catalog_terms = get_terms('sil_writing_systems');
					$arrLanguages = get_LanguageCodes();
					$arrVernacularLanguage = get_LanguageCodes(get_option('languagecode'));
					?>
					<select name="key" class="webonary_searchform_language_select">
					<option value="">
					<?php _e('All Languages','sil_dictionary'); ?>
					</option>
					<?php
					foreach ($arrLanguages as $language)
					{
						if($language->name != $arrVernacularLanguage[0]->name || ($language->name == $arrVernacularLanguage[0]->name && $language->language_code == get_option('languagecode')))
						{
					?>
						<option value="<?php echo $language->language_code; ?>"
							<?php if($key == $language->language_code) {?>selected<?php }?>>
							<?php echo $language->name; ?>
						</option>
						<?php
						}
					}
					?>
					</select>
					<?php

					/*
					 * Set up the Parts of Speech
					 */
					$parts_of_speech = get_terms('sil_parts_of_speech');

					if($parts_of_speech)
					{
						wp_dropdown_categories("show_option_none=" .
							__('All Parts of Speech','sil_dictionary') .
							"&show_count=1&selected=" . $_GET['tax'] .
							"&orderby=name&echo=1&name=tax&taxonomy=sil_parts_of_speech");
					}
					?>
					<br>
					<?php
					$checkedWholeWords = "";

					if(isset($_GET['search']))
					{
						if(isset($_GET['match_whole_words']))
						{
							$checkedWholeWords = "checked";
						}
					}
					else
					{
						if(get_option('include_partial_words') == 0)
						{
							$checkedWholeWords = "checked";
						}
					}
					?>
					<input name="match_whole_words" value="1" <?php echo $checkedWholeWords; ?> type="checkbox"> Match whole words
					<br>
					<?php
					$match_accents = false;
					if(isset($_GET['match_accents']))
					{
						$match_accents = true;
					}
					?>
					<input name="match_accents" <?php checked('1', $match_accents); ?> type="checkbox"> Match accents and tones
					<input id=displayAdvancedSearch name="displayAdvancedSearch" type="hidden" value="0">
				</div>
			</div>
		</form>
		<br>
		<div style="padding:3px; border:none;">
		<h2 class="widgettitle"><?php _e('Number of Entries', 'sil_dictionary'); ?></h2>
		<?php
		$import = new sil_pathway_xhtml_Import();

		$arrIndexed = $import->get_number_of_entries();

		$numberOfEntriesText = "";
		foreach($arrIndexed as $indexed)
		{
			$numberOfEntriesText .= $indexed->language_name . ":&nbsp;". $indexed->totalIndexed;
			$numberOfEntriesText .= "<br>";
		}
		echo $numberOfEntriesText;
		echo "<br>";
		$lastEditDate = $wpdb->get_var("SELECT post_date FROM " . $wpdb->posts . " WHERE post_status = 'publish' AND post_type = 'post' ORDER BY post_date DESC");

		if(isset($lastEditDate) && $lastEditDate != "0000-00-00 00:00:00")
		{
			_e('Last update:', 'sil_dictionary'); echo " " . strftime("%b %e, %Y", strtotime($lastEditDate));
		}

		$siteurlNoHttp = str_replace("https://", "", get_bloginfo('wpurl'));
		$siteurlNoHttp = str_replace("http://", "", $siteurlNoHttp);

		$publishedDate = $wpdb->get_var("SELECT link_updated FROM wp_links WHERE link_url LIKE '%" . $siteurlNoHttp . "%'");
		if(isset($publishedDate) && $publishedDate != "0000-00-00 00:00:00")
		{
			echo "<br>";
			_e('Date published:', 'sil_dictionary'); echo " " . strftime("%b %e, %Y", strtotime($publishedDate));
		}
		?>
		</div>
		<?php
		if(strlen(trim($_GET['s'])) > 0)
		{
			//$sem_domains = get_terms( 'sil_semantic_domains', 'name__like=' .  trim($_GET['s']) .'');
			$query = "SELECT t.*, tt.* FROM " . $wpdb->terms . " AS t INNER JOIN " . $wpdb->term_taxonomy . " AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy IN ('sil_semantic_domains') AND t.name LIKE '%" . trim($_GET['s']) . "%' AND tt.count > 0 GROUP BY t.name ORDER BY t.name ASC";
    		$sem_domains = $wpdb->get_results( $query );

			if(count($sem_domains) > 0 && count($sem_domains) <= 10)
			{
				echo "<p>&nbsp;</p>";
				echo "<strong>";
				 _e('Found in Semantic Domains:', 'sil_dictionary');
				echo "</strong>";
				echo "<ul>";
				foreach ($sem_domains as $sem_domain ) {
				  echo '<li><a href="?s=&partialsearch=1&tax=' . $sem_domain->term_id . '">'. $sem_domain->description . '</a></li>';
				}
				echo "</ul>";
			}
		}
		?>
<?php
}

add_action('init', 'searchform_init');

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

function getDictStageImage($publicationStatus, $language)
{
	if($language == "en")
	{
		$language = "";
	}
	$DictStage = "/wp-content/plugins/sil-dictionary-webonary/images/status/DictStage" . $publicationStatus . strtolower($language) . ".png";

	if(file_exists(ABSPATH . $DictStage))
	{
		echo $DictStage;
	}
	else
	{
		getDictStageImage($publicationStatus, "");
	}
}

function add_footer()
{
	global $post, $wpdb;
	$post_slug = $post->post_name;
	if(is_front_page() || $post_slug == "browse")
	{
		$arrLanguageCodes = get_LanguageCodes();

		$letter = "frontpage";
		if(isset($_GET['letter']))
		{
			$letter = $_GET['letter'];
		}
		$x = 0;
		foreach($arrLanguageCodes as $languagecode)
		{
			 if(get_option('languagecode') == $languagecode->language_code)
			 {
			 	$i = $x;
			 }
			$x++;
		}

		$sql = "SELECT post_title FROM $wpdb->posts WHERE post_content LIKE '%[vernacularalphabet]%'";
		$browse_title = $wpdb->get_var($sql);

		?>
		<div style="padding-left: 20px; padding-right: 20px; padding-bottom: 10px;">
			<div style="width: 100%; height: 12px; border-bottom: 1px solid black; text-align: center">
			  <span style="font-size: 16px; background-color: #FFFFFF; padding: 0 10px;">
			    <?php echo $browse_title; ?>
			  </span>
			</div>
			<?php echo vernacularalphabet_func($letter); ?>
		</div>

		<?php
		if(get_option('publicationStatus') && $post_slug != "browse")
		{
			$publicationStatus = get_option('publicationStatus');
			if($publicationStatus > 0) {

				$language = "";
				if (function_exists('qtranxf_getLanguage')) {
					$language = qtranxf_getLanguage();
				}
			?>

			<div align=center><img src="<?php getDictStageImage($publicationStatus, $language); ?>" style="padding: 5px; max-width: 100%;"></div>
		<?php
			}
		}
	}
	?>
	<div id="ubaPlayer"></div>
<?php
}

add_action('wp_footer', 'add_footer');
?>