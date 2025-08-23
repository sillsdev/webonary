<?php

use MongoDB\Client;
use MongoDB\Database;
use MongoDB\Driver\ServerApi;

class Webonary_Cloud
{
	private static ?string $dictionary_id = null;
	private static ICloudDictionary|stdClass|null $dictionary = null;
	private static bool $use_sem_domain_child = false;

	/** @var ICloudPartOfSpeech[]|null  */
	private static ?array $parts_of_speech = null;

	/** @var ICloudSemanticDomain[]|null  */
	private static ?array $semantic_domains = null;

	private static ILanguageEntryCount|stdClass|null $main_language = null;
	private static ?array $language_list = null;

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

	public static function isValidEntry($entry): bool
	{
		return is_object($entry) && isset($entry->_id);
	}

	private static function remoteGetJson($path, $dictionary_id, $apiParams = array())
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
		$hashed_url = hash('haval192,3', $url);
		$cached_val = Webonary_Cache::Get($hashed_url, $dictionary_id);
		if (!is_null($cached_val)) {
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

		Webonary_Cache::Save($hashed_url, $dictionary_id, $returned_val);

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

	private static function semanticDomainHref($lang, $domain_id): string
	{
		if (!empty($lang))
			return get_site_url() . '/browse/categories/?s=&lang=' . $lang . '&semnumber=' . urlencode($domain_id) ;

		return get_site_url() . '/browse/categories/?semnumber=' . urlencode($domain_id) ;
	}

	/**
	 * @throws Exception
	 */
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
		$lang = get_query_var('lang');
		$xml = new SimpleXMLElement($displayXhtml);
		$sem_domains = $xml->xpath('//*[@class="semanticdomain"]');

		if (!empty($sem_domains)) {
			foreach ($sem_domains as $sem_domain) {

				$abbreviations = $sem_domain->xpath('*[@class="abbreviation"]');

				if (empty($abbreviations))
					continue;

				// get the domain number and ID
				if (self::$use_sem_domain_child)
					$domain_text = (string)$abbreviations[0]->span[0]->children();
				else
					$domain_text = (string)$abbreviations[0]->span[0];

				if (!self::$use_sem_domain_child && $domain_text == '') {
					$domain_text = (string)$abbreviations[0]->span[0]->children();
					self::$use_sem_domain_child = true;
				}

				$domain_id = $domain_text;
				if (preg_match('/^([\d\-.]+)$/', $domain_id)) {
					if (!str_ends_with($domain_id, '.'))
						$domain_id .= '.';
				}

				$sem_domain_href = self::semanticDomainHref($lang, $domain_id);

				// change the domain number to a hyperlink
				foreach ($abbreviations as $abbreviation) {

					$tag_lang = (string)$abbreviation->span[0]->attributes()->lang;

					unset($abbreviation->span[0]);

					$span = $abbreviation->addChild('span');
					$span->addAttribute('lang', $tag_lang);
					$a = $span->addChild('a', $domain_text);
					$a->addAttribute('href', $sem_domain_href);
				}

				$names = $sem_domain->xpath('*[@class="name"]');

				// change the domain name to a hyperlink
				foreach ($names as $name) {

					$tag_lang = (string)$name->span[0]->attributes()->lang;

					if (self::$use_sem_domain_child)
						$tag_text = (string)$name[0]->span[0]->children();
					else
						$tag_text = (string)$name[0]->span[0];

					unset($name->span[0]);

					$span = $name->addChild('span');
					$span->addAttribute('lang', $tag_lang);
					$a = $span->addChild('a', $tag_text);
					$a->addAttribute('href', $sem_domain_href);
				}
			}
		}

		return preg_replace('/<\?xml[^\n]*\?>\n/m', '', $xml->asXML());
	}

