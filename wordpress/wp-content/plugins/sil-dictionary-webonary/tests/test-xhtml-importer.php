<?php /** @noinspection SqlResolve */
/**
 * Class ImportTest
 *
 * @package Sil_Dictionary_Webonary
 */

/**
 * Sample test case.
 */
class ImportTest extends WP_UnitTestCase {

	function test_convert_fieldworks_audio_to_wordpress() {

		$entry = '<div xmlns="http://www.w3.org/1999/xhtml" class="entry" id="g033a9b25-9bfa-4bf6-b87c-7134139e02d1"><span class="mainheadword"><span lang="tsi"><a href="#g033a9b25-9bfa-4bf6-b87c-7134139e02d1">a dm</a></span></span><span class="pronunciations"><span class="pronunciation"><span class="mediafiles"><span class="mediafile"><audio id="gad3db6b6-5a24-4786-a4e0-183347f300c1"><source src="AudioVisual\a_a-dm-ac-ps-01.mp3" /></audio><a class="CmFile" href="#gad3db6b6-5a24-4786-a4e0-183347f300c1" onclick="document.getElementById(\'gad3db6b6-5a24-4786-a4e0-183347f300c1\').play()">🔊</a></span></span></span></span><span class="senses"><span class="sensecontent"><span class="sense" entryguid="g033a9b25-9bfa-4bf6-b87c-7134139e02d1"><span class="morphosyntaxanalysis"><span class="partofspeech"><span lang="en">CL-INIT subordinating conjunction</span></span></span><span class="definitionorgloss"><span lang="en">in order to; so that</span></span><span class="examplescontents"><span class="examplescontent"><span class="example"><span lang="tsi"> ...a dm wil amukst.</span></span><span class="example_1"><span><audio id="ga_a-dm-ac-ps-01"><source src="AudioVisual\a_a-dm-ac-ps-01.mp3" /></audio><a class="tsi-Zxxx-x-audio" href="#ga_a-dm-ac-ps-01" onclick="document.getElementById(\'ga_a-dm-ac-ps-01\').play()"></a></span></span><span class="translationcontents"><span class="translationcontent"><span class="translation"><span lang="en">...so that he/she would listen.</span></span></span></span></span></span><span class="semanticdomains"><span class="semanticdomain"><span class="abbreviation"><span lang="en">9.6.2.7</span></span><span class="name"><span lang="en">Purpose </span></span></span><span class="semanticdomain"><span class="abbreviation"><span lang="en">9.6.2.5</span></span><span class="name"><span lang="en">Cause</span></span></span><span class="semanticdomain"><span class="abbreviation"><span lang="en">Ts-Y3</span></span><span class="name"><span lang="en">Seriation</span></span></span><span class="semanticdomain"><span class="abbreviation"><span lang="en">3.3.1.1</span></span><span class="name"><span lang="en">Purpose, goal</span></span></span><span class="semanticdomain"><span class="abbreviation"><span lang="en">Ts-Y2</span></span><span class="name"><span lang="en">Relative time, duration</span></span></span></span></span></span></span></div>';

		$doc = new DomDocument();
		$doc->preserveWhiteSpace = false;
		$doc->loadXML($entry);

		$import = new Webonary_Pathway_Xhtml_Import();
		$import->dom_xpath = new DOMXPath($doc);
		$import->dom_xpath->registerNamespace('xhtml', 'http://www.w3.org/1999/xhtml');

		$converted = $import->convert_fieldworks_audio_to_wordpress($doc)->saveXML();
		$converted = preg_replace( "/\r|\n/", "", $converted );

		$expected = '<?xml version="1.0"?><div xmlns="http://www.w3.org/1999/xhtml" class="entry" id="g033a9b25-9bfa-4bf6-b87c-7134139e02d1"><span class="mainheadword"><span lang="tsi"><a href="#g033a9b25-9bfa-4bf6-b87c-7134139e02d1">a dm</a></span></span><span class="pronunciations"><span class="pronunciation"><span class="mediafiles"><span class="mediafile"><audio id="gad3db6b6-5a24-4786-a4e0-183347f300c1"><source src="http://example.org/wp-content/uploads/AudioVisual/a_a-dm-ac-ps-01.mp3"/></audio><a class="CmFile" href="#gad3db6b6-5a24-4786-a4e0-183347f300c1" onclick="document.getElementById(\'gad3db6b6-5a24-4786-a4e0-183347f300c1\').play()">&#x1F50A;</a></span></span></span></span><span class="senses"><span class="sensecontent"><span class="sense" entryguid="g033a9b25-9bfa-4bf6-b87c-7134139e02d1"><span class="morphosyntaxanalysis"><span class="partofspeech"><span lang="en">CL-INIT subordinating conjunction</span></span></span><span class="definitionorgloss"><span lang="en">in order to; so that</span></span><span class="examplescontents"><span class="examplescontent"><span class="example"><span lang="tsi"> ...a dm wil amukst.</span></span><span class="example_1"><span><audio id="ga_a-dm-ac-ps-01"><source src="http://example.org/wp-content/uploads/AudioVisual/a_a-dm-ac-ps-01.mp3"/></audio><a class="tsi-Zxxx-x-audio" href="#ga_a-dm-ac-ps-01" onclick="document.getElementById(\'ga_a-dm-ac-ps-01\').play()"/></span></span><span class="translationcontents"><span class="translationcontent"><span class="translation"><span lang="en">...so that he/she would listen.</span></span></span></span></span></span><span class="semanticdomains"><span class="semanticdomain"><span class="abbreviation"><span lang="en">9.6.2.7</span></span><span class="name"><span lang="en">Purpose </span></span></span><span class="semanticdomain"><span class="abbreviation"><span lang="en">9.6.2.5</span></span><span class="name"><span lang="en">Cause</span></span></span><span class="semanticdomain"><span class="abbreviation"><span lang="en">Ts-Y3</span></span><span class="name"><span lang="en">Seriation</span></span></span><span class="semanticdomain"><span class="abbreviation"><span lang="en">3.3.1.1</span></span><span class="name"><span lang="en">Purpose, goal</span></span></span><span class="semanticdomain"><span class="abbreviation"><span lang="en">Ts-Y2</span></span><span class="name"><span lang="en">Relative time, duration</span></span></span></span></span></span></span></div>';
		$this->assertEquals($converted, $expected);
	}

