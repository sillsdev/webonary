<?php
$scriptUpdated = "2015-12-23 00:00:00";
/*
 * Template Name: OLAC
 */
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<Repository xmlns="http://www.openarchives.org/OAI/2.0/static-repository" xmlns:oai="http://www.openarchives.org/OAI/2.0/" xmlns:olac="http://www.language-archives.org/OLAC/1.1/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/static-repository              http://www.language-archives.org/OLAC/1.1/static-repository.xsd                 http://www.language-archives.org/OLAC/1.1/                 http://www.language-archives.org/OLAC/1.1/olac.xsd                 http://purl.org/dc/elements/1.1/                 http://dublincore.org/schemas/xmls/qdc/2006/01/06/dc.xsd                 http://purl.org/dc/terms/                 http://dublincore.org/schemas/xmls/qdc/2006/01/06/dcterms.xsd">

<!-- This document is valid according to both of the following schemas:
       http://www.openarchives.org/OAI/2.0/static-repository.xsd
       http://www.language-archives.org/OLAC/1.1/static-repository.xsd
-->

<Identify>
<oai:repositoryName>Webonary Sites</oai:repositoryName>
<oai:baseURL>https://www.webonary.org/olac</oai:baseURL>
<oai:protocolVersion>2.0</oai:protocolVersion>
<oai:adminEmail>webonary@sil.org</oai:adminEmail>
<oai:earliestDatestamp>2015-09-22</oai:earliestDatestamp>
<oai:deletedRecord>no</oai:deletedRecord>
<oai:granularity>YYYY-MM-DD</oai:granularity>
<oai:description>
	<oai-identifier xmlns="http://www.openarchives.org/OAI/2.0/oai-identifier" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai-identifier           http://www.openarchives.org/OAI/2.0/oai-identifier.xsd">
	<scheme>oai</scheme>
	<repositoryIdentifier>webonary.org</repositoryIdentifier>
	<delimiter>:</delimiter>
	<sampleIdentifier>oai:webonary.org:01</sampleIdentifier>
	</oai-identifier>
</oai:description>
<oai:description>
	<olac-archive type="institutional" currentAsOf="2015-09-22" xmlns="http://www.language-archives.org/OLAC/1.1/olac-archive" xsi:schemaLocation="http://www.language-archives.org/OLAC/1.1/olac-archive            http://www.language-archives.org/OLAC/1.1/olac-archive.xsd">
	<archiveURL>https://www.webonary.org</archiveURL>
	<participant email="webonary@sil.org" name="Philip Perry"
		role="Developer" />
	<participant email="verna_stutzman@sil.org" name="Verna Stutzman"
		role="Editor" />
	<institution>SIL International</institution>
	<institutionURL>http://www.sil.org</institutionURL>
    <shortLocation>Dallas, USA</shortLocation>
    <location>7500 W. Camp Wisdom Rd., Dallas, TX 75236, U.S.A.</location>
	<synopsis>webonary.org is a plattform for publishing dictionaries online</synopsis>
	<access>Access to web based content is open. </access>
	<archivalSubmissionPolicy>Any one who agrees with the terms of service published on the website may submit a dictionary produced with FLEx for publication by Webonary.</archivalSubmissionPolicy>
	</olac-archive>
</oai:description>
</Identify>

<ListMetadataFormats>

<oai:metadataFormat>
	<oai:metadataPrefix>olac</oai:metadataPrefix>
	<oai:schema>http://www.language-archives.org/OLAC/1.1/olac.xsd</oai:schema>
	<oai:metadataNamespace>http://www.language-archives.org/OLAC/1.1/ </oai:metadataNamespace>
</oai:metadataFormat>

</ListMetadataFormats>

<ListRecords metadataPrefix="olac">
 <?php
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

	if (0 < count ( $blogs )) :
	    $i = 1;
		foreach ( $blogs as $blog ) :
			// echo $blog['blog_id'] . "<br>";

			$sql = "SELECT REPLACE(meta_value, 'https://www.ethnologue.com/language/','') AS ethnologueCode " . " FROM wp_" . $blog ['blog_id'] . "_postmeta " . " WHERE meta_key = '_menu_item_url' AND meta_value LIKE '%ethnologue%'";

			$ethnologue_code = trim($wpdb->get_var ( $sql ));

			if (strlen($ethnologue_code) == 3) {
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

				$lastChange = $recordUpdated;
				if($scriptUpdated > $recordUpdated)
				{
					$lastChange = $scriptUpdated;
				}
			?>
			 <oai:record>
				<oai:header>
					<oai:identifier>oai:webonary.org:<?php echo sprintf('%02d', $i);?></oai:identifier>
					<oai:datestamp><?php echo date("Y-m-d", strtotime($lastChange)); ?></oai:datestamp>
				</oai:header>
				<oai:metadata>
					<olac:olac>
						<dc:title><?php echo $site_title; ?></dc:title>
						<dc:format xsi:type="dcterms:IMT">text/html</dc:format>
						<dc:identifier xsi:type="dcterms:URI"><?php echo $blog ['link_url']; ?></dc:identifier>
						<dc:subject xsi:type="olac:language" olac:code="<?php echo $ethnologue_code; ?>" />
						<dc:type xsi:type="olac:linguistic-type" olac:code="lexicon" />
						<dc:type xsi:type="dcterms:DCMIType">Text</dc:type>
						<dcterms:extent><?php echo $entriesTotal; ?> entries</dcterms:extent>

						<dc:date><?php echo $blog['link_updated']; ?></dc:date>
						<dc:publisher>SIL International</dc:publisher>
					</olac:olac>
				</oai:metadata>
			 </oai:record>
<?php
		$i++;
		}
	endforeach;
endif;
	?>
  </ListRecords>
</Repository>
