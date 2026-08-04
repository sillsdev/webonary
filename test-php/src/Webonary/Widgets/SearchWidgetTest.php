<?php

namespace SIL\Tests\Webonary\Widgets;

use SIL\Tests\Mocks\MockRequest;
use SIL\Webonary\Widgets\SearchWidget;
use Webonary_SearchCookie;
use WP_UnitTestCase;

/**
 * @covers SIL\Webonary\Widgets\SearchWidget
 *
 * @noinspection PhpUndefinedNamespaceInspection
 */
class SearchWidgetTest extends WP_UnitTestCase
{
	public function setUp(): void
	{
		parent::setUp();
		$_SERVER['SERVER_NAME'] = 'webonary.localhost';
		$_SERVER['QUERY_STRING'] = '';
		Webonary_SearchCookie::GetSearchCookie();
	}

	public function testShowWidget_NoLI()
	{
		$widget = new SearchWidget();

		$html = $widget->widget([], []);
		$this->assertStringContainsString('<form name="searchform" id="searchform" method="get"', $html);
		$this->assertStringContainsString('<select name="key" class="webonary_language_select">', $html);
		$this->assertStringNotContainsString('<li', $html);
	}

	public function testShowWidget_YesLI()
	{
		$widget = new SearchWidget(true);

		$html = $widget->widget([], []);
		$this->assertStringContainsString('<form name="searchform" id="searchform" method="get"', $html);
		$this->assertStringContainsString('<select name="key" class="webonary_language_select">', $html);
		$this->assertStringContainsString('<li', $html);
	}

	public function testShowWidget_PasswordRequired()
	{
		add_filter('post_password_required', '__return_true');

		$widget = new SearchWidget();

		$html = $widget->widget([], []);
		$this->assertEquals('Password required.', $html);
	}

	public function testForm()
	{
		$widget = new SearchWidget();

		$html = $widget->form([]);
		$this->assertStringContainsString('<p>There are no settings for this widget</p>', $html);
	}
}
