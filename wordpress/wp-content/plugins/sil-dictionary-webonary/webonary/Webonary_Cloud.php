<?php

class Webonary_Cloud
{
	public static $doBrowseByLetter = 'browse/entry';

	public static $doGetDictionary = 'get/dictionary';

	public static $doGetEntry = 'get/entry';

	public static $doSearchEntry = 'search/entry';

	private static function isValidDictionary($dictionary) {
		return is_object($dictionary) && isset($dictionary->_id);
	} 

	private static function isValidEntry($entry) {
		return is_object($entry) && isset($entry->_id);
	} 

	private static function convertGuidToId($guid) {
		return 'g' . $guid;
	} 

	private static function remoteGetJson($path, $params = array()) {
		if (!defined('WEBONARY_CLOUD_API_URL'))  {
			error_log('WEBONARY_CLOUD_API_URL is not set! Please do so in wp-config.php.');
			return null;
		}

		$encoded_path = array_map('rawurlencode', explode('/', $path));
		$url = rtrim(WEBONARY_CLOUD_API_URL, '/') . '/' . implode('/', $encoded_path);

		if (count($params)) {
			$url .= '?' . build_query(array_map('urlencode', $params));
		}

		if (!filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
			error_log($url . ' is not a valid URL. Is WEBONARY_CLOUD_API_URL in wp-config.php set to a correct URL?');
			return null;
		}

		if (WP_DEBUG){
			error_log('Getting results from ' . $url);
		}

		$response = wp_remote_get($url);

		if (is_wp_error($response)) {
			error_log($response->get_error_message());
			return null;
		}
		
		$body = wp_remote_retrieve_body($response);		
		return json_decode($body);
	}

	private static function remoteFileUrl($path) {
		if (!defined('WEBONARY_CLOUD_FILE_URL'))  {
			error_log('WEBONARY_CLOUD_FILE_URL is not set! Please do so in wp-config.php.');
			return null;
		}

		$encoded_path = array_map('rawurlencode', explode('/', $path));
		$url = rtrim(WEBONARY_CLOUD_FILE_URL, '/') . '/' . implode('/', $encoded_path);

		if (!filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
			error_log($url . ' is not a valid URL. Is WEBONARY_CLOUD_FILE_URL in wp-config.php set to a correct URL?');
			return null;
		}

		return $url;
	}

