<?php
/** @noinspection PhpMissingParamTypeInspection */

/**
 * @param $alphas
 * @param string $languageCode
 * @param boolean $rtl
 *
 * @return string
 */
function displayAlphabet($alphas, $languageCode, $rtl): string
{
	global $wpdb;

	$dir_class = $rtl ? 'rtl' : 'ltr';
	$no_alphas = (empty($alphas) || trim($alphas[0]) == '');

	if ($no_alphas && is_front_page())
		return '';

	$permalink = '';
	if (is_front_page()) {
		/** @noinspection SqlResolve */
		$sql = "SELECT ID FROM $wpdb->posts WHERE post_content LIKE '%[vernacularalphabet]%'";
		$post_id = $wpdb->get_var($sql);

		$permalink = get_permalink($post_id);
	}

	/** @noinspection HtmlUnknownTarget */
	$template = <<<HTML
<div class="lpTitleLetterCell">
	<span>
	<a class="%s" href="%s">%s</a>
	</span>
</div>
HTML;

	if ($no_alphas) {
		$content = sprintf($template, 'not-configured', get_site_url() . '/wp-admin/admin.php?page=webonary#browse', 'Alphabet not configured');
	}
	else {
		$content = '';

		$permalink = explode('?', $permalink)[0];

		$query_vars = $_GET ?? [];
		$query_vars['key'] = $languageCode;

		// remove page numbers from the new link, we always want to show the first page
		if (isset($query_vars['pagenr']))
			unset($query_vars['pagenr']);

		if (isset($query_vars['totalEntries']))
			unset($query_vars['totalEntries']);

		foreach ($alphas as $letter) {

			$query_vars['letter'] = $letter;
			$content .= sprintf($template, 'lpTitleLetter', $permalink . '?' . http_build_query($query_vars), stripslashes($letter));
		}
	}

	$font_family = get_option('vernacularLettersFont') ?: 'inherit';

	return <<<HTML
<style>
	#chosenLetterHead, .lpTitleLetter {font-family: $font_family}
</style>
<br>
<div style="text-align:center;">
	<div style="display:inline-block;" class="$dir_class">
		$content
	</div>
</div>
<div style=clear:both></div>
HTML;
}

function displayPageNumbers($chosenLetter, $totalEntries, $entriesPerPage, $languageCode, $requestName = null, $currentPage = null): string
{
	if(!isset($requestName))
		$requestName = 'letter';

	if(!$currentPage)
		$currentPage = Webonary_Utility::getPageNumber();

	$totalPages = ceil($totalEntries / $entriesPerPage);
	if(($totalEntries / $entriesPerPage) > $totalPages)
		$totalPages++;

	if ($totalPages <= 1)
		return '';


	$next_page = '&gt;';
	$prev_page = '&lt;';

	$url = '?' . $requestName . '=' . urlencode($chosenLetter) . '&key=' . $languageCode . '&totalEntries=' . $totalEntries;
	if(isset($_GET['lang']))
		$url .= '&lang=' . $_GET['lang'];


	$items = [];
	$limit_pages = 10;
	$items[] = '<li class="page_info">' . __('Page', 'sil_dictionary') . ' ' . $currentPage . ' ' . __('of', 'sil_dictionary') . ' ' . $totalPages . '</li>';



	if($currentPage > 1)
	{
		if($requestName == 'semnumber')
			$items[] = '<li><a href="?semdomain=' . $languageCode . '&semnumber=' . $chosenLetter . '&pagenr=' . ($currentPage - 1) . '">' . $prev_page . '</a></li>';
		else
			$items[] = '<li><a href="' . $url . '&pagenr=' . ($currentPage - 1) . '">' .$prev_page . '</a></li>';
	}

	$start = 1;
	if($currentPage > ($limit_pages - 5))
	{
		if($requestName == 'semnumber')
			$items[] = '<li><a href="?semdomain=' . $languageCode . '&semnumber=' . $chosenLetter . '&pagenr=1">1</a></li>';
		else
			$items[] = '<li><a href="' . $url . '&pagenr=1">1</a></li>';

		$items[] = '<li class="space">...</li>';
		$start = $currentPage - 5;
		if($currentPage == 6)
			$start = 2;
	}

	for($page = $start; $page <= $totalPages; $page++)
	{
		$class = '';
		if($currentPage == $page || ($page == 1 && !isset($currentPage)))
			$class='class="active_page"';

		if($requestName == 'semnumber')
			$items[] = "<li $class ><a href=\"?semdomain=$languageCode&semnumber=$chosenLetter&pagenr=$page\"></a></li>";
		else
			$items[] = "<li $class><a href=\"$url&pagenr=$page\">$page</a></li>";

		$minusPages = 5;
		if($currentPage < 5)
			$minusPages = $currentPage;

		if(($currentPage + $limit_pages - $minusPages) == $page && ($currentPage + $limit_pages) < $totalPages)
		{
			$items[] = '<li class="space">...</li>';
			$items[] = "<li $class><a href=\"$url&pagenr=$totalPages\">$totalPages</a></li>";
			break;
		}
	}

	if( $currentPage != '' && $currentPage < $totalPages)
	{
		$next_page_num = $currentPage + 1;
		if($requestName == 'semnumber')
			$items[] = "<li><a href=\"?semdomain=$languageCode&semnumber=$chosenLetter&pagenr=$next_page_num\"></a></li>";
		else
			$items[] = "<li><a href=\" $url&pagenr=$next_page_num\">$next_page</a></li>";
	}

	$item_str = implode(PHP_EOL, $items);

	return <<<HTML
<div style="text-align:center;">
<div style="display:inline-block;">
	<div id="wp_page_numbers">
		<ul>
$item_str
		</ul>
	</div>
</div>
</div>
HTML;
}

