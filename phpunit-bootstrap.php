<?php

const PHP_UNIT = true;
const TESTS_DIR = __DIR__ . '/test-php';
const CONFIG_DIR = TESTS_DIR . '/config';

register_shutdown_function(function(){
    print 'SHUTDOWN' . PHP_EOL;
});

include_once 'wordpress-develop/tests/phpunit/includes/bootstrap.php';

// activate the Webonary plugin
activate_plugin('sil-dictionary-webonary/sil-dictionary.php');