	public static function entryToFakePost($dictionaryId, $entry) {	
		//<div class="entry" id="ge5175994-067d-44c4-addc-ca183ce782a6"><span class="mainheadword"><span lang="es"><a href="http://localhost:8000/test/ge5175994-067d-44c4-addc-ca183ce782a6">bacalaitos</a></span></span><span class="senses"><span class="sensecontent"><span class="sense" entryguid="ge5175994-067d-44c4-addc-ca183ce782a6"><span class="definitionorgloss"><span lang="en">cod fish fritters/cod croquettes</span></span><span class="semanticdomains"><span class="semanticdomain"><span class="abbreviation"><span class=""><a href="http://localhost:8000/test/?s=&amp;partialsearch=1&amp;tax=9909">1.7</a></span></span><span class="name"><span class=""><a href="http://localhost:8000/test/?s=&amp;partialsearch=1&amp;tax=9909">Puerto Rican Fritters</a></span></span></span></span></span></span></span></div></div>
		$id = self::convertGuidToId($entry->_id);
		$mainHeadWord = '<span class="mainheadword"><span lang="' . $entry->mainHeadWord[0]->lang . '">'
			. '<a href="' . get_site_url() . '/' . $id . '">' . $entry->mainHeadWord[0]->value . '</a></span></span>';
				
		$lexemeform = '';
		if ($entry->audio->src != '') {
			$lexemeform .= '<span class="lexemeform"><span><audio id="' . $entry->audio->id . '">';
			$lexemeform .= '<source src="' . self::remoteFileUrl($dictionaryId . '/' . $entry->audio->src) . '"></audio>';
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
				$pictureUrl = self::remoteFileUrl($dictionaryId . '/' . $picture->src);
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

	 public static function entryToReversal($dictionaryId, $lang, $letter, $entry) {	
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

	public static function getBlogDictionaryId() {
		return (
			is_subdomain_install()
			? explode('.', $_SERVER['HTTP_HOST'])[0]
			: str_replace('/', '', get_blog_details()->path)
		);
	}


	public static function getDictionary($dictionaryId) {
		$request = self::$doGetDictionary . '/' . $dictionaryId;
		$response = self::remoteGetJson($request);
		$dictionary = NULL;
		if (self::isValidDictionary($response)) {
			$dictionary = $response;
		}
		return $dictionary;
	}

	public static function getEntriesAsPosts($doAction, $dictionaryId, $params = array()) {
		$request = $doAction . '/' . $dictionaryId;
		$response = self::remoteGetJson($request, $params);
		$posts = [];
		foreach ($response as $key => $entry) {
			if (self::isValidEntry($entry)) {
				$post = self::entryToFakePost($dictionaryId, $entry);
				$post->ID = -$key; // negative ID, to avoid clash with a valid post
				$posts[$key] = $post;	
			}
		}		
	
		return $posts;
	}
	
	public static function getEntriesAsReversals($dictionaryId, $lang, $letter) {	
		$request = self::$doBrowseByLetter . '/' . $dictionaryId;
		$params = array('text' => $letter, 'lang' => $lang);
		
		$response = self::remoteGetJson($request, $params);
		$reversals = [];
		foreach ($response as $key => $entry) {
			if (self::isValidEntry($entry)) {
				$reversals[$key] = self::entryToReversal($dictionaryId, $lang, $letter, $entry);
			}
		}	

		return $reversals;
	}

	public static function getEntryAsPost($doAction, $dictionaryId, $id) {
		$request = $doAction . '/' . $dictionaryId;
		$params = array('guid' => $id);
		$response = self::remoteGetJson($request, $params);
		$posts = [];
		if (self::isValidEntry($response)) { 
			$post = self::entryToFakePost($dictionaryId, $response);
			$post->ID = -1; // negative ID, to avoid clash with a valid post
			$posts[0] = $post;	
		}

		return $posts;
	}

	public static function registerAndEnqueueMainStyles($dictionaryId) {
		$dictionary = self::getDictionary($dictionaryId);
		$time = strtotime($dictionary->updatedAt);
		if (!is_null($dictionary)){
			foreach($dictionary->mainLanguage->cssFiles as $index => $cssFile) {
				if ($index === 0) {
					$handle = 'configured_stylesheet';
				}
				elseif ($index === 1) {
					$handle = 'overrides_stylesheet';
				}
				else {
					$handle = 'overrides_stylesheet' . $index;
				}

				$cssPath = $dictionaryId . '/' . $cssFile;   
				wp_register_style($handle, self::remoteFileUrl($cssPath), array(), $time);
				wp_enqueue_style($handle);
			}
		}
	}
	
	public static function registerAndEnqueueReversalStyles($dictionaryId, $lang) {
		$dictionary = self::getDictionary($dictionaryId);
		$time = strtotime($dictionary->updatedAt);
		if (!is_null($dictionary)){
			//$baseUrl = rtrim(WEBONARY_CLOUD_FILE_URL, '/') . '/' . $dictionaryId . '/';
			foreach($dictionary->reversalLanguages as $reversal) {
				if ($lang === $reversal->lang) {
					foreach($reversal->cssFiles as $index => $cssFile) {
						$handle = 'reversal_stylesheet' . ($index ? $index : '');
						$cssPath = $dictionaryId . '/' . $cssFile;   
						wp_register_style($handle, self::remoteFileUrl($cssPath), array(), $time);
						wp_enqueue_style($handle);
					}
					break;
				}
			}
		}
	}

	public static function setFontFaces($dictionary, $uploadPath) {
		if (!empty($dictionary->mainLanguage->cssFiles)) {
			$cssPath = $dictionary->_id . '/' . $dictionary->mainLanguage->cssFiles[0];
			$response = wp_remote_get(self::remoteFileUrl($cssPath));

			if (is_wp_error($response)) {
				error_log($response->get_error_message());
				return null;
			}
			
			$body = wp_remote_retrieve_body($response);
			$fontClass = new Webonary_Font_Management();
			$fontClass->set_fontFaces($body, $uploadPath);		
		}
		return null;
	}

	public static function searchEntries($posts, WP_Query $query) {
		if ($query->is_main_query()) {
			$dictionaryId = Webonary_Cloud::getBlogDictionaryId();

			$pageName = trim(get_query_var('name'));

			// name begins with 'g', then followed by GUID
			if (preg_match('/^g[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}$/i', $pageName)) {
				return self::getEntryAsPost(self::$doGetEntry, $dictionaryId, ltrim($pageName, 'g'));
			}
			$searchText = trim(get_search_query());
			if ($searchText === '') {
				$tax = filter_input(INPUT_GET, 'tax', FILTER_SANITIZE_STRING, array('options' => array('default' => '')));
				if ($tax !== '') {
					// This is a listing by semantic domains			
					$apiParams = array(
						'text' => $tax,
						'searchSemDoms' => '1'
					);
	
					return self::getEntriesAsPosts(self::$doSearchEntry, $dictionaryId, $apiParams);					
				}
			} 
			else {			
				$getParams = filter_input_array(
					INPUT_GET, 
					array(
						'key' => array('filter' => FILTER_SANITIZE_STRING),
						'tax' => array('filter' => FILTER_SANITIZE_STRING),
						'match_whole_words' => array('filter' => FILTER_SANITIZE_STRING,
							'options' => array('default' => get_option('include_partial_words') === '1' ? '0' : '1')),
						'match_accents' => array('filter' => FILTER_SANITIZE_STRING, 
							'options' => array('default' => '0'))
					)
				);

				$apiParams = array(
					'text' => $searchText,
					'lang' => $getParams['key'],
					'partOfSpeech' => $getParams['tax'],
					'matchPartial' => ($getParams['match_whole_words'] === '1') ? '' : '1',
					'matchAccents' => ($getParams['match_accents'] === 'on') ? '1' : ''
				);

				return self::getEntriesAsPosts(self::$doSearchEntry, $dictionaryId, $apiParams);
			}
		}

		return null;
	}
}
