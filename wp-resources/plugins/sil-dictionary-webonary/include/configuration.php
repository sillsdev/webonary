<?php /** @noinspection SqlResolve */
/** @noinspection HtmlFormInputWithoutLabel */
/** @noinspection HtmlUnknownTarget */

add_action('wp_ajax_getAjaxLanguage', 'Webonary_Ajax::ajaxLanguage');
add_action('wp_ajax_nopriv_getAjaxLanguage', 'Webonary_Ajax::ajaxLanguage');
add_action('wp_ajax_getAjaxCurrentIndexedCount', 'Webonary_Ajax::ajaxCurrentIndexedCount');
add_action('wp_ajax_getAjaxCurrentImportedCount', 'Webonary_Ajax::ajaxCurrentImportedCount');
add_action('wp_ajax_getAjaxRestartIndexing', 'Webonary_Ajax::ajaxRestartIndexing');
add_action('wp_ajax_getAjaxDisplaySites', 'Webonary_Ajax::ajaxDisplaySites');
add_action('wp_ajax_postAjaxDeleteData', 'Webonary_Ajax::deleteData');

function relevanceSave(): bool
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

		$found = Webonary_Db::GetBool("SELECT COUNT(*) FROM $tableCustomRelevance WHERE class = %s", $class_name);

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
function report_missing_senses(): void
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
 * @return void
 * @throws Exception
 */
function webonary_conf_dashboard(): void
{
	webonary_conf_widget(true);
}

function webonary_register_custom_css(): void
{
	$upload_dir = wp_upload_dir();
	wp_register_style(
		'custom_stylesheet',
		$upload_dir['baseurl'] . '/custom.css',
		[],
		date('U'),
		'all'
	);
	wp_enqueue_style('custom_stylesheet');
}
add_action('wp_enqueue_scripts', 'webonary_register_custom_css', 999993);

/**
 * @param bool $showTitle
 * @return void
 * @throws Exception
 */
