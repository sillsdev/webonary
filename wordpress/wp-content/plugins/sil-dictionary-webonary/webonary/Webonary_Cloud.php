<?php

class Webonary_Cloud
{
	public static $doBrowseByLetter = 'browse';

	public static $doGetEntry = 'get';

	public static $doSearchFulltext = 'search';

	private static function isValidEntry($entry) {
		return is_object($entry) && isset($entry->_id);
	} 

	private static function convertGuidToId($guid) {
		return 'g' . $guid;
	} 

	private static function remoteGet($path, $params = array()) {
		if (!defined('WEBONARY_CLOUD_API_URL'))  {
			error_log('WEBONARY_CLOUD_API_URL is not set! Please do so in wp-config.php.');
			return;
		}

		$encoded_path = array_map('rawurlencode', explode('/', $path));
		$url = rtrim(WEBONARY_CLOUD_API_URL, '/') . '/' . implode('/', $encoded_path);

		if (count($params)) {
			$url .= '?' . build_query(array_map('urlencode', $params));
		}

		if (!filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
			error_log($url . ' is not a valid URL. Is WEBONARY_CLOUD_API_URL in wp-config.php set to a correct URL?');
			return;
		}

		if (WP_DEBUG){
			error_log('Getting results from ' . $url);
		}

		return wp_remote_get($url);
	}

	private static function remoteFileUrl($path) {
		if (!defined('WEBONARY_CLOUD_FILE_URL'))  {
			error_log('WEBONARY_CLOUD_FILE_URL is not set! Please do so in wp-config.php.');
			return;
		}

		$encoded_path = array_map('rawurlencode', explode('/', $path));
		$url = rtrim(WEBONARY_CLOUD_FILE_URL, '/') . '/' . implode('/', $encoded_path);

		if (!filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
			error_log($url . ' is not a valid URL. Is WEBONARY_CLOUD_FILE_URL in wp-config.php set to a correct URL?');
			return;
		}

		return $url;
	}

	public static function entryToFakePost($dictionary, $entry) {	
		//<div class="entry" id="ge5175994-067d-44c4-addc-ca183ce782a6"><span class="mainheadword"><span lang="es"><a href="http://localhost:8000/test/ge5175994-067d-44c4-addc-ca183ce782a6">bacalaitos</a></span></span><span class="senses"><span class="sensecontent"><span class="sense" entryguid="ge5175994-067d-44c4-addc-ca183ce782a6"><span class="definitionorgloss"><span lang="en">cod fish fritters/cod croquettes</span></span><span class="semanticdomains"><span class="semanticdomain"><span class="abbreviation"><span class=""><a href="http://localhost:8000/test/?s=&amp;partialsearch=1&amp;tax=9909">1.7</a></span></span><span class="name"><span class=""><a href="http://localhost:8000/test/?s=&amp;partialsearch=1&amp;tax=9909">Puerto Rican Fritters</a></span></span></span></span></span></span></span></div></div>
		$id = self::convertGuidToId($entry->_id);
		$mainHeadWord = '<span class="mainheadword"><span lang="' . $entry->mainHeadWord[0]->lang . '">'
			. '<a href="' . get_site_url() . '/' . $id . '">' . $entry->mainHeadWord[0]->value . '</a></span></span>';
				
		$lexemeform = '';
		if ($entry->audio->src != '') {
			$lexemeform .= '<span class="lexemeform"><span><audio id="' . $entry->audio->id . '">';
			$lexemeform .= '<source src="' . self::remoteFileUrl($dictionary . '/' . $entry->audio->src) . '"></audio>';
			$lexemeform .= '<a class="' . $entry->audio->fileClass . '" href="#' . $entry->audio->id . '" onClick="document.getElementById(\'' . $entry->audio->id .   '\').play()"> </a></span></span>';
		}
	
		// TODO: There can be multiple media files, e.g. Hayashi, one for lexemeform and another in pronunciations

		$sharedgrammaticalinfo = '<span class="sharedgrammaticalinfo"><span class="morphosyntaxanalysis"><span class="partofspeech"><span lang="' . $entry->senses->partOfSpeech->lang . '">' . $entry->senses->partOfSpeech->value . '</span></span></span></span>';
	
		$sensecontent = '<span class="sensecontent"><span class="sense" entryguid="' . $id . '">'
			. '<span class="definitionorgloss">';
		foreach ($entry->senses->definitionOrGloss as $definition)	{
			$sensecontent .= '<span lang="' . $definition->lang . '">' . $definition->value . '</span>';
		}
		$sensecontent .= '</span></span>';
	
		$senses = '<span class="senses">' . $sharedgrammaticalinfo . $sensecontent . '</span>';
	
		$pictures = '';
		if (count($entry->pictures)) {
			$pictures = '<span class="pictures">';
			foreach ($entry->pictures as $picture)	{
				$pictureUrl = self::remoteFileUrl($dictionary . '/' . $picture->src);
				$pictures .= '<div class="picture">';
				$pictures .= '<a class="image" href="' . $pictureUrl . '">';
				$pictures .= '<img src="' . $pictureUrl . '"></a>';
				$pictures .= '<div class="captioncontent"><span class="headword"><span lang="' . $definition->lang . '">' . $picture->caption . '</span></span></div>';
				$pictures .= '</div>';
			}
			$pictures .= '</span>';	
		}
		$post = new stdClass();
		$post->post_title = $entry->mainHeadWord[0]->value;
		$post->post_name = $id;
		$post->post_content = '<div class="entry" id="' . $id . '">' . $mainHeadWord . $lexemeform . $senses . $pictures . '</div>';
		$post->post_status = 'publish';
		$post->comment_status = 'closed';
		$post->post_type = 'post';
		$post->filter = 'raw'; // important, to prevent WP looking up this post in db!		
	
		return $post;
	}

