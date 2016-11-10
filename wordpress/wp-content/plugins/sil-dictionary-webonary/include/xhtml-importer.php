<?php
/**
 * SIL FieldWorks XHTML Importer
 *
 * Imports data from an SIL FLEX XHTML file. The data may come from SIL
 * FieldWorks or other applications.
 *
 * PHP version 5.2
 *
 * LICENSE GPL v2
 *
 * @package WordPress
 * @subpackage Importer
 * @since 3.1
 */

set_time_limit(0);

// don't load directly
if ( ! defined('ABSPATH') )
	die( '-1' );
/*
// Check to make sure we can even load an importer.
if ( ! defined( 'WP_LOAD_IMPORTERS' ) )
    return;
*/
// Include the WordPress Importer.
require_once ABSPATH . 'wp-admin/includes/import.php';

if ( ! class_exists('WP_Importer') )  {
    $class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
    if ( file_exists( $class_wp_importer ) )
        require_once $class_wp_importer;
}

// One more check.
if ( ! class_exists( 'WP_Importer' ) )
	return;

//============================================================================//

/**
 * Importer class
 */
class sil_pathway_xhtml_Import extends WP_Importer {

	public $api; //if data is sent from an external program
	public $verbose;

	/*
	 * Table and taxonomy attributes
	 */

	public $search_table_name = SEARCHTABLE;
	public $reversal_table_name = REVERSALTABLE;
	public $pos_taxonomy = 'sil_parts_of_speech';
	public $semantic_domains_taxonomy = 'sil_semantic_domains';

	/*
	 * Relevance level attributes
	 */

	public $headword_relevance = 100;
	public $plural = 80;
	public $lexeme_form_relevance = 70;
	public $variant_form_relevance = 60;
	public $definition_word_relevance = 50;
	public $semantic_domain_relevance = 40;
	public $scientific_name = 35;
	public $sense_crossref_relevance = 30;
	public $custom_field_relevance = 20;
	public $example_sentences_relevance = 10;

	/*
	 * DOM attributes
	 */

	public $dom;
	public $dom_xpath;

	//-----------------------------------------------------------------------------//

	function sil_pathway_xhtml_Import()
	{
		/**
		 * Empty function
		 */
	}
	
	function start()
	{
		global $wpdb;
		global $current_user;
		
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
				echo "You can now close the browser window. <a href=\"../wp-admin/admin.php?page=webonary\">Click here to view the import status</a><br>";
			}
			flush();
			
			$file = $this->get_latest_xhtmlfile();
			$xhtmlFileURL = $file->url;
			$userid = $current_user->ID;
			require("run_import.php");
			
