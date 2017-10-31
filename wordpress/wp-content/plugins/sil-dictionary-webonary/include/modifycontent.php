<?php
function addLangQuery($content)
{
	if(isset($_GET['lang']))
	{
		$doc = new DOMDocument();

		// load the string into the DOM (this is your page's HTML), see below for more info
		$doc->loadHTML($content);

		//Loop through each <a> tag in the dom and change the href property
		foreach($doc->getElementsByTagName('a') as $anchor) {
			$link = $anchor->getAttribute('href');
			if(strpos("?", $link) > 0)
			{
				$link .= '&lang=' . $_GET['lang'];
			}
			else
			{
				$link .= '?lang=' . $_GET['lang'];
			}
			$anchor->setAttribute('href', $link);
		}
		$content = $doc->saveHTML();
	}

	return $content;
}

add_filter( 'the_content', 'addLangQuery');