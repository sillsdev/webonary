<?php

namespace SIL\Webonary\Helpers;

use Exception;

class GA4Helper
{
	private static string $ga4_id = '';
	private static string $client_id = '';
	private static string $ga4_secret = '';

	public static function SetGA4ID(string $ga4_id): void
	{
		self::$ga4_id = $ga4_id;
	}

	public static function SetClientID(string $client_id): void
	{
		self::$client_id = $client_id;
	}

	public static function SetGA4Secret(string $ga4_secret): void
	{
		self::$ga4_secret = $ga4_secret;
	}

	/**
	 * @return int Expect 204 from Google Analytics
	 * @throws Exception
	 */
	public static function HookFooter(): int
	{
		if (self::$ga4_id == '' || self::$client_id == '' || self::$ga4_secret == '')
			return 0;

		$agent = Request::ServerStr('HTTP_USER_AGENT');
		$ip = Request::ServerStr('REMOTE_ADDR');
		$host = Request::ServerStr('HTTP_HOST');
		$uri = Request::ServerStr('REQUEST_URI');
		$protocol = str_contains($host, 'localhost') ? 'http://' : 'https://';
		$title = html_entity_decode(wp_get_document_title());

		$url = 'https://www.google-analytics.com/mp/collect?measurement_id=' . self::$ga4_id . '&api_secret=' . self::$ga4_secret;
		$payload = [
			'client_id' => self::$client_id,
			'ip_override' => $ip,
			'user_agent' => $agent,
			'events' => [
				'name' => 'page_view',
				'params' => [
					'page_location' => $protocol . $host . $uri,
					'page_title' => $title ?: 'Webonary'
				]
			]
		];

		$response = Curl::PostJson($url, [], $payload, 'webonary.org');

		return $response->HttpCode;
	}

	public static function ResetForTesting(): void
	{
		self::$ga4_id = '';
		self::$client_id = '';
		self::$ga4_secret = '';
	}
}
