<?php
/** @noinspection PhpUnhandledExceptionInspection */

namespace SIL\Tests\Webonary;

use Exception;
use SIL\Webonary\Helpers\Curl;
use SIL\Webonary\Helpers\MockCurlResponse;
use WP_UnitTestCase;

/**
 * @covers SIL\Webonary\Helpers\Curl
 *
 * @noinspection PhpUndefinedNamespaceInspection
 */
class CurlTest extends WP_UnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testGet()
    {
        $resp1 = new MockCurlResponse(
            'GET',
            (object)[
                'Content' => '{"args": {"q": "Bob"}}',
                'ErrorNumber' => 0,
                'ErrorMessage' => '',
                'HttpCode' => 200
            ]
        );

        Curl::$MockCurl = true;
        Curl::$UrlMockResponse = [
            'https://phpunit.localhost/test1' => $resp1
        ];

        $resp = Curl::Get('https://phpunit.localhost/test1');
        $this->assertNotNull($resp);

        $obj = json_decode($resp);
        $this->assertNotEmpty($obj->args->q);

        $this->assertEquals([], Curl::GetResponseHeaders());
    }

    public function testReferer()
    {
        $resp1 = new MockCurlResponse(
            'GET',
            (object)[
                'Content' => '{"args": {"q": "Bob"}}',
                'ErrorNumber' => 0,
                'ErrorMessage' => '',
                'HttpCode' => 200
            ]
        );

        Curl::$MockCurl = true;
        Curl::$UrlMockResponse = [
            'https://phpunit.localhost/test1' => $resp1
        ];

        $url = 'https://phpunit.localhost/test1';
        $resp = Curl::Get($url, 'https://rvwholesalers.com/test');
        $this->assertNotNull($resp);

        $obj = json_decode($resp);
        $this->assertNotEmpty($obj->args->q);
    }

    public function testPost()
    {
        $resp1 = new MockCurlResponse(
            'POST',
            (object)[
                'Content' => '{"form": {"q": "Bob"}}',
                'ErrorNumber' => 0,
                'ErrorMessage' => '',
                'HttpCode' => 200
            ]
        );

        $resp2 = new MockCurlResponse(
            'POST',
            (object)[
                'Content' => '{"form": {"r": "Bob"}}',
                'ErrorNumber' => 0,
                'ErrorMessage' => '',
                'HttpCode' => 200
            ]
        );

        Curl::$MockCurl = true;
        Curl::$UrlMockResponse = [
            'https://phpunit.localhost/test1' => $resp1,
            'https://phpunit.localhost/test2' => $resp2
        ];

        $resp = Curl::Post('https://phpunit.localhost/test1', ['q' => 'Bob']);
        $this->assertNotNull($resp);

        $obj = json_decode($resp);
        $this->assertNotEmpty($obj->form->q);

        $resp = Curl::Post('https://phpunit.localhost/test2', ['r' => 'Bob'], true);
        $this->assertNotNull($resp);
        $this->assertNotEmpty($resp['form']['r']);
    }

    public function testDoCurl()
    {

        $resp1 = new MockCurlResponse(
            'GET',
            (object)[
                'Content' => '{"args": {"q": "Bob"}}',
                'ErrorNumber' => 0,
                'ErrorMessage' => '',
                'HttpCode' => 200
            ]
        );

        Curl::$MockCurl = true;
        Curl::$UrlMockResponse = [
            'https://phpunit.localhost/test1' => $resp1
        ];

        $url = 'https://phpunit.localhost/test1';

        $headers = array(
            'Accept: application/json, */*',
            'Content-Type: application/json; charset=utf-8',
            'Cache-Control: no-cache',
            'Connection: Keep-Alive'
        );

        $params = [
            CURLOPT_AUTOREFERER => true,
            CURLOPT_CERTINFO => true
        ];

        $resp = Curl::DoCurl($url, $headers, $params);
        $this->assertNotNull($resp);

        $obj = json_decode($resp);
        $this->assertNotEmpty($obj->args->q);
    }

    public function testDoCurl_InvalidQuery()
    {
        $resp1 = new MockCurlResponse(
            'GET',
            (object)[
                'Content' => 'invalid query',
                'ErrorNumber' => 0,
                'ErrorMessage' => '',
                'HttpCode' => 200
            ]
        );

        Curl::$MockCurl = true;
        Curl::$UrlMockResponse = [
            'https://phpunit.localhost/test1' => $resp1
        ];

        $url = 'https://phpunit.localhost/test1';

        $this->expectExceptionMessage('API query error: invalid query');

        Curl::DoCurl($url, []);
    }

    public function testDoCurl_ErrorNumber()
    {
        $resp1 = new MockCurlResponse(
            'GET',
            (object)[
                'Content' => 'Some content',
                'ErrorNumber' => 99,
                'ErrorMessage' => 'Error 99',
                'HttpCode' => 200
            ]
        );

        Curl::$MockCurl = true;
        Curl::$UrlMockResponse = [
            'https://phpunit.localhost/test1' => $resp1
        ];

        $url = 'https://phpunit.localhost/test1';

        $this->expectExceptionMessage('Error 99');

        Curl::DoCurl($url, []);
    }

    public function testBadUrl()
    {
        $url = 'https://non-existing-server.rvw.io';

        $this->expectException(Exception::class);

        Curl::Get($url);
    }

    public function testGetEx()
    {
        $resp1 = new MockCurlResponse(
            'GET',
            (object)[
                'Content' => '{"test_name": "testGetEx"}',
                'ErrorNumber' => 0,
                'ErrorMessage' => '',
                'HttpCode' => 200
            ]
        );

        Curl::$MockCurl = true;
        Curl::$UrlMockResponse = [
            'https://phpunit.localhost/test1' => $resp1
        ];

        $response = Curl::GetEx('https://phpunit.localhost/test1');
        $json = json_decode($response->Content);

        $this->assertEquals('testGetEx', $json->test_name);
    }

    public function testPostJson()
    {
        $resp1 = new MockCurlResponse(
            'POST',
            (object)[
                'Content' => '{"form": {"q": "Bob"}}',
                'ErrorNumber' => 0,
                'ErrorMessage' => '',
                'HttpCode' => 200
            ]
        );

        Curl::$MockCurl = true;
        Curl::$UrlMockResponse = [
            'https://phpunit.localhost/test1' => $resp1
        ];

        $resp = Curl::PostJson('https://phpunit.localhost/test1', [], ['q' => 'Bob']);
        $this->assertNotNull($resp);

        $obj = json_decode($resp->Content);
        $this->assertNotEmpty($obj->form->q);
    }
}
