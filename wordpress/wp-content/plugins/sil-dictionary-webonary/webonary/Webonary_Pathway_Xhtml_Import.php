<?php



class Webonary_Pathway_Xhtml_Import extends WP_Importer {

	public $api; //if data is sent from an external program
	public $verbose;

	/*
	 * Relevance level attributes
	 */

	public $headword_relevance = 100;
	public $citationform_relevance = 90;
	public $plural_relevance = 80;
	public $lexeme_form_relevance = 70;
	public $variant_form_relevance = 60;
	public $definition_word_relevance = 50;
	public $semantic_domain_relevance = 40;
	public $scientific_name_relevance = 35;
	public $sense_crossref_relevance = 30;
	public $custom_field_relevance = 20;
	public $example_sentences_relevance = 10;

	/*
	 * DOM attributes
	 */

	public $dom;
	public $dom_xpath;


	//-----------------------------------------------------------------------------//

	function start()
	{
		global $current_user, $webonary_include_path;

		/* @todo See if there is a better way to do this than these steps */
		if ( empty ( $_GET['step'] ) )
			$step = 0;
		else
			$step = (int) $_GET['step'];


		$this->header();

		$this->verbose = false;
		if(isset($_POST['chkShowProgress']))
		{
			$this->verbose = true;
		}

		if(isset($_POST['btnRestartImport']))
		{
			$filetype = "configured";
			remove_entries('');
			remove_entries('flexlinks');
			echo "Restarting Import...<br>";

			if($this->api == false && $this->verbose == false)
			{
				/** @noinspection HtmlUnknownTarget */
				echo "You can now close the browser window. <a href=\"../wp-admin/admin.php?page=webonary\">Click here to view the import status</a><br>";
			}
			flush();

			$file = $this->get_latest_xhtml_file();
			$xhtmlFileURL = $file->url;
			$userid = $current_user->ID;
			include $webonary_include_path . DIRECTORY_SEPARATOR . 'run_import.php';

			return;
		}

		if(isset($_POST['btnReindex']))
		{
			?>
			<DIV ID="flushme">Indexing Search Strings... </DIV>
			<?php
			$this->verbose = true;
			$this->index_searchstrings();

			$file = $this->get_latest_xhtml_file();
			wp_delete_attachment( $file->ID );
		}

		if(isset($_POST['btnRestartReversalImport']))
		{
			$filetype = "reversal";
			echo "Restarting Import of Reversal Entries...<br>";

			if($this->api == false && $this->verbose == false)
			{
				echo "You can now close the browser window. <a href=\"../wp-admin/admin.php?page=webonary\">Click here to view the import status</a><br>";
			}
			flush();

			$file = $this->get_latest_xhtml_file();
			$xhtmlFileURL = $file->url;
			$userid = $current_user->ID;
			include $webonary_include_path . DIRECTORY_SEPARATOR . 'run_import.php';

			return;

		}

		switch ($step) {
			/*
			 * First, greet the user and prompt for files.
			 */
			case 0 :
				$this->hello();
				$this->get_user_input();
				echo '</div>';
				break;
			/*
			 * Second, upload and import files
			 */
			case 1 :
				check_admin_referer('import-upload');

				flush();

				// Get the XHTML file
				$result = $this->upload_files('xhtml');
				if (is_wp_error( $result ))
				{
					echo $result->get_error_message();
				}
				$xhtml_file = $result['file'];

				// Get the CSS file
				$languagecode = "";
				if(isset($_POST['languagecode']))
				{
					$languagecode = $_POST['languagecode'];
				}
				$result = $this->upload_files('css', $_POST['filetype'], $languagecode);
				if (is_wp_error( $result ))
					echo $result->get_error_message();
				$css_file = $result['file'];

				if(isset($_POST['filetype']))
				{
					$filetype = $_POST['filetype'];
				}

				if($this->api == false && $this->verbose == false)
				{
					echo "You can now close the browser window. <a href=\"../wp-admin/admin.php?page=webonary\">Click here to view the import status</a><br>";
				}
				flush();

				$file = $this->get_latest_xhtml_file();
				if(isset($file))
				{
					$xhtmlFileURL = $file->url;
					$userid = $current_user->ID;
					include $webonary_include_path . DIRECTORY_SEPARATOR . 'run_import.php';
				}

				break;
			/*
			 * for indexing the search strings (configured dictionary)
			 */
			case 2 :
				?>
				<DIV ID="flushme">indexing...</DIV>
				<?php
				$this->index_searchstrings();

				$xhtml_file = $result['file'];

				$this->goodbye($xhtml_file, $css_file);

				$message = "The import of the vernacular (configured) xhtml export is completed.\n";
				$message .= "Go here to configure more settings: " . get_site_url() . "/wp-admin/admin.php?page=webonary";

				break;
		}
		$this->footer();
	}

	//-----------------------------------------------------------------------------//

	/**
	 * Greet the user.
	 */
	function hello(){
		echo '<div class="narrow">';
		echo '<p>' . __( 'Howdy! This importer allows you to import SIL FLEX XHTML data into your WordPress site.',
				'sil_dictionary' ) . '<br>';
		echo  __('Before re-importing, it\'s best to delete your existing entries. Go to <a href="../wp-admin/admin.php?page=webonary">Webonary</a>... \'Delete Data\' to do this.') . '</p>';
		?>
		<?php
	}

	//-----------------------------------------------------------------------------//

	/**
	 * Finish up.
	 */
	function goodbye($xhtml_file, $css_file){

		global $wpdb;

		echo '<div class="narrow">';

		if ( $_POST['filetype'] == 'configured')
		{
			if($this->verbose)
			{
				if($_GET['step'] == 1)
				{
					echo '<strong>Next step: </strong>';
					echo '<p>';
					echo '<form enctype="multipart/form-data" id="import-upload-form" method="post" action="' . esc_attr(wp_nonce_url("admin.php?import=pathway-xhtml&amp;step=2", 'import-upload')) . '">';
					echo '<input type="submit" class="button" name="btnIndex" value="Index Search Strings"/>';
					if(isset($_POST['chkConvertToLinks']))
					{
						echo '<input type="hidden" name="chkShowProgress" value=' . $_POST['chkShowProgress'] . '></input>';
						echo '<input type="hidden" name="chkConvertToLinks" value=' . $_POST['chkConvertToLinks'] .'></input>';
						echo '<input type="hidden" name="filetype" value="configured"></input>';
					}
					echo '</form>';
					echo '</p>';
				}
			}
			else
			{
				if(isset($xhtml_file))
				{
					$this->index_searchstrings();
				}
			}

		}


		flush();
		echo __( 'Finished!', 'sil_dictionary' );
	}
	//-----------------------------------------------------------------------------//


	function convert_fieldworks_audio_to_wordpress ($doc) {
		global $wpdb;

		$upload_dir = wp_upload_dir();

		// audio example:
		//<a class="audioButton" href="/files/audio/sprache.mp3"></a>
		//<span class="LexEntry-publishStemPara-Audio"><span lang="trc-Zxxx-x-audio" xml:space="preserve">634962856425589029a√É¬± doj.wav</span><span lang="en" xml:space="preserve"> </span></span>

		$audios = $this->dom_xpath->query('.//xhtml:span[contains(@class, "Audio")]|.//xhtml:span[contains(@class, "Audio")]|.//xhtml:span[contains(@class, "audio")]|//span[contains(@class, "audio")]', $doc);

		foreach ( $audios as $audio ) {

			if(strlen(trim($audio->textContent)) > 0)
			{
				$audiofiles = explode(";", $audio->textContent);
				$spanelement = $doc->createElement('span');
				foreach($audiofiles as $audiofile)
				{

					$filename = "/AudioVisual/" . str_replace("\\", "/", trim($audiofile));
					if (!file_exists($upload_dir['path'] . $filename))
					{
						echo "file " .  $upload_dir['baseurl'] . $filename . " doesn't exist<br>";
					}

					$newimage = $doc->createElement('img');
					$newimage->setAttribute("src", get_bloginfo('wpurl') . "/wp-content/plugins/sil-dictionary-webonary/audiolibs/img/blank.gif");

					$newelement = $doc->createElement('a');
					//$newelement->appendChild($this->dom->createTextNode(""));
					$newelement->appendChild($newimage);
					$newelement->setAttribute("class", "audioButton");
					$newelement->setAttribute("href",  $upload_dir['baseurl'] . $filename);

					$spanelement->appendChild($newelement);
				}
				$parent = $audio->parentNode;
				$parent->replaceChild($spanelement, $audio);
			}
		} // foreach ( $audios as $audio )

		$audioVisuals = $this->dom_xpath->query('//xhtml:source', $doc);

		foreach ( $audioVisuals as $audioVisual ) {

			$src = $audioVisual->getAttribute( "src" );

			$replaced_src = str_ireplace("AudioVisual\\", $upload_dir['baseurl'] . "/AudioVisual/", $src);

			$audioVisual->setAttribute("src", $replaced_src);
		}

		return $doc;
	}

