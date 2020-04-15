<?php
/*
Plugin Name: WP Render Blogroll Links
Version: 2.1.8
Plugin URI: http://0xtc.com/2009/04/22/wp-render-blogroll-links-plugin.xhtml
Description: Outputs your Blogroll links to a Page or Post. Add <code>[wp-blogroll]</code> to a Page or Post and all your Wordpress links/Blogrolls will be rendered. This extremely simple plug-in enables you to create your own Links page without having to write a custom template.<br />The output can easily be styled with CSS. Each category with its links is encapsulated in a DIV  with a classname called "linkcat". All the links are attributed with the class "brlink".
Contributors: 0xtc
Author: Tanin Ehrami
Author URI: http://0xtc.com/
Stable tag: trunk
*/

/*
	About: wp_list_bookmarks_plus, walk_bookmarks_plus:
	I really, REALLY didn't want to do this, but the limitations of the original wp_list_bookmarks and _walk_bookmarks function have forced me to take this path.
	The wp_list_bookmarks_plus function is an evolution of the wp_list_bookmarks function that is privded with Wordpress. Having this function here will allow this plugin to evolve even further than originally intended in the future.
*/

define (WPRBLVERSION, '2.1.7');

function walk_bookmarks_plus($bookmarks, $args = '' ) {
	$defaults = array(
		'show_updated' => 0, 'show_description' => 0, 'forcerel' => '', 'linkclass'=>'','livelinks'=>0,
		'show_images' => 1, 'show_name' => 0,'show_names_under_images' => 0,
		'before' => '<li>', 'after' => '</li>', 'between' => "\n",
		'show_rating' => 0, 'link_before' => '', 'link_after' => '','rss_image'=>'/wp-includes/images/rss.png','show_rss'=>0
	);
	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );
	$output = '';
	foreach ( (array) $bookmarks as $bookmark ) {
		if ( !isset($bookmark->recently_updated) ){
			$bookmark->recently_updated = false;
		}
		$output .= "\t\t\t\t".$before;
		if ( $show_updated && $bookmark->recently_updated ){
			$output .= get_option('links_recently_updated_prepend');
		}
		$the_link = '#';
		if ( !empty($bookmark->link_url) ){
			$the_link = esc_url($bookmark->link_url);
		}
		$the_rss = '{}';
		if ( !empty($bookmark->link_rss) ){
			$the_rss = esc_url($bookmark->link_rss);
		}
		$desc = esc_attr(sanitize_bookmark_field('link_description', $bookmark->link_description, $bookmark->link_id, 'display'));
		$name = esc_attr(sanitize_bookmark_field('link_name', $bookmark->link_name, $bookmark->link_id, 'display'));
 		$title = $desc;

		if ( $show_updated ){
			if ( '00' != substr($bookmark->link_updated_f, 0, 2) ) {
				$title .= ' (';
				$title .= sprintf(__('Last updated: %s'), date(get_option('links_updated_date_format'), $bookmark->link_updated_f + (get_option('gmt_offset') * 3600)));
				$title .= ')';
			}
		}
		$alt = ' alt="' . $name . ( $show_description ? ' ' . $title : '' ) . '"';

		if ( '' != $title ){
			$title = ' title="' . $title . '"';
		}
		$rel = $bookmark->link_rel;
		if ($forcerel != ''){$rel=$forcerel;}

		if ( '' != $rel ){
			$rel = ' rel="' . $rel . '"';
		}
		$target = $bookmark->link_target;
		if ( '' != $target ){
			$target = ' target="' . $target . '"';
		}

		if ( '' != $linkclass){
			$classname = ' class="' . $linkclass . '"';
		}

		if ($livelinks==1){
			$output .= '<span class="livelinks">';
		}


		$output .= '<a href="' . $the_link . '"' . $rel . $title . $target . $classname . '>';
		$output .= $link_before;
		if ( $bookmark->link_image != null && $show_images ) {
			if ( strpos($bookmark->link_image, 'http') === 0 ){
				$output .= "<img src=\"".$bookmark->link_image."\" $alt $title />";
			} else {
				$output .= "<img src=\"" . get_option('siteurl') . "$bookmark->link_image\" $alt $title />";
			}
			if ($show_name){
				if ($show_names_under_images){
					$output .= "<br />";
				}
				$output .= " $name";
			}
		} else {
			$output .= $name;
		}
		$output .= $link_after;
		$output .= '</a>';
		if ($livelinks==1){
			$output .= '</span>';
		}

		if ( $the_rss!='{}' && $show_rss==1) {
			$the_rss_out = apply_filters( 'walk_bookmarks_plus_rss', $the_rss);
			if ($the_rss_out == $the_rss) {
				$output .= '
				<a href="'.esc_url($bookmark->link_rss).'" title="Link feed" class="linkfeedurl">
					<img src="'.get_option('siteurl'). $rss_image . '" alt="site feed" />
				</a>';
			} else {
				$output .= $the_rss_out;
			}
		}

		if ( $show_updated && $bookmark->recently_updated ){
			$output .= get_option('links_recently_updated_append');
		}
		if ( $show_description && '' != $desc ){
			$output .= $between . $desc;
		}
		if ( $show_rating ){
			$output .= $between . sanitize_bookmark_field('link_rating', $bookmark->link_rating, $bookmark->link_id, 'display');
		}
		$output .= "$after\n";
	}
	return $output;
}

