<?php
function categories_func( $atts )
{
	global $wpdb;

	$display = "";

	$postsPerPage = Webonary_Utility::getPostsPerPage();

	$qTransLang = "en";

	if (function_exists('qtranxf_init_language'))
	{
		if(qtranxf_getLanguage() != "en")
		{
			$qTransLang = qtranxf_getLanguage();
			if(!file_exists($_SERVER['DOCUMENT_ROOT'] . "/wp-content/plugins/sil-dictionary-webonary/js/categoryNodes_" . $qTransLang . ".js"))
			{
				$qTransLang = "en";
			}
		}
	}

	$arrDomains = array();
	if (get_option('useCloudBackend'))
	{
		$dictionaryId = Webonary_Cloud::getBlogDictionaryId();
		Webonary_Cloud::registerAndEnqueueMainStyles($dictionaryId);

		$dictionary = Webonary_Cloud::getDictionary($dictionaryId);
		if(!is_null($dictionary) && count($dictionary->semanticDomains))
		{
			foreach($dictionary->semanticDomains as $domain)
			{
				if($domain->lang === $qTransLang) {
					$arrDomains[$domain->abbreviation] = array('slug' => str_replace('.', '-', $domain->abbreviation), 'name' => $domain->name);
				}
			}
			ksort($arrDomains, SORT_NATURAL);
		}
	}
	else
	{
		$upload_dir = wp_upload_dir();
		wp_register_style('configured_stylesheet', $upload_dir['baseurl'] . '/imported-with-xhtml.css?time=' . date("U"));
		wp_enqueue_style( 'configured_stylesheet');

		$sql = "SELECT " . $wpdb->prefix . "terms.name, slug " .
		" FROM " . $wpdb->prefix . "terms " .
		" INNER JOIN " . $wpdb->prefix . "term_taxonomy ON " . $wpdb->prefix . "term_taxonomy.term_id = " . $wpdb->prefix . "terms.term_id " .
		" WHERE taxonomy = 'sil_semantic_domains'" .
		" ORDER BY CAST(slug as SIGNED INTEGER) ASC, CAST(RPAD(REPLACE(REPLACE(slug, '-', ''), '10','99'), 5, '0') AS SIGNED INTEGER) ASC "; //this creates a numeric sort

		$arrDomains = $wpdb->get_results($sql, ARRAY_A);
	}
?>
	<style>
	   TD {font-size: 9pt; font-family: arial,helvetica; text-decoration: none; font-weight: bold;}
	   a.categorylink {text-decoration: none; color: navy; font-size: 15px; padding: 3px;}
	   #domRoot {
	   	float:left; width:250px; margin-left: 20px; margin-top: 5px;
	   }
	   #searchresults {
			width:70%;
			min-width: 270px;
			text-align:left;
			float: right;
			margin-top: 20px;
		}
		</style>
		<script src="<?php echo get_bloginfo('wpurl'); ?>/wp-content/plugins/sil-dictionary-webonary/js/ua.js" type="text/javascript"></script>

		<!-- Infrastructure code for the tree -->
		<script src="<?php echo get_bloginfo('wpurl'); ?>/wp-content/plugins/sil-dictionary-webonary/js/ftiens4.js" type="text/javascript"></script>

		<!-- Execution of the code that actually builds the specific tree.
		The variable foldersTree creates its structure with calls to gFld, insFld, and insDoc -->
		<?php
		//if(get_option("useSemDomainNumbers") == 0 || 1 == 1)
		/*
		if(get_option("useSemDomainNumbers") == 0)
		{
		?>
			<script src="<?php echo get_bloginfo('wpurl'); ?>/wp-content/plugins/sil-dictionary-webonary/js/categoryNodes_<?php echo $qTransLang; ?>.js" type="text/javascript"></script>
		<?php
		}
		else
		{
		*/
		require_once( dirname( __FILE__ ) . '/semdomains_func.php' );

		//if no semantic domains were imported, use the default domains defined in default_domains.php
		if(count($arrDomains) == 0)
		{
			$d = 0;
			foreach ($defaultDomain as $key => $value)
			{
				$arrDomains[$d]['slug'] = str_replace(".", "-", rtrim($key, "."));
				$arrDomains[$d]['name'] = $value;
				$d++;
			}
		}

		//echo "<script language=\"JavaScript\">";

		foreach($arrDomains as $domain)
		{
			//echo $domain['slug'] . " " . $domain['name'] . "\n";

			$slug = $domain['slug'];
			$domainNumber = $domain['slug'];

			$domainNumberAsInt = preg_replace('/-/', '', $domainNumber);

			if(is_numeric($domainNumberAsInt))
			{
				$currentSemDomain =  $slug . " " . $domain['name'];

				$levelOfDomain = substr_count("$domainNumber","-") + 1;

				printRootDomainIfNeeded($domainNumber);

				buildTreeToSupportThisItem($domainNumber, $levelOfDomain);

				$domainNumberModified = preg_replace('/-/', '.', $domainNumber) . '.';

				$domainName = trim(substr($currentSemDomain, strlen($domainNumber), strlen($currentSemDomain)));

				if($qTransLang == "en")
				{
					if(isset($defaultDomain[$domainNumberModified]))
					{
						$domainName = $defaultDomain[$domainNumberModified];
					}
				}
				else
				{
					$domainName = __($domainName, 'sil_dictionary');
				}
				$newString = "$domainNumberModified" . " " . $domainName;
				outputSemDomAsJava($levelOfDomain, $newString);
				$currentDigits = explode('-', $domainNumber);
				setLastSemDom($currentDigits);
			}

		}

		echo "</script>";
	//}
	?>
	<!-- Build the browser's objects and display default view of the tree. -->
	<script language="JavaScript">
		initializeDocument();
	</script>
