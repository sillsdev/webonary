<?php
/** @noinspection PhpMultipleClassDeclarationsInspection */
/** @noinspection PhpUnused */
/** @noinspection SqlResolve */
/**
 * Search
 *
 * Search functions for SIL Dictionaries.
 *
 * PHP version 5.2
 *
 * LICENSE GPL v2
 *
 * @package WordPress
 * @since 3.1
 */

// This file was originally based upon the Search Custom Fields plugin and template
// (search-custom.php) by Kaf Oseo. http://guff.szub.net/search-custom-fields/.
// The code has since been mangled and evolved beyond recognition from that.

// don't load directly
if ( ! defined('ABSPATH') )
	die( '-1' );

//---------------------------------------------------------------------------//

/**
 * Does the string have Chinese, Japanese, or Korean characters?
 * @param string $string = string to check
 * @return bool = whether the string has Chinese/Japanese/Korean characters.
 */
function is_CJK(string $string): bool
{
	$regex = '/' . implode( '|', get_CJK_unicode_ranges() ) . '/u';
	return preg_match( $regex, $string );
}

function is_match_whole_words($search): int
{
	global $wp_query;

	$match_whole_words = 0;
	if(isset($wp_query->query_vars['match_whole_words']))
	{
		if($wp_query->query_vars['match_whole_words'] == 1)
		{
			$match_whole_words = 1;
		}
	}

	if(filter_input(INPUT_GET, 'partialsearch', FILTER_VALIDATE_INT, ['options' => ['default' => 0]]) == 1)
	{
		$match_whole_words = 0;
	}

	if(strlen($search) == 0 && !empty(Webonary_Parts_Of_Speech::GetPartsOfSpeechSelected()))
	{
		$match_whole_words = 0;
	}

	return $match_whole_words;
}

//---------------------------------------------------------------------------//

/**
 * A function that returns Chinese/Japanese/Korean (CJK) Unicode code points
 * Slightly adapted from an answer by "simon" found at:
 * @link http://stackoverflow.com/questions/5074161/what-is-the-most-efficient-way-to-whitelist-utf-8-characters-in-php
 * @return array
 */
function get_CJK_unicode_ranges(): array
{
	return array(
		"[\x{2E80}-\x{2EFF}]",      # CJK Radicals Supplement
		"[\x{2F00}-\x{2FDF}]",      # Kangxi Radicals
		"[\x{2FF0}-\x{2FFF}]",      # Ideographic Description Characters
		"[\x{3000}-\x{303F}]",      # CJK Symbols and Punctuation
		"[\x{3040}-\x{309F}]",      # Hiragana
		"[\x{30A0}-\x{30FF}]",      # Katakana
		"[\x{3100}-\x{312F}]",      # Bopomofo
		"[\x{3130}-\x{318F}]",      # Hangul Compatibility Jamo
		"[\x{3190}-\x{319F}]",      # Kanbun
		"[\x{31A0}-\x{31BF}]",      # Bopomofo Extended
		"[\x{31F0}-\x{31FF}]",      # Katakana Phonetic Extensions
		"[\x{3200}-\x{32FF}]",      # Enclosed CJK Letters and Months
		"[\x{3300}-\x{33FF}]",      # CJK Compatibility
		"[\x{3400}-\x{4DBF}]",      # CJK Unified Ideographs Extension A
		"[\x{4DC0}-\x{4DFF}]",      # Yijing Hexagram Symbols
		"[\x{4E00}-\x{9FFF}]",      # CJK Unified Ideographs
		"[\x{A000}-\x{A48F}]",      # Yi Syllables
		"[\x{A490}-\x{A4CF}]",      # Yi Radicals
		"[\x{AC00}-\x{D7AF}]",      # Hangul Syllables
		"[\x{F900}-\x{FAFF}]",      # CJK Compatibility Ideographs
		"[\x{FE30}-\x{FE4F}]",      # CJK Compatibility Forms
		"[\x{1D300}-\x{1D35F}]",    # Tai Xuan Jing Symbols
		"[\x{20000}-\x{2A6DF}]",    # CJK Unified Ideographs Extension B
		"[\x{2F800}-\x{2FA1F}]"     # CJK Compatibility Ideographs Supplement
	);
}

//---------------------------------------------------------------------------//

// I'm not sure this is being used.

// NOTE: no longer used
//function no_standard_sort($k) {
//	global $wp_query;
//	if(!empty($wp_query->query_vars['s'])) {
//		$k->query_vars['orderby'] = 'none';
//		$k->query_vars['order'] = 'none';
//	}
//}

function get_indexed_entries($query, $language)
{
	global $wpdb;

	$sql = "SELECT post_id, language_code, relevance, search_strings " .
	" FROM " . SEARCHTABLE .
	" WHERE search_strings LIKE '%" . $query . "%' ";
	if(!empty($language))
	{
		$sql .= " AND language_code = '" . $language . "'";
	}
	$sql .= "ORDER BY relevance DESC";

	return $wpdb->get_results($sql);
}

