<?php

namespace SIL\Webonary\Helpers;

class Request
{
	/**
	 * Allows the filter_input to be overridden for unit testing
	 * @var string
	 */
	public static string $FilterCallable = 'filter_input';

	public static ?string $input_vars_file = null;

	/** @var  string[] */
	private static ?array $url_parts = null;

	/** @var  string[] */
	private static ?array $put_vars = null;

	/** @var  string[] */
	private static ?array $delete_vars = null;


	/**
	 * Doing it this way so we can mock the input values
	 * @param int $type
	 * @param string $variable_name
	 * @param int $filter
	 * @param int|array $options
	 * @return mixed
	 */
	private static function filterInput(int $type, string $variable_name, int $filter=FILTER_DEFAULT, int|array $options=0): mixed
	{
		return call_user_func(self::$FilterCallable ,$type, $variable_name, $filter, $options);
	}


	/**
	 * Returns true if the $_SERVER[$variable_name] is set
	 *
	 * @param string $variable_name
	 *
	 * @return bool
	 */
	public static function IsServerSet(string $variable_name): bool
	{
		return isset($_SERVER[$variable_name]);
	}

	/**
	 * Returns the $_SERVER[$variable_name] value as a string
	 *
	 * @param string $variable_name
	 * @param string $default
	 *
	 * @return string
	 */
	public static function ServerStr(string $variable_name, string $default = ''): string
	{
		$val = (string)self::filterInput(INPUT_SERVER, $variable_name, FILTER_UNSAFE_RAW);
		return self::TrimString($val, $default);
	}

	private static function TrimString($val, $default)
	{
		$val = $val ?? '';
		if ($val == '')
			return $default;

		// do not remove new line or tab - default is " \t\n\r\0\x0B"
		$val = trim((string)$val, " \0\x0B");
		if ($val == '')
			return $default;

		return $val;
	}

	/**
	 * Returns true if the $_POST[$variable_name] is set
	 *
	 * @param string $variable_name
	 *
	 * @return bool
	 */
	public static function IsPostSet(string $variable_name): bool
	{
		return isset($_POST[$variable_name]);
	}

	/**
	 * Returns the $_POST[$variable_name] value as a string
	 *
	 * @param string $variable_name
	 * @param string $default
	 *
	 * @return string
	 */
	public static function PostStr(string $variable_name, string $default = ''): string
	{
		$val = (string)self::filterInput(INPUT_POST, $variable_name, FILTER_UNSAFE_RAW);
		return self::TrimString($val, $default);
	}

	/**
	 * Returns the $_POST[$variable_name] value as a boolean.
	 * Empty, zero and 'false' are false, otherwise true
	 *
	 * @param string $variable_name
	 *
	 * @return boolean
	 */
	public static function PostBool(string $variable_name): bool
	{
		$val = strtolower(self::PostStr($variable_name));
		return match ($val) {
			'', '0', 'false', 'no' => false,
			default => true,
		};
	}

