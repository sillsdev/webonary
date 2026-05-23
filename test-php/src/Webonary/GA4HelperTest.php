<?php
/** @noinspection PhpUnhandledExceptionInspection */

namespace SIL\Tests\Webonary;

use SIL\Webonary\Helpers\GA4Helper;
use WP_UnitTestCase;

/**
 * @covers SIL\Webonary\Helpers\GA4Helper
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

	public function testGA4Helper_Tag_Found()
	{
		GA4Helper::SetGA4ID('PHP-UNIT-GA4-ID1');
		GA4Helper::HookMonsterInsightsG4ID('PHP-UNIT-GA4-ID1');
		$html = GA4Helper::HookHead();
		$this->assertEquals('', $html);
	}

	public function testGA4Helper_Tag_Not_Found()
	{
		GA4Helper::SetGA4ID('PHP-UNIT-GA4-ID2');
		$html = GA4Helper::HookHead();
		$this->assertStringContainsString('<script async src="https://www.googletagmanager.com/gtag/js?id=PHP-UNIT-GA4-ID2&l=webonaryLayer"></script>', $html);
	}
}