	 public static function entryToReversal($dictionary, $lang, $letter, $entry) {	
		//<div class=post><div xmlns="http://www.w3.org/1999/xhtml" class="reversalindexentry" id="g009ab666-43dd-4f2f-ba62-7017417f6b23"><span class="reversalform"><span lang="en">aardvark</span></span><span class="sensesrs"><span class="sensecontent"><span class="sensesr" entryguid="gee1142ec-65f5-4e23-8d95-413685a48c23"><span class="headword"><span lang="mos"><a href="https://www.webonary.org/moore/gee1142ec-65f5-4e23-8d95-413685a48c23">tãnturi</a></span></span><span class="scientificname"><span lang="en">orycteropus afer</span></span></span></span></span></div></div>
		$id = self::convertGuidToId($entry->_id);
		$reversal_value = '';
		foreach ($entry->senses->definitionOrGloss as $definition)	{
			if (($lang == $definition->lang) && ($letter == substr($definition->value, 0, 1))) {
				$reversal_value = $definition->value;
				break;
			}
		}
	
		$content = '<div class="reversalindexentry">';
		$content .= '<span class="reversalform"><span lang="' . $lang . '">';
		$content .= $reversal_value . '</span></span>';
		
		$content .= '<span class="sensesrs"><span class="sensecontent">';
		$content .= '<span class="sensesr" entryguid="' . $id . '">';
	
		$content .= '<span class="headword"><span lang="' . $entry->mainHeadWord[0]->lang . '">'
			. '<a href="' . get_site_url() . '/' . $id . '">' . $entry->mainHeadWord[0]->value . '</a></span></span>';
	
		$content .= '</span></span></span>';
		$content .= '</<div>';
		
		$reversal = new stdClass();
		$reversal->reversal_content = $content;
	
		return $reversal;
	}

	public static function getBlogDictionaryCode() {
		return (
			is_subdomain_install()
			? explode('.', $_SERVER['HTTP_HOST'])[0]
			: str_replace('/', '', get_blog_details()->path)
		);
	}

	public static function getEntriesAsPosts($doAction, $dictionary, $text) {
		$request = $doAction . '/' . $dictionary;
		$params = array();

		switch ($doAction) {
			case self::$doBrowseByLetter:
				$params['letterHead'] = $text;
				break;
			
			case self::$doSearchFulltext:
				$params['fullText'] = $text;
				break;
			
			default:
				break;
		}

		$response = self::remoteGet($request, $params);
		$posts = [];
	
		if (is_array($response)) { 
			$body = json_decode($response['body']); // use the content
			foreach ($body as $key => $entry) {
				if (self::isValidEntry($entry)) {
					$post = self::entryToFakePost($dictionary, $entry);
					$post->ID = -$key; // negative ID, to avoid clash with a valid post
					$posts[$key] = $post;	
				}
			}		
		}
	
		return $posts;
	}
	
	public static function getEntriesAsReversals($dictionary, $lang, $letter) {	
		$request = self::$doBrowseByLetter . '/' . $dictionary;
		$params = array('letterHead' => $letter, 'lang' => $lang);
		
		$response = self::remoteGet($request, $params);
		$reversals = [];
	
		if (is_array($response)) { 
			$body = json_decode($response['body']); // use the content
			foreach ($body as $key => $entry) {
				if (self::isValidEntry($entry)) {
					$reversals[$key] = self::entryToReversal($dictionary, $lang, $letter, $entry);
				}
			}	
		}
	
		return $reversals;
	}

	public static function getEntryAsPost($doAction, $dictionary, $id) {
		$request = $doAction . '/' . $dictionary;
		$params = array('guid' => $id);
		$response = self::remoteGet($request, $params);
		$entry = json_decode($response['body']); // use the content

		$posts = [];
		if (self::isValidEntry($entry)) { 
			$post = self::entryToFakePost($dictionary, $entry);
			$post->ID = -1; // negative ID, to avoid clash with a valid post
			$posts[0] = $post;	
		}

		return $posts;
	}

	public static function searchEntries($posts, WP_Query $query) {
		if ($query->is_main_query()) {
			$dictionary = Webonary_Cloud::getBlogDictionaryCode();

			$pageName = trim(get_query_var('name'));

			// name begins with 'g', then followed by GUID
			if (preg_match('/^g[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}$/i', $pageName)) {
				return self::getEntryAsPost(self::$doGetEntry, $dictionary, ltrim($pageName, 'g'));
			}

			$searchText = trim(get_search_query());
			if ($searchText != '') {
				return self::getEntriesAsPosts(self::$doSearchFulltext, $dictionary, $searchText);
			}
		}

		return null;
	}
}
