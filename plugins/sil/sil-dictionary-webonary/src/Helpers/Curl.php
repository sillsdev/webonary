<?php

namespace SIL\Webonary\Helpers;

use Exception;
use SIL\Webonary\Interfaces\ICurlResponse;
use stdClass;

class Curl
{
    public static bool $MockCurl = false;

    /**
     * Array key should be part of the URL to which the response belongs
     * @var ICurlResponse[]|MockCurlResponse[]
     */
    public static array $UrlMockResponse = [];

	private static ?array $response_headers = null;

    /**
     * @param string $url
     * @param string $referer
     * @return ICurlResponse|stdClass|null
     * @throws Exception
     */
    public static function GetEx(string $url, string $referer=''): ICurlResponse|stdClass|null
    {
        $headers = [
            'Accept: application/json, */*',
            'Content-Type: application/json; charset=utf-8',
            'Cache-Control: no-cache',
            'Connection: Keep-Alive'
        ];

        return self::DoCurlEx($url, $headers, null, $referer);
    }

    /**
     * @param string $url
     * @param string $referer
     * @return bool|string
     * @throws Exception
     */
    public static function Get(string $url, string $referer=''): bool|string
    {
        $headers = [
            'Accept: application/json, */*',
            'Content-Type: application/json; charset=utf-8',
            'Cache-Control: no-cache',
            'Connection: Keep-Alive'
        ];

        return self::DoCurl($url, $headers, null, $referer);
    }

    /**
     * @param string $url
     * @param array $headers
     * @param array $fields
     * @param bool $return_json
     * @param string $referer
     * @return mixed
     * @throws Exception
     */
    public static function PostEx(string $url, array $headers, array $fields, bool $return_json=false, string $referer=''): mixed
    {
        $params = [CURLOPT_POST => count($fields), CURLOPT_POSTFIELDS => http_build_query($fields)];

        $response = self::DoCurl($url, $headers, $params, $referer);

        if (!$return_json)
            return $response;

        return self::ResponseToJson($response);
    }

    /**
     * Send the POST data as JSON in the post body.
     *
     * @param string $url
     * @param array $headers
     * @param mixed $post_data
     * @param string $referer
     * @return ICurlResponse|stdClass|string
     * @throws Exception
     */
    public static function PostJson(string $url, array $headers, mixed $post_data, string $referer=''): ICurlResponse|stdClass|string
    {
        $default_headers = [
            'Accept' => 'application/json, */*',
            'Content-Type' => 'application/json',
            'Cache-Control' => 'no-cache',
            'Connection' => 'Keep-Alive'
        ];

        $headers = array_merge($default_headers, $headers);

        array_walk($headers, function(&$v, $k) {
            $v = $k . ': ' . $v;
        });

        if (!is_string($post_data))
            $post_data = json_encode($post_data);

        $params = [CURLOPT_POSTFIELDS => $post_data];

        $response = self::DoCurlEx($url, $headers, $params, $referer);

        self::CheckResponseForErrors($response);

        return $response;
    }

    /**
     * @param string $url
     * @param array $fields
     * @param bool $return_json
     * @param string $referer
     * @return mixed
     * @throws Exception
     */
    public static function Post(string $url, array $fields, bool $return_json=false, string $referer=''): mixed
    {
        $headers = [
            'Accept: application/json, */*',
            'Cache-Control: no-cache',
            'Connection: Keep-Alive'
        ];

        return self::PostEx($url, $headers, $fields, $return_json, $referer);
    }

    /**
     * @param $url
     * @param $headers
     * @param array|null $params
     * @param string $referer
     * @return ICurlResponse|stdClass|null
     * @throws Exception
     */
    public static function DoCurlEx($url, $headers, ?array $params=null, string $referer=''): ICurlResponse|stdClass|null
    {
		self::$response_headers = [];
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,60); // 60 second connection timeout
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);       // 5 minute function timeout
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADERFUNCTION,
			function($curl, $header)
			{
				$len = strlen($header);
				$header = explode(':', $header, 2);
				$name = trim($header[0]);

				if (count($header) < 2) {
					self::$response_headers[$name] = '';
					return $len;
				}

				self::$response_headers[$name] = trim($header[1]);

				return $len;
			}
		);

        if ($referer != '')
            curl_setopt($ch, CURLOPT_REFERER, $referer);

        if (!empty($params)) {
            foreach($params as $key => $value) {
                curl_setopt($ch, $key, $value);
            }
        }

        if (!self::$MockCurl) {
            /** @var ICurlResponse|stdClass $response */
            $response = new stdClass();
            $response->Content = curl_exec($ch);
            $response->ErrorNumber = curl_errno($ch);
            $response->ErrorMessage = curl_error($ch);
            $response->HttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            return $response;
        }
        else {

            $custom_request = $params[CURLOPT_CUSTOMREQUEST] ?? '';
            $has_post_fields = array_key_exists(CURLOPT_POSTFIELDS, $params ?? []);

            $return_val = '';
            $return_key = '';

            // look for a mock response that matches the URL
            foreach (self::$UrlMockResponse as $key => $value) {

                // does this response match the requested URL?
                if (!str_contains($url, $key))
                    continue;

                // the old way
                if (!($value instanceof MockCurlResponse))
                    return $value;

                // the new way
                if (!empty($custom_request) && $custom_request == $value->Method) {

                    // this is a PUT or DELETE (or possibly POST)
                    $return_val = $value->Response;
                    $return_key = $key;
                }
                elseif ($has_post_fields && empty($custom_request) && $value->Method == 'POST') {

                    // this is a POST
                    $return_val = $value->Response;
                    $return_key = $key;
                }
                elseif (!$has_post_fields && empty($custom_request) && $value->Method == 'GET') {

                    // this is a GET
                    $return_val = $value->Response;
                    $return_key = $key;
                }
            }

            if (!empty($return_key)) {
                // remove this response from the stack
                unset(self::$UrlMockResponse[$return_key]);

                // return the value
                return $return_val;
            }
        }

        return null;
    }

    /**
     * @param $url
     * @param $headers
     * @param array|null $params
     * @param string $referer
     * @return bool|string
     * @throws Exception
     */
    public static function DoCurl($url, $headers, ?array $params=null, string $referer=''): bool|string
    {
        $response = self::DoCurlEx($url, $headers, $params, $referer);

        self::CheckResponseForErrors($response);

        if (strtolower($response->Content) == 'invalid query')
            throw new Exception('API query error: invalid query');

        return $response->Content;
    }

    /**
     * @param ICurlResponse|stdClass|null $response
     * @return void
     * @throws Exception
     */
    private static function CheckResponseForErrors(ICurlResponse|stdClass|null $response): void
    {
        if (is_null($response))
            throw new Exception('Curl response returned was NULL.');

        if (!empty($response->ErrorNumber))
            throw new Exception($response->ErrorMessage);

        if ($response->HttpCode > 399)
            throw new Exception('HTTP error ' . $response->HttpCode . ': ' . $response->Content, $response->HttpCode);
    }

    /**
     * @param string $response
     * @return mixed
     * @throws Exception
     */
    private static function ResponseToJson(string $response): mixed
    {
        $json = json_decode($response, true);

        if (empty($json))
            throw new Exception('The response received was not decodable as json:' . PHP_EOL . $response);

        return $json;
    }

	public static function GetResponseHeaders(): ?array
	{
		return self::$response_headers;
	}
}
