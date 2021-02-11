<?php
/*
Plugin Name: Google Analytics Input
Plugin URI: http://wpable.com/wordpress-plugins/
Description: Google Analytics Input.. A simple and effective plugin, just add your google analytics ID then save.. and your ready to go! It will not track logged-in users such as admin because that would invalidate your results.
Version: 1.0
Author: Roy Duff
Author URI: http://wpable.com/
*/
?>
<?php
//create the admin settings.

	add_action('admin_menu', 'gaip_create_menu');
function gaip_create_menu() {
	
	add_options_page('Google Analytics Input Settings', 'Google Analytics Input Setting', 'administrator', __FILE__, 'gaip_settings_page');
	
	add_action( 'admin_init', 'register_mysettings' );
}	
function register_mysettings() {
	
	register_setting( 'gaip-settings-group', 'new_option_name' );
}
function gaip_settings_page() {
?>
<div class="wrap">
	<h2>Google Analytics Input.</h2>
		<p>
This is a very simple, but problem solver tool. Allowing you to add your google analytics code into wordpress. All you need is your google analytics id, type this into the box below.. click save and your ready to go! This plugin will not track logged in users because doing so would give you invaild results. Everything else gets tracked!
		</p>
	
	<form method="post" action="options.php">
<?php settings_fields( 'gaip-settings-group' ); ?>
    
	<table class="form-table">
        	<tr valign="top">
        	<th scope="row">Put your Google Analytics ID here--></th>
        	<td><input type="text" name="new_option_name" value="<?php echo get_option('new_option_name'); ?>"> An example; UA-0000000-0.</td>
        	</tr>
         
    	</table>

    		<p class="submit">
    			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    		</p>
	</form>
		
	<p>If you need any support go to the plugin homepage; http://wpable.com/wordpress-plugins/. Just leave a comment and i'll response.
<br />
If you want to support this plugin, please tweet about it, and tell others about it. Thanks.</p>
</div>

<?php } ?>
<?php
//Uses settings from the admin setting page in the plugin admin then checks if user is logged in or not... If is logged in then doesnt display anything else, adds google analytics code.

add_action('wp_footer', 'gaip_function');


function gaip_function() {
	if ( !is_user_logged_in() ) { ?>
		
	<script type="text/javascript">
	var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
	document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
	</script>
	<script type="text/javascript">
	try {
	var pageTracker = _gat._getTracker("<?php echo get_option('new_option_name'); ?>");
	pageTracker._trackPageview();
	} catch(err) {}</script>

<?php 
}
}
?>
