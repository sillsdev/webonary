<?php
/** @noinspection PhpUnusedParameterInspection */

namespace SIL\Tests\Mocks;

use WP_Error;

class MockWP_Http
{
	/**
	 * @param $false_response
	 * @param $args
	 * @param $url
	 * @return array|WP_Error
	 */
	public static function HandleHttpRequest($false_response, $args, $url): array|WP_Error
	{
		if ($args['method'] == 'GET') {

			if (preg_match('@/get/dictionary/([\w-]+)@', $url, $matches))
				return self::GetDictionary($matches[1]);

			if (preg_match('@/([\w-]+)\.css@', $url, $matches))
				return self::GetCssFile($matches[1]);
		}

		// @codeCoverageIgnoreStart
		return new WP_Error('Invalid test URL');
		// @codeCoverageIgnoreEnd
	}

	private static function GetFromResources($file_name): array
	{
		$response = file_get_contents(TEST_RESOURCES . '/' . $file_name);
		return json_decode($response, true);
	}

	/**
	 * @param $dictionary_id
	 * @return array
	 */
	private static function GetDictionary($dictionary_id): array
	{
		return self::GetFromResources('cloud-get-database.json');
	}

	/**
	 * @param $file_name
	 * @return array
	 */
	private static function GetCssFile($file_name): array
	{
		return self::GetFromResources('cloud-get-css.json');
	}
}
