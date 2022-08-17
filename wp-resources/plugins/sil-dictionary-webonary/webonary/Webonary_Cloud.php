<?php

class Webonary_Cloud
{
	public static $doBrowseByLetter = 'browse/entry';

	public static $doGetDictionary = 'get/dictionary';

	public static $doGetEntry = 'get/entry';

	public static $doSearchEntry = 'search/entry';

	public static $apiNamespace = 'webonary-cloud/v1';

	private static function isValidDictionary($dictionary): bool {
		return is_object($dictionary) && isset($dictionary->_id);
	}

	private static function isValidEntry($entry): bool {
		return is_object($entry) && isset($entry->_id);
	}

	private static function remoteGetJson($path, $apiParams = array()) {
		if (!defined('WEBONARY_CLOUD_API_URL'))  {
			error_log('WEBONARY_CLOUD_API_URL is not set! Please do so in wp-config.php.');
			return null;
		}

		$encoded_path = array_map('rawurlencode', explode('/', $path));
		$url = rtrim(WEBONARY_CLOUD_API_URL, '/') . '/' . implode('/', $encoded_path);

		if (count($apiParams)) {
			$url .= '?' . build_query(array_map('urlencode', $apiParams));
		}

		if (!filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
			error_log($url . ' is not a valid URL. Is WEBONARY_CLOUD_API_URL in wp-config.php set to a correct URL?');
			return null;
		}

		if (WP_DEBUG_WEBONARY_CLOUD){
			error_log('Getting results from ' . $url);
		}

		// check the cache first
		$found = false;
		$cached_val = wp_cache_get($url, 'webonary', false, $found);
		if ($found !== false)
			return $cached_val;

		$response = wp_remote_get($url);

		if (is_wp_error($response)) {
			error_log($response->get_error_message());
			return null;
		}

		$body = wp_remote_retrieve_body($response);
		$returned_val = json_decode($body);

		wp_cache_set($url, $returned_val, 'webonary');

		return $returned_val;
	}

