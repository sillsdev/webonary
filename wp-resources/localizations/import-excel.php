<?php
/** @noinspection PhpUnhandledExceptionInspection */

use PhpOffice\PhpSpreadsheet\IOFactory;

$excel_file = '';

// argv[0] is probably this file
if (str_ends_with($argv[0], basename(__FILE__)))
	array_splice($argv, 0, 1);

// was the --debug arg set on the command line?
$is_debug = array_search('--debug', $argv);
if ($is_debug !== false) {
	if (!defined('DEBUG'))
		define('DEBUG', true);

	array_splice($argv, $is_debug, 1);
}

// get the locale
$locale_pos = array_search('--locale', $argv);
if ($locale_pos === false) {
	echo PHP_EOL . 'No Locale found.' . PHP_EOL;
	exit();
}
$locale_code = $argv[$locale_pos + 1];
array_splice($argv, $locale_pos, 2);

// find the excel file in the remaining options
foreach ($argv as $arg) {
	if (str_ends_with(strtolower($arg), '.xlsx')) {
		if (is_file($arg)) {
			$excel_file = $arg;
			break;
		}
	}
}

if (empty($excel_file)) {
	echo PHP_EOL . 'No Excel file found.' . PHP_EOL;
	exit();
}

// composer autoloader
include_once dirname(__DIR__, 2) . '/vendor/autoload.php';
include_once 'shared-functions.php';

/**
 * Translate the strings in this PO file
 *
 * @param string $po_file_name
 * @param array $strings
 * @param string $lang_code
 * @return void
 */
function process_po_file(string $po_file_name, array $strings, string $lang_code): void
{
	echo 'Updating "' . $po_file_name . '"' . PHP_EOL;

	// load the existing localizations
	$lines = file($po_file_name, FILE_IGNORE_NEW_LINES);

	foreach ($strings as $entry) {
		addOrReplaceInPO($entry[0], $entry[1], $lines, true);
	}

	// set language code
	foreach ($lines as &$line ) {
		if (str_starts_with($line, '"Language: en_US'))
			$line = str_replace('"Language: en_US', '"Language: ' . $lang_code, $line);
	}

	// set html_lang_attribute
	for ($i = 0; $i < count($lines); $i++) {
		if ($lines[$i] == 'msgid "html_lang_attribute"') {
			$lines[$i + 1] = 'msgstr "' . str_replace('_', '-', $lang_code) . '"';
		}
	}

	makePOFile($po_file_name, $lines, false);
	makeMOFile($po_file_name, false);
}

$entries = [];

// open the excel file and read the strings
$spreadsheet = IOFactory::load($excel_file);
$sheet_count = $spreadsheet->getSheetCount();
for ($i = 0; $i < $sheet_count; $i++) {
	$sheet = $spreadsheet->getSheet($i);
	$last_row = $sheet->getHighestRow('A');

	echo 'Processing tab "' . $sheet->getTitle() . '"' . PHP_EOL;

	// get rows, skipping the first one
	for ($j = 2; $j <= $last_row; $j++) {
		$eng = trim($sheet->getCell([1, $j])->getValue());
		$ver = trim($sheet->getCell([2, $j])->getValue());

		if (!empty($eng) && !empty($ver))
			$entries[] = [$eng, $ver];
	}
}
unset($spreadsheet);

$po_files = [];


// update strings in sil_dictionary-xx_YY.po
$locale_code = str_replace('-', '_', $locale_code);
$po_name = 'sil_dictionary-' . $locale_code . '.po';
$po_file = dirname(__DIR__) . '/plugins/sil-dictionary-webonary/include/lang/' . $po_name;
$po_files[] = $po_file;

if (!is_file($po_file))
	copy(__DIR__ . '/english/sil_dictionary-en_US.po', $po_file);

process_po_file($po_file, $entries, $locale_code);


// update strings in sil_domains-xx_YY.po
$locale_code = str_replace('-', '_', $locale_code);
$po_name = 'sil_domains-' . $locale_code . '.po';
$po_file = dirname(__DIR__) . '/plugins/sil-dictionary-webonary/include/sem-domains/' . $po_name;
$po_files[] = $po_file;

if (!is_file($po_file))
	copy(__DIR__ . '/english/sil_domains-en_US.po', $po_file);

process_po_file($po_file, $entries, $locale_code);


// update strings in webonary-zeedisplay xx_YY.po
$po_name = $locale_code . '.po';
$po_file = dirname(__DIR__) . '/themes/webonary-zeedisplay/includes/lang/' . $po_name;

if (!is_file($po_file))
	copy(__DIR__ . '/english/webonary-theme-en_US.po', $po_file);

process_po_file($po_file, $entries, $locale_code);


// update string in Wordpress xx_YY.po
$po_file = __DIR__ . '/wordpress-base/' . $po_name;

if (!is_file($po_file))
	copy(__DIR__ . '/english/wordpress-en_US.po', $po_file);

process_po_file($po_file, $entries, $locale_code);

echo 'Finished' . PHP_EOL;
