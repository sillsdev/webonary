<?php

class Webonary_Cloud
{
	private static ?string $dictionary_id = null;
	private static ICloudDictionary|stdClass|null $dictionary = null;

	/** @var ICloudPartOfSpeech[]|null  */
	private static ?array $parts_of_speech = null;

	/** @var ICloudSemanticDomain[]|null  */
	private static ?array $semantic_domains = null;

	public static string $doBrowseByLetter = 'browse/entry';

	public static string $doGetDictionary = 'get/dictionary';

	public static string $doGetEntry = 'get/entry';

	public static string $doSearchEntry = 'search/entry';

	public static string $doDeleteDictionary = 'delete/dictionary';

	public static string $apiNamespace = 'webonary-cloud/v1';

	public static string $languageCategory = 'sil_writing_systems';

	private static function isValidDictionary($dictionary): bool
	{
		return is_object($dictionary) && isset($dictionary->_id);
	}

	private static function isValidEntry($entry): bool
	{
		return is_object($entry) && isset($entry->_id);
	}

	private static function remoteGetJson($path, $apiParams = array())
	{
		if (!defined('WEBONARY_CLOUD_API_URL')) {
			error_log('WEBONARY_CLOUD_API_URL is not set! Please do so in wp-config.php.');
			self::logDebugMessage('WEBONARY_CLOUD_API_URL is not set! Please do so in wp-config.php.');
			return null;
		}

		$encoded_path = array_map('rawurlencode', explode('/', $path));
		$url = rtrim(WEBONARY_CLOUD_API_URL, '/') . '/' . implode('/', $encoded_path);

		if (count($apiParams)) {

			// expanded this to allow for an array of 'parts of speech' to be passed as a value
			$pieces = [];
			foreach ($apiParams as $key => $val) {

				if (is_array($val)) {

					// send each value in the array separately, using the same key
					foreach ($val as $v) {
						$pieces[] = $key . '=' . urlencode($v);
					}
				} else {
					$pieces[] = $key . '=' . urlencode($val);
				}
			}

			$url .= '?' . implode('&', $pieces);
		}

		if (!filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
			error_log($url . ' is not a valid URL. Is WEBONARY_CLOUD_API_URL in wp-config.php set to a correct URL?');
			self::logDebugMessage($url . ' is not a valid URL. Is WEBONARY_CLOUD_API_URL in wp-config.php set to a correct URL?');
			return null;
		}

		self::logDebugMessage('Getting results from ' . $url);

		// check the cache first
		$found = false;
		$cached_val = wp_cache_get($url, 'webonary', false, $found);
		if ($found !== false) {
			self::logDebugMessage('Returned cached results.');
			return $cached_val;
		}

		self::logDebugMessage('Cached results not found, checking the cloud.');

		// the default timeout is just 5 seconds, sometimes not enough to get a dictionary
		$response = wp_remote_get($url, ['timeout' => 30, 'compress' => true]);

		if (is_wp_error($response)) {
			error_log($response->get_error_message());
			self::logDebugMessage($response->get_error_message());
			return null;
		}

		$body = wp_remote_retrieve_body($response);
		$returned_val = json_decode($body);

		wp_cache_set($url, $returned_val, 'webonary');

		self::logDebugMessage('Returned cloud results.');

		return $returned_val;
	}

