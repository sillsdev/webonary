<?php

class Webonary_Configuration_Widget
{
	public static function DisplayConfiguration(): void
	{
		// javascript and styles
		self::RegisterScriptsAndStyles();

		// opening tags
		$lines = [
			'<div class="wrap">',
			'<h2>' . __('Webonary', 'sil_dictionary') . '</h2>',
			__('Webonary provides the administration tools and framework for using WordPress for dictionaries. See <a href="https://www.webonary.org/help" target="_blank">Webonary Support</a> for help.', 'sil_dictionary')
		];

		// get tabs
		self::BuildTabs($lines);

		// not sure what this one is for
		$lines[] = '<div id="icon-tools" class="icon32"></div>';

		// the form
		self::BuildForm($lines);

		// closing tags
		$lines[] = '</div>';

		echo implode(PHP_EOL, $lines);
	}

	/**
	 * @return string
	 * @throws Exception
	 */
	public static function UpdateConfiguration(): string
	{
		if (!empty($_POST['delete_data'])) {
			Webonary_Delete_Data::DeleteDictionaryData();
			return '';
		}

		if (!empty($_POST['refresh_cloud_settings'])) {

			$dictionaryId = Webonary_Cloud::getBlogDictionaryId();
			Webonary_Cloud::resetDictionary($dictionaryId);
			return '';
		}

		if (!empty($_POST['save_settings']))
			return self::SaveSettings();

		return '';
	}

	private static function SaveSettings(): string
	{
		$return_val = [];

		update_option('publicationStatus', $_POST['publicationStatus']);
		update_option('searchSomposedCharacters', $_POST['search_composed_characters'] ?? 'no');
		//update_option('distinguish_diacritics', $_POST['distinguish_diacritics']);

		if (isset($_POST['normalization'])) {
			update_option('normalization', $_POST['normalization']);
		}

		$special_characters = trim($_POST['characters']);
		if (empty($special_characters))
			$special_characters = 'empty';

		update_option('special_characters', $special_characters);
		update_option('special_characters_rtl', $_POST['special_characters_rtl'] ?? 'no');
		update_option('inputFont', $_POST['inputFont']);
		update_option('vernacularLettersFont', $_POST['vernacularLettersFont']);

		//We no longer give the option to set this (only to unset it) as this can be done in FLEx
		update_option('DisplaySubentriesAsMainEntries', $_POST['DisplaySubentriesAsMainEntries'] ?? 'no');

		update_option('languagecode', $_POST['languagecode']);

		//We no longer give the option to set this (only to unset it) as the letter headers/sorting should be done in FLEx
		update_option('IncludeCharactersWithDiacritics', $_POST['IncludeCharactersWithDiacritics'] ?? 'no');

		update_option('displayCustomDomains', $_POST['displayCustomDomains']);
		update_option('vernacularRightToLeft', $_POST['vernacularRightToLeft'] ?? 'no');
		update_option('reversal1_langcode', $_POST['reversal1_langcode'] ?? '');
		update_option('reversal2_langcode', $_POST['reversal2_langcode'] ?? '');
		update_option('reversal3_langcode', $_POST['reversal3_langcode'] ?? '');
		update_option('reversal1RightToLeft', $_POST['reversal1RightToLeft'] ?? 'no');
		update_option('reversal2RightToLeft', $_POST['reversal2RightToLeft'] ?? 'no');
		update_option('reversal3RightToLeft', $_POST['reversal3RightToLeft'] ?? 'no');

		if (trim(strlen($_POST['txtVernacularName'])) == 0)
			$return_val[] = '<br><span style="color:red">Please fill out the text fields for the language names, as they will appear in a dropdown below the search box.</span><br>';

		if (isset($_POST['txtNotes']))
			update_option("notes", $_POST['txtNotes']);

		$noSearchForm = 0;
		if (isset($_POST['noSearchForm'])) {
			$noSearchForm = $_POST['noSearchForm'];
			if (is_plugin_active('wp-super-cache/wp-cache.php') && $noSearchForm == 1)
				prune_super_cache(get_supercache_dir(), true);
		}

		update_option('noSearch', $noSearchForm);
		update_option('countryName', $_POST['countryName']);
		update_option('languageFamily', $_POST['languageFamily']);

		$useCloudBackend = filter_input(
			INPUT_POST,
			'useCloudBackend',
			FILTER_SANITIZE_NUMBER_INT,
			array('options' => array('default' => '')));

		if ($useCloudBackend !== '1')
			$useCloudBackend = '';

		if ($useCloudBackend != get_option('useCloudBackend', '')) {
			if (is_plugin_active('wp-super-cache/wp-cache.php')) {
				prune_super_cache(get_supercache_dir(), true);
			}

			// Store this both as a blog option and metadata for convenience
			update_option('useCloudBackend', $useCloudBackend);
			update_site_meta(get_current_blog_id(), 'useCloudBackend', '1');

			// initial set up of dictionary using cloud values
			if ($useCloudBackend) {
				$dictionaryId = Webonary_Cloud::getBlogDictionaryId();
				Webonary_Cloud::resetDictionary($dictionaryId);
			}
		}

		self::UpdateLanguageCodesAndNames();

		// configured fonts
		$mapped = array_filter(
			$_POST,
			function ($key) {
				return str_starts_with($key, Webonary_Font_Management::$font_option_name_prefix);
			},
			ARRAY_FILTER_USE_KEY
		);

		$to_save = [];
		foreach ($mapped as $key => $val) {

			if ($val == '') {
				delete_option($key);
			}
			else {
				update_option($key, $val ?? '', false);
				$to_save[Webonary_Font_Management::GetFontNameFromOptionName($key)] = $val;
			}
		}

		$upload_dir = wp_upload_dir();
		Webonary_Font_Management::SaveSelectedFonts($to_save, $upload_dir['path']);

		if (is_super_admin()) {

			$letters = trim($_POST['vernacular_alphabet'] ?? '');

			// remove empty items from the list
			$letters = Webonary_Cloud::filterLetterList($letters, true);

			if (strlen($letters) > 0)
				update_option('vernacular_alphabet', $letters);

			update_option('reversal1_alphabet', $_POST['reversal1_alphabet'] ?? '');
			update_option('reversal2_alphabet', $_POST['reversal2_alphabet'] ?? '');
			update_option('reversal3_alphabet', $_POST['reversal3_alphabet'] ?? '');
		}

		$return_val[] = "<br>" . _e('Settings saved');

		return implode(PHP_EOL, $return_val);
	}

