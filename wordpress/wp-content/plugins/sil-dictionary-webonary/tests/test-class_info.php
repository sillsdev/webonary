<?php
/**
 * Class Test Info
 *
 * @package Sil_Dictionary_Webonary
 */

class InfoTest extends WP_UnitTestCase {

	function test_Import_Status()
	{
		update_option("importStatus", "configured");

		$entry_xml = '<div xmlns="http://www.w3.org/1999/xhtml" class="entry" id="g6d6ec840-6075-4b3a-9958-b445c9bc02d5"><span class="mainheadword"><span lang="sgr"><a href="#g6d6ec840-6075-4b3a-9958-b445c9bc02d5">ärt</a></span><span lang="sgr-Xpeo-IR"><a href="#g6d6ec840-6075-4b3a-9958-b445c9bc02d5">آرت</a></span></span><span class="senses"><span class="sharedgrammaticalinfo"><span class="morphosyntaxanalysis"><span class="partofspeech"><span lang="en">n.</span></span></span></span><span class="sensecontent"><span class="sense" entryguid="g6d6ec840-6075-4b3a-9958-b445c9bc02d5"><span class="gloss"><span lang="en">flour</span><span lang="fa"><span dir="rtl">آرد</span></span></span></span></span></span></div>';

		$import = new sil_pathway_xhtml_Import();

		$status = Webonary_Info::import_status();

		$this->assertContains("No entries have been imported yet.", $status);

		$import->import_xhtml_entries($entry_xml, 0, 0, true);
		$status = Webonary_Info::import_status();

		$this->assertContains("1 entries imported", $status);
		$this->assertContains("Importing...", $status);

		update_option("importStatus", "indexing");
		$status = Webonary_Info::import_status();

		$this->assertContains("Indexing 0 of 1 entries", $status);
		$this->assertContains("btnReindex", $status);

		$import->index_searchstrings();
		update_option("importStatus", "importFinished");
		$status = Webonary_Info::import_status();

		$this->assertContains("Last import of configured xhtml was", $status);
		$this->assertContains("Number of indexed entries", $status);
		$this->assertContains("sgr-Xpeo-IR", $status);
		$this->assertNotContains("Importing...", $status);
	}

