<?php
/** @noinspection SqlResolve */


$now = gmdate('Y-m-d\TH.i.s\Z');
$today = gmdate('Y-m-d');

header('Content-disposition: attachment; filename="WebonarySites_' . $now . '.tsv"');
header('Content-Type: text/plain');

$rows = [['Site Title', 'Country', 'URL', 'Copyright', 'Code', 'Entries', 'CreateDate', 'PublishDate', 'ContactEmail', 'Notes', 'LastImport']];

$sql =  "SELECT blog_id, domain, DATE_FORMAT(registered, '%Y-%m-%d') AS registered FROM $wpdb->blogs
    WHERE blog_id != $wpdb->blogid
    AND site_id = '$wpdb->siteid'
    AND spam = '0'
    AND deleted = '0'
    AND archived = '0'
    order by registered DESC";

$blogs = $wpdb->get_results($sql, ARRAY_A);

if ( 0 < count( $blogs ) ) {
    foreach( $blogs as $blog )  {

        switch_to_blog( $blog[ 'blog_id' ] );

        if ( get_theme_mod( 'show_in_home', 'on' ) !== 'on' )
            continue;

        $description  = get_bloginfo( 'description' );
        $blog_details = get_blog_details( $blog[ 'blog_id' ] );
        $fields = [];

        // 20190210 chungh: allow correct processing for subdirectory based multisite
        $domainPath = $blog_details->domain . $blog_details->path;

        preg_match_all('~[:en]](.+?)[\\[]~', $blog_details->blogname, $blognameMatches);
        if(count($blognameMatches[0]) > 0)
            $fields[] = $blognameMatches[1][0];
        else
            $fields[] = $blog_details->blogname;

        $fields[] = $wpdb->get_var("SELECT option_value FROM $wpdb->options WHERE option_name = 'countryName'");

        $fields[] = 'https://' . $domainPath;

        $themeOptions = get_option('themezee_options');
        $arrFooter = explode('©', str_replace('®', '', $themeOptions['themeZee_footer']));
        $copyright = $arrFooter[1];
        $copyright = preg_replace('/\d{4}/', '', $copyright);
        $copyright = str_replace('[year]', '', $copyright);

        $fields[] = trim($copyright);

        $sql = "SELECT REPLACE(meta_value, 'https://www.ethnologue.com/language/','') AS ethnologueCode " . " FROM wp_" . $blog ['blog_id'] . "_postmeta " . " WHERE meta_key = '_menu_item_url' AND meta_value LIKE '%ethnologue%'";

        $ethnologue_code = trim($wpdb->get_var ( $sql ));
        $fields[] = $ethnologue_code;

        $numpost = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'post'");
        $fields[] = $numpost;
        $fields[] = $blog['registered'];

        /** @noinspection SqlResolve */
        $publishedDate = $wpdb->get_var( "SELECT DATE_FORMAT(link_updated, '%Y-%m-%d') AS link_updated FROM wp_links WHERE link_url LIKE '%" . $domainPath . "%'");
        $fields[]      = $publishedDate;

        $email = $wpdb->get_var("SELECT option_value FROM $wpdb->options WHERE option_name = 'admin_email'");

        $fields[] = $email;

        $notes = $wpdb->get_var("SELECT option_value FROM $wpdb->options WHERE option_name = 'notes'");

        $fields[] = stripslashes($notes);

        $lastEditDate = $wpdb->get_var("SELECT post_date FROM wp_" . $blog ['blog_id'] . "_posts WHERE post_status = 'publish' AND post_type = 'post' ORDER BY post_date DESC");
		if (empty($lastEditDate))
			$fields[] = '';
		elseif ($lastEditDate == '0000-00-00 00:00:00')
			$fields[] = '';
		else
            $fields[] = $lastEditDate;

	    $mapped = array_map(function($a) {

			$s = preg_replace('/<script.+<\/script>/s', '', $a);
		    $s = preg_replace('/[\r\n\t]/', ',', $s);
		    return htmlspecialchars_decode(str_replace('<sup></sup>', '', $s), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401);
	    }, $fields);

		$rows[] = $mapped;

        restore_current_blog();
    }
}

/** @noinspection PhpUnhandledExceptionInspection */
Webonary_Excel::ToExcel( 'WebonarySites_' . $now, 'Webonary Sites: ' . $today, $rows);
