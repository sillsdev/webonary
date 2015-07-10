<?php
/**
 * Link style sheets. Called by header.php
 * See: http://themeshaper.com/2009/04/30/modular-css-wordpress-child-themes/
 */
function webonary_zeedisplay_link_style_sheets() {
	$templatedir = get_bloginfo('template_directory');
	$stylesheetdir = get_bloginfo('stylesheet_directory');

	$options = get_option('themezee_options');
	//if(isset($options['themeZee_blog_mode']) and $options['themeZee_blog_mode'] == DICTIONARY_MODE) {
		?>
		<!-- Styles that came with the XHTML file that was imported -->
<? $upload_dir = wp_upload_dir(); ?>
		<link rel="stylesheet" type="text/css" href="<? echo $upload_dir['baseurl']; ?>/imported-with-xhtml.css<?php echo '?'.mt_rand(); ?>" />
		<?php
	//} ?>

	<!-- Any styles specific to the language. Note that this particular theme has
		the ability to have custom css pasted into its options. So this hook is
		really not needed. It's just here if someone wants to use it. -->
   
	<?php
}
add_filter('webonary_zeedisplay_style_sheets', 'webonary_zeedisplay_link_style_sheets');
?>