	function test_convert_fieldworks_images_to_wordpress()
	{
		$entry = '<div  xmlns="http://www.w3.org/1999/xhtml" class="entry" id="g05e110fb-fb35-42e5-bd3d-7ae9a7d03989"> <span class="mainheadword"> <span lang="txo-Qaaa-x-Toto"> <a href="#g05e110fb-fb35-42e5-bd3d-7ae9a7d03989">abc</a> </span> <span lang="txo-Latn-fonipa-x-emic"> <a href="#g05e110fb-fb35-42e5-bd3d-7ae9a7d03989">dingbako layrung</a> </span> <span lang="txo-Beng-fonipa-x-emic"> <a href="#g05e110fb-fb35-42e5-bd3d-7ae9a7d03989">দিংবাকো লায়রুং</a> </span> </span> <span class="senses"> <span class="sharedgrammaticalinfo"> <span class="morphosyntaxanalysis"> <span class="partofspeech"> <span lang="en">n</span> </span> </span> </span> <span class="sensecontent"> <span class="sense" entryguid="g05e110fb-fb35-42e5-bd3d-7ae9a7d03989"> <span class="gloss"> <span lang="en">dragonfly</span> </span> </span> </span> </span> <span class="pictures"> <div class="picture"> <img class="thumbnail" src="pictures/AOR_4-3r.png" id="gf2e6b06b-7433-41b5-a071-38af8b37fd83"/> </div> </span> </div>';

		$doc = new DomDocument();
		$doc->preserveWhiteSpace = false;
		$doc->loadXML($entry);

		$import = new Webonary_Pathway_Xhtml_Import();
		$import->dom_xpath = new DOMXPath($doc);
		$import->dom_xpath->registerNamespace('xhtml', 'http://www.w3.org/1999/xhtml');

		$converted = $import->convert_fieldworks_images_to_wordpress($doc)->saveXML();
		$converted = preg_replace( "/\r|\n/", "", $converted );
		$expected = '<?xml version="1.0"?><div xmlns="http://www.w3.org/1999/xhtml" class="entry" id="g05e110fb-fb35-42e5-bd3d-7ae9a7d03989"><span class="mainheadword"><span lang="txo-Qaaa-x-Toto"><a href="#g05e110fb-fb35-42e5-bd3d-7ae9a7d03989">abc</a></span><span lang="txo-Latn-fonipa-x-emic"><a href="#g05e110fb-fb35-42e5-bd3d-7ae9a7d03989">dingbako layrung</a></span><span lang="txo-Beng-fonipa-x-emic"><a href="#g05e110fb-fb35-42e5-bd3d-7ae9a7d03989">&#x9A6;&#x9BF;&#x982;&#x9AC;&#x9BE;&#x995;&#x9CB; &#x9B2;&#x9BE;&#x9AF;&#x9BC;&#x9B0;&#x9C1;&#x982;</a></span></span><span class="senses"><span class="sharedgrammaticalinfo"><span class="morphosyntaxanalysis"><span class="partofspeech"><span lang="en">n</span></span></span></span><span class="sensecontent"><span class="sense" entryguid="g05e110fb-fb35-42e5-bd3d-7ae9a7d03989"><span class="gloss"><span lang="en">dragonfly</span></span></span></span></span><span class="pictures"><div class="picture"><a class="image" href="http://example.org/wp-content/uploads/images/original/AOR_4-3r.png"><img src="http://example.org/wp-content/uploads/images/thumbnail/AOR_4-3r.png"/></a></div></span></div>';

		$this->assertEquals($converted, $expected);
	}