function get_post_id_bycontent($query): ?string
{
	global $wpdb;

	$sql = "SELECT ID " .
			" FROM " . $wpdb->posts .
			" WHERE post_content LIKE '%" . $query . "%'";

	return $wpdb->get_var($sql);
}

function my_404_override() {
	global $wp_query;

	if(is_404())
	{
		$postname = get_query_var('name');

		$postid = get_post_id_bycontent($postname);

		if(isset($postid))
		{
			status_header( 200 );
			$wp_query->is_404=false;

			query_posts('p=' . $postid);
		}
	}
}
add_filter('template_redirect', 'my_404_override');

function filter_the_content_in_the_main_loop($content) {

	return normalizer_normalize($content, Normalizer::NFC);
}
add_filter('the_content', 'filter_the_content_in_the_main_loop');

function get_subquery_where($query, $search): string
{
	mb_internal_encoding('UTF-8');

	if (empty($search))
		return '';

	//search string gets trimmed and normalized to NFC
	if (class_exists('Normalizer', false)) {
		$normalization = Normalizer::FORM_C;
		if (get_option('normalization') == 'FORM_D')
			$normalization = Normalizer::FORM_D;

		$search = normalizer_normalize($search, $normalization);
	}

	$search = strtolower($search);

	if (!empty($_GET['key'])) {
		$key = $_GET['key'];
	} elseif (!empty($query->query_vars['langcode'])) {
		$key = $query->query_vars['langcode'];
	} else {
		$key = '';
	}

	$sub_queries = [];

	if (strlen(trim($key)) > 0)
		$sub_queries[] = "language_code = '$key'";

	//using search form
	$match_accents = false;
	if (isset($query->query_vars['match_accents'])) {
		$match_accents = true;
	}

	//by default d à, ä, etc. are handled as the same letters when searching
	$collateSearch = "";
	if (get_option('distinguish_diacritics') == 1 || $match_accents == true) {
		$collateSearch = "COLLATE " . MYSQL_CHARSET . "_BIN"; //"COLLATE 'UTF8_BIN'";
	}

	$expanded_search = $search;
	//this is for creating a regular expression that searches words with accents & composed characters by only using base characters
	if (preg_match('/([aeiou])/', $search) && $match_accents == false && get_option("searchSomposedCharacters") == 1) {
		//first we add brackets around all letters that aren't a vowel, e.g. yag becomes (y)a(g)
		$expanded_search = preg_replace('/(^[aeiou])/u', '($1)', $expanded_search);
		//see https://en.wiktionary.org/wiki/Appendix:Variations_of_%22a%22
		//the mysql regular expression can't find words with  accented characters if we don't include them
		$expanded_search = preg_replace('/([a])/u', '(à|ȁ|á|â|ấ|ầ|ẩ|ā|ä|ǟ|å|ǻ|ă|ặ|ȃ|ã|ą|ǎ|ȧ|ǡ|ḁ|ạ|ả|ẚ|a', $expanded_search);
		$expanded_search = preg_replace('/([e])/u', '(ē|é|ě|è|ȅ|ê|ę|ë|ė|ẹ|ẽ|ĕ|ȇ|ȩ|ḕ|ḗ|ḙ|ḛ|ḝ|ė|e', $expanded_search);
		$expanded_search = preg_replace('/([ε])/u', '(έ|ἐ|ἒ|ἑ|ἕ|ἓ|ὲ|ε', $expanded_search);
		$expanded_search = preg_replace('/([ɛ])/u', '(ɛ', $expanded_search);
		$expanded_search = preg_replace('/([ə])/u', '(ə́|ə', $expanded_search);
		$expanded_search = preg_replace('/([i])/u', '(ı|ī|í|ǐ|ĭ|ì|î|î|į|ï|ï|ɨ|i', $expanded_search);
		$expanded_search = preg_replace('/([o])/u', '(ō|ṓ|ó|ǒ|ò|ô|ö|õ|ő|ṓ|ø|ǫ|ȱ|ṏ|ȯ|ꝍ|o', $expanded_search);
		$expanded_search = preg_replace('/([ɔ])/u', '(ɔ', $expanded_search);
		$expanded_search = preg_replace('/([u])/u', '(ū|ú|ǔ|ù|ŭ|û|ü|ů|ų|ũ|ű|ȕ|ṳ|ṵ|ṷ|ṹ|ṻ|ʉ|u', $expanded_search);
		//for vowels we add [^a-z]* which will search for any character that comes after the normal character
		//one can't see it, but composed characters actually consist of two characters, for instance the a in ya̧g
		$expanded_search = preg_replace('/([aeiouɛεəɔ])/u', '$1)[^a-z^ ]*', $expanded_search);
	}

	$match_whole_words = is_match_whole_words($search);

	if (is_CJK($search) || $match_whole_words == 0) {
		if (get_option('searchSomposedCharacters') == 1)
			$sub_queries[] = 'LOWER(search_strings) REGEXP \'' . Webonary_Utility::escapeSql($expanded_search) . '\' ' . $collateSearch;
		else
			$sub_queries[] = 'LOWER(search_strings) LIKE \'%' . Webonary_Utility::escapeSqlLike($search) . '%\' ' . $collateSearch;
	} else {
		if (mb_strlen($search) > 1) {
			$expanded_search = Webonary_Utility::escapeSql($expanded_search);

			if (mb_strpos($search, '\'') === false)
				$sub_queries[] = "search_strings REGEXP '[[:<:]]{$expanded_search}[[:digit:]]?[[:>:]]' $collateSearch";
			else
				$sub_queries[] = "search_strings REGEXP '([[:blank:][:punct:]]|^){$expanded_search}[[:digit:]]?([[:punct:][:blank:]]|$)' $collateSearch";
		}
	}

	if (empty($sub_queries))
		return '';

	return 'WHERE ' . implode(' AND ', $sub_queries);
}