<?php
	global $wp_query;
	$pagenr = Webonary_Utility::getPageNumber();

	$semdomain = trim((string)filter_input(INPUT_GET, 'semdomain', FILTER_UNSAFE_RAW));
	$semnumber = trim((string)filter_input(INPUT_GET, 'semnumber', FILTER_UNSAFE_RAW));
	$semnumber_internal = rtrim(str_replace(".", "-",$semnumber), "-");
	$arrPosts = null;
	$display .= "<div id=searchresults>";
	if($semnumber != '')
	{
		if(get_option('useCloudBackend'))
		{
			$apiParams = array(
				'text' => $semdomain,
				'lang' => $qTransLang,
				'semDomAbbrev' => rtrim($semnumber, '.'),
				'searchSemDoms' => '1',
				'pageNumber' => $pagenr,
				'pageLimit' => $postsPerPage
			);
			$totalEntries = $_GET['totalEntries'] ?? Webonary_Cloud::getTotalCount(Webonary_Cloud::$doSearchEntry, $dictionaryId, $apiParams);
			$arrPosts = Webonary_Cloud::getEntriesAsPosts(Webonary_Cloud::$doSearchEntry, $dictionaryId, $apiParams);
		}
		else
		{
			$arrPosts = query_posts("semdomain=" . $semdomain . "&semnumber=" . $semnumber_internal . "&posts_per_page=" . $postsPerPage . "&paged=" . $pagenr);
			$totalEntries = $_GET['totalEntries'] ?? $wp_query->found_posts;			
		}
	}
	if(count($arrPosts) == 0)
	{
		if($semdomain != '')
		{
			$display .= __('No entries exist for', 'sil_dictionary') . ' "' . $semdomain . '"';
		}
	}
	else
	{
		$display .= "<h3>" . $semnumber  . " " . $semdomain . "</h3>";
		foreach($arrPosts as $mypost)
		{
				$display .= "<div class=post>" . $mypost->post_content . "</div>";
		}
	}

	$display .= displayPageNumbers($semnumber, $totalEntries, $postsPerPage,  $semdomain , "semnumber", $pagenr);
	$display .= "</div>";

 	wp_reset_query();
	return $display;

}
add_shortcode( 'categories', 'categories_func' );