	private static function UpdateLanguageCodesAndNames(): void
	{
		global $wpdb;

		$languages['txtVernacularName'] = 'languagecode';
		$languages['txtReversalName'] = 'reversal1_langcode';
		$languages['txtReversal2Name'] = 'reversal2_langcode';
		$languages['txtReversal3Name'] = 'reversal3_langcode';

		foreach ($languages as $key => $value) {

			$lang_code = trim($_POST[$value] ?? '');
			if (empty($lang_code))
				continue;

			$lang_name = trim($_POST[$key] ?? '');

			// is this an existing record?
			$sql = $wpdb->prepare("SELECT term_id FROM $wpdb->terms WHERE slug = %s", $lang_code);
			$found_term_id = $wpdb->get_var($sql);

			// update the terms table
			if (!empty($found_term_id)) {
				$sql = $wpdb->prepare("UPDATE $wpdb->terms SET `name` = %s WHERE slug = %s", $lang_name, $lang_code);
				$wpdb->query($sql);
				$term_id = $found_term_id;
			} else {
				$sql = $wpdb->prepare("INSERT INTO $wpdb->terms (`name`, slug) VALUES (%s, %s)", $lang_name, $lang_code);
				$wpdb->query($sql);
				$term_id = $wpdb->insert_id;
			}

			// update the terms_taxonomy table
			if (!empty($found_term_id))
				$sql = $wpdb->prepare("UPDATE $wpdb->term_taxonomy SET description = %s WHERE term_id = %s", $lang_name, $term_id);
			else
				$sql = $wpdb->prepare("INSERT INTO $wpdb->term_taxonomy (term_id, taxonomy, description, count) VALUES (%s, 'sil_writing_systems', %s, 999999)", $term_id, $lang_name);

			$wpdb->query($sql);
		}
	}

	private static function BuildTabs(&$lines): void
	{
		$sections = Webonary_Configuration::get_admin_sections();
		$lines[] = '<h2 class="nav-tab-wrapper">';
		$title = __('Click to switch to %s', 'sil_dictionary');
		/** @noinspection HtmlUnknownAnchorTarget */
		$template = <<<HTML
<a class="nav-tab" href="#%s" title="$title">%s</a>
HTML;

		foreach ($sections as $slug => $name) {
			$lines[] = sprintf($template, $slug, $name, $name);
		}

		$lines[] = '</h2>';
	}

	private static function RegisterScriptsAndStyles(): void
	{
		// add scripts
		wp_register_script(
			'webonary_options_script',
			get_bloginfo('wpurl') . '/wp-content/plugins/sil-dictionary-webonary/js/options.js',
			[],
			false,
			true);
		wp_enqueue_script('webonary_options_script');

		self::GetLanguageNameFunction();

		// add styles
		wp_register_style('webonary_options_style', false);
		wp_enqueue_style('webonary_options_style');

		self::GetFontNameCss();
	}