	private static function remoteFileUrl($path): ?string
	{
		if (!defined('WEBONARY_CLOUD_FILE_URL')) {
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

	private static function semanticDomainToLink($lang, $domain): string
	{
		return '<a href="' . get_site_url() . '?s=&lang=' . $lang . '&tax=' . urlencode($domain) . '">' . $domain . '</a>';
	}

	private static function entryToDisplayXhtml($entry): string
	{
		if (!isset($entry->displayXhtml) || $entry->displayXhtml === '') {
			return '';
		} else {
			$displayXhtml = Webonary_Utility::fix_entry_xml_links($entry->displayXhtml);
		}

		// set image and audio src path to the cloud, if they are found in the entry
		$baseUrl = self::remoteFileUrl($entry->dictionaryId) . '/';
		$displayXhtml = preg_replace_callback('/src=\"((?!http).+)\"/iU', function ($matches) use ($baseUrl) {
			return str_replace($matches[1], $baseUrl . str_replace('\\', '/', $matches[1]), $matches[0]);
		}, $displayXhtml);

		// set the URL for videos and such
		$displayXhtml = preg_replace_callback('/href=\"((?!http|#).+)\"/iU', function ($matches) use ($baseUrl) {
			return str_replace($matches[1], $baseUrl . str_replace('\\', '/', $matches[1]), $matches[0]);
		}, $displayXhtml);

		// media player
		$re = '/<span class="mediafile">[^<]*<a[^>]+(href=\"(.+)\")>[^<]+<\/a>[^<]*<\/span>/iUm';
		$displayXhtml = preg_replace_callback($re, function ($matches) use ($baseUrl) {
			return str_replace($matches[1], 'onclick="return Webonary.showVideo(\'' . $matches[2] . '\');"', $matches[0]);
		}, $displayXhtml);

		// set semantic domains as links, if they are found in the entry
		if (preg_match_all(
				'/<span class=\"semanticdomain\">.*<span class=\"name\">(<span lang=\"\S+\">(.*)<\/span>)+<\/span>/U',
				$displayXhtml,
				$matches) > 0) {
			foreach ($matches[0] as $semDom) {
				if (preg_match_all(
						'/(?:<span class=\"name\">|\G)+?(<span lang=\"(\S+)\">(.*?)<\/span>)/',
						$semDom,
						$semDomNames) > 0) {
					// <span lang="en">Language and thought</span>
					$newSemDom = $semDom;
					foreach ($semDomNames[1] as $index => $semDomNameSpan) {
						$lang = $semDomNames[2][$index];
						$domain = $semDomNames[3][$index];
						// @todo: For some reason, only the first semantic domain is made  in a link. Need to verify if correct.
						$newSemDom = str_replace(
							$semDomNameSpan,
							'<span lang="' . $lang . '">' . self::semanticDomainToLink($lang, $domain) . '</span>',
							$newSemDom);
					}
					$displayXhtml = str_replace($semDom, $newSemDom, $displayXhtml);
				}
			}
		}

		return $displayXhtml;
	}

	private static function validatePermissionToPost($header): stdClass
	{
		$response = new stdClass();
		$response->message = 'Invalid or missing authorization header';
		$response->code = 401;
		if (isset($header['authorization'][0])) {
			$credentials = base64_decode(str_replace('Basic ', '', $header['authorization'][0]));
			list($username, $password) = explode(':', $credentials, 2);
			if ($username !== '' && $password !== '') {
				$user = wp_authenticate($username, $password);

				if (isset($user->ID)) {
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
					} else {
						$response->message = 'No permission to post to a dictionary';
					}
				} else {
					$response->message = 'Invalid username or password';
				}
			}
		}

		return $response;
	}

	public static function entryToFakePost($entry): stdClass
	{
		$post = new stdClass();
		$post->post_title = $entry->mainheadword[0]->value;
		$post->post_name = $entry->guid;
		$post->post_status = 'publish';
		$post->comment_status = 'closed';
		$post->post_type = 'post';
		$post->filter = 'raw'; // important, to prevent WP looking up this post in db!
		$post->post_content = self::entryToDisplayXhtml($entry);

		return $post;
	}

	public static function entryToReversal($entry): stdClass
	{
		$reversal = new stdClass();
		$reversal->reversal_content = self::entryToDisplayXhtml($entry);

		return $reversal;
	}

	public static function getBlogDictionaryId(): string
	{
		if (!is_null(self::$dictionary_id))
			return self::$dictionary_id;

		if (function_exists('is_subdomain_install')) {
			self::$dictionary_id = (
			is_subdomain_install()
				? explode('.', $_SERVER['HTTP_HOST'])[0]
				: str_replace('/', '', get_blog_details()->path)
			);
		} elseif (defined('WEBONARY_CLOUD_DEFAULT_DICTIONARY_ID')) {
			self::$dictionary_id = WEBONARY_CLOUD_DEFAULT_DICTIONARY_ID;
		} else {
			self::$dictionary_id = '';
		}

		return self::$dictionary_id;
	}

	public static function getCurrentLanguage()
	{
		$locale = get_bloginfo('language') ?? 'en';
		return preg_split('/[-_]/', $locale)[0];
	}

	/**
	 * @param string $code
	 * @param string $default
	 * @return string
	 */
	public static function getLanguageName(string $code, string $default = ''): string
	{
		$term = get_term_by('slug', $code, self::$languageCategory);
		if ($term) {
			return $term->name;
		}

		$name = $default ?: $code;
		wp_insert_term(
			$name,
			self::$languageCategory,
			array('description' => $default, 'slug' => $code)
		);

		return $name;
	}

	/**
	 * @param string $dictionaryId
	 * @return ICloudDictionary|stdClass|null
	 */
	public static function getDictionaryById(string $dictionaryId): ICloudDictionary|stdClass|null
	{
		$request = self::$doGetDictionary . '/' . $dictionaryId;
		$response = self::remoteGetJson($request);
		if (self::isValidDictionary($response)) {
			update_option('dictionary', $response);
			return $response;
		}

		return null;
	}

	/**
	 * @return ICloudDictionary|stdClass|null
	 */
	public static function getDictionary(): ICloudDictionary|stdClass|null
	{
			if (!is_null(self::$dictionary))
				return self::$dictionary;

			$dictionary = get_option('dictionary');
			if (empty($dictionary)) {
				$dictionary = self::getDictionaryById(self::getBlogDictionaryId());
			}

			self::$dictionary = $dictionary;
			return self::$dictionary;
	}

	public static function getTotalCount($doAction, $apiParams = array()): int
	{
		$request = $doAction . '/' . self::getBlogDictionaryId();
		$apiParams['countTotalOnly'] = '1';
		$response = self::remoteGetJson($request, $apiParams);
		return $response->count ?? 0;
	}

	public static function getEntriesAsPosts($doAction, $apiParams = array()): array
	{
		$request = $doAction . '/' . self::getBlogDictionaryId();
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

	public static function getEntriesAsReversals($apiParams): array
	{
		$request = self::$doBrowseByLetter . '/' . self::getBlogDictionaryId();
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

	public static function getEntryAsPost($doAction, $id): array
	{
		$request = $doAction . '/' . self::getBlogDictionaryId();
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

	public static function registerAndEnqueueMainStyles($deps = array()): void
	{
		$dictionary = self::getDictionary();

		if (!is_null($dictionary)) {

			$time = strtotime($dictionary->updatedAt);
			foreach ($dictionary->mainLanguage->cssFiles as $index => $cssFile) {
				if ($index === 0) {
					$handle = 'configured_stylesheet';
				} elseif ($index === 1) {
					$handle = 'overrides_stylesheet';
				} else {
					$handle = 'overrides_stylesheet' . $index;
				}

				$cssPath = self::getBlogDictionaryId() . '/' . $cssFile;
				wp_register_style($handle, self::remoteFileUrl($cssPath), $deps, $time);
				wp_enqueue_style($handle);
			}
		}
	}

	public static function registerAndEnqueueReversalStyles($lang): void
	{
		$dictionary = self::getDictionary();
		$time = strtotime($dictionary->updatedAt);
		if (!is_null($dictionary)) {
			//$baseUrl = rtrim(WEBONARY_CLOUD_FILE_URL, '/') . '/' . $dictionaryId . '/';
			foreach ($dictionary->reversalLanguages as $reversal) {
				if ($lang === $reversal->lang) {
					foreach ($reversal->cssFiles as $index => $cssFile) {
						$handle = 'reversal_stylesheet' . ($index ?: '');
						$cssPath = self::getBlogDictionaryId() . '/' . $cssFile;
						wp_register_style($handle, self::remoteFileUrl($cssPath), array(), $time);
						wp_enqueue_style($handle);
					}
					break;
				}
			}
		}
	}

	public static function setFontFaces($dictionary, $uploadPath)
	{
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

	/**
	 * @param $posts
	 * @param WP_Query $query
	 * @return array|null
	 * @noinspection PhpUnusedParameterInspection
	 */
	public static function searchEntries($posts, WP_Query $query): ?array
	{
		global $search_cookie;

		$pageName = trim(get_query_var('name'));

		// name begins with 'g', then followed by GUID
		if (preg_match('/^g[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}$/i', $pageName))
			return self::getEntryAsPost(self::$doGetEntry, $pageName);

		if (!$query->is_main_query() || !is_search())
			return null;

		// get the selected semantic domains
		$semantic_domains = Webonary_Info::getSelectedSemanticDomains();

		// get the search term
		$searchText = Webonary_Utility::UnicodeTrim(get_search_query(false));

		// get the selected parts of speech list
		$taxonomies = Webonary_Parts_Of_Speech::GetPartsOfSpeechSelected();

		if ($searchText === '' && !empty($taxonomies[0])) {

			// This is a listing by semantic domains
			$apiParams = array(
				'text' => $taxonomies[0],
				'searchSemDoms' => '1'
			);

		}
		elseif ($searchText === '' && !empty($semantic_domains) && !empty(get_page_by_path('/browse/categories'))) {

			// redirect to the semantic domains page
			$url_lang = get_query_var('lang');
			$sem_domain_number = $semantic_domains[0];
			$sem_domain = Webonary_SemanticDomains::GetDomainName($sem_domain_number, self::getCurrentLanguage());
			$url = get_site_url() . '/browse/categories/?semdomain=' . urlencode($sem_domain) . '&semnumber=' . $sem_domain_number . '.&lang=en';
			if ($url_lang != '')
				$url .= '&lang=' . $url_lang;

			wp_redirect($url, 302, 'Webonary');
			exit();
		}
		else {
			$key = filter_input(INPUT_GET, 'key', FILTER_UNSAFE_RAW, array('options' => array('default' => '')));

			$apiParams = [
				'text' => $searchText,
				'lang' => $key,
				'partOfSpeech' => $taxonomies,
				'semanticDomain' => $semantic_domains,
				'matchPartial' => $search_cookie->match_whole_word ? '' : '1',  // note reverse logic, b/c params are opposite
				'matchAccents' => $search_cookie->match_accents ? '1' : ''
			];
		}

		if (!isset($apiParams))
			return null;

		$apiParams['pageNumber'] = $query->query_vars['paged'];
		$apiParams['pageLimit'] = $query->query_vars['posts_per_page'];

		$totalEntries = self::getTotalCount(self::$doSearchEntry, $apiParams);
		$query->found_posts = $totalEntries;
		$query->max_num_pages = ceil($totalEntries / $apiParams['pageLimit']);

		return self::getEntriesAsPosts(self::$doSearchEntry, $apiParams);
	}

	public static function registerApiRoutes(): void
	{
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

	public static function apiValidate($request): WP_REST_Response
	{
		$response = self::validatePermissionToPost($request->get_headers());
		return new WP_REST_Response($response->message, $response->code);
	}

	public static function apiResetDictionary($request): WP_REST_Response
	{
		$response = self::validatePermissionToPost($request->get_headers());

		if ($response->code !== 200) {
			// error in validation
			return new WP_REST_Response($response->message, $response->code);
		}

		$dictionaryId = self::getBlogDictionaryId();
		$code = 400; // Bad Request
		if ($response->message === '') {
			$message = 'You do not have permission to reset any dictionary';
		} elseif (in_array($dictionaryId, explode(',', $response->message))) {
			list($code, $message) = self::resetDictionary($dictionaryId);
		} else {
			$message = 'You do not have permission to reset dictionary ' . $dictionaryId;
		}

		return new WP_REST_Response($message, $code);
	}

	public static function resetDictionary($dictionaryId): array
	{
		// Since dictionary is persisted in options, unset it first
		delete_option('dictionary');
		self::$dictionary = null;
		self::$dictionary_id = $dictionaryId;

		$dictionary = self::getDictionary();

		// return Noy Found if $dictionary is null
		if (is_null($dictionary))
			return [404, 'Dictionary ' . $dictionaryId . ' not found'];

		$language = $dictionary->mainLanguage;
		update_option('languagecode', $language->lang);
		update_option('totalConfiguredEntries', $language->entriesCount);

		if (!empty($language->letters))
			update_option('vernacular_alphabet', implode(',', $language->letters));

		wp_insert_term(
			$language->title,
			self::$languageCategory,
			array('description' => $dictionary->mainLanguage->title, 'slug' => $dictionary->mainLanguage->lang)
		);

		$reversal_index = 0;

		foreach ($dictionary->reversalLanguages as $reversal) {
			$reversal_index++;
			update_option('reversal' . $reversal_index . '_langcode', $reversal->lang);
			update_option('reversal' . $reversal_index . '_alphabet', implode(',', $reversal->letters));

			wp_insert_term(
				$reversal->title,
				self::$languageCategory,
				array('description' => $reversal->title, 'slug' => $reversal->lang)
			);
		}

		// remove any leftover reversal settings
		while ($reversal_index < 3) {
			$reversal_index++;
			delete_option('reversal' . $reversal_index . '_langcode');
			delete_option('reversal' . $reversal_index . '_alphabet');
		}

		$arrDirectory = wp_upload_dir();
		$uploadPath = $arrDirectory['path'];
		self::setFontFaces($dictionary, $uploadPath);

		// Store this both as a blog option and metadata for convenience
		update_option('useCloudBackend', '1');
		update_site_meta(get_id_from_blogname($dictionaryId), 'useCloudBackend', '1');

		return [200, 'Successfully reset dictionary ' . $dictionaryId];
	}

	/**
	 * @param string $dictionary_id
	 * @return array
	 * @throws Exception
	 */
	public static function deleteDictionaryData(string $dictionary_id): array
	{
		// make sure the cloud path is set
		if (!defined('WEBONARY_CLOUD_API_URL')) {
			error_log('WEBONARY_CLOUD_API_URL is not set! Please do so in wp-config.php.');
			return ['deleted' => 0, 'msg' => 'WEBONARY_CLOUD_API_URL is not set! Please do so in wp-config.php.'];
		}

		// verify the password
		$pwd = filter_input(INPUT_POST, 'pwd', FILTER_UNSAFE_RAW, ['options' => ['default' => '']]);
		$user = wp_get_current_user();
		if (!wp_check_password($pwd, $user->user_pass, $user->ID)) {
			error_log('Invalid password entered.');
			return ['deleted' => 0, 'msg' => __('Invalid password entered - no data was deleted', 'sil_dictionary')];
		}

		$response = new stdClass();

		// set some headers
		$headers = [
			'User-agent: Webonary (' . $dictionary_id. ')',
			'Accept: */*'
		];

		// build the URL
		$url = rtrim(WEBONARY_CLOUD_API_URL, '/') . '/' . self::$doDeleteDictionary . '/' . rawurlencode($dictionary_id);

		// initialize CURL
		$ch = curl_init($url);
		curl_setopt_array($ch, [
			CURLOPT_CUSTOMREQUEST => 'DELETE',
			CURLOPT_CONNECTTIMEOUT => 60,     // 60 second timeout waiting for connection
			CURLOPT_TIMEOUT => 300,           // 5 minute timeout waiting for the server to finish deleting
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,  // HTTP_2 does not work!
			CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
			CURLOPT_USERPWD => $user->user_login . ':' . $pwd
		]);

		// delete the data now
		$response->Content = json_decode(curl_exec($ch));
		$response->ErrorMessage = curl_error($ch);
		curl_close($ch);

		// first check for a successful response
		if (isset($response->Content->deleteDictionaryCount)) {
			delete_option('dictionary');
			return ['deleted' => 1, 'msg' => __('Finished deleting Webonary data', 'sil_dictionary')];
		}

		// we were not successful, build the error message
		if (!empty($response->Content->Message))
			$err_msg = $response->Content->Message;
		elseif (!empty($response->ErrorMessage))
			$err_msg = $response->ErrorMessage;
		else
			$err_msg = __('Not able to delete Webonary data', 'sil_dictionary');

		return ['deleted' => 0, 'msg' => $err_msg];
	}

	/**
	 * @param string $lang_code
	 * @return array
	 */
	public static function getSemanticDomainSlugs(string $lang_code): array
	{
		$sem_domains = self::getSemanticDomains();

		if (empty($sem_domains))
			return [$lang_code, []];

		$domains = [];

		// get the entries for the current language
		$found = array_filter($sem_domains, function($val) use($lang_code) {
			return $val->lang == $lang_code;
		});

		// if no entries for the current language, pick the first language
		if (empty($found)){
			$lang_code = $sem_domains[0]->lang;
			$found = array_filter($sem_domains, function($val) use($lang_code) {
				return $val->lang == $lang_code;
			});
		}

		foreach($found as $domain) {

			if (empty($domain->abbreviation))
				continue;

			// use the abbreviation as the key so we can sort the results
			$domains[$domain->abbreviation] = array('slug' => $domain->abbreviation, 'name' => $domain->name);
		}

		ksort($domains, SORT_NATURAL);

		return [$lang_code, $domains];
	}

	/**
	 * Filter the list to remove empty values
	 *
	 * @return ICloudPartOfSpeech[]
	 */
	public static function getPartsOfSpeech(): array
	{
		if (!is_null(self::$parts_of_speech))
			return self::$parts_of_speech;

		$dictionary = self::getDictionary();

		if (!self::isValidDictionary($dictionary))
			return [];

		self::$parts_of_speech = [];
		foreach ($dictionary->partsOfSpeech as $item) {
			if (empty($item->entriesCount))
				continue;

			$part = [
				'abbreviation' => $item->abbreviation,
				'lang' => $item->lang,
				'name' => $item->name . '&ensp;(' . $item->entriesCount . ')',
				'guid' => $item->guid
			];
			self::$parts_of_speech[] = (object)$part;
		}

		return self::$parts_of_speech;
	}

	/**
	 * Filter the list to remove empty values
	 *
	 * @return ICloudSemanticDomain[]
	 */
	public static function getSemanticDomains(): array
	{
		if (!is_null(self::$semantic_domains))
			return self::$semantic_domains;

		$dictionary = self::getDictionary();

		if (!self::isValidDictionary($dictionary))
			return [];

		self::$semantic_domains = array_filter($dictionary->semanticDomains, function($val) {
			return !empty($val->abbreviation);
		});

		return self::$semantic_domains;
	}

	private static function logDebugMessage(string $message): void
	{
		if (!defined('WP_DEBUG_WEBONARY_CLOUD') || empty(WP_DEBUG_WEBONARY_CLOUD))
			return;

		if (!defined('DEBUG_LOG_FILE') || empty(DEBUG_LOG_FILE))
			return;

		// make sure the log directory exists
		$log_dir = dirname(DEBUG_LOG_FILE);
		if (!is_dir($log_dir))
			mkdir($log_dir, 0775, true);

		if (!is_dir($log_dir)) {
			error_log('Not able to create directory "' . $log_dir . '"');
			return;
		}

		self::checkLogFileSize(DEBUG_LOG_FILE);

		// using DateTimeImmutable to get microseconds
		$date = new DateTimeImmutable();
		$message = '[' . $date->format('D, d M Y h:i:s.u P') . '] [client ' . $_SERVER['REMOTE_ADDR'] . '] Message: ' . trim($message);
		error_log($message . PHP_EOL, 3, DEBUG_LOG_FILE);
	}

	/**
	 * Keep the log file under 5MB
	 * @param string $file_name
	 * @return void
	 * @noinspection PhpSameParameterValueInspection
	 */
	private static function checkLogFileSize(string $file_name): void
	{
		if (!is_file($file_name)) {
			file_put_contents($file_name, '');
			chmod($file_name, 0666);
		}

		$bytes = filesize($file_name);

		// OK if file doesn't exist
		if (empty($bytes))
			return;

		// OK if under 5MB
		if ($bytes < (1024 * 1024 * 5))
			return;

		$suffix = date('YmdHis');

		if (is_file($file_name . '.' . $suffix)) {

			$i = 1;

			while (is_file($file_name . '.' . $suffix . '.' . $i)) {
				$i++;
			}

			$suffix = $suffix . '.' . $i;
		}

		rename($file_name, $file_name . '.' . $suffix);
	}
}
