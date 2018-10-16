<?php
if(exec('echo EXEC') == 'EXEC' && file_exists($argv[1] . "exec-configured.txt") && isset($argv))
{
	define('WP_INSTALLING', true);



	require($argv[1] . "wp-load.php");
	switch_to_blog($argv[2]);
	require($argv[1] . "wp-content/plugins/sil-dictionary-webonary/include/infrastructure.php");
	install_sil_dictionary_infrastructure();

	require($argv[1] . "wp-content/plugins/sil-dictionary-webonary/include/xhtml-importer.php");

	//it isn't actually from the api, but saves us renaming the variable to "background" or something like that...
	$api = true;
	$verbose = true;
	$filetype = $argv[3];
	//remove numbers from string
	if(substr($filetype, 0, 8) == "reversal")
	{
		$filetype = "reversal";
	}
	$xhtmlFileURL = $argv[4];
	$userid = $argv[5];
}
else
{
	$api = false;
	$verbose = false;
}
global $wpdb;

if(isset($xhtmlFileURL))
{
	$path_parts = pathinfo($xhtmlFileURL);

	$uploadPath = $path_parts['dirname'];

	$import = new sil_pathway_xhtml_Import();

	$import->api = $api;
	$import->verbose = $verbose;

	$reader = new XMLReader;
	$reader->open($xhtmlFileURL);


	update_option("hasComposedCharacters", 0);
	update_option("importStatus", $filetype);

	/*
	 * Import
	 */

	$import->search_table_name = $wpdb->prefix . 'sil_search';

	$current_user = wp_get_current_user();

	if ( $filetype== 'configured' || $filetype == 'stem' || $filetype == 'reversal')
	{
		echo "Starting Import\n";

		$time_pre = microtime(true);

		$sql = "SELECT menu_order
		FROM $wpdb->posts
		INNER JOIN " . $wpdb->prefix . "term_relationships ON object_id = ID
			ORDER BY menu_order DESC
			LIMIT 0,1";

		$menu_order = $wpdb->get_var($sql);

		if($menu_order == NULL)
		{
			$menu_order = 0;
		}

		$header = "";
		$isHead = false;
		$letter = "";
		$letterLanguage = "";

		$postentry = "";
		$isEntry = false;
		$entry_counter = 1;

		/*
		 *
		 * Load the configured post entries
		 */
		while ($reader->read() && $reader->name !== 'head');

		if ($reader->name === 'head')
		{
			//$reader->read();
			$header = $reader->readOuterXml();
			$import->import_xhtml_writing_systems($header);
		}

		//while ($reader->read() && $reader->getAttribute("class") !== 'letData');

		//while ($reader->getAttribute("class") === 'letData')
		$arrLetters = array();
		$a = 0;
		$isNewFLExExport = true;
		while( $reader->read() )
		{
			//while ($reader->read() && $reader->getAttribute("class") !== 'entry' && $reader->getAttribute("class") !== 'minorentry');

			while($reader->getAttribute("class") == 'letter')
			{
				$letterHead = $reader->readInnerXml();
				$letterLanguage = $reader->getAttribute("lang");

				//if($letterHead != "?")
				//{
					if(strpos($letterHead, " ") > 0)
					{
						$letters = explode(" ", $letterHead);

						$letter = $letters[1];
					}
					else
					{
						$letter = $letterHead;
					}

					if (!in_array($letter, $arrLetters)) {
						$arrLetters[$a] = $letter;
						$a++;
					}
				//}
				$reader->next("div");
			}

			while ($reader->getAttribute("class") === 'entry' || $reader->getAttribute("class") === 'mainentrycomplex' || $reader->getAttribute("class") === 'reversalindexentry' || $reader->getAttribute("class") === 'minorentry' || $reader->getAttribute("class") === 'minorentryvariant' || $reader->getAttribute("class") === 'minorentrycomplex')
			{
				$postentry =  $reader->readOuterXml();

				if($entry_counter == 1)
				{
					$id = $reader->getAttribute("id");

					if(strlen($id) < 10 && $isNewFLExExport == true)
					{
						$isNewFLExExport = false;
						echo "<span style=\"color:red; font-weight:bold;\">Older FLEx xhtml exports are no longer supported by the Webonary importer. Please upgrade to FLEx 8.3.</span><br>";
						flush();
					}

				}
				//$reader->next("div");

				if(trim($postentry) != "" && $isNewFLExExport == true)
				{
					if($filetype == 'stem')
					{
						$entry_counter = $import->import_xhtml_stem_indexes($postentry, $entry_counter);
						if(strlen($letterLanguage) > 0)
						{
							update_option("languagecode", $letterLanguage);
						}
					}
					elseif($filetype == 'reversal')
					{
						$import->reversal_table_name = $wpdb->prefix . 'sil_reversals';
						$entry_counter = $import->import_xhtml_reversal_indexes($postentry, $entry_counter, $letter);
					}
					else
					{
						//filetype = configured
						$entry_counter = $import->import_xhtml_entries($postentry, $entry_counter, $menu_order, $isNewFLExExport, $letter);
						if(strlen($letterLanguage) > 0)
						{
							update_option("languagecode", $letterLanguage);
						}
					}

					$menu_order++;
				}

				$reader->next("div");
			}
		}

		$alphabet = "";
		$s = 1;
		foreach($arrLetters as $l)
		{
			$alphabet .= $l;
			if($s < count($arrLetters))
			{
				$alphabet .= ",";
			}
			$s++;
		}

		if($entry_counter == 1)
		{
			echo "<div style=color:red>ERROR: No entries found.</div><br>";
			//return;
		}

		$time_post = microtime(true);
		$exec_time = $time_post - $time_pre;

		echo $exec_time . "<br>";
	}

	$headers = array(
			'From: Webonary <webonary@sil.org>'
	);
	if($filetype == "configured")
	{
		/*
		similar_text(get_option('vernacular_alphabet'), $alphabet, $perSimilarAlphabet);

		if($perSimilarAlphabet < 60)
		{
			update_option("vernacular_alphabet", $alphabet);
		}
		*/
		update_option("vernacular_alphabet", $alphabet);

		update_option("totalConfiguredEntries", ($entry_counter - 1));

		update_option("importStatus", "indexing");

		$import->index_searchstrings();

		$message = "The import of the vernacular (configured) xhtml export is completed.\n";
		$message .= "Go here to configure more settings: " . get_site_url() . "/wp-admin/admin.php?page=webonary";

		wp_mail( $current_user->user_email, 'Import complete', $message, $headers);

		echo "Import finished\n";
	}
	elseif($filetype == "stem")
	{
		echo "Import finished\n";
	}
	elseif ( $filetype == 'reversal')
	{
		//reversal1_langcode
		if(isset($_POST['languagecode']))
		{
			$reversalLang = $_POST['languagecode'];
		}
		else
		{
			$reversalLang = str_replace($uploadPath . "/reversal_", "", $xhtmlFileURL);
			$reversalLang = str_replace(".xhtml", "", $reversalLang);
		}

		$reversalAlphabetOption = "reversal1_alphabet";
		if(get_option('reversal1_langcode') != $reversalLang)
		{
			$reversalAlphabetOption = "reversal2_alphabet";

			if(get_option('reversal2_langcode') != $reversalLang)
			{
				$reversalAlphabetOption = "reversal3_alphabet";
			}
		}
		similar_text(get_option($reversalAlphabetOption), $alphabet, $perSimilarAlphabet);

		if($perSimilarAlphabet < 60)
		{
			update_option($reversalAlphabetOption, $alphabet);
		}

		update_option("importStatus", "importFinished");
		//$import->index_reversals();

		$message = "The reversal import is completed.\n";
		$message .= "Go here to configure more settings: " . get_site_url() . "/wp-admin/admin.php?page=webonary";

		wp_mail( $current_user->user_email, 'Reversal Import complete', $message, $headers);
	}


	$file = $import->get_latest_xhtmlfile();

	if(isset($file))
	{
		if(substr($file->url, strlen($file->url) - 5, 5) == "xhtml")
		{
			wp_delete_attachment( $file->ID );
			error_log("deleted attachment: " . $file->url);
		}
	}
	else
	{
		//file is inside extracted zip directory
		unlink($xhtmlFileURL);
		$unlinkedFile = $xhtmlFileURL;
		error_log("unlinked: " . $xhtmlFileURL);

		error_log("Upload Path: " . $uploadPath . "\n");

		$files = scandir ( $uploadPath );
		$arrReversals = null;
		$x = 0;
		foreach ( $files as $file )
		{
			if (substr($file, 0, 9) == "reversal_" && substr($file, strlen($file) - 5, 5) == "xhtml")
			{
				error_log("reversal file: " . $file . "\n");
				$arrReversals[$x] = $file;
				$x++;
			}
		}
		if($arrReversals != null)
		{
			$fileReversal1 = $uploadPath . "/" . $arrReversals[0]; //str_replace("configured.xhtml", $arrReversals[0], $xhtmlFileURL);
			error_log("fileReversal1: " . $fileReversal1);
			$xhtmlReversal1 = null;
			if(file_exists($fileReversal1))
			{
				$xhtmlReversal1 = file_get_contents($fileReversal1);
			}

			if(isset($xhtmlReversal1))
			{
				$filetype = str_replace(".xhtml", "", $arrReversals[0]);
				$xhtmlFileURL = $fileReversal1;
				error_log($filetype . "#" . $xhtmlFileURL);

				require(ABSPATH . "wp-content/plugins/sil-dictionary-webonary/include/run_import.php");
			}
		}
		else
		{
			error_log("Export completed.\n");

			$user_info = get_userdata($userid);
			$email = $user_info->user_email;

			$message = "The export to Webonary is completed.\n";
			$message .= "Go here to configure more settings: " . get_site_url() . "/wp-admin/admin.php?page=webonary";
			try
			{
				error_log("Email sent to " . $email);
				$headers = 'From: Webonary <webonary@sil.org>' . "\r\n";
				mail($email, 'Webonary Export completed', $message, $headers);
			}
			catch(Exception $e) {
				error_log("Error: " . $e->getMessage());
			}

		}
	}
}
?>