	private static function GetLanguageNameFunction(): void
	{
		$admin_url = admin_url('admin-ajax.php');
		/** @noinspection JSUnusedLocalSymbols */
		$js = <<<JS
function getLanguageName(select_box, lang_name) {
	let e = document.getElementById(select_box);
	let langcode = e.options[e.selectedIndex].value;

	jQuery.ajax({
		url: '$admin_url',
		data : {action: "getAjaxLanguage", languagecode : langcode},
		type:'POST',
		dataType: 'html',
		success: function(output_string){
			jQuery('#' + lang_name).val(output_string);
		}
	})
}
JS;
		wp_add_inline_script('webonary_options_script', $js);
	}

	private static function GetFontNameCss(): void
	{
		// inline style for configured fonts
		$style = '#dashboard-widgets a {text-decoration: underline}' . PHP_EOL;

		$input_font = get_option('inputFont', '');
		if (!empty($input_font))
			$style .= '#characters {font-family: "' . $input_font . '"}' . PHP_EOL;

		$vernacular_font = get_option('vernacularLettersFont', '');
		if (!empty($vernacular_font))
			$style .= '#vernacularAlphabet {font-family: "' . $vernacular_font . '"}' . PHP_EOL;

		wp_add_inline_style('webonary_options_style', $style);
	}

	private static function BuildForm(&$lines): void
	{
		$lines[] = '<form id="configuration-form" method="post" action="">';
		$lines[] = '<div class="tabs-content">';

		self::DataTab($lines);
		self::SearchTab($lines);
		self::BrowseTab($lines);
		self::FontsTab($lines);
		self::SuperAdminTab($lines);

		$lines[] = '</div>';
		$lines[] = '</form>';
	}

	private static function DataTab(&$lines): void
	{
		$import_status = Webonary_Dashboard_Widget::GetImportStatus();
		$pub_select = self::GetPublicationStatusSelect();
		$use_cloud_checked = checked('1', IS_CLOUD_BACKEND, false);
		$refresh_cloud_button = self::GetRefreshCloudButton();
		$delete_block = self::GetDeleteDataBlock();


		$html = <<<HTML
<div id="tab-import" class="hidden">
    <div class="webonary-admin-block">
    	<div style="width: 100%; border: 1px solid red; padding: 0 0.5rem; margin-bottom: 1rem">
			<h3 style="margin: 0.5rem 0 1rem 0">Upload Status:</h3>
			$import_status
		</div>
		<div  class="flex-start-center" style="margin-top: 1rem">
			<label for="publicationStatus">Publication status:</label>
			$pub_select
		</div>
	</div>
	<div class="webonary-admin-block">
		<div class="flex-start-center">
		    <label for="useCloudBackend">Use cloud backend:</label>
			<input name="useCloudBackend" id="useCloudBackend" type="checkbox" value="1" $use_cloud_checked>
		</div>
		$refresh_cloud_button
	</div>
	<div class="webonary-admin-block">
		$delete_block
		<div style="margin: 2rem 0">
			<input type="submit" name="save_settings" class="button-primary" value="Save Changes">
		</div>
	</div>
</div>
HTML;
		$lines[] = $html;
	}

	private static function SearchTab(&$lines): void
	{
		$composed = self::GetSearchComposedBlock();
		$normalization = self::GetNormalizationBlock();
		$special = self::GetSpecialCharactersBlock();

		$html = <<<HTML
<div id="tab-search" class="hidden">
    <h3>Default Search Options</h3>
    $composed
    $normalization
    $special
</div>
HTML;
		$lines[] = $html;
	}

	private static function BrowseTab(&$lines): void
	{
		if (IS_CLOUD_BACKEND)
			$lang_codes = Webonary_Cloud::getLanguageCodes();
		else
			$lang_codes = Webonary_Configuration::get_LanguageCodes();

		$sub_entries_block = self::GetSubEntriesBlock();
		$vernacular_block = self::GetVernacularBrowseBlock($lang_codes);
		$reversals_block = self::GetReversalIndexesBlock($lang_codes);


		$html = <<<HTML
<div id="tab-browse" class="hidden">
    <h3>Browse Views</h3>
    <div class="webonary-admin-block">
		<p style="margin-top: 0">See <a href="https://www.webonary.org/help/creating-browse-views/" target="_blank">Help with creating Browse Views</a></p>
    	$sub_entries_block
    </div>
    $vernacular_block
    $reversals_block
    <div class="webonary-admin-block">
		<div style="margin: 2rem 0">
			<input type="submit" name="save_settings" class="button-primary" value="Save Changes">
		</div>
	</div>
</div>
HTML;
		$lines[] = $html;
	}

