<?php


class Webonary_SearchCookie
{
	private static $search_cookie_name = 'webonary_search';

	// default values
	public $match_whole_word = true;
	public $match_accents = false;


	public static function GetSearchCookie()
	{
		global $search_cookie;
		$search_cookie = new Webonary_SearchCookie();
	}

	private function __construct()
	{
		// get saved settings
		if (isset($_COOKIE[self::$search_cookie_name])) {

			/** @var ISearchCookie $decoded */
			$decoded = unserialize(base64_decode($_COOKIE[self::$search_cookie_name]));

			$this->match_whole_word = $decoded->match_whole_word;
			$this->match_accents = $decoded->match_accents;
		}

		$is_set = (int)filter_input(INPUT_GET, 'search_options_set', FILTER_VALIDATE_INT, ['options' => ['default' => 0]]);

		if ($is_set) {
			$this->match_whole_word = isset($_GET['match_whole_words']);
			$this->match_accents = isset($_GET['match_accents']);
		}

		$this->Save();
	}

	private function Save()
	{
		/*
		 * NB: using this version of the built-in PHP function `setcookie` because it allows us to set the SameSite
		 *     attribute required by Chrome 80 (https://www.chromestatus.com/feature/5633521622188032).
		 *     The following is the text of the warning in the javascript console:
		 *     >> A cookie associated with a cross-site resource at http://test.rvwholesalers.com/ was set
		 *     >> without the `SameSite` attribute. A future release of Chrome will only deliver cookies with
		 *     >> cross-site requests if they are set with `SameSite=None` and `Secure`.
		 */

		$options = [
			'expires' => time() + 30 * 24 * 60 * 60,
			'path' => '/',
			'domain' => self::GetDomain(),
			'secure' => is_ssl(),
			'httponly' => true,
			'samesite' => 'Strict'
		];

		setcookie(self::$search_cookie_name, base64_encode(serialize($this)), $options);
	}

	/**
	 * @return string
	 */
	private static function GetDomain()
	{
		// if not set, probably running from command line
		if (isset($_SERVER['SERVER_NAME']))
			return '';

		$svr_name = $_SERVER['SERVER_NAME'];

		// domain must be empty for localhost
		if (strpos(strtolower($svr_name), 'localhost') === false)
			return '';

		$parts = explode('.', $svr_name);

		// if there are 3 segments, use the root domain (2 segments)
		$part_count = count($parts);

		if ($part_count == 3)
			return '.' . $parts[1] . '.' . $parts[2];
		else
			return '.' . $svr_name;

	}
}
