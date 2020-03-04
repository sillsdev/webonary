<?php

$api = false;
$verbose = false;

global $wpdb;

if(isset($xhtmlFileURL))
{
	//if using Docker
	if (strpos($xhtmlFileURL, 'localhost:8000') !== false) {
		$xhtmlFileURL = str_replace('localhost:8000', $_SERVER['SERVER_ADDR'], $xhtmlFileURL);
	}
	$path_parts = pathinfo($xhtmlFileURL);

	$filetype = $path_parts['basename'];
	//remove numbers from string
	if(substr($filetype, 0, 8) == 'reversal')
	{
		$filetype = 'reversal';
	}

	$import = new Webonary_Pathway_Xhtml_Import();

	$import->api = $api;
	$import->verbose = $verbose;

	$import->process_xhtml_file($xhtmlFileURL, $filetype);
}