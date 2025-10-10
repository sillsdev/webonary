<?php


$lang_code = 'yo';
$locale_code = 'yo_NG';


include_once 'shared-functions.php';

$input_file_name = 'input/plugin-' . $locale_code . '.tab';
$po_file_name = dirname(__DIR__) . '/plugins/sil-dictionary-webonary/include/lang/sil_dictionary-' . $locale_code . '.po';

if (!is_file($po_file_name))
	copy(__DIR__ . '/english/sil_dictionary-en_US.po', $po_file_name);

function getPOLines($words): array
{
	global $po_file_name;

	// load the existing localizations
	$lines = file($po_file_name, FILE_IGNORE_NEW_LINES);

	// process the word array
	foreach($words as $word) {
		$key = trim($word[0] ?? '');
		$val = trim($word[1] ?? '');
		if (!empty($key) && !empty($val))
			addOrReplaceInPO($key, $val, $lines);
	}

	if ($lines[count($lines) - 1] != '')
		$lines[] = '';

	return $lines;
}

// read the source
$words = [];
$handle = fopen($input_file_name, 'r');

while (($line = fgets($handle)) !== false) {
	$words[] = explode("\t", trim($line), 2);
}

fclose($handle);

$lines = getPOLines($words);

makePOFile($po_file_name, $lines);
makeMOFile($po_file_name);

unset($lines);

print(PHP_EOL . 'Finished.' . PHP_EOL);
