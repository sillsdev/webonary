<?php
/*
Template Name: Display Sites
*/
?>
Site Title;Country;URL;Copyright;Code;Entries;CreateDate;PublishDate;ContactEmail;Notes;LastImport;
<?php
echo "<br>";

$sql =  "SELECT blog_id,domain, DATE_FORMAT(registered, '%Y-%m-%d') AS registered FROM {$wpdb->blogs}
    WHERE blog_id != {$wpdb->blogid}
    AND site_id = '{$wpdb->siteid}'
    AND spam = '0'
    AND deleted = '0'
    AND archived = '0'
    order by registered DESC";

$blogs = $wpdb->get_results($sql, ARRAY_A);
// get all blogs
//$blogs = get_blog_list( 0, 'all' );

if ( 0 < count( $blogs ) ) :
    foreach( $blogs as $blog ) :
        switch_to_blog( $blog[ 'blog_id' ] );

        if ( get_theme_mod( 'show_in_home', 'on' ) !== 'on' ) {
            continue;
        }

        $description  = get_bloginfo( 'description' );
        $blog_details = get_blog_details( $blog[ 'blog_id' ] );

	// 20190210 chungh: allow correct processing for subdirectory based multisite
	$domainPath = $blog_details->domain . $blog_details->path;

        preg_match_all('~[\\:en]](.+?)[\\[]~', $blog_details->blogname, $blognameMatches);
        if(count($blognameMatches[0]) > 0)
        {
        	echo  $blognameMatches[1][0] . ";";
        }
        else
        {
        	echo $blog_details->blogname . ";";
        }

        $countryName = $wpdb->get_var("SELECT option_value FROM $wpdb->options WHERE option_name = 'countryName'");
        echo $countryName . ";";


        echo "<a href=\"https://" . $domainPath . "\" target=\"_blank\">" . $domainPath . "</a>;";

        $themeOptions = get_option('themezee_options');
        $arrFooter = explode("©", str_replace("®", "", $themeOptions['themeZee_footer']));
        $copyright = $arrFooter[1];
        $copyright = preg_replace("/\d{4}/", "", $copyright);
        $copyright = str_replace("[year]", "", $copyright);

        echo trim($copyright) . ";";

        $sql = "SELECT REPLACE(meta_value, 'https://www.ethnologue.com/language/','') AS ethnologueCode " . " FROM wp_" . $blog ['blog_id'] . "_postmeta " . " WHERE meta_key = '_menu_item_url' AND meta_value LIKE '%ethnologue%'";

        $ethnologue_code = trim($wpdb->get_var ( $sql ));
        echo $ethnologue_code . ";";

        $numpost = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'post'");
        echo $numpost . ";";
        echo $blog['registered'] . ";";

        $publishedDate = $wpdb->get_var("SELECT DATE_FORMAT(link_updated, '%Y-%m-%d') AS link_updated FROM wp_links WHERE link_url LIKE '%" . $domainPath . "%'");
        echo $publishedDate . ";";

         /*
         $blogusers = get_users();
         foreach ($blogusers as $user) {
            echo $user->user_email . ' ';
       	 }
         */

         $email = $wpdb->get_var("SELECT option_value FROM $wpdb->options WHERE option_name = 'admin_email'");

         echo $email . ";";

         $notes = $wpdb->get_var("SELECT option_value FROM $wpdb->options WHERE option_name = 'notes'");

         echo stripslashes($notes) . ";";

         $lastEditDate = $wpdb->get_var("SELECT post_date FROM wp_" . $blog ['blog_id'] . "_posts WHERE post_status = 'publish' AND post_type = 'post' ORDER BY post_date DESC");
         echo $lastEditDate;


       /*
         $sql = "UPDATE  $wpdb->options SET option_value = '". $formData['email_to'] . "' WHERE option_name = 'admin_email'";

		$wpdb->query( $sql );
        */

        restore_current_blog();

         echo "<br>";
         ?>
<?php endforeach;
endif; ?>
