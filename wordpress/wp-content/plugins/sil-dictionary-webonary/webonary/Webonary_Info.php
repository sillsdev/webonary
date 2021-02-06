<?php /** @noinspection SqlResolve */


class Webonary_Info
{
	public static function getCountIndexed()
	{
		$counts = self::postCountByImportStatus();
		return (int)(empty($counts->indexed_count) ? 0 : $counts->indexed_count);
	}

	public static function getCountImported()
	{
		$counts = self::postCountByImportStatus();
		return (int)(empty($counts->unindexed_count) ? 0 : $counts->unindexed_count);
	}

	/**
	 * @return string
	 */
	public static function import_status()
	{
		global $wpdb;

		$counts = self::postCountByImportStatus();

		if($counts->total_count == 0)
			return 'No entries have been uploaded yet. <a href="' . $_SERVER['REQUEST_URI']  . '">refresh page</a>';

		$import_status = get_option('importStatus');

		if(empty($import_status))
			return 'The upload status will display here.<br>';

		$status = '';

		$countReversals = self::getCountReversals();
		$arrIndexed = self::number_of_entries();
		$countIndexed = self::getCountIndexed();
		$countImported = self::getCountImported();

		if($import_status == 'importFinished')
		{
			if(get_option('useSemDomainNumbers') != 0)
			{
				/** @noinspection SqlResolve */
				$sql = "SELECT COUNT(taxonomy) AS sdCount FROM {$wpdb->prefix}term_taxonomy WHERE taxonomy = 'sil_semantic_domains'";

				$sdCount = $wpdb->get_var($sql);

				if(empty($sdCount))
				{
					$status .= '<br>';
					$status .= '<span style="color:red;">It appears you uploaded semantic domains without the domain numbers. Please go to Tools -> Configure -> Dictionary.. in FLEx and check "Abbreviation" under Senses/Semantic Domains.</span><br>';
					$status .= 'Tip: You can hide the domain numbers from displaying, <a href=" https://www.webonary.org/help/tips-tricks/" target=_"blank">see here</a>.';
					$status .= '<hr>';
				}
			}

			if(!empty($counts->indexed_date))
			{
				$status .= 'Last upload of configured xhtml was at ' . $counts->indexed_date . ' (GMT).<br>';
				$status .= 'Download data sent from FLEx: ';

				$archiveFile = Webonary_Cloud::getBlogDictionaryId() . '.zip';

				if(file_exists(WP_CONTENT_DIR . '/archives/' . $archiveFile))
					$status .= '<a href="/wp-content/archives/' . $archiveFile . '">' . $archiveFile . '</a>';
				else
					$status .= 'no longer available';

				$status .= '<br>';
			}
		}
		else
		{
			$status .= 'Uploading...';
			$status .= '<p>You will receive an email when the upload has completed. You don\'t need to stay online.</p>';
		}

		if($import_status == 'indexing')
		{
			$percent = (int)ceil(($countIndexed / $countImported) * 100);
			if ($percent > 100)
				$percent = 100;

			$status .= 'Indexing <span id="sil-count-indexed" class="sil-bold">' . $countIndexed . '</span> of <span class="sil-bold">' . self::getPostCount() . '</span> entries' . PHP_EOL;
			$status .= '<br><progress id="sil-index-progress" max="100" value="' . $percent . '"></progress>';
			$status .= '<p id="timed-out-msg" style="display: none">If you believe indexing has timed out, click here: <input style="margin-left:8px" class="button button-webonary" type="button" name="btnReindex" id="btnReindex" value="Index Search Strings" onclick="RestartIndexing();"></p>';

			return $status;
		}

		if($import_status == 'configured')
		{
			$status .= '<span id="sil-count-imported" class="sil-bold">' . $countImported . '</span> entries uploadeded (not yet indexed)';

			if($counts->time_diff > 5)
			{
				$status .= '<p>It appears the upload has timed out, click here: <input style="margin-left:8px" class="button button-webonary" type="submit" name="btnRestartImport" value="Restart Upload" formaction="admin.php?import=pathway-xhtml&step=2"></p>';
			}
			return $status;
		}

		if($import_status == 'reversal')
		{
			$status .= 'Uploading reversals. So far uploaded: <span id="sil-count-imported" class="sil-bold">' . $countReversals . '</span> entries.';
			$status .= '<p>If you believe the upload has timed out, click here: <input style="margin-left:8px" class="button button-webonary" type="submit" name="btnRestartReversalImport" value="Restart Reversal Upload" formaction="admin.php?import=pathway-xhtml&step=2"></p>';
			return $status;
		}

		if($import_status == 'importFinished')
		{
			$status .= '<br>';
			$status .= '<div style="float: left;">';
			$status .= '<strong>Number of indexed entries (by language code):</strong><br>';
			$status .= '</div>';
			$status .= '<div style="min-width:50px; float: left; margin-left: 5px;">';
			$status .= self::reversalsMissing($arrIndexed);
			$status .= '</div>';
			$status .= '<br style="clear:both;">';
		}

		return $status;
	}