	private static function FontsTab(&$lines): void
	{
		$fonts_available = Webonary_Font_Management::getFontsAvailableNames();
		$fonts_system = Webonary_Font_Management::get_system_fonts();
		$fonts_default = ['monospace', 'sans-serif', 'serif'];
		$fonts_configured = Webonary_Font_Management::GetConfiguredFonts();

		$blocks = [];

		foreach ($fonts_configured as $font) {
			$blocks[] = self::BuildFontBlock($font, $fonts_available, $fonts_system, $fonts_default);
		}

		$blocks_combined = implode(PHP_EOL, $blocks);

		// load available font faces
		$available = Webonary_Font_Management::getFontsAvailable();
		$styles = ['R', 'B', 'I', 'BI'];
		$template = '@font-face { font-family: %s; src: url(/wp-content/uploads/fonts/%s); %s %s }';
		$loaded_fonts = '';
		foreach ($available as $font) {
			// add all available font styles to the css
			foreach ($styles as $style) {

				$file_name = $font['filename'] . '-' . $style . '.' . $font['type'];

				if (file_exists(ABSPATH . FONTFOLDER . $file_name)) {

					$bold = str_contains($style, 'B') ? 'font-weight: bold;' : '';
					$italic = str_contains($style, 'I') ? 'font-style: italic;' : '';
					$css = sprintf($template, $font['name'], $file_name, $bold, $italic);

					// remove extra spaces before saving
					$loaded_fonts .= preg_replace('/\s\s+/', ' ', $css) . PHP_EOL;
				}
			}
		}

		$html = <<<HTML
<style>
$loaded_fonts
</style>
<div id="tab-fonts" class="hidden">
    <h3>Fonts</h3>
    <div class="webonary-admin-block">
		<p style="margin-top: 0">See <a href="https://www.webonary.org/help/setting-up-a-font/" target="_blank">Setting up a Font</a>.</p>
    </div>
    <div class="webonary-admin-block">
		<table>
		    $blocks_combined
		</table>
		<div class="flex-column" style="margin-top: 1rem">
			<p style="margin: 0.5rem 0 0"><strong style="color: #990000">Available Fonts</strong> are web fonts that are freely available or licensed for use by Webonary. These fonts can be seen by all users.</p>
			<p style="margin: 0.5rem 0 0"><strong style="color: #990000">System Fonts</strong> are fonts that may be found on computers and mobile devices. Windows, Macintosh, Linux, Android and iOS all have different system fonts available. If the user's system does not have this font, it will use another font to display the characters.</p>
			<p style="margin: 0.5rem 0 0"><strong style="color: #990000">Default Fonts</strong> are defined by the browser. There is no way of knowing which font the user will see, but you do know some things about the font:</p>
			<ul style="margin: 0.5rem 0 0; list-style: disc; padding-left: 2rem">
				<li><span style="font-family: serif">Serif fonts will look like this, with marks at the top and bottom of vertical lines.</span></li>
				<li><span style="font-family: sans-serif">Sans-serif will have few, if any, marks at the top and bottom of vertical lines, like this.</span></li>
				<li><span style="font-family: monospace">Monospace fonts have fixed-width characters. This is helpful if you are lining up text vertically.</span></li>
			</ul>
			<p style="margin: 0.5rem 0 0"><strong style="color: #990000">If you need a font that is not on the list, please ask Webonary Support to add it.</strong> You may be asked to provide licensing information for the font if it is not freely available for use on the internet.</p>
		</div>
		<div style="margin: 2rem 0">
			<input type="submit" name="save_settings" class="button-primary" value="Save Changes">
		</div>
    </div>
</div>
HTML;
		$lines[] = $html;
	}