function displayAlphabet($alphas, $languagecode)
{
	global $wpdb;

	if(trim($alphas[0]) == "" && is_front_page())
	{
		return "";
	}
?>
	<style type="text/css">
	.lpTitleLetterCell {min-width:31px; height: 23px; padding-top: 3px; padding-bottom: 2px; text-bottom; text-align:center;background-color: #EEEEEE;border:1px solid silver; position: relative;}
	<?php
	if(get_option('vernacularLettersFont') != "")
	{
	?>
	.lpTitleLetter  {
		font-family: "<?php echo get_option('vernacularLettersFont'); ?>";
	}
	#chosenLetterHead {
		font-family: "<?php echo get_option('vernacularLettersFont'); ?>";
	}
	<?php
	}
	?>
	</style>
<?php
	$display = "<br>";
	$display .= "<div style=\"text-align:center;\"><div style=\"display:inline-block;\">";

	$letterCells = "<div class=\"lpTitleLetterCell\"><span class=lpTitleLetter>" . str_replace(",", "</span></div><div class=\"lpTitleLetterCell\"><span class=lpTitleLetter>", get_option('vernacular_alphabet')) . "</span></div><br>";

	$permalink = "";
	if(is_front_page())
	{
		$sql = "SELECT ID FROM $wpdb->posts WHERE post_content LIKE '%[vernacularalphabet]%'";
		$post_id = $wpdb->get_var($sql);

		$permalink = get_permalink($post_id);
	}

	foreach($alphas as $letter)
	{
		$display .= "<div class=\"lpTitleLetterCell\"><span>";
		if(trim($letter) == "" && !is_front_page())
		{
			$display .= "<a href=\"" . get_site_url() . "/wp-admin/admin.php?page=webonary#browse\" style=\"padding:2px;\">Alphabet not configured</a>";
		}
		else
		{
			$lang = "";
			if(isset($_GET['lang']))
			{
				$lang = "&lang=" . $_GET['lang'];
			}

			$display .= "<a class=lpTitleLetter href=\"" . $permalink . "?letter=" . stripslashes($letter) . "&key=" . $languagecode . $lang . "\">" . stripslashes($letter) . "</a>";
		}
		$display .= "</span></div>";
	}

	$display .= "</div></div>";
	$display .=  "<div style=clear:both></div>";

	return $display;

}

