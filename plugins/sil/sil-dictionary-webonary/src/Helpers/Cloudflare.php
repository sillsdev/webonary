<?php

namespace SIL\Webonary\Helpers;

use Exception;
use SIL\Webonary\Interfaces\ICurlResponse;
use stdClass;

class Cloudflare
{
    /**
     * Clears all files from the webonary cache
	 *
     * @return stdClass|ICurlResponse|null
     * @throws Exception
     */
    public static function ClearCache(): stdClass|ICurlResponse|null
    {
		global $cloudflare_settings;

		if (!isset($cloudflare_settings['api_key']))
			return null;

		list($headers, $url) = self::GetHeadersAndURL();
        return Curl::DoCurlEx($url, $headers, [CURLOPT_POSTFIELDS => json_encode(['purge_everything' => true])]);
    }

    /**
     * Clear by prefix. Starts with host name. Ex: www.webonary.org/site-name
	 *
     * @param array|string $prefixes One or more prefixes to clear
     * @return stdClass|ICurlResponse|null
     * @throws Exception
     */
    public static function ClearByPrefix(array|string $prefixes): stdClass|ICurlResponse|null
    {
		global $cloudflare_settings;
		if (!isset($cloudflare_settings['api_key']))
			return null;

        if (!is_array($prefixes))
            $prefixes = [$prefixes];

		list($headers, $url) = self::GetHeadersAndURL();
        return Curl::DoCurlEx($url, $headers, [CURLOPT_POSTFIELDS => json_encode(['prefixes' => $prefixes])]);
    }

	private static function GetHeadersAndURL(): array
	{
		global $cloudflare_settings;
		$headers = ['Authorization: Bearer ' . $cloudflare_settings['api_key'], 'Content-Type: application/json'];
		$url = 'https://api.cloudflare.com/client/v4/zones/' . $cloudflare_settings['zone_id'] . '/purge_cache';
		return [$headers, $url];
	}
}
