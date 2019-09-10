<?php
/**
 * Class ImportTest
 *
 * @package Sil_Dictionary_Webonary
 */

/**
 * Sample test case.
 */
class SearchTest extends WP_UnitTestCase {

	function test_Search_Sangesari_ComposedCharacters()
	{
		global $wp, $wp_query, $wpdb;

		$wp_http = new WP_Http();

		$entry_xml = '<div xmlns="http://www.w3.org/1999/xhtml" class="entry" id="g6d6ec840-6075-4b3a-9958-b445c9bc02d5"><span class="mainheadword"><span lang="sgr"><a href="#g6d6ec840-6075-4b3a-9958-b445c9bc02d5">ärt</a></span><span lang="sgr-Xpeo-IR"><a href="#g6d6ec840-6075-4b3a-9958-b445c9bc02d5">آرت</a></span></span><span class="senses"><span class="sharedgrammaticalinfo"><span class="morphosyntaxanalysis"><span class="partofspeech"><span lang="en">n.</span></span></span></span><span class="sensecontent"><span class="sense" entryguid="g6d6ec840-6075-4b3a-9958-b445c9bc02d5"><span class="gloss"><span lang="en">flour</span><span lang="fa"><span dir="rtl">آرد</span></span></span></span></span></span></div>';

		$import = new sil_pathway_xhtml_Import();
		$import->import_xhtml_entries($entry_xml, 0, 0, true);
		$import->index_searchstrings();

		update_option("searchSomposedCharacters", 1);

		$arrPosts = query_posts( 's=ärt');
		$this->assertEquals(count($arrPosts), 1);
		$this->assertEquals($arrPosts[0]->post_title, "ärtآرت");

		//find without Umlaut
		$arrPosts = query_posts( 's=art');
		$this->assertEquals(count($arrPosts), 1);
		$this->assertEquals($arrPosts[0]->post_title, "ärtآرت");

		//match accent and tones shouldn't find without Umlaut
		$url = home_url( $wp->request );
		$arrPosts = query_posts( 's=art&search=Search&key=&tax=-1&match_accents=on&displayAdvancedSearch=1');
		$this->assertEquals(count($arrPosts), 0);

		$arrPosts = query_posts( 's=flour');
		$this->assertEquals($arrPosts[0]->post_title, "ärtآرت");

		//matching whole word, no result
		$arrPosts = query_posts( 's=flou&match_whole_words=1');
		$this->assertEquals(count($arrPosts), 0);

		//matching whole word, has result
		$arrPosts = query_posts( 's=flour&match_whole_words=1');
		$this->assertEquals(count($arrPosts), 1);
		$this->assertEquals($arrPosts[0]->post_title, "ärtآرت");


		$arrPosts = query_posts( 's=cake');
		$this->assertEquals(count($arrPosts), 0);
	}

	function test_Search_Nafaanra()
	{
		/** @var WP $wp */
		global $wp;

		$entry_xml  = '<div xmlns="http://www.w3.org/1999/xhtml" class="entry" id="gc0a52176-c8fc-4376-b889-0b475a6fe70c"><span class="mainheadword"><span lang="nfr"><a href="#gc0a52176-c8fc-4376-b889-0b475a6fe70c">abani</a></span></span><span class="senses"><span class="sharedgrammaticalinfo"><span class="morphosyntaxanalysis"><span class="partofspeech"><span lang="en">n</span></span></span></span><span class="sensecontent"><span class="sense" entryguid="gc0a52176-c8fc-4376-b889-0b475a6fe70c"><span class="definitionorgloss"><span lang="en">government</span></span><span class="examplescontents"><span class="examplescontent"><span class="example"><span lang="nfr">Ala Ghana abani tia sro pan titi.</span></span><span class="translationcontents"><span class="translationcontent"><span class="translation"><span lang="en">This year there is food help from the Government.</span></span></span></span></span></span></span></span></span></div>';
		$entry_xml2 = '<div xmlns="http://www.w3.org/1999/xhtml" class="entry" id="g167f9b2d-aee4-4a19-80db-65b5ceedf7f2"><span class="mainheadword"><span lang="nfr"><a href="#g167f9b2d-aee4-4a19-80db-65b5ceedf7f2">aliire</a></span></span><span class="senses"><span class="sharedgrammaticalinfo"><span class="morphosyntaxanalysis"><span class="partofspeech"><span lang="en">n</span></span></span></span><span class="sensecontent"><span class="sense" entryguid="g167f9b2d-aee4-4a19-80db-65b5ceedf7f2"><span class="definitionorgloss"><span lang="en">food</span></span><span class="examplescontents"><span class="examplescontent"><span class="example"><span lang="nfr">Nyiɛkpɔɔ u o kaan o blênyini aliire.</span></span><span class="translationcontents"><span class="translationcontent"><span class="translation"><span lang="en">God gives us our daily food.</span></span></span></span></span></span></span></span></span></div>';

		$import = new sil_pathway_xhtml_Import();
		$import->import_xhtml_entries($entry_xml, 0, 0, true);
		$import->import_xhtml_entries($entry_xml2, 0, 0, true);
		$import->index_searchstrings();

		//matching partial word
		$arrPosts = query_posts( 's=bani');
		$this->assertEquals(count($arrPosts), 1);
		$this->assertEquals($arrPosts[0]->post_title, "abani");

		//matching whole word, no result
		$arrPosts = query_posts( 's=bani&match_whole_words=1');
		$this->assertEquals(count($arrPosts), 0);

		//matching whole word, has result
		$arrPosts = query_posts( 's=abani&match_whole_words=1');
		$this->assertEquals(count($arrPosts), 1);
		$this->assertEquals($arrPosts[0]->post_title, "abani");

		//find without Umlaut
		$arrPosts = query_posts( 's=blenyini');
		$this->assertEquals(count($arrPosts), 1);
		$this->assertEquals($arrPosts[0]->post_title, "aliire");

		//match accent and tones shouldn't find without Umlaut
		$url = home_url( $wp->request );
		$arrPosts = query_posts( 's=blenyini&search=Search&key=&tax=-1&match_accents=on&displayAdvancedSearch=1');
		$this->assertEquals(count($arrPosts), 0);


		//two resuls
		$arrPosts = query_posts( 's=food');
		$this->assertEquals(count($arrPosts), 2);
		$this->assertEquals($arrPosts[0]->post_title, "aliire");
		//should return this as second, since word is in example sentence, so result comes lower down
		$this->assertEquals($arrPosts[1]->post_title, "abani");
	}

}
