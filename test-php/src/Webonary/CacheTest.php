<?php

namespace SIL\Tests\Webonary;

use SIL\Webonary\Helpers\Cache;
use WP_UnitTestCase;

/**
 * @covers SIL\Webonary\Helpers\Cache
 *
 * @noinspection PhpUndefinedNamespaceInspection
 */
class CacheTest extends WP_UnitTestCase
{
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
}
