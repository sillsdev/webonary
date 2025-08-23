<?php

class Webonary_ShortCodes
{
	public static function Init(): void
	{
		add_shortcode('year', [self::class, 'CurrentYear']);
		add_shortcode('copyright', [self::class, 'CopyrightHolder']);
		add_shortcode('vernacularalphabet', [self::class, 'VernacularAlphabet']);
		add_shortcode('reversalindex1', [self::class, 'ReversalAlphabet']);
		add_shortcode('reversalindex2', [self::class, 'ReversalAlphabet']);
		add_shortcode('reversalindex3', [self::class, 'ReversalAlphabet']);
		add_shortcode('reversalindex4', [self::class, 'ReversalAlphabet']);
		add_shortcode('categories', [self::class, 'Categories']);
		add_shortcode('englishalphabet', [self::class, 'EnglishAlphabet']);
	}

	public static function CurrentYear(): string
	{
		return date('Y');
	}

	public static function CopyrightHolder(): string
	{
		$copyright_holder = get_option('copyrightHolder');
		return do_shortcode($copyright_holder, true);
	}

	/**
	 * @param $attributes
	 * @return string
	 * @throws Exception
	 * @noinspection PhpMultipleClassDeclarationsInspection
	 */
	public static function VernacularAlphabet($attributes): string
	{
		global $wpdb;

		$rtl = get_option('vernacularRightToLeft') == '1';
		$align_class = $rtl ? 'right' : 'left';

		if (IS_CLOUD_BACKEND) {
			Webonary_Cloud::registerAndEnqueueMainStyles();
		} else {
			$upload_dir = wp_upload_dir();
			wp_register_style('configured_stylesheet', $upload_dir['baseurl'] . '/imported-with-xhtml.css?time=' . date("U"));
			wp_enqueue_style('configured_stylesheet');

			if (file_exists($upload_dir['basedir'] . '/ProjectDictionaryOverrides.css')) {
				wp_register_style('overrides_stylesheet', $upload_dir['baseurl'] . '/ProjectDictionaryOverrides.css?time=' . date("U"));
				wp_enqueue_style('overrides_stylesheet');
			}
		}

		$language_code = get_option('languagecode');

		$alphas = Webonary_Cloud::filterLetterList(get_option('vernacular_alphabet'));

		if (empty($alphas) || trim($alphas[0]) == '')
			return '';

		$chosenLetter = get_letter($alphas[0]);

		$display = displayAlphabet($alphas, $language_code, $rtl);

		//just displaying letters, not entries (for homepage)
		if ($attributes == 'frontpage')
			return $display;

		$display .= '<div class="center"><h1 id=chosenLetterHead>' . $chosenLetter . '</h1></div><br>';

		if (empty($language_code)) {
			$display .= 'No language code provided. Please import your dictionary.';
			return $display;
		}

		$displaySubentriesAsMinorEntries = get_option('DisplaySubentriesAsMainEntries') == 1;

		$page_num = Webonary_Utility::getPageNumber();
		$postsPerPage = Webonary_Utility::getPostsPerPage();

		if (IS_CLOUD_BACKEND) {
			$apiParams = array(
				'text' => $chosenLetter,
				'mainLang' => $language_code,
				'pageNumber' => $page_num,
				'pageLimit' => $postsPerPage);

			$arrPosts = Webonary_Cloud::getEntriesAsPosts(Webonary_Cloud::$doBrowseByLetter, $apiParams);
			$totalEntries = $_GET['totalEntries'] ?? Webonary_Cloud::getTotalCount(Webonary_Cloud::$doBrowseByLetter, $apiParams);
		} else {
			$arrPosts = getVernacularEntries($chosenLetter, $language_code, $page_num, $postsPerPage);
			$totalEntries = $_GET['totalEntries'] ?? $wpdb->get_var('SELECT FOUND_ROWS()');
		}

		if (empty($arrPosts)) {
			$content = __('No entries exist starting with this letter.', 'sil_dictionary');
		} else {

			$template = <<<HTML
<div class="entry">
	<span class="headword">%s</span>
	<span class="lpMiniHeading">See main entry:</span> <a href="/?s=%s&partialsearch=1">%s</a>
</div>
HTML;

			$content = '';

			foreach ($arrPosts as $my_post) {
				//legacy
				if (!IS_CLOUD_BACKEND && $displaySubentriesAsMinorEntries) {
					if (trim($my_post->post_title ?? '') != trim($my_post->search_strings ?? '')) {
						$headword = getVernacularHeadword($my_post->ID, $language_code);
						$content .= sprintf($template, $my_post->search_strings, $headword, $headword);
					} else {
						$the_content = addLangQuery($my_post->post_content);
						$the_content = normalizer_normalize($the_content, Normalizer::NFC);
						$content .= '<div class="post">' . $the_content . '</div>' . PHP_EOL;
					}
				} else {
					$the_content = addLangQuery($my_post->post_content);
					$the_content = normalizer_normalize($the_content, Normalizer::NFC);
					$content .= '<div class="post">' . $the_content . '</div>' . PHP_EOL;
				}
			}
		}

		$page_nums = displayPageNumbers($chosenLetter, $totalEntries, $postsPerPage, $language_code);

		$html = <<<HTML
$display
<div id="searchresults" class="vernacular-results $align_class">
	$content
</div>
<div class="center">
	<br>
	$page_nums
</div>
<br>
HTML;

		wp_reset_query();
		return $html;
	}

