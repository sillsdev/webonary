<?php
/**
 * Set up the SIL Dictionary in WordPress Dashboard Tools
 */
function add_admin_menu() {
	add_menu_page( "Webonary", "Webonary", true, "webonary", "webonary_conf_dashboard",  get_bloginfo('wpurl') . "/wp-content/plugins/sil-dictionary-webonary/images/webonary-icon.png", 76 );
	add_submenu_page('edit.php', 'Missing Senses', 'Missing Senses', 3, __FILE__, 'report_missing_senses');
	remove_submenu_page('edit.php', 'sil-dictionary-webonary/include/configuration.php');
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

	return $wpdb->get_results($sql);;
}

//display the senses that don't get linked in the reversal browse view
function report_missing_senses()
{
	global $wpdb;
?>
	<div class="wrap">
		<h2>Missing Senses for the <?php echo $_GET['language'];?> browse view</h2>
		These senses will not get found when clicking on them in the browse view.<br>
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
		update_option("normalization", $_POST['normalization']);
		$special_characters = $_POST['characters'];
		if(trim($special_characters) == "")
		{
			$special_characters = "empty";
		}
		update_option("special_characters", $special_characters);
		update_option("inputFont", $_POST['inputFont']);
		
		$displaySubentriesAsMainEntries = 'no';
		if(isset($_POST['DisplaySubentriesAsMainEntries']))
		{
			$displaySubentriesAsMainEntries = 1;
		}
		update_option("DisplaySubentriesAsMainEntries", $displaySubentriesAsMainEntries);
		update_option("languagecode", $_POST['languagecode']);
		update_option("vernacular_alphabet", $_POST['vernacular_alphabet']);
		 
		$IncludeCharactersWithDiacritics = 'no';
		if(isset($_POST['IncludeCharactersWithDiacritics']))
		{
			$IncludeCharactersWithDiacritics = 1;
		}
		update_option("IncludeCharactersWithDiacritics", $IncludeCharactersWithDiacritics);
		 
		update_option("reversalType", $_POST['reversalType']);
		update_option("reversal1_langcode", $_POST['reversal1_langcode']);
		update_option("reversal1_alphabet", $_POST['reversal1_alphabet']);
		update_option("reversal2_alphabet", $_POST['reversal2_alphabet']);
		update_option("reversal2_langcode", $_POST['reversal2_langcode']);
		update_option("reversal3_alphabet", $_POST['reversal3_alphabet']);
		update_option("reversal3_langcode", $_POST['reversal3_langcode']);
		

		if(trim(strlen($_POST['txtVernacularName'])) == 0)
		{
			echo "<br><span style=\"color:red\">Please fill out the textfields for the language names, as they will appear in a dropdown below the searcbhox.</span><br>";
		}

		$arrLanguages[0]['name'] = "txtVernacularName";
		$arrLanguages[0]['code'] = "languagecode";
		$arrLanguages[1]['name'] = "txtReversalName";
		$arrLanguages[1]['code'] = "reversal_langcode";
		$arrLanguages[2]['name'] = "txtReversal2Name";
		$arrLanguages[2]['code'] = "reversal2_langcode";

		foreach($arrLanguages as $language)
		{
			if(strlen(trim($_POST[$language['code']])) != 0)
			{
				$sql = "INSERT INTO  $wpdb->terms (name,slug) VALUES ('" . $_POST[$language['name']] . "','" . $_POST[$language['code']] . "')
		  		ON DUPLICATE KEY UPDATE name = '" . $_POST[$language['name']]  . "'";

				$wpdb->query( $sql );

				$lastid = $wpdb->insert_id;

				if($lastid != 0)
				{
					$sql = "INSERT INTO  $wpdb->term_taxonomy (term_id, taxonomy,description,count) VALUES (" . $lastid . ", 'sil_writing_systems', '" . $_POST[$language['name']] . "',999999)
			  		ON DUPLICATE KEY UPDATE description = '" . $_POST[$language['name']]  . "'";

					$wpdb->query( $sql );
				}
			}
		}

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
	
	$fontClass = new fontMonagment();
	$css_string = file_get_contents($upload_dir['baseurl'] . '/imported-with-xhtml.css');
	$arrUniqueCSSFonts = $fontClass->get_fonts_fromCssText($css_string);
	?>
	<script src="<?php echo get_bloginfo('wpurl'); ?>/wp-content/plugins/sil-dictionary-webonary/js/options.js" type="text/javascript"></script>
	<div class="wrap">
	<?php
	if($showTitle)
	{
	?>
		<h2><?php _e( 'Webonary', 'webonary' ); ?></h2>
	<?php
	}
	_e('Webonary provides the admininstration tools and framework for using WordPress for dictionaries. See <a href="http://www.webonary.org/help" target="_blank">Webonary Support</a> for help.', 'sil_dictionary'); ?>
	
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
			<strong>Import Status:</strong> <?php echo $a; echo $import->get_import_status(); ?>
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
			<h3><?php _e('Search Options');?></h3>
			<input name="include_partial_words" type="checkbox" value="1"
						<?php checked('1', get_option('include_partial_words')); ?> />
						<?php _e('Always include searching through partial words.'); ?>
			</p>
			<p>
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
			$charWidget = new special_characters();
			$settings = $charWidget->get_settings();
			$settings = reset($settings);
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
			<input type="input" name="characters" type="checkbox" value="<?php echo $special_characters; ?>">
			</p>
			<b>Font to use for the search field and character buttons:</b>
			<br>
			<select name=inputFont>
			<option value=""></option>
			<?php
			$arrUniqueCSSFonts = $fontClass->get_fonts_fromCssText($css_string);
			foreach($arrUniqueCSSFonts as $font)
			{
			?>
				<option value="<?php echo $font;?>" <?php if($font == get_option("inputFont")) { echo "selected"; } ?>><?php echo $font;?></option>
			<?php
			}
			?>
			</select>
			<br><br>
			<?php admin_section_end('search', 'Save Changes'); ?>
			<?php
			//////////////////////////////////////////////////////////////////////////
			admin_section_start('browse');
			?>
			<p>
			<h3>Browse Views</h3>
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
				<span style="color:red">You need to first import your xhtml file before you can select a language code.</span>
				<p>
			<?php
			}
			_e('Vernacular Language Code:'); ?>
			<select id=vernacularLanguagecode name="languagecode" onchange="getLanguageName('vernacularLanguagecode', 'vernacularName');">
				<option value=""></option>
				<?php
				$x = 0;
				foreach($arrLanguageCodes as $languagecode) {?>
					<option value="<?php echo $languagecode->language_code; ?>" <?php if(get_option('languagecode') == $languagecode->language_code) { $i = $x; ?>selected<?php }?>><?php echo $languagecode->language_code; ?></option>
				<?php
				$x++;
				} ?>
			</select>
			<?php _e('Language Name:'); ?> <input  id=vernacularName type="text" name="txtVernacularName" value="<?php if(count($arrLanguageCodes) > 0) { echo $arrLanguageCodes[$i]->name; } ?>">
			<p>
			<?php _e('Vernacular Alphabet:'); ?>
			<input name="vernacular_alphabet" type="text" size=50 value="<?php echo stripslashes(get_option('vernacular_alphabet')); ?>" />
			<?php _e('(Letters separated by comma)'); ?>
			<p>
			<?php
			$IncludeCharactersWithDiacritics = get_option('IncludeCharactersWithDiacritics');
			if($IncludeCharactersWithDiacritics != "no" && !isset($IncludeCharactersWithDiacritics))
			{
				$IncludeCharactersWithDiacritics = 1;
			}
			?>
			<input name="IncludeCharactersWithDiacritics" type="checkbox" value="1" <?php checked('1', $IncludeCharactersWithDiacritics); ?> />
			<?php _e('Include characters with diacritics (e.g. words starting with ä, à, etc. will all display under a)')?>
			<p>
			<b><?php _e('Reversal Indexes:'); ?></b>
			<p>
			<?php
			$displayXHTML = true;
			getReversalEntries("", 0, get_option('reversal1_langcode'), $displayXHTML);
			_e('Display:'); ?>
			<select name="reversalType">
				<option value="full">Full FLEx Reversal view</option>
				<option value="minimal" <?php if(!$displayXHTML || get_option("reversalType") == "minimal") { echo "selected"; }?>>Minimal Index view</option>
			</select>
			<p>
			<?php _e('Main reversal index code:'); ?>
			<select id=reversalLangcode name="reversal1_langcode" onchange="getLanguageName('reversalLangcode', 'reversalName');">
				<option value=""></option>
				<?php
					$x = 0;
					foreach($arrLanguageCodes as $languagecode) {?>
					<option value="<?php echo $languagecode->language_code; ?>" <?php if(get_option('reversal1_langcode') == $languagecode->language_code) { $k = $x; ?>selected<?php }?>><?php echo $languagecode->language_code; ?></option>
				<?php
					$x++;
					} ?>
			</select>
			<?php _e('Language Name:'); ?> <input id=reversalName type="text" name="txtReversalName" value="<?php if(count($arrLanguageCodes) > 0) { echo $arrLanguageCodes[$k]->name; } ?>">
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
			<?php _e('Main Reversal Index Alphabet:'); ?>
			<input name="reversal1_alphabet" type="text" size=50 value="<?php echo $reversal1alphabet; ?>" />
			<?php _e('(Letters separated by comma)'); ?>
			<hr>
			 <i><?php _e('2. Reversal index'); ?></i> Shortcode: [reversalindex2]
			 <p>
			<?php _e('Secondary reversal index code:'); ?>
			<select id=reversal2Langcode name="reversal2_langcode" onchange="getLanguageName('reversal2Langcode', 'reversal2Name');">
				<option value=""></option>
				<?php
				$x = 0;
				foreach($arrLanguageCodes as $languagecode) {?>
					<option value="<?php echo $languagecode->language_code; ?>" <?php if(get_option('reversal2_langcode') == $languagecode->language_code) { $n = $x; ?>selected<?php }?>><?php echo $languagecode->language_code; ?></option>
				<?php
				$x++;
				} ?>
			</select>
			<?php _e('Language Name:'); ?> <input id=reversal2Name type="text" name="txtReversal2Name" value="<?php if(count($arrLanguageCodes) > 0) { echo $arrLanguageCodes[$n]->name; } ?>">
			<p>
			<?php _e('Secondary Reversal Index Alphabet:'); ?>
			<input name="reversal2_alphabet" type="text" size=50 value="<?php echo stripslashes(get_option('reversal2_alphabet')); ?>" />
			<?php _e('(Letters separated by comma)'); ?>
			
			<hr>
			 <i><?php _e('3. Reversal index'); ?></i> Shortcode: [reversalindex3]
			 <p>
			<?php _e('Third reversal index code:'); ?>
			<select id=reversal3Langcode name="reversal3_langcode" onchange="getLanguageName('reversal3Langcode', 'reversal3Name');">
				<option value=""></option>
				<?php
				$x = 0;
				foreach($arrLanguageCodes as $languagecode) {?>
					<option value="<?php echo $languagecode->language_code; ?>" <?php if(get_option('reversal3_langcode') == $languagecode->language_code) { $n = $x; ?>selected<?php }?>><?php echo $languagecode->language_code; ?></option>
				<?php
				$x++;
				} ?>
			</select>
			<?php _e('Language Name:'); ?> <input id=reversal3Name type="text" name="txtReversal3Name" value="<?php if(count($arrLanguageCodes) > 0) { echo $arrLanguageCodes[$n]->name; } ?>">
			<p>
			<?php _e('Secondary Reversal Index Alphabet:'); ?>
			<input name="reversal3_alphabet" type="text" size=50 value="<?php echo stripslashes(get_option('reversal3_alphabet')); ?>" />
			<?php _e('(Letters separated by comma)'); ?>
			<?php
			/*
			?>
			<h3><?php _e('Comments');?></h3>
			If you have the comments turned on, you need to re-sync your comments after re-importing of your posts.
			<p>
			<a href="admin.php?import=comments-resync">Re-sync comments</a>
			<?php
			*/
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
		<?php
		$fontFacesFile = file_get_contents($upload_dir['baseurl'] . '/custom.css');
		$arrFontFacesFile = $fontClass->get_fonts_fromCssText($fontFacesFile);
		
		$options = get_option('themezee_options');
		$arrFontFacesZeeOptions = $fontClass->get_fonts_fromCssText($options['themeZee_custom_css']);

		$fontClass->getFontsAvailable($arrFontName, $arrFontStorage, $arrHasSubFonts);
		
		foreach($arrUniqueCSSFonts as $userFont)
		{
			$userFont = trim($userFont);
			
			$fontKey = array_search($userFont, $arrFontName);
			
			if(!strstr($userFont, "default font"))
			{
				echo "<strong>" . $userFont . "</strong><br>";
				$fontLinked = false;
				if(count($arrFontFacesFile) > 0)
				{
					if(in_array($userFont, $arrFontFacesFile))
					{
						$fontLinked = true;
						echo "linked in <a href=\"" . $upload_dir['baseurl'] . "/custom.css\">custom.css</a><br>";
					}
				}
				if(count($arrFontFacesZeeOptions) > 0)
				{
					if(in_array($userFont, $arrFontFacesZeeOptions))
					{
						echo "linked in <a href=\"/wp-admin/themes.php?page=themezee&customcss=1\">zeeDisplay Options</a>";
						if($fontLinked)
						{
							echo " <span style=\"font-weight:bold;\">(you should remove the custom css from here, as it's now in the file custom.css - once is enough...)</span>";
						}
						$fontLinked = true;
								
						echo "<br>";
					}
				}
				
				if($fontLinked)
				{
					if($fontKey > 0)
					{
						if($arrHasSubFonts[$fontKey])
						{
							echo "<span style=\"color:orange; font-weight: bold;\">This web font is very large and will take a long time to load! Please use a <a href=\"http://scripts.sil.org/FontSubsets\" target=\"_blank\" style=\"color:orange; font-weight:bold;\">font subset</a> if possible.</span><br>";
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
				echo "<p></p>";
			}
		}
		?>
		<?php admin_section_end('fonts'); ?>

		</div><?php //<!-- /tabs-container --> ?>
		</form>
	</div>
	<?php
}

