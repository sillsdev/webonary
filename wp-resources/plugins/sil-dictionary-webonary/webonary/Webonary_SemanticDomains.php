<?php

/**
 * Class Webonary_SemanticDomains
 */
class Webonary_SemanticDomains
{
	private static ?array $roots;
	private static ?array $rootDomainPrinted;
	private static array $lastSemDom = [ 0, 0, 0, 0, 0, 0 ];
	private static array $root_names;

	public static function GetRoots(): void
	{
		global $webonary_include_path;

		if (!empty(self::$roots))
			return;

		self::$root_names = [
			__('Universe, creation', 'sil_domains'),
			__('Person', 'sil_domains'),
			__('Language and thought', 'sil_domains'),
			__('Social behavior', 'sil_domains'),
			__('Daily life', 'sil_domains'),
			__('Work and occupation', 'sil_domains'),
			__('Physical actions', 'sil_domains'),
			__('States', 'sil_domains'),
			__('Grammar', 'sil_domains'),
			__('Custom Domains', 'sil_domains')
		];

		if (get_option('displayCustomDomains') == 'yakan') {
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

		} else if (get_option('displayCustomDomains') == 'spanishfoods') {
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
				' aux1 = insFld(foldersTree, gFld("1. ' . self::$root_names[0] . '", "c0001.htm"))',
				' aux1 = insFld(foldersTree, gFld("2. ' . self::$root_names[1] . '", "c0105.htm"))',
				' aux1 = insFld(foldersTree, gFld("3. ' . self::$root_names[2] . '", "c0241.htm"))',
				' aux1 = insFld(foldersTree, gFld("4. ' . self::$root_names[3] . '", "c0472.htm"))',
				' aux1 = insFld(foldersTree, gFld("5. ' . self::$root_names[4] . '", "c0803.htm"))',
				' aux1 = insFld(foldersTree, gFld("6. ' . self::$root_names[5] . '", "c0900.htm"))',
				' aux1 = insFld(foldersTree, gFld("7. ' . self::$root_names[6] . '", "c1141.htm"))',
				' aux1 = insFld(foldersTree, gFld("8. ' . self::$root_names[7] . '", "c1314.htm"))',
				' aux1 = insFld(foldersTree, gFld("9. ' . self::$root_names[8] . '", "c1599.htm"))',
				' aux1 = insFld(foldersTree, gFld("10. ' . self::$root_names[9] . '", "c1599.htm"))'
			];
		}
	}

	private static function setLastSemDom($currentDigits): void
	{
		self::$lastSemDom = [0, 0, 0, 0, 0, 0];
		for ($i = 0; $i < count($currentDigits); $i++) {
			self::$lastSemDom[$i] = $currentDigits[$i];
		}
	}

	private static function buildTreeToSupportThisItem($domainNumber): string
	{
		global $defaultDomain;

		//First insert the standard tree root element if it is needed here.
		$currentDomainDigits = explode('-', $domainNumber);
		$currentDomainCount = count($currentDomainDigits);

		$domainNrToPrint = self::$lastSemDom[0] . '.';
		$currentDigits = array(self::$lastSemDom[0]);
		$return_val = '';

		//Note skip the first digit since we printed it already
		for ($i = 1; $i < $currentDomainCount - 1; $i++) {
			$domainNrToPrint = $domainNrToPrint . $currentDomainDigits[$i] . '.';
			$strToPrint = $domainNrToPrint . ' ' . $defaultDomain[$domainNrToPrint];
			$currentDigits[$i] = $currentDomainDigits[$i];

			if ($currentDomainDigits[$i] > self::$lastSemDom[$i]) {
				$return_val .= self::outputSemDomAsJava(($i + 1), $strToPrint);
				self::setLastSemDom($currentDigits);
			}
		}

		return $return_val;
	}

	private static function printRootDomainIfNeeded($domainNumber): string
	{
		$rootDomain = preg_replace('/[.-]/', '', substr($domainNumber, 0, 2));

		$return_val = '';

		if (self::$rootDomainPrinted[$rootDomain] == 'no') {
			$return_val .= self::$roots[$rootDomain] . ' ' . PHP_EOL;
			self::$rootDomainPrinted[$rootDomain] = 'yes';
			self::$lastSemDom = [$rootDomain, 0, 0, 0, 0];
		}

		return $return_val;
	}

	private static function outputSemDomAsJava($levelOfDomain, $newString): string
	{
		if ($levelOfDomain < 2)
			return '';

		$levelMinus1 = $levelOfDomain - 1;

		return 'aux' . $levelOfDomain . '= insFld(aux' . $levelMinus1 . ', gFld("' . $newString . '", "c1000.htm"))' . PHP_EOL;
	}

	/**
	 * @param string $lang_code
	 * @param string $selected_domain_key
	 * @return string
	 */
	public static function GetJavaScript(string $lang_code, string $selected_domain_key): string
	{
		$js = <<<JS
// You can find instructions for this here: https://www.treeview.net
USETEXTLINKS = 1;  // 1 = text, 0 = hyperlink
USEICONS = 0;
STARTALLOPEN = 0;  //replace 0 with 1 to show the whole tree
ICONPATH = '/wp-content/plugins/sil-dictionary-webonary/images/';
foldersTree = gFld('', '');
JS;

		$domains = self::GetTranslatedList($lang_code);
		$selected_index = 0;
		$idx = 0;

		foreach ($domains as $domain) {

			$domainNumber = $domain['slug'];

			$domainNumberAsInt = preg_replace('/[.-]/', '', $domainNumber);

			if (!is_numeric($domainNumberAsInt))
				continue;

			$idx++;

			$levelOfDomain = preg_match_all('/[.-]/', $domainNumber) + 1;

			$js .= self::printRootDomainIfNeeded($domainNumber);
			$js .= self::buildTreeToSupportThisItem($domainNumber);

			$domainNumberModified = str_replace('-', '.', $domainNumber) . '.';
			$domainName = trim($domain['name']);

			if ($domainNumberModified == $selected_domain_key)
				$selected_index = $idx;

			$newString = $domainNumberModified . ' ' . $domainName;
			$js .= self::outputSemDomAsJava($levelOfDomain, $newString);
			$currentDigits = preg_split('/[.-]/', $domainNumber);
			self::setLastSemDom($currentDigits);
		}

		return $js . PHP_EOL . 'let selected_idx = ' . $selected_index . ';' . PHP_EOL . 'initializeDocument();' . PHP_EOL;
	}

	/**
	 * @param $actual_domains
	 * @return void
	 */
	private static function LoadDefaultsIfNeeded(&$actual_domains): void
	{
		global $defaultDomain;

		self::GetRoots();

		//if no semantic domains were imported, use the default domains defined in default_domains.php
		if (count($actual_domains) == 0) {

			// reset the index
			$actual_domains = [];

			foreach ($defaultDomain as $key => $value) {
				$actual_domains[] = [
					'slug' => str_replace('.', '-', rtrim($key, '.')),
					'name' => $value
				];
			}
		}
	}

	private static function ApplyMissingRoots(&$domains): void
	{
		global $webonary_include_path, $defaultDomain;

		// check for numeric slugs
		$test = reset($domains);

		if ($test !== false) {
			if (!preg_match('/^([\d\-.]+)$/', $test['slug']))
				return;
		}

		include_once $webonary_include_path . '/default_domains.php';

		$use_dash = str_contains($test['slug'] ?? '', '-');

		foreach ($defaultDomain as $key => $value) {

			$key = trim($key, '.');

			if ($use_dash)
				$key = str_replace('.', '-', $key);

			if (empty(array_filter($domains, function($val) use($key) { return $val['slug'] == $key; })))
				$domains[] = ['slug' => $key, 'name' => $value];
		}

		// check for custom domains
		$found = array_filter($domains, function($val) {
			return str_starts_with($val['slug'], '10');
		});

		if (!empty($found))
			$domains[] = ['slug' => '10', 'name' => self::$root_names[9]];

		usort($domains, function ($a, $b) {
			return strcasecmp($a['slug'], $b['slug']);
		});
	}

	public static function GetTranslatedList(string $lang_code = '', bool $include_defaults = true): array
	{
		global $wpdb, $defaultDomain;

		// get the default language if not provided
		if (empty($lang_code)) {
			if (IS_CLOUD_BACKEND)
				$lang_code = Webonary_Cloud::getCurrentLanguage();
			else
				$lang_code = get_option('languagecode', 'en');
		}

		$found_lang_code = '';

		if (IS_CLOUD_BACKEND) {
			list($found_lang_code, $domains) = Webonary_Cloud::getSemanticDomainSlugs($lang_code);
		}
		else {
			/** @noinspection SqlResolve */
			$sql = <<<SQL
SELECT t.name, t.slug
FROM $wpdb->terms AS t
  INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id
WHERE tt.taxonomy = 'sil_semantic_domains' AND t.slug
ORDER BY CAST(t.slug as SIGNED INTEGER), CAST(RPAD(REPLACE(REPLACE(t.slug, '-', ''), '10','99'), 5, '0') AS SIGNED INTEGER)
SQL;
			$domains = $wpdb->get_results($sql, ARRAY_A);
		}

		if (!count($domains) && $include_defaults) {
			self::LoadDefaultsIfNeeded($domains);
			$found_lang_code = 'en';
		}

		self::ApplyMissingRoots($domains);

		if ($found_lang_code != $lang_code) {

			// translate the list
			foreach ($domains as &$domain) {

				// check if slug is only digits or dash or dot
				if (!preg_match('/^([\d\-.]+)$/', $domain['slug']))
					continue;

				$domain_number = str_replace('-', '.', $domain['slug']) . '.';

				if ($lang_code == 'en' && isset($defaultDomain[$domain_number])) {
					$domain['name'] = $defaultDomain[$domain_number];
				} else {
					$domain['name'] = __($domain['name'], 'sil_dictionary');
				}
			}
		}

		return $domains;
	}

	public static function GetDropdown(string $lang_code = ''): string
	{
		// check for cached list
		if (IS_CLOUD_BACKEND) {
			$dictionary = Webonary_Cloud::getDictionary();

			if (!empty($dictionary->usedSemanticDomains))
				$domains = $dictionary->usedSemanticDomains;
		}

		// if no cached list, get from the database
		if (empty($domains)) {
			$domains = self::GetTranslatedList($lang_code, false);

			// if no semantic domains were found, return now
			if (empty($domains))
				return '';
		}

		if (IS_CLOUD_BACKEND
			&& empty($dictionary->usedSemanticDomains)
			&& !empty($dictionary->semanticDomainAbbreviationsUsed)) {

			// For cloud, remove unused domains
			$domains = array_filter($domains, function ($value) use ($dictionary) {
				return in_array($value['slug'], $dictionary->semanticDomainAbbreviationsUsed);
			});

			// cache the list of used domains
			$dictionary->usedSemanticDomains = $domains;
			update_option('dictionary', $dictionary);
		}

		$selected_domains = Webonary_Info::getSelectedSemanticDomains();

		$options = ['<option value="">' . __('Semantic Domains', 'sil_domains') . '</option>'];

		foreach ($domains as $domain) {

			$domain_name = $domain['name'];

			if (preg_match('/^([\d\-.]+)$/', $domain['slug']))
				$domain_name = str_replace('-', '.', $domain['slug']) . '. ' . $domain_name;

			$selected = (in_array($domain['slug'],$selected_domains)) ? 'selected' : '';

			$options[] = "<option value=\"{$domain['slug']}\" $selected>$domain_name</option>";
		}

		$option_str = implode(PHP_EOL, $options);

		return <<<HTML
<div class="pos-container">
	<select name="semantic_domain" class="webonary_searchform_domain_select">
		$option_str
	</select>
</div>
HTML;
	}

	public static function GetDomainName(string $domain_key, string $lang_code = ''): string
	{
		$domains = self::GetTranslatedList($lang_code);
		$domain_key = rtrim($domain_key, '.');

		$filtered = array_filter($domains, function ($val) use ($domain_key) {
			return $val['slug'] == $domain_key;
		});

		if (!empty($filtered)) {
			$found = reset($filtered);
			if (!empty($found['name']))
				return $found['name'];
		}

		return '';
	}
}