function displayPageNumbers($chosenLetter, $totalEntries, $entriesPerPage, $languagecode, $requestname = null, $currentPage = null)
{
?>
	<link rel="stylesheet" href="<?php echo get_bloginfo('wpurl'); ?>/wp-content/plugins/wp-page-numbers/classic/wp-page-numbers.css" />

<?php
	$display = '';
	if(!isset($requestname))
	{
		$requestname = "letter";
	}

	if(!$currentPage)
	{
		$currentPage = Webonary_Utility::getPageNumber();
	}

	$totalPages = ceil($totalEntries / $entriesPerPage);
	if(($totalEntries / $entriesPerPage) > $totalPages)
	{
		$totalPages++;
	}
	if($totalPages > 1)
	{
		$display .= "<div style=\"text-align:center;\"><div style=\"display:inline-block;\">";
		$display .= "<div  id='wp_page_numbers'><ul>";
		$nextpage = "&gt;";
		$prevpage = "&lt;";
		$lang = "";
		if(isset($_GET['lang']))
		{
			$lang = "&lang=" . $_GET['lang'];
		}

		$url = "?" . $requestname . "=" . $chosenLetter . "&key=" . $languagecode . "&totalEntries=" . $totalEntries . $lang;

		$limit_pages = 10;
		$display .= "<li class=page_info>" . gettext("Page") . " " . $currentPage . " " . gettext("of") . " " . $totalPages . "</li>";

		if( $totalPages > 1 && $currentPage > 1 )
		{
			if($requestname == "semnumber")
			{
				$display .= "<li><a href=\"?semdomain=" . $languagecode . "&semnumber=" . $chosenLetter . "&pagenr=" . ($currentPage - 1) . "\">" . $prevpage . "</a></li> ";
			}
			else
			{
				$display .= "<li><a href=\"" . $url . "&pagenr=" . ($currentPage - 1) . "\">" .$prevpage . "</a></li>";
			}
		}

		$start = 1;
		if($currentPage > ($limit_pages - 5))
		{
			if($requestname == "semnumber")
			{
				$display .= "<li><a href=\"?semdomain=" . $languagecode . "&semnumber=" . $chosenLetter . "&pagenr=1\">1</a></li> ";
			}
			else
			{
				$display .= "<li><a href=\"" . $url . "&pagenr=1\">1</a></li> ";
			}
			$display .= "<li class=space>...</li>";
			$start = $currentPage - 5;
			if($currentPage == 6)
			{
				$start = 2;
			}
		}

		for($page = $start; $page <= $totalPages; $page++)
		{
			$class = "";
			if($currentPage == $page || ($page == 1 && !isset($currentPage)))
			{
				$class="class=active_page";
			}
			if($requestname == "semnumber")
			{
				$display .= "<li " . $class . "><a href=\"?semdomain=" . $languagecode . "&semnumber=" . $chosenLetter . "&pagenr=" . $page . "\">" . $page . "</a></li> ";
			}
			else
			{
				$display .= "<li " . $class . "><a href=\"" . $url . "&pagenr=" . $page . "\">" . $page . "</a></li> ";
			}
			$minusPages = 5;
			if($currentPage < 5)
			{
				$minusPages = $currentPage;
			}
			if(($currentPage + $limit_pages - $minusPages) == $page && ($currentPage + $limit_pages) < $totalPages)
			{
				$display .= "<li class=space>...</li>";
				$display .= "<li " . $class . "><a href=\"" . $url . "&pagenr=" . $totalPages . "\">" . $totalPages . "</a></li> ";
				break;
			}
		}

		if( $currentPage != "" && $currentPage < $totalPages)
		{
			if($requestname == "semnumber")
			{
				$display .= "<li><a href=\"?semdomain=" . $languagecode . "&semnumber=" . $chosenLetter . "&pagenr=" . ($currentPage + 1) . "\">" . $nextpage . "</a></li> ";
			}
			else
			{
				$display .= "<li><a href=\"" . $url . "&pagenr=" . ($currentPage + 1) . "\">" .$nextpage . "</a></li>";
			}
		}
		$display .= "</ul></div>";
		$display .= "</div></div>";
	}
	return $display;
}

function englishalphabet_func( $atts, $content, $tag ) {

	$languagecode = '';
	if(strlen(trim(get_option('reversal1_alphabet'))) == 0)
	{
		$languagecode = "en";

		if(isset($_GET['letter']))
		{
			$chosenLetter = $_GET['letter'];
		}
		else {
			$chosenLetter = "a";
		}

		$alphas = range('a', 'z');
		$display = displayAlphabet($alphas, $languagecode);

		$display = reversalindex($display, $chosenLetter, $languagecode);
	}
	else
	{
		$display = reversalalphabet_func(null, "", "reversalindex1");
	}

 return $display;
}

add_shortcode( 'englishalphabet', 'englishalphabet_func');


function get_has_reversalbrowseletters()
{
	global $wpdb;

	$result = $wpdb->get_results("SHOW COLUMNS FROM " . REVERSALTABLE . " LIKE 'browseletter'");
	$exists = (count($result))?TRUE:FALSE;

	return $exists;
}


/**
 * Returns a MySQL LIMIT statement
 *
 * @param int $page
 * @param int $postsPerPage
 *
 * @return string
 */
function getLimitSql($page=null, $postsPerPage=null)
{
	if (is_null($page))
	    $page = Webonary_Utility::getPageNumber();

	if (is_null($postsPerPage))
	    $postsPerPage = Webonary_Utility::getPostsPerPage();

	$startFrom = ($page > 1) ? (($page - 1) * $postsPerPage) : 0;
	return " LIMIT $postsPerPage OFFSET $startFrom";
}

