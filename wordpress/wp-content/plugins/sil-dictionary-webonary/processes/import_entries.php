<?php
if(isset($xhtmlFileURL) && isset($filetype))
{
	// if using Docker
	if(strpos($xhtmlFileURL, 'localhost:8000') !== false)
		$xhtmlFileURL = str_replace('localhost:8000', '127.0.0.1', $xhtmlFileURL);

	$import = new Webonary_Pathway_Xhtml_Import();
	$import->api = false;
	$import->verbose = false;
	$import->process_xhtml_file($xhtmlFileURL, $filetype);
}
else{
	echo "File name and type must be set before importing entries! \n";
}