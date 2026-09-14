<?php
/** @noinspection PhpArrayWriteIsNotUsedInspection */

namespace SIL\Tests\CreateSite;

use SIL\WebonaryCreateSite2\Controllers\NewSite;
use WP_UnitTestCase;

/**
 * @covers SIL\WebonaryCreateSite2\Controllers\NewSite
 * @noinspection PhpUndefinedNamespaceInspection
 */
class NewSiteTest extends WP_UnitTestCase
{
	public function testDisplayNewSite_Blank()
	{
		$html = NewSite::DisplayNewSite();
		$this->assertStringContainsString('<input type="text" id="desired-url" name="desired-url" value=""', $html);
	}

	public function testDisplayNewSite_NotBlank()
	{
		$html = NewSite::DisplayNewSite('1234567890.1234');
		$this->assertStringContainsString('<input type="text" id="desired-url" name="desired-url" value="unit-test-lang"', $html);
	}

	public function testAjaxCreateSite_MissingFields()
	{
		$decoded = NewSite::AjaxCreateSite();

		$this->assertIsArray($decoded['errors']);
		$this->assertContains('Please select a Source Site to Copy.', $decoded['errors']);
		$this->assertContains('Please enter a New Site Address.', $decoded['errors']);
		$this->assertContains('Please enter a New Site Title.', $decoded['errors']);
	}

	public function testGetSiteAdmin()
	{
		$_POST = [
			'username' => 'unittest',
			'from_email' => 'unit_test@email.com',
			'first_name' => 'Unit',
			'last_name' => 'Test'
		];

		// create a new user
		list($user_id, $password) = NewSite::GetSiteAdmin();
		$this->assertGreaterThan(1, $user_id);
		$this->assertEquals(16, strlen($password));

		// try to create a duplicate
		list($user_id2, $password2) = NewSite::GetSiteAdmin();
		$this->assertEquals($user_id, $user_id2);
		$this->assertEquals('N/A', $password2);

		// try to create a duplicate, different username
		$_POST = [
			'username' => '',
			'from_email' => 'unit_test@email.com',
			'first_name' => 'Unit',
			'last_name' => 'Test'
		];
		$response = NewSite::GetSiteAdmin();
		$this->assertEquals('An existing user with the email "unit_test@email.com" was found, but the username is "unittest"', $response['errors'][0]);

		// try to create a duplicate, different email
		$_POST = [
			'username' => 'unittest',
			'from_email' => 'unit_test@web.com',
			'first_name' => 'Unit',
			'last_name' => 'Test'
		];
		$response = NewSite::GetSiteAdmin();
		$this->assertEquals('An existing user with the username "unittest" was found, but the email is "unit_test@email.com"', $response['errors'][0]);
	}

	public function testCreateNewBlog()
	{
		global $wpdb;

		// get the current max blog ID
		$sql = <<<SQL
SELECT MAX(blog_id)
FROM {$wpdb->base_prefix}blogs
SQL;
		$max_blog_id = $wpdb->get_var($sql);

		// create a new user
		$_POST = [
			'username' => 'createblog',
			'from_email' => 'crate_blog@email.com',
			'first_name' => 'Create',
			'last_name' => 'Blog'
		];
		list($user_id, $password) = NewSite::GetSiteAdmin();

		// needed for the multi-site plugin
		$_SERVER['SCRIPT_FILENAME'] = '';
		$_SERVER['REMOTE_ADDR'] = '217.0.0.1';

		// create a new blog
		$response = NewSite::CreateNewBlog($user_id, 'new-blog-1', 'New Blog One');
		$this->assertEquals($max_blog_id + 1, $response['blog_id']);

		// try to create a duplicate blog
		$response2 = NewSite::CreateNewBlog($user_id, 'new-blog-1', 'New Blog One');
		$this->assertEquals('Sorry, that site already exists!', $response2['errors'][0]);

		// copy the information
		$this->assertFalse(get_blog_option($response['blog_id'], 'some-random-option'));
		update_blog_option(3, 'some-random-option', 1234567890);
		NewSite::CopyTemplateToBlog(3, $response['blog_id'], $user_id, $password);
		$this->assertEquals(1234567890, get_blog_option($response['blog_id'], 'some-random-option'));
	}
}
