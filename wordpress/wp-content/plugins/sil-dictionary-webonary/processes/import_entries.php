<?php

if(exec('echo EXEC') == 'EXEC' && file_exists($argv[1] . "exec-configured.txt") && isset($argv))
{
	define('WP_INSTALLING', true);

	include $argv[1] . 'wp-load.php';
	switch_to_blog($argv[2]);
	include $argv[1] . 'wp-content/plugins/sil-dictionary-webonary/include/configuration.php';
	include $argv[1] . 'wp-content/plugins/sil-dictionary-webonary/include/class_info.php';
	include $argv[1] . 'wp-content/plugins/sil-dictionary-webonary/include/class_utilities.php';

	include $argv[1] . 'wp-content/plugins/sil-dictionary-webonary/include/infrastructure.php';
	install_sil_dictionary_infrastructure();

	include $argv[1] . 'wp-content/plugins/sil-dictionary-webonary/include/xhtml-importer.php';

	add_filter('option_active_plugins', 'Webonary_Utility::disablePlugins');

	//it isn't actually from the api, but saves us renaming the variable to "background" or something like that...
	$api = true;
	$verbose = true;
	$filetype = $argv[3];
	//remove numbers from string
	if(substr($filetype, 0, 8) == 'reversal')
	{
		$filetype = 'reversal';
	}
	$xhtmlFileURL = $argv[4];
	$userid = $argv[5];
}
else
{
	$api = false;
	$verbose = false;
}
global $wpdb;

if(isset($xhtmlFileURL))
{
	//if using Docker
	if (strpos($xhtmlFileURL, 'localhost:8000') !== false) {
		$xhtmlFileURL = str_replace('localhost:8000', $_SERVER['SERVER_ADDR'], $xhtmlFileURL);
	}
	$path_parts = pathinfo($xhtmlFileURL);

	$uploadPath = $path_parts['dirname'];

	$import = new Webonary_Pathway_Xhtml_Import();

	$import->api = $api;
	$import->verbose = $verbose;

	$import->process_xhtml_file($xhtmlFileURL, $filetype);

}
