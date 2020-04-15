<?php
/*
Plugin Name: Links Map Publish
Plugin URI:
Description: Updates the link_updated field, so you can sort on link_updated. Now only if link_updated is empty as it's used for publishing date.
Also provides a form for adding the map coordinates
Version: 1.2
Author: Philip Perry
*/

/*

Output links chronologically:
get_bookmarks("category_name=Notes&orderby=updated&order=desc");

*/
add_action('edit_link', 'change_link_updated');
add_action('add_link', 'change_link_updated');

create_map_table();

add_action( 'admin_menu', 'add_admin_menu' );

function add_admin_menu()
{
	add_submenu_page('link-manager.php', 'Add Links to Map', 'Add Links to Map', 3, __FILE__, 'add_links_to_map');
}

function change_link_updated($link) {
	global $wpdb, $table_prefix;

	$wpdb->query("UPDATE ".$table_prefix."links SET link_updated = NOW() WHERE link_updated = '0000-00-00 00:00:00' AND link_id = ".$link);
}

function add_links_to_map()
{
	global $wpdb;

	if(isset($_POST['btnSave']))
	{
		$gpxMapFile = "<?xml version='1.0' encoding='UTF-8'?>\n";
		$gpxMapFile .= "<gpx xmlns=\"http://www.topografix.com/GPX/1/1\" xmlns:gpxdata=\"http://www.cluetrust.com/XML/GPXDATA/1/0\" xmlns:t=\"http://www.garmin.com/xmlschemas/TrainingCenterDatabase/v2\" creator=\"pytrainer http://sourceforge.net/projects/pytrainer\" version=\"1.1\">\n";
		$gpxMapFile .= "<metadata>\n";
		$gpxMapFile .= "<name>Webonary Dictionary Sites</name>\n";
		$gpxMapFile .= "<link href=\"https://www.webonary.org\"/>\n";
		$gpxMapFile .= "<time>" . date("Y-m-d H:i:s") . "</time>\n";
		$gpxMapFile .= "</metadata>\n";

		$n = 0;
		foreach($_POST['linkid'] AS $linkid)
		{
			if($_POST['lat'][$n] != NULL && $_POST['lon'][$n] != NULL)
			{
				$sql = "SELECT link_name, link_url, link_description " .
						" FROM wp_links " .
						" WHERE link_id = " . $linkid;

				$arrLink = $wpdb->get_row($sql);

				$gpxMapFile .= "<wpt lat=\"" .  $_POST['lat'][$n] . "\" lon=\"" .  $_POST['lon'][$n] . "\">\n";
				$gpxMapFile .= "<name><![CDATA[<a href=\"" . $arrLink->link_url . "\" target=\"_blank\">" . $arrLink->link_name . "</a>]]></name>\n";
				$gpxMapFile .= "<desc><![CDATA[" . htmlentities($arrLink->link_description) . "]]></desc>\n";
				$gpxMapFile .= "<sym>Start</sym>\n";
				$gpxMapFile .= "</wpt>\n";

				if($_POST['markerid'][$n] != NULL)
				{
					$sql = "UPDATE wp_map "	.
							" SET lat = " . $_POST['lat'][$n] . ", " .
							" lon = " . $_POST['lon'][$n] .
							" WHERE markerid = " . $_POST['markerid'][$n];
				}
				else
				{
					$sql = "INSERT INTO wp_map " .
						   " (linkid, lat, lon) " .
						   " VALUES(" . $linkid . "," . $_POST['lat'][$n] . ", " . $_POST['lon'][$n] . ")";
				}

				$wpdb->query($sql);
			}
			$n++;
		}

		$gpxMapFile .= "</gpx>\n";

		$file = $_SERVER['DOCUMENT_ROOT'] . "/wp/wp-content/uploads/webonary-sites.gpx";
		file_put_contents($file, $gpxMapFile);
	}

	$str = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/wp/wp-content/plugins/links-map-publish/languages.json');

	$json = json_decode($str, true); // decode the JSON into an associative array

	//Robust languages
	$mapPoints = $json[0]['leaflet'][0]['features'][0]['features'];
	//echo var_dump($json[0]['leaflet'][0]['features']) . "\n";
	$e = 0;

	$ethnologueData = getEthnologueData($ethnologueData, $e, $mapPoints);

	//Endangered languages
	$mapPoints = $json[0]['leaflet'][0]['features'][1]['features'];
	$ethnologueData = getEthnologueData($ethnologueData, $e, $mapPoints);
?>
	<div class="wrap">
		<h2>Add markers to map</h2>
		The published Webonary sites appear here. If the coordinates (based on Ethnologue code) can be found, they will be prefilled and one just needs to click
		"Save" to add them to the map. Otherwise you have the option to manually set the coordinates by looking them up on a map.
		<p>
		<form action="" method="post">
		<h3>Markers not yet set</h3>
	<?php
		$i = 0;

		$i = display_links_coordinates(false, $ethnologueData, $i);
	?>
	<br>
	<input type="submit" name="btnSave" value="Save">
	<br>
	<h3>Existing Markers</h3>
	<?php
	display_links_coordinates(true, $ethnologueData, $i);

	echo "<br><br>";
	?>
	<br>
	<input type="submit" name="btnSave" value="Save">

	</form>
	</div>

	<?php
}

