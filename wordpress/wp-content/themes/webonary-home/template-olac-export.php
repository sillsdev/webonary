<?php
/*
 * Template Name: OLAC Export
 */

// 20200210 chungh: Make this work for both subdirectory and subdomain based multisite
$sql = "SELECT blog_id, link_url, DATE_FORMAT(link_updated, '%Y-%m-%d') AS link_updated
	FROM wp_links
	INNER JOIN wp_term_relationships ON  wp_term_relationships.object_id = wp_links.link_id
	INNER JOIN wp_blogs ON wp_links.link_url = CONCAT('https://',wp_blogs.domain, wp_blogs.path)
	WHERE wp_term_relationships.term_taxonomy_id = 8
	ORDER BY link_url ASC";

$blogs = $wpdb->get_results ( $sql, ARRAY_A );
// get all blogs that are linked on webonary homepage
// $blogs = get_blog_list( 0, 'all' );

if (0 < count ( $blogs ))
{
	$i = 1;
	echo "Published Date;Site Title;URL;Code;Entries;Last Import<br>";
	foreach ( $blogs as $blog ) 
	{
		$sql = "SELECT REPLACE(meta_value, 'https://www.ethnologue.com/language/','') AS ethnologueCode " . " FROM wp_" . $blog ['blog_id'] . "_postmeta " . " WHERE meta_key = '_menu_item_url' AND meta_value LIKE '%ethnologue%'";

		$ethnologue_code = trim($wpdb->get_var ( $sql ));

		$sql = "SELECT option_value " . " FROM wp_" . $blog ['blog_id'] . "_options " . " WHERE option_name = 'blogname'";

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

		$entriesTotal = $wpdb->get_var("SELECT COUNT(*) FROM wp_" . $blog ['blog_id'] . "_posts WHERE post_status = 'publish' AND post_type = 'post'");

		$lastEditDate = $wpdb->get_var("SELECT post_date FROM wp_" . $blog ['blog_id'] . "_posts WHERE post_status = 'publish' AND post_type = 'post' ORDER BY post_date DESC");

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