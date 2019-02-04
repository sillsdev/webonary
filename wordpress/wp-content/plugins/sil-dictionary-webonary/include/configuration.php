<?php
class Config
{
	/*
	 * Table and taxonomy attributes
	 */

	public static $search_table_name = SEARCHTABLE;
	public static $reversal_table_name = REVERSALTABLE;
	public static $pos_taxonomy = 'sil_parts_of_speech';
	public static $semantic_domains_taxonomy = 'sil_semantic_domains';
}
/**
 * Set up the SIL Dictionary in WordPress Dashboard Tools
 */
function add_admin_menu() {

	$data = get_userdata( get_current_user_id() );
	$role = ( array ) $data->roles;

	if ( $role[0] == "editor" || $role[0] == "administrator" || is_super_admin())
	{
		add_menu_page( "Webonary", "Webonary", 'edit_pages', "webonary", "webonary_conf_dashboard",  get_bloginfo('wpurl') . "/wp-content/plugins/sil-dictionary-webonary/images/webonary-icon.png", 76 );
		add_submenu_page('edit.php', 'Missing Senses', 'Missing Senses', 3, __FILE__, 'report_missing_senses');
		remove_submenu_page('edit.php', 'sil-dictionary-webonary/include/configuration.php');
	}
}

function on_admin_bar(){
	global $wp_admin_bar;

	$wp_admin_bar->add_menu(array(
			'id' => 'Webonary',
			'title' => 'Webonary',
			'parent' => 'site-name',
			'href' => admin_url('/admin.php?page=webonary'),
	));

	$wp_admin_bar->remove_menu( "themes" );
	$wp_admin_bar->remove_menu( "widgets" );
	$wp_admin_bar->remove_menu( "menus" );
}

function get_admin_sections() {
	//$q_config['admin_sections'] = array();
	//$admin_sections = &$q_config['admin_sections'];
	$admin_sections = array();

	$admin_sections['import'] = __('Data (Import)', 'sil_dictionary');
	$admin_sections['search'] = __('Search', 'sil_dictionary');
	$admin_sections['browse'] = __('Browse Views', 'sil_dictionary');
	$admin_sections['fonts'] = __('Fonts', 'sil_dictionary');
	if(is_super_admin())
	{
		$admin_sections['superadmin'] = __('Super Admin', 'sil_dictionary');
	}

	return $admin_sections;
}

function admin_section_start($nm) {
	echo '<div id="tab-'.$nm.'" class="hidden">'.PHP_EOL;
}

function admin_section_end($nm, $button_name=null, $button_class='button-primary') {
	if(isset($button_name))
	{
		echo '<p class="submit" style="float:left;"><input type="submit" name="save_settings"';
		if($button_class) echo ' class="'.$button_class.'"';
		echo ' value="'.$button_name.'" /></p><br><br>';
	}
	echo '</div>'.PHP_EOL; //'<!-- id="tab-'.$nm.'" -->';
}

function ajaxlanguage()
{
	global $wpdb;

	$sql = "SELECT name
	FROM $wpdb->terms
	WHERE slug = '" . $_POST['languagecode'] . "'";

	$languagename = $wpdb->get_var( $sql);


	echo $languagename;
	die();
}

add_action( 'wp_ajax_getAjaxlanguage', 'ajaxlanguage' );
add_action( 'wp_ajax_nopriv_getAjaxlanguage', 'ajaxlanguage' );

function get_LanguageCodes($languageCode = null) {
	global $wpdb;

	$sql = "SELECT language_code, name
		FROM " . $wpdb->prefix . "sil_search
		LEFT JOIN " . $wpdb->terms . " ON " . $wpdb->terms . ".slug = " . $wpdb->prefix . "sil_search.language_code";
	if(isset($languageCode))
	{
		$sql .= " WHERE language_code = '" . $languageCode . "' ";
	}
	else
	{
		$sql .= " WHERE language_code != '' ";
	}
	$sql .= " GROUP BY language_code
		ORDER BY language_code";

	return $wpdb->get_results($sql, 'ARRAY_A');
}

