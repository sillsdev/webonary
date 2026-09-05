<?php

use SIL\Tests\Mocks\MockWP_Http;

const PHP_UNIT = true;
const TESTS_DIR = __DIR__ . '/test-php';
const CONFIG_DIR = TESTS_DIR . '/config';
const TEST_RESOURCES = TESTS_DIR . '/resources';
const WEBONARY_CLOUD_DEFAULT_DICTIONARY_ID = 'unit_test';
const WEBONARY_CLOUD_API_URL = 'https://unit-test.local/v1/';
const WEBONARY_CLOUD_FILE_URL = 'https://unit-test.local/';
const MULTISITE = true;
const SUBDOMAIN_INSTALL = false;

register_shutdown_function(function(){
    print 'SHUTDOWN' . PHP_EOL;
});

include_once 'wordpress-develop/tests/phpunit/includes/bootstrap.php';

// we need to set this before activating the plugin
update_option('useCloudBackend', '1');

// activate the Webonary plugin
activate_plugin('sil-dictionary-webonary/sil-dictionary.php');
activate_plugin('links-dropdown-widget/plugin.php');
activate_plugin('webonary-create-site2/webonary-create-site2.php');
activate_plugin('contact-form-7-to-database-extension/contact-form-7-db.php');
do_action('init');

// this is so we can mock requests to the cloud
add_filter('pre_http_request', [MockWP_Http::class, 'HandleHttpRequest'], 10, 3);

// remove the test cache directory, if it exists
$cache_dir = rtrim(sys_get_temp_dir(), '/\\') . '/webonary-cache-php-unit';
if (is_dir($cache_dir))
	SIL\Webonary\Helpers\Cache::ClearDirectory($cache_dir, true);

// add a couple test sites
$data = get_site(1)->to_array();

// webonary.org site, no path
$data['domain'] = 'webonary.org';
$data['path'] = '/';
wp_insert_site($data);


// webonary.org site, with path
$data['path'] = '/unit-test';
wp_insert_site($data);


// create an application for new site
global $wpdb;

$wpdb->query("DELETE FROM {$wpdb->base_prefix}cf7dbplugin_st WHERE submit_time > 0");
$wpdb->query("DELETE FROM {$wpdb->base_prefix}cf7dbplugin_submits WHERE submit_time > 0");

$sql = <<<SQL
INSERT INTO {$wpdb->base_prefix}cf7dbplugin_st (submit_time)
VALUES (1234567890.1234),
       (1234567890.5678)
SQL;

$sql = "";
$wpdb->query($sql);

$sql = <<<SQL
INSERT INTO {$wpdb->base_prefix}cf7dbplugin_submits (submit_time, form_name, field_name, field_value, field_order, file)
VALUES  (1234567890.1234, 'Application for Account', 'FirstName', 'Unit', 0, NULL),
        (1234567890.1234, 'Application for Account', 'LastName', 'Test', 1, NULL),
        (1234567890.1234, 'Application for Account', 'from_email', 'unit-test@email.com', 2, NULL),
        (1234567890.1234, 'Application for Account', 'language-name', 'Unit Test Lang', 3, NULL),
        (1234567890.1234, 'Application for Account', 'language-iso-code', 'utl', 4, NULL),
        (1234567890.1234, 'Application for Account', 'country-name', 'Test Land', 5, NULL),
        (1234567890.1234, 'Application for Account', 'region', 'America - North', 6, NULL),
        (1234567890.1234, 'Application for Account', 'desired-url', 'unit-test-lang', 7, NULL),
        (1234567890.1234, 'Application for Account', 'template-to-use', 'http://webonary.localhost/template-spanish', 8, NULL),
        (1234567890.1234, 'Application for Account', 'ui-languages', 'Pig-Latin English', 9, NULL),
        (1234567890.1234, 'Application for Account', 'reversal-languages', 'Pig-Latin English', 10, NULL),
        (1234567890.1234, 'Application for Account', 'the-publication-status-of-the-dictionary', 'Rough draft', 11, NULL),
        (1234567890.1234, 'Application for Account', 'message', '', 12, NULL),
        (1234567890.1234, 'Application for Account', 'copyright-holder', 'Unit Tester', 13, NULL),
        (1234567890.1234, 'Application for Account', 'This-dictionary-has-pictures-and-I-have', 'Yes', 14, NULL),
        (1234567890.1234, 'Application for Account', 'i-have-read-the-terms-of-service', '1', 15, NULL),
        (1234567890.1234, 'Application for Account', 'newapplication', '', 16, NULL),
        (1234567890.1234, 'Application for Account', 'Page Title', 'Application for Webonary account', 17, NULL),
        (1234567890.1234, 'Application for Account', 'Page URL', 'http://webonary.localhost/application-for-webonary-account/', 18, NULL),
        (1234567890.1234, 'Application for Account', 'Submitted From', '000:000:000:000:000:000:000:000', 10000, NULL);
SQL;
$wpdb->query($sql);

// this application has a username (admin) that is already taken
$sql = <<<SQL
INSERT INTO {$wpdb->base_prefix}cf7dbplugin_submits (submit_time, form_name, field_name, field_value, field_order, file)
VALUES  (1234567890.5678, 'Application for Account', 'FirstName', '', 0, NULL),
        (1234567890.5678, 'Application for Account', 'LastName', '', 1, NULL),
        (1234567890.5678, 'Application for Account', 'from_email', 'admin@email.com', 2, NULL),
        (1234567890.5678, 'Application for Account', 'language-name', 'Unit Test Lang', 3, NULL),
        (1234567890.5678, 'Application for Account', 'language-iso-code', 'utl', 4, NULL),
        (1234567890.5678, 'Application for Account', 'country-name', 'Test Land', 5, NULL),
        (1234567890.5678, 'Application for Account', 'region', 'America - North', 6, NULL),
        (1234567890.5678, 'Application for Account', 'desired-url', 'unit-test-lang', 7, NULL),
        (1234567890.5678, 'Application for Account', 'template-to-use', 'http://webonary.localhost/template-spanish', 8, NULL),
        (1234567890.5678, 'Application for Account', 'ui-languages', 'Pig-Latin English', 9, NULL),
        (1234567890.5678, 'Application for Account', 'reversal-languages', 'Pig-Latin English', 10, NULL),
        (1234567890.5678, 'Application for Account', 'the-publication-status-of-the-dictionary', 'Rough draft', 11, NULL),
        (1234567890.5678, 'Application for Account', 'message', '', 12, NULL),
        (1234567890.5678, 'Application for Account', 'copyright-holder', 'Unit Tester', 13, NULL),
        (1234567890.5678, 'Application for Account', 'This-dictionary-has-pictures-and-I-have', 'Yes', 14, NULL),
        (1234567890.5678, 'Application for Account', 'i-have-read-the-terms-of-service', '1', 15, NULL),
        (1234567890.5678, 'Application for Account', 'newapplication', '', 16, NULL),
        (1234567890.5678, 'Application for Account', 'Page Title', 'Application for Webonary account', 17, NULL),
        (1234567890.5678, 'Application for Account', 'Page URL', 'http://webonary.localhost/application-for-webonary-account/', 18, NULL),
        (1234567890.5678, 'Application for Account', 'Submitted From', '000:000:000:000:000:000:000:000', 10000, NULL);
SQL;
$wpdb->query($sql);
