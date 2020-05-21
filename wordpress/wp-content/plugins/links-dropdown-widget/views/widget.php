<?php
global $wpdb;
$cats = get_terms('link_category', array('name__like' => '', 'exclude' => '2'));
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
<script>
	function fillDictionariesDropdown(dictionaries)
	{
		jQuery.each(dictionaries, function(i, option) {
			var link_name = option.link_name.replace("&#039;", "'");
			jQuery('#publishedSites').append(jQuery('<option/>').attr("value", option.link_url).text(link_name));
		    });
	}
	function filter_dictionaries(dictionariesAll, catid){
		var iterator = 0;
		for (var i = 0; i < dictionariesAll.length; i++) {
		    if (dictionariesAll[i][0]['term_taxonomy_id'] == catid)
		    {
		        iterator = i;
		        break;
		    }
		}
		return dictionariesAll[iterator];
	}

	var dictionariesAll = <?php echo json_encode($bookmarks); ?>;
	var dictionaries = filter_dictionaries(dictionariesAll, 8);

	function onUserSelectCountry()
    {
		var dictionariesDropdown = document.querySelector("#publishedSites");
		var i;
	    for(i = dictionariesDropdown.options.length - 1 ; i >= 1 ; i--)
	    {
	    	dictionariesDropdown.remove(i);
	    }

	    var countryDropdown = document.querySelector("#countries");
	    var countryid = countryDropdown.options[countryDropdown.selectedIndex].value;
		var dictionaries = filter_dictionaries(dictionariesAll, countryid);
		fillDictionariesDropdown(dictionaries);
    }
	function onUserSelectsDictionary () {
		var goButton = document.querySelector("#linkGo");
		var dropdown = document.querySelector("#publishedSites");

		if(dropdown.selectedIndex == 0){
		  goButton.style.backgroundColor = '#f5f5f5';
		  goButton.style.color = "black";
		} else {
			goButton.style.backgroundColor = '#85005B';
			goButton.style.color = "white";
		}
	}
	function onUserClicked() {
		var goButton = document.querySelector("#linkGo");
		goButton.style.backgroundColor = '#f5f5f5';
		goButton.style.color = "black";

		var dropdown = document.querySelector("#publishedSites");
		//Launching the URL for the selected web site
		if(dropdown.selectedIndex > 0){
			var url = dropdown.options[dropdown.selectedIndex].value;
			window.open(url,'_blank');
			//How do we turn off the default launch behavior for this widget???
			dropdown.selectedIndex = 0;
		}
	}
</script>
<form enctype="multipart/form-data" id="select_dictionary" method="post">
<?php
	$countries = get_terms( 'link_category', array(
			'hide_empty' => false, 'exclude' => "2,8"
	) );

	$countryOutput = '';
	foreach($countries as $country)
	{
		$countryOutput .= '<option value="'.$country->term_id.'">'.$country->name.'</option>';
	}
?>
<select id="countries" name="link-dropdown1" onchange="onUserSelectCountry()" style="margin-bottom:2px;">
	<option value="8"><?php echo gettext("Select Country") ?></option>
	<?php echo $countryOutput ?>
</select>
<select id="publishedSites" name="link-dropdown2" onchange="onUserSelectsDictionary()">
	<option value=""><?php echo $default_option ?></option>
	<?php // echo $linkOutput ?>
</select><input type="button" name="btnLinkGo" id=linkGo value="Go" onclick="onUserClicked()">

</form>
<script>
fillDictionariesDropdown(dictionaries);
</script>
