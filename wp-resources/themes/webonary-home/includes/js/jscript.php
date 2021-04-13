<?php 

// Includes Javascripts for Navigation Menu (pluggable)
add_action('wp_head', 'themezee_jscript_navi_menus');

if ( ! function_exists( 'themezee_jscript_navi_menus' ) ) :
function themezee_jscript_navi_menus() {

	/* Available Slide Menu Effects
		show - show(500) 
		slide - slideDown(500)
		fade - show().css({opacity:0}).animate({opacity:1},500)
		diagonal - animate({width:'show',height:'show'},500)
		left - animate({width:'show'},500)
		slidefade - animate({height:'show',opacity:1})
	*/
	
	// Set Javascript for Navigation Menus
	$jscript = "<script type=\"text/javascript\">
				//<![CDATA[
					jQuery(document).ready(function($) {
						$('#nav ul').css({display: 'none'}); // Opera Fix
						$('#nav li').hover(function(){
							$(this).find('ul:first').css({visibility: 'visible',display: 'none'}).slideDown(350);
						},function(){
							$(this).find('ul:first').css({visibility: 'hidden'});
						});
						
						$('#topnav ul').css({display: 'none'}); // Opera Fix
						$('#topnav li').hover(function(){
							$(this).find('ul:first').css({visibility: 'visible',display: 'none'}).slideDown(350);
						},function(){
							$(this).find('ul:first').css({visibility: 'hidden'});
						});
					});
				//]]>
				</script>";
	
	echo $jscript; // Print Javascript
}
endif;


// Includes Javascripts for Featured Post Slider (pluggable)
add_action('wp_head', 'themezee_jscript_post_slider');

if ( ! function_exists( 'themezee_jscript_post_slider' ) ) :
function themezee_jscript_post_slider() {

	/* Slideshow is based on the malsup cycle plugin 
	Learn more about all possible slideshow settings here: 
	http://jquery.malsup.com/cycle/ */
	
	$jscript = ''; // Declare Variable to prevent WP debug errors
	
	// Check if Featured Post Slider is activated
	$options = get_option('themezee_options');
	if(isset($options['themeZee_show_slider']) and $options['themeZee_show_slider'] == 'true') :
	
		// Select Post Slider Mode
		switch($options['themeZee_slider_mode']) {
			case 0:
				// Horizontal Slider
				$jscript = "<script type=\"text/javascript\">
				//<![CDATA[
					jQuery(document).ready(function($) {
						$('#slideshow')
							.cycle({
							fx: 'scrollHorz',
							speed: 1000,
							timeout: 10000,
							next: '#slide_next',
							prev: '#slide_prev'
						});
					});
				//]]>
				</script>";

			break;
			case 1:
				// Dropdown Slider
				$jscript = "<script type=\"text/javascript\">
				//<![CDATA[
					jQuery(document).ready(function($) {
						$('#slideshow')
							.cycle({
							fx:     'scrollVert',
							speed: 1000,
							timeout: 10000,
							next: '#slide_next',
							prev: '#slide_prev'
						});
					});
				//]]>
				</script>";

			break;
			case 2:
				// Fade Slider
				$jscript = "<script type=\"text/javascript\">
				//<![CDATA[
					jQuery(document).ready(function($) {
						$('#slideshow')
							.cycle({
							fx: 'fade',
							speed: 600,
							timeout: 10000,
							next: '#slide_next',
							prev: '#slide_prev'
						});
					});
				//]]>
				</script>";

			break;
			default:
				// Default Slider: Horizontal
				$jscript = "<script type=\"text/javascript\">
				//<![CDATA[
					jQuery(document).ready(function($) {
						$('#slideshow')
							.cycle({
							fx: 'scrollHorz',
							speed: 1000,
							timeout: 10000,
							next: '#slide_next',
							prev: '#slide_prev'
						});
					});
				//]]>
				</script>";
			break;
		}
	endif;
	
	echo $jscript; // Print Javascript
}
endif;

?>