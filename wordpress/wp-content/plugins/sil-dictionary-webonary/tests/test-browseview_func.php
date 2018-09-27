<?php
/**
 * Class ImportTest
 *
 * @package Sil_Dictionary_Webonary
 */

/**
 * Sample test case.
 */
class BrowseviewFuncTest extends WP_UnitTestCase {

	function test_getReversalEntries()
	{
		global $wpdb;

		$entry_xml = '<div class="entry" id="g1b499aaf-5080-486a-ab08-6be3a95acc42"><span class="mainheadword"><span lang="ill"><a href="http://webonary.localhost/lubwisi/g1b499aaf-5080-486a-ab08-6be3a95acc42">aig</a></span></span><span class="senses"><span class="sharedgrammaticalinfo"><span class="morphosyntaxanalysis"><span class="partofspeech"><span lang="en">n</span></span></span></span><span class="sensecontent"><span class="sense" entryguid="g1b499aaf-5080-486a-ab08-6be3a95acc42"><span class="definition"><span class="writingsystemprefix">Eng</span><span lang="en">water</span><span class="writingsystemprefix">Mal</span><span lang="ms">air</span></span></span></span></span></div>';
		$headword_text = "aig";
		$post_parent = 0;
		$flexid = "g1b499aaf-5080-486a-ab08-6be3a95acc42";

		$sql = $wpdb->prepare(
				"INSERT INTO ". $wpdb->posts . " (post_date, post_title, post_content, post_status, post_parent, post_name, comment_status, menu_order, post_content_filtered, pinged)
				VALUES (NOW(), '%s', '%s', 'publish', %d, '%s', '%s', %d, '%s', '%s')",
				trim($headword_text), $entry_xml, $post_parent, $flexid, "open", 19, "a", "indexed");

		$wpdb->query( $sql );

		$post_id = $wpdb->insert_id;

		$sql = "INSERT INTO ". SEARCHTABLE . " (post_id, language_code, relevance, search_strings, subid, sortorder) VALUES " .
				"(" . $post_id . ", 'en', 50, 'water', 0, 0), " .
				"(" . $post_id . ", 'ill', 100, 'aig', 1, 19), " .
				"(" . $post_id . ", 'ms', 50, 'air', 0, 0)";

		$wpdb->query( $sql );

		$displayXHTML = true;
		$arrReversals = getReversalEntries("w", 1, "en", $displayXHTML, 1);

		$this->assertEquals(count($arrReversals), 0);

		$sql = "INSERT INTO ". SEARCHTABLE . " (post_id, language_code, relevance, search_strings, subid, sortorder) VALUES " .
				"(" . $post_id . ", 'en', 100, 'water', 0, 0)";

		$wpdb->query( $sql );
		update_option('languagecode', 'ill');

		$arrReversals = getReversalEntries("w", 1, "en", $displayXHTML, 1);

		$this->assertEquals(count($arrReversals), 1);
		$this->assertEquals($displayXHTML, false);

		$sql = "INSERT INTO " . REVERSALTABLE . " (id, language_code, reversal_head, reversal_content, sortorder, browseletter) VALUES
		('ga9766f67-d1f7-4eec-', 'en', 'water', '<div xmlns=\"http://www.w3.org/1999/xhtml\" class=\"reversalindexentry\" id=\"ga9766f67-d1f7-4eec-b9e7-a31fa2a018e5\"><span class=\"reversalform\"><span lang=\"en\">water</span></span><span class=\"referringsenses\"><span class=\"sensecontent\"><span class=\"referringsense\" entryguid=\"g1b499aaf-5080-486a-ab08-6be3a95acc42\"><span class=\"headword\"><span lang=\"ill\"><a href=\"http://webonary.localhost/lubwisi/g1b499aaf-5080-486a-ab08-6be3a95acc42\">aig</a></span></span></span></span></span></div>', 532, 'w')";

		$wpdb->query( $sql );

		$displayXHTML = true;
		$arrReversals = getReversalEntries("w", 1, "en", $displayXHTML, 1);

		$this->assertEquals(count($arrReversals), 1);
		$this->assertEquals($displayXHTML, true);

		$arrReversals = getReversalEntries("w", 1, "ms", $displayXHTML, 2);
		$this->assertEquals(count($arrReversals), 0);
	}

}