	/**
	 * @param $attributes
	 * @param $content
	 * @param $shortcode_tag
	 * @return string
	 * @throws Exception
	 * @noinspection PhpUnusedParameterInspection
	 */
	public static function ReversalAlphabet($attributes, $content, $shortcode_tag): string
	{
		$reversal_num = intval(mb_substr($shortcode_tag, -1)) ?: 1;
		$alphas = explode(",", get_option('reversal' . $reversal_num . '_alphabet'));

		if (isset($_GET['letter']))
			$chosenLetter = filter_input(INPUT_GET, 'letter', FILTER_UNSAFE_RAW, ['options' => ['default' => '']]);
		else
			$chosenLetter = stripslashes($alphas[0]);

		$rtl = get_option('reversal' . $reversal_num . 'RightToLeft') == '1';
		$alphas = explode(",", get_option('reversal' . $reversal_num . '_alphabet'));
		$display = displayAlphabet($alphas, get_option('reversal' . $reversal_num . '_langcode'), $rtl);

		return reversalindex($display, $chosenLetter, get_option('reversal' . $reversal_num . '_langcode'), $reversal_num);
	}

	/**
	 * @param $attributes
	 * @param $content
	 * @param $shortcode_tag
	 * @return string
	 * @noinspection PhpUnusedParameterInspection
	 * @throws Exception
	 */
	public static function Categories($attributes, $content, $shortcode_tag): string
	{
		global $wpdb;

		Webonary_SemanticDomains::GetRoots();

		$display = '<div id="domRoot"></div>' . PHP_EOL;

		$posts_per_page = Webonary_Utility::getPostsPerPage();

		$qTransLang = Webonary_Cloud::getCurrentLanguage();

		if(!file_exists($_SERVER['DOCUMENT_ROOT'] . '/wp-content/plugins/sil-dictionary-webonary/js/categoryNodes_' . $qTransLang . '.js'))
			$qTransLang = 'en';

		if (IS_CLOUD_BACKEND) {
			Webonary_Cloud::registerAndEnqueueMainStyles();
		}
		else {
			$upload_dir = wp_upload_dir();
			wp_register_style('configured_stylesheet', $upload_dir['baseurl'] . '/imported-with-xhtml.css?time=' . date("U"));
			wp_enqueue_style('configured_stylesheet');
		}

		$blog_url = get_bloginfo('wpurl');

		wp_register_script('webonary_ua_script', $blog_url . '/wp-content/plugins/sil-dictionary-webonary/js/ua.js', [], false, true);
		wp_enqueue_script('webonary_ua_script');

		wp_register_script('webonary_ftiens_script', $blog_url . '/wp-content/plugins/sil-dictionary-webonary/js/ftiens4.js', [], false, true);
		wp_enqueue_script('webonary_ftiens_script');

		$selected_domain_key = get_query_var('semnumber');
		$js = Webonary_SemanticDomains::GetJavaScript($qTransLang, $selected_domain_key);
		wp_add_inline_script('webonary_ftiens_script', $js);

		$page_num = Webonary_Utility::getPageNumber();

		$semdomain = trim((string)filter_input(INPUT_GET, 'semdomain', FILTER_UNSAFE_RAW));
		$semnumber = trim((string)filter_input(INPUT_GET, 'semnumber', FILTER_UNSAFE_RAW));
		$semnumber_internal = rtrim(str_replace('.', '-', $semnumber), '-');
		$arrPosts = null;

		$display .= '<div id="searchresults" class="semantic-domain">';
		if ($semnumber != '') {
			if ($semdomain == '')
				$semdomain = Webonary_SemanticDomains::GetDomainName($semnumber, $qTransLang);

			if(IS_CLOUD_BACKEND)
			{
				list($totalEntries, $arrPosts) = Webonary_MongoDB::DoSemanticDomainSearch($semdomain, rtrim($semnumber, '.'), $page_num, $posts_per_page);
			}
			else
			{
				/** @noinspection SqlResolve */
				$sql = <<<SQL
SELECT SQL_CALC_FOUND_ROWS p.*
FROM $wpdb->posts AS p
     INNER JOIN $wpdb->term_relationships AS r ON p.ID = r.object_id
     INNER JOIN $wpdb->term_taxonomy AS x ON x.term_taxonomy_id = r.term_taxonomy_id
     INNER JOIN $wpdb->terms AS t ON t.term_id = x.term_id
WHERE x.taxonomy = 'sil_semantic_domains'
  AND t.slug LIKE '$semnumber_internal%'
ORDER BY p.post_title, p.ID
SQL;
				$sql .= getLimitSql($page_num, $posts_per_page);

				$arrPosts = $wpdb->get_results($sql);

				$totalEntries = $_GET['totalEntries'] ?? $wpdb->get_var('SELECT FOUND_ROWS();');
			}
		}
		else
		{
			$totalEntries = 0;
		}

		if(!$arrPosts)
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

		$display .= displayPageNumbers($semnumber, $totalEntries, $posts_per_page,  $semdomain , "semnumber", $page_num);
		$display .= "</div>";

		wp_reset_query();
		return $display;
	}

	/**
	 * @return string
	 * @throws Exception
	 */
	public static function EnglishAlphabet(): string
	{
		if(strlen(trim(get_option('reversal1_alphabet'))) == 0)
		{
			$languagecode = "en";

			if(isset($_GET['letter']))
			{
				$chosenLetter = filter_input(INPUT_GET, 'letter', FILTER_UNSAFE_RAW, ['options' => ['default' => '']]);
			}
			else {
				$chosenLetter = "a";
			}

			$alphas = range('a', 'z');
			$display = displayAlphabet($alphas, $languagecode, false);

			$display = reversalindex($display, $chosenLetter, $languagecode);
		}
		else
		{
			$display = Webonary_ShortCodes::ReversalAlphabet(null, '', 'reversalindex1');
		}

		return $display;
	}
}
