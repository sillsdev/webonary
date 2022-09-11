<?php

include_once 'shared-functions.php';

$po_directory = dirname(__DIR__) . '/plugins/sil-dictionary-webonary/include/lang';

$po_files = glob("$po_directory/*.po");

foreach($po_files as $po_file) {

	echo 'Compiling ' . basename($po_file) . PHP_EOL;
	makeMOFile($po_file);
}

echo 'Finished.' . PHP_EOL;