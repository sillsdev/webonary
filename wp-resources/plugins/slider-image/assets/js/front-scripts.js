jQuery(document).ready(function() {
	jQuery('.hugeit-slider-nav-dot').on('click', function() {
		var sliderId = jQuery('#hugeit_slider_main_container').data('slider-id')

		if (jQuery(this).hasClass('huge_it_slideshow_dots_active_' + sliderId)) {
			return false;
		}
	})
});