	private static function validatePermissionToPost($header): stdClass
	{
		// NOTE: in order to get the authorization header, you may need to add this line
		//       to .htaccess: `SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1`
		if (isset($header['authorization'][0])) {
			$credentials = base64_decode(str_replace('Basic ', '', $header['authorization'][0]));
			list($username, $password) = explode(':', $credentials, 2);
		}

		// if the username or password is missing, return not authorized
		if (empty($username) || empty($password))
			return (object)['code' => 401, 'message' => 'Invalid or missing authorization header'];

		// escape single quotes in the password
		$password = str_replace('\'', '\\\'', $password);

		// validate these credentials
		$user = wp_authenticate($username, $password);

		// if no valid user found, return not authorized
		if (!isset($user->id))
			return (object)['code' => 401, 'message' => 'Invalid username or password'];

		// get a list of blogs for which this user is allowed to upload data
		$blogs = get_blogs_of_user($user->ID);
		$blogsToPost = [];
		$allowed_roles = ['editor', 'editorplus', 'administrator'];

		foreach ($blogs as $blogId => $blogData) {

			$user_data = get_users(
				[
					'blog_id' => $blogId,
					'search' => $user->ID
				]
			);

			if (!empty(array_intersect($allowed_roles, $user_data[0]->roles)))
				$blogsToPost[] = trim($blogData->path, '/');
		}

		// if not allowed to upload, return not authorized
		if (!count($blogsToPost))
			return (object)['code' => 401, 'message' => 'No permission to post to a dictionary'];

		// return authorized, with a list of authorized dictionaries
		return (object)['code' => 200, 'message' => implode(',', $blogsToPost)];
	}

	/**
	 * @param $entry
	 * @return WP_Post
	 * @throws Exception
	 */
	public static function entryToFakePost($entry): WP_Post
	{
		$allow_comments = get_option('default_comment_status') == 'open';

		if (is_object($entry->updatedAt))
			$updated_timestamp = intval($entry->updatedAt->{'$date'}->{'$numberLong'} / 1000);
		else
			$updated_timestamp = strtotime($entry->updatedAt);

		$post_date = date('Y-m-d H:i:s', $updated_timestamp);
		$post_title = $entry->mainheadword[0]->value ?? '';

		// create the fake post
		$fake_post = new stdClass();
		$fake_post->post_title = $post_title;
		$fake_post->post_name = $entry->guid;
		$fake_post->post_status = 'publish';
		$fake_post->comment_status = $allow_comments ? 'open' : 'closed';
		$fake_post->comment_count = 0;
		$fake_post->post_type = 'webonary_cloud';
		$fake_post->filter = 'raw'; // important, to prevent WP looking up this post in db!
		$fake_post->post_content = self::entryToDisplayXhtml($entry);
		$fake_post->post_date = $post_date;
		$fake_post->post_date_gmt = $post_date;

		// find the placeholder for linking comments
		$placeholder_posts = get_posts([
			'post_type' => 'webonary_cloud',
			'title' => $post_title,
			'post_status' => 'all',
			'numberposts' => 1,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'orderby' => 'post_date ID',
			'order' => 'DESC'
		]);

		if (empty($placeholder_posts)) {

			// no placeholder found, create it now
			$fake_post->ID = wp_insert_post([
				'post_title' => $post_title,
				'post_name' => 'cloud',
				'guid' => '0',
				'post_status' => 'publish',
				'comment_status' => 'open',
				'post_type' => 'webonary_cloud',
				'post_content' => '',
				'post_date' => $post_date,
				'post_date_gmt' => $post_date
			]);
		}
		else {

			// placeholder found, get the ID for linking comments
			$found_post = $placeholder_posts[0];
			$fake_post->ID = $found_post->ID;

			// if the post has been updated, update the placeholder timestamp also
			if (strtotime($found_post->post_date_gmt) < $updated_timestamp) {
				$found_post->post_date_gmt = $post_date;
				wp_update_post($found_post->to_array());
			}
		}

		// set alignment for the post
		$rtl = get_option('vernacularRightToLeft') == '1';
		$align_class = $rtl ? 'right' : 'left';
		$fake_post->post_content = preg_replace('/(<div[^>]+class="[^"]*?entry[^"]*?)(")/m', "$1 $align_class$2", $fake_post->post_content);

		return new WP_Post($fake_post);
	}

