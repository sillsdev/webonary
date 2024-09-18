<?php

function addLangQuery($content): string
{
	$lang = $_GET['lang'] ?? false;

	$doc = new DOMDocument();
	$doc->preserveWhiteSpace = true;
	$doc->formatOutput = false;

	// load the string into the DOM (this is your page's HTML), see below for more info
	libxml_use_internal_errors(true);
	$doc->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));

	if ($lang !== false) {

		//Loop through each <a> tag in the dom and change the href property
		foreach ($doc->getElementsByTagName('a') as $anchor) {

			$link = $anchor->getAttribute('href');

			if (strpos($link, "?") > 0)
				$link .= '&lang=' . $lang;
			else
				$link .= '?lang=' . $lang;

			if (!strpos($link, ".apk"))
				$anchor->setAttribute('href', $link);
		}
	}

	// render the content
	$content = $doc->saveHTML();

	// return the innerHTML of the <body> tag
	$re = '/^.*?<body>(.*)?<\/body>.*$/s';
	return preg_replace($re, '$1', $content);
}

remove_filter('the_content', 'wptexturize');
add_filter('the_content', 'addLangQuery');
