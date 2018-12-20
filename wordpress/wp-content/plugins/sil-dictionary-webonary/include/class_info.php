<?php
class Info
{
	public static function category_id() {
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

	public static function import_status() {
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
				$status .= "Tip: You can hide the domain numbers from displaying, <a href=\" https://www.webonary.org/help/tips-tricks/\" target=_\"blank\">see here</a>.";
				$status .= "<hr>";
			}
		}

		$catid = Info::category_id();

		if($catid == NULL)
		{
			$catid = 0;
		}

		$arrPostCount = Info::postCountByImportStatus($catid);

		$arrReversalsImported = Info::reversalPosts();

		$arrIndexed = Info::number_of_entries();

		if(count($arrPostCount) > 0)
		{
			$countIndexed = 0;
			$totalImportedPosts = count(Info::posts());

			foreach($arrPostCount as $posts)
			{
				if($posts->pinged == "indexed")
				{
					$countIndexed = $posts->entryCount;
				}
				else
				{

					$countImported = $posts->entryCount;
				}
			}

			if(!get_option("importStatus"))
			{
				return "The import status will display here.<br>";
			}

			if(get_option("importStatus") == "importFinished")
			{
				if($posts->post_date != NULL)
				{
					$status .= "Last import of configured xhtml was at " . $posts->post_date . " (server time)";
					$status .= "<br>";

				}
			}
			else
			{
				$status .= "Importing... <a href=\"" . $_SERVER['REQUEST_URI']  . "\">refresh page</a><br>";
				$status .= " You will receive an email when the import has completed. You don't need to stay online.";
				$status .= "<br>";
			}

			if(get_option("importStatus") == "indexing")
			{
				$status .= "Indexing " . $countIndexed . " of " . $totalImportedPosts . " entries";

				$status .= "<br>If you believe indexing has timed out, click here: <input type=\"submit\" name=\"btnReindex\" value=\"Index Search Strings\"/>";
				return $status;
			}
			if(get_option("importStatus") == "configured")
			{
				$status .= $countImported . " entries imported (not yet indexed)";

				if($arrPostCount[0]->timediff > 5)
				{
					$status .= "<br>It appears the import has timed out, click here: <input type=\"submit\" name=\"btnRestartImport\" value=\"Restart Import\">";
				}
				return $status;

			}
			if(get_option("importStatus") == "reversal")
			{
				$status .= "<strong>Importing reversals. So far imported: " . count($arrReversalsImported) . " entries.</strong>";

				$status .= "<br>If you believe the import has timed out, click here: <input type=\"submit\" name=\"btnRestartReversalImport\" value=\"Restart Reversal Import\">";
				return $status;
			}

			if(count($arrIndexed) > 0 && ($countIndexed == $totalImportedPosts))
			{
				$status .= "<br>";
				$status .= "<div style=\"float: left;\">";
				$status .= "<strong>Number of indexed entries (by language code):</strong><br>";
				$status .= "</div>";
				$status .= "<div style=\"min-width:50px; float: left; margin-left: 5px;\">";

				$status .= Info::reversalsMissing($arrIndexed, $arrReversalsImported);

				$status .= "</div>";
				$status .= "<br style=\"clear:both;\">";

				return $status;
			}
		}

		if(count($arrPostCount) == 0)
		{
			return "No entries have been imported yet. <a href=\"" . $_SERVER['REQUEST_URI']  . "\">refresh page</a>";
		}
	}

	public static function number_of_entries()
	{
		global $wpdb;

		//gets the language codes for all entries plus number of indexed entries
		//(number of reversal entries is not exact, which is why we get reversal entries eeparate)
		$sql = " SELECT language_code, COUNT(post_id) AS totalIndexed " .
		" FROM " . Config::$search_table_name.
		" WHERE relevance = 100 " .
		" GROUP BY language_code ";

		$arrIndexed = $wpdb->get_results($sql);

		$sql = " SELECT language_code, COUNT(language_code) AS totalIndexed " .
				" FROM " . Config::$reversal_table_name .
				" INNER JOIN $wpdb->terms ON $wpdb->terms.slug = " . Config::$reversal_table_name . ".language_code " .
				" GROUP BY language_code " .
				" ORDER BY name ASC";

		$arrReversals = $wpdb->get_results($sql);

		$r = 0;
		$s = 0;
		foreach($arrIndexed as $indexed)
		{

			$sqlLangName = "SELECT name as language_name " .
					" FROM $wpdb->terms " .
					" WHERE slug = '" . $indexed->language_code . "'";

			$language_name = $wpdb->get_var($sqlLangName);

			$arrIndexed[$r]->language_name = $language_name;

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
			$count_posts = count(Info::posts(''));
			foreach($arrIndexed as $indexed)
			{
				$sql = " SELECT search_strings " .
						" FROM " . Config::$search_table_name .
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

	public static function posts($index = ""){
		global $wpdb;

		// @todo: If $headword_text has a double quote in it, this
		// will probably fail.
		$sql = "SELECT ID, post_title, post_content, post_parent, menu_order " .
		" FROM $wpdb->posts " .
		" INNER JOIN " . $wpdb->prefix . "term_relationships ON object_id = ID " .
		" WHERE " . $wpdb->prefix . "term_relationships.term_taxonomy_id = " . Info::category_id();
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

	public static function postCountByImportStatus($catid)
	{
		global $wpdb;

		$sql = "SELECT COUNT(pinged) AS entryCount, post_date, TIMESTAMPDIFF(SECOND, MAX(post_date),NOW()) AS timediff, pinged FROM " . $wpdb->prefix . "posts " .
				" WHERE post_type IN ('post', 'revision') AND " .
				" ID IN (SELECT object_id FROM " . $wpdb->prefix . "term_relationships WHERE " . $wpdb->prefix . "term_relationships.term_taxonomy_id = " . $catid .") " .
				" GROUP BY pinged " .
				" ORDER BY post_date DESC";

		$arrPostCount = $wpdb->get_results($sql);

		return $arrPostCount;
	}

	public static function reversalsMissing($arrIndexed, $arrReversalsImported)
	{
		global $wpdb;

		$status = "";
		foreach($arrIndexed as $indexed)
		{
			$status .= "<div style=\"clear:both;\"><div style=\"text-align:right; float:left;\"><nobr>" . $indexed->language_code . ":</nobr></div><div style=\"float:left;\">&nbsp;". $indexed->totalIndexed;

			array_filter($arrReversalsImported, function($el) { return $el->post_id == 0; });

			$sql = "SELECT COUNT(language_code) AS missing " .
					" FROM " . Config::$search_table_name .
					" WHERE post_id = 0 AND language_code = '" . $indexed->language_code . "'" .
					" GROUP BY language_code";

			$missingReversals = $wpdb->get_var($sql);

			if($missingReversals > 0)
			{
				$status .= " <a href=\"edit.php?page=sil-dictionary-webonary/include/configuration.php&reportMissingSenses=1&languageCode=" . $indexed->language_code . "&language=" . $indexed->language_name . "\" style=\"color:red;\">missing senses for " . $missingReversals . " entries</a>";
			}

			$status .= "</div></div>";
		}
		return $status;
	}

	public static function reversalPosts()
	{
		global $wpdb;

		$sql = " SELECT * " .
				" FROM " . Config::$reversal_table_name;

		$arrReversalsImported = $wpdb->get_results($sql);

		return $arrReversalsImported;
	}

}