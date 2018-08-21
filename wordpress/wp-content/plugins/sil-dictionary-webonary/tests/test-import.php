<?php
/**
 * Class ImportTest
 *
 * @package Sil_Dictionary_Webonary
 */

/**
 * Sample test case.
 */
class ImportTest extends WP_UnitTestCase {

	function test_convert_fieldworks_audio_to_wordpress() {
		$this->assertEquals("en", "en");
	}

	function test_convert_fieldworks_images_to_wordpress()
	{
		$entry = '<div  xmlns="http://www.w3.org/1999/xhtml" class="entry" id="g05e110fb-fb35-42e5-bd3d-7ae9a7d03989"> <span class="mainheadword"> <span lang="txo-Qaaa-x-Toto"> <a href="#g05e110fb-fb35-42e5-bd3d-7ae9a7d03989">abc</a> </span> <span lang="txo-Latn-fonipa-x-emic"> <a href="#g05e110fb-fb35-42e5-bd3d-7ae9a7d03989">dingbako layrung</a> </span> <span lang="txo-Beng-fonipa-x-emic"> <a href="#g05e110fb-fb35-42e5-bd3d-7ae9a7d03989">দিংবাকো লায়রুং</a> </span> </span> <span class="senses"> <span class="sharedgrammaticalinfo"> <span class="morphosyntaxanalysis"> <span class="partofspeech"> <span lang="en">n</span> </span> </span> </span> <span class="sensecontent"> <span class="sense" entryguid="g05e110fb-fb35-42e5-bd3d-7ae9a7d03989"> <span class="gloss"> <span lang="en">dragonfly</span> </span> </span> </span> </span> <span class="pictures"> <div class="picture"> <img class="thumbnail" src="pictures/AOR_4-3r.png" id="gf2e6b06b-7433-41b5-a071-38af8b37fd83"/> </div> </span> </div>';

		$doc = new DomDocument();
		$doc->preserveWhiteSpace = false;
		$doc->loadXML($entry);

		$import = new sil_pathway_xhtml_Import();
		$import->dom_xpath = new DOMXPath($doc);
		$import->dom_xpath->registerNamespace('xhtml', 'http://www.w3.org/1999/xhtml');

		$converted = $import->convert_fieldworks_images_to_wordpress($doc);
	}
	/*
	function test_import() {

		$exampleFile = "tests/test-configured.xhtml";
		$xhtmlFileURL = "tests/test.xhtml";
		//copy test data to test folder
		copy($exampleFile, $xhtmlFileURL);

		$filetype = "configured";
		$abspath = str_replace("tests", "", __DIR__);

		require($abspath . "processes/import_entries.php");

		//import entry "abadaw"
		$import = new sil_pathway_xhtml_Import();

		//test if postid for abaa gets found after import
		$id = $import->get_post_id('abadaw');
		$this->assertGreaterThan(0, $id);

		//test if FLExID gets imported into post_name field
		$id2 = $import->get_post_id('gd7350bd4-b0eb-4d29-8f32-a6f1ef70fc2a');
		$this->assertGreaterThan(0, $id2);

		//test if number of indexed entries equals 1
		$arrIndexed = $import->get_number_of_entries();
		$this->assertEquals(2, $arrIndexed[0]->totalIndexed);

		//test if languagecode option got set
		$this->assertEquals("msb", get_option("languagecode"));

		$arrPosts = $import->get_posts("linksconverted");

		//check if can find link to the synonym adaw
		$postSynLinkPos = strpos($arrPosts[0]->post_content, '<a href="http://example.org/g15713a4a-ddbb-47a5-991b-88b10c25d1db">adaw</a>');
		$this->assertGreaterThan(0, $postSynLinkPos);


		///////////////////
		// IMPORT REVERSAL
		//////////////////

		$exampleFile = "tests/test-reversal-en.xhtml";
		$xhtmlFileURL = "tests/test-reversal.xhtml";
		//copy test data to test folder
		copy($exampleFile, $xhtmlFileURL);

		$filetype = "reversal";
		$abspath = str_replace("tests", "", __DIR__);
		require($abspath . "processes/import_entries.php");

		//import entry "abadaw"
		$import = new sil_pathway_xhtml_Import();

		$arrIndexed = $import->get_number_of_entries();
		$this->assertEquals(1, $arrIndexed[0]->totalIndexed);
		$this->assertEquals("en", $arrIndexed[0]->language_code);

		//////////////////////
		// CHECK CONFIGURATION
		//////////////////////

		update_option("IncludeCharactersWithDiacritics", 1);

		$browseview = vernacularalphabet_func(null);

		$doc = new DomDocument();
		$doc->preserveWhiteSpace = false;
		$doc->loadHTML($browseview);

		$xpath = new DOMXPath($doc);
		//$xpath->registerNamespace('xhtml', 'http://www.w3.org/1999/xhtml');

		$headwords = $xpath->query('//div[@class="entry"]/span[@class="mainheadword"]')->item(0);

		echo $headwords->textContent;

	}
	*/
}
