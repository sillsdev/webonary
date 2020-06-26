<?php /** @noinspection SqlResolve */
/** @noinspection HtmlFormInputWithoutLabel */
/** @noinspection HtmlUnknownTarget */

add_action('wp_ajax_getAjaxLanguage', 'Webonary_Ajax::ajaxLanguage');
add_action('wp_ajax_nopriv_getAjaxLanguage', 'Webonary_Ajax::ajaxLanguage');
add_action('wp_ajax_getAjaxCurrentIndexedCount', 'Webonary_Ajax::ajaxCurrentIndexedCount');
add_action('wp_ajax_getAjaxCurrentImportedCount', 'Webonary_Ajax::ajaxCurrentImportedCount');
add_action('wp_ajax_getAjaxRestartIndexing', 'Webonary_Ajax::ajaxRestartIndexing');

function relevanceSave()
{
	global $wpdb;

	$tableCustomRelevance = $wpdb->prefix . 'custom_relevance';

	$class_names = Webonary_Filters::PostArray('classname');
	$relevances = Webonary_Filters::PostArray('relevance');

	for ($i = 0; $i < count($class_names); $i++) {

		$relevance = intval($relevances[$i]);
		if ($relevance < 0 || $relevance > 99) {
			echo '<span style="color: red;">Relevance has to be >= 0 and < 100 for all fields!</span><br>';
			return false;
		}

		$class_name = $class_names[$i];

		$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}sil_search SET relevance = %s WHERE class = %s", $relevance, $class_name));

		$found = Webonary_Db::GetBool("SELECT COUNT(*) FROM {$tableCustomRelevance} WHERE class = %s", $class_name);

		if ($found) {
			$wpdb->query($wpdb->prepare("UPDATE {$tableCustomRelevance} SET relevance = %s WHERE class = %s", $relevance, $class_name));
		}
		else {
			$wpdb->query($wpdb->prepare("INSERT INTO {$tableCustomRelevance} (relevance, class) VALUES (%s, %s)", $relevance, $class_name));
		}
	}

	$r = 0;
	foreach($_POST['classname'] as $class)
	{
		if($_POST['relevance'][$r] < 0 || $_POST['relevance'][$r] > 99 || !is_numeric($_POST['relevance'][$r]))
		{
			echo '<span style="color: red;">Relevance has to be >= 0 and < 100 for all fields!</span><br>';
			return false;
		}
		//echo $class . ": " . $_POST['relevance'][$r] . "<br>";

		$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}sil_search SET relevance = %s WHERE class = %s", $_POST['relevance'][$r], $class));

		$result = $wpdb->get_results($wpdb->prepare('SELECT relevance FROM $tableCustomRelevance WHERE class = %s', $class));

		if (count ($result) > 0) {
			$wpdb->query($wpdb->prepare("UPDATE {$tableCustomRelevance} SET relevance = %s WHERE class = %s", $_POST['relevance'][$r], $class));
		}
		else {
			$wpdb->query($wpdb->prepare("INSERT INTO {$tableCustomRelevance} (class, relevance) VALUES (%s, %s)", $class, $_POST['relevance'][$r]));
		}

		$r++;
	}

	$wpdb->print_error();

	if($wpdb->last_error === '')
	{
		echo "<h3>Relevance Settings were saved.</h3>";
	}
	echo "<hr>";

	return true;
}

//display the senses that don't get linked in the reversal browse view
function report_missing_senses()
{
	global $wpdb;

	$sql = <<<SQL
SELECT search_strings
FROM {$wpdb->prefix}sil_search
WHERE post_id = 0 AND language_code = '{$_GET['languageCode']}'
SQL;

	$arrMissing = $wpdb->get_results($sql);
	$missing_items = '';
	foreach($arrMissing as $missing) {
		$missing_items .= "<li>{$missing->search_strings}</li>\n";
	}

	$html = <<<HTML
	<div class="wrap">
		<h2>Missing Senses for the {$_GET['language']} browse view</h2>
		One or more senses will not get found for the following entries when clicking on them in the browse view.<br>
		Please check in the FLEx dictionary view, if they show up there.
		<ul>
			{$missing_items}
		</ul>
		<a href="admin.php?page=webonary">Back to the Webonary settings</a>
	</div>
HTML;

	echo $html;
}