/**
 * Hook to override the default post query
 *
 * @param $input
 * @param WP_Query|null $query
 * @return string
 */
function replace_default_search_filter($input, ?WP_Query $query=null): string
{
	global $wpdb;

	if (empty($query) || get_option('useCloudBackend'))
		return $input;

	// get the selected parts of speech list, and escape it for SQL
	$parts_of_speech = $wpdb->_escape(Webonary_Parts_Of_Speech::GetPartsOfSpeechSelected());

	// get the search term
	$search_term = filter_input(INPUT_GET, 's', FILTER_UNSAFE_RAW, ['options' => ['default' => ($query->query_vars['s'] ?? '')]]);

	// get the selected semantic domains
	$semantic_domains = $wpdb->_escape(Webonary_Utility::RemoveEmptyStrings([$query->query_vars['semnumber']]));

	$join_tables = [];
	$where_and = [
		'p.post_type = \'post\'',
	    'p.post_status = \'publish\''
	];


	// add additional conditions if 'Parts of Speech' or 'Semantic Domains' were selected
	if (!empty($parts_of_speech)) {

		$taxonomy_list = implode("','", $parts_of_speech);

		$join_tables['r'] = "LEFT JOIN $wpdb->term_relationships AS r ON p.ID = r.object_id";
		$join_tables['t'] = "LEFT JOIN $wpdb->term_taxonomy AS t ON r.term_taxonomy_id = t.term_taxonomy_id";
		$where_and[] = "t.term_id IN ('$taxonomy_list')";

	} elseif (!empty($semantic_domains)) {

		$join_tables['r'] = "LEFT JOIN $wpdb->term_relationships AS r ON p.ID = r.object_id";
		$join_tables['t'] = "LEFT JOIN $wpdb->term_taxonomy AS t ON r.term_taxonomy_id = t.term_taxonomy_id";
		$join_tables['m'] = "LEFT JOIN $wpdb->terms AS m ON t.term_id = m.term_id";

		// the semantic domains will be joined by 'OR' rather than 'AND'
		$sem_domain_where = [];
		foreach ($semantic_domains AS $sd) {
			$sem_domain_where[] = "m.slug REGEXP '^$sd([-]|$)'";
		}

		$where_and[] = 't.taxonomy = \'sil_semantic_domains\'';
		$where_and[] = '(' . implode(' OR ', $sem_domain_where) . ')';
	}

	$join_tables_str = implode(PHP_EOL, $join_tables);
	$where_and_str = implode(' AND ', $where_and);

	// is this a search for a word or something else?
	if (is_search() && !empty($search_term)) {

		$search_tbl = SEARCHTABLE;
		$where = get_subquery_where($query, $search_term);

		$input = <<<SQL
SELECT SQL_CALC_FOUND_ROWS DISTINCTROW p.*, s.relevance
FROM {$wpdb->posts} AS p
  INNER JOIN (
			  SELECT post_id, MAX(relevance) AS relevance
			  FROM $search_tbl 
			  $where
			  GROUP BY post_id
			 ) AS s ON p.ID = s.post_id
  $join_tables_str
WHERE $where_and_str
ORDER BY s.relevance DESC, p.post_title
SQL;

	} elseif (!empty($join_tables_str)) {

		$input = <<<SQL
SELECT SQL_CALC_FOUND_ROWS DISTINCTROW p.*
FROM $wpdb->posts AS p
  $join_tables_str
WHERE $where_and_str
ORDER BY s.relevance DESC, p.post_title
SQL;

	} else {

		return $input;
	}

	Webonary_Utility::setPageNumber((int)($query->query_vars['paged'] ?? 0));

	return $input . PHP_EOL . getLimitSql();
}