function wp_list_bookmarks_plus ($args = '') {
	$defaults = array(
		'showhide'=>0,
		'orderby' => 'name',
		'show_images' => 1,
		'show_name' => 0,
		'show_names_under_images' => 0,
		'forcerel' => '',
		'linkclass'=>'',
		'livelinks'=>0,
		'order' => 'ASC',
		'limit' => -1,
		'rss_image'=>'/wp-includes/images/rss.png',
		'show_rss'=>0,
		'category' => '',
		'exclude_category' => '',
		'category_name' => '',
		'hide_invisible' => 1,
		'show_updated' => 0,
		'echo' => 1,
		'categorize' => 1,
		'title_li' => __('Bookmarks'),
		'title_before' => '<h2 class="linkcattitle">',
		'title_after' => '</h2>',
		'category_orderby' => 'name',
		'category_order' => 'ASC',
		'class' => 'linkcat',
		'category_before' => '<li id="%id" class="%class">',
		'category_after' => '</li>',
		'notitle' => '0',
		'showbrk'=> ' ',
		'between'=> ' ',
		'showdash'=> '0',
		'showcatdesc' => '0',
		'catdescription_before'=> '<p class="catdescription">',
		'catdescription_after'=> '</p>'
	);
	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );
	$output = '';
	if ($categorize) {
		$cats = get_terms('link_category', array('name__like' => $category_name, 'include' => $category, 'exclude' => $exclude_category, 'orderby' => $category_orderby, 'order' => $category_order, 'hierarchical' => 0));
		foreach ( (array) $cats as $cat ) {
			$params = array_merge($r, array('category'=>$cat->term_id));
			$bookmarks = get_bookmarks($params);
			if ( empty($bookmarks) )
				continue;
			$output .= "
		" . str_replace(array('%id', '%class'), array("linkcat-$cat->term_id", $class), $category_before);
			$catname = apply_filters( "link_category", $cat->name );
			$catdescription = apply_filters( "link_category", $cat->description );
			if ($showhide){
				$output .= "
			$title_before<span style=\"cursor:default\" onclick='toggleLinkGrp(\"catid".$cat->term_id."\");'>$catname</span>$title_after";
			} else {
				$output .= "
			$title_before$catname$title_after";
			}
			if ($showcatdesc=='1' && $catdescription <> ''){$output .= "
			$catdescription_before$catdescription$catdescription_after";}

			if ($showhide){
				$output .= '
			<div id="catid'.$cat->term_id.'" style="display:none;">
			<ul class="xoxo blogroll">'."\r\n";
			} else {
				$output .= '
			<div id="catid'.$cat->term_id.'">
			<ul class="xoxo blogroll">'."\r\n";
			}
			$output .= walk_bookmarks_plus($bookmarks, $r);
			$output .= "
			</ul>
			</div>
		$category_after\r\n";
		}
	} else {
		$bookmarks = get_bookmarks($r);
		if ( !empty($bookmarks) ) {
			if ( !empty( $title_li ) ){
				$output .= str_replace(array('%id', '%class'), array("linkcat-$category", $class), $category_before);
				$output .= "$title_before$title_li$title_after\n\t\t\t".'<ul class="xoxo blogroll" id="catid'.$cat->term_id.'">'."\r\n";
				$output .= walk_bookmarks_plus($bookmarks, $r);
				$output .= "
			</ul>
		$category_after\r\n";
			} else {
				$output .= walk_bookmarks_plus($bookmarks, $r);
			}
		}
	}
	// allowing interoperability
	$output = apply_filters( 'wp_list_bookmarks_plus', $output );
	if ( !$echo ){
 		return $output;
	}
	echo $output;
}

