<?php

$xml = simplexml_load_file('LocalizedLists-ru.xml');
$lang_code = 'ru';
$po_file_name = dirname(__DIR__) . '/plugins/sil-dictionary-webonary/include/lang/sil_dictionary-ru_MY.po';

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

function addOrReplace(string $key, string $value, string $code, array &$po_list)
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

function processDomain($domain, array &$po_list)
{
	addOrReplace($domain['eng'], $domain['name'], $domain['code'], $po_list);

	foreach ($domain['domains'] as $d) {
		processDomain($d, $po_list);
	}
}


// parse the XML file for the semantic domains
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


// load the existing localizations
$lines = file($po_file_name, FILE_IGNORE_NEW_LINES);

// process the output array
addOrReplace($output['eng'], $output['name'], '', $lines);

foreach ($output['domains'] as $domain) {
	processDomain($domain, $lines);
}

if ($lines[count($lines) - 1] != '')
	$lines[] = '';

// rename the po file
if (is_file($po_file_name))
	rename($po_file_name, $po_file_name . '.old');

// save the new po file
file_put_contents($po_file_name, implode(PHP_EOL, $lines));
