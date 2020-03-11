<script language="JavaScript">
// You can find instructions for this file here:
// http://www.treeview.net

// Decide if the names are links or just the icons
USETEXTLINKS = 1  //replace 0 with 1 for hyperlinks

// Don't use icons
USEICONS = 0

// Decide if the tree is to start all open or just showing the root folders
STARTALLOPEN = 0 //replace 0 with 1 to show the whole tree

ICONPATH = '/wp-content/plugins/sil-dictionary-webonary/images/' //change if the gif's folder is a subfolder, for example: 'images/'

foldersTree = gFld("", "")
<?php
global $defaultDomain;
global $roots;
global $rootDomainPrinted;
global $lastSemDom;
global $lastSemDomLevel;

if(get_option('displayCustomDomains') == "yakan")
{
	require_once( dirname( __FILE__ ) . '/default_domains-yakan.php' );

	$rootDomainPrinted = array('no zero domain',
			'no', 'no', 'no', 'no', 'no', 'no', 'no', 'no', 'no', 'no', 'no', 'no', 'no', 'no', 'no','no','no','no','no','no','no','no','no','no','no','no','no','no','no','no','no','no','no');

	$roots = array( 'no 0 domain',
			' aux1 = insFld(foldersTree, gFld("1. ' . __('PLANTS') . '", "c0001.htm"))',
			' aux1 = insFld(foldersTree, gFld("2. ' . __('ANIMALS (CREATURES ON LAND)', 'sil_dictionary') . '", "c0002.htm"))',
			' aux1 = insFld(foldersTree, gFld("3. ' . __('BIRDS', 'sil_dictionary') . '", "c0003.htm"))',
			' aux1 = insFld(foldersTree, gFld("4. ' . __('FISH AND THINGS OF THE SEA', 'sil_dictionary') . '", "c0004.htm"))',
			' aux1 = insFld(foldersTree, gFld("5. ' . __('NATURAL PHENOMENA', 'sil_dictionary') . '", "c0005.htm"))',
			' aux1 = insFld(foldersTree, gFld("6. ' . __('SEA AND NAVIGATION', 'sil_dictionary') . '", "c0006.htm"))',
			' aux1 = insFld(foldersTree, gFld("7. ' . __('NUMBERS', 'sil_dictionary') . '", "c0007.htm"))',
			' aux1 = insFld(foldersTree, gFld("8. ' . __('AGRICULTURE', 'sil_dictionary') . '", "c0008.htm"))',
			' aux1 = insFld(foldersTree, gFld("9. ' . __('RICE CULTIVATION', 'sil_dictionary') . '", "c0009.htm"))',
			' aux1 = insFld(foldersTree, gFld("10. ' . __('COCONUT CULTIVATION', 'sil_dictionary') . '", "c0010.htm"))',
			' aux1 = insFld(foldersTree, gFld("11. ' . __('BODY PARTS AND FUNCTIONS', 'sil_dictionary') . '", "c0011.htm"))',
			' aux1 = insFld(foldersTree, gFld("12. ' . __('SICKNESSES/MEDICAL TERMS', 'sil_dictionary') . '", "c0012.htm"))',
			' aux1 = insFld(foldersTree, gFld("13. ' . __('DEATH', 'sil_dictionary') . '", "c0013.htm"))',
			' aux1 = insFld(foldersTree, gFld("14. ' . __('SUPERNATURAL/RELIGION', 'sil_dictionary') . '", "c0014.htm"))',
			' aux1 = insFld(foldersTree, gFld("15. ' . __('WEDDINGS AND OTHER CEREMONIES', 'sil_dictionary') . '", "c0015.htm"))',
			' aux1 = insFld(foldersTree, gFld("16. ' . __('RELATIONSHIPS', 'sil_dictionary') . '", "c0016.htm"))',
			' aux1 = insFld(foldersTree, gFld("17. ' . __('LAW AND JUDGING', 'sil_dictionary') . '", "c0017.htm"))',
			' aux1 = insFld(foldersTree, gFld("18. ' . __('TYPES OF CONVEYANCES', 'sil_dictionary') . '", "c0018.htm"))',
			' aux1 = insFld(foldersTree, gFld("19. ' . __('TYPES OF HOUSES AND CARPENTRY', 'sil_dictionary') . '", "c0019.htm"))',
			' aux1 = insFld(foldersTree, gFld("20. ' . __('IMPLEMENTS', 'sil_dictionary') . '", "c0020.htm"))',
			' aux1 = insFld(foldersTree, gFld("21. ' . __('FOOD ITEMS', 'sil_dictionary') . '", "c0021.htm"))',
			' aux1 = insFld(foldersTree, gFld("22. ' . __('EATING', 'sil_dictionary') . '", "c0022.htm"))',
			' aux1 = insFld(foldersTree, gFld("23. ' . __('CLOTHING AND SEWING', 'sil_dictionary') . '", "c0023.htm"))',
			' aux1 = insFld(foldersTree, gFld("24. ' . __('WEAVING', 'sil_dictionary') . '", "c0024.htm"))',
			' aux1 = insFld(foldersTree, gFld("25. ' . __('COLOR TERMS', 'sil_dictionary') . '", "c0025.htm"))',
			' aux1 = insFld(foldersTree, gFld("26. ' . __('CONCERNING HAIR', 'sil_dictionary') . '", "c0026.htm"))',
			' aux1 = insFld(foldersTree, gFld("27. ' . __('GAMES AND TOYS', 'sil_dictionary') . '", "c0027.htm"))',
			' aux1 = insFld(foldersTree, gFld("28. ' . __('SOUNDS', 'sil_dictionary') . '", "c0028.htm"))',
			' aux1 = insFld(foldersTree, gFld("29. ' . __('WAYS OF CUTTING', 'sil_dictionary') . '", "c0029.htm"))',
			' aux1 = insFld(foldersTree, gFld("30. ' . __('WAYS OF SPEAKING AND THINKING', 'sil_dictionary') . '", "c0030.htm"))',
			' aux1 = insFld(foldersTree, gFld("31. ' . __('WAYS OF WALKING', 'sil_dictionary') . '", "c0031.htm"))',
			' aux1 = insFld(foldersTree, gFld("32. ' . __('WAYS OF TYING THINGS', 'sil_dictionary') . '", "c0032.htm"))',
			' aux1 = insFld(foldersTree, gFld("33. ' . __('SEEING', 'sil_dictionary') . '", "c0033.htm"))'
	);

}
else if(get_option('displayCustomDomains') == "spanishfoods")
{
	require_once( dirname( __FILE__ ) . '/default_domains-SpanishFoods.php' );

	$rootDomainPrinted = array('no zero domain', 'no');

	$roots = array( 'no 0 domain',
			' aux1 = insFld(foldersTree, gFld("1. ' . __('FOODS') . '", "c0001.htm"))'
	);

}
else
{
	require_once( dirname( __FILE__ ) . '/default_domains.php' );

	$rootDomainPrinted = array('no zero domain',
			'no', 'no', 'no', 'no', 'no', 'no', 'no', 'no', 'no', 'no');

	$roots = array( 'no 0 domain',
			' aux1 = insFld(foldersTree, gFld("1. ' . __('Universe, creation', 'sil_dictionary') . '", "c0001.htm"))',
			' aux1 = insFld(foldersTree, gFld("2. ' . __('Person', 'sil_dictionary') . '", "c0105.htm"))',
			' aux1 = insFld(foldersTree, gFld("3. ' . __('Language and thought', 'sil_dictionary') . '", "c0241.htm"))',
			' aux1 = insFld(foldersTree, gFld("4. ' . __('Social behavior', 'sil_dictionary') . '", "c0472.htm"))',
			' aux1 = insFld(foldersTree, gFld("5. ' . __('Daily life', 'sil_dictionary') . '", "c0803.htm"))',
			' aux1 = insFld(foldersTree, gFld("6. ' . __('Work and occupation', 'sil_dictionary') . '", "c0900.htm"))',
			' aux1 = insFld(foldersTree, gFld("7. ' . __('Physical actions', 'sil_dictionary') . '", "c1141.htm"))',
			' aux1 = insFld(foldersTree, gFld("8. ' . __('States', 'sil_dictionary') . '", "c1314.htm"))',
			' aux1 = insFld(foldersTree, gFld("9. ' . __('Grammar', 'sil_dictionary') . '", "c1599.htm"))',
			' aux1 = insFld(foldersTree, gFld("10. ' . __('Custom Domains', 'sil_dictionary') . '", "c1599.htm"))'
	);

}
//define a way to keep track of which semantic domain parents have been processed already.
//eg is we have 1.3.1.1 and the odomerter says 1,3,0,0,0,0 then we need to first output 1,3,1
$lastSemDom = array (0,0,0,0,0,0);
$lastSemDomLevel = 0;