/* The function that takes your request and translates it to parameters */
function renderlinks_tc_hnd ($att,$content=null) {
	$wprbr_n = 'WP Render Blogroll Links ';
	$wprbl_v = WPRBLVERSION;
	extract	(shortcode_atts(array('showhide'=>0,'show_images'=>1,'always_show_names'=>0,'show_names_under_images'=>0,'excludecat'=>'','limit'=>'-1','forcerel'=>'','linkclass'=>'','livelinks'=>0,'catorder'=>'ASC','catorderby'=>'name','order'=>'ASC','orderby'=>'name','catid' => '','catname' => '','showcatdesc' => '0','showdesc' => '0','showbrk'=> ' ','showdash'=> '0','notitle'=>'0','rss_image'=>'/wp-includes/images/rss.png','show_rss'=>0), $att));
	if ($notitle=="1"){$notitletag='&categorize=0&title_li= ';} else {$notitletag='';}
	if ($showbrk=="1"){$brk="<br />";} else  if ($showdash=="1") {$brk=" - ";} else {$brk=' ';}
	$wprbl_s = '
		<!-- start['.$wprbr_n.$wprbl_v.'] -->';
	$wprbl_e = '
		<!-- end['.$wprbr_n.$wprbl_v.'] -->';
	return $wprbl_s."\r\n".wp_list_bookmarks_plus("showhide=$showhide&rss_image=$rss_image&show_rss=$show_rss&show_images=$show_images&show_names_under_images=$show_names_under_images&show_name=$always_show_names&exclude_category=$excludecat&category_orderby=$catorderby&category_order=$catorder&forcerel=$forcerel&linkclass=$linkclass&livelinks=$livelinks&limit=$limit&order=$order&orderby=$orderby&show_description=$showdesc&showcatdesc=$showcatdesc&category_name=$catname&category=$catid&category_before=<div class=\"linkcat\">&category_after=</div>&before=<li class=\"brlink\">&after=</li>&echo=0".$notitletag. "&between=".$brk)."\r\n".$wprbl_e;
}

function wprbl_add_javascript() {
	?>
	<script type="text/javascript">
	 //<![CDATA[
	function toggleLinkGrp(id) {
	   var e = document.getElementById(id);
	   if(e.style.display == 'block')
			e.style.display = 'none';
	   else
			e.style.display = 'block';
	}
	// ]]>
	</script>
	<?php
}