/**
 * @return bool
 */
function get_has_reversal_browseletters(): bool
{
	global $wpdb;

	$result = $wpdb->get_results("SHOW COLUMNS FROM " . REVERSALTABLE . " LIKE 'browseletter'");

	return count($result) > 0;
}

/**
 * Returns a MySQL LIMIT statement
 *
 * @param int|null $page
 * @param int|null $postsPerPage
 *
 * @return string
 */
function getLimitSql($page=null, $postsPerPage=null): string
{
	if (is_null($page))
		$page = Webonary_Utility::getPageNumber();

	if (is_null($postsPerPage))
		$postsPerPage = Webonary_Utility::getPostsPerPage();

	$startFrom = ($page > 1) ? (($page - 1) * $postsPerPage) : 0;
	return " LIMIT $postsPerPage OFFSET $startFrom";
}

/**
 * @param string $letter
 * @param int $page
 * @param string $reversalLangcode
 * @param bool $displayXHTML
 * @param string $reversalnr
 * @param int|null $postsPerPage
 *
 * @return mixed
 * @noinspection PhpMixedReturnTypeCanBeReducedInspection
 */
function getReversalEntries($letter, $page, $reversalLangcode, &$displayXHTML, $reversalnr, $postsPerPage = null): mixed
{
	if(strlen($reversalLangcode) === 0 && $reversalnr > 0)
	{
		return null;
	}

	global $wpdb;

	$postsPerPage = $postsPerPage ?? Webonary_Utility::getPostsPerPage();
	$limitSql = getLimitSql($page, $postsPerPage);

	$result = $wpdb->get_var('SHOW COLUMNS FROM '. REVERSALTABLE . ' LIKE \'sortorder\'');
	$sortorderExists = !is_null($result);

	$alphabet = str_replace(',', '', get_option('reversal' . $reversalnr . '_alphabet'));
	$collate = 'COLLATE ' . MYSQL_CHARSET . '_BIN';

	$sql = 'SELECT SQL_CALC_FOUND_ROWS reversal_content ' .
	' FROM ' . REVERSALTABLE  .
	' WHERE 1 = 1 ';
	if($letter != '')
	{
		//new imports use the letter header from FLEx for grouping
		if(get_has_reversal_browseletters())
		{
			$sql .= " AND browseletter =  '" . $letter . "' " . $collate;
		}
		else
		{
			if((!preg_match('/[^a-z]/', $alphabet)))
			{
				$collate = "";
			}

			$sql .= " AND reversal_head LIKE  '" . $letter . "%' " . $collate;
		}
	}
	if(strlen($reversalLangcode) > 0)
	{
		$sql .=	" AND language_code = '" . $reversalLangcode . "' ";
	}
	if($sortorderExists && !Webonary_Configuration::use_pinyin($reversalLangcode))
	{
		$sql .= " ORDER BY sortorder, reversal_head ASC";
	}
	else
	{
		$sql .= " ORDER BY reversal_head ASC";
	}
	$sql .= $limitSql;

	$arrReversals = $wpdb->get_results($sql);

	if(count($arrReversals) == 0)
	{
		//just get headwords (legacy code, as we didn't use to display the xhtml for reversals)
		$sql = "SELECT SQL_CALC_FOUND_ROWS a.post_id, a.search_strings AS English, b.search_strings AS Vernacular " .
		" FROM " . SEARCHTABLE . " a ";
		$sql .= " INNER JOIN " . SEARCHTABLE. " b ON a.post_id = b.post_id "; // AND a.subid = b.subid " .
		//$sql .= " INNER JOIN " . SEARCHTABLE. " b ON a.post_name = b.post_name ";
		if(strlen($reversalLangcode) > 0)
		{
			$sql .= " AND a.language_code =  '" . $reversalLangcode . "' " .
			" AND b.language_code = '" . get_option('languagecode') . "' ";
		}
		$sql .= " AND a.relevance >=95 AND b.relevance >= 95 " .
		" AND a.search_strings LIKE  '" . $letter . "%' " . $collate .
		" GROUP BY a.post_id, a.search_strings " .
		" ORDER BY a.search_strings ";

		$sql .= $limitSql;

		$arrReversals = $wpdb->get_results($sql);
		if(count($arrReversals) > 0)
		{
			$displayXHTML = false;
		}

	}

	return $arrReversals;
}