	private static function SuperAdminTab(&$lines): void
	{
		$blog_id = get_current_blog_id();
		$notes = stripslashes(get_option('notes'));
		$checked = checked('1', get_option('noSearch'), false);
		$country = get_option('countryName', 'N/A');
		$language_family = get_option('languageFamily', 'N/A');

		$html = <<<HTML
<div id="tab-superadmin" class="hidden">
    <h3>Notes</h3>
    <div class="webonary-admin-block">
		<div class="flex-column">
			<p style="margin-top: 0">Site ID: <strong>$blog_id</strong></p>
			<p style="margin: 0 0 0.3rem"><span style="color: #990000">These notes are only visible to super admins.</span></p>
			<textarea name=txtNotes rows=6 style="width: 100%">$notes</textarea>
			<div class="flex-start-center" style="margin: 1rem 0">
				<label for="noSearchForm">Hide search form:</label>
				<input name="noSearchForm" id="noSearchForm" type="checkbox" value="1" $checked>
			</div>
			<div class="flex-start-center" style="margin: 1rem 0">
				<table>
					<tr>
						<td><label for="countryName">Country:</label></td>
						<td><input name="countryName" id="countryName" type="text" value="$country"></td>
					</tr>
					<tr>
						<td><label for="languageFamily">Language Family:</label></td>
						<td><input name="languageFamily" id="languageFamily" type="text" value="$language_family"></td>
					</tr>
				</table>
			</div>
		</div>
		<div style="margin: 2rem 0">
			<input type="submit" name="save_settings" class="button-primary" value="Save Changes">
		</div>
    </div>
</div>
HTML;
		$lines[] = $html;
	}

	private static function GetPublicationStatusSelect(): string
	{
		$list = [
			0 => __('no status set'),
			1 => __('Rough draft'),
			2 => __('Self-reviewed draft'),
			3 => __('Community-reviewed draft'),
			4 => __('Consultant approved'),
			5 => __('Finished (no formal publication)'),
			6 => __('Formally published')
		];

		$pub_status = intval(get_option('publicationStatus', 0));

		return Webonary_Utility::BuildSelectElement('publicationStatus', $list, $pub_status);
	}

	private static function FindSelectedValue(?string $value, array $array): ?string
	{
		// make sure the selected value is a key
		if (($value ?? false) !== false) {
			if (!array_key_exists($value, $array)) {
				$found = array_search($value, $array);
				if ($found !== false)
					return $found;
			}
		}

		return $value;
	}

	private static function GetConfiguredFontSelect(string $element_name, ?string $selected_value): string
	{
		$fonts = Webonary_Font_Management::GetConfiguredFonts();
		$selected_value = self::FindSelectedValue($selected_value, $fonts);
		$fonts_plus = ['' => ''] + $fonts;

		return Webonary_Utility::BuildSelectElement($element_name, $fonts_plus, $selected_value);
	}

	private static function GetAvailableFontSelect(string $element_name, ?string $selected_value): string
	{
		$fonts_available = Webonary_Font_Management::getFontsAvailableNames();
		$fonts_system = Webonary_Font_Management::get_system_fonts();
		$fonts_default = ['monospace', 'sans-serif', 'serif'];

		$return_val = [
			'<select id="' . $element_name . '" name="' . $element_name . '">',
			'<optgroup label="Not Set">',
			'<option value="">(not set)</option>',
			'</optgroup>'
		];
		if (!empty($fonts_available)) {
			$return_val[] = '<optgroup label="Available Fonts">';

			foreach ($fonts_available as $font) {
				$return_val[] = '<option value="' . $font . '" ' . selected($selected_value, $font, false) . '>' . $font . '</option>';
			}

			$return_val[] = '</optgroup>';
		}

		if (!empty($fonts_system)) {
			$return_val[] = '<optgroup label="System Fonts">';

			foreach ($fonts_system as $font) {
				$return_val[] = '<option value="' . $font . '" ' . selected($selected_value, $font, false) . '>' . $font . '</option>';
			}

			$return_val[] = '</optgroup>';
		}

		if (!empty($fonts_default)) {
			$return_val[] = '<optgroup label="Default Fonts">';

			foreach ($fonts_default as $font) {
				$return_val[] = '<option value="' . $font . '" ' . selected($selected_value, $font, false) . '>' . $font . '</option>';
			}

			$return_val[] = '</optgroup>';
		}

		$return_val[] = '</select>';

		return implode(PHP_EOL, $return_val);
	}

	private static function GetRefreshCloudButton(): string
	{
		if (!IS_CLOUD_BACKEND)
			return '';

		return <<<HTML
<div style="margin-top: 8px">
	<button style="margin: 0" class="button button-webonary" type="submit" name="refresh_cloud_settings" value="refresh">Refresh Settings From Cloud Data</button>
</div>
HTML;
	}

