<?php
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection SqlResolve */
/**
 * Class ImportTest
 *
 * @package Sil_Dictionary_Webonary
 */

/**
 * Sample test case.
 */
class SearchTest extends WP_UnitTestCase
{
	function test_Search_Sangesari_ComposedCharacters()
	{
		update_option('importStatus', 'configured');

		$entry_xml = '<div xmlns="http://www.w3.org/1999/xhtml" class="entry" id="g6d6ec840-6075-4b3a-9958-b445c9bc02d5"><span class="mainheadword"><span lang="sgr"><a href="#g6d6ec840-6075-4b3a-9958-b445c9bc02d5">ärt</a></span><span lang="sgr-Xpeo-IR"><a href="#g6d6ec840-6075-4b3a-9958-b445c9bc02d5">آرت</a></span></span><span class="senses"><span class="sharedgrammaticalinfo"><span class="morphosyntaxanalysis"><span class="partofspeech"><span lang="en">n.</span></span></span></span><span class="sensecontent"><span class="sense" entryguid="g6d6ec840-6075-4b3a-9958-b445c9bc02d5"><span class="gloss"><span lang="en">flour</span><span lang="fa"><span dir="rtl">آرد</span></span></span></span></span></span></div>';

		$import = new Webonary_Pathway_Xhtml_Import();
		$import->import_xhtml_entries($entry_xml, 0, 0, true);

		update_option('importStatus', 'indexing');
		$import->index_searchstrings();

		update_option('importStatus', 'importFinished');
		update_option('searchSomposedCharacters', 1);

		$arrPosts = query_posts('s=ärt');
		$this->assertEquals(1, count($arrPosts));
		$this->assertEquals("ärtآرت", $arrPosts[0]->post_title);

		//find without Umlaut
		$arrPosts = query_posts('s=art');
		$this->assertEquals(count($arrPosts), 1);
		$this->assertEquals($arrPosts[0]->post_title, "ärtآرت");

		// NOTE: The "distinguish_diacritics" option has been removed in configuration.php, so this test is no longer valid
//		//match accent and tones shouldn't find without Umlaut
//		home_url($wp->request);
//		$arrPosts = query_posts('s=art&search=Search&key=&tax=-1&match_accents=on&displayAdvancedSearch=1');
//		$this->assertEquals(0, count($arrPosts));

		$arrPosts = query_posts('s=flour');
		$this->assertEquals("ärtآرت", $arrPosts[0]->post_title);

		//matching whole word, no result
		$arrPosts = query_posts( 's=flou&match_whole_words=1');
		$this->assertEquals(0, count($arrPosts));

		//matching whole word, has result
		$arrPosts = query_posts('s=flour&match_whole_words=1');
		$this->assertEquals(1, count($arrPosts));
		$this->assertEquals("ärtآرت", $arrPosts[0]->post_title);

		$arrPosts = query_posts('s=cake');
		$this->assertEquals(0, count($arrPosts));
	}

	function test_Search_Nafaanra()
	{
		/** @var WP $wp */
		global $wp;

		update_option('importStatus', 'configured');

		$entry_xml  = '<div xmlns="http://www.w3.org/1999/xhtml" class="entry" id="gc0a52176-c8fc-4376-b889-0b475a6fe70c"><span class="mainheadword"><span lang="nfr"><a href="#gc0a52176-c8fc-4376-b889-0b475a6fe70c">abani</a></span></span><span class="senses"><span class="sharedgrammaticalinfo"><span class="morphosyntaxanalysis"><span class="partofspeech"><span lang="en">n</span></span></span></span><span class="sensecontent"><span class="sense" entryguid="gc0a52176-c8fc-4376-b889-0b475a6fe70c"><span class="definitionorgloss"><span lang="en">government</span></span><span class="examplescontents"><span class="examplescontent"><span class="example"><span lang="nfr">Ala Ghana abani tia sro pan titi.</span></span><span class="translationcontents"><span class="translationcontent"><span class="translation"><span lang="en">This year there is food help from the Government.</span></span></span></span></span></span></span></span></span></div>';
		$entry_xml2 = '<div xmlns="http://www.w3.org/1999/xhtml" class="entry" id="g167f9b2d-aee4-4a19-80db-65b5ceedf7f2"><span class="mainheadword"><span lang="nfr"><a href="#g167f9b2d-aee4-4a19-80db-65b5ceedf7f2">aliire</a></span></span><span class="senses"><span class="sharedgrammaticalinfo"><span class="morphosyntaxanalysis"><span class="partofspeech"><span lang="en">n</span></span></span></span><span class="sensecontent"><span class="sense" entryguid="g167f9b2d-aee4-4a19-80db-65b5ceedf7f2"><span class="definitionorgloss"><span lang="en">food</span></span><span class="examplescontents"><span class="examplescontent"><span class="example"><span lang="nfr">Nyiɛkpɔɔ u o kaan o blênyini aliire.</span></span><span class="translationcontents"><span class="translationcontent"><span class="translation"><span lang="en">God gives us our daily food.</span></span></span></span></span></span></span></span></span></div>';

		$import = new Webonary_Pathway_Xhtml_Import();
		$import->import_xhtml_entries($entry_xml, 0, 0, true);
		$import->import_xhtml_entries($entry_xml2, 0, 0, true);

		update_option('importStatus', 'indexing');
		$import->index_searchstrings();

		update_option('searchSomposedCharacters', 0);

		//matching partial word
		$arrPosts = query_posts( 's=bani');
		$this->assertEquals(1, count($arrPosts));
		$this->assertEquals("abani", $arrPosts[0]->post_title);

		//matching whole word, no result
		$arrPosts = query_posts( 's=bani&match_whole_words=1');
		$this->assertEquals(0, count($arrPosts));

		//matching whole word, has result
		$arrPosts = query_posts( 's=abani&match_whole_words=1');
		$this->assertEquals(1, count($arrPosts));
		$this->assertEquals("abani", $arrPosts[0]->post_title);

		//find without Umlaut
		$arrPosts = query_posts( 's=blenyini');
		$this->assertEquals(1, count($arrPosts));
		$this->assertEquals("aliire", $arrPosts[0]->post_title);

		//match accent and tones shouldn't find without Umlaut
		home_url($wp->request);
		$arrPosts = query_posts( 's=blenyini&search=Search&key=&tax=-1&match_accents=on&displayAdvancedSearch=1');
		$this->assertEquals(0, count($arrPosts));


		//two results
		$arrPosts = query_posts( 's=food');
		$this->assertEquals(2, count($arrPosts));
		$this->assertEquals("aliire", $arrPosts[0]->post_title);
		//should return this as second, since word is in example sentence, so result comes lower down
		$this->assertEquals("abani", $arrPosts[1]->post_title);
	}

}
