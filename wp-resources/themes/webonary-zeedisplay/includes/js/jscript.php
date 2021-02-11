<?php 
	
add_action('wp_head', 'themezee_include_jscript');
function themezee_include_jscript() {

	// Select Slider Modus
	$options = get_option('themezee_options');
	if(isset($options['themeZee_show_slider']) and $options['themeZee_show_slider'] == 'true') {
		switch($options['themeZee_slider_mode']) {
			case 0:
				$return = "<script type=\"text/javascript\">
				//<![CDATA[
					// Horizontal Slider
					jQuery(document).ready(function($) {
						$('#slideshow')
							.cycle({
							fx: 'scrollHorz',
							next:   '#slide_next', 
							prev:   '#slide_prev'
						});
					});
				//]]>
				</script>";

			break;
			case 1:
				$return = "<script type=\"text/javascript\">
				//<![CDATA[
					// Dropdown Slider
					jQuery(document).ready(function($) {
						$('#slideshow')
							.cycle({
							fx:     'scrollVert',
							next:   '#slide_next', 
							prev:   '#slide_prev'
						});
					});
				//]]>
				</script>";

			break;
			case 2:
				$return = "<script type=\"text/javascript\">
				//<![CDATA[
					// Fade Slider
					jQuery(document).ready(function($) {
						$('#slideshow')
							.cycle({
							fx: 'fade',
							speed: 'slow',
							next:   '#slide_next', 
							prev:   '#slide_prev'
						});
					});
				//]]>
				</script>";

			break;
			default:
				$return = "<script type=\"text/javascript\">
				//<![CDATA[
					// Horizontal Slider
					jQuery(document).ready(function($) {
						$('#slideshow')
							.cycle({
							fx: 'scrollHorz',
							next:   '#slide_next', 
							prev:   '#slide_prev'
						});
					});
				//]]>
				</script>";
			break;
		}
		echo $return;
	}
}
?>