function webonary_conf_widget(bool $showTitle = false): void
{
	Webonary_Configuration_Widget::UpdateConfiguration();
	Webonary_Configuration_Widget::DisplayConfiguration();
	return;

	$upload_dir = wp_upload_dir();

	$fontClass = new Webonary_Font_Management();
	$css_string = null;
	$configured_css_file = $upload_dir['basedir'] . '/imported-with-xhtml.css';
	if(file_exists($configured_css_file))
		$css_string = file_get_contents($configured_css_file);

	if(is_super_admin() && isset($_POST['uploadButton']))
		$fontClass->uploadFontForm();

	if(isset($_GET['changerelevance']))
		echo Webonary_Configuration::relevanceForm();

	if(isset($_POST['uploadFont']))
		$fontClass->uploadFont();

	if(isset($_POST['btnSaveRelevance']))
		relevanceSave();

	$arrLanguageCodes = array();
	$noReversalEntries = true;
	if (IS_CLOUD_BACKEND) {

		$dictionary = Webonary_Cloud::getDictionary();
		$import_status = '';
		if (!is_null($dictionary)) {
			$import_status .= '<li>Last Upload: <em>' . date('Y-m-d h:i:s', strtotime($dictionary->updatedAt)) . ' (GMT)</em>';
			$import_status .= '<li>Main Language (' . $dictionary->mainLanguage->lang . ')';
			$import_status .= ' entries: <em>' . number_format($dictionary->mainLanguage->entriesCount) . '</em>';
			$arrLanguageCodes[] = array(
				'language_code' => $dictionary->mainLanguage->lang,
				'name' => $dictionary->mainLanguage->title ?? $dictionary->mainLanguage->lang);
			foreach ($dictionary->reversalLanguages as $reversal) {
				$import_status .= '<li>Reversal Language (' . $reversal->lang . ')';
				if (isset($reversal->entriesCount) && $reversal->entriesCount) {
					$import_status .= ' entries: <em>'. number_format($reversal->entriesCount) . '</em>';
					$arrLanguageCodes[] = array(
						'language_code' => $reversal->lang,
						'name' => $reversal->title ?? $reversal->lang);
					$noReversalEntries = false;
				}
			}
			$import_status = '<ul>' . $import_status . '</ul>';
		}
	}
	else {
		$import_status = Webonary_Info::import_status();
		$arrLanguageCodes = Webonary_Configuration::get_LanguageCodes();
		$displayXHTML = true;
		$reversalEntries = getReversalEntries("", 0, "", $displayXHTML, "");
		if (count($reversalEntries))
			$noReversalEntries = false;
	}
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
			echo '<h2>' . __('Webonary', 'sil_dictionary') . '</h2>' . PHP_EOL;

		_e('Webonary provides the administration tools and framework for using WordPress for dictionaries. See <a href="https://www.webonary.org/help" target="_blank">Webonary Support</a> for help.', 'sil_dictionary');

		$admin_sections = Webonary_Configuration::get_admin_sections();
		echo '<h2 class="nav-tab-wrapper">' . PHP_EOL;
		foreach($admin_sections as $slug => $name){
			echo '<a class="nav-tab" href="#'.$slug.'" title="'.sprintf(__('Click to switch to %s', 'sil_dictionary'), $name).'">'.$name.'</a>' . PHP_EOL;
		}
		echo '</h2>' . PHP_EOL;

		// enctype="multipart/form-data"
		?>
		<script>
			function getLanguageName(select_box, lang_name)
			{
				let e = document.getElementById(select_box);
				let langcode = e.options[e.selectedIndex].value;

				jQuery.ajax({
					url: '<?php echo admin_url('admin-ajax.php'); ?>',
					data : {action: "getAjaxLanguage", languagecode : langcode},
					type:'POST',
					dataType: 'html',
					success: function(output_string){
						jQuery('#' + lang_name).val(output_string);
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
		<form id="configuration-form" method="post" action="">
		<div class="tabs-content">
			<?php
			Webonary_Configuration::admin_section_start('import');
			?>

			<div style="max-width: 600px; border-style:solid; border-width: 1px; border-color: red; padding: 5px; margin: 1rem 0">
				<h3 style="margin: 0.5rem 0 1rem 0">Upload Status:</h3>
				<?php echo $import_status ?>
			</div>

				<div style="margin: 1rem 0">
					<?php _e('Publication status:'); ?>
					<select name="publicationStatus">
						<option value=0><?php _e('no status set'); ?></option>
						<option value=1 <?php selected(get_option('publicationStatus'), 1); ?>><?php _e('Rough draft'); ?></option>
						<option value=2 <?php selected(get_option('publicationStatus'), 2); ?>><?php _e('Self-reviewed draft'); ?></option>
						<option value=3 <?php selected(get_option('publicationStatus'), 3); ?>><?php _e('Community-reviewed draft'); ?></option>
						<option value=4 <?php selected(get_option('publicationStatus'), 4); ?>><?php _e('Consultant approved'); ?></option>
						<option value=5 <?php selected(get_option('publicationStatus'), 5); ?>><?php _e('Finished (no formal publication)'); ?></option>
						<option value=6 <?php selected(get_option('publicationStatus'), 6); ?>><?php _e('Formally published'); ?></option>
					</select>
				</div>

				<div style="border-top: 1px solid #ccc;border-bottom: 1px solid #ccc;padding:12px 0;max-width:610px">
			        <div>Use cloud backend: <input name="useCloudBackend" type="checkbox" value="1" <?php checked('1', IS_CLOUD_BACKEND); ?> /></div>
			        <?php if (IS_CLOUD_BACKEND) { ?>
			        <div style="margin-top: 8px">
						<button style="margin: 0" class="button button-webonary" type="submit"
								name="refresh_cloud_settings" value="refresh"><?php _e('Refresh Settings From Cloud Data', 'sil_dictionary'); ?></button>
					</div>
			        <?php } ?>
				</div>

				<h3><?php _e('Delete Data', 'sil_dictionary'); ?></h3>
				<div style="margin: 1rem 0">
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
					<input type="hidden" id="confirm-delete-text"
						   value="<?php _e('Are you sure you want to delete the dictionary data?', 'sil_dictionary'); ?>">
					<div id="webonary-delete-msg"></div>
					<?php if (IS_CLOUD_BACKEND) { ?>
						<input type="hidden" id="pwd-required-text"
							   value="<?php _e('Your password is required.', 'sil_dictionary'); ?>">
						<div style="margin-bottom: 8px">
							<p style="margin-bottom: 3px">Enter your Webonary password to delete</p>
							<input type="password" name="pwd" id="user_pass" aria-describedby="login-message"
								   class="input password-input" value="" size="20" autocomplete="current-password">
						</div>
					<?php } ?>
					<div>
						<button style="margin: 0 0 12px 0" class="button button-webonary" type="button"
								onclick="DeleteWebonaryData();"><?php _e('Delete', 'sil_dictionary'); ?></button>
					</div>
					<?php _e('(deletes all posts in the category "webonary")', 'sil_dictionary'); ?>
				</div>

				<?php
				Webonary_Configuration::admin_section_end('import', 'Save Changes');

				Webonary_Configuration::admin_section_start('search');
				?>

				<h3><?php _e('Default Search Options');?></h3>

				<div style="margin:1rem 0">
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
				</div>
				<?php
				if(class_exists('special_characters'))
				{
					//this is here for legacy purposes. The special characters used to be Widget in a separate plugin.
					//we need to get those characters for older dictionary sites and display them in the dashboard.
					$charWidget = new special_characters();
					$settings = $charWidget->get_settings();
					$settings = reset($settings);
				}
				$special_characters = get_option('special_characters');
				if(!empty($settings['characters'])
					&& trim($special_characters) == ''
					&& !isset($_POST['characters']))
				{
					$special_characters = $settings['characters'];
				}
				$special_characters = str_replace('empty', '', $special_characters);
				?>
				<div style="margin: 1em 0">
					<strong><?php _e('Special character input buttons');?></strong><br>
					These will appear above the search field.<br>Separate the characters by comma:
					<input type="text" name="characters" id=characters style="display: block; width: 100%" value="<?php echo $special_characters; ?>">
				</div>
				<div style="margin: 1em 0">
					<input name="special_characters_rtl" type="checkbox" value="1" <?php checked('1', get_option('special_characters_rtl')); ?> /><?php _e('Display right-to-left') ?>
				</div>
				<div style="margin: 1em 0">
					<b>Font to use for the search field and character buttons:</b>
					<select name="inputFont" style="display: block">
						<option value=""></option>
						<?php
						$arrUniqueCSSFonts = Webonary_Font_Management::get_fonts_fromCssText($css_string);
						if (isset($arrUniqueCSSFonts)) {

							/** @noinspection HtmlUnknownAttribute */
							$option_html = '<option value="%1$s" %2$s>%1$s</option>' . PHP_EOL;

							foreach ($arrUniqueCSSFonts as $font) {
								$selected = ($font == $input_font) ? 'selected' : '';
								echo sprintf($option_html, $font, $selected);
							}
						}
						?>
					</select>
				</div>
				<p>
					<!--suppress HtmlUnknownAnchorTarget -->
					<a href="?page=webonary&changerelevance=true#search">Relevance Settings for Fields</a>
				</p>
				<?php
				Webonary_Configuration::admin_section_end('search', 'Save Changes');

				Webonary_Configuration::admin_section_start('browse');
				?>

				<h3>Browse Views</h3>
				<p>See <a href="https://www.webonary.org/help/creating-browse-views/" target="_blank">Help with creating Browse Views</a></p>

				<div style="margin:1rem 0">
					<?php
					$DisplaySubentriesAsMainEntries = get_option('DisplaySubentriesAsMainEntries');
					if ($DisplaySubentriesAsMainEntries == 1)
					{
						if (IS_CLOUD_BACKEND) {
							update_option('DisplaySubentriesAsMainEntries', 0);
						}
						else {
					?>
					<input name="DisplaySubentriesAsMainEntries" type="checkbox" value="1"
						<?php checked('1', $DisplaySubentriesAsMainEntries); ?> />
					<?php _e('Display subentries as main entries'); ?>
				</div>
				<div style="margin:1rem 0">
					<?php
					    }
					}
					if(count($arrLanguageCodes) == 0)
					{
						?>
						<span style="color:red">You need to first upload your dictionary.</span>
						<br><br>
						<?php
					}
					if(count($arrLanguageCodes) > 0)
					{
					?>
					<i><?php _e('Vernacular Browse view:'); ?></i><br>
					<?php $i = array_search(get_option('languagecode'), array_column($arrLanguageCodes, 'language_code')); ?>
					<input type="hidden" name="languagecode" value="<?php echo get_option('languagecode'); ?>">
					<strong>[<?php  echo get_option('languagecode'); ?>]</strong> <?php _e('Language Name:'); ?> <input id=vernacularName type="text" name="txtVernacularName" value="<?php if(count($arrLanguageCodes) > 0) { echo $arrLanguageCodes[$i]['name']; } ?>" <?php if (IS_CLOUD_BACKEND) echo 'readonly'; ?>>
				</div>
				<div style="margin:1rem 0">
					<?php _e('Vernacular Alphabet'); ?> (<a href="https://www.webonary.org/help/alphabet/" target="_blank"><?php _e('configure in FLEx'); ?></a>):
					<?php
					if(is_super_admin())
					{
						?>
						<span style="color:red;">Only remove letters, do not change/add letters!</span><br>
						<input type="text" name="vernacular_alphabet" class="admin-alphabet" size=50 value="<?php echo stripslashes(Webonary_Cloud::filterLetterList(get_option('vernacular_alphabet'), true)); ?>">
						<?php
					}
					else
					{
						echo stripslashes(Webonary_Cloud::filterLetterList(get_option('vernacular_alphabet'), true));
					}?>
				</div>
				<div style="margin:1rem 0">
					Font to use for the vernacular letters in browse view:
					<select name=vernacularLettersFont>
						<option value=""></option>
						<?php
						$arrUniqueCSSFonts = Webonary_Font_Management::get_fonts_fromCssText($css_string);
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
				</div>
				<div style="margin:1rem 0">
					<input name="vernacularRightToLeft" type="checkbox" value="1" <?php checked('1', get_option('vernacularRightToLeft')); ?> /><?php _e('Display right-to-left') ?>
				</div>
			<?php
			$IncludeCharactersWithDiacritics = get_option('IncludeCharactersWithDiacritics');
			if($IncludeCharactersWithDiacritics == 1) {
				$IncludeCharactersWithDiacritics = 1;
			}
			?>
				<div style="margin:1rem 0">
					<input name="IncludeCharactersWithDiacritics" type="checkbox" value="1" <?php checked('1', $IncludeCharactersWithDiacritics); ?> />
					<?php _e('Include characters with diacritics (e.g. words starting with ä, à, etc. will all display under a)')?>
				</div>
				<div style="margin:1rem 0">
					<b><?php _e('Reversal Indexes:'); ?></b>
				</div>

			<?php
			if($noReversalEntries)
			{
				echo 'No reversal indexes uploaded.';
			}
			else
			{
				$k = array_search(get_option('reversal1_langcode'), array_column($arrLanguageCodes, 'language_code'));

				if($k >= 0)
				{
					?>
					<div style="margin:1rem 0">
						<i><?php _e('1. Reversal index'); ?></i><br>
						Shortcode: [reversalindex1]
						<br><br>
						<input type="hidden" name="reversal1_langcode" value="<?php echo get_option('reversal1_langcode'); ?>">
						<strong>[<?php echo get_option('reversal1_langcode'); ?>]</strong>
						<?php _e('Language Name:'); ?> <input id=reversalName type="text" name="txtReversalName" value="<?php if(count($arrLanguageCodes) > 0) { echo $arrLanguageCodes[$k]['name']; } ?>" <?php if (IS_CLOUD_BACKEND) echo 'readonly'; ?>>
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
							<input type="text" size=50 name="reversal1_alphabet" class="admin-alphabet" value="<?php echo $reversal1alphabet; ?>">
							<?php
						}
						else
						{
							echo $reversal1alphabet;
						}
						?>
						<input name="reversal1RightToLeft" type="checkbox" value="1" <?php checked('1', get_option('reversal1RightToLeft')); ?> /><?php _e('Display right-to-left') ?>
					</div>
					<?php
				}

				if(strlen(get_option('reversal2_langcode')) > 0)
				{
					?>
					<hr>
					<i><?php _e('2. Reversal index'); ?></i><br>
					Shortcode: [reversalindex2]
					<div style="margin:1rem 0">
						<input type="hidden" name="reversal2_langcode" value="<?php echo get_option('reversal2_langcode'); ?>">
						<?php $k = array_search(get_option('reversal2_langcode'), array_column($arrLanguageCodes, 'language_code')); ?>
						<strong>[<?php echo get_option('reversal2_langcode'); ?>]</strong> <?php _e('Language Name:'); ?> <input id=reversal2Name type="text" name="txtReversal2Name" value="<?php if(count($arrLanguageCodes) > 0) { echo $arrLanguageCodes[$k]['name']; } ?>" <?php if (IS_CLOUD_BACKEND) echo 'readonly'; ?>>
					</div>
					<div style="margin:1rem 0">
						<?php _e('Alphabet:'); ?> (<a href="https://www.webonary.org/help/alphabet/"
													  target="_blank"><?php _e('configure in FLEx'); ?></a>):
						<?php
						if (is_super_admin()) {
							?>
							<span style="color:red;">Only remove letters, do not change/add letters!</span><br>
							<input type="text" size=50 name="reversal2_alphabet" class="admin-alphabet"
								   value="<?php echo stripslashes(get_option('reversal2_alphabet')); ?>">
							<?php
						} else {
							echo stripslashes(get_option('reversal2_alphabet'));
						}
						?>
						<input name="reversal2RightToLeft" type="checkbox"
							   value="1" <?php checked('1', get_option('reversal2RightToLeft')); ?> /><?php _e('Display right-to-left') ?>
					</div>
					<?php
				}

				if(strlen(get_option('reversal3_langcode')) > 0)
				{
					?>
					<hr>
					<i><?php _e('3. Reversal index'); ?></i><br>
					Shortcode: [reversalindex3]
					<div style="margin:1rem 0">
						<input type="hidden" name="reversal3_langcode" value="<?php echo get_option('reversal3_langcode'); ?>">
						<?php $k = array_search(get_option('reversal3_langcode'), array_column($arrLanguageCodes, 'language_code')); ?>
						<strong>[<?php echo get_option('reversal3_langcode'); ?>]</strong> <?php _e('Language Name:'); ?> <input id=reversal3Name type="text" name="txtReversal3Name" value="<?php if(count($arrLanguageCodes) > 0) { echo $arrLanguageCodes[$k]['name']; } ?>" <?php if (IS_CLOUD_BACKEND) echo 'readonly'; ?>>
					</div>
					<div style="margin:1rem 0">
						<?php _e('Alphabet:'); ?> (<a href="https://www.webonary.org/help/alphabet/" target="_blank"><?php _e('configure in FLEx'); ?></a>):
						<?php
						if(is_super_admin())
						{
							?>
							<span style="color:red;">Only remove letters, do not change/add letters!</span><br>
							<input type="text" size=50 name="reversal3_alphabet" class="admin-alphabet" value="<?php echo stripslashes(get_option('reversal3_alphabet')); ?>">
							<?php
						}
						else
						{
							echo stripslashes(get_option('reversal3_alphabet'));
						}
						?>
						<input name="reversal3RightToLeft" type="checkbox" value="1" <?php checked('1', get_option('reversal3RightToLeft')); ?> /><?php _e('Display right-to-left') ?>
					</div>
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
					$arrFontFacesFile = Webonary_Font_Management::get_fonts_fromCssText($fontFacesFile);
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
							echo '<div style="margin:1rem 0">' . PHP_EOL;
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
								if(in_array($userFont, Webonary_Font_Management::get_system_fonts()))
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

							if(is_super_admin() && !in_array($userFont, Webonary_Font_Management::get_system_fonts()))
							{
							?>
								<input type="hidden" name="fontname[<?php echo $fontNr; ?>]" value="<?php echo $userFont; ?>">
								<input class="button-webonary" type="submit" value="Upload" name="uploadButton[<?php echo $fontNr; ?>]">
								<?php
								$fontNr++;
							}
							echo '</div>' . PHP_EOL;
						}
					}
				}

				Webonary_Configuration::admin_section_end('fonts');

				Webonary_Configuration::admin_section_start('superadmin');
				?>

				<h3>Notes</h3>
				<p>Site ID: <?php echo get_current_blog_id(); ?></p>
				<p>
					<span style="color:red">These notes are only visible to super admins.</span>
				</p>
				<div style="margin:1rem 0">
					<textarea name=txtNotes cols=50 rows=6><?php echo stripslashes(get_option('notes'));?></textarea>
				</div>
				<div style="margin:1rem 0">
					Hide search form: <input name="noSearchForm" type="checkbox" value="1" <?php checked('1', get_option('noSearch')); ?> />
				</div>

			    <?php Webonary_Configuration::admin_section_end('superadmin', 'Save Changes'); ?>

		</div>
		</form>
	</div>
	<?php
}