	function test_postCountByImportStatus($assert = true)
	{
		$entry_xml .= '<div xmlns="http://www.w3.org/1999/xhtml" class="entry" id="gc0a52176-c8fc-4376-b889-0b475a6fe70c"><span class="mainheadword"><span lang="nfr"><a href="#gc0a52176-c8fc-4376-b889-0b475a6fe70c">abani</a></span></span><span class="senses"><span class="sharedgrammaticalinfo"><span class="morphosyntaxanalysis"><span class="partofspeech"><span lang="en">n</span></span></span></span><span class="sensecontent"><span class="sense" entryguid="gc0a52176-c8fc-4376-b889-0b475a6fe70c"><span class="definitionorgloss"><span lang="en">government</span></span><span class="examplescontents"><span class="examplescontent"><span class="example"><span lang="nfr">Ala Ghana abani tia sro pan titi.</span></span><span class="translationcontents"><span class="translationcontent"><span class="translation"><span lang="en">This year there is food help from the Government.</span></span></span></span></span></span></span></span></span></div>';
		$entry_xml2 = '<div xmlns="http://www.w3.org/1999/xhtml" class="entry" id="g167f9b2d-aee4-4a19-80db-65b5ceedf7f2"><span class="mainheadword"><span lang="nfr"><a href="#g167f9b2d-aee4-4a19-80db-65b5ceedf7f2">aliire</a></span></span><span class="senses"><span class="sharedgrammaticalinfo"><span class="morphosyntaxanalysis"><span class="partofspeech"><span lang="en">n</span></span></span></span><span class="sensecontent"><span class="sense" entryguid="g167f9b2d-aee4-4a19-80db-65b5ceedf7f2"><span class="definitionorgloss"><span lang="en">food</span></span><span class="examplescontents"><span class="examplescontent"><span class="example"><span lang="nfr">Nyiɛkpɔɔ u o kaan o blenyini aliire.</span></span><span class="translationcontents"><span class="translationcontent"><span class="translation"><span lang="en">God gives us our daily food.</span></span></span></span></span></span></span></span></span></div>';

		$import = new sil_pathway_xhtml_Import();
		$import->import_xhtml_entries($entry_xml, 0, 0, true);
		$import->import_xhtml_entries($entry_xml2, 0, 0, true);

		$arrPostCount = Webonary_Info::postCountByImportStatus(Webonary_Info::category_id());

		if($assert)
		{
			$this->assertEquals(2, $arrPostCount[0]->entryCount);
			$this->assertEquals("", $arrPostCount[0]->pinged);
		}

		$import->index_searchstrings();
		if($assert)
		{
			$this->assertEquals("", $arrPostCount[0]->indexed);
		}
	}
	function test_reversalPosts()
	{
		$this->test_postCountByImportStatus();

		$import = new sil_pathway_xhtml_Import();

		$reversal_xml = '<div xmlns="http://www.w3.org/1999/xhtml" class="reversalindexentry" id="gc9f30d9e-d675-492d-9179-f8c2bacfd95c"><span class="reversalform"><span lang="en">government</span></span><span class="referringsenses"><span class="sensecontent"><span class="referringsense" entryguid="gc0a52176-c8fc-4376-b889-0b475a6fe70c"><span class="headword"><span lang="nfr"><a href="#gc0a52176-c8fc-4376-b889-0b475a6fe70c">abani</a></span></span></span></span></span></div>';
		$import->import_xhtml_reversal_indexes($reversal_xml);

		$arrReversalsImported = Webonary_Info::reversalPosts();

		$this->assertEquals(1, count($arrReversalsImported));
		$this->assertEquals("en", $arrReversalsImported[0]->language_code);
		$this->assertEquals("government", $arrReversalsImported[0]->reversal_head);
	}

	function test_reversalsMissig()
	{
		$entry_xml2 = '<div xmlns="http://www.w3.org/1999/xhtml" class="entry" id="g167f9b2d-aee4-4a19-80db-65b5ceedf7f2"><span class="mainheadword"><span lang="nfr"><a href="#g167f9b2d-aee4-4a19-80db-65b5ceedf7f2">aliire</a></span></span><span class="senses"><span class="sharedgrammaticalinfo"><span class="morphosyntaxanalysis"><span class="partofspeech"><span lang="en">n</span></span></span></span><span class="sensecontent"><span class="sense" entryguid="g167f9b2d-aee4-4a19-80db-65b5ceedf7f2"><span class="definitionorgloss"><span lang="en">food</span></span><span class="examplescontents"><span class="examplescontent"><span class="example"><span lang="nfr">Nyiɛkpɔɔ u o kaan o blenyini aliire.</span></span><span class="translationcontents"><span class="translationcontent"><span class="translation"><span lang="en">God gives us our daily food.</span></span></span></span></span></span></span></span></span></div>';

		$import = new sil_pathway_xhtml_Import();
		$import->import_xhtml_entries($entry_xml2, 0, 0, true);
		$import->index_searchstrings();

		$reversal_xml = '<div xmlns="http://www.w3.org/1999/xhtml" class="reversalindexentry" id="gc9f30d9e-d675-492d-9179-f8c2bacfd95c"><span class="reversalform"><span lang="en">government</span></span><span class="referringsenses"><span class="sensecontent"><span class="referringsense" entryguid="gc0a52176-c8fc-4376-b889-0b475a6fe70c"><span class="headword"><span lang="nfr"><a href="#gc0a52176-c8fc-4376-b889-0b475a6fe70c">abani</a></span></span></span></span></span></div>';
		$import->import_xhtml_reversal_indexes($reversal_xml);

		$arrIndexed = Webonary_Info::number_of_entries();
		$arrReversalsImported = Webonary_Info::reversalPosts();

		$status = Webonary_Info::reversalsMissing($arrIndexed, $arrReversalsImported);
		$this->assertContains("missing senses for 1 entries", $status);
	}


}