	/**
	 * @param $entry
	 * @return stdClass
	 * @throws Exception
	 */
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
	 * @param ILanguageEntryCount[] $indexed_languages
	 * @return string
	 */
	public static function getLanguageName(string $code, string $default = '', array $indexed_languages = []): string
	{
		if ($default == $code)
			$default = '';

		// first look in the saved terms
		$term = get_term_by('slug', $code, self::$languageCategory);
		if (!empty($term)) {

			// return the name, if we found it
			if (!empty($term->name) && $term->name != $code)
				return $term->name;
		}

		// Check if this is a major language code
		$name = locale_get_display_language($code, 'en');

		// locale_get_display_language returns the locale code if it doesn't know the name
		if ($name == $code)
			$name = '';
		else
			$description = locale_get_display_name($code, 'en');

		// now look in the indexed languages
		if (empty($name)) {
			foreach ($indexed_languages as $lang) {

				if ($lang->language_code == $code && !empty($lang->language_name)) {
					$name = $lang->language_name;
					break;
				}
			}
		}

		// if still not found, try the default
		if (empty($name))
			$name = $default;

		// if the locale was not found, it just returns the $code
		if (empty($name))
			$name = $code;

		if (empty($description))
			$description = $name;

		if (!empty($term)) {
			if (!empty($name) && $name != $code)
				wp_update_term($term->term_id, self::$languageCategory, ['name' => $name, 'description' => $description]);
		}
		else {
			wp_insert_term(
				$name,
				self::$languageCategory,
				array('description' => $description, 'slug' => $code)
			);
		}

		return $name;
	}

	/**
	 * Removes languages that are no longer used.
	 * @param string[] $current_slugs
	 * @return void
	 */
	public static function cleanLanguageList(array $current_slugs): void
	{
		$lower_slugs = array_map('strtolower', $current_slugs);

		/** @var WP_Term[] $terms */
		$terms = get_terms(
			[
				'get' => 'all',
				'taxonomy' => self::$languageCategory,
				'orderby' => 'none',
				'suppress_filter' => 1
			]
		);

		$terms = array_filter($terms, function ($term) use ($lower_slugs) {
			return (!in_array(strtolower($term->slug), $lower_slugs));
		});

		foreach ($terms as $term) {
			wp_delete_term($term->term_id, self::$languageCategory);
		}
	}

