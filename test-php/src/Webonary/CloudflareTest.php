<?php
/** @noinspection PhpUnhandledExceptionInspection */

namespace SIL\Tests\Webonary;

use SIL\Webonary\Helpers\Cloudflare;
use SIL\Webonary\Helpers\Curl;
use SIL\Webonary\Helpers\MockCurlResponse;
use WP_UnitTestCase;

class CloudflareTest extends WP_UnitTestCase
{
	public function setUp(): void
	{
		parent::setUp();

		global $cloudflare_settings;
		$cloudflare_settings = null;
	}

	public function tearDown(): void
	{
		parent::tearDown();

		global $cloudflare_settings;
		$cloudflare_settings = null;
	}

	public function testClearCache()
	{
		global $cloudflare_settings;
		$cloudflare_settings = [
			'zone_id' => 'unit-test-zone',
			'api_key' => 'unit-test-api-key'
		];

		$resp1 = new MockCurlResponse(
			'POST',
			(object)[
				'Content' => '{"success":true,"errors":[],"messages":[],"result":{"id":"unit-test-result"}}',
				'ErrorNumber' => 0,
				'ErrorMessage' => '',
				'HttpCode' => 200
			]
		);

		Curl::$MockCurl = true;
		Curl::$UrlMockResponse = [
			'https://api.cloudflare.com/client/v4/zones' => $resp1
		];

		$response = Cloudflare::ClearCache();

		$this->assertEquals(200, $response->HttpCode);
	}

	public function testClearCacheNoApiKey()
	{
		$this->assertNull(Cloudflare::ClearCache());
	}

	public function testClearByPrefix()
	{
		global $cloudflare_settings;
		$cloudflare_settings = [
			'zone_id' => 'unit-test-zone',
			'api_key' => 'unit-test-api-key'
		];

		$resp1 = new MockCurlResponse(
			'POST',
			(object)[
				'Content' => '{"success":true,"errors":[],"messages":[],"result":{"id":"unit-test-result"}}',
				'ErrorNumber' => 0,
				'ErrorMessage' => '',
				'HttpCode' => 200
			]
		);

		Curl::$MockCurl = true;
		Curl::$UrlMockResponse = [
			'https://api.cloudflare.com/client/v4/zones' => $resp1
		];

		$response = Cloudflare::ClearByPrefix('unit-test.com/site-name/prefix');

		$this->assertEquals(200, $response->HttpCode);
	}

	public function testClearByPrefixNoApiKey()
	{
		$this->assertNull(Cloudflare::ClearByPrefix('unit-test.com/site-name/prefix'));
	}
}