	private static function remoteFileUrl($path): ?string {
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

	private static function sematicDomainToLink($lang, $domain): string {
		return '<a href="' . get_site_url() . '?s=&lang=' . $lang . '&tax=' . urlencode($domain) . '">' . $domain . '</a>';
	}

	private static function entryToDisplayXhtml($entry) {
		if (!isset($entry->displayXhtml) || $entry->displayXhtml === '') {
			return '';
		}
		else {
			$displayXhtml = Webonary_Utility::fix_entry_xml_links($entry->displayXhtml);
		}

		// set image and audio src path to the cloud, if they are found in the entry
		$baseUrl = self::remoteFileUrl($entry->dictionaryId) . '/';
		$displayXhtml = preg_replace_callback('/src=\"((?!http).+)\"/iU', function ($matches) use($baseUrl) {
			return str_replace($matches[1], $baseUrl . str_replace('\\', '/', $matches[1]), $matches[0]);
		}, $displayXhtml);

		// set the URL for videos and such
		$displayXhtml = preg_replace_callback('/href=\"((?!http|#).+)\"/iU', function ($matches) use($baseUrl) {
			return str_replace($matches[1], $baseUrl . str_replace('\\', '/', $matches[1]), $matches[0]);
		}, $displayXhtml);

		// media player
		$re = '/<span class="mediafile">[^<]*<a[^>]+(href=\"(.+)\")>[^<]+<\/a>[^<]*<\/span>/iUm';
		$displayXhtml = preg_replace_callback($re, function ($matches) use($baseUrl) {
			return str_replace($matches[1], 'onclick="return Webonary.showVideo(\'' . $matches[2] . '\');"', $matches[0]);
		}, $displayXhtml);

		// set semantic domains as links, if they are found in the entry
		if (preg_match_all(
			'/<span class=\"semanticdomain\">.*<span class=\"name\">(<span lang=\"\S+\">(.*)<\/span>)+<\/span>/U',
			$displayXhtml,
			$matches)  > 0) {
			foreach($matches[0] as $semDom) {
				if (preg_match_all(
					'/(?:<span class=\"name\">|\G)+?(<span lang=\"(\S+)\">(.*?)<\/span>)/',
					$semDom,
					$semDomNames) > 0) {
					// <span lang="en">Language and thought</span>
					$newSemDom = $semDom;
					foreach($semDomNames[1] as $index => $semDomNameSpan) {
						$lang = $semDomNames[2][$index];
						$domain = $semDomNames[3][$index];
						// @todo: For some reason, only the first semantic domain is made  in a link. Need to verify if correct.
						$newSemDom = str_replace(
							$semDomNameSpan,
							'<span lang="' . $lang . '">' . self::sematicDomainToLink($lang, $domain) . '</span>',
							$newSemDom);
					}
					$displayXhtml = str_replace($semDom, $newSemDom, $displayXhtml);
				}
			}
		}

		return $displayXhtml;
	}

	private static function validatePermissionToPost($header): stdClass {
		$response = new stdClass();
		$response->message = 'Invalid or missing authorization header';
		$response->code = 401;
		if (isset($header['authorization'][0])) {
			$credentials = base64_decode(str_replace('Basic ', '', $header['authorization'][0]));
			list($username, $password) = explode(':', $credentials, 2);
			if ($username !== '' && $password !== '') {
				$user = wp_authenticate($username, $password);

				if(isset($user->ID)) {
					$blogs = get_blogs_of_user($user->ID);
					$blogsToPost = array();
					foreach ($blogs as $blogId => $blogData) {
						$userData = get_users(array(
							'blog_id' => $blogId,
							'search' => $user->ID)
						);
						if (in_array($userData[0]->roles[0], array('editor', 'administrator'))) {
							$blogsToPost[] = trim($blogData->path, '/');
						}
					}
					if (count($blogsToPost)) {
						$response->message = implode(',', $blogsToPost);
						$response->code = 200;
					}
					else {
						$response->message = 'No permission to post to a dictionary';
					}
				}
				else {
					$response->message = 'Invalid username or password';
				}
			}
		}

		return $response;
	}

	public static function entryToFakePost($entry): stdClass {
		$post = new stdClass();
		$post->post_title = $entry->mainHeadWord[0]->value;
		$post->post_name = $entry->guid;
		$post->post_status = 'publish';
		$post->comment_status = 'closed';
		$post->post_type = 'post';
		$post->filter = 'raw'; // important, to prevent WP looking up this post in db!
		$post->post_content = self::entryToDisplayXhtml($entry);

		return $post;
	}

	 public static function entryToReversal($entry): stdClass {
		$reversal = new stdClass();
		$reversal->reversal_content = self::entryToDisplayXhtml($entry);

		return $reversal;
	}

	public static function getBlogDictionaryId() {
		return (
			is_subdomain_install()
			? explode('.', $_SERVER['HTTP_HOST'])[0]
			: str_replace('/', '', get_blog_details()->path)
		);
	}

	public static function getCurrentLanguage() {
		return (
			function_exists('qtranxf_init_language')
			? qtranxf_getLanguage()
			: 'en'
		);
	}

	public static function getDictionary($dictionaryId) {
		$request = self::$doGetDictionary . '/' . $dictionaryId;
		$response = self::remoteGetJson($request);

		return (self::isValidDictionary($response)) ? $response : null;
	}

	public static function getTotalCount($doAction, $dictionaryId, $apiParams = array()): int {
		$request = $doAction . '/' . $dictionaryId;
		$apiParams['countTotalOnly'] = '1';
		$response = self::remoteGetJson($request, $apiParams);
		return $response->count ?? 0;
	}

	public static function getEntriesAsPosts($doAction, $dictionaryId, $apiParams = array()): array {
		$request = $doAction . '/' . $dictionaryId;
		$response = self::remoteGetJson($request, $apiParams);
		if (empty($response))
			return [];

		$posts = [];
		foreach ($response as $key => $entry) {
			if (self::isValidEntry($entry)) {
				$post = self::entryToFakePost($entry);
				$post->ID = -$key; // negative ID, to avoid clash with a valid post
				$posts[$key] = $post;
			}
		}

		return $posts;
	}

	public static function getEntriesAsReversals($dictionaryId, $apiParams): array {
		$request = self::$doBrowseByLetter . '/' . $dictionaryId;
		$response = self::remoteGetJson($request, $apiParams);
		if (empty($response))
			return [];

		$reversals = [];
		foreach ($response as $key => $entry) {
			if (self::isValidEntry($entry)) {
				$reversals[$key] = self::entryToReversal($entry);
			}
		}

		return $reversals;
	}

	public static function getEntryAsPost($doAction, $dictionaryId, $id): array {
		$request = $doAction . '/' . $dictionaryId;
		$apiParams = array('guid' => $id);
		$entry = self::remoteGetJson($request, $apiParams);
		if (empty($entry))
			return [];

		$posts = [];
		if (self::isValidEntry($entry)) {
			$post = self::entryToFakePost($entry);
			$post->ID = -1; // negative ID, to avoid clash with a valid post
			$posts[0] = $post;
		}

		return $posts;
	}

	public static function registerAndEnqueueMainStyles($dictionaryId, $deps = array()) {

		$dictionary = self::getDictionary($dictionaryId);

		if (!is_null($dictionary)) {

			$time = strtotime($dictionary->updatedAt);
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
				wp_register_style($handle, self::remoteFileUrl($cssPath), $deps, $time);
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
						$handle = 'reversal_stylesheet' . ($index ?: '');
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

	public static function searchEntries($posts, WP_Query $query): ?array {
		global $search_cookie;

		if (!$query->is_main_query())
			return null;

		$dictionaryId = self::getBlogDictionaryId();

		$pageName = trim(get_query_var('name'));

		// name begins with 'g', then followed by GUID
		if (preg_match('/^g[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}$/i', $pageName) === 1) {
			return self::getEntryAsPost(self::$doGetEntry, $dictionaryId, $pageName);
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
			}
		}
		else {
			$getParams = filter_input_array(
				INPUT_GET,
				[
					'key' => ['filter' => FILTER_UNSAFE_RAW],
					'tax' => ['filter' => FILTER_UNSAFE_RAW]
				]
			);

			$apiParams = [
				'text' => $searchText,
				'lang' => $getParams['key'],
				'partOfSpeech' => $getParams['tax'],
				'matchPartial' => $search_cookie->match_whole_word ? '' : '1',  // note reverse logic, b/c params are opposite
				'matchAccents' => $search_cookie->match_accents ? '1' : ''
			];
		}

		if (!isset($apiParams))
			return null;

		$apiParams['pageNumber'] = $query->query_vars['paged'];
		$apiParams['pageLimit'] = $query->query_vars['posts_per_page'];

		$totalEntries = self::getTotalCount(self::$doSearchEntry, $dictionaryId, $apiParams);
		$query->found_posts = $totalEntries;
		$query->max_num_pages = ceil($totalEntries / $apiParams['pageLimit']);

		return self::getEntriesAsPosts(self::$doSearchEntry, $dictionaryId, $apiParams);
	}

	public static function registerApiRoutes() {
		register_rest_route(self::$apiNamespace, '/validate', array(
				'methods' => WP_REST_Server::CREATABLE,
				'callback' => __CLASS__ . '::apiValidate',
				'permission_callback' => '__return_true'
			)
		);

		register_rest_route(self::$apiNamespace, '/resetDictionary', array(
				'methods' => WP_REST_Server::CREATABLE,
				'callback' => __CLASS__ . '::apiResetDictionary',
				'permission_callback' => '__return_true'
			)
		);
	}

	public static function apiValidate($request) {
		$response = self::validatePermissionToPost($request->get_headers());
		return new WP_REST_Response($response->message, $response->code);
	}

	public static function apiResetDictionary($request) {
		$response = self::validatePermissionToPost($request->get_headers());

		if ($response->code !== 200) {
			// error in validation
			return new WP_REST_Response($response->message, $response->code);
		}

		$dictionaryId = self::getBlogDictionaryId();
		$code = 400; // Bad Request
		if ($response->message === '') {
			$message = 'You do not have permission to reset any dictionary';
		}
		elseif (in_array($dictionaryId, explode(',', $response->message))) {
			$code = 200;
			$message = 'Successfully reset dictionary ' . $dictionaryId;
			self::resetDictionary($dictionaryId);
		}
		else {
			$message = 'You do not have permission to reset dictionary ' . $dictionaryId;
		}

		return new WP_REST_Response($message, $code);
	}

	public static function resetDictionary($dictionaryId) {
		$dictionary = self::getDictionary($dictionaryId);
		if (!is_null($dictionary)) {
			$language = $dictionary->mainLanguage;
			update_option('languagecode', $language->lang);
			update_option('totalConfiguredEntries', $language->entriesCount);

			if (!empty($language->letters))
				update_option('vernacular_alphabet', implode(',', $language->letters));

			wp_insert_term(
				$language->title,
				'sil_writing_systems',
				array('description' => $dictionary->mainLanguage->title, 'slug' => $dictionary->mainLanguage->lang)
			);

			foreach($dictionary->reversalLanguages as $index => $reversal) {
				$reversal_index = $index + 1;
				update_option('reversal' . $reversal_index . '_langcode', $reversal->lang);
				update_option('reversal' . $reversal_index . '_alphabet', implode(',', $reversal->letters));

				wp_insert_term(
					$reversal->title,
					'sil_writing_systems',
					array('description' => $reversal->title, 'slug' => $reversal->lang)
				);
			}
			$arrDirectory = wp_upload_dir();
			$uploadPath = $arrDirectory['path'];
			self::setFontFaces($dictionary, $uploadPath);

			// Store this both as a blog option and metadata for convenience
			update_option('useCloudBackend', '1');
			update_site_meta(get_id_from_blogname($dictionaryId), 'useCloudBackend', '1');
		}
	}
}
