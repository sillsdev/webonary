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
	}
}
