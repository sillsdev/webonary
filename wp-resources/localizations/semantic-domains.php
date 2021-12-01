<?php
/** @noinspection PhpUnused */

$input_file_name = 'LocalizedLists-ru_RU.xml';
$lang_code = 'ru';
$locale_code = 'ru_RU';

$po_file_name = dirname(__DIR__) . '/plugins/sil-dictionary-webonary/include/lang/sil_dictionary-' . $locale_code . '.po';


function getDomains(): array
{
	global $input_file_name, $lang_code;

	// parse the XML file for the semantic domains
	$xml = simplexml_load_file($input_file_name);
	$output = [];

	/** @var SimpleXMLElement $list */
	$list = $xml->xpath('List[@field="SemanticDomainList"]')[0];
	$name = $list->xpath('Name/AUni[@ws="' . $lang_code . '"]')[0];
	$eng = $list->xpath('Name/AUni[@ws="en"]')[0];

	$output['name'] = (string)$name;
	$output['eng'] = (string)$eng;
	$output['domains'] = [];

	$possibilities = $list->xpath('Possibilities/CmSemanticDomain');

	foreach ($possibilities as $possibility) {
		$output['domains'][] = getSubPossibilities($possibility);
	}

	// save to a temp file
	$temp_file_name = '/tmp/semantic-domains.json';
	file_put_contents($temp_file_name, json_encode($output));

	// free some memory
	unset($list);
	unset($name);
	unset($eng);
	unset($possibilities);
	unset($possibility);
	unset($xml);

	return $output;


}

function getPOLines($output): array
{
	global $po_file_name;

	// load the existing localizations
	$lines = file($po_file_name, FILE_IGNORE_NEW_LINES);

	// process the output array
	addOrReplacePO($output['eng'], $output['name'], '', $lines);

	foreach ($output['domains'] as $domain) {
		processPODomain($domain, $lines);
	}

	if ($lines[count($lines) - 1] != '')
		$lines[] = '';

	return $lines;
}

function getSubPossibilities(SimpleXMLElement $e): array
{
	global $lang_code;

	$sem_domains = [];
	$code = $e->xpath('Abbreviation/AUni[@ws="en"]')[0];
	$name = $e->xpath('Name/AUni[@ws="' . $lang_code . '"]')[0];
	$eng = $e->xpath('Name/AUni[@ws="en"]')[0];

	$sem_domains['code'] = (string)$code;
	$sem_domains['name'] = (string)$name;
	$sem_domains['eng'] = (string)$eng;
	$sem_domains['domains'] = [];

	$possibilities = $e->xpath('SubPossibilities/CmSemanticDomain');
	foreach ($possibilities as $possibility) {

		$sem_domains['domains'][] = getSubPossibilities($possibility);
	}

	return $sem_domains;
}

function escapeString($string)
{
	$string = str_replace("\n", '\\n', $string);

	return str_replace("\"", '\\"', $string);
}

function addOrReplacePO(string $key, string $value, string $code, array &$po_list)
{
	$e_key = escapeString($key);
	$e_val = escapeString($value);

	$find = 'msgid "' . $e_key . '"';
	$idx = array_search($find, $po_list);

	if ($idx === false) {

		// add a blank line
		if ($po_list[count($po_list) - 1] != '')
			$po_list[] = '';

		$po_list[] = '#: semantic domain ' . $code;
		$po_list[] = 'msgid "' . $e_key . '"';
		$po_list[] = 'msgstr "' . $e_val . '"';
	}
	else {
		$po_list[$idx + 1] = 'msgstr "' . $e_val . '"';
	}
}

function processPODomain($domain, array &$po_list)
{
	addOrReplacePO($domain['eng'], $domain['name'], $domain['code'], $po_list);

	foreach ($domain['domains'] as $d) {
		processPODomain($d, $po_list);
	}
}

function makePOFile($lines)
{
	global $po_file_name;

	// rename the old file
	if (is_file($po_file_name))
		rename($po_file_name, $po_file_name . '.old');

	// save the new po file
	file_put_contents($po_file_name, implode(PHP_EOL, $lines));
}

function makeMOFile()
{
	global $po_file_name;

	$mo_file_name = substr($po_file_name, 0, -2) . 'mo';

	// rename the old file
	if (is_file($mo_file_name))
		rename($mo_file_name, $mo_file_name . '.old');

	// generate the new mo file
	$output = null;
	$return_val = null;
	$cmd = 'msgfmt ' . $po_file_name . ' -o ' . $mo_file_name;
	exec($cmd, $output, $return_val);
}

function makeJSFile($output): void
{
	global $lang_code;

	// load the english file
	$en_file_name = dirname(__DIR__) . '/plugins/sil-dictionary-webonary/js/categoryNodes_en.js';
	$lines = file($en_file_name, FILE_IGNORE_NEW_LINES);

	foreach ($output['domains'] as $domain) {
		processJSDomain($domain, $lines);
	}

	$out_file_name = dirname(__DIR__) . '/plugins/sil-dictionary-webonary/js/categoryNodes_' . $lang_code . '.js';

	// rename the old file
	if (is_file($out_file_name))
		rename($out_file_name, $out_file_name . '.old');

	// save the new po file
	file_put_contents($out_file_name, implode(PHP_EOL, $lines));
}

function processJSDomain($domain, &$lines)
{
	addOrReplaceJS($domain['eng'], $domain['name'], $domain['code'], $lines);

	foreach ($domain['domains'] as $d) {
		processJSDomain($d, $lines);
	}
}

function addOrReplaceJS(string $key, string $value, string $code, array &$lines)
{
	$find = '"' . $code . '. ' . $key . '"';

	foreach($lines as &$line) {

		if (strpos($line, $find) !== false) {
			$replace = '"' . $code . '. ' . $value . '"';
			$line = str_replace($find, $replace, $line);
			return;
		}
	}

	// output a warning if you get here
	print('WARNING: "' . $code . ' ' . $key . '" was not found in the JS list.' . PHP_EOL);
}

$output = getDomains();
$lines = getPOLines($output);

makePOFile($lines);
makeMOFile();

unset($lines);

// makeJSFile($output);

print(PHP_EOL . 'Finished.' . PHP_EOL);
