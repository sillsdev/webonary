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

	/**
	 * A single example test.
	 */
	function test_import() {

		$exampleFile = "tests/test-data.xhtml";
		$xhtmlFileURL = "tests/test.xhtml";
		//copy test data to test folder
		copy($exampleFile, $xhtmlFileURL);

		$filetype = "configured";
		$abspath = str_replace("tests", "", __DIR__);
		require($abspath . "processes/import_entries.php");
		
		//import entry "abaa"
		$import = new sil_pathway_xhtml_Import();
		
		//test if postid for abaa gets found after import
		$id = $import->get_post_id('abaa');
		$this->assertGreaterThan(0, $id);
		
		//test if FLExID gets imported into post_name field
		$id2 = $import->get_post_id('g881f7e4d-fe89-4229-8016-a425b12465fc');
		$this->assertGreaterThan(0, $id2);
		
		//test if number of indexed entries equals 1
		$arrIndexed = $import->get_number_of_entries();
		$this->assertEquals(1, count($arrIndexed));

		//test if languagecode option got set
		$this->assertEquals("msb", get_option("languagecode"));
	}
}