/* processFromFile();

function processFromFile()
{
	global $lastSemDom;
	global $lastSemDomLevel;
	 $file = fopen("SemDomainsSena3.txt","r");
	 while (!feof($file))
	 {
		 $currentSemDomain = fgets($file);
		 //print "$currentSemDomain<br>";
		 $parsedData = explode(" ", $currentSemDomain);
		 $domainNumber = $parsedData[0];
		 $levelOfDomain = substr_count("$domainNumber","-") + 1;

		 printRootDomainIfNeeded($domainNumber);

		 buildTreeToSupportThisItem($domainNumber, $levelOfDomain);

		 $domainNumberModified = preg_replace('/-/', '.', $domainNumber) . '.';
		 $newString = "$domainNumberModified" . substr($currentSemDomain, strlen($domainNumber), strlen($currentSemDomain));
		 outputSemDomAsJava($levelOfDomain, $newString);
		 $currentDigits = explode('-', $domainNumber);
		 setLastSemDom($currentDigits);
	 }
	 fclose($file);
}

*/

function setLastSemDom($currentDigits)
{
	global $lastSemDom;
	global $lastSemDomLevel;
	$lastSemDom = array (0,0,0,0,0,0);
	for ($i=0; $i<count($currentDigits); $i++)
	{
		$lastSemDom[$i] = $currentDigits[$i];
	}
	$lastSemDomLevel = count($lastSemDom);

}

