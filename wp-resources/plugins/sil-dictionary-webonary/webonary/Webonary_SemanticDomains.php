<?php
/** @noinspection PhpMultipleClassDeclarationsInspection */
/** @noinspection PhpUnused */


/**
 * Class Webonary_SemanticDomains
 */
class Webonary_SemanticDomains {

	private static $roots;
	private static $rootDomainPrinted;
	private static $lastSemDom = [ 0, 0, 0, 0, 0, 0 ];

	public static function GetRoots() {

		global $webonary_include_path;

		if ( ! empty( self::$roots ) ) {
			return;
		}

		if ( get_option( 'displayCustomDomains' ) == 'yakan' ) {
			include_once $webonary_include_path . '/default_domains-yakan.php';

			self::$rootDomainPrinted = [
				'no zero domain',
				'no',
				'no',
				'no',
				'no',
				'no',
				'no',
				'no',
				'no',
				'no',
				'no',
				'no',
				'no',
				'no',
				'no',
				'no',
				'no',
				'no',
				'no',
				'no',
				'no',
				'no',
				'no',
				'no',
				'no',
				'no',
				'no',
				'no',
				'no',
				'no',
				'no',
				'no',
				'no',
				'no'
			];

			self::$roots = [
				'no 0 domain',
				' aux1 = insFld(foldersTree, gFld("1. ' . __( 'PLANTS', 'sil_domains' ) . '", "c0001.htm"))',
				' aux1 = insFld(foldersTree, gFld("2. ' . __( 'ANIMALS (CREATURES ON LAND)', 'sil_domains' ) . '", "c0002.htm"))',
				' aux1 = insFld(foldersTree, gFld("3. ' . __( 'BIRDS', 'sil_domains' ) . '", "c0003.htm"))',
				' aux1 = insFld(foldersTree, gFld("4. ' . __( 'FISH AND THINGS OF THE SEA', 'sil_domains' ) . '", "c0004.htm"))',
				' aux1 = insFld(foldersTree, gFld("5. ' . __( 'NATURAL PHENOMENA', 'sil_domains' ) . '", "c0005.htm"))',
				' aux1 = insFld(foldersTree, gFld("6. ' . __( 'SEA AND NAVIGATION', 'sil_domains' ) . '", "c0006.htm"))',
				' aux1 = insFld(foldersTree, gFld("7. ' . __( 'NUMBERS', 'sil_domains' ) . '", "c0007.htm"))',
				' aux1 = insFld(foldersTree, gFld("8. ' . __( 'AGRICULTURE', 'sil_domains' ) . '", "c0008.htm"))',
				' aux1 = insFld(foldersTree, gFld("9. ' . __( 'RICE CULTIVATION', 'sil_domains' ) . '", "c0009.htm"))',
				' aux1 = insFld(foldersTree, gFld("10. ' . __( 'COCONUT CULTIVATION', 'sil_domains' ) . '", "c0010.htm"))',
				' aux1 = insFld(foldersTree, gFld("11. ' . __( 'BODY PARTS AND FUNCTIONS', 'sil_domains' ) . '", "c0011.htm"))',
				' aux1 = insFld(foldersTree, gFld("12. ' . __( 'SICKNESSES/MEDICAL TERMS', 'sil_domains' ) . '", "c0012.htm"))',
				' aux1 = insFld(foldersTree, gFld("13. ' . __( 'DEATH', 'sil_domains' ) . '", "c0013.htm"))',
				' aux1 = insFld(foldersTree, gFld("14. ' . __( 'SUPERNATURAL/RELIGION', 'sil_domains' ) . '", "c0014.htm"))',
				' aux1 = insFld(foldersTree, gFld("15. ' . __( 'WEDDINGS AND OTHER CEREMONIES', 'sil_domains' ) . '", "c0015.htm"))',
				' aux1 = insFld(foldersTree, gFld("16. ' . __( 'RELATIONSHIPS', 'sil_domains' ) . '", "c0016.htm"))',
				' aux1 = insFld(foldersTree, gFld("17. ' . __( 'LAW AND JUDGING', 'sil_domains' ) . '", "c0017.htm"))',
				' aux1 = insFld(foldersTree, gFld("18. ' . __( 'TYPES OF CONVEYANCES', 'sil_domains' ) . '", "c0018.htm"))',
				' aux1 = insFld(foldersTree, gFld("19. ' . __( 'TYPES OF HOUSES AND CARPENTRY', 'sil_domains' ) . '", "c0019.htm"))',
				' aux1 = insFld(foldersTree, gFld("20. ' . __( 'IMPLEMENTS', 'sil_domains' ) . '", "c0020.htm"))',
				' aux1 = insFld(foldersTree, gFld("21. ' . __( 'FOOD ITEMS', 'sil_domains' ) . '", "c0021.htm"))',
				' aux1 = insFld(foldersTree, gFld("22. ' . __( 'EATING', 'sil_domains' ) . '", "c0022.htm"))',
				' aux1 = insFld(foldersTree, gFld("23. ' . __( 'CLOTHING AND SEWING', 'sil_domains' ) . '", "c0023.htm"))',
				' aux1 = insFld(foldersTree, gFld("24. ' . __( 'WEAVING', 'sil_domains' ) . '", "c0024.htm"))',
				' aux1 = insFld(foldersTree, gFld("25. ' . __( 'COLOR TERMS', 'sil_domains' ) . '", "c0025.htm"))',
				' aux1 = insFld(foldersTree, gFld("26. ' . __( 'CONCERNING HAIR', 'sil_domains' ) . '", "c0026.htm"))',
				' aux1 = insFld(foldersTree, gFld("27. ' . __( 'GAMES AND TOYS', 'sil_domains' ) . '", "c0027.htm"))',
				' aux1 = insFld(foldersTree, gFld("28. ' . __( 'SOUNDS', 'sil_domains' ) . '", "c0028.htm"))',
				' aux1 = insFld(foldersTree, gFld("29. ' . __( 'WAYS OF CUTTING', 'sil_domains' ) . '", "c0029.htm"))',
				' aux1 = insFld(foldersTree, gFld("30. ' . __( 'WAYS OF SPEAKING AND THINKING', 'sil_domains' ) . '", "c0030.htm"))',
				' aux1 = insFld(foldersTree, gFld("31. ' . __( 'WAYS OF WALKING', 'sil_domains' ) . '", "c0031.htm"))',
				' aux1 = insFld(foldersTree, gFld("32. ' . __( 'WAYS OF TYING THINGS', 'sil_domains' ) . '", "c0032.htm"))',
				' aux1 = insFld(foldersTree, gFld("33. ' . __( 'SEEING', 'sil_domains' ) . '", "c0033.htm"))'
			];

		} else if ( get_option( 'displayCustomDomains' ) == 'spanishfoods' ) {
			include_once $webonary_include_path . '/default_domains-SpanishFoods.php';

			self::$rootDomainPrinted = [ 'no zero domain', 'no' ];

			self::$roots = [
				'no 0 domain',
				' aux1 = insFld(foldersTree, gFld("1. ' . __( 'FOODS' ) . '", "c0001.htm"))'
			];

		} else {
			include_once $webonary_include_path . '/default_domains.php';

			self::$rootDomainPrinted = [
				'no zero domain',
				'no',
				'no',
				'no',
				'no',
				'no',
				'no',
				'no',
				'no',
				'no',
				'no'
			];

			self::$roots = [
				'no 0 domain',
				' aux1 = insFld(foldersTree, gFld("1. ' . __( 'Universe, creation', 'sil_domains' ) . '", "c0001.htm"))',
				' aux1 = insFld(foldersTree, gFld("2. ' . __( 'Person', 'sil_domains' ) . '", "c0105.htm"))',
				' aux1 = insFld(foldersTree, gFld("3. ' . __( 'Language and thought', 'sil_domains' ) . '", "c0241.htm"))',
				' aux1 = insFld(foldersTree, gFld("4. ' . __( 'Social behavior', 'sil_domains' ) . '", "c0472.htm"))',
				' aux1 = insFld(foldersTree, gFld("5. ' . __( 'Daily life', 'sil_domains' ) . '", "c0803.htm"))',
				' aux1 = insFld(foldersTree, gFld("6. ' . __( 'Work and occupation', 'sil_domains' ) . '", "c0900.htm"))',
				' aux1 = insFld(foldersTree, gFld("7. ' . __( 'Physical actions', 'sil_domains' ) . '", "c1141.htm"))',
				' aux1 = insFld(foldersTree, gFld("8. ' . __( 'States', 'sil_domains' ) . '", "c1314.htm"))',
				' aux1 = insFld(foldersTree, gFld("9. ' . __( 'Grammar', 'sil_domains' ) . '", "c1599.htm"))',
				' aux1 = insFld(foldersTree, gFld("10. ' . __( 'Custom Domains', 'sil_domains' ) . '", "c1599.htm"))'
			];

		}
	}

