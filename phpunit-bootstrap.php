<?php

const PHP_UNIT = true;
const TESTS_DIR = __DIR__ . '/test-php';
const CONFIG_DIR = TESTS_DIR . '/config';
const TEST_RESOURCES = TESTS_DIR . '/resources';
const WEBONARY_CLOUD_DEFAULT_DICTIONARY_ID = 'unit_test';
const WEBONARY_CLOUD_API_URL = 'https://unit-test.local/v1/';

register_shutdown_function(function(){
    print 'SHUTDOWN' . PHP_EOL;
});

include_once 'wordpress-develop/tests/phpunit/includes/bootstrap.php';

// activate the Webonary plugin
activate_plugin('sil-dictionary-webonary/sil-dictionary.php');
do_action('init');
