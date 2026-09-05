<?php

namespace SIL\Tests\CreateSite;

use SIL\WebonaryCreateSite2\Models\Application;
use SIL\WebonaryCreateSite2\Models\Applications;
use WP_UnitTestCase;

/**
 * @covers SIL\WebonaryCreateSite2\Models\Application
 * @covers SIL\WebonaryCreateSite2\Models\Applications
 * @noinspection PhpUndefinedNamespaceInspection
 */
class ApplicationsTest extends WP_UnitTestCase
{
	public function testGetActiveApplications()
	{
		$apps = Applications::GetActiveApplications();
		$this->assertGreaterThan(0, count($apps));
	}

	public function testGetByID()
	{
		$app = Applications::GetByID(1234567890.1234);
		$this->assertEquals('unit-test@email.com', $app->FromEmail);
		$this->assertEquals('unit-test@email.com', $app->GetFieldValue('from_email'));
		$this->assertEquals(null, $app->GetFieldValue('bogus'));
		$this->assertEquals('New Application', $app->Status);

		// test new username
		$this->assertEquals('unittest', $app->GetFieldValue('username'));

		// test duplicate username
		$app->FromEmail = 'admin@example.org';
		$this->assertEquals('admin', $app->GetFieldValue('username'));
	}

	public function testGuessUsername()
	{
		// test new username
		$app = Applications::GetByID(1234567890.1234);
		$this->assertEquals('unittest', $app->GetFieldValue('username'));

		// test duplicate username
		$app->FromEmail = 'admin@example.org';
		$this->assertEquals('admin', $app->GetFieldValue('username'));

		// test already-taken username
		$app = Applications::GetByID(1234567890.5678);
		$this->assertEquals('', $app->GetFieldValue('username'));
	}

	public function testConstructBlank()
	{
		$app = new Application();
		$this->assertEquals(0, $app->Timestamp);
		$this->assertEquals('Unknown', $app->Status);
		$this->assertNull($app->ID);
	}

	public function testMarkRemoved()
	{
		global $wpdb;

		$sql = <<<SQL
UPDATE {$wpdb->base_prefix}cf7dbplugin_submits
SET field_name = 'newapplication'
WHERE field_name IN ('created', 'removed') AND submit_time = 1234567890.1234
SQL;
		$wpdb->query($sql);

		$app = Applications::GetByID(1234567890.1234);
		$this->assertEquals('New Application', $app->Status);

		$app->MarkRemoved();
		$app = Applications::GetByID(1234567890.1234);
		$this->assertEquals('Removed', $app->Status);
	}

	public function testMarkCreated()
	{
		global $wpdb;

		$sql = <<<SQL
UPDATE {$wpdb->base_prefix}cf7dbplugin_submits
SET field_name = 'newapplication'
WHERE field_name IN ('created', 'removed') AND submit_time = 1234567890.1234
SQL;
		$wpdb->query($sql);

		$app = Applications::GetByID(1234567890.1234);
		$this->assertEquals('New Application', $app->Status);

		$app->MarkCreated();
		$app = Applications::GetByID(1234567890.1234);
		$this->assertEquals('Created', $app->Status);
	}
}
