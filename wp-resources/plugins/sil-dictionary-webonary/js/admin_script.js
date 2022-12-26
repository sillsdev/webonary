
let previous_count = 0;
let same_count = 0;
const gray_cover = '<div id="gray-cover" style="position:fixed;top:0;left:0;overflow:hidden;display:none;width:100%;height:100%;background-color:#000000;opacity:0.5;z-index:20000;filter:alpha(opacity=50);cursor: wait;"></div>';

function GetCurrentIndexedCount() {

    const count_span = jQuery('#sil-count-indexed');
    if (count_span.length === 0)
        return;

    // webonary_ajax_obj is added by WordPress `wp_localize_script()`
    // noinspection JSUnresolvedVariable
    jQuery.ajax({
        url: webonary_ajax_obj.ajax_url,
        data: {action: 'getAjaxCurrentIndexedCount'},
        type: 'POST',
        dataType: 'json',
        success: function(data) {

            const indexed = data.indexed;
            const total = data.total;

            if (indexed >= total)
                window.location.reload();

            if (indexed !== previous_count) {
                previous_count = indexed;
                same_count = 0;
                jQuery('#timed-out-msg').hide();
            }
            else {
                same_count++;

                if (same_count > 5) {
                    jQuery('#timed-out-msg').show();
                }
            }

            count_span.html(indexed.toString());
            let percent = Math.ceil((indexed / total) * 100);
            if (percent > 100)
                percent = 100;

            jQuery('#sil-index-progress').val(percent.toString());
            window.setTimeout(GetCurrentIndexedCount, 5000);
        },
        error: function(jqXHR, status, message) {
            console.log(message);
        }
    });
}

function GetCurrentImportedCount() {

    const count_span = jQuery('#sil-count-imported');
    if (count_span.length === 0)
        return;

    // webonary_ajax_obj is added by WordPress `wp_localize_script()`
    // noinspection JSUnresolvedVariable
    jQuery.ajax({
        url: webonary_ajax_obj.ajax_url,
        data: {action: 'getAjaxCurrentImportedCount'},
        type: 'POST',
        dataType: 'json',
        success: function(data) {

            const imported = data.imported;
            if (imported < 0)
                window.location.reload();
            else
                count_span.html(imported.toString());

            window.setTimeout(GetCurrentImportedCount, 5000);
        },
        error: function(jqXHR, status, message) {
            console.log(message);
        }
    });
}

function RestartIndexing() {

    // webonary_ajax_obj is added by WordPress `wp_localize_script()`
    // noinspection JSUnresolvedVariable
    jQuery.ajax({
        url: webonary_ajax_obj.ajax_url,
        data: {action: 'getAjaxRestartIndexing'},
        type: 'POST',
        dataType: 'json',
        success: function(data) {
            console.log(data);
        },
        error: function(jqXHR, status, message) {
            console.log(message);
        }
    });
}

function DisablePage() {

    let cover = document.getElementById('gray-cover');
    if (!cover) {
        jQuery('body').append(jQuery(gray_cover));
        cover = document.getElementById('gray-cover');
    }
    cover.style.display = 'inline-block';
}

function EnablePage() {

    let cover = document.getElementById('gray-cover');
    if (cover)
        cover.style.display = 'none';
}

/**
 * Displays a WordPress Admin notice to the user
 *
 * @param msg_text
 * @param msgClass
 */
function ShowDeleteMessage(msg_text, msgClass) {

    // styled div that contains the message
    let msg_div = document.createElement('div');
    msg_div.classList.add('notice', 'is-dismissible', msgClass);

    // the message
    let msg_p = document.createElement('p');
    msg_p.innerHTML = msg_text;
    msg_div.appendChild(msg_p);

    // button to dismiss the message
    let btn = document.createElement('button');
    btn.setAttribute('type', 'button');
    btn.onclick = function() {
        this.parentElement.remove();
    };
    btn.classList.add('notice-dismiss');
    btn.innerHTML = '<span class="screen-reader-text">Dismiss this notice.</span>';
    msg_div.appendChild(btn);

    document.getElementById('webonary-delete-msg').appendChild(msg_div);
}

function DeleteWebonaryData() {

    const pwd_box = document.getElementById('user_pass');
    let password = '';

    // password required for cloud backend
    if (pwd_box) {

        password = pwd_box.value.trim();

        if (!password) {
            alert(document.getElementById('pwd-required-text').value);
            return;
        }
    }

    const confirm_text = document.getElementById('confirm-delete-text').value;
    if (!window.confirm(confirm_text))
        return;

    DisablePage();

    // webonary_ajax_obj is added by WordPress `wp_localize_script()`
    // noinspection JSUnresolvedVariable
    jQuery.ajax({
        url: webonary_ajax_obj.ajax_url,
        data: {action: 'postAjaxDeleteData', pwd: password},
        type: 'POST',
        dataType: 'json',
        success: function(data) {

            // noinspection JSUnresolvedVariable
            if (data.deleted === 0)
                ShowDeleteMessage(data.msg, 'notice-warning');
            else
                ShowDeleteMessage(data.msg, 'notice-success');
        },
        error: function(jqXHR, status, message) {
            console.log(message);
            ShowDeleteMessage(message, 'notice-warning');
        },
        complete: function() {
            EnablePage();
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {

    // site-address must be lowercase, and replace underscore with hyphen
    let site_address = document.getElementById('site-address');
    if (site_address) {
        document.getElementById('site-address').onkeyup = function(evt) {

            // skip if it is not a printable character key
            let char = evt.key || '';
            if (char.length !== 1)
                return;

            let start = this.selectionStart;
            let end = this.selectionEnd;
            this.value = this.value.toLowerCase().replace(/_/g, '-');
            this.setSelectionRange(start, end);
        };
    }

    // start the count updater
    window.setTimeout(GetCurrentImportedCount, 5000);
    window.setTimeout(GetCurrentIndexedCount, 5000);
});