	function test_convert_semantic_domains_to_links()
	{
		global $wpdb;

		$entry_xml = '<div class="entry" id="gabca4e11-59cd-4c7e-a3f3-b504e9665e83"><span class="mainheadword"><span lang="ify"><a href="http://webonary.localhost/lubwisi/gabca4e11-59cd-4c7e-a3f3-b504e9665e83">Zealot</a></span></span><span class="senses"><span class="sharedgrammaticalinfo"><span class="morphosyntaxanalysis"><span class="partofspeech"><span lang="en">Prop.N</span></span></span></span><span class="sensecontent"><span class="sense" entryguid="gabca4e11-59cd-4c7e-a3f3-b504e9665e83"><span class="definitionorgloss"><span lang="en">refers to a member of a political party that was known for being zealous to overthrow the Roman government during the time of Jesus</span></span><span class="examplescontents"><span class="examplescontent"><span class="example"><span lang="ify">Huyyan Zealot ey hakey ni grupuh ni tuun eleg meminhed ni mengu-unnud ni gubilnun Rome. (Footnote: Matthew 10:2-4)</span></span><span class="translationcontents"><span class="translationcontent"><span class="translation"><span lang="en">This Zealot Party was one political group of people who did not want to support the government in Rome.</span></span></span></span></span></span><span class="semanticdomains"><span class="semanticdomain"><span class="abbreviation"><span lang="en">9.7</span></span><span class="name"><span lang="en">Name</span></span></span></span></span></span></span></div>';
		$headword_text = "Zealot";
		$post_parent = 0;
		$flexid = "gabca4e11-59cd-4c7e-a3f3-b504e9665e83";


		$sql = $wpdb->prepare(
				"INSERT INTO ". $wpdb->posts . " (post_date, post_title, post_content, post_status, post_parent, post_name, comment_status, menu_order, post_content_filtered)
				VALUES (NOW(), '%s', '%s', 'publish', %d, '%s', '%s', %d, '%s')",
				trim($headword_text), $entry_xml, $post_parent, $flexid, "open", 19, "z" );

		$wpdb->query( $sql );

		$post_id = $wpdb->insert_id;

		//convert 1  (Semantic Domain Numbers)
		$doc = new DomDocument();
		$doc->preserveWhiteSpace = false;
		$doc->loadXML($entry_xml);

		$xpath = new DOMXPath($doc);
		$xpath->registerNamespace('xhtml', 'http://www.w3.org/1999/xhtml');

		$import = new Webonary_Pathway_Xhtml_Import();
		$import->dom_xpath = new DOMXPath($doc);
		$import->dom_xpath->registerNamespace('xhtml', 'http://www.w3.org/1999/xhtml');

		$semantic_domains = $xpath->query('//span[starts-with(@class, "semantic-domains")]|//span[starts-with(@class, "semanticdomains")]');
		$sd_numbers = $xpath->query('//span[starts-with(@class, "semantic-domains")]//span[starts-with(@class, "semantic-domain-abbr")]|//span[@class = "semanticdomains"]//span[starts-with(@class, "abbreviation")]/span[not(@class = "writingsystemprefix")]', $semantic_domains[0]);

		$converted = $import->convert_semantic_domains_to_links($doc, $sd_numbers->item(0), 2);
		$converted = preg_replace( "/\r|\n/", "", $converted );
		$expected = '<?xml version="1.0" encoding="UTF-8"?><div class="entry" id="gabca4e11-59cd-4c7e-a3f3-b504e9665e83"><span class="mainheadword"><span lang="ify"><a href="http://webonary.localhost/lubwisi/gabca4e11-59cd-4c7e-a3f3-b504e9665e83">Zealot</a></span></span><span class="senses"><span class="sharedgrammaticalinfo"><span class="morphosyntaxanalysis"><span class="partofspeech"><span lang="en">Prop.N</span></span></span></span><span class="sensecontent"><span class="sense" entryguid="gabca4e11-59cd-4c7e-a3f3-b504e9665e83"><span class="definitionorgloss"><span lang="en">refers to a member of a political party that was known for being zealous to overthrow the Roman government during the time of Jesus</span></span><span class="examplescontents"><span class="examplescontent"><span class="example"><span lang="ify">Huyyan Zealot ey hakey ni grupuh ni tuun eleg meminhed ni mengu-unnud ni gubilnun Rome. (Footnote: Matthew 10:2-4)</span></span><span class="translationcontents"><span class="translationcontent"><span class="translation"><span lang="en">This Zealot Party was one political group of people who did not want to support the government in Rome.</span></span></span></span></span></span><span class="semanticdomains"><span class="semanticdomain"><span class="abbreviation"><span class=""><a href="http://example.org/?s=&amp;partialsearch=1&amp;tax=2">9.7</a></span></span><span class="name"><span lang="en">Name</span></span></span></span></span></span></span></div>';
		$this->assertEquals($converted, $expected);

		//convert 2 (Semantic Domain Names)
		$converted = '<div class="entry" id="gabca4e11-59cd-4c7e-a3f3-b504e9665e83"><span class="mainheadword"><span lang="ify"><a href="http://webonary.localhost/lubwisi/gabca4e11-59cd-4c7e-a3f3-b504e9665e83">Zealot</a></span></span><span class="senses"><span class="sharedgrammaticalinfo"><span class="morphosyntaxanalysis"><span class="partofspeech"><span lang="en">Prop.N</span></span></span></span><span class="sensecontent"><span class="sense" entryguid="gabca4e11-59cd-4c7e-a3f3-b504e9665e83"><span class="definitionorgloss"><span lang="en">refers to a member of a political party that was known for being zealous to overthrow the Roman government during the time of Jesus</span></span><span class="examplescontents"><span class="examplescontent"><span class="example"><span lang="ify">Huyyan Zealot ey hakey ni grupuh ni tuun eleg meminhed ni mengu-unnud ni gubilnun Rome. (Footnote: Matthew 10:2-4)</span></span><span class="translationcontents"><span class="translationcontent"><span class="translation"><span lang="en">This Zealot Party was one political group of people who did not want to support the government in Rome.</span></span></span></span></span></span><span class="semanticdomains"><span class="semanticdomain"><span class="abbreviation"><span class=""><a href="http://example.org/?s=&amp;partialsearch=1&amp;tax=2">9.7</a></span></span><span class="name"><span lang="en">Name</span></span></span></span></span></span></span></div>';
		$convertedNoXML = preg_replace( '/\<\?xml version="1.0" encoding="UTF-8"\?\>/', '', $converted );

		$doc = new DomDocument();
		$doc->preserveWhiteSpace = false;
		$doc->loadXML($convertedNoXML);

		$xpath = new DOMXPath($doc);
		$xpath->registerNamespace('xhtml', 'http://www.w3.org/1999/xhtml');

		$import = new Webonary_Pathway_Xhtml_Import();
		$import->dom_xpath = new DOMXPath($doc);
		$import->dom_xpath->registerNamespace('xhtml', 'http://www.w3.org/1999/xhtml');

		$semantic_domains = $xpath->query('//span[starts-with(@class, "semantic-domains")]|//span[starts-with(@class, "semanticdomains")]');
		$sd_names = $xpath->query('//span[starts-with(@class, "semantic-domains")]//*[starts-with(@class, "semantic-domain-name")]|//span[@class = "semanticdomains"]//span[starts-with(@class, "name")]/span[not(@class = "writingsystemprefix")]', $semantic_domains[0]);

		$converted = $import->convert_semantic_domains_to_links($doc, $sd_names->item(0), 2);
		$converted = preg_replace( "/\r|\n/", "", $converted );

		$expected = '<?xml version="1.0" encoding="UTF-8"?><div class="entry" id="gabca4e11-59cd-4c7e-a3f3-b504e9665e83"><span class="mainheadword"><span lang="ify"><a href="http://webonary.localhost/lubwisi/gabca4e11-59cd-4c7e-a3f3-b504e9665e83">Zealot</a></span></span><span class="senses"><span class="sharedgrammaticalinfo"><span class="morphosyntaxanalysis"><span class="partofspeech"><span lang="en">Prop.N</span></span></span></span><span class="sensecontent"><span class="sense" entryguid="gabca4e11-59cd-4c7e-a3f3-b504e9665e83"><span class="definitionorgloss"><span lang="en">refers to a member of a political party that was known for being zealous to overthrow the Roman government during the time of Jesus</span></span><span class="examplescontents"><span class="examplescontent"><span class="example"><span lang="ify">Huyyan Zealot ey hakey ni grupuh ni tuun eleg meminhed ni mengu-unnud ni gubilnun Rome. (Footnote: Matthew 10:2-4)</span></span><span class="translationcontents"><span class="translationcontent"><span class="translation"><span lang="en">This Zealot Party was one political group of people who did not want to support the government in Rome.</span></span></span></span></span></span><span class="semanticdomains"><span class="semanticdomain"><span class="abbreviation"><span class=""><a href="http://example.org/?s=&amp;partialsearch=1&amp;tax=2">9.7</a></span></span><span class="name"><span class=""><a href="http://example.org/?s=&amp;partialsearch=1&amp;tax=2">Name</a></span></span></span></span></span></span></span></div>';
		$this->assertEquals($converted, $expected);
	}

