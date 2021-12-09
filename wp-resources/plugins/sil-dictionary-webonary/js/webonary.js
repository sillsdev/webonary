/**
 * @property gray_cover
 * @property media_light_box
 */
class Webonary {

    static showVideo(url) {
        Webonary.disablePage();
        jQuery('body').append(jQuery(Webonary.media_light_box));
        jQuery('div.w-modal video').prop('src', url);
        jQuery('div.w-modal').css('visibility', 'visible');
        return false;
    }

    static closeVideo() {
        jQuery('.w-modal, .w-close').remove();
        Webonary.enablePage();
    }

    static disablePage() {
        let cover = document.getElementById('gray-cover');
        if (!cover) {
            jQuery('body').append(jQuery(Webonary.gray_cover));
            cover = document.getElementById('gray-cover');
        }
        cover.style.display = 'inline-block';
    }

    static enablePage() {
        let cover = document.getElementById('gray-cover');
        if (!cover) return;
        cover.style.display = 'none';
    }
}

Webonary.gray_cover = '<div id="gray-cover" style="position:fixed;top:0;left:0;overflow:hidden;display:none;width:100%;height:100%;background-color:#000000;opacity:0.5;z-index:15000;filter:alpha(opacity=50);cursor: wait;"></div>';
Webonary.media_light_box = '<div class="w-modal" style="visibility: hidden"><video controls><source src="" type="">Sorry, your browser doesn\'t support embedded videos.</video></div><div class="w-close" onclick="Webonary.closeVideo();"><div>X</div></div>';
