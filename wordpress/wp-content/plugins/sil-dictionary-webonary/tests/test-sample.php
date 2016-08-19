<?php
/**
 * Class SampleTest
 *
 * @package Sil_Dictionary_Webonary
 */

/**
 * Sample test case.
 */
class SampleTest extends WP_UnitTestCase {

	/**
	 * A single example test.
	 */
	function test_sample() {

		$exampleFile = "tests/test-data.xhtml";
		$xhtmlFileURL = "tests/test.xhtml";
		copy($exampleFile, $xhtmlFileURL);

		$filetype = "configured";
		$abspath = str_replace("tests", "", __DIR__);
		require($abspath . "processes/import_entries.php");
		
		$import = new sil_pathway_xhtml_Import();
		$id = $import->get_post_id('abaa');
		
		$this->assertGreaterThan(0, $id);
		
		$arrIndexed = $import->get_number_of_entries();
		$this->assertEquals(1, count($arrIndexed));
	}
}
