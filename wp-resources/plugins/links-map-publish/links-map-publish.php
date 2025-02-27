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
 * @return array
 * @throws Exception
 */
function process_ethnologue_language_file(): array {

	// decode the JSON into an associative array
	$json = json_decode(file_get_contents(__DIR__ . '/languages.json'), true);

	$languages = $json[0]['leaflet'][0]['features'];
	$data = [];

	// robust languages
	getEthnologueData($data, $languages[0]['features']);

	// endangered languages
	getEthnologueData($data, $languages[1]['features']);

	// store the processed data
	file_put_contents(__DIR__ . '/processed_languages.dat', serialize($data));

	return $data;
}

/**
 * @throws Exception
 * @noinspection JSUnresolvedVariable
 */
function add_links_to_map()
{
	$data_file = __DIR__ . '/processed_languages.dat';
	if (is_file($data_file)) {
		$ethnologueData = unserialize(file_get_contents($data_file), ['allowed_classes' => false]);
	}
	else {
		$ethnologueData = process_ethnologue_language_file();
	}

    $has_not = display_links_coordinates(false, $ethnologueData);
	$has = display_links_coordinates(true, $ethnologueData);
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
        <button type="button" onclick="saveMapCoordinates();">Save and Rebuild Map</button>
        <br>
        <h3>Existing Markers</h3>

        <table>
            $has_str
        </table>

        <br><br><br>
        <button type="button" onclick="saveMapCoordinates();">Save and Rebuild Map</button>
    </form>
</div>
<script type="text/javascript">

    document.getElementById('map-coordinates-form').addEventListener('input', function (evt) {
        // tag rows that have changed
        jQuery(evt.target).closest('tr')[0]['dataset'].changed = '1';
    });

	jQuery('button.delete-link').on('click', (evt) => {

		let jq_tr = jQuery(evt.target).closest('tr');
		jq_tr[0]['dataset'].changed = '1';
		jq_tr.find('input').val('DELETE').attr('disabled', true);
		evt.target.disabled = true;
	})


    function saveMapCoordinates() {

        // get the values that have changed
        const elements = document.querySelectorAll("[data-changed='1']");

        if (elements.length === 0) {
            toastr.info('Nothing to save.');
            return;
        }

        // collect the data for changed items
        let changed = [];

        elements.forEach(tr => {

            let jqtr = jQuery(tr);

            changed.push(
                {
                    link_id: jqtr.data('linkId'),
                    marker_id: jqtr.data('markerId'),
                    lat: jqtr.find('.coordinate.lat').val(),
                    lon: jqtr.find('.coordinate.lon').val()
                }
            )
        })

        // send to the server
        jQuery.ajax({
            url: '$url',
            dataType: 'json',
            type: 'POST',
            data: {items: changed, btnSave: 'Save', action: 'saveMapCoordinates'}
        }).done(function (data) {

            // check for error condition
            if ('success' in data && data['success'] === 'OK') {

                // reload the page
                location.reload();
                return;
            }

            toastr.warning('Unexpected response. Data may not be saved.')

        }).fail(function () {
            toastr.warning('Failed. Data may not be saved.')
        });
    }
</script>
HTML;
}

/**
 * @param bool $hasCoordinates
 * @param array $ethnologueData
 *
 * @return array
 */
function display_links_coordinates(bool $hasCoordinates, array $ethnologueData): array
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

    if ($hasCoordinates)
        $sql .= '  AND lat IS NOT NULL' . PHP_EOL;
    else
        $sql .= '  AND lat IS NULL' . PHP_EOL;

    $sql .= 'ORDER BY link_name ASC';

	$arrLinks = $wpdb->get_results($sql);
	$values = [];

	/** @noinspection HtmlUnknownTarget */
	$template = <<<'HTML'
<tr data-link-id="%3$d" data-marker-id="%4$d" data-changed="%7$d">
<td><a href="%1$s" target="_blank">%2$s</a></td>
<td>Lat: <input class="coordinate lat" type="text" name="lat[]" value="%5$s"></td>
<td>Lon: <input class="coordinate lon" type="text" name="lon[]" value="%6$s"></td>
<td><a href="https://www.whatsmygps.com/index.php?lat=%5$s&lng=%6$s" target="_blank">Find coordinates</a>%8$s</td>
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
		$changed = 0;

		if ($link->lat != null && $link->lon != null) {
			$lat = $link->lat;
			$lon = $link->lon;
		}
		elseif (isset($ethnologueData[$ethnologue_code])) {
			$lat = $ethnologueData[$ethnologue_code]['lat'];
			$lon = $ethnologueData[$ethnologue_code]['lon'];
		}
		else {
			$lat = '';
			$lon = '';
		}

		if ($hasCoordinates) {
			$delete = '&emsp;<button type="button" class="delete-link">Delete</button>';
		}
		else {
			$delete = '';
		}
		if (!$hasCoordinates) {
			if ($lat && $lon)
				$changed = 1;
		}

        $values[] = sprintf($template, $link->link_url, $link->link_name, $link->link_id, $link->markerid, $lat, $lon, $changed, $delete);
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
 * @param $mapPoints
 *
 * @throws Exception
 */
function getEthnologueData(&$ethnologueData, $mapPoints)
{
	foreach($mapPoints as $point)
	{
		// get the language code from the href of the anchor tag
		$url = $point['popup'];
		$a = new SimpleXMLElement($url);
		$code = substr($a['href'], strrpos($a['href'], '/') + 1);

		// Round values to 5 decimal places (about 1 meter).
		// The source file has 15 decimal places (less than 1 nanometer).
		$ethnologueData[$code] = [
			'name' => (string)$a,
			'lat' => round((float)$point['lat'], 5),
			'lon' => round((float)$point['lon'], 5)
		];
	}
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

			if ($item['lat'] == 'DELETE' && $item['lon'] == 'DELETE') {

				$sql = <<<SQL
DELETE FROM wp_map WHERE markerid = %d
SQL;
				$sql = $wpdb->prepare($sql, [$marker_id]);
				$wpdb->query($sql);
			}
			elseif ($lat != '' && $lon != '')
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
		if ($body != '')
			$body .= PHP_EOL;

	    // Round values to 5 decimal places (about 1 meter).
	    $lat = round((float)$link->lat, 5);
		$lon = round((float)$link->lon, 5);
        $body .= sprintf($template, $lat, $lon, $link->link_url, $link->link_name, htmlentities( $link->link_description ));
    }

    $timestamp = date('Y-m-d H:i:s');
	/** @noinspection HttpUrlsUsage */
	$xml = <<<XML
<?xml version='1.0' encoding='UTF-8'?>
<gpx xmlns="http://www.topografix.com/GPX/1/1" xmlns:gpxdata="http://www.cluetrust.com/XML/GPXDATA/1/0" xmlns:t="http://www.garmin.com/xmlschemas/TrainingCenterDatabase/v2" creator="pytrainer http://sourceforge.net/projects/pytrainer" version="1.1">
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
