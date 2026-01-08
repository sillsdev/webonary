<?php

namespace SIL\Tests\Webonary;

use SIL\Webonary\Hooks;
use WP_UnitTestCase;

/**
 * @covers SIL\Webonary\Hooks
 * @noinspection PhpUndefinedNamespaceInspection
 */
class HooksTest extends WP_UnitTestCase
{
	public function testSetHooks()
	{
		global $wp_filter;

		$hook_count = Hooks::SetHooks();
		$this->assertGreaterThan(10, $hook_count);

		$scripts = $wp_filter['wp_enqueue_scripts'];
		$callbacks = $scripts->callbacks;
		$this->assertArrayHasKey(999991, $callbacks);
		$this->assertArrayHasKey('Webonary_Utility::EnqueueJsAndCss', $callbacks[999991]);
	}
}
