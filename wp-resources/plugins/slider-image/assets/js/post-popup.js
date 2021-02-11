jQuery(document).ready(function() {
	jQuery('#hugeit_slider_insert_slider_to_post').on('click', function() {
		if ((window.parent.tinyMCE || window.parent.tinyMCE.activeEditor) && jQuery.isNumeric(jQuery(this).siblings('select').val())) {
			window.parent.send_to_editor('[huge_it_slider id="' + jQuery(this).siblings('select').val() + '"]');
		}
	});
});