function relevanceForm()
{
	global $wpdb;

	$sql = "SELECT class AS classname, relevance
		FROM " . $wpdb->prefix . "sil_search
		GROUP BY class
		ORDER BY relevance DESC";

	$arrClasses = $wpdb->get_results($sql);

?>
<form action="admin.php?page=webonary#search" method="post" enctype="multipart/form-data">
<h1>Relevance Settings for Fields</h1>
<p>
The search returns results based on relevance. That is, if the word you are looking for is found in a headword, that will be more important than finding the word in a definition for another word.
</p>
<p>
Normally you don't need to change anything here. But if you import a custom field, it will be imported with a relevance of zero in which case you have the option to change the relevance setting.
</p>
<?php
	if(count($arrClasses) == 0)
	{
		echo "<strong>You need to reimport this dictionary if you want to change the relevance settings.</strong>";
	}
	echo "<ul>";
	foreach($arrClasses as $class)
	{
		if(strpos($class->classname, "abbr") !== false || strpos($class->classname, "partofspeech") !== false || (strpos($class->classname, "headword") !== false && $class->relevance == 0))
		{
			continue;
		}
		echo "<li><div><strong>" . $class->classname . ": </strong></div>";
		if($class->relevance == 100 && strpos($class->classname, "headword") !== false)
		{
			echo $class->relevance;
		}
		else
		{
		?>
			<div>
			<input type="hidden" name=classname[] value="<?php echo $class->classname; ?>">
			<input type="text" name=relevance[] size=5 value="<?php echo $class->relevance; ?>">
			</div>
		<?php
		}
		echo "</li>";
	}
	echo "</ul>";
	?>
<p>
	<input type="submit" name="btnSaveRelevance" value="Save">
</p>
</form>
<?php
}

function relevanceSave()
{
	global $wpdb;

	$tableCustomRelevance = $wpdb->prefix . "custom_relevance";
	$sql = "CREATE TABLE IF NOT EXISTS " . $tableCustomRelevance . "(
			`class` varchar(50),
			`relevance` tinyint,
			PRIMARY KEY  (`class`)
			)";

	dbDelta( $sql );

	$r = 0;
	foreach($_POST['classname'] as $class)
	{

		if($_POST['relevance'][$r] < 0 || $_POST['relevance'][$r] > 100 || !is_numeric($_POST['relevance'][$r]))
		{
			echo "<span style=\"color: red;\">Relevance has to be >= 0 and <= 100 for all fields!</span><br>";
			return false;
		}
		//echo $class . ": " . $_POST['relevance'][$r] . "<br>";

		$wpdb->query ("UPDATE " . $wpdb->prefix . "sil_search SET relevance = ". $_POST['relevance'][$r] ." WHERE class = '".$class."'");

		$result = $wpdb->get_results ("SELECT relevance FROM $tableCustomRelevance WHERE class = '".$class."'");

		if (count ($result) > 0) {
			$wpdb->query ("UPDATE $tableCustomRelevance SET relevance = ". $_POST['relevance'][$r] ." WHERE class = '".$class."'");
		} else {
			$wpdb->query ("INSERT INTO $tableCustomRelevance (class, relevance) VALUES ('".$class."'," . $_POST['relevance'][$r] . ")");
		}

		$wpdb->query($sql);
		$r++;
	}

	$wpdb->print_error();

	if($wpdb->last_error === '')
	{
		echo "<h3>Relevance Settings were saved.</h3>";
	}
	echo "<hr>";
}

//display the senses that don't get linked in the reversal browse view
function report_missing_senses()
{
	global $wpdb;
?>
	<div class="wrap">
		<h2>Missing Senses for the <?php echo $_GET['language'];?> browse view</h2>
		One or more senses will not get found for the following entries when clicking on them in the browse view.<br>
		Please check in the FLEx dictionary view, if they show up there.
		<ul>
		<?php
		$sql = " SELECT search_strings " .
				" FROM " . $wpdb->prefix . "sil_search" .
				" WHERE post_id = 0 AND language_code = '" . $_GET['languageCode'] . "'";

		$arrMissing = $wpdb->get_results($sql);

		foreach($arrMissing as $missing)
		{
			echo "<li>" . $missing->search_strings . "</li>";
		}
		?>
		</ul>
		<a href="admin.php?page=webonary">Back to the Webonary settings</a>
	</div>
<?php
}

/**
 * Do what the user said to do.
 */