	public static function number_of_entries()
	{
		global $wpdb;

		//gets the language codes for all entries plus number of indexed entries
		//(number of reversal entries is not exact, which is why we get reversal entries separately)
		$table_name = Webonary_Configuration::$search_table_name;
		/** @noinspection SqlResolve */
		$sql = <<<SQL
SELECT language_code, COUNT(post_id) AS totalIndexed
FROM {$table_name}
WHERE relevance = 100
GROUP BY language_code
SQL;

		$arrIndexed = $wpdb->get_results($sql);

		$table_name = Webonary_Configuration::$reversal_table_name;
		/** @noinspection SqlResolve */
		$sql = <<<SQL
SELECT r.language_code, COUNT(r.language_code) AS totalIndexed
FROM {$table_name} AS r
GROUP BY r.language_code
ORDER BY r.language_code
SQL;

		$arrReversals = $wpdb->get_results($sql);
		foreach($arrReversals as $reversal) {
			$reversals[$reversal->language_code] = $reversal->totalIndexed;
		}

		foreach($arrIndexed as $key => $indexed)
		{
			/** @noinspection SqlResolve */
			$sqlLangName = <<<SQL
SELECT `name`
FROM {$wpdb->terms}
WHERE slug = '{$indexed->language_code}'
ORDER BY name, slug
LIMIT 1
SQL;

			$language_name = $wpdb->get_var($sqlLangName);
			$arrIndexed[$key]->language_name = $language_name;

			if(isset($reversals[$indexed->language_code]))
				$arrIndexed[$key]->totalIndexed = $reversals[$indexed->language_code];
		}

		//legacy code, to count approximate number of reversals before we imported reversal entries into
		//the reversal table
		if(count($arrReversals) == 0 && count($arrIndexed) > 0)
		{
			$table_name = Webonary_Configuration::$search_table_name;
			$char_set = MYSQL_CHARSET;
			$count_posts = count(self::posts(''));
			foreach($arrIndexed as $key => $indexed)
			{
				if($count_posts != $indexed->totalIndexed && ($count_posts + 1) != $indexed->totalIndexed)
				{
					/** @noinspection SqlResolve */
					$sql = <<<SQL
SELECT COUNT(DISTINCT search_strings)
FROM {$table_name} 
WHERE language_code = '{$indexed->language_code}'
AND relevance >= 95
SQL;
					$arrIndexed[$key]->totalIndexed = $wpdb->get_var($sql);
				}
			}
		}

		return $arrIndexed;
	}

	public static function posts($index = '')
	{
		global $wpdb;

		$args = [];

		$sql = <<<SQL
SELECT p.ID 
FROM {$wpdb->prefix}posts AS p
  INNER JOIN {$wpdb->term_relationships} AS r ON p.ID = r.object_id
  INNER JOIN {$wpdb->term_taxonomy} AS x ON r.term_taxonomy_id = x.term_taxonomy_id
  INNER JOIN {$wpdb->terms} AS t ON x.term_id = t.term_id
WHERE t.slug = 'webonary'
  AND p.post_status = 'publish'
SQL;

		if(strlen($index) > 0 && $index != '-')
		{
			$sql .= ' AND pinged = %s';
			$args[] = $index;
		}
		if($index == '-')
		{
			$sql .= ' AND pinged = \'\'';
		}

		$sql .= ' ORDER BY menu_order ASC';

		if (!empty($args))
			$sql = $wpdb->prepare($sql, $args);

		return $wpdb->get_results($sql);
	}

	public static function getPostCount($index = '')
	{
		global $wpdb;

		$args = [];

		$sql = <<<SQL
SELECT COUNT(*)
FROM {$wpdb->prefix}posts AS p
  INNER JOIN {$wpdb->term_relationships} AS r ON p.ID = r.object_id
  INNER JOIN {$wpdb->term_taxonomy} AS x ON r.term_taxonomy_id = x.term_taxonomy_id
  INNER JOIN {$wpdb->terms} AS t ON x.term_id = t.term_id
WHERE t.slug = 'webonary'
  AND p.post_status = 'publish'
SQL;

		if(strlen($index) > 0 && $index != '-')
		{
			$sql .= ' AND pinged = %s';
			$args[] = $index;
		}
		if($index == '-')
		{
			$sql .= ' AND pinged = \'\'';
		}


		if (!empty($args))
			$sql = $wpdb->prepare($sql, $args);

		return (int)$wpdb->get_var($sql);
	}