	/**
	 * @param string $dictionaryId
	 * @return ICloudDictionary|stdClass|null
	 */
	public static function getDictionaryById(string $dictionaryId): ICloudDictionary|stdClass|null
	{
		$request = self::$doGetDictionary . '/' . $dictionaryId;
		$response = self::remoteGetJson($request, $dictionaryId);
		if (self::isValidDictionary($response)) {
			Webonary_Cache::Save('dictionary', $dictionaryId, $response);
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

			$dictionary = Webonary_Cache::Get('dictionary', self::getBlogDictionaryId());
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
		$response = self::remoteGetJson($request, self::getBlogDictionaryId(), $apiParams);
		return $response->count ?? 0;
	}

	/**
	 * @throws Exception
	 */
	public static function getEntriesAsPosts($doAction, $apiParams = array()): array
	{
		$request = $doAction . '/' . self::getBlogDictionaryId();
		$response = self::remoteGetJson($request, self::getBlogDictionaryId(), $apiParams);
		if (empty($response))
			return [];

		$posts = [];
		foreach ($response as $key => $entry) {
			if (self::isValidEntry($entry)) {
				$post = self::entryToFakePost($entry);
				$posts[$key] = $post;
			}
		}

		return $posts;
	}

	/**
	 * @throws Exception
	 */
	public static function getEntriesAsReversals($apiParams): array
	{
		$request = self::$doBrowseByLetter . '/' . self::getBlogDictionaryId();
		$response = self::remoteGetJson($request, self::getBlogDictionaryId(), $apiParams);
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

	/**
	 * @throws Exception
	 */
	public static function getEntryAsPost($doAction, $id): array
	{
		$request = $doAction . '/' . self::getBlogDictionaryId();
		$apiParams = array('guid' => $id);
		$entry = self::remoteGetJson($request, self::getBlogDictionaryId(), $apiParams);
		if (empty($entry)) {
			status_header(404);
			nocache_headers();
			return [];
		}

		$posts = [];
		if (self::isValidEntry($entry)) {
			$post = self::entryToFakePost($entry);
			$posts[0] = $post;
		}
		else {
			status_header(404);
			nocache_headers();
			exit();
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
	 * @throws Exception
	 */
	public static function searchEntries($posts, WP_Query $query): ?array
	{
		global $search_cookie;

		if (!$query->is_main_query() && !is_search())
			return null;

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

		$key = filter_input(INPUT_GET, 'key', FILTER_UNSAFE_RAW, array('options' => array('default' => '')));

		$apiParams = [
			'text' => $searchText,
			'lang' => $key,
			'partOfSpeech' => $taxonomies,
			'semanticDomain' => $semantic_domains,
			'matchPartial' => $search_cookie->match_whole_word ? '' : '1',  // note reverse logic, b/c params are opposite
			'matchAccents' => $search_cookie->match_accents ? '1' : ''
		];

		$apiParams['pageNumber'] = $query->query_vars['paged'];
		$apiParams['pageLimit'] = min($query->query_vars['posts_per_page'], 300);

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

	public static function apiValidate(WP_REST_Request $request): WP_REST_Response
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
		Webonary_Cache::DeleteAllForDictionary(self::getBlogDictionaryId());
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
			update_option('vernacular_alphabet', self::filterLetterList($language->letters, true));

		$term = get_term_by('slug', $dictionary->mainLanguage->lang, self::$languageCategory);
		if (empty($term)) {
			wp_insert_term(
				$language->title,
				self::$languageCategory,
				array('description' => $dictionary->mainLanguage->title, 'slug' => $dictionary->mainLanguage->lang)
			);
		}

		$reversal_index = 0;

		foreach ($dictionary->reversalLanguages as $reversal) {
			$reversal_index++;
			update_option('reversal' . $reversal_index . '_langcode', $reversal->lang);
			update_option('reversal' . $reversal_index . '_alphabet', implode(',', $reversal->letters));

			$term = get_term_by('slug', $reversal->lang, self::$languageCategory);
			if (empty($term)) {
				wp_insert_term(
					$reversal->title,
					self::$languageCategory,
					array('description' => $reversal->title, 'slug' => $reversal->lang)
				);
			}
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

		// This should be reset for cloud to prevent odd behavior
		update_option('DisplaySubentriesAsMainEntries', 0);

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
			Webonary_Cache::DeleteAllForDictionary(self::getBlogDictionaryId());
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

	public static function commentRedirect($location): string
	{
		// redirect back to the page we just came from
		$from = $_SERVER['HTTP_REFERER'] ?? null;

		if (!empty($from))
			return $from;

		return $location;
	}

	public static function filterLetterList(array|string $letters, bool $return_string = false): array|string
	{
		if (!is_array($letters))
			$letters = explode(',', $letters);

		if (empty($letters))
			return [];

		// remove empty items from the list
		$letters = array_filter($letters, fn($value) => $value !== '');

		if (!$return_string)
			return $letters;

		return implode(',', $letters);
	}

	public static function getLanguageCodes(): array
	{
		$lang_codes = [];
		$dictionary = self::getDictionary();

		if (!is_null($dictionary)) {

			$lang_codes[] = [
				'language_code' => $dictionary->mainLanguage->lang,
				'name' => $dictionary->mainLanguage->title ?? $dictionary->mainLanguage->lang
			];

			foreach ($dictionary->reversalLanguages as $reversal) {

				if (isset($reversal->entriesCount) && $reversal->entriesCount) {

					$lang_codes[] = [
						'language_code' => $reversal->lang,
						'name' => $reversal->title ?? $reversal->lang
					];
				}
			}
		}

		return $lang_codes;
	}

	/**
	 * Gets the contents of a file for this site from the S3 bucket.
	 *
	 * @param $file_name
	 * @return string|null
	 */
	public static function getFileContents($file_name): ?string
	{
		$url = rtrim(WEBONARY_CLOUD_FILE_URL, '/') . '/' . Webonary_Cloud::getBlogDictionaryId() . '/' . $file_name;

		// the default timeout is just 5 seconds, sometimes not enough to get a dictionary
		$response = wp_remote_get($url, ['timeout' => 30, 'compress' => true]);

		if (is_wp_error($response)) {
			error_log($response->get_error_message());
			self::logDebugMessage($response->get_error_message());
			return null;
		}

		return wp_remote_retrieve_body($response);
	}

	public static function HasSemanticDomains(): bool
	{
		$dictionary = self::getDictionary();
		return !empty($dictionary->usedSemanticDomains) || !empty($dictionary->semanticDomainAbbreviationsUsed);
	}

	public static function GetMongoDbConnection($live_site = false): Database
	{
		if ($live_site)
			$settings = WEBONARY_MONGO_PRODUCTION;
		else
			$settings = WEBONARY_MONGO_WORK;

		$catalog = $settings['cat'];
		$uri = "mongodb+srv://{$settings['usr']}:{$settings['pwd']}@{$settings['url']}/?retryWrites=true&w=majority&appName=Cluster0";

		$api_version = new ServerApi(ServerApi::V1);
		$client = new Client($uri, [], ['serverApi' => $api_version]);

		return $client->$catalog;
	}

	/**
	 * @param ICloudDictionary|stdClass $dictionary
	 * @return ILanguageEntryCount[]
	 */
	public static function GetLanguageList(ICloudDictionary|stdClass $dictionary): array
	{
		// did we do this already?
		if (!is_null(self::$language_list))
			return self::$language_list;

		// is this value cached?
		$cache_key = 'language-list';
		$cached_val = Webonary_Cache::Get($cache_key, $dictionary->_id);
		if (!is_null($cached_val)) {
			self::$language_list = $cached_val;
			return self::$language_list;
		}

		$main_language = self::GetMainLanguage($dictionary);

		$language_list[strtolower($main_language->language_code)] = $main_language;

		/** @var ICloudLanguage[] $reversalLanguages */
		$reversalLanguages = array_values(array_filter($dictionary->reversalLanguages, function ($v) {
			return !empty($v->lang);
		}));

		foreach ($reversalLanguages as $reversal) {

			$key = strtolower($reversal->lang);
			if (array_key_exists($key, $language_list))
				continue;

			/** @var ILanguageEntryCount $lang */
			$lang = new stdClass();
			$lang->language_name = Webonary_Cloud::getLanguageName($reversal->lang, $reversal->title, $language_list);
			$lang->language_code = $reversal->lang;
			$lang->total_indexed = $reversal->entriesCount ?? 0;
			$lang->is_main = false;
			$lang->is_reversal = true;
			$language_list[$key] = $lang;
		}

		$other_search_languages = array_filter($dictionary->definitionOrGlossLangs, function($search_lang) use($main_language) {
			return $search_lang != $main_language->language_code;
		});

		foreach ($other_search_languages as $search_lang) {

			$key = strtolower($search_lang);
			if (array_key_exists($key, $language_list))
				continue;

			$localized_name = __(Webonary_Cloud::getLanguageName($search_lang, '', $language_list));

			/** @var ILanguageEntryCount $lang */
			$lang = new stdClass();
			$lang->language_name = $localized_name;
			$lang->language_code = $search_lang;
			$lang->total_indexed = 0;
			$lang->is_main = false;
			$lang->is_reversal = false;
			$language_list[$key] = $lang;
		}

		// mark hidden languages
		foreach ($language_list as $lang) {

			$term = get_term_by('slug', $lang->language_code, self::$languageCategory);
			$text_field_hidden = get_term_meta($term->term_id, 'hide_language', true);
			$lang->hidden = !empty($text_field_hidden);
		}

		self::$language_list = $language_list;

		Webonary_Cache::Save($cache_key, $dictionary->_id, self::$language_list);

		return self::$language_list;
	}

	/**
	 * @param ICloudDictionary|stdClass $dictionary
	 * @return ILanguageEntryCount|stdClass
	 */
	public static function GetMainLanguage(ICloudDictionary|stdClass $dictionary): ILanguageEntryCount|stdClass
	{
		if (!is_null(self::$main_language))
			return self::$main_language;

		$cache_key = 'main-language';
		$cached_val = Webonary_Cache::Get($cache_key, $dictionary->_id);
		if (!is_null($cached_val)) {
			self::$main_language = $cached_val;
			return self::$main_language;
		}

		self::$main_language = new stdClass();
		self::$main_language->language_name = Webonary_Cloud::getLanguageName($dictionary->mainLanguage->lang, $dictionary->mainLanguage->title);
		self::$main_language->language_code = $dictionary->mainLanguage->lang;
		self::$main_language->total_indexed = $dictionary->mainLanguage->entriesCount ?? 0;
		self::$main_language->is_main = true;
		self::$main_language->is_reversal = false;

		Webonary_Cache::Save($cache_key, $dictionary->_id, self::$main_language);

		return self::$main_language;
	}
}