	public static function setLastSemDom( $currentDigits ) {
		self::$lastSemDom = [ 0, 0, 0, 0, 0, 0 ];
		for ( $i = 0; $i < count( $currentDigits ); $i ++ ) {
			self::$lastSemDom[ $i ] = $currentDigits[ $i ];
		}
	}

	public static function buildTreeToSupportThisItem( $domainNumber ): string {

		global $defaultDomain;

		//First insert the standard tree root element if it is needed here.
		$currentDomainDigits = explode( '-', $domainNumber );
		$currentDomainCount  = count( $currentDomainDigits );

		$domainNrToPrint = self::$lastSemDom[0] . '.';
		$currentDigits   = array( self::$lastSemDom[0] );
		$return_val = '';

		//Note skip the first digit since we printed it already
		for ( $i = 1; $i < $currentDomainCount - 1; $i ++ ) {
			$domainNrToPrint     = $domainNrToPrint . $currentDomainDigits[ $i ] . '.';
			$strToPrint          = $domainNrToPrint . ' ' . $defaultDomain[ $domainNrToPrint ];
			$currentDigits[ $i ] = $currentDomainDigits[ $i ];

			if ( $currentDomainDigits[ $i ] > self::$lastSemDom[ $i ] ) {
				$return_val .= self::outputSemDomAsJava( ( $i + 1 ), $strToPrint );
				self::setLastSemDom( $currentDigits );
			}
		}

		return $return_val;
	}