	/**
	 *
	 * @param string $variable_name
	 * @param float $default
	 * @param int|null $decimal_places
	 * @return float
	 */
	public static function PostFloat(string $variable_name, float $default = 0.0, int $decimal_places = null): float
	{
		$val = self::filterInput(INPUT_POST, $variable_name, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

		if (is_null($val) || $val === false || $val === '')
			$val = $default;

		if ($decimal_places != null) $val = number_format($val, $decimal_places);

		return (float)$val;
	}

	/**
	 * Returns the $_POST[$variable_name] value as an int
	 *
	 * @param string $variable_name
	 * @param int $default
	 *
	 * @return int
	 */
	public static function PostInt(string $variable_name, int $default = 0): int
	{
		$val = self::PostFloat($variable_name, $default, 0);
		return (int)$val;
	}

	/**
	 *
	 * @param string $variable_name
	 * @return int|null a timestamp on success, <b>null</b> otherwise.
	 */
	public static function PostDate(string $variable_name): ?int
	{
		$val = self::PostStr($variable_name);

		if (empty($val))
			return null;

		return strtotime($val);
	}

	/**
	 * Returns true if the $_GET[$variable_name] is set
	 * @param string $variable_name
	 * @return bool
	 */
	public static function IsGetSet(string $variable_name): bool
	{
		return isset($_GET[$variable_name]);
	}

	/**
	 * Returns the $_GET[$variable_name] value as a string
	 *
	 * @param string $variable_name
	 * @param string $default
	 *
	 * @return string
	 */
	public static function GetStr(string $variable_name, string $default = ''): string
	{
		$val = (string)self::filterInput(INPUT_GET, $variable_name, FILTER_UNSAFE_RAW);
		return self::TrimString($val, $default);
	}

	/**
	 * Returns the $_GET[$variable_name] value as a boolean.
	 * Empty, zero and 'false' are false, otherwise true
	 *
	 * @param string $variable_name
	 *
	 * @return bool
	 */
	public static function GetBool(string $variable_name): bool
	{
		$val = strtolower(self::GetStr($variable_name));
		return match ($val) {
			'', '0', 'false', 'no' => false,
			default => true,
		};
	}

	/**
	 * Returns the $_GET[$variable_name] value as a float
	 * @param string $variable_name
	 * @param float $default
	 * @param int|null $decimal_places
	 * @return float
	 */
	public static function GetFloat(string $variable_name, float $default = 0.0, int $decimal_places = null): float
	{
		$val = self::filterInput(INPUT_GET, $variable_name, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

		if (is_null($val) || $val === false || $val === '')
			$val = $default;

		if ($decimal_places != null) $val = number_format($val, $decimal_places);

		return (float) $val;
	}

	/**
	 * Returns the $_GET[$variable_name] value as an int
	 * @param string $variable_name
	 * @param int $default
	 * @return int
	 */
	public static function GetInt(string $variable_name, int $default = 0): int
	{
		$val = self::GetFloat($variable_name, $default, 0);
		return (int)$val;
	}

	/**
	 * First looks for the value in $_POST, then $_GET if not found
	 *
	 * @param string $variable_name
	 * @param string|null $default
	 *
	 * @return string|null
	 */
	public static function PostGetStr(string $variable_name, string $default = null): ?string
	{
		if (self::IsPostSet($variable_name))
			return self::PostStr($variable_name);

		if (self::IsGetSet($variable_name))
			return self::GetStr($variable_name);

		return $default;
	}

	/**
	 * First looks for the value in $_POST, then $_GET if not found
	 *
	 * @param string $variable_name
	 * @param int|null $default
	 *
	 * @return int|null
	 */
	public static function PostGetInt(string $variable_name, int $default = null): ?int
	{
		if (self::IsPostSet($variable_name))
			return self::PostInt($variable_name);

		if (self::IsGetSet($variable_name))
			return self::GetInt($variable_name);

		return $default;
	}

	/**
	 * First looks for the value in $_POST, then $_GET if not found
	 *
	 * @param string $variable_name
	 * @param bool $default
	 *
	 * @return bool
	 */
	public static function PostGetBool(string $variable_name, bool $default = false): bool
	{
		if (self::IsPostSet($variable_name))
			return self::PostBool($variable_name);

		if (self::IsGetSet($variable_name))
			return self::GetBool($variable_name);

		return $default;
	}

	/**
	 * Returns an array of strings from the POST collection whose keys all begin with $prefix.
	 * The array key is the same as the key in the POST collection
	 *
	 * @param string $prefix
	 *
	 * @return string[]
	 */
	public static function PostPrefixStr(string $prefix): array
	{
		$return_val = array();

		$keys = array_keys($_POST);
		foreach ($keys as $key) {
			if (str_starts_with($key, $prefix))
				$return_val[$key] = self::PostStr($key);
		}

		return $return_val;
	}

	/**
	 * Returns the URL as an array of segments split on the slashes
	 * @return array|string[]|null
	 */
	public static function UrlParts(): ?array
	{
		if (empty(self::$url_parts)) {

			$uri = strtolower(self::ServerStr('REQUEST_URI'));

			// remove the query string
			$pos = strpos($uri, '?');
			if ($pos !== false)
				$uri = substr($uri, 0, $pos);

			self::$url_parts = preg_split('@/@', $uri, -1, PREG_SPLIT_NO_EMPTY);
		}

		return self::$url_parts;
	}

	public static function GetPutVars(): ?array
	{
		if (self::$put_vars == null) {

			self::$put_vars = [];

			if (defined('PHP_UNIT'))
				$file_name = self::$input_vars_file ?? TEST_RESOURCES . '/php-input.txt';
			else
				$file_name = 'php://input';

			if (self::ServerStr('REQUEST_METHOD') == 'PUT')
				parse_str(file_get_contents($file_name), self::$put_vars);
		}

		return self::$put_vars;
	}

	public static function PutStr(string $variable_name): string
	{
		self::GetPutVars();

		if (isset(self::$put_vars[$variable_name]))
			return filter_var(self::$put_vars[$variable_name], FILTER_UNSAFE_RAW);

		return '';
	}

	public static function PutInt(string $variable_name): int
	{
		self::GetPutVars();

		if (isset(self::$put_vars[$variable_name]))
			return (int)filter_var(self::$put_vars[$variable_name], FILTER_SANITIZE_NUMBER_INT);

		return 0;
	}

	public static function GetDeleteVars(): ?array
	{
		if (self::$delete_vars == null) {

			self::$delete_vars = [];

			if (defined('PHP_UNIT'))
				$file_name = self::$input_vars_file ?? TEST_RESOURCES . '/php-input.txt';
			else
				$file_name = 'php://input';

			if (self::ServerStr('REQUEST_METHOD') == 'DELETE')
				parse_str(file_get_contents($file_name), self::$delete_vars);
		}

		return self::$delete_vars;
	}

	public static function DeleteStr(string $variable_name): string
	{
		self::GetDeleteVars();

		if (isset(self::$delete_vars[$variable_name]))
			return filter_var(self::$delete_vars[$variable_name], FILTER_UNSAFE_RAW);

		return '';
	}

	public static function DeleteInt(string $variable_name): int
	{
		self::GetDeleteVars();

		if (isset(self::$delete_vars[$variable_name]))
			return (int)filter_var(self::$delete_vars[$variable_name], FILTER_SANITIZE_NUMBER_INT);

		return 0;
	}

	public static function UserIsBot(): bool
	{
		$agent = strtolower(self::filterInput(INPUT_SERVER, 'HTTP_USER_AGENT', FILTER_UNSAFE_RAW, ['options' => ['default' => '']]));

		// Check if the user agent is empty
		if (empty($agent)) {
			// Most browsers identify themselves, so possibly a bot
			return true;
		}

		// Declare partial bot user agents (lowercase)
		$botUserAgents = array('bot', 'spider', 'slurp', 'jeeves', 'yahoo', 'yandex', 'bing', 'msn', 'crawl', 'google');

		// Check if a bot name is in the agent
		foreach ($botUserAgents as $botUserAgent) {
			if (stripos($agent, $botUserAgent) !== false) {
				// If it is, return true
				return true;
			}
		}

		// Probably a real user
		return false;
	}

	public static function IsHTTPS(): bool
	{
		return (self::IsServerSet('HTTPS') && self::ServerStr('HTTPS') !== 'off');
	}

	public static function IsLocalhost(): bool
	{
		return (self::IsServerSet('HTTP_HOST') && str_contains(self::ServerStr('HTTP_HOST'), 'localhost'));
	}

	public static function IsAjax(): bool
	{
		return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
	}

	public static function IsAjaxGet(): bool
	{
		if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET')
			return self::IsAjax();

		return false;
	}

	public static function IsAjaxPost(): bool
	{
		if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST')
			return self::IsAjax();

		return false;
	}

	public static function IsAjaxDelete(): bool
	{
		if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'DELETE')
			return self::IsAjax();

		return false;
	}

	public static function IsAjaxPut(): bool
	{
		if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'PUT')
			return self::IsAjax();

		return false;
	}

	/**
	 * This function is for unit testing.
	 *
	 * @return void
	 * @codeCoverageIgnore
	 */
	public static function Reset(): void
	{
		self::$input_vars_file = null;
		self::$url_parts = null;
		self::$put_vars = null;
		self::$delete_vars = null;
	}
}
