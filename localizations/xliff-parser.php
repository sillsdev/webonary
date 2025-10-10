<?php /** @noinspection PhpComposerExtensionStubsInspection */
/** @noinspection PhpUnhandledExceptionInspection */

include_once 'shared-functions.php';

global $source, $locale, $debug;
global $po_files;

/**
 * Process the command line args
 *
 * @return void
 * @throws Exception
 */
function process_command_args(): void
{
	global $argv, $source, $locale, $debug;

	$debug = in_array('--debug', $argv);

	$idx = array_search('--source', $argv);
	if ($idx !== false)
		$source = $argv[$idx + 1];

	$idx = array_search('--locale', $argv);
	if ($idx !== false)
		$locale = $argv[$idx + 1];

	if (empty($source) || str_starts_with($source, '-'))
		throw new Exception('No source given.');

	if (empty($locale) || str_starts_with($locale, '-'))
		throw new Exception('No locale given.');
}

/**
 * Gets the xliff files from the source
 *
 * @return string[]
 */
function get_xliff_files(): array
{
	global $source;

	// check if a single xliff file was passed
	if (str_ends_with($source, '.xlf') || str_ends_with($source, '.xliff'))
		return [$source];

	$pattern = rtrim($source, '/\\') . DIRECTORY_SEPARATOR . '*.xlf';
	return glob($pattern);
}

/**
 * Checks if the PO files exist, and creates them if not
 *
 * @return bool
 */
function verify_po_files(): bool
{
	global $locale, $po_files;

	$po_files = [];

	// WordPress core file
	$po_file = __DIR__ . '/wordpress-base/' . $locale . '.po';
	if (!is_file($po_file)) {
		$en_file = __DIR__ . '/wordpress-base/en_US.po';
		$data = file_get_contents($en_file);
		$data = str_replace('en_US', $locale, $data);
		file_put_contents($po_file, $data);
		unset($data);
	}
	$po_files[] = $po_file;

	// theme file
	$po_file = dirname(__DIR__) . '/themes/webonary-zeedisplay/includes/lang/' . $locale . '.po';
	if (!is_file($po_file)) {
		$en_file = __DIR__ . '/english/webonary-theme-en_US.po';
		$data = file_get_contents($en_file);
		$data = str_replace('en_US', $locale, $data);
		file_put_contents($po_file, $data);
		unset($data);
	}
	$po_files[] = $po_file;

	// webonary file
	$po_file = dirname(__DIR__) . '/plugins/sil-dictionary-webonary/include/lang/sil_dictionary-' . $locale . '.po';
	if (!is_file($po_file)) {
		$en_file = __DIR__ . '/english/sil_dictionary-en_US.po';
		$data = file_get_contents($en_file);
		$data = str_replace('en_US', $locale, $data);
		file_put_contents($po_file, $data);
		unset($data);
	}
	$po_files[] = $po_file;

	// semantic domains
	$po_file = dirname(__DIR__) . '/plugins/sil-dictionary-webonary/include/sem-domains/sil_domains-' . $locale . '.po';
	if (!is_file($po_file)) {
		$en_file = __DIR__ . '/english/sil_domains-en_US.po';
		$data = file_get_contents($en_file);
		$data = str_replace('en_US', $locale, $data);
		file_put_contents($po_file, $data);
		unset($data);
	}
	$po_files[] = $po_file;

	return true;
}

/**
 * Walk through the trans-unit collection and return the values
 *
 * @param string $xliff_file_name
 * @return array
 */
function get_strings(string $xliff_file_name): array
{
	$return_val = [];

	$xml = simplexml_load_file($xliff_file_name);
	$trans_unit = 'trans-unit';
	$units = $xml->file->body->$trans_unit;

	foreach ($units as $unit) {

		$key = (string)$unit->source;
		$value = (string)$unit->target;

		// ignore empty values
		if ($key == '' || $value == '')
			continue;

		$return_val[$key] = $value;
	}

	return $return_val;
}

/**
 * Translate the strings in this PO file
 *
 * @param $po_file_name
 * @param $xliff_files
 * @return void
 */
function process_po_file($po_file_name, $xliff_files): void
{
	// load the existing localizations
	$lines = file($po_file_name, FILE_IGNORE_NEW_LINES);

	foreach ($xliff_files as $file_name) {
		$strings = get_strings($file_name);

		foreach ($strings as $key => $value) {

			if (str_contains($file_name, 'domains'))
				addOrReplaceInPO($key, $value, $lines, true, 'semantic domain');
			else
				addOrReplaceInPO($key, $value, $lines, true);
		}

		makePOFile($po_file_name, $lines, false);
		makeMOFile($po_file_name, false);
	}
}

process_command_args();
$files = get_xliff_files();

if (!verify_po_files()) {
	echo 'ERROR: Not able to verify the PO files.' . PHP_EOL;
	exit(1);
}

foreach ($po_files as $po_file) {
	process_po_file($po_file, $files);
}