function display_links_coordinates($hasCoordinates, $ethnologueData, $i)
{
	global $wpdb;

	$sql = " SELECT markerid, link_id, link_url, link_name, lat, lon " .
			" FROM wp_links " .
			" INNER JOIN wp_term_relationships ON wp_term_relationships.object_id = wp_links.link_id " .
			" INNER JOIN wp_terms ON wp_terms.term_id = wp_term_relationships.term_taxonomy_id ";
			//$sql .= " INNER JOIN wp_blogs ON wp_blogs.domain = replace(replace(wp_links.link_url, 'http://',''),'/','') ";
			//$sql .= " INNER JOIN wp_blogs ON wp_blogs.blog_id = 1";
			$sql .= " LEFT JOIN wp_map ON wp_links.link_id = wp_map.linkid " .
			" WHERE slug = 'available-dictionaries' ";
			if($hasCoordinates)
			{
				$sql .= " AND lat IS NOT NULL ";
			}
			else
			{
				$sql .= " AND lat IS NULL ";
			}
			$sql .= " ORDER BY link_name ASC";

	$arrLinks = $wpdb->get_results($sql);

	echo "<table>";
	foreach($arrLinks as $link)
	{
		// 20200220 chungh: Account for subdirectory based install
		if ( is_subdomain_install() )
		{
			$domain = str_replace("https://", "", $link->link_url);
			$domain = str_replace("/", "", $domain);
			$sql = "SELECT blog_id FROM wp_blogs WHERE domain = '" . $domain . "'";
		}
		else
		{
			preg_match('/^.*www\..+\..+\/(.+)\/.*$/', $link->link_url, $matches);
			$sql = "SELECT blog_id FROM wp_blogs WHERE path = '/" . $matches[1] . "/'";
		}

		$blog_id = trim($wpdb->get_var ( $sql ));

		$sql = "SELECT REPLACE(meta_value, 'https://www.ethnologue.com/language/','') AS ethnologueCode " . " FROM wp_" . $blog_id . "_postmeta " . " WHERE meta_key = '_menu_item_url' AND meta_value LIKE '%ethnologue%'";

		$ethnologue_code = trim($wpdb->get_var ( $sql ));

		$n = searchForId($ethnologue_code,$ethnologueData, 'code');

		$lat = $ethnologueData[$n]['lat'];
		$lon = $ethnologueData[$n]['lon'];

		if($link->lat != null && $link->lon != null)
		{
			$lat = $link->lat;
			$lon = $link->lon;
		}

		echo "<tr>";
		echo "<td><a href=\"" . $link->link_url . "\" target=\"_blank\">" . $link->link_name . "</a>";
		echo "<input type=hidden name=linkid[" . $i . "] value=" . $link->link_id . ">";
		echo "<input type=hidden name=markerid[" . $i . "] value=" . $link->markerid . ">";
		echo "</td>";
		echo "<td>Lat: <input type=text name=lat[" . $i . "] value='" . $lat . "'></td>";
		echo "<td>Lon: <input type=text name=lon[" . $i . "] value='" .  $lon . "'></td>";
		echo "<td><a href=\"http://www.whatsmygps.com/index.php?lat=" . $lat. "&lng=" . $lon. "\" target=\"_blank\">Find coordinates</a></td>";
		?>
		<?php
		echo "</tr>";
		$i++;
	}
	echo "</table>";
	return $i;
}

function create_map_table () {
	global $wpdb;

	$sql = "CREATE TABLE IF NOT EXISTS wp_map (
			markerid int(11) NOT NULL AUTO_INCREMENT,
			linkid int(11) NOT NULL,
			lat double NOT NULL,
			lon double NOT NULL,
			PRIMARY KEY (markerid))";

	$wpdb->get_var( $sql );
}

function getEthnologueData($ethnologueData, &$e, $mapPoints)
{
	foreach($mapPoints as $point)
	{
		$url = $point['popup'];
		$a = new SimpleXMLElement($url);
		//echo var_dump($a) . "\n";
		//echo $e . ": " . $a[2] . "\n";
		$ethnologueCode = str_replace('http://www.ethnologue.com/language/', '', $a['href']);
		//echo $ethnologueCode . "\n";
		$ethnologueData[$e]['code'] = $ethnologueCode;
		$ethnologueData[$e]['lat'] = $point['lat'];
		$ethnologueData[$e]['lon'] = $point['lon'];
		$e++;
	}

	return $ethnologueData;
}

function searchForId($id, $array, $column) {
	foreach ($array as $key => $val) {
		if ($val[$column] === $id) {
			return $key;
		}
	}
	return null;
}