/**
 * Do what the user said to do.
 */
function save_configurations()
{
	global $wpdb;

	if (!empty($_POST['delete_data'])) {
		clean_out_dictionary_data();
	}
	if (!empty($_POST['save_settings'])) {
		update_option('publicationStatus', $_POST['publicationStatus']);
		update_option('include_partial_words', $_POST['include_partial_words']);
		update_option('searchSomposedCharacters', $_POST['search_composed_characters']);
		//update_option('distinguish_diacritics', $_POST['distinguish_diacritics']);
		if(isset($_POST['normalization'])) {
			update_option('normalization', $_POST['normalization']);
		}

		$special_characters = trim($_POST['characters']);
		if(empty($special_characters))
			$special_characters = 'empty';

		update_option('special_characters', $special_characters);
		update_option('inputFont', $_POST['inputFont']);
		update_option('vernacularLettersFont', $_POST['vernacularLettersFont']);

		//We no longer give the option to set this (only to unset it) as this can be done in FLEx
		update_option('DisplaySubentriesAsMainEntries', (isset($_POST['DisplaySubentriesAsMainEntries']) ? 1 : 'no'));
		update_option('languagecode', $_POST['languagecode']);
		if(is_super_admin()) {
			update_option('vernacular_alphabet', $_POST['vernacular_alphabet']);
		}

		//We no longer give the option to set this (only to unset it) as the letter headers/sorting should be done in FLEx
		update_option('IncludeCharactersWithDiacritics', (isset($_POST['IncludeCharactersWithDiacritics']) ? 1 : 'no'));

		update_option('displayCustomDomains', $_POST['displayCustomDomains']);

		update_option('vernacularRightToLeft', (isset($_POST['vernacularRightToLeft']) ? 1 : 'no'));

		update_option('reversal1_langcode', $_POST['reversal1_langcode']);
		update_option('reversal2_langcode', $_POST['reversal2_langcode']);
		update_option('reversal3_langcode', $_POST['reversal3_langcode']);
		if(is_super_admin())
		{
			update_option('reversal1_alphabet', $_POST['reversal1_alphabet']);
			update_option('reversal2_alphabet', $_POST['reversal2_alphabet']);
			update_option('reversal3_alphabet', $_POST['reversal3_alphabet']);
		}

		update_option('reversal1RightToLeft', (isset($_POST['reversal1RightToLeft']) ? 1 : 'no'));
		update_option('reversal2RightToLeft', (isset($_POST['reversal2RightToLeft']) ? 1 : 'no'));
		update_option('reversal3RightToLeft', (isset($_POST['reversal3RightToLeft']) ? 1 : 'no'));

		if(trim(strlen($_POST['txtVernacularName'])) == 0)
			echo '<br><span style="color:red">Please fill out the text fields for the language names, as they will appear in a dropdown below the search box.</span><br>';

		$arrLanguages[0]['name'] = 'txtVernacularName';
		$arrLanguages[0]['code'] = 'languagecode';
		$arrLanguages[1]['name'] = 'txtReversalName';
		$arrLanguages[1]['code'] = 'reversal1_langcode';
		$arrLanguages[2]['name'] = 'txtReversal2Name';
		$arrLanguages[2]['code'] = 'reversal2_langcode';
		$arrLanguages[3]['name'] = 'txtReversal3Name';
		$arrLanguages[3]['code'] = 'reversal3_langcode';

		foreach($arrLanguages as $language) {

			if(strlen(trim($_POST[$language['code']])) != 0) {

				$sql = $wpdb->prepare("SELECT term_id, `name` FROM {$wpdb->terms} WHERE slug = %s", array($_POST[$language['code']]));
				$arrLanguageNames = $wpdb->get_results($sql);

				if(count($arrLanguageNames) > 0) {
					$sql = $wpdb->prepare("UPDATE {$wpdb->terms} SET `name` = %s WHERE slug = %s", array($_POST[$language['name']], $_POST[$language['code']]));
					$wpdb->query($sql);
					$term_id = $arrLanguageNames[0]->term_id;
				}
				else {
					$sql = $wpdb->prepare("INSERT INTO {$wpdb->terms} (`name`, slug) VALUES (%s, %s)", array($_POST[$language['name']], $_POST[$language['code']]));
					$wpdb->query($sql);
					$term_id = $wpdb->insert_id;
				}

				if(count($arrLanguageNames) > 0)
				{
					$sql = "UPDATE $wpdb->term_taxonomy SET description = '" . $_POST[$language['name']] . "' WHERE term_id = " . $term_id;
				}
				else
				{
					$sql = "INSERT INTO  $wpdb->term_taxonomy (term_id, taxonomy,description,count) VALUES (" . $term_id . ", 'sil_writing_systems', '" . $_POST[$language['name']] . "',999999)";
				}

				$wpdb->query($sql);
			}
		}

		if(isset($_POST['txtNotes']))
		{
			update_option("notes", $_POST['txtNotes']);
		}

		$noSearchForm = 0;
		if(isset($_POST['noSearchForm']))
		{
			$noSearchForm = $_POST['noSearchForm'];
			if (is_plugin_active('wp-super-cache/wp-cache.php') && $noSearchForm == 1)
			{
				/** @noinspection PhpUndefinedFunctionInspection */
				prune_super_cache(get_supercache_dir(), true);
			}

		}
		update_option("noSearch", $noSearchForm);

		$useCloudBackend = filter_input(
			INPUT_POST, 
			'useCloudBackend', 
			FILTER_SANITIZE_STRING, 
			array('options' => array('default' => '')));

		if($useCloudBackend != get_option('useCloudBackend', ''))
		{
			if (is_plugin_active('wp-super-cache/wp-cache.php'))
			{
				/** @noinspection PhpUndefinedFunctionInspection */
				prune_super_cache(get_supercache_dir(), true);
			}
			update_option('useCloudBackend', $useCloudBackend);

			// initial set up of dictionary using cloud values
			if ($useCloudBackend) {
				$dictionaryId = Webonary_Cloud::getBlogDictionaryId();
				Webonary_Cloud::resetDictionary($dictionaryId);
			}
		} 
		
		echo "<br>" . _e('Settings saved');
	}
}

