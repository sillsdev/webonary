<?php
/** @noinspection XmlUnusedNamespaceDeclaration */
/** @noinspection SqlResolve */
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
add_action('wp_ajax_saveMapCoordinates', 'saveMapCoordinates');

function add_admin_menu()
{
	add_submenu_page('link-manager.php', 'Add Links to Map', 'Add Links to Map', 'edit_posts', __FILE__, 'add_links_to_map');
}

function change_link_updated($link) {
	global $wpdb, $table_prefix;

	$wpdb->query("UPDATE ".$table_prefix."links SET link_updated = NOW() WHERE link_updated = '0000-00-00 00:00:00' AND link_id = ".$link);
}

/**
 * @throws Exception
 */
function add_links_to_map()
{
	$str = file_get_contents(__DIR__ . '/languages.json');

	$json = json_decode($str, true); // decode the JSON into an associative array

	//Robust languages
	$mapPoints = $json[0]['leaflet'][0]['features'][0]['features'];
	//echo var_dump($json[0]['leaflet'][0]['features']) . "\n";
	$e = 0;

	$ethnologueData = [];
	$ethnologueData = getEthnologueData($ethnologueData, $e, $mapPoints);

	//Endangered languages
	$mapPoints = $json[0]['leaflet'][0]['features'][1]['features'];
	$ethnologueData = getEthnologueData($ethnologueData, $e, $mapPoints);

    $has_not = display_links_coordinates(false, $ethnologueData, 0);
	$has = display_links_coordinates(true, $ethnologueData, count($has_not));
    $url = admin_url('admin-ajax.php');

    $has_not_str = implode(PHP_EOL, $has_not);
	$has_str = implode(PHP_EOL, $has);

	echo <<<HTML
<div class="wrap">
    <h2>Add markers to map</h2>
    <p>The published Webonary sites appear here. If the coordinates (based on Ethnologue code) can be found, they
        will be prefilled and one just needs to click "Save" to add them to the map. If not, you have the option
        to manually set the coordinates by looking them up on a map.</p>

    <form id="map-coordinates-form" onsubmit="return false;">
        <h3>Markers not yet set</h3>
        
        <table>
            $has_not_str
        </table>
        
        <br>
        <button type="button" onclick="saveMapCoordinates();">Save</button>
        <br>
        <h3>Existing Markers</h3>
        
        <table>
            $has_str
        </table>
        
        <br><br><br>
        <button type="button" onclick="saveMapCoordinates();">Save</button>
    </form>
</div>
<script type="text/javascript">

    document.getElementById('map-coordinates-form').addEventListener('input', function (evt) {
        // tag elements that have changed
        evt.target['dataset'].changed = '1';
    });

    function saveMapCoordinates() {
        
        // get the values that have changed
        const elements = document.querySelectorAll("[data-changed]");
        
        if (elements.length === 0) {
            toastr.info('Nothing to save.');
            return;
        }
        
        const regex = /.*\[(\d+)\]/;
        let ids = [];
        
        console.log(elements);
        
        elements.forEach(el => {
            
            // get the index
            ids.push(el.name.match(regex)[1]);
        })
        
        // remove duplicates
        ids = [...new Set(ids)];
        
        // collect the data for changed items
        let changed = [];
        
        for (let i=0; i < ids.length; i++) {
            
            let idx = ids[i];

            changed.push(
                {
                    link_id: document.getElementsByName('linkid[' + idx + ']')[0].value,
                    marker_id: document.getElementsByName('markerid[' + idx + ']')[0].value,
                    lat: document.getElementsByName('lat[' + idx + ']')[0].value,
                    lon: document.getElementsByName('lon[' + idx + ']')[0].value
                }
            )
        }
        
        // send to the server
        jQuery.ajax({
            url: '$url',
            dataType: 'json',
            type: 'POST',
            data: {items: changed, btnSave: 'Save', action: 'saveMapCoordinates'}
        }).done(function (data) {

            // check for error condition
            if ('success' in data && data['success'] === 'OK') {
                
                // remove the changed flag
                elements.forEach(el => {
                    el.removeAttribute('data-changed');
                });
                
                // update the "Find coordinates" link
	            for (let i=0; i < ids.length; i++) {
	            
		            let idx = ids[i];
	                let lat = document.getElementsByName('lat[' + idx + ']')[0].value;
	                let lon = document.getElementsByName('lon[' + idx + ']')[0].value;
	                
		            document.getElementById('map-link-' + idx.toString()).setAttribute('href', 'https://www.whatsmygps.com/index.php?lat=' + lat + '&lng=' + lon);
		        }
        
                toastr.success('Data saved');
                return;
            }

            toastr.warning('Unexpected response. Data may not be saved.')
            
        }).fail(function (jqXHR, textStatus, errorThrown) {
            toastr.warning('Failed. Data may not be saved.')
        });
    }
</script>
HTML;
}

