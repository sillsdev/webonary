<?php
/** @noinspection PhpUnhandledExceptionInspection */

namespace SIL\Tests\Webonary;

use SIL\Webonary\Helpers\Curl;
use SIL\Webonary\Helpers\GA4Helper;
use SIL\Webonary\Helpers\MockCurlResponse;
use WP_UnitTestCase;

/**
 * @covers SIL\Webonary\Helpers\GA4Helper
 * @covers SIL\Webonary\Helpers\Curl
 * @covers SIL\Webonary\Helpers\MockCurlResponse
 *
 * @noinspection PhpUndefinedNamespaceInspection
 */
class GA4HelperTest extends WP_UnitTestCase
{
	public function setUp(): void
	{
		parent::setUp();
		GA4Helper::ResetForTesting();
	}

	public function tearDown(): void
	{
		parent::tearDown();
		GA4Helper::ResetForTesting();
	}

	public function testGA4Helper_Has_Numbers()
	{
		$resp = new MockCurlResponse(
			'POST',
			(object)[
				'Content' => '',
				'ErrorNumber' => 0,
				'ErrorMessage' => '',
				'HttpCode' => 204
			]
		);

		Curl::$MockCurl = true;
		Curl::$UrlMockResponse = [
			'https://www.google-analytics.com/mp' => $resp
		];

		GA4Helper::SetGA4ID('GA4-ID1');
		GA4Helper::SetClientID('Client-ID1');
		GA4Helper::SetGA4Secret('GA4-Secret1');
		$code = GA4Helper::HookFooter();
		$this->assertEquals(204, $code);
	}

	public function testGA4Helper_Has_No_Numbers()
	{
		$code = GA4Helper::HookFooter();
		$this->assertEquals(0, $code);
	}
}