function save_configurations() {
	global $wpdb;

	if ( ! empty( $_POST['delete_data'])) {
		clean_out_dictionary_data();
	}
	if ( ! empty( $_POST['save_settings'])) {
		update_option("publicationStatus", $_POST['publicationStatus']);
		update_option("include_partial_words", $_POST['include_partial_words']);
		//update_option("distinguish_diacritics", $_POST['distinguish_diacritics']);
		if(isset($_POST['normalization']))
		{
			update_option("normalization", $_POST['normalization']);
		}
		$special_characters = $_POST['characters'];
		if(trim($special_characters) == "")
		{
			$special_characters = "empty";
		}
		update_option("special_characters", $special_characters);
		update_option("inputFont", $_POST['inputFont']);
		update_option("vernacularLettersFont", $_POST['vernacularLettersFont']);

		//We no longer give the option to set this (only to unset it) as this can be done in FLEx
		$displaySubentriesAsMainEntries = 'no';
		if(isset($_POST['DisplaySubentriesAsMainEntries']))
		{
			$displaySubentriesAsMainEntries = 1;
		}
		update_option("DisplaySubentriesAsMainEntries", $displaySubentriesAsMainEntries);
		update_option("languagecode", $_POST['languagecode']);
		if(is_super_admin())
		{
			update_option("vernacular_alphabet", $_POST['vernacular_alphabet']);
		}

		//We no longer give the option to set this (only to unset it) as the letter headers/sorting should be done in FLEx
		$IncludeCharactersWithDiacritics = 'no';
		if(isset($_POST['IncludeCharactersWithDiacritics']))
		{
			$IncludeCharactersWithDiacritics = 1;
		}
		update_option("IncludeCharactersWithDiacritics", $IncludeCharactersWithDiacritics);

		update_option("displayCustomDomains", $_POST['displayCustomDomains']);

		$vernacularRightToLeft = 'no';
		if(isset($_POST['vernacularRightToLeft']))
		{
			$vernacularRightToLeft = 1;
		}
		update_option("vernacularRightToLeft", $vernacularRightToLeft);

		update_option("reversal1_langcode", $_POST['reversal1_langcode']);
		update_option("reversal2_langcode", $_POST['reversal2_langcode']);
		update_option("reversal3_langcode", $_POST['reversal3_langcode']);
		if(is_super_admin())
		{
			update_option("reversal1_alphabet", $_POST['reversal1_alphabet']);
			update_option("reversal2_alphabet", $_POST['reversal2_alphabet']);
			update_option("reversal3_alphabet", $_POST['reversal3_alphabet']);
		}

		$reversal1RightToLeft = 'no';
		if(isset($_POST['reversal1RightToLeft']))
		{
			$reversal1RightToLeft = 1;
		}
		update_option("reversal1RightToLeft", $reversal1RightToLeft);

		$reversal2RightToLeft = 'no';
		if(isset($_POST['reversal2RightToLeft']))
		{
			$reversal2RightToLeft = 1;
		}
		update_option("reversal2RightToLeft", $reversal2RightToLeft);

		$reversal3RightToLeft = 'no';
		if(isset($_POST['reversal3RightToLeft']))
		{
			$reversal3RightToLeft = 1;
		}
		update_option("reversal3RightToLeft", $reversal3RightToLeft);

		if(trim(strlen($_POST['txtVernacularName'])) == 0)
		{
			echo "<br><span style=\"color:red\">Please fill out the textfields for the language names, as they will appear in a dropdown below the searcbhox.</span><br>";
		}

		$arrLanguages[0]['name'] = "txtVernacularName";
		$arrLanguages[0]['code'] = "languagecode";
		$arrLanguages[1]['name'] = "txtReversalName";
		$arrLanguages[1]['code'] = "reversal1_langcode";
		$arrLanguages[2]['name'] = "txtReversal2Name";
		$arrLanguages[2]['code'] = "reversal2_langcode";
		$arrLanguages[3]['name'] = "txtReversal3Name";
		$arrLanguages[3]['code'] = "reversal3_langcode";

		foreach($arrLanguages as $language)
		{
			if(strlen(trim($_POST[$language['code']])) != 0)
			{
				$sql = "SELECT term_id, name
				FROM $wpdb->terms
				WHERE slug = '" . $_POST[$language['code']] . "'";

				$arrLanguageNames = $wpdb->get_results($sql);

				if(count($arrLanguageNames) > 0)
				{
					$sql = "UPDATE $wpdb->terms SET name = '" . $_POST[$language['name']]  . "' WHERE slug = '" . $_POST[$language['code']]  . "'";
					$termid = $arrLanguageNames[0]->term_id;
				}
				else
				{
					$sql = "INSERT INTO  $wpdb->terms (name,slug) VALUES ('" . $_POST[$language['name']] . "','" . $_POST[$language['code']] . "')";
					$termid = $wpdb->insert_id;
				}

				$wpdb->query( $sql );


				if(count($arrLanguageNames) > 0)
				{
					$sql = "UPDATE $wpdb->term_taxonomy SET description = '" . $_POST[$language['name']] . "' WHERE term_id = " . $termid;
				}
				else
				{
					$sql = "INSERT INTO  $wpdb->term_taxonomy (term_id, taxonomy,description,count) VALUES (" . $term_id . ", 'sil_writing_systems', '" . $_POST[$language['name']] . "',999999)";
				}

				$wpdb->query( $sql );
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
			if ( is_plugin_active( 'wp-super-cache/wp-cache.php' ) && $noSearchForm == 1)
			{
				prune_super_cache( get_supercache_dir(), true );
			}

		}
		update_option("noSearch", $noSearchForm);



		echo "<br>" . _e('Settings saved');
	}
}

function webonary_conf_dashboard()
{
	webonary_conf_widget(true);
}
function webonary_conf_widget($showTitle = false) {
	save_configurations();

	$upload_dir = wp_upload_dir();

	$fontClass = new fontManagment();
	$css_string = null;
	$configured_css_file = $upload_dir['basedir'] . '/imported-with-xhtml.css';
	if(file_exists($configured_css_file))
	{
		$css_string = file_get_contents($configured_css_file);
	}
	$arrUniqueCSSFonts = $fontClass->get_fonts_fromCssText($css_string);
	wp_register_style('custom_css', $upload_dir['baseurl'] . '/custom.css?time=' . date("U"));
	wp_enqueue_style( 'custom_css');

	if(is_super_admin() && isset($_POST['uploadButton']))
	{
		$fontClass->uploadFontForm();
	}

	if(isset($_GET['changerelevance']))
	{
		relevanceForm();
	}

	if(isset($_POST['uploadFont']))
	{
		$fontClass->uploadFont();
	}

	if(isset($_POST['btnSaveRelevance']))
	{
		relevanceSave();
	}
	?>
	<script src="<?php echo get_bloginfo('wpurl'); ?>/wp-content/plugins/sil-dictionary-webonary/js/options.js" type="text/javascript"></script>
	<style>
	#dashboard-widgets a {
		text-decoration: underline;
	}
	<?php
	if(get_option('inputFont') != "")
	{
	?>
	#characters {
		font-family: "<?php echo get_option('inputFont'); ?>";
	}
	<?php
	}
	?>
	<?php
	if(get_option('vernacularLettersFont') != "")
	{
	?>
	#vernacularAlphabet {
		font-family: "<?php echo get_option('vernacularLettersFont'); ?>";
	}
	<?php
	}
	?>
	</style>
	<div class="wrap">
	<?php
	if($showTitle)
	{
	?>
		<h2><?php _e( 'Webonary', 'webonary' ); ?></h2>
	<?php
	}
	?>
	<?php
	_e('Webonary provides the admininstration tools and framework for using WordPress for dictionaries. See <a href="https://www.webonary.org/help" target="_blank">Webonary Support</a> for help.', 'sil_dictionary'); ?>

	<?php
	$admin_sections = get_admin_sections();
	echo '<h2 class="nav-tab-wrapper">'.PHP_EOL;
	foreach( $admin_sections as $slug => $name ){
		echo '<a class="nav-tab" href="#'.$slug.'" title="'.sprintf(__('Click to switch to %s', 'sil_dictionary'), $name).'">'.$name.'</a>'.PHP_EOL;
	}
	echo '</h2>'.PHP_EOL;

	$arrLanguageCodes = get_LanguageCodes();

	// enctype="multipart/form-data"
	?>
	<script>
	function getLanguageName(selectbox, langname)
	{
		var e = document.getElementById(selectbox);
		var langcode = e.options[e.selectedIndex].value;

		jQuery.ajax({
     		url: '<?php echo admin_url('admin-ajax.php'); ?>',
     		data : {action: "getAjaxlanguage", languagecode : langcode},
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
			<div class="tabs-content"><?php //<!-- tabs-container --> ?>
			<?php
			//////////////////////////////////////////////////////////////////////////////
			admin_section_start('import');

			$import = new sil_pathway_xhtml_Import();
			?>
			<p>
			<h3><?php _e( 'Import Data', 'sil_dictionary' ); ?></h3>

			<div style="max-width: 600px; border-style:solid; border-width: 1px; border-color: red; padding: 5px;">
			<form enctype="multipart/form-data" id="import-upload-form" method="post" action="<?php echo esc_attr(
				wp_nonce_url("admin.php?import=pathway-xhtml&amp;step=1", 'import-upload')); ?>">
			<strong>Import Status:</strong> <?php echo Webonary_Info::import_status(); ?>
			</form>
			</div>

			<p><?php _e('<a href="admin.php?import=pathway-xhtml" style="font-size:16px;">Click here to import your FLEx data</a>', 'sil_dictionary'); ?></p>

			<form id="configuration-form" method="post" action="">
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
			<p>
			<h3><?php _e( 'Delete Data', 'sil_dictionary' ); ?></h3>
			<br>
			<?php if(strpos($_SERVER['HTTP_HOST'], 'localhost') === false && is_super_admin()) { ?>
				<strong style=color:red;>You are not in your testing environment!</strong>
				<br>
			<?php } ?>
			<?php
			if($_GET['delete_taxonomies'] == 1)
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
				<input type="submit" name="delete_data" value="<?php _e('Delete', 'sil_dictionary'); ?>">
				<br>
				<?php _e('(deletes all posts in the category "webonary")', 'sil_dictionary'); ?>
			</p>

			<?php admin_section_end('import', 'Save Changes'); ?>
			<?php
			//////////////////////////////////////////////////////////////////////////////
			admin_section_start('search');
			?>
			<p>
			<h3><?php _e('Default Search Options');?></h3>
			<input name="include_partial_words" type="checkbox" value="1"
						<?php checked('1', get_option('include_partial_words')); ?> />
						<?php _e('Always include searching through partial words.'); ?>
			<br>
			<?php
			/*
			?>
			<input name="distinguish_diacritics" type="checkbox" value="1"
						<?php checked('1', get_option('distinguish_diacritics')); ?> />
						<?php _e('Distinguish diacritic letters'); ?>
			</p>
			<?php */ ?>
			<p>
			<?php
			//this is only for legacy purposes.
			//Now the import will convert all text to NFC, so this is no longer needed for newer imports
			if(get_option("normalization") != null)
			{
			?>
				<strong>Normalization:</strong>
				<br>
				<select name="normalization">
					<option value="FORM_C" <?php if(get_option("normalization") == "FORM_C") { echo "selected"; } ?>>FORM C</option>
					<option value="FORM_D" <?php if(get_option("normalization") == "FORM_D") { echo "selected"; } ?>>FORM D</option>
				</select>
				<br>
				See <a href="http://unicode.org/reports/tr15/" target="_blank">here</a> for more info on normalization of composite characters.
				<br>
				By default Webonary uses FORM C. If your search for a word that contains
				a composite character doesn't return a result, try FORM D.
				</p>
			<?php
			}
			if(class_exists(special_characters))
			{
				//this is here for legacy purposes. The special characters used to be Widget in a separate plugin.
				//we need to get those characters for older ditionary sites and display them in the dashboard.
				$charWidget = new special_characters();
				$settings = $charWidget->get_settings();
				$settings = reset($settings);
			}
			$special_characters = get_option('special_characters');
			if(trim($special_characters) == "" && !isset($_POST['characters']) && trim($special_characters) != "empty")
			{
				$special_characters = $settings['characters'];
			}
			$special_characters = str_replace("empty", "", $special_characters);
			?>
			<p>
			<strong><?php _e('Special character input buttons');?></strong>
			<br>
			These will appear above the search field.<br>
			Separate the characters by comma:
			<input type="input" name="characters" id=characters type="checkbox" value="<?php echo $special_characters; ?>">
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
					<option value="<?php echo $font;?>" <?php if($font == get_option("inputFont")) { echo "selected"; } ?>><?php echo $font;?></option>
				<?php
				}
			}
			?>
			</select>
			<br><br>
			<a href="?page=webonary&changerelevance=true#search">Relevance Settings for Fields</a>
			<br><br>
			<?php admin_section_end('search', 'Save Changes'); ?>
			<?php
			//////////////////////////////////////////////////////////////////////////
			admin_section_start('browse');
			?>
			<p>
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
			<p>
			<?php
			}
			if(count($arrLanguageCodes) == 0)
			{
			?>
				<span style="color:red">You need to first import your dictionary.</span>
				<p>
			<?php
			}
			if(count($arrLanguageCodes) > 0)
			{
			?>
				<i><?php _e('Vernacular Browse view:'); ?></i><br>
				<?php $i = array_search(get_option('languagecode'), array_column($arrLanguageCodes, 'language_code')); ?>
				<input type="hidden" name="languagecode" value="<?php echo get_option('languagecode'); ?>">
				<strong>[<?php  echo get_option('languagecode'); ?>]</strong> <?php _e('Language Name:'); ?> <input id=vernacularName type="text" name="txtVernacularName" value="<?php if(count($arrLanguageCodes) > 0) { echo $arrLanguageCodes[$i]['name']; } ?>">
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
						<option value="<?php echo $font;?>" <?php if($font == get_option("vernacularLettersFont")) { echo "selected"; } ?>><?php echo $font;?></option>
					<?php
					}
				}
				?>
				</select>

				<input name="vernacularRightToLeft" type="checkbox" value="1" <?php checked('1', get_option("vernacularRightToLeft")); ?> /><?php _e('Display right-to-left') ?>

				<p>
				<?php
				$IncludeCharactersWithDiacritics = get_option('IncludeCharactersWithDiacritics');
				if($IncludeCharactersWithDiacritics == 1)
				{
					$IncludeCharactersWithDiacritics = 1;
				?>
				<input name="IncludeCharactersWithDiacritics" type="checkbox" value="1" <?php checked('1', $IncludeCharactersWithDiacritics); ?> />
				<?php _e('Include characters with diacritics (e.g. words starting with ä, à, etc. will all display under a)')?>
				<?php
				}
				?>
				<p>
				<b><?php _e('Reversal Indexes:'); ?></b>
				<p>
				<?php
				$displayXHTML = true;
				$reversalEntries = getReversalEntries("", 0, "", $displayXHTML, "");

				if(count($reversalEntries) == 0)
				{
					echo "No reversal indexes imported.";
				}
				else
				{
					$k = array_search(get_option('reversal1_langcode'), array_column($arrLanguageCodes, 'language_code'));

					if($k >= 0)
					{
					?>
						<i><?php _e('1. Reversal index'); ?></i><br>
						Shortcode: [reversalindex1]
						<p>
						<input type="hidden" name="reversal1_langcode" value="<?php echo get_option('reversal1_langcode'); ?>">
						<strong>[<?php echo get_option('reversal1_langcode'); ?>]</strong>
						<?php _e('Language Name:'); ?> <input id=reversalName type="text" name="txtReversalName" value="<?php if(count($arrLanguageCodes) > 0) { echo $arrLanguageCodes[$k]['name']; } ?>">
						<p>
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
						<input name="reversal1RightToLeft" type="checkbox" value="1" <?php checked('1', get_option("reversal1RightToLeft")); ?> /><?php _e('Display right-to-left') ?>
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
						<p>
						<?php _e('Alphabet:'); ?> (<a href="https://www.webonary.org/help/alphabet/" target="_blank"><?php _e('configure in FLEx'); ?></a>):
						<?php
						if(is_super_admin())
						{
						?>
							<span style="color:red;">Only remove letters, do not change/add letters!</span><br>
							<input type="text" size=50 name="reversal2_alphabet" value="<?php echo stripslashes(get_option('reversal2_alphabet')); ?>">
						<?php
						}
						else
						{
						 	echo stripslashes(get_option('reversal2_alphabet'));
						}
						?>
						<input name="reversal2RightToLeft" type="checkbox" value="1" <?php checked('1', get_option("reversal2RightToLeft")); ?> /><?php _e('Display right-to-left') ?>
						<?php
					}
					?>
					<?php
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
						<input name="reversal3RightToLeft" type="checkbox" value="1" <?php checked('1', get_option("reversal3RightToLeft")); ?> /><?php _e('Display right-to-left') ?>
						<?php
					}
				}
				?>

				<?php
				if(is_super_admin())
				{
					$displayCustomDomains = get_option('displayCustomDomains');
					/*
					if($displayCustomDomains != "no" && !isset($displayCustomDomains))
					{
						$displayCustomDomains = 1;
					}
					*/
				?>
					<h3>Semantic Domains</h3>
					<select name="displayCustomDomains">
						<option value="default" <?php selected($displayCustomDomains, "default"); ?>>Default View</option>
						<option value="yakan" <?php selected($displayCustomDomains, "yakan"); ?>>Yakan (Philippines)</option>
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
		?>
		</p>
		<?php admin_section_end('browse', 'Save Changes'); ?>
		<?php
		//////////////////////////////////////////////////////////////////////////
		admin_section_start('fonts');
		?>
		<p>
		<h3>Fonts</h3>
		<p>
		See <a href="https://www.webonary.org/help/setting-up-a-font/" target="_blank">Setting up a Font</a>.
		<hr>
		<?php
		$arrFontFacesFile = array();
		$customCSSFilePath = $upload_dir['basedir'] . '/custom.css';
		if (file_exists($customCSSFilePath)) {
			$fontFacesFile = file_get_contents($customCSSFilePath);
			$arrFontFacesFile = $fontClass->get_fonts_fromCssText($fontFacesFile);
		}
		$options = get_option('themezee_options');

		$arrFont = $fontClass->getFontsAvailable();

		if(isset($arrUniqueCSSFonts))
		{
			$fontNr = 0;
			foreach($arrUniqueCSSFonts as $userFont)
			{
				$userFont = trim($userFont);

				$fontKey = array_search($userFont, array_column($arrFont, 'name'));

				if(!strstr($userFont, "default font"))
				{
					echo "<strong>" . $userFont . "</strong><br>";
					$fontLinked = false;
					if(count($arrFontFacesFile) > 0)
					{
						if(in_array($userFont, $arrFontFacesFile))
						{
							$fontLinked = true;
							echo "linked in <a href=\"" . $upload_dir['baseurl'] . "/custom.css\">custom.css</a>";
						}
					}

					if($fontLinked)
					{
						if($fontKey !== false)
						{
							if($arrFont[$fontKey]["hasSubFonts"])
							{
								echo "<span style=\"color:orange; font-weight: bold;\">This web font is very large and will take a long time to load! Please use a <a href=\"https://www.webonary.org/help/setting-up-a-font/\" target=\"_blank\" style=\"color:orange; font-weight:bold;\">font subset</a> if possible.</span>";
							}
						}
					}

					if(!$fontLinked)
					{
						$arrSystemFonts = $fontClass->get_system_fonts();
						if(in_array($userFont, $arrSystemFonts))
						{
							echo "This is a system font that most computers already have installed.";
						}
						else
						{
							echo "<strong style=\"color:red;\">";
							if($fontKey > 0)
							{
								echo "Font not linked. Please reupload the css file to get it linked.";
							}
							else
							{
								echo "Font not found in the repository. Please ask Webonary Support to add it.";
							}
							echo "</strong>";
						}
					}

					if(is_super_admin() && !in_array($userFont, $arrSystemFonts))
					{
					?>
							<input type="hidden" name="fontname[<?php echo $fontNr; ?>]" value="<?php echo $userFont; ?>">
							<input type="submit" value="Upload" name="uploadButton[<?php echo $fontNr; ?>]">
					<?php
						$fontNr++;
					}

					echo "<p></p>";
				}
			}
		}
		?>
		<?php admin_section_end('fonts'); ?>
		<?php
		//////////////////////////////////////////////////////////////////////////
		admin_section_start('superadmin');
		?>
		<p>
		<h3>Notes</h3>
		Site ID: <?php echo get_current_blog_id(); ?>
		<p>
		<span style="color:red">These notes are only visible to super admins.</span>
		<p>
		<textarea name=txtNotes cols=50 rows=6><?php echo stripslashes(get_option("notes"));?></textarea>
		<p>
		Hide search form: <input name="noSearchForm" type="checkbox" value="1" <?php checked('1', get_option("noSearch")); ?> />
		<p>
		<?php admin_section_end('superadmin', 'Save Changes'); ?>

		</div><?php //<!-- /tabs-container --> ?>
		</form>
	</div>
	<?php
}