	function test_import_xhtml_search_Sangesari()
	{
		$import = new Webonary_Pathway_Xhtml_Import();

		$entry_xml = '<div class="entry" id="g6d6ec840-6075-4b3a-9958-b445c9bc02d5"><span class="mainheadword"><span lang="sgr"><a href="#g6d6ec840-6075-4b3a-9958-b445c9bc02d5">ärt</a></span><span lang="sgr-Xpeo-IR"><a href="#g6d6ec840-6075-4b3a-9958-b445c9bc02d5">آرت</a></span></span><span class="senses"><span class="sharedgrammaticalinfo"><span class="morphosyntaxanalysis"><span class="partofspeech"><span lang="en">n.</span></span></span></span><span class="sensecontent"><span class="sense" entryguid="g6d6ec840-6075-4b3a-9958-b445c9bc02d5"><span class="gloss"><span lang="en">flour</span><span lang="fa"><span dir="rtl">آرد</span></span></span></span></span></span></div>';

		$doc = new DomDocument();
		$doc->preserveWhiteSpace = false;
		$doc->loadXML($entry_xml);

		$xpath = new DOMXPath($doc);
		$xpath->registerNamespace('xhtml', 'http://www.w3.org/1999/xhtml');

		$arrStringsForIndexing = $import->import_xhtml_classes(1, $xpath, true);

		$this->assertEquals($arrStringsForIndexing["mainheadword"]["sgr"][0], "ärt");
		$this->assertEquals($arrStringsForIndexing["mainheadword"]["sgr-Xpeo-IR"][0], "آرت");

		$this->assertEquals($arrStringsForIndexing["gloss"]["en"][7], "flour");
	}

