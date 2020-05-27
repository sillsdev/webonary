<?php
/*
Plugin Name: Shockingly Simple Favicon
Plugin URI: http://www.incerteza.org/blog/projetos/shockingly-simple-favicon/
Description: A simple way to put a <a href="http://en.wikipedia.org/wiki/Favicon" target="blank">favicon</a> on your site.
Author: matias s
Version: 1.8.2
Author URI: http://www.incerteza.org/blog/
*/

/*
Copyright 2008  Matias Schertel  (email : matias@incerteza.org)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
// Global Variables
$favi_dom = 'shockingly-simple-favicon';

// DEFAULT OPTIONS
function favi_default_opt() {
	$url = get_option('siteurl') . '/favicon.ico';
	$setup = array(
		'url' => $url,
		'admin' => 'default'
	);
	return $setup;
}

// ACTIVATION
register_activation_hook(__FILE__ , 'favi_activate');
function favi_activate() {
	$opt = get_option('favi_options');
	if (!is_array($opt)) {
		delete_option('favi_setup');	// OLD NORMAL OPTIONS
		delete_option('favi_url');		// OLD NORMAL OPTIONS
		delete_option('favi_admin');	// OLD NORMAL OPTIONS
		add_option('favi_options', favi_default_opt());
	} else {
		//update_option('favi_options', favi_default_opt());
	}
}

// DEACTIVATION
register_deactivation_hook(__FILE__, 'favi_deactivate');
function favi_deactivate() {
	//delete_option('favi_options');
}

// FUNCTION: favi_geturl() - return favicon url
function favi_geturl() {
	$opt = get_option('favi_options');
	$icon = $opt['url'];
	if ( $icon == '' || $icon == NULL ) { // se favicon url n�o setada, seta como favicon padr�o
		$icon = get_option('siteurl') . '/wp-content/plugins/shockingly-simple-favicon/default/favicon.ico';
	}
	return $icon;
}

// HEADER: Blog
add_action('wp_head', 'favi_head');
function favi_head() {
	echo '<link rel="shortcut icon" href="' . favi_geturl() . '" type="image/x-icon" /><!-- Favi -->';
}

// HEADER: Admin
if ( is_admin() ) {	add_action('admin_head', 'favi_admin_head'); }
function favi_admin_head() {
	$opt = get_option('favi_options');
	if ( $opt['admin'] == 'default' ) {
		echo '<link rel="shortcut icon" href="' . get_option('siteurl') . '/wp-content/plugins/shockingly-simple-favicon/admin/favicon.ico" type="image/x-icon" /><!-- Favi -->';
	} 	else if ( $opt['admin'] == 'blog' ) {
		echo '<link rel="shortcut icon" href="' . favi_geturl() . '" type="image/x-icon" /><!-- Favi -->';
	}
}

// INITIALIZATION - locales @ /lang/
if ( is_admin() ) { add_action('init', 'favi_init'); }
function favi_init() {
	global $favi_dom;
	load_plugin_textdomain($favi_dom, false, dirname(plugin_basename(__FILE__ )) .'/lang/');
}

// OPTIONS PAGE
if ( is_admin() ) {	add_action('admin_menu', 'favi_menu'); }
function favi_menu() {
	add_options_page(__('Shockingly Simple Favicon Options', $favi_dom), __('S. Simple Favicon', $favi_dom), 8, __FILE__, 'favi_options');
}
function favi_options() {
global $favi_dom;
$opt = get_option('favi_options');
$plug_name = 'Shockingly Simple Favicon';
$plug_ver = '1.8.2';
$plug_site = 'http://www.incerteza.org/blog/projetos/shockingly-simple-favicon/';
	if ( isset($_POST['update_options']) ) {
		$opt['url'] = $_POST['favi_form_url_opt'];
		$opt['admin'] = $_POST['favi_form_admin_opt'];
		update_option('favi_options', $opt);
        echo '<div id="message" class="updated fade"><p><strong>' . __('Settings saved.', $favi_dom) . '</strong></p></div>';
	} else if ( isset($_POST['reset_options']) ) {
		$opt = favi_default_opt();
		update_option('favi_options', $opt);
		echo '<div id="message" class="updated fade"><p><strong>' . __('Default options loaded.', $favi_dom) . '</strong></p></div>';
	}
?>
<div class="wrap">
  <h2><?php echo __('Shockingly Simple Favicon Options', $favi_dom); ?></h2>
  <p><?php echo __('"<i>A <a href="http://en.wikipedia.org/wiki/Favicon" target="_blank" rel="nofollow">favicon</a> (short for favorites icon), also known as a website icon, shortcut icon, url icon, or bookmark icon is an icon associated with a particular website or webpage.</i>"', $favi_dom); ?></p>
  <p><?php echo __('Upload your favicon to', $favi_dom) . ' <strong>' . get_option('siteurl') . '/</strong>, ' . __('the file should be named <strong>favicon.ico</strong>.', $favi_dom); ?></p>
  <p><?php echo __('A favicon is normally a .ico file with 16x16 in size, although .png and .gif are also suported, IE (obviously) only supports the .ico file.', $favi_dom); ?></p>
  <p><?php echo __('A cool site to you draw yours is <a href="http://www.favicon.cc/" target="_blank" rel="nofollow">favicon.ico Generator</a>.', $favi_dom); ?></p>
  <p></p>
  <p><?php echo __('<strong>Attention</strong>: Internet Explorer (7.0 or lesses) handles favicons in a horrible way, the code use works in IE7 and IE6, but there are somethings you have to pay attention for it works:', $favi_dom); ?></p>
  <ul style="margin-left:15px">
    <li><?php echo __('The .ico file should be 16x16 in size;', $favi_dom); ?></li>
    <li><?php echo __('The .ico file should be a 100% valid icon;', $favi_dom); ?></li>
    <li><?php echo __('There cant be another sizes in the .ico file (icons can have many sizes images in one file);', $favi_dom); ?></li>
    <li><?php echo __('It cant be animated (<strong>most important!</strong>).', $favi_dom); ?></li>
  </ul>
  <p><?php echo __('If you want to have certain that your favicon match this characteristics i recommend that you use the freeware icon edition program <a href="http://icofx.ro/" target="_blank" rel="nofollow">icoFX</a>, open your favicon.ico, delete any size image diferent then 16x16, delete other frames (if animated), and save as a new favicon.ico, that should do the trick!', $favi_dom); ?></p>
  <h3><?php echo __('Settings', $favi_dom); ?></h3>
  <p style="margin-left:10px;"><?php echo __('By default the <strong>favicon.ico</strong> will be loaded from the root directory of your blog', $favi_dom) . ' (<strong>' . get_option('siteurl') . '/</strong>). ' . __('Here you can specify a different location, leave this field <em>blank</em> for the default <strong>favicon</strong>', $favi_dom) . ' (<img src="' . get_option('siteurl') . '/wp-content/plugins/shockingly-simple-favicon/default/favicon.ico" width="16" height="16">).'; ?></p>
  <form method="post" name="options" target="_self">
    <p>
      <label for="name" style="width: 125px;float: left;text-align: left;margin-left: 10px;margin-right: 10px"><?php echo __('Favicon URL', $favi_dom); ?></label>
      <input type="text" name="favi_form_url_opt" style="width:500px;background-image:url(<?php echo favi_geturl(); ?>);background-repeat:no-repeat;padding-left:20px;background-position:3px center;" value="<?php echo $opt['url']; ?>" />
    </p>
    <p>
      <label for="name" style="width: 125px;float: left;text-align: left;margin-left: 10px;margin-right: 10px"><?php echo __('Admin favicon', $favi_dom); ?></label>
      <select name="favi_form_admin_opt" style="width: 100px">
        <option value="none" <?php if ( $opt['admin'] == 'none' ) echo 'selected="selected"'; ?> />
        <?php echo __('None', $favi_dom); ?>
        </option>
        <option value="default" <?php if ( $opt['admin'] == 'default' ) echo 'selected="selected"'; ?> style="background-image:url(<?php echo get_option('siteurl'); ?>/wp-content/plugins/shockingly-simple-favicon/admin/favicon.ico);background-repeat:no-repeat;background-position:98% 50%;"/>
        <?php echo __('Default', $favi_dom); ?>
        </option>
        <option value="blog" <?php if ( $opt['admin'] == 'blog' ) echo 'selected="selected"'; ?> style="background-image:url(<?php echo favi_geturl(); ?>);background-repeat:no-repeat;background-position:98% 50%;"/>
        <?php echo __('Blog', $favi_dom); ?>
        </option>
      </select>
    </p>
    <p><?php echo __('If you use the <strong>Blog</strong> option in <i>Admin Favicon</i> you need to have a icon at', $favi_dom) . ' <strong>' . get_option('siteurl') . '/</strong> ' . __('or the favicon at the admin area will be blank.',$favi_dom); ?></p>
    <p class="submit">
      <input type="submit" name="update_options" class="button-primary" value="<?php echo __('Save Changes', $favi_dom); ?>"/>
      <input type="submit" name="reset_options" value="<?php echo __('Reset Options', $favi_dom); ?>" />
    </p>
  </form>
  <hr />
  <p><?php echo __('<strong>Note</strong>: i\'m learning PHP & Wordpress coding and using this plugin to study, so if you have any idea or any kind of suggestion please contact me.', $favi_dom); ?></p>
  <p><?php echo '<a href="' . $plug_site . '">' . $plug_name . ' v' . $plug_ver . '</a> ' . __('by', $favi_dom) . ' <a href="mailto:matias@incerteza.org">matias s.</a> ' . __('at', $favi_dom) . ' <a href="http://www.incerteza.org/blog/" target="_blank" rel="nofollow">incerteza.org</a>'; ?></p>
</div>
<?php }
?>
