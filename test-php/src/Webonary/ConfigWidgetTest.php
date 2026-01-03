<?php
/** @noinspection PhpUnhandledExceptionInspection */

namespace SIL\Tests\Webonary;

use SIL\Tests\Mocks\MockRequest;
use SIL\Webonary\ConfigWidget;
use WP_UnitTestCase;
use WP_UnitTestCase_Base;

/**
 * @covers SIL\Webonary\ConfigWidget
 * @covers SIL\Webonary\Admin
 * @covers SIL\Tests\Mocks\MockWP_Http
 *
 * @noinspection PhpUndefinedNamespaceInspection
 */
class ConfigWidgetTest extends WP_UnitTestCase
{
	public function setUp(): void
	{
		parent::setUp();
		MockRequest::Init();

		$user_id = WP_UnitTestCase_Base::factory()->user->create(['role' => 'administrator']);
		wp_set_current_user( $user_id );
	}

	public function testShowWidget()
	{
		update_option('hasComposedCharacters', 1);

		$html = ConfigWidget::ShowWidget();
		$this->assertStringContainsString('<h2>Webonary</h2>', $html);
		$this->assertStringContainsString('Webonary provides the administration tools and framework for using WordPress for dictionaries.', $html);
	}

	public function testDeleteData()
	{
		$_POST['delete_data'] = 1;
		$_POST['delete_taxonomies'] = 1;
		$html = ConfigWidget::ShowWidget();
		$this->assertStringContainsString('Dictionary data deleted.', $html);
	}

	public function testRefreshCloudSettings()
	{
		$_POST['refresh_cloud_settings'] = 1;
		$html = ConfigWidget::ShowWidget();
		$this->assertStringContainsString('Dictionary settings refreshed from the cloud.', $html);
	}

	public function testClearLocalCache()
	{
		$_POST['clear_local_cache'] = 1;
		$html = ConfigWidget::ShowWidget();
		$this->assertStringContainsString('Local cache cleared.', $html);
	}

	public function testSendTestEmail()
	{
		$_POST['send_test_email'] = 1;
		$html = ConfigWidget::ShowWidget();
		$this->assertStringContainsString('Test email sent.', $html);
	}

	public function testSaveSettings()
	{
		update_option('useCloudBackend', '');

		$_POST['save_settings'] = 1;
		$_POST['publicationStatus'] = 0;
		$_POST['normalization'] = 'FORM C';
		$_POST['characters'] = '';
		$_POST['inputFont'] = 'Times New Roman';
		$_POST['vernacularLettersFont'] = 'Times New Roman';
		$_POST['languagecode'] = 'en';
		$_POST['txtVernacularName'] = 'English';
		$_POST['countryName'] = '';
		$_POST['languageFamily'] = '';
		$_POST['regionName'] = '';
		$_POST['copyrightHolder'] = '';
		$_POST['txtNotes'] = 'Notes.';
		$_POST['noSearchForm'] = 1;
		$_POST['useCloudBackend'] = '1';

		$html = ConfigWidget::ShowWidget();
		$this->assertStringContainsString('Settings saved.', $html);
	}
}