function buildTreeToSupportThisItem($domainNumber, $levelOfDomain)
{
	global $defaultDomain;
	global $lastSemDom;
	global $lastSemDomLevel;

	//First insert the standard tree root element if it is needed here.
	//printRootDomainIfNeeded($domainNumber);
	$currentDomainDigits = explode('-', $domainNumber);
	$currentDomainCount = count($currentDomainDigits);

	//printDomainDigits('$currentDomainDigits is ', $currentDomainDigits);
	//printDomainDigits('$lastSemDom is ', $lastSemDom);
	//solve this
	//1.1.1.6  last one printed  to
	//1.2.2.3  current one
	//need 1.2, 1.2.2 before 1.2.2.3

	$domainNrToPrint = $lastSemDom[0].'.';
	$currentDigits = array($lastSemDom[0]);
	//Note skip the first digit since we printed it already

	for ($i=1; $i<$currentDomainCount-1; $i++)
	{
		$domainNrToPrint = $domainNrToPrint . $currentDomainDigits[$i] . ".";
		/*
		foreach ($defaultDomain as $key => $val) {
			if ($val[0] == $domainNrToPrint) {
				return $key;
			}
		}
		*/

		$strToPrint = $domainNrToPrint . " " . $defaultDomain[$domainNrToPrint]; //$defaultDomain[$key][1];
		$currentDigits[$i] = $currentDomainDigits[$i];

		if ($currentDomainDigits[$i] > $lastSemDom[$i])
		{
			outputSemDomAsJava(($i+1), $strToPrint);
			setLastSemDom($currentDigits);
		}
	}
}

function printDomainDigits($whatisstring, $digitsArray)
{
	print $whatisstring . ': ';
	$numDigits = count($digitsArray);
	for ($i=0; $i<$numDigits; $i++)
	{
		print "$digitsArray[$i]" . '.';
	}
	print "\n";
}

function printRootDomainIfNeeded($domainNumber)
{
	global $lastSemDom;
	global $lastSemDomLevel;
	global $rootDomainPrinted;
	global $roots;
	$rootDomain = str_replace("-", "", substr($domainNumber, 0, 2));
	//print "rootDomain:$rootDomain rootDomainPrinted:$rootDomainPrinted[$rootDomain] ";
	if ($rootDomainPrinted[$rootDomain] =="no")
	{
		print "$roots[$rootDomain] \n";
		$rootDomainPrinted[$rootDomain] = "yes";

		$lastSemDom = array($rootDomain,0,0,0,0);
		$lastSemDomLevel = 1;
		//printlastNamelessSemDom ();
	}
}

function outputSemDomAsJava($levelOfDomain, $newString)
{
	$levelMinus1 = (string)$levelOfDomain-1;
	if($levelMinus1 == 0)
	{
		$levelMinus1 = 1;
	}

	if($levelOfDomain > 1)
	{
		print 'aux' . (string)$levelOfDomain . '= insFld(aux' . $levelMinus1 . ', gFld("';
	    print "$newString";
		print '", "c1000.htm"))';
		print "\n";
	}
}

?>