	private static function GetDeleteDataBlock(): string
	{
		if(!str_contains($_SERVER['HTTP_HOST'], 'localhost') && !str_contains($_SERVER['HTTP_HOST'], '.work') && is_super_admin())
			$testing_msg = '<p><strong style=color:red;>You are not in your testing environment!</strong></p>';
		else
			$testing_msg = '';

		$pwd_block = '';
		if (IS_CLOUD_BACKEND) {
			$pwd_block = <<<HTML
<input type="hidden" id="pwd-required-text" value="Your password is required.">
<div style="margin-bottom: 8px">
	<p style="margin-bottom: 3px">Enter your Webonary password to delete</p>
	<input type="password" name="pwd" id="user_pass" aria-describedby="login-message" class="input password-input" value="" size="20" autocomplete="current-password">
</div>
HTML;
		}

		return <<<HTML
<h3>Delete Data</h3>
<div style="margin: 1rem 0">
	$testing_msg
	<input type="hidden" name="delete_taxonomies" value="1">
	<input type="hidden" id="confirm-delete-text" value="Are you sure you want to delete the dictionary data?">
	<div id="webonary-delete-msg"></div>
	<div style="padding: 0 1rem; border: 1px solid #bbb">
        <p>In this section you can delete either the cloud dictionary data or Wordpress posts data.</p>
        <ul style="list-style: disc outside; margin-left: 1.5rem;">
		    <li>To delete cloud dictionary data, make sure the "Use cloud backend" box above is checked.</li>
		    <li>To delete WordPress dictionary data, make sure the "Use cloud backend" box above is unchecked.</li>
		</ul>
		<p>Then enter your password and click on Delete.</p>
		<p>Older dictionaries which were later uploaded to the cloud may have both types of data.</p>
    </div>
	$pwd_block
	<button style="margin: 0 0 12px 0; display: block" class="button button-webonary" type="button" onclick="DeleteWebonaryData();">Delete</button>
</div>
HTML;
	}

	private static function GetSearchComposedBlock(): string
	{
		if (get_option('hasComposedCharacters') != 1)
			return '';

		$checked = checked('1', get_option('searchSomposedCharacters'), false);

		return <<<HTML
<div class="webonary-admin-block">
	<div class="flex-start-center">
		<input name="search_composed_characters" type="checkbox" value="1" $checked>
		<span>Search for composed characters using base characters (<a href="https://www.webonary.org/searching-for-composed-characters-using-base-characters/" target="_blank">help</a>)</span>
	</div>
</div>
HTML;
	}

	private static function GetNormalizationBlock(): string
	{
		//this is only for legacy purposes.
		//Now the import will convert all text to NFC, so this is no longer needed for newer imports
		$normalization = get_option('normalization', '');
		if (empty($normalization))
			return '';

		$list = [
			'FORM_C' => 'FORM C',
			'FORM_D' => 'FORM D'
		];

		$select = Webonary_Utility::BuildSelectElement('normalization', $list, $normalization);

		return <<<HTML
<div class="webonary-admin-block">
    <div class="flex-column">
        <strong>Normalization:</strong>
        $select
        <div>See <a href="https://unicode.org/reports/tr15/" target="_blank">here</a> for more info on normalization of composite characters.</div>
        <div>By default Webonary uses FORM C. If your search for a word that contains a composite character doesn't return a result, try FORM D.</div>
    </div>
</div>
HTML;
	}

	private static function GetSpecialCharactersBlock(): string
	{
		$special_characters = trim(get_option('special_characters', ''));

		if (empty($special_characters) && !isset($_POST['characters']) && class_exists('special_characters')) {

			// This is here for legacy purposes. The special characters used to be Widget in a separate plugin.
			// We need to get those characters for older dictionary sites and display them in the dashboard.
			/** @noinspection PhpMultipleClassDeclarationsInspection */
			$charWidget = new special_characters();
			$settings = $charWidget->get_settings();
			$settings = reset($settings);
			$special_characters = $settings['characters'];
		}

		$special_characters = str_replace('empty', '', $special_characters);
		$rtl_checked = checked('1', get_option('special_characters_rtl'), false);

		$input_font = get_option('inputFont', '');
		$select = self::GetConfiguredFontSelect('inputFont', $input_font);

		return <<<HTML
<div class="webonary-admin-block">
	<div class="flex-column">
		<strong>Special character input buttons</strong>
		<span>These will appear above the search field.</span>
		<label for="characters">Separate the characters by comma:</label>
		<input type="text" name="characters" id=characters style="width: 100%" value="$special_characters">
		<div class="flex-start-center" style="margin-top: 1rem">
			<input name="special_characters_rtl" id="special_characters_rtl" type="checkbox" value="1" $rtl_checked>
			<label for="special_characters_rtl">Display right-to-left</label>
		</div>
		<label for="inputFont" style="font-weight: 700; margin-top: 1rem">Font to use for the search field and character buttons:</label>
		$select
		<p><a href="?page=webonary&changerelevance=true#search">Relevance Settings for Fields</a></p>
	</div>
	<div style="margin: 2rem 0">
		<input type="submit" name="save_settings" class="button-primary" value="Save Changes">
	</div>
</div>
HTML;
	}

