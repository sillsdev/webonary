<?php

namespace SIL\Tests\CreateSite;

use SIL\WebonaryCreateSite2\Hooks;
use WP_UnitTestCase;

/**
 * @covers SIL\WebonaryCreateSite2\Hooks
 * @noinspection PhpUndefinedNamespaceInspection
 */
class HooksTest extends WP_UnitTestCase
{
	public function testSetHooks()
	{
		$hook_count = Hooks::SetHooks();
		$this->assertEquals(0, $hook_count);
	}

	public function testSetHooks_Admin()
	{
		global $wp_filter;

		set_current_screen('dashboard');

		$hook_count = Hooks::SetHooks();
		$this->assertEquals(2, $hook_count);

		$scripts = $wp_filter['admin_enqueue_scripts'];
		$callbacks = $scripts->callbacks;
		$this->assertArrayHasKey('SIL\WebonaryCreateSite2\Hooks::EnqueueAdminScripts', $callbacks[10]);
	}
}