	function test_import_xhtml_search_Nafaanra()
	{
		$import = new Webonary_Pathway_Xhtml_Import();

		$entry_xml = '<div class="entry" id="gc0a52176-c8fc-4376-b889-0b475a6fe70c"><span class="mainheadword"><span lang="nfr"><a href="#gc0a52176-c8fc-4376-b889-0b475a6fe70c">abani</a></span></span><span class="senses"><span class="sharedgrammaticalinfo"><span class="morphosyntaxanalysis"><span class="partofspeech"><span lang="en">n</span></span></span></span><span class="sensecontent"><span class="sense" entryguid="gc0a52176-c8fc-4376-b889-0b475a6fe70c"><span class="definitionorgloss"><span lang="en">government</span></span><span class="examplescontents"><span class="examplescontent"><span class="example"><span lang="nfr">Ala Ghana abani tia sro pan titi.</span></span><span class="translationcontents"><span class="translationcontent"><span class="translation"><span lang="en">This year there is food help from the Government.</span></span></span></span></span></span></span></span></span></div>';

		$doc = new DomDocument();
		$doc->preserveWhiteSpace = false;
		$doc->loadXML($entry_xml);

		$xpath = new DOMXPath($doc);
		$xpath->registerNamespace('xhtml', 'http://www.w3.org/1999/xhtml');

		$arrStringsForIndexing = $import->import_xhtml_classes(1, $xpath, true);

		$this->assertEquals($arrStringsForIndexing["mainheadword"]["nfr"][0], "abani");
		$this->assertEquals($arrStringsForIndexing["definitionorgloss"]["en"][7], "government");
		$this->assertEquals($arrStringsForIndexing["example"]["nfr"][10], "Ala Ghana abani tia sro pan titi.");
		$this->assertEquals($arrStringsForIndexing["translation"]["en"][13], "This year there is food help from the Government.");
	}