	private static function GetSubEntriesBlock(): string
	{
		$display_subentries_as_main_entries = get_option('DisplaySubentriesAsMainEntries');
		if (empty($display_subentries_as_main_entries))
			return '';

		if (IS_CLOUD_BACKEND) {
			update_option('DisplaySubentriesAsMainEntries', 0);
			return '';
		}

		$checked = checked('1', $display_subentries_as_main_entries, false);

		return <<<HTML
<div class="flex-start-center">
    <input name="DisplaySubentriesAsMainEntries" id="DisplaySubentriesAsMainEntries" type="checkbox" value="1" $checked>
    <label for="DisplaySubentriesAsMainEntries">Display subentries as main entries</label>
</div>
HTML;
	}

	private static function GetVernacularBrowseBlock(array $lang_codes): string
	{
		if (empty($lang_codes))
			return '<span style="color:red">You need to first upload your dictionary.</span>';

		$vernacular_font = get_option('vernacularLettersFont', '');
		if (empty($vernacular_font))
			$font_family = '';
		else
			$font_family = 'style="font-family:' . $vernacular_font . '"';

		$read_only = !empty(IS_CLOUD_BACKEND) ? 'readonly' : '';
		$lang_code = get_option('languagecode');
		$i = array_search($lang_code, array_column($lang_codes, 'language_code'));
		$lang_name = $lang_codes[$i]['name'];
		$alphabet = stripslashes(Webonary_Cloud::filterLetterList(get_option('vernacular_alphabet'), true));
		$select = self::GetConfiguredFontSelect('vernacularLettersFont', $vernacular_font);
		$rtl_checked = checked('1', get_option('vernacularRightToLeft'), false);
		$diacritics_checked = checked('1', get_option('IncludeCharactersWithDiacritics'), false);

		if (is_super_admin()) {
			$red_text = '<span style="color:red;">Only remove letters, do not change/add letters!</span>';
			$alphabet_out = '<input type="text" name="vernacular_alphabet" id="vernacular_alphabet" class="admin-alphabet" value="' . $alphabet . '" ' . $font_family . '>';
		}
		else {
			$red_text = '';
			$alphabet_out = '<span id="vernacular_alphabet" ' . $font_family . '>' . $alphabet . '</span>';
		}

		return <<<HTML
<div class="webonary-admin-block">
    <div class="flex-column">
    	<h4>Vernacular Browse view:</h4>
    	<input type="hidden" name="languagecode" value="$lang_code">
    	<div class="flex-start-center">
    	    <label for="txtVernacularName"><strong>[$lang_code]</strong> Language Name:</label>
    	    <input id=vernacularName type="text" name="txtVernacularName" id="txtVernacularName" value="$lang_name" $read_only>
        </div>
        <div class="flex-start-center" style="margin-top: 1rem">
            <label for="vernacular_alphabet">Vernacular Alphabet</label>
            <span>(<a href="https://www.webonary.org/help/alphabet/" target="_blank">configure in FLEx</a>):</span>
            $red_text
        </div>
        <div class="flex-start-center" style="width: 100%">
        	$alphabet_out
		</div>
		<div class="flex-start-center" style="margin-top: 1rem">
        	<label for="vernacularLettersFont">Font to use for the vernacular letters in browse view:</label>
        	$select
		</div>
		<div class="flex-start-center" style="margin-top: 1rem">
			<input name="vernacularRightToLeft" id="vernacularRightToLeft" type="checkbox" value="1" $rtl_checked>
			<label for="vernacularRightToLeft">Display right-to-left</label>
		</div>
		<div class="flex-start-center" style="margin-top: 1rem">
			<input name="IncludeCharactersWithDiacritics" id="IncludeCharactersWithDiacritics" type="checkbox" value="1" $diacritics_checked>
			<label for="IncludeCharactersWithDiacritics">Include characters with diacritics (e.g. words starting with ä, à, etc. will all display under a)</label>
		</div>
	</div>
</div>
<script type="text/javascript">
	document.addEventListener("DOMContentLoaded", function() {
		document.getElementById('vernacularLettersFont').onchange = function() {
			document.getElementById('vernacular_alphabet').style.fontFamily = this.value;
		}
	});
</script>
HTML;
	}