/**
 * @param $display
 * @param $chosenLetter
 * @param $langcode
 * @param $reversalnr
 * @return string
 * @throws Exception
 */
function reversalindex($display, $chosenLetter, $langcode, $reversalnr = ""): string
{
	global $wpdb;

	$rtl = get_option('reversal' . $reversalnr . 'RightToLeft') == '1';
	$align_class = $rtl ? 'right' : 'left';

	$page_num = Webonary_Utility::getPageNumber();
	$postsPerPage = Webonary_Utility::getPostsPerPage();
	$displayXHTML = true;

	if(IS_CLOUD_BACKEND)
	{
		Webonary_Cloud::registerAndEnqueueReversalStyles($langcode);

		$apiParams = array(
			'text' => $chosenLetter,
			'lang' => $langcode,
			'entryType' => 'reversalindexentry',
			'pageNumber' => $page_num,
			'pageLimit' => $postsPerPage
		);

		$totalEntries = $_GET['totalEntries'] ?? Webonary_Cloud::getTotalCount(Webonary_Cloud::$doBrowseByLetter, $apiParams);
		$arrReversals = Webonary_Cloud::getEntriesAsReversals($apiParams);
	}
	else
	{
		$upload_dir = wp_upload_dir();
		//wp_register_style('reversal_stylesheet', '/files/reversal_' . $langcode . '.css?time=' . date("U"));
		$reversalCSSFile = 'reversal_' . $langcode . '.css';
		if(!file_exists($upload_dir['baseurl'] . '/' . $reversalCSSFile))
		{
			$reversalCSSFile = str_replace('-', '_', $reversalCSSFile);
		}

		wp_register_style('reversal_stylesheet', $upload_dir['baseurl'] . '/' . $reversalCSSFile . '?time=' . date("U"));
		wp_enqueue_style( 'reversal_stylesheet');

		$arrReversals = getReversalEntries($chosenLetter, $page_num, $langcode, $displayXHTML, $reversalnr, $postsPerPage);
		$totalEntries = $_GET['totalEntries'] ?? $wpdb->get_var('SELECT FOUND_ROWS()');
	}

	if($arrReversals == null)
	{
		$display .= "No reversal entries imported.";
		return $display;
	}

	$display .= '<h1 class="center">' . $chosenLetter . '</h1><br>';

	if($displayXHTML)
	{
		$display .=  sprintf('<div id="searchresults" class="%s">', $align_class);
	}
	else
	{
		$display .=  sprintf('<div id="searchresults" class="reversal-results%s">', $reversalnr);
	}

	$background = "even";
	$count = 0;
	$previousEnglishWord = '';
	foreach($arrReversals as $reversal)
	{
		if($displayXHTML)
		{
			$display .= "<div class=post>" . $reversal->reversal_content . "</div>";
		}
		else
		{
			$display .=  "<div id=searchresult class=" . $background . " style=\"clear:both;\">";

			$display .=  "<div id=englishcol>";
			if($reversal->English != $previousEnglishWord)
			{
				$display .=  $reversal->English;
				$previousEnglishWord = $reversal->English;
			}
			$display .=  "</div>";

			$url = "?p=" . trim($reversal->post_id);
			$display .=  "<div id=vernacularcol><a href=\"" . get_bloginfo('wpurl') . "/" . $url  . "\">" . $reversal->Vernacular . "</a></div>";
			$display .=  "</div>";

			if($background == "even")
			{
				$background = "odd";
			}
			else
			{
				$background = "even";
			}
		}
		$count++;
		if($count == $postsPerPage)
		{
			break;
		}
	}

	$display .=  "<div style=clear:both></div>";
	$display .= displayPageNumbers($chosenLetter, $totalEntries, $postsPerPage, $langcode);

	$display .=  "</div><br>";

	if(isset($_GET['p']))
	{
		if($_GET['p'] == 0)
		{
			$display = "<p>" . __('Entry not found', 'sil_dictionary');
		}
	}

	return $display;
}

