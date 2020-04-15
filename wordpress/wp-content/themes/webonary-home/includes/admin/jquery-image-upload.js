jQuery(document).ready(function($) {
	jQuery('.zee-upload-image-button').click(function() {
        inputID = $(this).prev().attr('id');
		formfield = jQuery('#'+inputID).attr('name');
        tbframe_interval = setInterval(function() {jQuery('#TB_iframeContent').contents().find('.savesend .button').val(zee_localizing_upload_js.use_this_image);}, 1000);
		tb_show('', 'media-upload.php?type=image&TB_iframe=true');
		
		window.send_to_editor = function(html) {
			imgurl = jQuery('img',html).attr('src');
			jQuery('#'+inputID).val(imgurl);
			jQuery('#'+inputID+'img').attr("src",imgurl);
			tb_remove();
		}
        return false;
    });
});