function getReversalEntries($letter = "", $page, $reversalLangcode = "", &$displayXHTML = true, $reversalnr, $postsPerPage = null)
{
	if(strlen($reversalLangcode) === 0 && $reversalnr > 0)
	{
		return null;
	}

	global $wpdb;

	$postsPerPage = $postsPerPage ?? Webonary_Utility::getPostsPerPage();
	$limitSql = getLimitSql($page, $postsPerPage);

	$result = $wpdb->get_results("SHOW COLUMNS FROM ". REVERSALTABLE . " LIKE 'sortorder'");
	$sortorderExists = (count($result))?TRUE:FALSE;

	$alphabet = str_replace(",", "", get_option('reversal' . $reversalnr . '_alphabet'));
	$collate = "COLLATE " . MYSQL_CHARSET . "_BIN";

	$sql = "SELECT SQL_CALC_FOUND_ROWS reversal_content " .
	" FROM " . REVERSALTABLE  .
	" WHERE 1 = 1 ";
	if($letter != "")
	{
		//new imports use the letter header from FLEx for grouping
		if(get_has_reversalbrowseletters())
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
	if($sortorderExists && $reversalLangcode != "zh-CN" && $reversalLangcode != "zh-Hans-CN")
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

add_shortcode( 'reversalindex1', 'reversalalphabet_func' );
add_shortcode( 'reversalindex2', 'reversalalphabet_func' );
add_shortcode( 'reversalindex3', 'reversalalphabet_func' );
add_shortcode( 'reversalindex4', 'reversalalphabet_func' );

function reversalalphabet_func($atts, $content, $tag)
{
	if($tag == "reversalindex2")
	{
		$reversalnr = 2;
	}
	else if($tag == "reversalindex3")
	{
		$reversalnr = 3;
	}
	else if($tag == "reversalindex4")
	{
		$reversalnr = 4;
	}
	else
	{
		$reversalnr = 1;
	}

	$alphas = explode(",",  get_option('reversal'. $reversalnr . '_alphabet'));

	if(isset($_GET['letter']))
	{
		$chosenLetter = stripslashes($_GET['letter']);
	}
	else {
		$chosenLetter = stripslashes($alphas[0]);
	}

	$alphas = explode(",",  get_option('reversal' . $reversalnr . '_alphabet'));
	$display = displayAlphabet($alphas, get_option('reversal' . $reversalnr . '_langcode'));

	$display = reversalindex($display, $chosenLetter, get_option('reversal' . $reversalnr . '_langcode'), $reversalnr);

	return $display;
}

function reversalindex($display, $chosenLetter, $langcode, $reversalnr = "")
{
?>
	<style>
	#searchresult {
		width:70%;
		min-width: 270px;
		text-align:left;
	}
	#englishcol {
		float:left;
		margin: 1px;
		padding-left: 2px;
		width:50%;
		text-align:left;
	}
	#vernacularcol {
		text-align:left;
	}
	.odd { background: #CCCCCC; };
	.even { background: #FFF; };
	</style>
	<style>
	<?php
	if(get_option('reversal' . $reversalnr . 'RightToLeft') == 1)
	{
	?>
		#searchresults {
		text-align: right;
		}
		.lpTitleLetterCell {float:right;}
	<?php
	}
	else
	{
	?>
	.lpTitleLetterCell {float:left;}
	<?php
	}
	?>
	</style>
<?php
	global $wpdb;

	$pagenr = Webonary_Utility::getPageNumber();
	$postsPerPage = Webonary_Utility::getPostsPerPage();
	$displayXHTML = true;

	if(get_option('useCloudBackend'))
	{
		$dictionaryId = Webonary_Cloud::getBlogdictionaryId();
		Webonary_Cloud::registerAndEnqueueReversalStyles($dictionaryId, $langcode);

		$apiParams = array(
			'text' => $chosenLetter,
			'lang' => $langcode,
			'pageNumber' => $pagenr,
			'pageLimit' => $postsPerPage
		);

		$totalEntries = $_GET['totalEntries'] ?? Webonary_Cloud::getTotalCount(Webonary_Cloud::$doBrowseByLetter, $dictionaryId, $apiParams);
		$arrReversals = Webonary_Cloud::getEntriesAsReversals($dictionaryId, $apiParams);
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

		$arrReversals = getReversalEntries($chosenLetter, $pagenr, $langcode, $displayXHTML, $reversalnr, $postsPerPage);
		$totalEntries = $_GET['totalEntries'] ?? $wpdb->get_var('SELECT FOUND_ROWS()');
	}

	if($arrReversals == null)
	{
		$display .= "No reversal entries imported.";
		return $display;
	}

	$display .= "<h1 align=center>" . $chosenLetter . "</h1><br>";

	if($displayXHTML)
	{
		$display .=  "<div id=searchresults>";
	}
	else
	{
		$display .=  "<div align=center id=searchresults>";
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
			$display = "<p>" . gettext("Entry not found");
		}
	}

	return $display;
}