			return;
		}

		if(isset($_POST['btnReindex']))
		{
		?>
			<DIV ID="flushme">Indexing Search Strings... </DIV>
			<?php
			$this->verbose = true;
			$this->index_searchstrings();

			$file = $this->get_latest_xhtmlfile();
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
			
			$file = $this->get_latest_xhtmlfile();
			$xhtmlFileURL = $file->url;
			$userid = $current_user->ID;
			require("run_import.php");

			return;
			
		}
		
		/*
		if(isset($_POST['btnConvertFLExLinks']))
		{
		?>
			<DIV ID="flushme">Converting FLEx links to Webonary links... </DIV>
		<?php
			$this->verbose = true;
			$this->convert_fieldworks_links_to_wordpress($_POST['pinged']);
		}
		*/
		if(isset($_POST['btnMakeLinks']))
		{
		?>
			<DIV ID="flushme">Converting headwords to links... </DIV>
		<?php
			$this->verbose = true;
			$this->convert_fields_to_links();
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
				
				// Get the XHMTL file
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

				$file = $this->get_latest_xhtmlfile();
				if(isset($file))
				{
					$xhtmlFileURL = $file->url;
					$userid = $current_user->ID;
					require("run_import.php");
				}
				
				/*
				?>
				<DIV ID="flushme">importing...</DIV>
				<?php

				$file = $this->get_latest_xhtmlfile();
				if(isset($file))
				{
					$xhtml_file = file_get_contents($file->url);
				}
				$result = $this->import_xhtml($xhtml_file, false, $this->verbose);

				$this->goodbye($xhtml_file, $css_file);

				if(isset($file))
				{
					//##wp_delete_attachment( $file->ID );
				}
				*/
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
			case 3 :
				?>
				<DIV ID="flushme">converting links...</DIV>
				<?php
				$this->convert_fields_to_links();

				echo '<p>' . __( 'Finished!', 'sil_dictionary' ) . '</p>';
				echo '<p>&nbsp;</p>';
				echo '<p>After importing, go to <strong><a href="../wp-admin/admin.php?page=webonary">Webonary</a></strong> to configure more settings.</p>';
			?>
			<?php
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
				if($_GET['step'] == 2 && $_POST['chkConvertToLinks'] > 0)
				{
					echo '<strong>Next step: </strong>';
					echo '<p>';
						echo '<form enctype="multipart/form-data" id="import-upload-form" method="post" action="' . esc_attr(wp_nonce_url("admin.php?import=pathway-xhtml&amp;step=3", 'import-upload')) . '">';
							echo '<input type="hidden" name="chkShowProgress" value=' . $_POST['chkShowProgress'] . '></input>';
							echo '<input type="submit" class="button" name="btnIndex" value="Convert Links"/>';
							if(isset($_POST['chkConvertToLinks']))
							{
								echo '<input type="hidden" name="chkConvertToLinks" value=' . $_POST['chkConvertToLinks'] . '></input>';
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
					if($_POST['chkConvertToLinks'] > 0)
					{
						$this->convert_fields_to_links();
					}
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

					$filename = "/audio/" . str_replace("\\", "/", trim($audiofile));
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

	function convert_fields_to_links() {

		global $wpdb;

		$arrPosts = $this->get_posts("indexed");

		$entry_counter = 1;
		$entries_count = count($arrPosts);

		foreach($arrPosts as $post)
		{
			$post_id = $post->ID;

			$entry = new DomDocument();
			$entry->preserveWhiteSpace = false;
			$entry->loadXML($post->post_content);
			$xpath = new DOMXPath($entry);

			$this->import_xhtml_show_progress( $entry_counter, $entries_count, $post->post_title, "Converting Links");

			$sql = "UPDATE $wpdb->posts SET pinged = 'linksconverted' WHERE ID = " . $post_id;
			$wpdb->query( $sql );

			$arrAllQueries = $this->getArrFieldQueries(true);

			//we convert the headwords to links by default
			//clicking on a headword will lead to a page with a comment form (if comments are activated)
			$arrFieldQueries[0] = $arrAllQueries[0];
			$arrFieldQueries[1] = $arrAllQueries[1];

			foreach($arrFieldQueries as $fieldQuery)
			{
				if (!preg_match("/sense-crossref/i", $fieldQuery))
				{
					$fields = $xpath->query($fieldQuery);

					foreach($fields as $field)
					{
						$Emphasized_Text = null;
						$searchstring = $field->textContent;
						if(is_numeric(substr($searchstring, (strlen($searchstring) - 1), 1)))
						{
							$searchstring = substr($searchstring, 0, (strlen($searchstring) - 1));
						}

						if($field->getAttribute("class") == "definition" || $field->getAttribute("class") == "definition-sub")
						{
							$Emphasized_Text = $xpath->query('//span[@class = "Emphasized_Text"]');

							if($Emphasized_Text->length > 0)
							{
								if($field->getAttribute("class") == "definition-sub")
								{
									$newField = $xpath->query('//span[@class="definition-sub"]/node()[not(@class = "Emphasized_Text")]');
								}
								else
								{
									$newField = $xpath->query('//span[@class="definition"]/node()[not(@class = "Emphasized_Text")]');
								}

								$field = $newField->item(0);
								$searchstring = $field->textContent;
							}
						}
						
						//if($Emphasized_Text->length == 0)
						if($field->getAttribute("class") != "partofspeech" && !preg_match("/HeadWordRef/i", $field->getAttribute("class")) && strpos($entry->saveXML($field), "href") == null)
						{
							//$newelement = $this->dom->createElement('a');
							$newelement = $entry->createElement('span');
							//$newelement->appendChild($entry->createTextNode("<span>" . addslashes($field->textContent) . "</span>"));
							//$newelement->appendChild($entry->createElement('span', addslashes($field->textContent)));

							$allNodes = "";
							foreach($field->childNodes as $node)
							{
								$allNodes .= $entry->saveXML($node);
							}
							$fragment = $entry->createDocumentFragment();
							$url = get_bloginfo('wpurl') . "/?s=" . addslashes(trim($field->textContent)) . "&partialsearch=1";
							$fragment->appendXML('<a href="' . htmlspecialchars($url) . '">' . $allNodes . '</a>');
							$newelement->appendChild($fragment);
								
							//$newelement->setAttribute("href", "/?s=" . addslashes(trim($searchstring)) . "&partialsearch=1");
							if(isset($Emphasized_Text))
							{
								$newelement->setAttribute("class", "definition");
							}
							else
							{
								$newelement->setAttribute("class", $field->getAttribute("class"));
							}
							////$newelement->setAttribute("lang", $field->getAttribute("lang"));
							/*
							if($Emphasized_Text->length > 0)
							{
								$Emphasized_Text->item(0)->insertBefore($newelement);
								$newelement = $Emphasized_Text->item(0);
							}
							*/
							
							$parent = $field->parentNode;
							$parent->replaceChild($newelement, $field);
						}
					}
				}
			}
			$entry_xml = $entry->saveXML( $entry );

			$sql = "UPDATE $wpdb->posts " .
			" SET post_content = '" . addslashes(stripslashes($entry_xml)) . "'" .
			" WHERE ID = " . $post_id;

			$wpdb->query( $sql );

			if($entry_counter % 50 == 0)
			{
				////sleep(1);
			}
						
			$entry_counter++;
		}
		update_option("importStatus", "importFinished");
	}

	function convert_semantic_domains_to_links($post_id, $doc, $field, $termid) {
		global $wpdb;

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

	}

	/**
	 * Footer for the screen
	 */
	function footer() {
		echo '</div>';
	}
	
	function getArrFieldQueries($step = 0)
	{
		if($_GET['step'] >= 2 || $step >= 2)
		{
			$querystart = "//span";
		}
		else
		{
			$querystart = ".//xhtml:span";
		}
	
		//$arrFieldQueries[0] = $querystart . '[@class="headword"]|//*[@class="headword_L2"]|//*[@class="headword-minor"]';
		$arrFieldQueries[0] = '//div[@class="entry"]/span[@class="mainheadword"]|//div[@class="mainentrycomplex"]/span[@class="headword"]|//div[@class="entry"]/*[@class="headword"]|//div[@class="minorentry"]/span[@class="headword"]|//div[@class="minorentryvariant"]/span[@class="headword"]|//div[@class="minorentrycomplex"]/span[@class="headword"]|./*[@class="headword_L2"]|//span[@class="headword-minor"]';
		$arrFieldQueries[1] = $querystart . '[@class = "headword-sub"]|' . $querystart . '[@class="subentry"]/*[@class="headword"]';
		$arrFieldQueries[2] = $querystart . '[contains(@class, "LexemeForm")]|' . $querystart . '[contains(@class, "lexemeform")]';
		//$arrFieldQueries[3] = $querystart . '[@class = "definition"]|//*[@class = "definition_L2"]|//*[@class = "definition-minor"]';
		$arrFieldQueries[3] = $querystart . '[starts-with(@class,"definition")]/span|' . $querystart . '[starts-with(@class,"gloss")]/span|' . $querystart . '[starts-with(@class,"LexSense")]';
		$arrFieldQueries[4] = $querystart . '[starts-with(@class,"definition-sub")]'; //REMOVE WITH FLEX 8.3
		$arrFieldQueries[5] = $querystart . '[@class = "example"]/span[@lang]';
		$arrFieldQueries[6] = $querystart . '[@class = "translation"]/span[@lang]';
		$arrFieldQueries[7] = $querystart . '[starts-with(@class,"LexEntry-") and not(contains(@class, "LexEntry-publishRoot-DefinitionPub_L2"))]/span';
		$arrFieldQueries[8] = $querystart . '[@class = "variantref-form"]';
		$arrFieldQueries[9] = $querystart . '[@class = "variantref-form-sub"]';
		$arrFieldQueries[10] = $querystart . '[@class = "sense-crossref"]';
		$arrFieldQueries[11] = $querystart . '[@class = "scientificname"]|' . $querystart . '[@class = "scientific-name"]';
		$arrFieldQueries[12] = $querystart . '[@class = "plural"]';
	
		return $arrFieldQueries;
	}
	
	function get_category_id() {
		global $wpdb;
	
		$catid = $wpdb->get_var( "
			SELECT term_id
			FROM $wpdb->terms
			WHERE slug LIKE 'webonary'");

		if(!isset($catid))
		{
			$catid = 0;
		}
		
		return $catid;
	}

	function get_duplicate($postid, $searchstring, $relevance, $lang) {
		global $wpdb;

		$isDuplicate = false;

		$duplicatePostId = $wpdb->get_var( "
			SELECT post_id
			FROM $this->search_table_name
			WHERE post_id = " . $postid . " AND search_strings = '" . addslashes($searchstring) .
			"' AND relevance = ". $relevance . " AND language_code = '" . $lang . "'");

		if($duplicatePostId == $postid)
		{
			$isDuplicate = true;
		}

		return $isDuplicate;
	}

	function get_import_status() {
		global $wpdb;
		$status = "";
		
		if(get_option("useSemDomainNumbers") == 0)
		{
			$sql = "SELECT COUNT(taxonomy) AS sdCount FROM " . $wpdb->prefix  . "term_taxonomy WHERE taxonomy LIKE 'sil_semantic_domains'";
			
			$sdCount = $wpdb->get_var($sql);
			
			if($sdCount > 0)
			{
				$status .= "<br>";
				$status .= "<span style=\"color:red;\">It appears you imported semantic domains without the domain numbers. Please go to Tools -> Configure -> Dictionary.. in FLEx and check \"Abbrevation\" under Senses/Semantic Domains.</span><br>";
				$status .= "Tip: You can hide the domain numbers from displaying, <a href=\" http://www.webonary.org/help/tips-tricks/\" target=_\"blank\">see here</a>.";
				$status .= "<hr>";
			}
		}

		$countLinksConverted = 0;

		$catid = $this->get_category_id();

		if($catid == NULL)
		{
			$catid = 0;
		}

		$sql = "SELECT COUNT(pinged) AS entryCount, post_date, TIMESTAMPDIFF(SECOND, MAX(post_date),NOW()) AS timediff, pinged FROM " . $wpdb->prefix . "posts " .
		" WHERE post_type IN ('post', 'revision') AND " .
		" ID IN (SELECT object_id FROM " . $wpdb->prefix . "term_relationships WHERE " . $wpdb->prefix . "term_relationships.term_taxonomy_id = " . $catid .") " .
		" GROUP BY pinged " .
		" ORDER BY post_date DESC";
				
		$arrPosts = $wpdb->get_results($sql);
		
		$sql = " SELECT * " .
				" FROM " . $this->reversal_table_name;

		$arrReversalsImported = $wpdb->get_results($sql);
		
		$arrIndexed = $this->get_number_of_entries();
		
		if(count($arrPosts) > 0)
		{
			$countIndexed = 0;
			$totalImportedPosts = count($this->get_posts());

			foreach($arrPosts as $posts)
			{
				if($posts->pinged == "indexed")
				{
					$countIndexed = $posts->entryCount;
				}
				elseif($posts->pinged == "linksconverted")
				{

					$countLinksConverted = $posts->entryCount;
				}
				else
				{

					$countImported = $posts->entryCount;
				}
			}

			$countIndexed = $countIndexed + $countLinksConverted;
			
			/*
			$importFinished = false;
			if($countIndexed == $totalImportedPosts || $countLinksConverted == $totalImportedPosts)
			{
				$importFinished = true;
			}
			*/
			$status .= "<form method=\"post\" action=\"" . $_SERVER['REQUEST_URI'] . "\">";
			if(get_option("importStatus") == "importFinished")
			{
				if($posts->post_date != NULL)
				{
					$status .= "Last import of configured xhtml was at " . $posts->post_date . " (server time)";
					$status .= "<br>";
					
					/*
					if($_GET['convertlinks'] == 1)
					{
						$status .= "<input type=hidden name=chkConvertFLExLinks value=1>";
						$status .= "<input type=hidden name=pinged value=linksconverted>";
						$status .= "<br><input type=\"submit\" name=\"btnConvertFLExLinks\" value=\"Retry converting FLEx links\">&nbsp;&nbsp;&nbsp;";
					}
					*/
				}
			}
			else
			{
				if(!get_option("importStatus"))
				{
					echo "The import status will display here.<br>";
				}
				else
				{
					$status .= "Importing... <a href=\"" . $_SERVER['REQUEST_URI']  . "\">refresh page</a><br>";
					$status .= " You will receive an email when the import has completed. You don't need to stay online.";
					$status .= "<br>";
	
					if(get_option("importStatus") == "indexing")
					{
						$status .= "Indexing " . $countIndexed . " of " . $totalImportedPosts . " entries";
	
						$status .= "<br>If you believe indexing has timed out, click here: <input type=\"submit\" name=\"btnReindex\" value=\"Index Search Strings\"/>";
					}
					elseif(get_option("importStatus") == "convertlinks")
					{
						$status .= "Converting Links " . $countLinksConverted . " of " . get_option("totalConfiguredEntries") . " entries";
	
						$status .= "<input type=hidden name=chkConvertToLinks value=1>";
						$status .= "<br>If you believe indexing has timed out, click here: <input type=\"submit\" name=\"btnMakeLinks\" value=\"Turn headwords into links\"/>";
					}
					elseif(get_option("importStatus") == "configured")
					{
						$status .= $countImported . " entries imported";
						
						if($arrPosts[0]->timediff > 5)
						{
							$status .= "<br>It appears the import has timed out, click here: <input type=\"submit\" name=\"btnRestartImport\" value=\"Restart Import\">";
						}
					}
					elseif(get_option("importStatus") == "importingReversals")
					{
						$status .= "<strong>Importing reversals. So far imported: " . count($arrReversalsImported) . " entries.</strong>";
						
						$status .= "<br>If you believe the import has timed out, click here: <input type=\"submit\" name=\"btnRestartReversalImport\" value=\"Restart Reversal Import\">";
					}
					elseif(get_option("importStatus") == "indexingReversals" && count($arrReversalsImported) > 0)
					{
						$status .= "<strong>Indexing reversal entries.</strong>";
		
						$status .= "<br>If you believe indexing has timed out, click here: <input type=\"submit\" name=\"btnIndexReversals\" value=\"Index reversal entries (" . count($arrReversalsImported)  . " left)\"/>";
					}
				}
			}
			
			if(count($arrIndexed) > 0 && ($countIndexed == $totalImportedPosts || $countLinksConverted == $totalImportedPosts))
			{
				$status .= "<br>";
				$status .= "<div style=\"float: left;\">";
					$status .= "<strong>Number of indexed entries (by language code):</strong><br>";
				$status .= "</div>";
				$status .= "<div style=\"min-width:50px; float: left; margin-left: 5px;\">";
				$x = 0;
				foreach($arrIndexed as $indexed)
				{
					$status .= "<div style=\"clear:both;\"><div style=\"text-align:right; float:left;\"><nobr>" . $indexed->language_code . ":</nobr></div><div style=\"float:left;\">&nbsp;". $indexed->totalIndexed;

					$arrReversalsFiltered = array_filter($arrReversalsImported, function($el) { return $el->post_id == 0; });
					
					$sql = "SELECT COUNT(language_code) AS missing " .
							" FROM " . $this->search_table_name .
							" WHERE post_id = 0 AND language_code = '" . $indexed->language_code . "'" .
							" GROUP BY language_code";
					
					$missingReversals = $wpdb->get_var($sql);
					
					if($missingReversals > 0)
					{
						$status .= " <a href=\"edit.php?page=sil-dictionary-webonary/include/configuration.php&reportMissingSenses=1&languageCode=" . $indexed->language_code . "&language=" . $indexed->language_name . "\" style=\"color:red;\">missing senses for " . $missingReversals . " entries</a>";
					}
					
					$status .= "</div></div>";
				}
				$status .= "</div>";
				$status .= "<br style=\"clear:both;\">";
				
			}
			
			$status .= "</form>";
				
			return $status;
		}
		else
		{
			return "No entries have been imported yet. <a href=\"" . $_SERVER['REQUEST_URI']  . "\">refresh page</a>";
		}

		$sql = "SELECT post_date, pinged FROM " . $wpdb->prefix . "posts ".
		" WHERE post_type IN ('post', 'revision') AND ".
		" ID IN (SELECT object_id FROM " . $wpdb->prefix . "term_relationships WHERE " . $wpdb->prefix . "term_relationships.term_taxonomy_id = " . $catid .") ".
		" AND pinged = 'indexed' ".
		" ORDER BY post_date DESC";

		$arrIndexed = $wpdb->get_results($sql);

		if(count($arrPosts) > 0 || count($arrIndexed) > 0)
		{
			if($arrPosts[0]->pinged != "indexed")
			{
				$entries = count($arrPosts);
				if(count($arrIndexed) > 0)
				{
					$entries = get_option("totalConfiguredEntries") - count($arrIndexed);
				}

				$status .= $entries . " of " . get_option("totalConfiguredEntries") . " entries imported (not yet indexed)<br>";
			}
			else
			{
				$status .= "Indexing " . count($arrIndexed) . " of " . get_option("totalConfiguredEntries") . " entries<br>";
			}
			return $status;
		}
		else
		{
			return "No entries have been imported yet.";
		}
	}

	function get_latest_xhtmlfile(){
		global $wpdb;

		$sql = "SELECT ID, post_content AS url
			FROM $wpdb->posts
			WHERE post_content LIKE '%.xhtml' AND post_type LIKE 'attachment'
			ORDER BY post_date DESC
			LIMIT 0,1";
		
		$arrLastFile = $wpdb->get_results($sql);

		if(count($arrLastFile) > 0)
		{
			return $arrLastFile[0];
		}
		else
		{
			return null;
		}
	}
	
	function get_number_of_entries()
	{
		global $wpdb;
		
		$sql = " SELECT language_code, COUNT(post_id) AS totalIndexed, name AS language_name " .
				" FROM " . $this->search_table_name .
				" INNER JOIN $wpdb->terms ON $wpdb->terms.slug = " . $this->search_table_name . ".language_code " .
				" WHERE relevance >= 95 " .
				" GROUP BY language_code " .
				" ORDER BY name ASC";
		
		$arrIndexed = $wpdb->get_results($sql);

		$sql = " SELECT language_code, COUNT(language_code) AS totalIndexed " .
				" FROM " . $this->reversal_table_name .
				" INNER JOIN $wpdb->terms ON $wpdb->terms.slug = " . $this->reversal_table_name . ".language_code " .
				" GROUP BY language_code " .
				" ORDER BY name ASC";
		
		$arrReversals = $wpdb->get_results($sql);
		
		$r = 0;
		$s = 0;
		foreach($arrIndexed as $indexed)
		{
			if($arrReversals[$s]->language_code == $indexed->language_code)
			{
				$arrIndexed[$r]->totalIndexed = $arrReversals[$s]->totalIndexed;
				$s++;
			}
			$r++;
		}
		
		//legacy code, to count approximate number of reversals before we imported reversal entries into
		//the reversal table
		if(count($arrReversals) == 0 && count($arrIndexed) > 0)
		{
			$x = 0;
			$count_posts = count($this->get_posts(''));
			foreach($arrIndexed as $indexed)
			{
				$sql = " SELECT search_strings " .
					" FROM " . $this->search_table_name .
					" WHERE language_code = '" . $indexed->language_code . "' " .
					" AND relevance >= 95 " .
					" GROUP BY search_strings COLLATE " . COLLATION . "_BIN";
					
				$arrIndexGrouped = $wpdb->get_results($sql);
				
				if($count_posts != $indexed->totalIndexed && ($count_posts + 1) != $indexed->totalIndexed)
				{
					$arrIndexed[$x]->totalIndexed = count($arrIndexGrouped);
				}
				$x++;
			}
		}
		
		return $arrIndexed;
	}
	
	function get_posts($index = ""){
		global $wpdb;

		// @todo: If $headword_text has a double quote in it, this
		// will probably fail.
		$sql = "SELECT ID, post_title, post_content, post_parent, menu_order " .
			" FROM $wpdb->posts " .
			" INNER JOIN " . $wpdb->prefix . "term_relationships ON object_id = ID " .
			" WHERE " . $wpdb->prefix . "term_relationships.term_taxonomy_id = " . $this->get_category_id();
		//using pinged field for not yet indexed
		$sql .= " AND post_status = 'publish'";
		if(strlen($index) > 0 && $index != "-")
		{
		 $sql .= " AND pinged = '" . $index . "'";
		}
		if($index == "-")
		{
		 $sql .= " AND pinged = ''";
		}
		$sql .= " ORDER BY menu_order ASC";

		return $wpdb->get_results($sql);
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
			WHERE post_name = '" . trim($flexid) . "'	collate " . COLLATION . "_bin AND post_status = 'publish'";
		
		$row = $wpdb->get_row( $sql );

		if(count($row) > 0)
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
			WHERE post_content LIKE '%" . trim($flexid) . "%'	collate " . COLLATION . "_bin AND post_status = 'publish'";
			
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
			WHERE post_title = '" . addslashes(trim($headword)) . "' collate " . COLLATION . "_bin ";
		
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
				<?php _e('**XHTML file must be sorted. Webonary does not resort the entries.**'); ?>
				</label>
			</p>
			<p>
				<input type="file" id="upload" name="xhtml" size="100" />
			</p>
			<div id="uploadCSS">
			<p>
				<label for="upload"><?php _e( 'Choose a CSS file from your computer (optional):' ); ?></label>
					(<?php printf( __('Maximum size: %s' ), $size ); ?>)
			</p>
			<p>
				<input type="file" id="upload" name="css" size="100" />
			</p>
			</div>
			<?php
			$arrLanguageCodes = get_LanguageCodes();
			if(count($arrLanguageCodes) > 2)
			{
			?>
			<div id=langCode style="display:none;">
			<p>
				<?php _e("Language Code:"); ?>
				<select id=reversalLanguagecode name="languagecode">
					<option value=""></option>
					<?php
					foreach($arrLanguageCodes as $languagecode) {
						if(strlen(trim($languagecode->language_code)) > 0)
						{
					?>
						<option value="<?php echo $languagecode->language_code; ?>"><?php echo $languagecode->language_code; ?></option>
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
				<input type="radio" name="filetype" value="stem" onChange="toggleReversal();" /> *<?php esc_attr_e('Sort Order'); ?> <a href="http://webonary.org/data-transfer/#sortorder" target="_blank">only if sort order is different than configured view</a><BR>
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
		
	/**
	 * Header for the screen
	 */
	function header() {
		echo '<div class="wrap">';
		screen_icon();
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
	function import_xhtml_entries ($postentry, $entry_counter, $menu_order) {
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
				
			//inform the user about which fields are available
			$arrFieldQueries = $this->getArrFieldQueries(2);
			foreach($arrFieldQueries as $fieldQuery)
			{
				$fields = $this->dom_xpath->query($fieldQuery);
				if($fields->length == 0 && isset($_POST['chkShowDebug']))
				{
					echo "No entries found for the query " . $fieldQuery . "<br>";
				}
			}
		}
	
		// Find the headword. Should be only 1 headword at most. The
		// $headword->textContent picks up the value of both the headword and
		// the homograph number. This is presumably because the XML DOM
		// textContent property "returns the value of all text nodes
		// within the element node." The XHTML for an entry with homograph
		// number looks like this:
		// <span class="headword" lang="ii">my headword<span class="xhomographnumber">1</span></span>
		$doc = $this->convert_fieldworks_images_to_wordpress($doc);
		$doc = $this->convert_fieldworks_audio_to_wordpress($doc);
		$doc = $this->convert_fieldworks_video_to_wordpress($doc);
		
		$headwords = $this->dom_xpath->query( './xhtml:span[@class="mainheadword"]|./xhtml:span[@class="headword"]|./xhtml:span[@class="headword_L2"]|./xhtml:span[@class="headword-minor"]|./*[@class="headword-sub"]');
				
		//$headword = $headwords->item( 0 )->nodeValue;
		$h = 0;
		foreach ( $headwords as $headword ) {
			
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
			
			if($entry_counter == 1)
			{
				update_option("languagecode", $headword_language);
			}
	
			$entry = $this->dom_xpath->query('//xhtml:span[@class="mainheadword"]/..|//xhtml:span[@class="headword"]/..|//xhtml:span[@class="headword_L2"]/..|//xhtml:span[@class="headword-minor"]/..|//xhtml:div[@class="minorentries"]/span[@class="headword-minor"]/..|//xhtml:span[@class="headword-sub"]/..', $doc)->item(0);
				
			//$entry = $this->dom_xpath->query('//div', $doc)->item(0);
	
			$headword_text = $headword->textContent;
							
			$flexid = "";
			//if($this->dom_xpath->query('//xhtml:div[@id]', $entry)->length > 0)
			//{
			$flexid = $entry->getAttribute("id");
			//}
				
			if(strlen(trim($flexid)) == 0)
			{
				$flexid = $headword_text;
			}
	
			$entry->removeAttributeNS("http://www.w3.org/1999/xhtml", "");
				
			$entry_xml = $doc->saveXML($entry, LIBXML_NOEMPTYTAG);
				
			//this replaces a link like this: <a href="#gcec78a67-91e9-4e72-82d3-4be7b316b268">
			//to this: <a href="/gcec78a67-91e9-4e72-82d3-4be7b316b268">
			//but it will keep a link like this href="#" (important for playing audio)
			
			//first make sure audio href only contains a hastag (or any href with onclick after it)
			$entry_xml = preg_replace('/href="(#)([^"]+)" onclick/', 'href="#" onclick', $entry_xml);
					
			$entry_xml = preg_replace('/href="(#)([^"]+)"/', 'href="' . get_bloginfo('wpurl') . '/\\2"', $entry_xml);
				
			//video character is in utf8mb4 which is currently not supported on the webonary server
			//therefore we convert it into an image.
			$entry_xml = str_replace("🎥", "<img src=\"" . get_bloginfo('wpurl') . "/wp-content/plugins/sil-dictionary-webonary/images/video.png\"/>", $entry_xml);
				
			$entry_xml = addslashes($entry_xml);
			$entry_xml = stripslashes($entry_xml);
			//$entry_xml = str_replace("'","&#39;",$entry_xml);

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
						"INSERT INTO ". $wpdb->posts . " (post_date, post_title, post_content, post_status, post_parent, post_name, comment_status, menu_order)
				VALUES (NOW(), '%s', '%s', 'publish', %d, '%s', '%s', %d)",
						trim($headword_text), $entry_xml, $post_parent, $flexid, get_option('default_comment_status'), $menu_order );
	
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
			} // foreach ( $headwords as $headword )
	
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
	 * @param <type> $entry_counter = current entry number
	 * @param <type> $entries_count = total number of entries
	 * @param <type> $headword_text = text of the headword
	 */
	function import_xhtml_show_progress( $entry_counter, $entries_count, $headword_text, $msg = "" ) {
	
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
						info += "<?php echo " " + $headword_text; ?>";
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

	//-----------------------------------------------------------------------------//

	/**
	 * Load the search table for the entry.
	 * @param <type> $entry = XHTML of the dictionary entry
	 * @param <type> $post_id = ID of the WordPress post.
	 * @param <type> $query = the xhtml query
	 * @param <type> $relevance = weighted importance of this particular string for search results
	 */
	function import_xhtml_search( $doc, $post_id, $query, $relevance, $subid = 0 ) {

		if($relevance == ($this->headword_relevance - 5))
		{
			$subid++;
		}
		//$fields = $this->dom_xpath->query( $query, $entry );

		$xpath = new DOMXPath($doc);
		$xpath->registerNamespace('xhtml', 'http://www.w3.org/1999/xhtml');

		$fields = $xpath->query($query);
		
		foreach ( $fields as $field ) {

			$language = $field->getAttribute( "lang" );

			//if(strlen(trim($language)) == 0 && property_exists($field->childNodes->item(0), 'lang'))
			if($field->childNodes->length > 0)
			{
				if(strlen(trim($language)) == 0 && $field->childNodes->item(0)->hasAttributes())
				{
					$language = $field->childNodes->item(0)->getAttribute( "lang" );
				}
			}
				
			if(strlen(trim($language)) != 0)
			{
				$this->import_xhtml_search_string($post_id, $field->textContent, $relevance, $language, $subid);
			}
			if($subid > 0)
			{
				$subid++;
			}
		}

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
	function import_xhtml_search_string( $post_id, $search_string, $relevance, $language_code, $subid = 0) {
		global $wpdb;

		//$language_code = $field->getAttribute("lang");

		// $wdbt->prepare likes to add single quotes around string replacements,
		// and that's why I concatenated the table name.
		if(strlen(trim($search_string)) > 0)
		{
			$sql = $wpdb->prepare(
				"INSERT IGNORE INTO `". $this->search_table_name . "` (post_id, language_code, search_strings, relevance, subid)
				VALUES (%d, '%s', '%s', %d, %d)",
				$post_id, $language_code, trim($search_string), $relevance, $subid );
			
				//ON DUPLICATE KEY UPDATE search_strings = CONCAT(search_strings, ' ',  '%s');",

				$wpdb->query( $sql );
		}

		//this replaces the special apostroph with the standard apostroph
		//the first time round the special apostroph is inserted, so that both searches are valid
		if(strstr($search_string,"’"))
		{
			$mySearch_string = str_replace("’", "'", $search_string);
			$this->import_xhtml_search_string( $post_id, $mySearch_string, $relevance, $language_code, $subid);
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
				$this->pos_taxonomy,
				array(
					'description' => $pos_name,
					'slug' => $pos_name
				)
			);

			wp_set_object_terms( $post_id, $pos_name, $this->pos_taxonomy, true);
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
		
		//$entry_xml = preg_replace('/<span class="Writing_System_Abbreviation"(.*?)<\\/span>/', '', $entry_xml);
		
		$xpath = new DOMXPath($doc);
		$xpath->registerNamespace('xhtml', 'http://www.w3.org/1999/xhtml');

		/*
		if($subentry)
		{
			$semantic_domains = $xpath->query('//span[@class = "semantic-domains-sub"]//span[@class = "semantic-domain-name-sub"]');
		}
		else
		{
			//$semantic_domain_terms = $xpath->query('//span[@class = "semantic-domains"]//span[starts-with(@class, "semantic-domain-name")]');
			$semantic_domains = $xpath->query('//span[@class = "semantic-domains"]|//span[@class = "semanticdomains"]');
		}
		*/
		$semantic_domains = $xpath->query('//span[starts-with(@class, "semantic-domains")]|//span[starts-with(@class, "semanticdomains")]');

		$i = 0;
		
		$sd_numbers = null;
		
		foreach ( $semantic_domains as $semantic_domain ){
		
			$sd_names = $xpath->query('//span[starts-with(@class, "semantic-domains")]//*[starts-with(@class, "semantic-domain-name")]|//span[@class = "semanticdomains"]//span[starts-with(@class, "name")]', $semantic_domain);
			$sd_numbers = $xpath->query('//span[starts-with(@class, "semantic-domains")]//span[starts-with(@class, "semantic-domain-abbr")]|//span[@class = "semanticdomains"]//span[starts-with(@class, "abbreviation")]/span[not(@class = "writingsystemprefix")]', $semantic_domain);
			///span[not(@class = "Writing_System_Abbreviation")]
			$sc = 0;
			foreach($sd_names as $sd_name)
			{
				$semantic_domain_language = $sd_name->getAttribute("lang");
				$domain_name = str_replace("]", "", $sd_name->textContent);
				$domain_name = str_replace(")", "", $domain_name);
				
				$sd_number_text = $domain_name;

				if(isset($sd_numbers->item($sc)->textContent))
				{
					$sd_number_text = str_replace("[", "", $sd_numbers->item($sc)->textContent);
					$sd_number_text = str_replace("(", "", $sd_number_text);
					$sd_number_text = trim(str_replace("-", "", $sd_number_text));
				}
				
				$domain_class = $sd_name->getAttribute("class");

				$arrTerm = wp_insert_term(
					$domain_name,
					$this->semantic_domains_taxonomy,
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
						$terms[$i] = $termid;
						$i++;
					}
				}

				$this->convert_semantic_domains_to_links($post_id, $doc, $sd_name, $termid);

				if(isset($termid))
				{
					$wpdb->query( "INSERT INTO $wpdb->term_relationships (object_id, term_taxonomy_id) VALUES (" . $post_id . ", " . $termid . ") ON DUPLICATE KEY UPDATE term_order = VALUES(term_order)" );
				}
				/*
				if($convertToLinks == true)
				{
					$this->convert_semantic_domains_to_links($post_id, $doc, $sd_name, $termid);
				}
				else
				{
					$x = 0;
					wp_set_object_terms( $post_id, $domain_name, $this->semantic_domains_taxonomy, true );
				}
				*/
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
	function import_xhtml_reversal_indexes ($postentry = null, $entry_counter = null) {
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
			$headwords = $this->dom_xpath->query('//xhtml:*[@class = "referringsense"]/*[@class = "headword" or @class = "lexemeform"]|//xhtml:span[starts-with(@class, "headref")]');

			if($headwords->length == 0)
			{
				echo "No senses found for '" . $reversal_head . "'<br>\n";
			}
			
			if(strpos($postentry, "reversalindexentry") > 0)
			{
				$reversal_xml = preg_replace('/href="(#)([^"]+)"/', 'href="' . get_bloginfo('wpurl') . '/\\2"', $postentry);
			}
			$sql = "SELECT id FROM " . $this->reversal_table_name . " ORDER BY id+0 DESC LIMIT 0,1 ";
							
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
				$id = $lastid + $entry_counter;
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
					
					$sql = "SELECT id, reversal_head FROM " . $this->reversal_table_name;
					$sql .= " WHERE ";
					if(strpos($postentry, "reversalindexentry") > 0)
					{
						$sql .= " id = '" . $id . "' ";
					
					}
					else
						
					{
						$sql .= " reversal_head = '" . addslashes($reversal_head) . "' collate " . COLLATION . "_bin ";
					}
					$sql .= "AND language_code = '" . $reversal_language . "'";
					
					$existing_entry = $wpdb->get_var($sql);
					
					//If the reversal language is set to Chinese and
					//the extension pinyin from https://github.com/duguying/pinyin-php is installed
					//the Chinese headwords get converted to pinyin, so that the reversal browse view
					//will work
					$reversal_browsehead = $reversal_head;
					if($reversal_language == "zh-CN" && extension_loaded("pinyin"))
					{
						//the pinyin extension doesn't work correctly with brackets
						$pinyinstring = str_replace("（", " ", $reversal_head);
						$pinyinstring = str_replace("）", "", $pinyinstring);
						$pinyin = pinyin($pinyinstring);
						$reversal_browsehead = remove_accents($pinyin);
					}
					
					if($existing_entry == NULL)
					{
						$sql = $wpdb->prepare(
								"INSERT IGNORE INTO `". $this->reversal_table_name . "` (id, language_code, reversal_head, reversal_content, sortorder)
								VALUES('%s', '%s', '%s', '%s', %d)",
								$id, $reversal_language, $reversal_browsehead, $reversal_xml, $entry_counter);
					}
					else
					{
						$sql = $wpdb->prepare(
								"UPDATE " . $this->reversal_table_name . "
								SET reversal_content = '%s'
								WHERE reversal_head = '%s' AND language_code = '%s' AND $id = '%s'",
								$reversal_xml, $reversal_browsehead, $reversal_language, $id);
					}

					$wpdb->query( $sql );
					
				}
				if($post_id == 0)
				{
					echo "PostId for '" . $headword_text . "' not found.<br>";
					//we want to display the sense, not the reversal word in the "missing senses" report
					////$reversal_head = $headword_text;
				}
				$this->import_xhtml_search_string( $post_id, $reversal_head, $this->headword_relevance, $reversal_language, 0);
		
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
		$sql = "UPDATE " . $this->search_table_name . " SET sortorder = " . $entry_counter . " WHERE search_strings = '" . addslashes($headword_text) . "' COLLATE '" . COLLATION . "_BIN' AND relevance >= 95";
		$wpdb->query( $sql );
		
		//this is used for the search sort order
		$sql = "UPDATE " . $wpdb->posts . " SET menu_order = " . $entry_counter . " WHERE post_title = '" . addslashes($headword_text) . "' collate " . COLLATION . "_bin";
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
	
		$search_table_exists = $wpdb->get_var( "show tables like '$this->search_table_name'" ) == $this->search_table_name;
		$pos_taxonomy_exists = taxonomy_exists( $this->pos_taxonomy );
		$semantic_domains_taxonomy_exists = taxonomy_exists( $this->semantic_domains_taxonomy );
	
		if ( $search_table_exists ) {
			////$arrPosts = $this->get_posts('flexlinks');
			$arrPosts = $this->get_posts('-');
				
			$subid = 1;
			/*
				$sortorder = $wpdb->get_var( "
				SELECT sortorder
				FROM $this->search_table_name
				WHERE relevance >= 95 ORDER BY sortorder DESC LIMIT 0, 1");
	
				if($sortorder == null || $sortorder == 0)
				{
				$sortorder = 1;
				}
				else
				{
				$sortorder++;
				}
				*/
	
			$entry_counter = 1;
			$entries_count = count($arrPosts);
	
			update_option("useSemDomainNumbers", 0);
				
			foreach($arrPosts as $post)
			{
				$subentry = false;
				if ( $post->ID ){
					/*
						$oldSortorder = $wpdb->get_var( "SELECT sortorder FROM $this->search_table_name WHERE relevance >= 95 AND post_id = " . $post->ID . " AND sortorder <> 0");
	
						if(isset($oldSortorder))
						{
						$sortorder = $oldSortorder;
						}
						*/
	
					$sql = $wpdb->prepare("DELETE FROM `". $this->search_table_name . "` WHERE post_id = %d", $post->ID);
	
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
	
				$arrFieldQueries = $this->getArrFieldQueries(2);
	
				$headword = $xpath->query($arrFieldQueries[0])->item(0);
	
				$this->import_xhtml_show_progress( $entry_counter, $entries_count, $post->post_title, "Step 2 of 2: Indexing Search Strings");
	
				if(isset($headword) && $post->post_parent == 0)
				{
					$headword_language = $headword->getAttribute( "lang" );
					if(strlen(trim($headword_language)) == 0)
					{
						if(strlen(trim($headword_language)) == 0)
						{
							$headword_language = $headword->childNodes->item(0)->getAttribute( "lang" );
						}
					}
						
					//import headword
					$this->import_xhtml_search_string($post->ID, $headword->textContent, $this->headword_relevance, $headword_language, $subid);
					//sub headwords
					$this->import_xhtml_search($doc, $post->ID, $arrFieldQueries[1], ($this->headword_relevance - 5), $subid);
					//lexeme forms
					$this->import_xhtml_search($doc, $post->ID, $arrFieldQueries[2], $this->lexeme_form_relevance);
					//definitions
					$this->import_xhtml_search($doc, $post->ID, $arrFieldQueries[3], $this->definition_word_relevance);
					//sub definitions
					$this->import_xhtml_search($doc, $post->ID, $arrFieldQueries[4], ($this->definition_word_relevance - 5));
					//example sentences
					$this->import_xhtml_search($doc, $post->ID, $arrFieldQueries[5], $this->example_sentences_relevance);
					//Translation of example sentences
					$this->import_xhtml_search($doc, $post->ID, $arrFieldQueries[6], $this->example_sentences_relevance);
					//custom fields
					$this->import_xhtml_search($doc, $post->ID, $arrFieldQueries[7], $this->custom_field_relevance);
					//variant forms
					$this->import_xhtml_search($doc, $post->ID, $arrFieldQueries[8], $this->variant_form_relevance);
					$this->import_xhtml_search($doc, $post->ID, $arrFieldQueries[9], $this->variant_form_relevance);
					//cross references
					$this->import_xhtml_search($doc, $post->ID, $arrFieldQueries[10], $this->sense_crossref_relevance);
					//scientific names
					$this->import_xhtml_search($doc, $post->ID, $arrFieldQueries[11], $this->scientific_name);
					//plurals
					$this->import_xhtml_search($doc, $post->ID, $arrFieldQueries[12], $this->plural);
				}
				else
				{
					$subentry = true;
				}
	
				$headword_text = trim($headword->textContent);
	
				//this is used for the browse view sort order
				$sql = "UPDATE " . $this->search_table_name . " SET sortorder = " . $post->menu_order . " WHERE search_strings = '" . addslashes($headword_text) . "' COLLATE '" . COLLATION . "_BIN' AND relevance >= 95 AND sortorder = 0" ;
				$wpdb->query( $sql );
	
				//this is used for the search sort order
				/*
				$sql = "UPDATE " . $wpdb->posts . " SET menu_order = " . $sortorder . " WHERE post_title = '" . addslashes($headword_text) . "' collate utf8_bin AND menu_order = 0";
				$wpdb->query( $sql );
				*/
				/*
				 * Load semantic domains
				*/
				if ( $semantic_domains_taxonomy_exists )
				{
					$this->import_xhtml_semantic_domain($doc, $post->ID, $subentry, false);
					$this->import_xhtml_semantic_domain($doc, $post->ID, $subentry, true);
				}
				/*
				 * Load parts of speech (POS)
				 */
				if ( $pos_taxonomy_exists )
					$this->import_xhtml_part_of_speech($doc, $post->ID);
	
				if($entry_counter % 50 == 0)
				{
					////sleep(1);
				}
					
				$subid++;
				$entry_counter++;
			}
				
			update_option("importStatus", "convertlinks");
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
	
		$hasError = false;
	
		if ( !isset($_FILES[$which_file]) ) {
			$file['error'] = __( 'The file is either empty, or uploads are disabled in your php.ini, or post_max_size is defined as smaller than upload_max_filesize in php.ini.' );
			return $file;
		}
	
		$overrides = array( 'test_form' => false, 'test_type' => false );
		$file = wp_handle_upload( $_FILES[$which_file], $overrides );
	
	
		if ( isset( $file['error'] ) )
			return $file;
	
		$url = $file['url'];
		$type = $file['type'];
		$file = addslashes( $file['file'] );
		$filename = basename( $file );
	
		$info = pathinfo($file);
		$extension = $info['extension'];
	
		if($extension == "css")
		{
			$upload_dir = wp_upload_dir();
			$target_path = $upload_dir['path'] . "/imported-with-xhtml.css";
			if($filetype == "reversal")
			{
				if($reversalLang == "")
				{
					$arrLanguageCodes = get_LanguageCodes();
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
				
				$fontClass = new fontMonagment();
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
	
} // class

//===================================================================================//


/*
 * Register the importer so WordPress knows it exists. Specify the start
 * function as an entry point. Paramaters: $id, $name, $description,
 * $callback.
 */
$pathway_import = new sil_pathway_xhtml_Import();
register_importer('pathway-xhtml',
		__('SIL FLEX XHTML', 'sil_dictionary'),
		__('Import posts from an SIL FLEX XHTML file.', 'sil_dictionary'),
		array ($pathway_import, 'start'));

//} // class_exists( 'WP_Importer')


//===================================================================================//

function pathway_xhtml_importer_init(){
	/*
	 * Load the translated strings for the plugin.
	 */
    load_plugin_textdomain('sil_dictionary', false, dirname(plugin_basename(__FILE__ )) .'/lang/');
}


//===================================================================================//

/*
 * Hook the importer's init into the WordPress init.
 */
add_action('init', 'pathway_xhtml_importer_init');