	function test_import_xhtml_search_Agutaynen()
	{
		$import = new Webonary_Pathway_Xhtml_Import();

		$entry_xml = '<div class="entry" id="g175a8442-7a8d-4171-8f3b-9603e85cf91d"><span class="mainheadword"><span lang="agn"><a href="#g175a8442-7a8d-4171-8f3b-9603e85cf91d">abol</a></span></span><span class="senses"><span class="sensecontent"><span class="sensenumber">1</span><span class="sense" entryguid="g175a8442-7a8d-4171-8f3b-9603e85cf91d"><span class="morphosyntaxanalysis"><span class="graminfoabbrev"><span lang="en">adj</span></span></span><span class="grammarnote"><span lang="en">mābol, kābol</span></span><span class="tagalog"><span lang="tl">mapurol</span></span><span class="definitionorgloss"><span lang="en">Dull, as of a blade.</span></span><span class="examplescontents"><span class="examplescontent"><span class="example"><span lang="agn"><span lang="agn">Baiden mo kay tang gedo, doro rag </span><span style="font-weight:bold;" lang="agn">kābol. </span></span></span><span class="translationcontents"><span class="translationcontent"><span class="translation"><span lang="en"><span lang="en">Please sharpen my bolo, it is already very </span><span style="font-weight:bold;" lang="en">dull. </span></span></span></span></span></span></span></span></span><span class="sensecontent"><span class="sensenumber">2</span><span class="sense" entryguid="g175a8442-7a8d-4171-8f3b-9603e85cf91d"><span class="morphosyntaxanalysis"><span class="graminfoabbrev"><span lang="en">vt</span></span></span><span class="grammarnote"><span lang="en"><span lang="en">Undergoer: </span><span style="font-weight:bold;" lang="en">-on</span></span></span><span class="definitionorgloss"><span lang="en">To make a blade dull by misusing it.</span></span><span class="examplescontents"><span class="examplescontent"><span class="example"><span lang="agn"><span lang="agn">Indi kay </span><span style="font-weight:bold;" lang="agn">abolon</span><span lang="agn"> mo tang gonsingo!</span></span></span><span class="translationcontents"><span class="translationcontent"><span class="translation"><span lang="en"><span lang="en">Please don\'t </span><span style="font-weight:bold;" lang="en">make</span><span lang="en"> my scissors </span><span style="font-weight:bold;" lang="en">dull! </span></span></span></span></span></span></span><span class="lexsensereferences"><span class="lexsensereference"><span class="ownertype_abbreviation"><span lang="en">ant</span></span><span class="configtargets"><span class="configtarget"><span class="headword"><span lang="agn"><span lang="agn"><a href="#gd2672515-c472-48e4-9823-4e031e13aca1">matarem </a></span><span style="font-weight:bold;" lang="en"><a href="#gd2672515-c472-48e4-9823-4e031e13aca1">1</a></span></span></span></span></span></span></span></span></span></span></div>';

		$doc = new DomDocument();
		$doc->preserveWhiteSpace = false;
		$doc->loadXML($entry_xml);

		$xpath = new DOMXPath($doc);
		$xpath->registerNamespace('xhtml', 'http://www.w3.org/1999/xhtml');

		$arrStringsForIndexing = $import->import_xhtml_classes(1, $xpath, true);

		$this->assertEquals($arrStringsForIndexing["mainheadword"]["agn"][0], "abol");
		$this->assertEquals($arrStringsForIndexing["definitionorgloss"]["en"][9], "Dull, as of a blade.");
		$this->assertEquals(trim($arrStringsForIndexing["example"]["agn"][12]), "Baiden mo kay tang gedo, doro rag kābol.");
		$this->assertEquals(trim($arrStringsForIndexing["translation"]["en"][15]), "Please sharpen my bolo, it is already very dull.");
	}