function getNoLetters($chosenLetter, $alphas)
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

function getVernacularEntries($letter = "", $langcode = "", $page, $postsPerPage = null)
{
	global $wpdb;

	$postsPerPage = $postsPerPage ?? Webonary_Utility::getPostsPerPage();
	$limitSql = getLimitSql($page, $postsPerPage);

	$collate = "COLLATE " . MYSQL_CHARSET . "_BIN"; //"COLLATE 'UTF8_BIN'";
	if(get_option('IncludeCharactersWithDiacritics') == 1)
	{
		$collate = "";
	}

	$sql = "SELECT SQL_CALC_FOUND_ROWS ID, post_content";
	$sql .= " FROM $wpdb->posts";
	$sql .= " WHERE post_content_filtered = %s $collate";
	$sql .= " ORDER BY menu_order ASC";
	$sql .= $limitSql;

	$arrEntries = $wpdb->get_results($wpdb->prepare($sql, $letter));

	//for legacy browse views, where we didn't use the fields post_content_filtered and menu_order
	if(count($arrEntries) == 0)
	{
		$sql = "SELECT SQL_CALC_FOUND_ROWS p.ID, post_title, post_content, search_strings";
		$sql .= " FROM $wpdb->posts AS p";
		$sql .= " JOIN " . SEARCHTABLE . " AS s ON p.ID = s.post_id";
		$sql .= " WHERE LOWER(search_strings) LIKE %s $collate AND language_code = %s AND relevance >= 95";

		$alphas = explode(",",  get_option('vernacular_alphabet'));
		$chosenLetter = get_letter($alphas[0]);

		$noletters = getNoLetters($chosenLetter, $alphas);
		$arrNoLetters = explode(",",  $noletters);
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

function getVernacularHeadword($postid, $languagecode)
{
	global $wpdb;

	$sql = "SELECT search_strings " .
	" FROM " . SEARCHTABLE .
	" WHERE post_id = " . $postid . " AND relevance = 100 AND language_code = '" . $languagecode . "'";

	return $wpdb->get_var($sql);

}

function get_letter($firstLetterOfAlphabet = "") {
	if (!isset($_GET['letter'])) {
		return $firstLetterOfAlphabet;
	}
	$chosenLetter = stripslashes(trim($_GET['letter']));
	// REVIEW: Do we really want to silently fail if this is not true? CP 2017-02
	if (class_exists("Normalizer", $autoload = false))
	{
		$normalization = Normalizer::FORM_C;
		if(get_option("normalization") == "FORM_D")
		{
			$normalization = Normalizer::FORM_D;
		}
		$chosenLetter = normalizer_normalize($chosenLetter, $normalization);
	}
	return $chosenLetter;
}

function vernacularalphabet_func( $atts )
{
	global $wpdb;
?>
	<style>
		.lpTitleLetterCell {float:left;}
	</style>
	<?php
	if(get_option('vernacularRightToLeft') == 1)
	{
	?>
		<style>
		#searchresults {
		text-align: right;
		}
		.lpTitleLetterCell {float:right;}
		</style>
	<?php
	}

	if (get_option('useCloudBackend'))
	{
		$dictionaryId = Webonary_Cloud::getBlogDictionaryId();
		Webonary_Cloud::registerAndEnqueueMainStyles($dictionaryId);
	}
	else
	{
		$upload_dir = wp_upload_dir();
		wp_register_style('configured_stylesheet', $upload_dir['baseurl'] . '/imported-with-xhtml.css?time=' . date("U"));
		wp_enqueue_style( 'configured_stylesheet');

		if(file_exists($upload_dir['basedir'] . '/ProjectDictionaryOverrides.css'))
		{
			wp_register_style('overrides_stylesheet', $upload_dir['baseurl'] . '/ProjectDictionaryOverrides.css?time=' . date("U"));
			wp_enqueue_style( 'overrides_stylesheet');
		}
	}

	$languagecode = get_option('languagecode');

	$alphas = explode(",",  get_option('vernacular_alphabet'));

	$chosenLetter = get_letter($alphas[0]);

	$display = displayAlphabet($alphas, $languagecode);

	//just displaying letters, not entries (for homepage)
	if($atts != "frontpage")
	{
		$display .= "<div align=center><h1 id=chosenLetterHead>" . $chosenLetter . "</h1></div><br>";

		if(empty($languagecode))
		{
			$display .=  "No language code provided. Please import your dictionary.";
			return $display;
		}

		$display .= "<div id=searchresults>";

		$displaySubentriesAsMinorEntries = true;
		if(get_option('DisplaySubentriesAsMainEntries') == 'no')
		{
			$displaySubentriesAsMinorEntries = false;
		}
		if(get_option('DisplaySubentriesAsMainEntries') == 1)
		{
			$displaySubentriesAsMinorEntries = true;
		}

		//$arrPosts = query_posts("s=a&letter=" . $chosenLetter . "&noletters=" . $noLetters . "&langcode=" . $languagecode . "&posts_per_page=" . $posts_per_page . "&paged=" . $_GET['pagenr'] . "&DisplaySubentriesAsMainEntries=" . $displaySubentriesAsMinorEntries);
		$pagenr = Webonary_Utility::getPageNumber();
		$postsPerPage = Webonary_Utility::getPostsPerPage();

		if(get_option('useCloudBackend'))
		{
			$dictionaryId = Webonary_Cloud::getBlogdictionaryId();
			$apiParams = array(
				'text' => $chosenLetter,
				'mainLang' => $languagecode,
				'pageNumber' => $pagenr,
				'pageLimit' => $postsPerPage);

			$arrPosts = Webonary_Cloud::getEntriesAsPosts(Webonary_Cloud::$doBrowseByLetter, $dictionaryId, $apiParams);
			$totalEntries = $_GET['totalEntries'] ?? Webonary_Cloud::getTotalCount(Webonary_Cloud::$doBrowseByLetter, $dictionaryId, $apiParams);
		}
		else
		{
			$arrPosts = getVernacularEntries($chosenLetter, $languagecode, $pagenr, $postsPerPage);
			$totalEntries = $_GET['totalEntries'] ?? $wpdb->get_var('SELECT FOUND_ROWS()');
		}

		if(count($arrPosts) == 0)
		{
			$display .= __('No entries exist starting with this letter.', 'sil_dictionary');
		}

		foreach($arrPosts as $mypost)
		{
			//legacy
			if($displaySubentriesAsMinorEntries == true)
			{
				if(trim($mypost->post_title) != trim($mypost->search_strings))
				{
					$headword = getVernacularHeadword($mypost->ID, $languagecode);
					$display .= "<div class=entry><span class=headword>" . $mypost->search_strings . "</span> ";
					$display .= "<span class=lpMiniHeading>See main entry:</span> <a href=\"/?s=" . $headword . "&partialsearch=1\">" . $headword . "</a></div>";
				}
				else
				{
					$the_content = addLangQuery($mypost->post_content);
					$the_content = normalizer_normalize($the_content, Normalizer::NFC );
					$display .= "<div class=\"post\">" . $the_content . "</div>";
				}
			}
			else
			{
				$the_content = addLangQuery($mypost->post_content);
				$the_content = normalizer_normalize($the_content, Normalizer::NFC );
				$display .= "<div class=\"post\">" . $the_content . "</div>";
			}
		}

		$display .= "</div>";

		$display .= "<div align=center><br>";
		$display .= displayPageNumbers($chosenLetter, $totalEntries, $postsPerPage, $languagecode);
		$display .= "</div><br>";
	}
 	wp_reset_query();
	return $display;
}

add_shortcode( 'vernacularalphabet', 'vernacularalphabet_func' );
?>
