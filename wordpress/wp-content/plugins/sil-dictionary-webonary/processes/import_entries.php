<?php
if (isset($xhtmlFileURL) && isset($filetype) && isset($user)) {
	// if using Docker
	if(strpos($xhtmlFileURL, 'localhost:8000') !== false)
		$xhtmlFileURL = str_replace('localhost:8000', '127.0.0.1', $xhtmlFileURL);

	$import = new Webonary_Pathway_Xhtml_Import();

	$import->api = $api ?? FALSE;
	$import->verbose = $verbose ?? FALSE;

	$import->process_xhtml_file($xhtmlFileURL, $filetype, $user);
}
else {
	error_log("Programming Error: File name ($xhtmlFileURL) and type ($filetype) user record ($user->ID) must be set before importing entries!");
}