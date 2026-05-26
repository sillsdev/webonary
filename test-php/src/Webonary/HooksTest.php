<?php

namespace SIL\Tests\Webonary;

use SIL\Webonary\Hooks;
use WP_Textdomain_Registry;
use WP_UnitTestCase;

/**
 * @covers SIL\Webonary\Hooks
 * @noinspection PhpUndefinedNamespaceInspection
 */
class HooksTest extends WP_UnitTestCase
{
	public function testSetHooks_Not_Admin()
	{
		global $wp_filter;

		$hook_count = Hooks::SetHooks();
		$this->assertGreaterThan(10, $hook_count);

		$scripts = $wp_filter['wp_enqueue_scripts'];
		$callbacks = $scripts->callbacks;
		$this->assertArrayHasKey(999991, $callbacks);
		$this->assertArrayHasKey('Webonary_Utility::EnqueueJsAndCss', $callbacks[999991]);
	}

	public function testSetHooks_Admin()
	{
		global $wp_filter;

		set_current_screen('dashboard');

		$hook_count = Hooks::SetHooks();
		$this->assertGreaterThan(10, $hook_count);

		$scripts = $wp_filter['admin_enqueue_scripts'];
		$callbacks = $scripts->callbacks;
		$this->assertArrayHasKey('SIL\Webonary\Admin::EnqueueAdminScripts', $callbacks[10]);
	}

	public function testLoadAdditionalTextDomains()
	{
		/** @var WP_Textdomain_Registry $registry */
		$registry = $GLOBALS['wp_textdomain_registry'];

		Hooks::LoadAdditionalTextDomains();

		$this->assertTrue($registry->has('sil_domains'));
	}

	public function testOptimizeStylesheetUri()
	{
		$url = Hooks::OptimizeStylesheetUri('http://example.org/site/wp-content/plugins/test', '', 'test1');
		$this->assertEquals('http://example.org/wp-content/plugins/test', $url);

		// again to test saved value
		$url = Hooks::OptimizeStylesheetUri('http://example.org/site/wp-content/plugins/test', '', 'test1');
		$this->assertEquals('http://example.org/wp-content/plugins/test', $url);
	}

	public function testOptimizeThemeUri()
	{
		$url = Hooks::OptimizeThemeUri('http://example.org/site/wp-content/themes/test', '', 'test2');
		$this->assertEquals('http://example.org/wp-content/themes/test', $url);

		// again to test saved value
		$url = Hooks::OptimizeThemeUri('http://example.org/site/wp-content/themes/test', '', 'test2');
		$this->assertEquals('http://example.org/wp-content/themes/test', $url);
	}

	public function testOptimizePluginUri()
	{
		$url = Hooks::OptimizePluginUri('http://example.org/site/wp-content/plugins/test3', '', '/site/wp-content/plugins/test3');
		$this->assertEquals('http://example.org/wp-content/plugins/test3', $url);

		// again to test saved value
		$url = Hooks::OptimizePluginUri('http://example.org/site/wp-content/plugins/test3', '', '/site/wp-content/plugins/test3');
		$this->assertEquals('http://example.org/wp-content/plugins/test3', $url);
	}

	public function testOptimizeIncludesUri()
	{
		$url = Hooks::OptimizeIncludesUri('http://example.org/site/wp-includes/test4', 'test4');
		$this->assertEquals('http://example.org/wp-includes/test4', $url);

		// again to test saved value
		$url = Hooks::OptimizeIncludesUri('http://example.org/site/wp-includes/test4t', 'test4');
		$this->assertEquals('http://example.org/wp-includes/test4', $url);
	}

	public function testModifyResponseHeaders()
	{
		$headers = Hooks::ModifyResponseHeaders([]);
		$this->assertArrayHasKey('Cache-Control', $headers);
	}
}
