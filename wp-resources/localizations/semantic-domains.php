<?php


$lang_code = 'ar';
$locale_code = 'ar_AR';


include_once 'shared-functions.php';

$input_file_name = 'input/LocalizedLists-' . $locale_code . '.xml';
$po_file_name = dirname(__DIR__) . '/plugins/sil-dictionary-webonary/include/sem-domains/sil_domains-' . $locale_code . '.po';

if (!is_file($po_file_name))
	copy(__DIR__ . '/english/sil_domains-en_US.po', $po_file_name);

function fixIdeophones(string $english): string
{
	if ($english == 'Idiophones')
		return 'Ideophones';

	if (strtolower($english) == 'onomatopoeic words')
		return 'Ideophones';

	return $english;
}

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
	$output['eng'] = fixIdeophones((string)$eng);
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
	$sem_domains['eng'] = fixIdeophones((string)$eng);
	$sem_domains['domains'] = [];

	$possibilities = $e->xpath('SubPossibilities/CmSemanticDomain');
	foreach ($possibilities as $possibility) {

		$sem_domains['domains'][] = getSubPossibilities($possibility);
	}

	return $sem_domains;
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


$output = getDomains();
$lines = getPOLines($output);

makePOFile($po_file_name, $lines);
makeMOFile($po_file_name);

unset($lines);

print(PHP_EOL . 'Finished.' . PHP_EOL);