	function convert_fieldworks_images_to_wordpress ($doc) {
		global $wpdb;

		// image example (with link):
		//<a href="javascript:openImage('mouse.png')"><img src="wp-content/uploads/images/thumbnail/mouse.png" /></a>

		$images = $this->dom_xpath->query('//xhtml:img', $doc);

		foreach ( $images as $image ) {

			$src = $image->getAttribute( "src" );

			$upload_dir = wp_upload_dir();
			$replaced_src = str_ireplace("pictures/", $upload_dir['baseurl'] . "/images/thumbnail/", $src);
			$replaced_src = str_ireplace("pictures\\", $upload_dir['baseurl'] . "/images/thumbnail/", $replaced_src);
			$pic = str_ireplace("pictures/", "", $src);
			$pic = str_ireplace("pictures\\", "", $pic);

			$newimage = $doc->createElement('img');
			$newimage->setAttribute("src", $replaced_src);

			$newelement = $doc->createElement('a');
			$newelement->appendChild($newimage);
			$newelement->setAttribute("class", "image");
			$newelement->setAttribute("href",  $upload_dir['baseurl'] . "/images/original/" . $pic);
			$parent = $image->parentNode;
			$parent->replaceChild($newelement, $image);

			//error_log("IMAGE: " . $replaced_src);

		} // foreach ( $images as $image )

		return $doc;
	} // function convert_fieldworks_images_to_wordpress()

	function convert_fieldworks_video_to_wordpress ($doc) {

		$upload_dir = wp_upload_dir();

		$audioVisuals = $this->dom_xpath->query('//xhtml:span[starts-with(@class, "mediafile")]/*[@class = "CmFile"]', $doc);

		foreach ( $audioVisuals as $audioVisual ) {

			$href = $audioVisual->getAttribute( "href" );

			$replaced_src = str_ireplace("AudioVisual\\", $upload_dir['baseurl'] . "/AudioVisual/", $href);

			$audioVisual->setAttribute("href", $replaced_src);
		}

		return $doc;
	}


	function convert_semantic_domains_to_links($post_id, $doc, $field, $termid) {
		global $wpdb;

		if(empty($field))
		{
			return false;
		}

		$newelement = $doc->createElement('span');

		$allNodes = "";

		foreach($field->childNodes as $node)
		{
			$allNodes .= $doc->saveXML($node);
		}
		$fragment = $doc->createDocumentFragment();
		$url = get_bloginfo('wpurl') . "/?s=&partialsearch=1&tax=" . $termid;
		$fragment->appendXML('<a href="' . htmlspecialchars($url) . '">' . $allNodes . '</a>');
		$newelement->appendChild($fragment);

		$newelement->setAttribute("class", $field->getAttribute("class"));

		$parent = $field->parentNode;
		$parent->replaceChild($newelement, $field);

		$entry_xml = $doc->saveXML( $doc );

		$sql = "UPDATE $wpdb->posts " .
			" SET post_content = '" . addslashes($entry_xml) . "'" .
			" WHERE ID = " . $post_id;

		$wpdb->query( $sql );

		return $entry_xml;
	}

	/**
	 * Footer for the screen
	 */
	function footer() {
		echo '</div>';
	}

	/**
	 * @return IXhtmlFileInfo
	 */
	function get_latest_xhtml_file(){
		global $wpdb;

		/** @noinspection SqlResolve */
		$sql = <<<SQL
SELECT ID, post_content AS url
FROM $wpdb->posts
WHERE post_content LIKE '%.xhtml' AND post_type LIKE 'attachment'
ORDER BY post_date DESC
LIMIT 0,1
SQL;
		$arrLastFile = $wpdb->get_results($sql);

		if(count($arrLastFile) > 0)
			return $arrLastFile[0];
		else
			return null;
	}

	/**
	 * Utility function return the post ID given a headword.
	 * @param string $headword = headword to find
	 * @return int = post ID
	 */
	function get_post_id( $flexid ){
		global $wpdb;

		$sql = "SELECT id
			FROM $wpdb->posts
			WHERE post_name = '" . trim($flexid) . "'	collate " . MYSQL_CHARSET . "_bin AND post_status = 'publish'";

		$row = $wpdb->get_row( $sql );

		if(!empty($row))
		{
			$post_id = $wpdb->get_var($sql);
		}
		else
		{
			//only exists as subentry
			//note that in this case it will find the postid to insert into the sil_search table,
			//but the guid doesn't exist as a post_name. If a link can't be found, it will be searched for in post_content
			//see dictionary-search.php -> my_404_override
			$sql = "SELECT id
			FROM $wpdb->posts
			WHERE post_content LIKE '%" . trim($flexid) . "%'	collate " . MYSQL_CHARSET . "_bin AND post_status = 'publish'";

			$post_id = $wpdb->get_var($sql);
		}

		return $post_id;
	}

	function get_post_id_bytitle( $headword, $langcode, &$subid, $isLangCode = false ){
		global $wpdb;

		// @todo: If $headword_text has a double quote in it, this
		// will probably fail.
		$sql = "SELECT ID
			FROM $wpdb->posts
			WHERE post_title = '" . addslashes(trim($headword)) . "' collate " . MYSQL_CHARSET . "_bin ";

		$row = $wpdb->get_row( $sql );

		$postid = 0;
		if(count($row) > 0)
		{
			//$subid = $row->subid;
			$subid = 0; //subid is no longer needed
			$postid = $row->ID;
		}

		return $postid;
	}