function settingsbox1(){
	$outp ='
					<h4>Usage of the <code>[wp-blogroll]</code> shortcode</h4>
					<div style="padding:0 20px">
						<h4>A short introduction</h4>
						<p>WP Render Blogroll Links works using a "shortcode". Shortcodes are snippets of pseudo code that are placed in blog posts, pages and on some forums to easily render HTML output.</p>
						<p>The following is the basic form of a shortcode:</p>
						<p><code>[shortcodename]</code></p>
						<p></p>
						<p>To facilitate customization of shortcodes, parameters are used. Shortcode parameters are entered in the following format:<p>
						<p><code>[shortcodename parametername=parametervalue]</code></p>
						<p></p>
						<p>The "wp-blogroll" shortcode including an example parameter called "catname" looks like this:</p>
						<p><code>[wp-blogroll catname=News]</code></p>
						<p>Below is a list of all the supported parameters and their functions.</p>
						<style type="text/css">
							<!--
							#wprblhelptable {border: 1px solid #aaa;border-collapse:collapse;font:8pt Verdana}
							#wprblhelptable th {background:#f9f9f9;}
							#wprblhelptable td {border-top:1px solid #aaa;padding:4px 3px;vertical-align: top;font:8pt Verdana}
							#wprblhelptable td.secondrow {font:7pt Verdana;padding:4px 4px;}
							-->
						</style>
						<table id="wprblhelptable">
							<tr>
								<th>Parameter name</th>
								<th>Parameter options</th>
								<th>Description</th>
							</tr>
							<tr>
								<td>always_show_names</td>
								<td class="secondrow">0 or 1 (Default:0)</td>
								<td>This option when set to 1 will show link titles even if the link has an image defined.</td>
							</tr>
							<tr>
								<td>catid</td>
								<td class="secondrow">ID(s)</td>
								<td>Use this parameter to specify what categories should be shown by category ID. For example <code>[wp-blogroll catid=12]</code></td>
							</tr>
							<tr>
								<td>catname</td>
								<td class="secondrow">Name(s)</td>
								<td>Use this parameter to specify what categories should be shown by name. For example <code>[wp-blogroll catname=News]</code>. If the catname has spaces, simply wrap the name in quotes. Example: <code>[wp-blogroll catname="Social Media"]</code></td>
							</tr>
							<tr>
								<td>catorder</td>
								<td class="secondrow">ASC, DESC (Default: ASC)</td>
								<td>Indicates the category sort direction in ascending or descending.</td>
							</tr>
							<tr>
								<td>catorderby</td>
								<td class="secondrow">id, name, count, slug or term_group (Default:name)</td>
								<td>This indicates in what order the categories should be ordered by.</td>
							</tr>
							<tr>
								<td>excludecat</td>
								<td class="secondrow">ID of a link category</td>
								<td>This will exclude the set categories from displaying their links. Multiple values may be entered separated by comma (,). Example: <code>[wp-blogroll excludecat=34,35]</code></td>
							</tr>
							<tr>
								<td>forcerel</td>
								<td class="secondrow">Relationship (Default:blank)</td>
								<td>Forces a relationship tag on all links. For example rel=&quot;external&quot; can be achieved using <code>[wp-blogroll forcerel=external]</code></td>
							</tr>
							<tr>
								<td>limit</td>
								<td class="secondrow">n</td>
								<td>Limits the number of links displayed per category. For example, to show a maximum of 20 links per category, use <code>[wp-blogroll limit=20]</code></td>
							</tr>
							<tr>
								<td>notitle</td>
								<td class="secondrow">0 or 1 (Default:0)</td>
								<td>This removes categorization and as a result removes the category titles</td>
							</tr>
							<tr>
								<td>order</td>
								<td class="secondrow">ASC, DESC (Default: DESC)</td>
								<td>Indicates the link sort direction in ascending or descending.</td>
							</tr>
							<tr>
								<td>orderby</td>
								<td class="secondrow">id, url, name, target, description, owner, rating, updated, rel, notes, rss, length, rand</td>
								<td>This indicates in what order the links displayed should be ordered by.</td>
							</tr>
							<tr>
								<td>rss_image</td>
								<td class="secondrow">Relative path. Default is \'/wp-includes/images/rss.png\'</td>
								<td>A relative path the icon that is to be used as RSS link for links. For example: /wp-includes/images/wlw/wp-icon.png</td>
							</tr>
							<tr>
								<td>show_images</td>
								<td class="secondrow">0 or 1 (Default:1)</td>
								<td>By default links that have images defined will display them. To disable this, select 0 as the value.</td>
							</tr>
							<tr>
								<td>show_names_under_images</td>
								<td class="secondrow">0 or 1 (Default:0)</td>
								<td>In combination with the always_show_names parameter, this option puts the titles of the links with images, under their images by adding a BR tag.</td>
							</tr>
							<tr>
								<td>show_rss</td>
								<td class="secondrow">0 or 1 (Default:0)</td>
								<td>Indicates whether links that have RSS feeds associated with them should show them.</td>
							</tr>
							<tr>
								<td>showdash</td>
								<td class="secondrow">0 or 1 (Default:0)</td>
								<td>This indicate you want a dash ( - ) between the link and its description. (This will also add a space before and after the dash.)</td>
							</tr>
							<tr>
								<td>showbrk</td>
								<td class="secondrow">0 or 1 (Default:0)</td>
								<td>This indicate you want a line break between the link and its description.</td>
							</tr>
							<tr>
								<td>showcatdesc</td>
								<td class="secondrow">0 or 1 (Default:0)</td>
								<td>Use this parameter to show the description set for each category.</td>
							</tr>
							<tr>
								<td>showdesc</td>
								<td class="secondrow">0 or 1 (Default:0)</td>
								<td>Use this parameter to show the description set for each link.</td>
							</tr>
							<tr>
								<td>showhide</td>
								<td class="secondrow">0 or 1 (Default:0)</td>
								<td>Indicates whether the links should remain hidden until the category is clicked on.</td>
							</tr>
						</table>
						<p>To get your desired result, you are also free to use (almost) any combination of the parameters.</p>
						<p>Example:</p>
						<p><code>[wp-blogroll limit=2 orderby=name order=ASC excludecat=4,5]</code></p>
					</div>';
	echo $outp;
}

