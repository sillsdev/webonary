<?php

function addLangQuery($content): string
{
	if (empty($content))
		return '';

	$lang = $_GET['lang'] ?? false;

	$doc = new DOMDocument();
	$doc->preserveWhiteSpace = true;
	$doc->formatOutput = false;

	// load the string into the DOM (this is your page's HTML), see below for more info
	libxml_use_internal_errors(true);
	$content = iconv('UTF-8', 'UCS-4', trim($content));

	if (empty($content))
		return '';

	$doc->loadHTML($content);

	if ($lang !== false)
		ProcessHrefs($doc, $lang);

	// render the content, and return the innerHTML of the <body> tag
	$re = '/^.*?<body>(.*)?<\/body>.*$/s';
	return preg_replace($re, '$1', $doc->saveHTML());
}

/**
 * Loop through the document, adding the lang to the href query string
 *
 * @param DOMDocument $doc
 * @param string $lang
 * @return void
 */
function ProcessHrefs(DOMDocument $doc, string $lang): void
{
	//Loop through each <a> tag in the dom and change the href property
	foreach ($doc->getElementsByTagName('a') as $anchor) {

		$link = $anchor->getAttribute('href');

		// skip this for .apk links
		if (str_contains($link, '.apk'))
			continue;

		$parts = explode('?', $link);

		if (count($parts) > 1) {

			// remove any existing lang values
			$qs_parts = array_filter(
				explode('&', $parts[1]),
				function ($val) { return !str_starts_with($val, 'lang='); }
			);
		}
		else {
			$qs_parts = [];
		}

		$qs_parts[] = 'lang=' . $lang;

		$link = $parts[0] . '?' . implode('&', $qs_parts);

		$anchor->setAttribute('href', $link);
	}
}

remove_filter('the_content', 'wptexturize');

// setting the priority to 99 because we need this to run after the short-code hooks
add_filter('the_content', 'addLangQuery', 99);