	function get_relevance($classname, $classnameLong)
	{
		$relevance = 0;

		if($classname == "mainheadword" || $classname == "lexemeform" || ($classname == "headword" && $classnameLong == "headword_"))
		{
			$relevance = $this->headword_relevance;
		}
		if($classname == "headword-sub" || $classname == "subentry_headword")
		{
			$relevance = $this->headword_relevance - 5;
		}
		/*
		if($classname == "LexemeForm" || $classname == "lexemeform")
		{
			$relevance = $this->lexeme_form_relevance;
		}
		*/
		if($classname == "definition" || $classname == "gloss" || $classname == "definitionorgloss")
		{
			$relevance = $this->definition_word_relevance;
		}
		if($classname == "definition-sub")
		{
			$relevance = $this->definition_word_relevance - 5;
		}
		if($classname == "example")
		{
			$relevance = $this->example_sentences_relevance;
		}
		if($classname == "translation")
		{
			$relevance = $this->example_sentences_relevance;
		}
		if($classname == "variantref-form")
		{
			$relevance = $this->variant_form_relevance;
		}
		if($classname == "lexsensereference")
		{
			$relevance = $this->sense_crossref_relevance;
		}
		if($classname == "scientificname")
		{
			$relevance = $this->scientific_name_relevance;
		}
		if($classname == "plural")
		{
			$relevance = $this->plural_relevance;
		}
		if($classname == "citationform")
		{
			$relevance = $this->citationform_relevance;
		}

		return $relevance;
	}
	function get_user_input() {

		$bytes = apply_filters( 'import_upload_size_limit', wp_max_upload_size() );
		//multisite cannot handle wp_max_upload_size
		if ( is_multisite() )
		{
			$size = "50MB";
		}
		else
		{
			$size = size_format( $bytes );
		}

		$upload_dir = wp_upload_dir();
		if ( ! empty( $upload_dir['error'] ) ) :
			?><div class="error"><p><?php _e('Before you can upload your import file, you will need to fix the following error:'); ?></p>
			<p><strong><?php echo $upload_dir['error']; ?></strong></p></div><?php
		else :
			?>
			<script type="text/javascript">
                function toggleConfigured() {
                    document.getElementById("uploadCSS").style.visibility = 'visible';
                    document.getElementById("langCode").style.display = "hidden";
                    //document.getElementById("convertToLinks").style.visibility = 'visible';
                }
                function toggleReversal() {
                    //document.getElementById("convertToLinks").style.visibility = 'hidden';
                    document.getElementById("langCode").style.display = "block";
                }
			</script>
			<form enctype="multipart/form-data" id="import-upload-form" method="post" action="<?php echo esc_attr(
				wp_nonce_url("admin.php?import=pathway-xhtml&amp;step=1", 'import-upload')); ?>">
				<p>
					<label for="upload"><?php _e( 'Choose an XHTML file from your computer:' ); ?> (<?php printf( __('Maximum size: %s' ), $size ); ?>)
						<br>
						<?php _e('**XHTML file must be sorted. Webonary does not sort the entries.**'); ?>
					</label>
				</p>
				<p>
					<input type="file" id="upload" name="xhtml" size="100" />
				</p>
				<div id="uploadCSS">
					<p>
						<label for="upload"><?php _e( 'Choose a CSS file from your computer (optional):' ); ?>
							(<?php printf( __('Maximum size: %s' ), $size ); ?>)</label>
					</p>
					<p>
						<input type="file" id="upload" name="css" size="100" />
					</p>
				</div>
				<?php
				$arrLanguageCodes = Webonary_Configuration::get_LanguageCodes();
				if(count($arrLanguageCodes) > 1)
				{
					?>
					<div id=langCode style="display:none;">
						<p>
							<?php _e("Language Code:"); ?>
							<select id=reversalLanguagecode name="languagecode">
								<option value=""></option>
								<?php
								foreach($arrLanguageCodes as $languagecode) {
									if(strlen(trim($languagecode['language_code'])) > 0)
									{
										?>
										<option value="<?php echo $languagecode['language_code']; ?>"><?php echo $languagecode['language_code']; ?></option>
										<?php
									}
								} ?>
							</select>
						</p>
					</div>
					<?php
				}
				?>
				<p>
					<input type="radio" name="filetype" value="configured" onChange="toggleConfigured();" CHECKED/> <?php esc_attr_e('Configured Dictionary'); ?><BR>
					<input type="radio" name="filetype" value="reversal" onChange="toggleReversal();" /> <?php esc_attr_e('Reversal Index'); ?><BR>
					<input type="radio" name="filetype" value="stem" onChange="toggleReversal();" /> *<?php esc_attr_e('Sort Order'); ?> <a href="https://webonary.org/data-transfer/#sortorder" target="_blank">only if sort order is different than configured view</a><BR>
				</p>
				<p>
					<input type="hidden" name="chkConvertToLinks" value="1">
					<?php /*?>
			<input type="checkbox" name="chkShowProgress"> <?php echo esc_attr_e('Check to show import progress in browser (slower). Keep unchecked to run import in the background.'); ?>
			*/
					?>
				<p>
					<input type="submit" class="button" value="<?php esc_attr_e( 'Upload files and import' ); ?>" />
				</p>
			</form>
		<?php
		endif;
	}

	/*
	 * This function checks if a character consists of composed characters.
	 * The search is handled differently if a dictionary has composed characters
	 * Since there is no way to programmaticaly know if a character is a composed character, we check for length of string and
	 * if the character is not a composed character, but a special character
	 * This method is not fool-proof, but it's better to have the majority of dictionaries use the normal search without REGEX if possible
	 */
	function hasComposedCharacters() {

		global $wpdb;

		$UTF8_ACCENTS = array(
			'à' => 'a', 'ȁ' => 'a', 'ấ' => 'a', 'ầ' => 'a', 'ẩ' => 'a', 'ǟ' => 'a', 'ǻ' => 'a', 'ặ' => 'ặ',
			'ȃ' => 'a', 'ǎ' => 'a', 'ȧ' => 'a', 'ǡ' => 'a', 'ḁ' => 'a', 'ạ' => 'a', 'ả' => 'a',
			'ô' => 'o', 'ď' => 'd', 'ḟ' => 'f', 'ë' => 'e', 'š' => 's', 'ơ' => 'o', 'ẚ' => 'a',
			'ß' => 'ss', 'ă' => 'a', 'ř' => 'r', 'ț' => 't', 'ň' => 'n', 'ā' => 'a', 'ķ' => 'k',
			'ŝ' => 's', 'ỳ' => 'y', 'ņ' => 'n', 'ŋ' => 'n', 'ĺ' => 'l', 'ħ' => 'h', 'ṗ' => 'p',
			'ó' => 'o', 'ȯ' => 'o', 'ɔ' => 'o', 'ȅ' => 'e', 'ẹ' => 'e', 'ẽ' => 'e', 'ȇ' => 'e', 'ȩ' => 'e',
			'ḕ' => 'e', 'ḗ' => 'e', 'ḙ' => 'e', 'ḛ' => 'e', 'ḝ' => 'e', 'ı' => 'i', 'ǐ' => 'i', 'ĭ' => 'i',
			'ɨ' => 'i', 'ṓ' => 'o', 'ǒ' => 'o', 'ǫ' => 'o', 'ȱ' => 'o', 'ȱ' => 'o', 'ṏ' => 'o',
			'ú' => 'u', 'ě' => 'e', 'é' => 'e', 'ç' => 'c', 'ẁ' => 'w', 'ċ' => 'c', 'õ' => 'o',
			'ǔ' => 'u', 'ȕ' => 'u', 'ṳ' => 'u', 'ṵ' => 'u', 'ṷ' => 'u', 'ṹ' => 'u', 'ṻ' => 'u',
			'ṡ' => 's', 'ø' => 'o', 'ģ' => 'g', 'ŧ' => 't', 'ș' => 's', 'ė' => 'e', 'ĉ' => 'c',
			'ś' => 's', 'î' => 'i', 'ű' => 'u', 'ć' => 'c', 'ę' => 'e', 'ŵ' => 'w', 'ṫ' => 't',
			'ū' => 'u', 'č' => 'c', 'ö' => 'oe', 'è' => 'e', 'ŷ' => 'y', 'ą' => 'a', 'ł' => 'l',
			'ų' => 'u', 'ů' => 'u', 'ş' => 's', 'ğ' => 'g', 'ļ' => 'l', 'ƒ' => 'f', 'ž' => 'z',
			'ẃ' => 'w', 'ḃ' => 'b', 'ɓ' => 'b', 'å' => 'a', 'ì' => 'i', 'ï' => 'i', 'ḋ' => 'd', 'ť' => 't',
			'ŗ' => 'r', 'ä' => 'ae', 'í' => 'i', 'ŕ' => 'r', 'ê' => 'e', 'ü' => 'ue', 'ò' => 'o', 'ꝍ' => 'o',
			'ē' => 'e', 'ñ' => 'n', 'ń' => 'n', 'ĥ' => 'h', 'ĝ' => 'g', 'đ' => 'd', 'ĵ' => 'j',
			'ÿ' => 'y', 'ũ' => 'u', 'ŭ' => 'u', 'ư' => 'u', 'ţ' => 't', 'ý' => 'y', 'ő' => 'o', 'ʉ' => 'u',
			'â' => 'a', 'ľ' => 'l', 'ẅ' => 'w', 'ż' => 'z', 'ī' => 'i', 'ã' => 'a', 'ġ' => 'g',
			'ṁ' => 'm', 'ō' => 'o', 'ĩ' => 'i', 'ù' => 'u', 'į' => 'i', 'ź' => 'z', 'á' => 'a',
			'û' => 'u', 'þ' => 'th', 'ð' => 'dh', 'æ' => 'ae', 'µ' => 'u', 'ĕ' => 'e', 'ɛ' => 'e',
			'έ' => 'e', 'ἐ' => 'e', 'ἒ' => 'e', 'ἑ' => 'e', 'ἕ'  => 'e', 'ἓ' => 'e', 'ὲ' => 'e', 'ε' => 'e',
			'À' => 'A', 'Ô' => 'O', 'Ď' => 'D', 'Ḟ' => 'F', 'Ë' => 'E', 'Š' => 'S', 'Ơ' => 'O',
			'Ă' => 'A', 'Ř' => 'R', 'Ț' => 'T', 'Ň' => 'N', 'Ā' => 'A', 'Ķ' => 'K', 'ə' => 'e',
			'Ŝ' => 'S', 'Ỳ' => 'Y', 'Ņ' => 'N', 'Ĺ' => 'L', 'Ħ' => 'H', 'Ṗ' => 'P', 'Ó' => 'O',
			'Ú' => 'U', 'Ě' => 'E', 'É' => 'E', 'Ç' => 'C', 'Ẁ' => 'W', 'Ċ' => 'C', 'Õ' => 'O',
			'Ṡ' => 'S', 'Ø' => 'O', 'Ģ' => 'G', 'Ŧ' => 'T', 'Ș' => 'S', 'Ė' => 'E', 'Ĉ' => 'C',
			'Ś' => 'S', 'Î' => 'I', 'Ű' => 'U', 'Ć' => 'C', 'Ę' => 'E', 'Ŵ' => 'W', 'Ṫ' => 'T',
			'Ū' => 'U', 'Č' => 'C', 'Ö' => 'Oe', 'È' => 'E', 'Ŷ' => 'Y', 'Ą' => 'A', 'Ł' => 'L',
			'Ų' => 'U', 'Ů' => 'U', 'Ş' => 'S', 'Ğ' => 'G', 'Ļ' => 'L', 'Ƒ' => 'F', 'Ž' => 'Z',
			'Ẃ' => 'W', 'Ḃ' => 'B', 'Å' => 'A', 'Ì' => 'I', 'Ï' => 'I', 'Ḋ' => 'D', 'Ť' => 'T',
			'Ŗ' => 'R', 'Ä' => 'Ae', 'Í' => 'I', 'Ŕ' => 'R', 'Ê' => 'E', 'Ü' => 'Ue', 'Ò' => 'O',
			'Ē' => 'E', 'Ñ' => 'N', 'Ń' => 'N', 'Ŋ' => 'N', 'Ĥ' => 'H', 'Ĝ' => 'G', 'Đ' => 'D', 'Ĵ' => 'J',
			'Ÿ' => 'Y', 'Ũ' => 'U', 'Ŭ' => 'U', 'Ư' => 'U', 'Ţ' => 'T', 'Ý' => 'Y', 'Ő' => 'O',
			'Â' => 'A', 'Ľ' => 'L', 'Ẅ' => 'W', 'Ż' => 'Z', 'Ī' => 'I', 'Ã' => 'A', 'Ġ' => 'G',
			'Ṁ' => 'M', 'Ō' => 'O', 'Ĩ' => 'I', 'Ù' => 'U', 'Į' => 'I', 'Ź' => 'Z', 'Á' => 'A',
			'Û' => 'U', 'Þ' => 'Th', 'Ð' => 'Dh', 'Æ' => 'Ae', 'Ĕ' => 'E', '…' => '...', '’' => '\'',
			'–' => '-', '“' => '"', 'ʼ' => '\'', 'ʾ' => '\'', '°' => '', '₁' => '1', '₂' => '2', '₃' => '3',
			'₄' => '4', '₅' => '5', 'ứ' => 'u', 'ắ' => 'a', 'ố' => 'o', 'ớ' => 'o', 'ệ' => 'e', 'ế' => 'e',
			'ỏ' => 'o', 'ữ' => 'u', 'ọ' => 'o', 'ị' => 'i', 'ủ' => 'u', 'ổ' => 'o', 'ề' => 'e', 'ỉ' => 'i',
			'ử' => 'u', 'ể' => 'e', 'ợ' => 'o', 'ộ' => 'o', 'ậ' => 'a', 'ụ' => 'u', 'ừ' => 'u', 'ồ' => 'o',
			'ỗ' => 'o', 'ặ' => 'a', 'ẻ' => 'e', 'ờ' => 'o', 'ở' => 'o', 'ằ' => 'a', 'ễ' => 'e', 'ự' => 'u',
			'ỡ' => 'o', 'ỷ' => 'y', 'ẫ' => 'a', 'ỡ' => 'o', '”' => '"', 'ẳ' => 'a', '‘' => '\'', 'ɩ' => 'i',
			'ʋ' => 'u', ';' => '', 'Ɛ' => 'e', 'Ɔ' => 'o', 'Ʋ' => 'u', 'œ' => 'o', '¢' => 'c', '·' => '.',
			'ɣ' => 'y', 'ʒ' => 'z', 'Ʒ' => 'z', '•' => '.', '¼' => '4', '²' => '2', '³' => '3', 'ˈ' => '\'',
			'‑' => '-', 'ꞌ' => '\'', 'ᵐ' => 'm', 'ᵑ' => 'n', 'ⁿ' => 'n', 'ʸ' => 'y', 'ʷ' => 'w', 'ɮ' => 'b',
			'ɬ' => 'c', 'ʃ' => 'f', 'ɗ' => 'd', 'ƴ' => 'y', 'ʔ' => '?', 'ʁ' => 'b', 'Ɨ' => 'I',
			'«' => '<', '»' => '>', 'Ɗ' => 'D', 'Ɓ' => 'B', 'Ƴ' => 'Y', 'ǹ' => 'n', ' ' => '-', 'ː' => ':',
			'O' => 'O', 'ɲ' => 'n', 'ɾ' => 'r', 'ɡ' => 'g', 'ɖ' => 'd', '̀' => '\'', '†' => "t", "ˋ" => "\'",
			'◦' => 'o', 'ʰ' => 'h', '¡' => '!', 'χ' => 'x', 'ɦ' => 'ɦ', '' => '?', 'Ɖ' => 'D', 'ǝ' => 'e',
			'Ɩ' => 'l', '' => '?', '&' => 'a', 'ẽ' => 'e', 'Ǔ' => 'U', 'ɥ' => 'u', '½' => '1', '⅓' => '1',
			'‰' => '%', 'ẽ' => 'e', 'ʜ' => 'H', 'ḭ' => 'i', '¿' => '?', '◊' => 'o'
		);


		$hasComposedCharacters = 0;

		$sql = "SELECT * FROM " . Webonary_Configuration::$search_table_name;

		$arrIndex = $wpdb->get_results($sql);

		$countComposedCharacters = 0;
		$notRoman = 0;
		foreach($arrIndex as $index)
		{
			$search_string = $index->search_strings;
			$search_string = preg_replace('/\s+/', '', $search_string);
			$search_string2 = normalizer_normalize($search_string, Normalizer::NFC );
			if(mb_strlen(trim($search_string), "utf-8") != mb_strlen(trim($search_string2), "auto") && preg_match('/([aeiou])/', $search_string)
				&& strtr($search_string, $UTF8_ACCENTS) == $search_string)
			{
				if(mb_strlen(trim($search_string)) <= 10 && mb_strlen(trim($search_string2), "auto") - mb_strlen(trim($search_string)) >= 5)
				{
					$notRoman++;
				}
				$countComposedCharacters++;
			}
		}

		if($countComposedCharacters > 5 && $notRoman < 10)
		{
			$hasComposedCharacters = 1;
		}

		return $hasComposedCharacters;
	}
	/**
	 * Header for the screen
	 */
	function header() {
		echo '<div class="wrap">';
		echo '<h2>' . __( 'Import SIL FLEX XHTML', 'sil_dictionary' ) . '</h2>';
	}

	/**
	 * Import the writing systems (languages)
	 * @global <type> $wpdb
	 */

	/**
	 * Import entries for the Configured Dictionary.
	 * @global <type> $wpdb
	 */
	function import_xhtml_entries ($postentry, $entry_counter, $menu_order, $isNewFLExExport = true, $browseletter = "") {
		global $wpdb;

		$doc = new DomDocument();
		$doc->preserveWhiteSpace = false;
		$doc->loadXML($postentry);

		$this->dom_xpath = new DOMXPath($doc);
		$this->dom_xpath->registerNamespace('xhtml', 'http://www.w3.org/1999/xhtml');

		if($entry_counter == 1)
		{
			//  Make sure we're not working on a reversal file.
			$reversals = $this->dom_xpath->query( '(./xhtml:span[contains(@class, "reversal-form")])[1]|(./xhtml:span[contains(@class, "reversalform")])[1]' );
			if ( $reversals->length > 0 )
				return;
		}

		$doc = $this->convert_fieldworks_images_to_wordpress($doc);
		$doc = $this->convert_fieldworks_audio_to_wordpress($doc);
		$doc = $this->convert_fieldworks_video_to_wordpress($doc);

		// Find the headword. Should be only 1 headword at most. The
		// $headword->textContent picks up the value of both the headword and
		// the homograph number. This is presumably because the XML DOM
		// textContent property "returns the value of all text nodes
		// within the element node." The XHTML for an entry with homograph
		// number looks like this:
		// <span class="headword" lang="ii">my headword<span class="xhomographnumber">1</span></span>

		if($isNewFLExExport)
		{
			$headwords = $this->dom_xpath->query( './xhtml:span[@class="mainheadword"]|./xhtml:span[@class="lexemeform"]|./xhtml:span[@class="headword"]');
		}
		else
		{
			$headwords = $this->dom_xpath->query( './xhtml:span[@class="headword"]|./xhtml:span[@class="headword_L2"]|./xhtml:span[@class="headword-minor"]|./*[@class="headword-sub"]');
		}

		//$headword = $headwords->item( 0 )->nodeValue;
		$h = 0;
		$headword = $headwords->item(0);
		//foreach ( $headwords as $headword ) {

		if($entry_counter == 1)
		{

			if(isset($headword))
			{
				$headword_language = $headword->getAttribute( "lang" );
				if(strlen(trim($headword_language)) == 0)
				{
					$headword_language = $headword->childNodes->item(0)->getAttribute( "lang" );

					//if span with language attribute is inside a link
					if(strlen(trim($headword_language)) == 0)
					{
						$headword_language = $headword->childNodes->item(0)->childNodes->item(0)->getAttribute( "lang" );
					}
				}

				update_option("languagecode", $headword_language);

				//we no longer make the user change the normalization. All text is now normalized on import to NFC
				update_option("normalization", null);
			}
		}

		if($isNewFLExExport)
		{
			//$entry = $this->dom_xpath->query('//xhtml:span[@class="mainheadword"]/..|//xhtml:span[@class="lexemeform"]/..|//xhtml:span[@class="headword"]/..', $doc)->item(0);
			$entry = $this->dom_xpath->query('//xhtml:div[@class="entry"]|//xhtml:div[@class="mainentrycomplex"]|//xhtml:div[@class="minorentryvariant"]|//xhtml:div[@class="minorentrycomplex"]', $doc)->item(0);
		}
		else
		{
			$entry = $this->dom_xpath->query('//xhtml:span[@class="headword"]/..|//xhtml:span[@class="headword_L2"]/..|//xhtml:span[@class="headword-minor"]/..|//xhtml:div[@class="minorentries"]/span[@class="headword-minor"]/..|//xhtml:span[@class="headword-sub"]/..', $doc)->item(0);
		}

		//$entry = $this->dom_xpath->query('//div', $doc)->item(0);

		$headword_text = "?";
		if(isset($headword))
		{
			$headword_text = normalizer_normalize($headword->textContent, Normalizer::NFC );
		}

		$flexid = $entry->getAttribute("id");

		if(strlen(trim($flexid)) == 0)
		{
			$flexid = $headword_text;
		}

		$entry->removeAttributeNS("http://www.w3.org/1999/xhtml", "");

		$entry_xml = $doc->saveXML($entry, LIBXML_NOEMPTYTAG);

		//this replaces a link like this: <a href="#gcec78a67-91e9-4e72-82d3-4be7b316b268">
		//to this: <a href="/gcec78a67-91e9-4e72-82d3-4be7b316b268">
		//but it will keep a link like this href="#gcec78a67-91e9-4e72-82d3-4be7b316b268" (important for playing audio)

		//first make sure audio href only contains a hastag (or any href with onclick after it)
		$entry_xml = preg_replace('/href="(#)([^"]+)" onclick/', 'href="#$2" onclick', $entry_xml);

		//closing tag for <a .play()"/>, needs to have an empty space between > </a>
		$entry_xml = str_replace('></a>', '> </a>', $entry_xml);

		//make all links that are not using onclick (e.g. have format "#">) use the url path
		$entry_xml = preg_replace('/href="(#)([^"]+)">/', 'href="' . get_bloginfo('wpurl') . '/\\2">', $entry_xml);

		$entry_xml = addslashes($entry_xml);
		$entry_xml = stripslashes($entry_xml);
		$entry_xml = normalizer_normalize($entry_xml, Normalizer::NFC );
		//$entry_xml = str_replace("'","&#39;",$entry_xml);

		$browseletter = normalizer_normalize($browseletter, Normalizer::NFC );

		$post_parent = 0;

		/*
		 * Insert the new entry into wp_posts
		 */

		//$post_id = $this->get_post_id( $flexid );
		//$post_id = $this->get_post_id_bytitle( $headword_text, $headword_language, $subid, true);
		$post_id = $wpdb->get_var("SELECT ID FROM " . $wpdb->posts . " WHERE post_name = '" . $flexid . "'");

		//$post_id = wp_insert_post( $post );

		if($post_id == NULL)
		{
			$sql = $wpdb->prepare(
				"INSERT INTO ". $wpdb->posts . " (post_date, post_title, post_content, post_status, post_parent, post_name, comment_status, menu_order, post_content_filtered)
				VALUES (NOW(), '%s', '%s', 'publish', %d, '%s', '%s', %d, '%s')",
				trim($headword_text), $entry_xml, $post_parent, $flexid, get_option('default_comment_status'), $menu_order, $browseletter );

			$wpdb->query( $sql );

			$post_id = $wpdb->insert_id;
			if($post_id == 0)
			{
				$post_id = $wpdb->get_var("SELECT ID FROM " . $wpdb->posts . " WHERE post_title = '" . addslashes(trim($headword_text)) . "'");
			}

			wp_set_object_terms( $post_id, "webonary", 'category' );
		}
		else
		{
			$sql = $wpdb->prepare(
				"UPDATE " . $wpdb->posts . " SET post_date = NOW(), post_title = '%s', post_content = '%s', post_status = 'publish', pinged='', post_parent=%d, post_name='%s', comment_status='%s' WHERE ID = %d",
				trim($headword_text), $entry_xml, $post_parent, $flexid, get_option('default_comment_status'), $post_id);

			$wpdb->query( $sql );
		}
		/*
			echo "<hr style=\"border-color:red;\">";
			print_r($wpdb->queries);
			$wpdb->queries = null;
			*/
		/*
		 * Show progresss to the user.
		 */
		$this->import_xhtml_show_progress( $entry_counter, null, $headword_text, "Step 1 of 2: Importing Post Entries" );
		$h++;
		//} // foreach ( $headwords as $headword )

		if($entry_counter % 50 == 0)
		{
			////sleep(1);
		}

		if($h > 0)
		{
			$entry_counter++;
		}

		return $entry_counter;
	}

	/**
	 * Show progress to the user
	 * @param int $entry_counter = current entry number
	 * @param int $entries_count = total number of entries
	 * @param string $headword_text = text of the headword
	 * @param string $msg
	 */
	function import_xhtml_show_progress( $entry_counter, $entries_count, $headword_text, $msg = '' ) {

		if($this->verbose)
		{
			flush();
			if($entry_counter == 1 && $this->api == true)
			{
				echo $msg . "\n";
			}

			//only display every 25 entries or if last entry
			if($entry_counter % 25 == 0 || $entry_counter == $entries_count)
			{
				if($this->api)
				{
					echo $entry_counter . " ";
					if(isset($entries_count))
					{
						echo "of " . $entries_count . " entries: ";
					}
					echo $headword_text . "\n";
				}
				else
				{
					?>
					<SCRIPT type="text/javascript">//<![CDATA[
                        d = document.getElementById("flushme");
                        info = "<strong><?php echo $msg; ?></strong><br>";
						<?php
						if($entries_count >= 1)
						{
						?>
                        info += "<?php echo $entry_counter; ?>";
						<?php
						if(isset($entries_count))
						{
						?>
                        info += " of <?php echo $entries_count; ?> entries:";
						<?php
						}
						?>
                        info += "<?php echo ' ' . $headword_text; ?>";
						<?php
						}
						?>
                        //info += "<br>";
                        //info += "Memory Usage: <?php echo memory_get_usage() . " bytes"; ?>";

                        d.innerHTML = info;
                        //]]></SCRIPT>
					<?php
				}
			}
		}
	}

	function import_xhtml_classes($postid, $doc)
	{
		$xpath = new DOMXPath($doc);
		$xpath->registerNamespace('xhtml', 'http://www.w3.org/1999/xhtml');

		$fields = $xpath->query("//span[@class]");

		$classnameLong = "";
		$searchstring = "";
		$lang = "";

		$arrResults = [];
		$i = 0;
		foreach($fields as $field)
		{
			$langContent = $xpath->query("span[@lang]", $field);

			$classname = $field->getAttribute("class");
			$classnameLong .= $classname . "_";

			$relevance = $this->get_relevance($classname, $classnameLong);

			foreach($langContent as $content)
			{
				$searchstring = $content->textContent;
				$lang = $content->getAttribute("lang");
				$arrResult[$classname][$lang][$i] = $searchstring;
				$this->import_xhtml_search_string($postid, $searchstring, $relevance, $lang, $classname);
			}

			$i++;
		}

		return $arrResult;
	}

	//-----------------------------------------------------------------------------//

	/**
	 * Utility function to store off the search string
	 * @param <type> $table = table holding the search strings
	 * @param <type> $post_id = ID of post in wp_posts
	 * @param <type> $language_code = Should be ISO 639-3, but can be longer
	 * @param <type> $search_string = string we want to search for in the post
	 * @param <int> $relevance = weighted importance of this particular string for search results
	 */
	function import_xhtml_search_string( $post_id, $search_string, $relevance, $language_code, $classname, $subid = 0) {
		global $wpdb;

		// $wdbt->prepare likes to add single quotes around string replacements,
		// and that's why I concatenated the table name.
		if(strlen(trim($search_string)) > 0)
		{
			$search_string = normalizer_normalize($search_string, Normalizer::NFC );
			$sql = $wpdb->prepare(
				"INSERT IGNORE INTO `". Webonary_Configuration::$search_table_name . "` (post_id, language_code, search_strings, relevance, class, subid)
				VALUES (%d, '%s', '%s', %d, '%s', %d)",
				$post_id, $language_code, trim($search_string), $relevance, $classname, $subid );

			//ON DUPLICATE KEY UPDATE search_strings = CONCAT(search_strings, ' ',  '%s');",

			$wpdb->query( $sql );
		}

		//this replaces the special apostroph with the standard apostroph
		//the first time round the special apostroph is inserted, so that both searches are valid
		if(strstr($search_string,"’"))
		{
			$mySearch_string = str_replace("’", "'", $search_string);
			$this->import_xhtml_search_string( $post_id, $mySearch_string, $relevance, $language_code, $classname, $subid);
		}
	}

	/**
	 * Import the part(s) of speech (POS) for an entry.
	 * @param <type> $entry = XHTML of the dictionary entry
	 * @param <type> $post_id = ID of the WordPress post.
	 */

	// Currently we aren't deleting any existing POS terms. More than one post may
	// refer to a domain. For the moment, any bad POSs must be removed by hand.

	function import_xhtml_part_of_speech( $doc, $post_id ){

		$xpath = new DOMXPath($doc);

		//only index pos under the main entry, not subentries
		$pos_terms = $xpath->query('//div/span[@class = "senses"]//span[contains(@class, "partofspeech")]');

		$i = 0;
		//$parent_term_id = 0;
		foreach ( $pos_terms as $pos_term ){
			$pos_name = (strlen($pos_term->textContent) > 30) ? substr($pos_term->textContent, 0, 30) . '...' : $pos_term->textContent;
			$pos_name = trim(str_replace(".", "", $pos_name));

			wp_insert_term(
				$pos_name,
				Webonary_Configuration::$pos_taxonomy,
				array(
					'description' => $pos_name,
					'slug' => $pos_name
				)
			);

			wp_set_object_terms( $post_id, $pos_name, Webonary_Configuration::$pos_taxonomy, true);
		}
	}

	//-----------------------------------------------------------------------------//

	/**
	 * Import the semantic domain(s) for the entry.
	 * @param <type> $entry = XHTML of the dictionary entry
	 * @param <type> $post_id = ID of the WordPress post.
	 */

	// Currently we aren't deleting any existing semantic domains. More than one post may
	// refer to a domain. For the moment, any bad domains must be removed by hand.

	function import_xhtml_semantic_domain( $doc, $post_id, $subentry, $convertToLinks){

		global $wpdb;

		$xpath = new DOMXPath($doc);
		$xpath->registerNamespace('xhtml', 'http://www.w3.org/1999/xhtml');

		$semantic_domains = $xpath->query('//span[starts-with(@class, "semantic-domains")]|//span[starts-with(@class, "semanticdomains")]');

		$i = 0;

		$sd_numbers = null;

		foreach ( $semantic_domains as $semantic_domain ){

			$sd_names = $xpath->query('//span[starts-with(@class, "semantic-domains")]//*[starts-with(@class, "semantic-domain-name")]|//span[@class = "semanticdomains"]//span[starts-with(@class, "name")]/span[not(@class = "writingsystemprefix")]', $semantic_domain);
			$sd_numbers = $xpath->query('//span[starts-with(@class, "semantic-domains")]//span[starts-with(@class, "semantic-domain-abbr")]|//span[@class = "semanticdomains"]//span[starts-with(@class, "abbreviation")]/span[not(@class = "writingsystemprefix")]', $semantic_domain);
			///span[not(@class = "Writing_System_Abbreviation")]
			$sc = 0;
			foreach($sd_numbers as $sd_number)
			{
				$semantic_domain_language = $sd_number->getAttribute("lang");

				$sd_number_text = str_replace("[", "", $sd_number->textContent);
				$sd_number_text = str_replace("(", "", $sd_number_text);
				$sd_number_text = trim(str_replace("-", "", $sd_number_text));

				$domain_name = $sd_number_text;
				if(isset($sd_names->item($sc)->textContent))
				{
					$domain_name = $sd_names->item($sc)->textContent;
					$domain_name = str_replace("]", "", $domain_name);
					$domain_name = str_replace(")", "", $domain_name);

				}

				$arrTerm = wp_insert_term(
					$domain_name,
					Webonary_Configuration::$semantic_domains_taxonomy,
					array(
						'description' => trim($domain_name),
						'slug' => $sd_number_text
					));

				$termid = $wpdb->get_var("
						SELECT term_id
						FROM $wpdb->terms
						WHERE slug = '" . str_replace(".", "-", $sd_number_text) . "'");

				if($termid == NULL || $termid == 0)
				{
					if (array_key_exists('term_id', $arrTerm))
					{
						$termid = $arrTerm['term_id'];
						$taxonomyid = $arrTerm['term_taxonomy_id'];
						$terms[$i] = $termid;
						$i++;
					}
				}

				$this->convert_semantic_domains_to_links($post_id, $doc, $sd_number, $termid);
				$this->convert_semantic_domains_to_links($post_id, $doc, $sd_names->item($sc), $termid);

				if(isset($termid))
				{
					wp_set_object_terms( $post_id, $domain_name, Webonary_Configuration::$semantic_domains_taxonomy, true);
				}

				$arrTerm = null;

				$sc++;
			}
		}

		if(isset($sd_numbers))
		{
			if($sd_numbers->length > 0)
			{
				update_option("useSemDomainNumbers", 1);
			}
		}

		$sql = $wpdb->query("UPDATE $wpdb->term_taxonomy SET COUNT = 1 WHERE taxonomy = 'sil_semantic_domains'");

	}

	//-----------------------------------------------------------------------------//

	/**
	 * Import reversal indexes from a reversal index XHTML file. This will
	 * not add any new lexical entries, but it will make entries in the search
	 * table.
	 */
	function import_xhtml_reversal_indexes ($postentry = null, $entry_counter = null, $browseletter = "") {
		global $wpdb;

		$doc = new DomDocument();
		$doc->preserveWhiteSpace = false;
		$doc->loadXML($postentry);
		$this->dom_xpath = new DOMXPath($doc);
		$this->dom_xpath->registerNamespace('xhtml', 'http://www.w3.org/1999/xhtml');

		//$entry = $this->dom_xpath->query('//div', $doc)->item(0);

		// Should be only 1 reversal at most per entry.
		//$reversals = $this->dom_xpath->query( './xhtml:span[contains(@class, "reversal-form")]', $entry );
		$reversals = $this->dom_xpath->query( './xhtml:span[contains(@class, "reversal-form")]|./xhtml:span[contains(@class, "reversalform")]');
		$entries_count = null;
		$reversal_xml = "";
		$browseletter = normalizer_normalize($browseletter, Normalizer::NFC );

		if($reversals->length > 0)
		{
			$reversal_head = trim($reversals->item(0)->textContent);

			$reversal_language = $reversals->item(0)->getAttribute( "lang" );
			if(strlen(trim($reversal_language)) == 0)
			{
				$reversal_language = $reversals->item(0)->childNodes->item(0)->getAttribute( "lang" );
			}

			/*
			 * Show progresss to the user.
			 */
			$this->import_xhtml_show_progress( $entry_counter, null, $reversal_head, "", "Step 1 of 2: Importing reversal entries" );

			if($entry_counter == 1)
			{
				//automatically sets the language code for the reversal on import.
				//if reversal1 already exists, it sets reversal 2
				if(strlen(get_option('reversal1_langcode')) > 0 && get_option('reversal1_langcode') != $reversal_language)
				{
					if(strlen(get_option('reversal2_langcode')) == 0)
					{
						update_option("reversal2_langcode", $reversal_language);
					}

					if(strlen(get_option('reversal2_langcode')) > 0 && get_option('reversal2_langcode') != $reversal_language)
					{
						update_option("reversal3_langcode", $reversal_language);
					}
				}
				else
				{
					update_option("reversal1_langcode", $reversal_language);
				}
			}

			//$headwords = $this->dom_xpath->query('./xhtml:span[@class = "senses"]/xhtml:span[@class = "sense"]/xhtml:span[@class = "headword"]|./xhtml:span[@class = "senses"]/xhtml:span[starts-with(@class, "headref")]', $entry );
			$headwords = $this->dom_xpath->query('//xhtml:*[@class = "referringsense" or @class="sensesr"]/*[@class = "headword" or @class = "lexemeform"]|//xhtml:span[starts-with(@class, "headref")]');

			if($headwords->length == 0)
			{
				echo "No senses found for '" . $reversal_head . "'<br>\n";
			}

			if(strpos($postentry, "reversalindexentry") > 0)
			{
				$reversal_xml = preg_replace('/href="(#)([^"]+)"/', 'href="' . get_bloginfo('wpurl') . '/\\2"', $postentry);
			}
			$sql = "SELECT id FROM " . Webonary_Configuration::$reversal_table_name . " ORDER BY id+0 DESC LIMIT 0,1 ";

			$lastid = $wpdb->get_var($sql);

			$headwordCount = 1;
			foreach ( $headwords as $headword )
			{

				//the Sense-Reference-Number doesn't exist in search_strings field, so in order for it not to be searched, it has to be removed
				$sensereferences = $this->dom_xpath->query('//xhtml:span[@class="Sense-Reference-Number"]', $headword);
				foreach($sensereferences as $sensereference)
				{
					$sensereference->parentNode->removeChild($sensereference);
				}

				$headword_text = trim($headword->textContent);

				$this->import_xhtml_show_progress( $entry_counter, $entries_count, $reversal_head . " (" . $headword_text . ")",  "Step 2 of 2: Indexing reversal entries"  );

				$post_name = "";

				//echo $doc->saveXML($headword, LIBXML_NOEMPTYTAG) . "<br>";
				//for reversal exports from FLEx 8.3. onwards, the headwords are linked
				//we check that it's a 8.3 export by searching for reversalindexentry as previously the class "entry" was used instead
				if(is_numeric($lastid))
				{
					$id = $lastid + $entry_counter;
				}
				if(strpos($postentry, "reversalindexentry") > 0)
				{
					$entry = $this->dom_xpath->query('//xhtml:span[@class="reversalform"]/..', $doc)->item(0);
					$id = $entry->getAttribute( "id" );
					if($headword->childNodes->length > 0)
					{
						$href = $headword->childNodes->item(0)->getAttribute( "href" );
						if(strlen(trim($href)) == 0)
						{
							$href = $headword->childNodes->item(0)->childNodes->item(0)->getAttribute( "href" );
						}
						//if href still is empty...
						if(strlen(trim($href)) == 0)
						{
							$href = $headword->childNodes->item(0)->childNodes->item(0)->childNodes->item(0)->getAttribute( "href" );
						}

						if(strlen(trim($href)) != 0)
						{
							$post_id = $this->get_post_id(str_replace("#", "", $href));
						}
					}
				}
				else
				{
					$post_id = $this->get_post_id_bytitle( $headword_text, $reversal_language, $subid);

					$newelement = $doc->createElement('a');
					//$newelement->appendChild($this->dom->createTextNode(addslashes(trim($field->textContent))));
					$newelement->appendChild($doc->createTextNode(addslashes($headword->textContent)));
					$newelement->setAttribute("href", "?p=" . $post_id);
					$newelement->setAttribute("class", $headword->getAttribute("class"));
					$newelement->setAttribute("lang", $headword->getAttribute("lang"));
					$parent = $headword->parentNode;
					$parent->replaceChild($newelement, $headword);

					$reversal_xml = $doc->saveXML($doc, LIBXML_NOEMPTYTAG);
				}

				if($headwordCount == $headwords->length)
				{
					if(strlen(trim($reversal_xml)) == 0)
					{
						$reversal_xml = $postentry;
					}
					$reversal_xml = stripslashes($reversal_xml);

					//$doc->removeAttributeNS("http://www.w3.org/1999/xhtml", "");

					$sql = "SELECT id, reversal_head FROM " . Webonary_Configuration::$reversal_table_name;
					$sql .= " WHERE ";
					if(strpos($postentry, "reversalindexentry") > 0)
					{
						$sql .= " id = '" . $id . "' ";

					}
					else

					{
						$sql .= " reversal_head = '" . addslashes($reversal_head) . "' collate " . MYSQL_CHARSET . "_bin ";
					}
					$sql .= "AND language_code = '" . $reversal_language . "'";

					$existing_entry = $wpdb->get_var($sql);

					//If the reversal language is set to Chinese
					//the Chinese headwords get converted to pinyin, so that the reversal browse view
					//will work see https://github.com/overtrue/pinyin
					$reversal_browsehead = $reversal_head;
					if(($reversal_language == "zh-CN" || $reversal_language == "zh-Hans-CN"))
					{
						require_once( dirname( __FILE__ ) . '/pinyin/src/Pinyin.php' );
						require_once( dirname( __FILE__ ) . '/pinyin/src/DictLoaderInterface.php' );
						require_once( dirname( __FILE__ ) . '/pinyin/src/FileDictLoader.php' );

						$pinyin = new Pinyin();
						$reversal_browsehead = $pinyin->sentence($reversal_head);
						$browseletter = substr($reversal_browsehead, 0, 1);
					}

					if($existing_entry == NULL)
					{
						$sql = $wpdb->prepare(
							"INSERT IGNORE INTO `". Webonary_Configuration::$reversal_table_name . "` (id, language_code, reversal_head, reversal_content, sortorder, browseletter)
								VALUES('%s', '%s', '%s', '%s', %d, '%s')",
							$id, $reversal_language, $reversal_browsehead, $reversal_xml, $entry_counter, $browseletter);
					}
					else
					{
						$sql = $wpdb->prepare(
							"UPDATE " . Webonary_Configuration::$reversal_table_name . "
								SET reversal_content = '%s',
								browseletter = '%s'
								WHERE reversal_head = '%s' AND language_code = '%s' AND $id = '%s'",
							$reversal_xml, $browseletter, $reversal_browsehead, $reversal_language, $id);
					}

					$wpdb->query( $sql );

				}
				if($post_id == 0)
				{
					error_log("PostId for '" . $headword_text . "' not found.");
				}
				$this->import_xhtml_search_string( $post_id, $reversal_head, $this->headword_relevance, $reversal_language, "reversalform", 0);

				$headwordCount++;

			}
			$entry_counter++;
		}

		return $entry_counter;
	}


	/**
	 * Import stem indexes from a stem view index XHTML file. This will
	 * not add any new lexical entries, but it will update the field "sortorder" in the search
	 * table.
	 */

	function import_xhtml_stem_indexes ($postentry, $entry_counter) {
		global $wpdb;

		$doc = new DomDocument();
		$doc->preserveWhiteSpace = false;
		$doc->loadXML($postentry);
		$this->dom_xpath = new DOMXPath($doc);
		$this->dom_xpath->registerNamespace('xhtml', 'http://www.w3.org/1999/xhtml');

		//$entries = $this->dom_xpath->query('//xhtml:div[@class="entry"]');
		//$entry = $this->dom_xpath->query('./xhtml:span[@class="headword"]|./xhtml:span[@class="headword-minor"]|./xhtml:span[@class="headword-sub"]');
		$headwords = $this->dom_xpath->query( './xhtml:span[@class="headword"]|./xhtml:span[@class="headword_L2"]|./xhtml:span[@class="headword-minor"]|./*[@class="headword-sub"]');

		$entry = $headwords->item(0);
		$headword_text = trim($entry->textContent);

		//this is used for the browse view sort order
		$sql = "UPDATE " . Webonary_Configuration::$search_table_name . " SET sortorder = " . $entry_counter . " WHERE search_strings = '" . addslashes($headword_text) . "' COLLATE '" . MYSQL_CHARSET . "_BIN' AND relevance >= 95";
		$wpdb->query( $sql );

		//this is used for the search sort order
		$sql = "UPDATE " . $wpdb->posts . " SET menu_order = " . $entry_counter . " WHERE post_title = '" . addslashes($headword_text) . "' collate " . MYSQL_CHARSET . "_bin";
		$wpdb->query( $sql );

		/*
		 * Show progresss to the user.
		 */
		$this->import_xhtml_show_progress( $entry_counter, $entries_count, $headword_text );

		$entry_counter++;

		return $entry_counter;
	}

	// Currently we aren't deleting any existing writing systems.
	// For the moment, any bad writing systems must be removed by hand.
	function import_xhtml_writing_systems ($header) {
		global $wpdb;

		$this->writing_system_taxonomy = "sil_writing_systems";


		$doc = new DomDocument();
		$doc->preserveWhiteSpace = false;
		$doc->loadXML($header);

		$this->dom_xpath = new DOMXPath($doc);
		$this->dom_xpath->registerNamespace('xhtml', 'http://www.w3.org/1999/xhtml');

		if ( taxonomy_exists( $this->writing_system_taxonomy ) )
		{
			// An example of writing system and font in meta of the XHTML file header:
			// <meta name="en" content="English" scheme="Language Name" />
			// <meta name="en" content="Times New Roman" scheme="Default Font" />
			$writing_systems = $this->dom_xpath->query( '//xhtml:meta[@scheme = "Language Name"]|//xhtml:meta[@name = "DC.language"]');

			// Currently we aren't using font info.
			// $writing_system_fonts = $this->dom_xpath->query( '//xhtml:meta[@scheme = "Default Font"]' );
			if($writing_systems->length == 0 && isset($_POST['chkShowDebug']))
			{
				echo "The language names were not found. Please add the language name meta tag in your xhtml file.<br>";
			}
			foreach ( $writing_systems as $writing_system ) {

				$writing_system_abbreviation = $writing_system->getAttribute( "name");
				$writing_system_name = $writing_system->getAttribute( "content");

				if($writing_system->getAttribute( "name") == "DC.language")
				{
					$content = explode(":", $writing_system->getAttribute( "content"));
					$writing_system_abbreviation = $content[0];
					$writing_system_name = $content[1];
				}

				// Currently we aren't using font info.
				//$writing_system_font = $this->dom_xpath->query(
				//  '../xhtml:meta[@name = "' . $writing_system_abbreviation . '" and @scheme = "Default Font"]',
				//  $writing_system );
				//$font = $writing_system_font->item( 0 )->getAttribute( "content" );

				wp_insert_term(
					$writing_system_name,
					$this->writing_system_taxonomy,
					array(
						'description' => $writing_system_name,
						'slug' => $writing_system_abbreviation
					));

				// We are not using this taxonomy to group posts, but rather to search for strings
				// with a given writing system. If we ever change that, we'll want to load this on
				// a post by post basis.
				//
				//wp_set_object_terms( $post_id, $writing_system_name, $writing_systems_taxonomy );

			} // foreach ( $writing_systems as $writing_system ) {

			// Since we're not associating this taxonomy with any posts, wp_term_taxonomy.count = 0.
			// When that's true, the taxonomy doesn't work correctly in the drop down list. The
			// field needs a count of at least 1. I'm filling the number with something bigger
			// so that it looks more obviously like a dummy number.

			$sql = $wpdb->prepare("UPDATE $wpdb->term_taxonomy SET COUNT = 999999 WHERE taxonomy = '%s'", $this->writing_system_taxonomy );
			$wpdb->query( $sql );
		}
	}

	function index_searchstrings()
	{
		global $wpdb;

		$search_table_exists = $wpdb->get_var( "show tables like '" . Webonary_Configuration::$search_table_name . "'" ) == Webonary_Configuration::$search_table_name;
		$pos_taxonomy_exists = taxonomy_exists( Webonary_Configuration::$pos_taxonomy );
		$semantic_domains_taxonomy_exists = taxonomy_exists( Webonary_Configuration::$semantic_domains_taxonomy );

		if ( $search_table_exists ) {
			$arrPosts = Webonary_Info::posts('-');

			$subid = 1;

			$entry_counter = 1;
			$entries_count = count($arrPosts);

			update_option("useSemDomainNumbers", 0);

			foreach($arrPosts as $post)
			{
				$subentry = false;
				if ( $post->ID ){

					$sql = $wpdb->prepare("DELETE FROM `". Webonary_Configuration::$search_table_name . "` WHERE post_id = %d", $post->ID);

					$wpdb->query( $sql );
					//set as indexed
					$sql = "UPDATE $wpdb->posts SET pinged = 'indexed' WHERE ID = " . $post->ID;
					$wpdb->query( $sql );
				}

				$doc = new DomDocument();
				$doc->preserveWhiteSpace = false;
				$doc->loadXML($post->post_content);

				$xpath = new DOMXPath($doc);
				$xpath->registerNamespace('xhtml', 'http://www.w3.org/1999/xhtml');

				$this->import_xhtml_show_progress( $entry_counter, $entries_count, $post->post_title, "Step 2 of 2: Indexing Search Strings");

				if($post->post_parent == 0)
				{
					$this->import_xhtml_classes($post->ID, $doc);
				}
				else
				{
					$subentry = true;
				}

				//this is used for the browse view sort order, no longer needed for
				$sql = "UPDATE " . Webonary_Configuration::$search_table_name . " SET sortorder = " . $post->menu_order . " WHERE post_id = " . $post->ID . " AND relevance >= 95 AND sortorder = 0" ;
				$wpdb->query( $sql );

				/*
				 * Import semantic domains
				*/
				if ( $semantic_domains_taxonomy_exists )
				{
					$this->import_xhtml_semantic_domain($doc, $post->ID, $subentry, false);
				}
				/*
				 * Import parts of speech (POS)
				 */
				if ( $pos_taxonomy_exists )
					$this->import_xhtml_part_of_speech($doc, $post->ID);

				$subid++;
				$entry_counter++;
			}

			$this->update_relevance();
			update_option("importStatus", "importFinished");
		}
	}

	function update_relevance()
	{
		global $wpdb;

		$tableCustomRelevance = $wpdb->prefix . "custom_relevance";

		$arrClasses = $wpdb->get_results ("SELECT class, relevance FROM $tableCustomRelevance");

		if (count ($arrClasses) > 0) {
			foreach($arrClasses as $class)
			{
				$wpdb->query ("UPDATE " . Webonary_Configuration::$search_table_name . " SET relevance = ". $class->relevance ." WHERE class = '".$class->class."'");
			}
		}
	}

	/**
	 * Upload the files indicated by the user. An override of wp_import_handle_upload.
	 *
	 * @param string $which_file = The file being uploaded
	 * @return array $file = the file, $id = the file's ID
	 */

	// The max file size is determined by the settings in php.ini. upload_max_files is set to 2MB by default
	// in development versions, which is too small for what we do. The setting has been found to be higher
	// in production settings. The post_max_size apparently needs to be at least as big as the
	// upload_max_files setting. If the file size is bigger than the limit, the server simply will not
	// upload it, and there is no indication to the user as to what happened.
	function upload_files( $which_file, $filetype = "",  $reversalLang = "") {
		global $wpdb;
		$upload_dir = wp_upload_dir();

		$hasError = false;

		if ( !isset($_FILES[$which_file]) ) {
			$file['error'] = __( 'The file is either empty, or uploads are disabled in your php.ini, or post_max_size is defined as smaller than upload_max_filesize in php.ini.' );
			return $file;
		}

		if( $_FILES[$which_file]["name"] == "ProjectDictionaryOverrides.css" || $_FILES[$which_file]["name"] == "ProjectReversalOverrides.css" )
		{
			unlink($upload_dir['path'] . "/" .  $_FILES[$which_file]["name"]);
		}

		$overrides = array( 'test_form' => false, 'test_type' => false );
		$file = wp_handle_upload( $_FILES[$which_file], $overrides );

		if ( isset( $file['error'] ) )
		{
			return $file;
		}

		$url = $file['url'];
		$type = $file['type'];
		$file = addslashes( $file['file'] );
		$filename = $_FILES[$which_file]["name"];

		$info = pathinfo($file);
		$extension = $info['extension'];

		if($extension == "css")
		{
			$target_path = $upload_dir['path'] . "/imported-with-xhtml.css";

			if($filename == "ProjectDictionaryOverrides.css")
			{
				$target_path = $upload_dir['path'] . "/ProjectDictionaryOverrides.css";
			}
			if($filetype == "reversal")
			{
				if($reversalLang == "")
				{
					$arrLanguageCodes = Webonary_Configuration::get_LanguageCodes();
					if(count($arrLanguageCodes) <= 2)
					{
						$reversalLang = get_option('reversal1_langcode');
					}
					else
					{
						echo "Please select a language code.<br>";
						$hasError = true;
					}
				}
				$target_path = $upload_dir['path'] . "/reversal_" . $reversalLang . ".css";
			}
			$from_path = $upload_dir['path'] . "/" . $filename;

			/*
				if(file_exists($target_path))
				{
				_e('The file imported-with-xhtml.css already exists in your upload folder. If you want to replace it, you have to delete it manually before you import a new file.');
				}
				*/
			error_reporting(E_ALL);
			if((copy($from_path, $target_path) || $from_path == $target_path) && $hasError == false) {

				$fontClass = new Webonary_Font_Management();
				$fontClass->set_fontFaces($target_path, $upload_dir['path']);

				_e('The css file has been uploaded into your upload folder:<br>' . $target_path . '<br>');
			} else{
				echo "<span style=color:red;>";
				_e('There was an error uploading the file, please try again!');
				echo "</span>";
				echo "<br>";
				echo "From Path: " . $from_path . "<br>";
				echo "Target Path: " . $target_path . "<br>";
			}
		}

		// Construct the object array
		$object = array( 'post_title' => $filename,
			'post_content' => $url,
			'post_mime_type' => $type,
			'guid' => $url
		);

		// Save the data
		$id = wp_insert_attachment( $object, $file );

		if($extension == "css" && $from_path != $target_path)
		{
			unlink($file);

			$sql = "DELETE FROM " . $wpdb->prefix . "posts WHERE post_type = 'attachment' AND post_title LIKE '%." . $extension . "'";

			$wpdb->query( $sql );
		}

		return array( 'file' => $file, 'id' => $id );
	}

	function my_cust_filename($dir, $name, $ext){
		echo "<b>" . $name.$ext . "</b><br>";
		return $name.$ext;
	}

} // class