function display_links_coordinates($hasCoordinates, $ethnologueData, $i): array
{
	global $wpdb;

    $sql = <<<SQL
SELECT markerid, link_id, link_url, link_name, lat, lon
FROM wp_links
  INNER JOIN wp_term_relationships ON wp_term_relationships.object_id = wp_links.link_id
  INNER JOIN wp_terms ON wp_terms.term_id = wp_term_relationships.term_taxonomy_id
  LEFT JOIN wp_map ON wp_links.link_id = wp_map.linkid
WHERE slug = 'available-dictionaries'
SQL;

    if($hasCoordinates)
        $sql .= '  AND lat IS NOT NULL' . PHP_EOL;
    else
        $sql .= '  AND lat IS NULL' . PHP_EOL;

    $sql .= 'ORDER BY link_name ASC';

	$arrLinks = $wpdb->get_results($sql);
	$values = [];

	/** @noinspection HtmlUnknownTarget */
	$template = <<<'HTML'
<tr>
<td><a href="%1$s" target="_blank">%2$s</a>
<input type="hidden" name="linkid[%3$d]" value="%4$d">
<input type="hidden" name="markerid[%3$d]" value="%5$d">
</td>
<td>Lat: <input class="coordinate" type="text" name="lat[%3$d]" value="%6$s"></td>
<td>Lon: <input class="coordinate" type="text" name="lon[%3$d]" value="%7$s"></td>
<td><a id="map-link-%3$d" href="https://www.whatsmygps.com/index.php?lat=%6$s&lng=%7$s" target="_blank">Find coordinates</a></td>
</tr>
HTML;

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
			preg_match('!^http[s]?://.+?/(.+?)/.*$!i', $link->link_url, $matches);
            if (empty($matches[1]))
                continue;

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

        $values[] = sprintf($template, $link->link_url, $link->link_name, $i, $link->link_id, $link->markerid, $lat, $lon);
        $i++;
	}

    return $values;
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

/**
 * @param $ethnologueData
 * @param $e
 * @param $mapPoints
 *
 * @return mixed
 * @throws Exception
 */
function getEthnologueData($ethnologueData, &$e, $mapPoints)
{
	foreach($mapPoints as $point)
	{
		$url = $point['popup'];
		$a = new SimpleXMLElement($url);
		//echo var_dump($a) . "\n";
		//echo $e . ": " . $a[2] . "\n";
		$ethnologueCode = str_replace('https://www.ethnologue.com/language/', '', $a['href']);
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

function saveMapCoordinates()
{
	global $wpdb;

	if(isset($_POST['btnSave']))
	{
		foreach($_POST['items'] as $item) {

			$link_id = (int)$item['link_id'];
			$marker_id = (int)$item['marker_id'];
			$lat = filterCoordinate($item['lat']);
			$lon = filterCoordinate($item['lon']);

			if ($lat != '' && $lon != '')
            {
				if ($marker_id > 0)
				{
                    $sql = <<<SQL
UPDATE wp_map SET lat = %s, lon = %s WHERE markerid = %d
SQL;
					$sql = $wpdb->prepare($sql, [$lat, $lon, $marker_id]);
				}
				else
				{
                    $sql = <<<SQL
INSERT INTO wp_map (linkid, lat, lon) VALUES( %d, %s, %s)
SQL;
					$sql = $wpdb->prepare($sql, [$link_id, $lat, $lon]);
				}

				$wpdb->query($sql);
			}
        }

		saveGpxFile();

		echo json_encode(['success' => 'OK']);
		exit();
	}
}

function filterCoordinate($val)
{
	$re = '/^-?[0-9]{1,3}(?:\.[0-9]*)?$/';
    $result = preg_match($re, $val);

    if (!$result)
        return '';

    return $val;
}

function saveGpxFile()
{
	global $wpdb;

    $sql = <<<SQL
SELECT l.link_name, l.link_url, l.link_description, m.lat, m.lon
FROM wp_links AS l
  INNER JOIN wp_map AS m ON l.link_id = m.linkid
ORDER BY m.markerid
SQL;

	$links = $wpdb->get_results( $sql );

    $template = <<<'XML'
<wpt lat="%s" lon="%s">
  <name><![CDATA[<a href="%s" target="_blank">%s</a>]]></name>
  <desc><![CDATA[%s]]></desc>
  <sym>Start</sym>
</wpt>
XML;

    $body = '';

    foreach ($links as $link) {
        $body .= sprintf($template, $link->lat, $link->lon, $link->link_url, $link->link_name, htmlentities( $link->link_description ));
    }

    $timestamp = date('Y-m-d H:i:s');
    $xml = <<<XML
<?xml version='1.0' encoding='UTF-8'?>
<gpx xmlns="https://www.topografix.com/GPX/1/1" xmlns:gpxdata="https://www.cluetrust.com/XML/GPXDATA/1/0" xmlns:t="https://www.garmin.com/xmlschemas/TrainingCenterDatabase/v2" creator="pytrainer https://sourceforge.net/projects/pytrainer" version="1.1">
<metadata>
  <name>Webonary Dictionary Sites</name>
  <link href="https://www.webonary.org"/>
  <time>$timestamp</time>
</metadata>
$body
</gpx>
XML;

	$file = WP_CONTENT_DIR . '/uploads/webonary-sites.gpx';
	file_put_contents($file, $xml);
}
