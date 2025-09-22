<?php
global $wpdb, $default_option;
$cats = get_terms('link_category', array('name__like' => '', 'exclude' => '2'));
$bookmarks = [];
foreach ( (array) $cats as $cat ) {
	$sql = "SELECT link_url, link_name, term_taxonomy_id " .
			" FROM wp_links " .
			" INNER JOIN wp_term_relationships ON wp_links.link_id = wp_term_relationships.object_id " .
			" WHERE term_taxonomy_id = " . $cat->term_id .
	        " ORDER BY link_name ASC";

	//$bookmarks[] = get_bookmarks(array('category'=>$cat->term_id));
	$bookmarks[] = $wpdb->get_results($sql);
	$categories = $bookmarks;
}
?>
<form enctype="multipart/form-data" id="select_dictionary" method="post">
<?php
	$countries = get_terms( 'link_category', array(
			'hide_empty' => false, 'exclude' => "2,8"
	) );

	// sort countries that start with characters that are not letters or number at the bottom
	usort($countries, function($a, $b) {

		$a_name = $a->name;
		$b_name = $b->name;

		// unicode character 129769 (U+1FAE9) is "face with bags under eyes"
		$compare = [33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,58,59,60,61,62,63,64,.91,92,93,94,95,96,123,124,125,126];
		$test = mb_ord($a_name[0]);
		if (in_array($test, $compare))
			$a_name = mb_chr(129769) . $a_name;

		$test = mb_ord($b_name[0]);
		if (in_array($test, $compare))
			$b_name = mb_chr(129769) . $b_name;

		return $a_name <=> $b_name;
	});

	$countryOutput = '';
	foreach($countries as $country)
	{
		$countryOutput .= '<option value="'.$country->term_id.'">'.$country->name.'</option>';
	}
?>
<select id="countries" name="link-dropdown1" onchange="onUserSelectCountry()" style="margin-bottom:3px;" class="form-select" title="">
	<option value="8"><?php echo gettext("Select Country") ?></option>
	<?php echo $countryOutput ?>
</select>
<select id="publishedSites" name="link-dropdown2" onchange="onUserSelectsDictionary()" style="margin-bottom:3px;" class="form-select d-inline-block" title="">
	<option value=""><?php echo $default_option ?></option>
	<?php // echo $linkOutput ?>
</select><button type="button" name="btnLinkGo" id=linkGo value="Go" onclick="onUserClicked()">Go</button>

</form>
<!--suppress JSUnusedAssignment -->
<script type="application/javascript">
    /** @type {Array[]} */
    const dictionariesAll = <?php echo json_encode($bookmarks); ?>;

    /**
     * @param {object[]} dictionaries
     */
    function fillDictionariesDropdown(dictionaries) {
        let $published_sites = jQuery('#publishedSites');
        dictionaries.forEach((dictionary) => {
            let link_name = dictionary.link_name.replace('&#039;', "'").replace('&amp;', '&');
            $published_sites.append(jQuery('<option/>').attr('value', dictionary.link_url).text(link_name));
        });
    }

    /**
     * @param {Array[]} dictionariesAll
     * @param {string|number} cat_id
     * @returns {object[]}
     */
    function filter_dictionaries(dictionariesAll, cat_id) {
        for (let i = 0; i < dictionariesAll.length; i++) {
            if (dictionariesAll[i][0]['term_taxonomy_id'] === cat_id.toString())
                return dictionariesAll[i];
        }
        return [];
    }

    function onUserSelectCountry() {

        /** @type {HTMLSelectElement} */
        const published_sites = document.getElementById('publishedSites');
        published_sites.options.length = 1;

        /** @type {HTMLSelectElement} */
        const countryDropdown = document.getElementById('countries');
        const dictionaries = filter_dictionaries(dictionariesAll, countryDropdown.value);
        fillDictionariesDropdown(dictionaries);
    }

    function onUserSelectsDictionary () {

        /** @type {HTMLInputElement} */
        const goButton = document.getElementById('linkGo');

        /** @type {HTMLSelectElement} */
        const dropdown = document.getElementById('publishedSites');

        if(dropdown.selectedIndex === 0)
            goButton.classList.remove('highlight');
        else
            goButton.classList.add('highlight');
    }

    function onUserClicked() {

        /** @type {HTMLInputElement} */
        const goButton = document.getElementById('linkGo');
        goButton.classList.remove('highlight');

        /** @type {HTMLSelectElement} */
        const dropdown = document.getElementById('publishedSites');
        if(dropdown.selectedIndex > 0){
            window.open(dropdown.value,'_blank');
            dropdown.selectedIndex = 0;
        }
    }

    fillDictionariesDropdown(filter_dictionaries(dictionariesAll, 8));
</script>