	private static function GetReversalIndexesBlock(array $lang_codes): string
	{
		$return_val = [];
		$is_last = true;

		for ($i = 3; $i > 0; $i--) {

			$lang_code = get_option('reversal' . $i . '_langcode');
			$k = array_search($lang_code, array_column($lang_codes, 'language_code'));
			if ($k !== false) {
				array_unshift($return_val, self::BuildReversalIndexBlock($i, $lang_code, $lang_codes[$k]['name'], $is_last));
				$is_last = false;
			}
		}

		if (empty($return_val))
			return '';

		$blocks = implode(PHP_EOL, $return_val);

		return <<<HTML
<div class="webonary-admin-block">
	<div class="flex-column">
		<h4>Reversal Indexes:</h4>
		$blocks
	</div>
</div>
HTML;
	}

	private static function BuildReversalIndexBlock(int $idx, string $lang_code, string $lang_name, bool $is_last): string
	{
		$read_only = !empty(IS_CLOUD_BACKEND) ? 'readonly' : '';
		$field_name = 'reversal' . $idx . '_alphabet';
		$rtl_checked = checked('1', get_option('reversal' . $idx . 'RightToLeft'), false);
		$alphabet = trim(stripslashes(get_option($field_name)));

		// default to English alphabet
		if (strlen($alphabet) == 0)
			$alphabet = implode(',', range('a', 'z'));

		if (is_super_admin()) {
			$red_text = '<span style="color:red;">Only remove letters, do not change/add letters!</span>';
			$alphabet_out = sprintf('<input type="text" name="%1$s" id="%1$s" class="admin-alphabet" value="' . $alphabet . '">', $field_name);
		}
		else {
			$red_text = '';
			$alphabet_out = '<span>' . $alphabet . '</span>';
		}

		if ($is_last)
			$button = <<<HTML
<div style="margin: 2rem 0">
	<input type="submit" name="save_settings" class="button-primary" value="Save Changes">
</div>
HTML;
		else
			$button = '';

		return <<<HTML
<div class="flex-column">
	<em>Reversal Index $idx</em>
	<span>Shortcode: [reversalindex$idx]</span>
	<input type="hidden" name="reversal{$idx}_langcode" value="$lang_code">
	<div class="flex-start-center" style="margin-top: 1rem">
		<strong>[$lang_code]</strong>
		<label for="reversalName$idx">Language Name:</label>
		<input id="reversalName$idx" type="text" name="txtReversalName" value="$lang_name" $read_only>
	</div>
	<div class="flex-start-center" style="margin-top: 1rem">
		<label for="$field_name">Alphabet</label>
		<span>(<a href="https://www.webonary.org/help/alphabet/" target="_blank">configure in FLEx</a>):</span>
		$red_text
	</div>
	<div class="flex-start-center" style="width: 100%">
		$alphabet_out
	</div>
	<div class="flex-start-center" style="margin: 0.2rem 0 2rem">
		<input name="reversal{$idx}RightToLeft" id="reversal{$idx}RightToLeft" type="checkbox" value="1" $rtl_checked>
		<label for="reversal{$idx}RightToLeft">Display right-to-left</label>
	</div>
	$button
</div>
HTML;
	}

	private static function BuildFontBlock(string $font, array $fonts_available, array $system_fonts, array $fonts_default): string
	{
		// check for saved value
		$option_name = Webonary_Font_Management::GetFontOptionName($font);
		$selected = get_option($option_name, '');
		$find_len = mb_strlen($font);
		$saved = '';

		// check for an available font
		if (empty($selected)) {
			foreach ($fonts_available as $available) {
				if (mb_strlen($available) == $find_len) {
					if (mb_stripos($available, $font) !== false) {
						$selected = $available;
						$saved = 'Auto-selected, not saved';
						break;
					}
				}
			}
		}

		// check for a system font
		if (empty($selected)) {
			foreach ($system_fonts as $system) {
				if (mb_strlen($system) == $find_len) {
					if (mb_stripos($system, $font) !== false) {
						$selected = $system;
						$saved = 'Auto-selected, not saved';
						break;
					}
				}
			}
		}

		// check for a default font
		if (empty($selected)) {
			foreach ($fonts_default as $default) {
				if (mb_strlen($default) == $find_len) {
					if (mb_stripos($default, $font) !== false) {
						$selected = $default;
						$saved = 'Auto-selected, not saved';
						break;
					}
				}
			}
		}

		$select = self::GetAvailableFontSelect($option_name, $selected);
		return <<<HTML
<tr>
	<td style="padding-right: 0.5rem"><label for="$option_name" style="font-weight: 700">$font</label></td>
	<td>$select</td>
	<td style="padding-left: 0.5rem"><span style="color: #990000">$saved</span></td>
</tr>
HTML;

	}
}