function getNoLetters($chosenLetter, $alphas): string
{
	//if for example somebody searches for "k", but there is also a letter 'kp' in the alphabet then
	//words starting with kp should not appear
	$noLetters = "";
	foreach($alphas as $alpha)
	{
		$alpha = trim($alpha);

		if($chosenLetter != "?")
		{
			if(preg_match("/" . $chosenLetter . "/i", $alpha) && $chosenLetter != stripslashes($alpha) && strtoupper($chosenLetter) != strtoupper($alpha))
			{
				if(strlen($noLetters) > 0)
				{
					$noLetters .= ",";
				}
				$noLetters .= $alpha;
			}
		}
	}
	return $noLetters;
}

/**
 * @param string $letter
 * @param string $langcode
 * @param int $page
 * @param int $postsPerPage
 *
 * @return mixed
 * @noinspection PhpMixedReturnTypeCanBeReducedInspection
 */
function getVernacularEntries(string $letter, string $langcode, int $page, int $postsPerPage): mixed
{
	global $wpdb;

	$limitSql = getLimitSql($page, $postsPerPage);

	$collate = "COLLATE " . MYSQL_CHARSET . "_BIN"; //"COLLATE 'UTF8_BIN'";
	if(get_option('IncludeCharactersWithDiacritics') == 1)
	{
		$collate = "";
	}

	/** @noinspection SqlResolve */
	$sql = <<<SQL
SELECT SQL_CALC_FOUND_ROWS ID, post_content
FROM $wpdb->posts
WHERE IFNULL(post_content_filtered, '') <> '' AND post_content_filtered = %s $collate
ORDER BY menu_order
$limitSql
SQL;

	$arrEntries = $wpdb->get_results($wpdb->prepare($sql, $letter));

	//for legacy browse views, where we didn't use the fields post_content_filtered and menu_order
	if(count($arrEntries) == 0)
	{
		$sql = "SELECT SQL_CALC_FOUND_ROWS p.ID, post_title, post_content, search_strings";
		$sql .= " FROM $wpdb->posts AS p";
		$sql .= " JOIN " . SEARCHTABLE . " AS s ON p.ID = s.post_id";
		$sql .= " WHERE LOWER(search_strings) LIKE %s $collate AND language_code = %s AND relevance >= 95";

		$alphas = Webonary_Cloud::filterLetterList(get_option('vernacular_alphabet'));
		$chosenLetter = get_letter($alphas[0]);

		$noLetters = getNoLetters($chosenLetter, $alphas);
		$arrNoLetters = explode(",",  $noLetters);
		foreach($arrNoLetters as $noLetter)
		{
			if(strlen($noLetter) > 0)
			{
				$sql .= " AND search_strings NOT LIKE '" . $noLetter ."%' $collate" .
				" AND search_strings NOT LIKE '" . strtoupper($noLetter) ."%' $collate";
			}
		}

		$sql .= " ORDER BY sortorder ASC, search_strings ASC";
		$sql .= $limitSql;

		$arrEntries = $wpdb->get_results($wpdb->prepare($sql, strtolower($letter) . '%', $langcode));
	}

	return $arrEntries;
}

function getVernacularHeadword($post_id, $language_code): ?string
{
	global $wpdb;

	$sql = "SELECT search_strings " .
	" FROM " . SEARCHTABLE .
	" WHERE post_id = " . $post_id . " AND relevance = 100 AND language_code = '" . $language_code . "'";

	return $wpdb->get_var($sql);

}

/** @noinspection PhpMultipleClassDeclarationsInspection */
function get_letter($firstLetterOfAlphabet = '')
{
	$chosenLetter = filter_input(
		INPUT_GET,
		'letter',
		FILTER_UNSAFE_RAW, ['options' => ['default' => $firstLetterOfAlphabet]]
	);

	// REVIEW: Do we really want to silently fail if this is not true? CP 2017-02
	if (class_exists('Normalizer', false)) {

		$normalization = match (get_option('normalization')) {
			'FORM_D' => Normalizer::FORM_D,
			default => Normalizer::FORM_C
		};

		$chosenLetter = normalizer_normalize($chosenLetter, $normalization);
	}

	return $chosenLetter;
}

Webonary_ShortCodes::Init();