function donatebox(){
?>

<div style="border:1px dashed #eb9320;background-color:#fafcc7;margin:0 auto 5px auto;padding:5px;">
<p>If you find this plugin useful and would like to contribute to its further development, consider making a donation.</p>
<p style="text-align:center;"><strong> - Tanin</strong></p>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<p style="text-align:center">
<input type="hidden" name="cmd" value="_s-xclick"/>
<input type="hidden" name="hosted_button_id" value="6228273"/>
<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!"/>
<br /><img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1"/></p>
</form>
</div>

<?php
}

function settingsbox2(){
	$bml = 'javascript:void(linkmanpopup=window.open(\''.get_bloginfo('wpurl').'/wp-admin/link-add.php?action=popup&linkurl=\'+escape(location.href)+\'&name=\'+(document.title),\'LinkManager\',\'scrollbars=yes,width=960px,height=550px,left=15,top=15,status=yes,resizable=yes\'));linkmanpopup.focus();window.focus();linkmanpopup.focus();';
	$outp= '
							<p><strong>Add new links to your blogroll with a few simple clicks thanks to this amazing <em>bookmarklet</em>!</strong></p>
							<p>To use this feature, simply <strong>drag the bookmarklet below to your toolbar</strong> or bookmarks so it\'s ready to be clicked whenever you find something interesting.</p>
							<p>This is the bookmarklet:<br />
							<a href="'.$bml.'" style="text-shadow: 1px 1px 1px rgba(255,255,255, .9);color:#030;font:bold 10pt Verdana;text-decoration:none;line-height:20px;text-decoration: none;vertical-align:bottom;background:#5c0;padding:1px 15px;border:2px solid #333;border-radius:15px;-moz-border-radius:15px;-webkit-border-radius: 15px;" title="Add to BlogRoll">Add to Blogroll</a></p>
							<p>You\'re done! All you have to do now is click on the bookmarklet in your toolbar whenever you want to add a site and as if by magic the links will appear on your blogroll!</p>
							<p></p>
							<p>Your friends will go bonkers as you do your blogroll magic!</p>';
	echo $outp;
}

function renderlinks_settings_page(){
  if( function_exists( 'add_meta_box' )) {
    add_meta_box( 'WPRenderBlogrollLinks-2', 'Bookmartklet', 'settingsbox2','WPRBL','side');
    add_meta_box( 'WPRenderBlogrollLinks-3', 'Donations', 'donatebox','WPRBL','side');
    add_meta_box( 'WPRenderBlogrollLinks-1', 'Documented options', 'settingsbox1', 'WPRBL', 'normal');
?>
		<div id="wprbl-mbox-general" class="wrap">
			<?php screen_icon('options-general'); ?>
			<h2>WP Render Blogroll Links</h2>
			<?php
			wp_nonce_field('wprbl-mbox-general');
			wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false );
			wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false );
			?>
			<div id="poststuff" class="metabox-holder has-right-sidebar">
				<div id="side-info-column" class="inner-sidebar">
					<?php do_meta_boxes('WPRBL', 'side', null); ?>
				</div>
				<div id="post-body" class="has-sidebar">
					<div id="post-body-content" class="has-sidebar-content">
						<?php do_meta_boxes('WPRBL', 'normal', null); ?>
					</div>
				</div>
				<br class="clear"/>
			</div>
		</div>
<?php
  }
}

function renderlinks_admin() {
	if (function_exists('add_options_page')) {
		add_options_page('WP Render Blogroll Links', 'WP Blogroll Links', 8, basename(__FILE__), 'renderlinks_settings_page');
	}
}

add_action('admin_menu', 'renderlinks_admin');
add_action('wp_head', 'wprbl_add_javascript');
add_shortcode('wp-blogroll', 'renderlinks_tc_hnd')

?>