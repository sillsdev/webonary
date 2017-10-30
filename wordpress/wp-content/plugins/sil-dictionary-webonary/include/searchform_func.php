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
				<script LANGUAGE="JavaScript">
				<!--
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
				if ($catalog_terms) {
					?>
					<!-- If you need to control the width of the dropdown, use the
					class webonary_searchform_language_select in your theme .css -->
					<select name="key" class="webonary_searchform_language_select">
					<option value="">
						<?php _e('All Languages','sil_dictionary'); ?>
					</option>
					<?php
					foreach ($catalog_terms as $catalog_term)
					{ ?>
						<option value="<?php echo $catalog_term->slug; ?>"
							<?php if($key == $catalog_term->slug) {?>selected<?php }?>>
							<?php echo $catalog_term->name; ?>
						</option>
						<?php
					}
					?>
					</select>
					<br>
					<?php
				}
				*/
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
?>
	<?php
	if(get_option('publicationStatus'))
	{
		$publicationStatus = get_option('publicationStatus');
		if(is_front_page() && $publicationStatus > 0) {

			$language = "";
			if (function_exists('qtranxf_getLanguage')) {
				$language = qtranxf_getLanguage();
			}
		?>

		<div align=center><img src="<?php getDictStageImage($publicationStatus, $language); ?>" style="padding: 5px; max-width: 100%;"></div>
	<?php
		}
	}
	?>
	<div id="ubaPlayer"></div>
<?php
}

add_action('wp_footer', 'add_footer');
?>