	public static function printRootDomainIfNeeded( $domainNumber ): string {

		$rootDomain = str_replace( '-', '', substr( $domainNumber, 0, 2 ) );

		$return_val = '';

		if ( self::$rootDomainPrinted[ $rootDomain ] == 'no' ) {
			$return_val .= self::$roots[ $rootDomain ] . ' ' . PHP_EOL;
			self::$rootDomainPrinted[ $rootDomain ] = 'yes';
			self::$lastSemDom = [ $rootDomain, 0, 0, 0, 0 ];
		}

		return $return_val;
	}

	public static function outputSemDomAsJava( $levelOfDomain, $newString ): string {

		$levelMinus1 = $levelOfDomain - 1;
		if ( $levelMinus1 == 0 )
			$levelMinus1 = 1;

		if ( $levelOfDomain < 2)
			return '';

		return 'aux' . $levelOfDomain . '= insFld(aux' . $levelMinus1 . ', gFld("' . $newString . '", "c1000.htm"))' . PHP_EOL;
	}

	/**
	 * @param $actual_domains
	 * @param $default_domains
	 * @param $lang
	 *
	 * @return string
	 */
	public static function GetJavaScript( &$actual_domains, $default_domains, $lang ): string {

		self::GetRoots();

		$js = <<<JS
// You can find instructions for this here: https://www.treeview.net
USETEXTLINKS = 1;  // 1 = text, 0 = hyperlink
USEICONS = 0;
STARTALLOPEN = 0;  //replace 0 with 1 to show the whole tree
ICONPATH = '/wp-content/plugins/sil-dictionary-webonary/images/';
foldersTree = gFld('', '');
JS;

		//if no semantic domains were imported, use the default domains defined in default_domains.php
		if ( count( $actual_domains ) == 0 ) {
			$d = 0;
			foreach ( $default_domains as $key => $value ) {
				$actual_domains[ $d ]['slug'] = str_replace( '.', '-', rtrim( $key, '.' ) );
				$actual_domains[ $d ]['name'] = $value;
				$d ++;
			}
		}

		foreach ( $actual_domains as $domain ) {

			$slug         = $domain['slug'];
			$domainNumber = $domain['slug'];

			$domainNumberAsInt = preg_replace( '/-/', '', $domainNumber );

			if ( is_numeric( $domainNumberAsInt ) ) {

				$currentSemDomain = $slug . " " . $domain['name'];
				$levelOfDomain = substr_count( "$domainNumber", "-" ) + 1;

				$js .= self::printRootDomainIfNeeded( $domainNumber );
				$js .= self::buildTreeToSupportThisItem( $domainNumber );

				$domainNumberModified = preg_replace( '/-/', '.', $domainNumber ) . '.';
				$domainName = trim( substr( $currentSemDomain, strlen( $domainNumber ), strlen( $currentSemDomain ) ) );

				if ( $lang == "en" ) {
					if ( isset( $defaultDomain[ $domainNumberModified ] ) ) {
						$domainName = $defaultDomain[ $domainNumberModified ];
					}
				} else {
					$domainName = __( $domainName, 'sil_domains' );
				}
				$newString = "$domainNumberModified" . " " . $domainName;
				$js .= self::outputSemDomAsJava( $levelOfDomain, $newString );
				$currentDigits = explode( '-', $domainNumber );
				self::setLastSemDom( $currentDigits );
			}

		}

		return $js . PHP_EOL . 'initializeDocument();' . PHP_EOL;
	}
}