	function test_import_xhtml_search_Iranun_Sabah()
	{
		$import = new Webonary_Pathway_Xhtml_Import();

		$entry_xml = '<div class="entry" id="gcb70fdbe-9ce6-4529-977c-e2cdf0098890"><span class="mainheadword"><span lang="ill"><a href="#gcb70fdbe-9ce6-4529-977c-e2cdf0098890">balik</a></span></span><span class="senses"><span class="sharedgrammaticalinfo"><span class="morphosyntaxanalysis"><span class="partofspeech"><span lang="en">n</span></span></span></span><span class="sensecontent"><span class="sense" entryguid="gcb70fdbe-9ce6-4529-977c-e2cdf0098890"><span class="scientificname"><span lang="en">Clarias batrachus</span></span><span class="definition"><span class="writingsystemprefix">Eng</span><span lang="en">catfish</span><span class="writingsystemprefix">Mal</span><span lang="ms">ikan keli</span></span><span class="lexsensereferences"><span class="lexsensereference"><span class="configtargets"><span class="configtarget"><span class="headword"><span lang="ill"><span lang="ill"><a href="#g998a1ace-ec98-425b-ac0e-0cb14be67d46">seda\'</a></span><span style="font-weight:bold;font-size:58%;position:relative;top:0.3em;" lang="ill"><a href="#g998a1ace-ec98-425b-ac0e-0cb14be67d46">1</a></span></span></span></span></span></span></span></span></span></span><span class="pictures"><div class="picture"><img class="thumbnail" src="pictures\Clarias gariepinus catfish balik North African catfish intruduced mm.jpg" id="g0c73f08e-4c2c-4f58-9afb-8dc57bb65ad8" /><div class="captionContent"><span class="caption"><span lang="ill">Mabulmaddin Shaiddin</span></span></div></div></span></div>';

		$doc = new DomDocument();
		$doc->preserveWhiteSpace = false;
		$doc->loadXML($entry_xml);

		$xpath = new DOMXPath($doc);
		$xpath->registerNamespace('xhtml', 'http://www.w3.org/1999/xhtml');

		$arrStringsForIndexing = $import->import_xhtml_classes(1, $xpath, true);

		$this->assertEquals($arrStringsForIndexing["mainheadword"]["ill"][0], "balik");
		$this->assertEquals($arrStringsForIndexing["definition"]["en"][8], "catfish");
		$this->assertEquals($arrStringsForIndexing["definition"]["ms"][8], "ikan keli");
		$this->assertEquals($arrStringsForIndexing["scientificname"]["en"][7], "Clarias batrachus");
		$this->assertEquals($arrStringsForIndexing["headword"]["ill"][15], "seda'1");
	}
}
