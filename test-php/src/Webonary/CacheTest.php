<?php
/** @noinspection PhpUnhandledExceptionInspection */

namespace SIL\Tests\Webonary;

use SIL\Webonary\Helpers\Cache;
use SIL\Webonary\Helpers\Curl;
use SIL\Webonary\Helpers\MockCurlResponse;
use WP_UnitTestCase;

/**
 * @covers SIL\Webonary\Helpers\Cache
 *
 * @noinspection PhpUndefinedNamespaceInspection
 */
class CacheTest extends WP_UnitTestCase
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

	public function testGetCacheDir()
	{
		$this->assertStringEndsWith('/webonary-cache-php-unit/site-1', Cache::GetCacheDir());
	}

	public function testSaveGetAndDelete()
	{
		$key = Cache::GetCacheKey('unit-test');

		$original = ['a' => 1, 'b' => 2];
		Cache::Save('unit-test', $original);
		$returned = Cache::Get('unit-test');
		$this->assertEquals($original, $returned);

		$this->assertFileExists($key);

		Cache::Delete('unit-test');
		$this->assertFileDoesNotExist($key);
	}

	public function testClearDirectory()
	{
		$key = Cache::GetCacheKey('unit-test2');
		$dir = dirname($key);

		Cache::Save('unit-test2', 'test');
		$this->assertFileExists($key);
		$this->assertDirectoryExists($dir);

		Cache::ClearDirectory(dirname($dir), true);
		$this->assertFileDoesNotExist($key);
		$this->assertDirectoryDoesNotExist($dir);
	}

	public function testDeleteAllForThisDictionary()
	{
		switch_to_blog(1);

		$cache_dir = Cache::GetCacheDir();
		$file_name = $cache_dir . '/test.cache';
		file_put_contents($file_name, 'unit test');
		$this->assertFileExists($file_name);

		$response = Cache::DeleteAllForThisDictionary();
		$this->assertCount(3, $response);
		$this->assertContains('Cache directory cleared.', $response);
		$this->assertContains('FPM cache NOT cleared.', $response);
		$this->assertContains('Not webonary.org.', $response);
		$this->assertFileDoesNotExist($file_name);

		switch_to_blog(2);

		$response = Cache::DeleteAllForThisDictionary();
		$this->assertCount(3, $response);
		$this->assertContains('Cache directory cleared.', $response);
		$this->assertContains('FPM cache NOT cleared.', $response);
		$this->assertContains('Not a dictionary site.', $response);

		switch_to_blog(3);

		global $cloudflare_settings;
		$cloudflare_settings = [
			'zone_id' => 'unit-test-zone',
			'api_key' => 'unit-test-api-key'
		];

		$resp1 = new MockCurlResponse(
			'POST',
			(object)[
				'Content' => '{"success":false,"errors":["Cloudflare error"],"messages":[],"result":{"id":"unit-test-result"}}',
				'ErrorNumber' => 0,
				'ErrorMessage' => '',
				'HttpCode' => 200
			]
		);

		Curl::$MockCurl = true;
		Curl::$UrlMockResponse = [
			'https://api.cloudflare.com/client/v4/zones' => $resp1
		];

		$response = Cache::DeleteAllForThisDictionary();
		$this->assertCount(3, $response);
		$this->assertContains('Cache directory cleared.', $response);
		$this->assertContains('FPM cache NOT cleared.', $response);
		$this->assertContains('Cloudflare error', $response);

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

		$response = Cache::DeleteAllForThisDictionary();
		$this->assertCount(3, $response);
		$this->assertContains('Cache directory cleared.', $response);
		$this->assertContains('FPM cache NOT cleared.', $response);
		$this->assertContains('Cloudflare cleared.', $response);

		// reset the site
		switch_to_blog(1);
	}
}