	public static function getPost($post_id)
	{
		global $wpdb;

		$sql = <<<SQL
SELECT ID, post_title, post_content, post_parent, menu_order 
FROM {$wpdb->posts} AS p
WHERE ID = %s
SQL;

		$sql = $wpdb->prepare($sql, $post_id);
		return $wpdb->get_row($sql);
	}

	public static function getNextPost()
	{
		global $wpdb;

		$sql = <<<SQL
SELECT p.ID, p.post_title, p.post_content, p.post_parent, p.menu_order 
FROM {$wpdb->posts} AS p
    INNER JOIN {$wpdb->term_relationships} AS r ON p.id = r.object_id
    INNER JOIN {$wpdb->term_taxonomy} AS x ON r.term_taxonomy_id = x.term_taxonomy_id
    INNER JOIN {$wpdb->terms} AS t ON x.term_id = t.term_id
WHERE t.slug = 'webonary' 
  AND p.post_status = 'publish'
  AND p.pinged = ''
ORDER BY p.ID
LIMIT 1;
SQL;
		return $wpdb->get_row($sql);
	}

	/**
	 * @return IIndexedCounts
	 */
	public static function postCountByImportStatus()
	{
		global $wpdb;

		/**
		 * Note: Because of how WordPress unit tests work, using the category ID here does not
		 *       return the correct results. During unit testing, the $wpdb connection runs
		 *       inside an explicit transaction and query results are not actually committed
		 *       to the database.
		 *
		 * See: https://make.wordpress.org/core/handbook/testing/automated-testing/writing-phpunit-tests/#database
		 *
		 */
		$sql = <<<SQL
SELECT SUM(IF(p.pinged IN ('indexed', 'linksconverted'), 1, 0)) AS indexed_count,
       MAX(IF(p.pinged IN ('indexed', 'linksconverted'), post_date, NULL)) AS indexed_date,
       SUM(IF(p.pinged IN ('indexed', 'linksconverted'), 0, 1)) AS unindexed_count,
       MAX(IF(p.pinged IN ('indexed', 'linksconverted'), NULL, post_date)) AS unindexed_date,
       COUNT(*) AS total_count,
       TIMESTAMPDIFF(SECOND, MAX(p.post_date),NOW()) AS time_diff
FROM {$wpdb->prefix}posts AS p
  INNER JOIN {$wpdb->term_relationships} AS r ON p.ID = r.object_id
  INNER JOIN {$wpdb->term_taxonomy} AS x ON r.term_taxonomy_id = x.term_taxonomy_id
  INNER JOIN {$wpdb->terms} AS t ON x.term_id = t.term_id
WHERE p.post_type IN ('post', 'revision')
  AND t.slug = 'webonary';
SQL;

		/** @var IIndexedCounts $post_counts */
		$post_counts = $wpdb->get_row($sql);

		return $post_counts;
	}

	public static function reversalsMissing($arrIndexed)
	{
		global $wpdb;

		$status = "";
		foreach($arrIndexed as $indexed)
		{
			$status .= '<div style="clear:both;"><div style="text-align:right;float:left;white-space:nowrap">' . $indexed->language_code . ':</div><div style="float:left;">&nbsp;'. $indexed->totalIndexed;

			$table_name = Webonary_Configuration::$search_table_name;
			/** @noinspection SqlResolve */
			$sql = <<<SQL
SELECT COUNT(language_code) AS missing
FROM {$table_name}
WHERE post_id = 0 AND language_code = '{$indexed->language_code}'
SQL;

			$missingReversals = $wpdb->get_var($sql);

			// This feature was removed in:  v. 8.3.5 15 Oct 2019 removed missing senses link
			// if($missingReversals > 0)
			// $status .= ' <a href="edit.php?page=sil-dictionary-webonary/include/configuration.php&reportMissingSenses=1&languageCode=' . $indexed->language_code . '&language=' . $indexed->language_name . '" style="color:red;">missing senses for ' . $missingReversals . ' entries</a>';

			$status .= '</div></div>';
		}
		return $status;
	}

	public static function getCountReversals()
	{
		global $wpdb;

		$table_name = Webonary_Configuration::$reversal_table_name;

		/** @noinspection SqlResolve */
		$sql = <<<SQL
SELECT COUNT(*) 
FROM {$table_name}
SQL;

		return $wpdb->get_var($sql);
	}
}