function webonary_conf_dashboard()
{
	webonary_conf_widget(true);
}

function webonary_conf_widget($showTitle = false)
{
	save_configurations();

	$upload_dir = wp_upload_dir();

	$fontClass = new Webonary_Font_Management();
	$css_string = null;
	$configured_css_file = $upload_dir['basedir'] . '/imported-with-xhtml.css';
	if(file_exists($configured_css_file))
		$css_string = file_get_contents($configured_css_file);

	wp_register_style('custom_css', $upload_dir['baseurl'] . '/custom.css?time=' . date("U"));
	wp_enqueue_style('custom_css');

	if(is_super_admin() && isset($_POST['uploadButton']))
	{
		$fontClass->uploadFontForm();
	}

	if(isset($_GET['changerelevance']))
		echo Webonary_Configuration::relevanceForm();

	if(isset($_POST['uploadFont']))
		$fontClass->uploadFont();

	if(isset($_POST['btnSaveRelevance']))
		relevanceSave();

	?>
	<script src="<?php echo get_bloginfo('wpurl'); ?>/wp-content/plugins/sil-dictionary-webonary/js/options.js" type="text/javascript"></script>
	<style>
		#dashboard-widgets a {text-decoration: underline;}
		<?php
		$input_font = get_option('inputFont', '');
		if (!empty($input_font))
			echo "#characters {font-family: \"{$input_font}\";}" . PHP_EOL;

		$vernacular_font = get_option('vernacularLettersFont', '');
		if (!empty($vernacular_font))
			echo "#vernacularAlphabet {font-family: \"{$vernacular_font}\";}" . PHP_EOL;

		?>
	</style>
	<div class="wrap">
		<?php
		if($showTitle)
			echo '<h2>' . _e('Webonary', 'webonary') . '</h2>' . PHP_EOL;

		_e('Webonary provides the administration tools and framework for using WordPress for dictionaries. See <a href="https://www.webonary.org/help" target="_blank">Webonary Support</a> for help.', 'sil_dictionary');

		$admin_sections = Webonary_Configuration::get_admin_sections();
		echo '<h2 class="nav-tab-wrapper">' . PHP_EOL;
		foreach($admin_sections as $slug => $name){
			echo '<a class="nav-tab" href="#'.$slug.'" title="'.sprintf(__('Click to switch to %s', 'sil_dictionary'), $name).'">'.$name.'</a>' . PHP_EOL;
		}
		echo '</h2>' . PHP_EOL;

		$arrLanguageCodes = Webonary_Configuration::get_LanguageCodes();

		// enctype="multipart/form-data"
		?>
		<script>
			function getLanguageName(selectbox, langname)
			{
				let e = document.getElementById(selectbox);
				let langcode = e.options[e.selectedIndex].value;

				jQuery.ajax({
					url: '<?php echo admin_url('admin-ajax.php'); ?>',
					data : {action: "getAjaxLanguage", languagecode : langcode},
					type:'POST',
					dataType: 'html',
					success: function(output_string){
						jQuery('#' + langname).val(output_string);
					}
				})
			}
		</script>
		<div id="icon-tools" class="icon32"></div>
		<?php
		/*
		 * Standard UI
		 */
		?>
		<div class="tabs-content">
			<?php
			Webonary_Configuration::admin_section_start('import');
			?>
			<h3><?php _e('Import Data', 'sil_dictionary'); ?></h3>

			<form method="post" action="admin.php?import=pathway-xhtml&step=2">
				<div style="max-width: 600px; border-style:solid; border-width: 1px; border-color: red; padding: 5px;">
					<strong>Import Status:</strong> <?php echo Webonary_Info::import_status(); ?>
				</div>
			</form>

			<p><a href="admin.php?import=pathway-xhtml" style="font-size:16px;"><?php _e('Click here to import your FLEx data', 'sil_dictionary'); ?></a></p>

			<form id="configuration-form" method="post" action="">
				<button type="submit" disabled style="display: none" aria-hidden="true"></button>

				<p>
					<?php _e('Publication status:'); ?>
					<select name=publicationStatus>
						<option value=0><?php _e('no status set'); ?></option>
						<option value=1 <?php selected(get_option('publicationStatus'), 1); ?>><?php _e('Rough draft'); ?></option>
						<option value=2 <?php selected(get_option('publicationStatus'), 2); ?>><?php _e('Self-reviewed draft'); ?></option>
						<option value=3 <?php selected(get_option('publicationStatus'), 3); ?>><?php _e('Community-reviewed draft'); ?></option>
						<option value=4 <?php selected(get_option('publicationStatus'), 4); ?>><?php _e('Consultant approved'); ?></option>
						<option value=5 <?php selected(get_option('publicationStatus'), 5); ?>><?php _e('Finished (no formal publication)'); ?></option>
						<option value=6 <?php selected(get_option('publicationStatus'), 6); ?>><?php _e('Formally published'); ?></option>
					</select>
				</p>

				<h3><?php _e('Delete Data', 'sil_dictionary'); ?></h3>
				<p>
					<?php if(strpos($_SERVER['HTTP_HOST'], 'localhost') === false && is_super_admin()) { ?>
						<strong style=color:red;>You are not in your testing environment!</strong>
						<br>
					<?php } ?>
					<?php
					if(!empty($_GET['delete_taxonomies']) && $_GET['delete_taxonomies']== 1)
					{
						_e('Lists are kept unless you check the following:'); ?><br>
						<label for="delete_taxonomies">
							<input name="delete_taxonomies" type="checkbox" id="delete_taxonomies" value="1"
								<?php checked('1', get_option('delete_taxonomies')); ?> />
							<?php _e('Delete lists such as Part of Speech?') ?>
						</label>
						<br>
						<?php
					}
					else
					{
						?>
						<input type="hidden" name="delete_taxonomies" value="1">
						<?php
					}
					?>
					<?php _e('Are you sure you want to delete the dictionary data?', 'sil_dictionary'); ?>
					<input style="margin-left: 8px" class="button button-webonary" type="submit" name="delete_data" value="<?php _e('Delete', 'sil_dictionary'); ?>">
					<br>
					<?php _e('(deletes all posts in the category "webonary")', 'sil_dictionary'); ?>
				</p>

				<?php
				Webonary_Configuration::admin_section_end('import', 'Save Changes');

				Webonary_Configuration::admin_section_start('search');
				?>

				<h3><?php _e('Default Search Options');?></h3>

				<p>
					<input name="include_partial_words" type="checkbox" value="1"
						<?php checked('1', get_option('include_partial_words')); ?> />
					<?php _e('Always include searching through partial words.'); ?>
					<br>
					<?php
					if(get_option('hasComposedCharacters') == 1)
					{
						?>
						<input name="search_composed_characters" type="checkbox" value="1"
							<?php checked('1', get_option('searchSomposedCharacters')); ?> />
						Search for composed characters using base characters (<a href="https://www.webonary.org/searching-for-composed-characters-using-base-characters/" target="_blank">help</a>)
						<br>
						<?php
					}
					//this is only for legacy purposes.
					//Now the import will convert all text to NFC, so this is no longer needed for newer imports
					$normalization = get_option('normalization', '');
					if (!empty($normalization))
					{
						?>
						<strong>Normalization:</strong>
						<br>
						<select name="normalization">
							<option value="FORM_C" <?php if($normalization == 'FORM_C') { echo 'selected'; } ?>>FORM C</option>
							<option value="FORM_D" <?php if($normalization == 'FORM_D') { echo 'selected'; } ?>>FORM D</option>
						</select>
						<br>
						See <a href="http://unicode.org/reports/tr15/" target="_blank">here</a> for more info on normalization of composite characters.
						<br>
						By default Webonary uses FORM C. If your search for a word that contains
						a composite character doesn't return a result, try FORM D.

						<?php
					}
					?>
				</p>
				<?php
				if(class_exists('special_characters'))
				{
					//this is here for legacy purposes. The special characters used to be Widget in a separate plugin.
					//we need to get those characters for older dictionary sites and display them in the dashboard.
					/** @noinspection PhpUndefinedClassInspection */
					$charWidget = new special_characters();
					/** @noinspection PhpUndefinedMethodInspection */
					$settings = $charWidget->get_settings();
					$settings = reset($settings);
				}
				$special_characters = get_option('special_characters');
				if(!empty($settings['characters'])
					&& trim($special_characters) == ''
					&& !isset($_POST['characters'])
					&& trim($special_characters) != 'empty')
				{
					$special_characters = $settings['characters'];
				}
				$special_characters = str_replace('empty', '', $special_characters);
				?>
				<p>
					<strong><?php _e('Special character input buttons');?></strong>
					<br>
					These will appear above the search field.<br>
					Separate the characters by comma:
					<input type="text" name="characters" id=characters value="<?php echo $special_characters; ?>">
				</p>
				<b>Font to use for the search field and character buttons:</b>
				<br>
				<select name=inputFont>
					<option value=""></option>
					<?php
					$arrUniqueCSSFonts = $fontClass->get_fonts_fromCssText($css_string);
					if(isset($arrUniqueCSSFonts))
					{
						foreach($arrUniqueCSSFonts as $font)
						{
							?>
							<option value="<?php echo $font;?>" <?php if($font == $input_font) { echo 'selected'; } ?>><?php echo $font;?></option>
							<?php
						}
					}
					?>
				</select>
				<br><br>
				<!--suppress HtmlUnknownAnchorTarget -->
				<a href="?page=webonary&changerelevance=true#search">Relevance Settings for Fields</a>
				<br><br>
				<?php
				Webonary_Configuration::admin_section_end('search', 'Save Changes');

				Webonary_Configuration::admin_section_start('browse');
				?>

				<h3>Browse Views</h3>
				See <a href="https://www.webonary.org/help/creating-browse-views/" target="_blank">Help with creating Browse Views</a>
				<p>
					<?php
					$DisplaySubentriesAsMainEntries = get_option('DisplaySubentriesAsMainEntries');
					if($DisplaySubentriesAsMainEntries == 1)
					{
					?>
					<input name="DisplaySubentriesAsMainEntries" type="checkbox" value="1"
						<?php checked('1', $DisplaySubentriesAsMainEntries); ?> />
					<?php _e('Display subentries as main entries'); ?>
				</p>
				<p>
					<?php
					}
					if(count($arrLanguageCodes) == 0)
					{
						?>
						<span style="color:red">You need to first import your dictionary.</span>
						<br><br>
						<?php
					}
					if(count($arrLanguageCodes) > 0)
					{
					?>
					<i><?php _e('Vernacular Browse view:'); ?></i><br>
					<?php $i = array_search(get_option('languagecode'), array_column($arrLanguageCodes, 'language_code')); ?>
					<input type="hidden" name="languagecode" value="<?php echo get_option('languagecode'); ?>">
					<strong>[<?php  echo get_option('languagecode'); ?>]</strong> <?php _e('Language Name:'); ?> <input id=vernacularName type="text" name="txtVernacularName" value="<?php if(count($arrLanguageCodes) > 0) { echo $arrLanguageCodes[$i]['name']; } ?>">
				</p>
				<p>
					<?php _e('Vernacular Alphabet'); ?> (<a href="https://www.webonary.org/help/alphabet/" target="_blank"><?php _e('configure in FLEx'); ?></a>):
					<?php
					if(is_super_admin())
					{
						?>
						<span style="color:red;">Only remove letters, do not change/add letters!</span><br>
						<input type="text" name="vernacular_alphabet" size=50 value="<?php echo stripslashes(get_option('vernacular_alphabet')); ?>">
						<?php
					}
					else
					{
						echo stripslashes(get_option('vernacular_alphabet'));
					}?>
				</p>
				<p>

					Font to use for the vernacular letters in browse view:
					<select name=vernacularLettersFont>
						<option value=""></option>
						<?php
						$arrUniqueCSSFonts = $fontClass->get_fonts_fromCssText($css_string);
						if(isset($arrUniqueCSSFonts))
						{
							foreach($arrUniqueCSSFonts as $font)
							{
								?>
								<option value="<?php echo $font;?>" <?php if($font == $vernacular_font) { echo "selected"; } ?>><?php echo $font;?></option>
								<?php
							}
						}
						?>
					</select>
				</p>
				<p>
					<input name="vernacularRightToLeft" type="checkbox" value="1" <?php checked('1', get_option('vernacularRightToLeft')); ?> /><?php _e('Display right-to-left') ?>
				</p>
			<?php
			$IncludeCharactersWithDiacritics = get_option('IncludeCharactersWithDiacritics');
			if($IncludeCharactersWithDiacritics == 1) {
				$IncludeCharactersWithDiacritics = 1;
			}
			?>
				<p>
					<input name="IncludeCharactersWithDiacritics" type="checkbox" value="1" <?php checked('1', $IncludeCharactersWithDiacritics); ?> />
					<?php _e('Include characters with diacritics (e.g. words starting with ä, à, etc. will all display under a)')?>
				</p>
				<p>
					<b><?php _e('Reversal Indexes:'); ?></b>
				</p>

			<?php
			$displayXHTML = true;
			$reversalEntries = getReversalEntries("", 0, "", $displayXHTML, "");

			if(count($reversalEntries) == 0)
			{
				echo 'No reversal indexes imported.';
			}
			else
			{
				$k = array_search(get_option('reversal1_langcode'), array_column($arrLanguageCodes, 'language_code'));

				if($k >= 0)
				{
					?>
					<p>
						<i><?php _e('1. Reversal index'); ?></i><br>
						Shortcode: [reversalindex1]
						<br><br>
						<input type="hidden" name="reversal1_langcode" value="<?php echo get_option('reversal1_langcode'); ?>">
						<strong>[<?php echo get_option('reversal1_langcode'); ?>]</strong>
						<?php _e('Language Name:'); ?> <input id=reversalName type="text" name="txtReversalName" value="<?php if(count($arrLanguageCodes) > 0) { echo $arrLanguageCodes[$k]['name']; } ?>">
						<br><br>
						<?php
						if(strlen(trim(stripslashes(get_option('reversal1_alphabet')))) == 0)
						{
							$reversal1alphabet = "";
							$alphas = range('a', 'z');
							$i = 1;
							foreach($alphas as $letter)
							{
								$reversal1alphabet .= $letter;
								if($i != count($alphas))
								{
									$reversal1alphabet .= ",";
								}
								$i++;
							}
						}
						else
						{
							$reversal1alphabet = stripslashes(get_option('reversal1_alphabet'));
						}
						?>
						<?php _e('Alphabet:'); ?> (<a href="https://www.webonary.org/help/alphabet/" target="_blank"><?php _e('configure in FLEx'); ?></a>):
						<?php
						if(is_super_admin())
						{
							?>
							<span style="color:red;">Only remove letters, do not change/add letters!</span><br>
							<input type="text" size=50 name="reversal1_alphabet" value="<?php echo $reversal1alphabet; ?>">
							<?php
						}
						else
						{
							echo $reversal1alphabet;
						}
						?>
						<input name="reversal1RightToLeft" type="checkbox" value="1" <?php checked('1', get_option('reversal1RightToLeft')); ?> /><?php _e('Display right-to-left') ?>
					</p>
					<?php
				}

				if(strlen(get_option('reversal2_langcode')) > 0)
				{
					?>
					<hr>
					<i><?php _e('2. Reversal index'); ?></i><br>
					Shortcode: [reversalindex2]
					<p>
						<input type="hidden" name="reversal2_langcode" value="<?php echo get_option('reversal2_langcode'); ?>">
						<?php $k = array_search(get_option('reversal2_langcode'), array_column($arrLanguageCodes, 'language_code')); ?>
						<strong>[<?php echo get_option('reversal2_langcode'); ?>]</strong> <?php _e('Language Name:'); ?> <input id=reversal2Name type="text" name="txtReversal2Name" value="<?php if(count($arrLanguageCodes) > 0) { echo $arrLanguageCodes[$k]['name']; } ?>">
					</p>
					<p>
						<?php _e('Alphabet:'); ?> (<a href="https://www.webonary.org/help/alphabet/"
													  target="_blank"><?php _e('configure in FLEx'); ?></a>):
						<?php
						if (is_super_admin()) {
							?>
							<span style="color:red;">Only remove letters, do not change/add letters!</span><br>
							<input type="text" size=50 name="reversal2_alphabet"
								   value="<?php echo stripslashes(get_option('reversal2_alphabet')); ?>">
							<?php
						} else {
							echo stripslashes(get_option('reversal2_alphabet'));
						}
						?>
						<input name="reversal2RightToLeft" type="checkbox"
							   value="1" <?php checked('1', get_option('reversal2RightToLeft')); ?> /><?php _e('Display right-to-left') ?>
					</p>
					<?php
				}

				if(strlen(get_option('reversal3_langcode')) > 0)
				{
					?>
					<hr>
					<i><?php _e('3. Reversal index'); ?></i><br>
					Shortcode: [reversalindex3]
					<p>
						<input type="hidden" name="reversal3_langcode" value="<?php echo get_option('reversal3_langcode'); ?>">
						<?php $k = array_search(get_option('reversal3_langcode'), array_column($arrLanguageCodes, 'language_code')); ?>
						<strong>[<?php echo get_option('reversal3_langcode'); ?>]</strong> <?php _e('Language Name:'); ?> <input id=reversal3Name type="text" name="txtReversal3Name" value="<?php if(count($arrLanguageCodes) > 0) { echo $arrLanguageCodes[$k]['name']; } ?>">
					</p>
					<p>
						<?php _e('Alphabet:'); ?> (<a href="https://www.webonary.org/help/alphabet/" target="_blank"><?php _e('configure in FLEx'); ?></a>):
						<?php
						if(is_super_admin())
						{
							?>
							<span style="color:red;">Only remove letters, do not change/add letters!</span><br>
							<input type="text" size=50 name="reversal3_alphabet" value="<?php echo stripslashes(get_option('reversal3_alphabet')); ?>">
							<?php
						}
						else
						{
							echo stripslashes(get_option('reversal3_alphabet'));
						}
						?>
						<input name="reversal3RightToLeft" type="checkbox" value="1" <?php checked('1', get_option('reversal3RightToLeft')); ?> /><?php _e('Display right-to-left') ?>
					</p>
					<?php
				}
			}

			if(is_super_admin())
			{
				$displayCustomDomains = get_option('displayCustomDomains');
				/*
				if($displayCustomDomains != 'no' && !isset($displayCustomDomains))
				{
					$displayCustomDomains = 1;
				}
				*/
				?>
				<h3>Semantic Domains</h3>
				<select name="displayCustomDomains">
					<option value="default" <?php selected($displayCustomDomains, 'default'); ?>>Default View</option>
					<option value="yakan" <?php selected($displayCustomDomains, 'yakan'); ?>>Yakan (Philippines)</option>
					<option value="spanishfoods" <?php selected($displayCustomDomains, 'spanishfoods'); ?>>Spanish Foods</option>
				</select>
				<?php
			}
			/*
			?>
			<h3><?php _e('Comments');?></h3>
			If you have the comments turned on, you need to re-sync your comments after re-importing of your posts.
			<p>
			<a href="admin.php?import=comments-resync">Re-sync comments</a>
			<?php
			*/
			}

			Webonary_Configuration::admin_section_end('browse', 'Save Changes');

			Webonary_Configuration::admin_section_start('fonts');
			?>

				<h3>Fonts</h3>
				<p>
					See <a href="https://www.webonary.org/help/setting-up-a-font/" target="_blank">Setting up a Font</a>.
				</p>
				<hr>
				<?php
				$arrFontFacesFile = array();
				$customCSSFilePath = $upload_dir['basedir'] . '/custom.css';
				if (file_exists($customCSSFilePath)) {
					$fontFacesFile = file_get_contents($customCSSFilePath);
					$arrFontFacesFile = $fontClass->get_fonts_fromCssText($fontFacesFile);
				}

				$arrFont = $fontClass->getFontsAvailable();

				if(isset($arrUniqueCSSFonts))
				{
					$fontNr = 0;
					foreach($arrUniqueCSSFonts as $userFont)
					{
						$userFont = trim($userFont);

						$fontKey = array_search($userFont, array_column($arrFont, 'name'));

						if(!strstr($userFont, 'default font'))
						{
							echo "<strong>" . $userFont . "</strong><br>";
							$fontLinked = false;
							if(count($arrFontFacesFile) > 0)
							{
								if(in_array($userFont, $arrFontFacesFile))
								{
									$fontLinked = true;
									echo 'linked in <a href="' . $upload_dir['baseurl'] . '/custom.css">custom.css</a>';
								}
							}

							if($fontLinked)
							{
								if($fontKey !== false)
								{
									if($arrFont[$fontKey]['hasSubFonts'])
									{
										echo '<span style="color:orange; font-weight: bold;">This web font is very large and will take a long time to load! Please use a <a href="https://www.webonary.org/help/setting-up-a-font/" target="_blank" style="color:orange; font-weight:bold;">font subset</a> if possible.</span>';
									}
								}
							}
							else
							{
								if(in_array($userFont, $fontClass->get_system_fonts()))
								{
									echo 'This is a system font that most computers already have installed.';
								}
								else
								{
									echo "<strong style=\"color:red;\">";
									if($fontKey > 0)
									{
										echo 'Font not linked. Please re-upload the css file to get it linked.';
									}
									else
									{
										echo 'Font not found in the repository. Please ask Webonary Support to add it.';
									}
									echo '</strong>';
								}
							}

							if(is_super_admin() && !in_array($userFont, $fontClass->get_system_fonts()))
							{
							?>
								<input type="hidden" name="fontname[<?php echo $fontNr; ?>]" value="<?php echo $userFont; ?>">
								<input class="button-webonary" type="submit" value="Upload" name="uploadButton[<?php echo $fontNr; ?>]">
								<?php
								$fontNr++;
							}							
							echo "<p></p>";
						}
					}
				}

				Webonary_Configuration::admin_section_end('fonts');

				Webonary_Configuration::admin_section_start('superadmin');
				?>

				<h3>Notes</h3>
				Site ID: <?php echo get_current_blog_id(); ?>
				<p>
					<span style="color:red">These notes are only visible to super admins.</span>
				</p>
				<p>
					<textarea name=txtNotes cols=50 rows=6><?php echo stripslashes(get_option('notes'));?></textarea>
				</p>
				<p>
					Hide search form: <input name="noSearchForm" type="checkbox" value="1" <?php checked('1', get_option('noSearch')); ?> />
				</p>
				<p>
					Use cloud backend: <input name="useCloudBackend" type="checkbox" value="1" <?php checked('1', get_option('useCloudBackend')); ?> />
				</p>
				<p>
					<?php Webonary_Configuration::admin_section_end('superadmin', 'Save Changes'); ?>
				</p>
			</form>
		</div>
	<?php
}
