<?php

$possible = [
	'lang:',
	'site_id:',
	'xml:'
];

$options = getopt('', $possible);
$lang = $options['lang'] ?? '';
$site_id = intval($options['site_id'] ?? 0);
$xml_file = $options['xml'] ?? '';

// check for a language code
if (empty($lang)) {
	print PHP_EOL . 'The parameter --lang is required' . PHP_EOL;
	exit(1);
}

// check for a site ID
if (empty($site_id)) {
	print PHP_EOL . 'The parameter --site_id is required' . PHP_EOL;
	exit(1);
}

// check for a xml file
if (empty($xml_file)) {
	print PHP_EOL . 'The parameter --xml is required' . PHP_EOL;
	exit(1);
}

if (!is_file($xml_file)) {
	print PHP_EOL . 'The file ' . $xml_file . ' does not exist.' . PHP_EOL;
	exit(1);
}

/**
 * @param DOMElement $root_node
 * @param string $lang
 *
 * @return string[]
 */
function GetDomainInfo(DOMElement $root_node, string $lang): array {

	$name = '';
	$num = '';

	/** @var DOMElement $node */
	foreach ($root_node->childNodes as $node) {

		if (empty($node->tagName))
			continue;

		if ($node->tagName == 'Name') {
			/** @var DOMElement $auni */
			foreach ($node->getElementsByTagName('AUni') as $auni) {

				if ($auni->getAttribute('ws') == $lang)
					$name = $auni->nodeValue;
			}
		}

		if ($node->tagName == 'Abbreviation') {
			/** @var DOMElement $auni */
			foreach ($node->getElementsByTagName('AUni') as $auni) {

				if ($auni->getAttribute('ws') == 'en')
					$num = $auni->nodeValue;
			}
		}
	}

	return [$num, $name];
}

function DoSubPossibilities(DOMElement $root_node) {

	global $lang, $xpath, $compiled;

	$subs = $xpath->query('SubPossibilities/CmSemanticDomain', $root_node);

	if ($subs->length == 0)
		return;

	/** @var DOMElement $sub */
	foreach ($subs as $sub) {

		list($num, $name) = GetDomainInfo($sub, $lang);
		$compiled[$num] = $name;

		DoSubPossibilities($sub);
	}
}


$doc = new DOMDocument();
$doc->load($xml_file);

$xpath = new DOMXpath($doc);

$root_domains = $xpath->query('/Lists/List/Possibilities/CmSemanticDomain');

$compiled = [];


/** @var DOMElement $root_domain */
foreach ($root_domains as $root_domain) {

	list($num, $name) = GetDomainInfo($root_domain, $lang);
	$compiled[$num] = $name;

	DoSubPossibilities($root_domain);
}

$db = new mysqli('localhost', 'webonary', 'yranobew23');
$db->set_charset('utf8mb4');
$db->select_db('webonary');

foreach ($compiled as $slug => $domain) {

	$wp_slug = str_replace('.', '-', $slug);
	$wp_domain = trim($domain);

	if (empty($wp_slug) || empty($wp_domain))
		continue;

	/** @noinspection SqlResolve */
	$sql = <<<SQL
UPDATE wp_{$site_id}_terms AS t
  INNER JOIN wp_{$site_id}_term_taxonomy AS x ON t.term_id = x.term_id
SET t.name = '$wp_domain', x.description = '$wp_domain'
WHERE x.taxonomy = 'sil_semantic_domains'
  AND t.slug = '$wp_slug'
SQL;

	$db->multi_query($sql);
	$db->store_result();
}

print PHP_EOL . 'Finished.' . PHP_EOL;
