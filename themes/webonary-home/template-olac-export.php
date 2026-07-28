<?php
/*
 * Template Name: OLAC Export
 */

// 20200210 chungh: Make this work for both subdirectory and subdomain based multisite
$sql = <<<SQL
SELECT blog_id, link_url, DATE_FORMAT(link_updated, '%Y-%m-%d') AS link_updated
FROM {$wpdb->prefix}links AS l
  INNER JOIN {$wpdb->prefix}term_relationships AS r ON  r.object_id = l.link_id
  INNER JOIN {$wpdb->prefix}blogs AS b ON l.link_url = CONCAT('https://', b.domain, b.path)
WHERE r.term_taxonomy_id = 8
ORDER BY link_url ASC
SQL;

$blogs = $wpdb->get_results ( $sql, ARRAY_A );
// get all blogs that are linked on webonary homepage
// $blogs = get_blog_list( 0, 'all' );

if (0 < count ( $blogs ))
{
	$i = 1;
	echo "Published Date;Site Title;URL;Code;Entries;Last Import<br>";
	foreach ( $blogs as $blog )
	{
		$sql = "SELECT REPLACE(meta_value, 'https://www.ethnologue.com/language/','') AS ethnologueCode " . " FROM {$wpdb->prefix}" . $blog ['blog_id'] . "_postmeta " . " WHERE meta_key = '_menu_item_url' AND meta_value LIKE '%ethnologue%'";

		$ethnologue_code = trim($wpdb->get_var ( $sql ));

		$sql = "SELECT option_value " . " FROM {$wpdb->prefix}" . $blog ['blog_id'] . "_options " . " WHERE option_name = 'blogname'";

		$blogname = $wpdb->get_var ( $sql );

		preg_match_all('~[\\:en]](.+?)[\\[]~', $blogname, $blognameMatches);
		if(count($blognameMatches[0]) > 0)
		{
			$site_title =  $blognameMatches[1][0];
		}
		else
		{
			$site_title = $blogname;
		}

		$entriesTotal = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}" . $blog ['blog_id'] . "_posts WHERE post_status = 'publish' AND post_type = 'post'");

		$lastEditDate = $wpdb->get_var("SELECT post_date FROM {$wpdb->prefix}" . $blog ['blog_id'] . "_posts WHERE post_status = 'publish' AND post_type = 'post' ORDER BY post_date DESC");

		if($lastEditDate > $blog['link_updated'])
		{
			$recordUpdated = $lastEditDate;
		}
		else
		{
			$recordUpdated = $blog['link_updated'];
		}

		$output = implode(';', array(
			date("Y-m-d", strtotime($recordUpdated)),
			$site_title,
			$blog['link_url'],
			$ethnologue_code,
			$entriesTotal,
			date("Y-m-d", strtotime($lastEditDate))));

		echo "$output<br>";
		$i++;
	}
}
?>
