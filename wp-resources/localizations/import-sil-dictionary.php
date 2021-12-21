<?php


$lang_code = 'fr';
$locale_code = 'fr_FR';


include_once 'shared-functions.php';

$input_file_name = 'input/plugin-' . $locale_code . '.tab';
$po_file_name = dirname(__DIR__) . '/plugins/sil-dictionary-webonary/include/lang/sil_dictionary-' . $locale_code . '.po';

if (!is_file($po_file_name))
	copy(__DIR__ . '/english/sil_dictionary-en_US.po', $po_file_name);

function addOrReplacePO(string $key, string $value, array &$po_list)
{
	$e_key = escapeString($key);
	$e_val = escapeString($value);

	$find = 'msgid "' . $e_key . '"';
	$idx = array_search($find, $po_list);

	if ($idx === false) {

		// add a blank line
		if ($po_list[count($po_list) - 1] != '')
			$po_list[] = '';

		$po_list[] = '#: extra string';
		$po_list[] = 'msgid "' . $e_key . '"';
		$po_list[] = 'msgstr "' . $e_val . '"';
	}
	else {
		$po_list[$idx + 1] = 'msgstr "' . $e_val . '"';
	}
}

function getPOLines($words): array
{
	global $po_file_name;

	// load the existing localizations
	$lines = file($po_file_name, FILE_IGNORE_NEW_LINES);

	// process the word array
	foreach($words as $word) {
		$key = trim($word[0]);
		$val = trim($word[1]);
		if (!empty($key) && !empty($val))
			addOrReplacePO($key, $val, $lines);
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

$lines = getXmlPOLines($words);

makePOFile($po_file_name, $lines);
makeMOFile($po_file_name);

unset($lines);

print(PHP_EOL . 'Finished.' . PHP_EOL);
