
const gray_cover = '<div id="gray-cover" style="position:fixed;top:0;left:0;overflow:hidden;display:none;width:100%;height:100%;background-color:#000000;opacity:0.5;z-index:20000;filter:alpha(opacity=50);cursor: wait;"></div>';

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

function ShowCopyMessage(site, message_id) {

    let txt = document.getElementById('copy-data-progress');

    switch (message_id) {
        case 1:
            txt.value = 'Copying database record for ' + site + '.';
            break;

        case 2:
            txt.value += '\nCopying vernacular entries.';
            break;

        case 3:
            txt.value += '\nCreating vernacular indexes.';
            break;

        case 4:
            txt.value += '\nCopying reversal entries.';
            break;

        case 5:
            txt.value += '\nFinished copying.';
            break;

        default:
            txt.value += '\nUnknown command ' + message_id + '.';
    }

    txt.scrollTop = txt.scrollHeight;
}

function DoCopyAjax(site, step, callback) {

    // noinspection JSUnresolvedReference
    jQuery.ajax({
        url: webonary_ajax_obj.ajax_url,
        data: {action: 'postCopyMongoData', site: site, step: step},
        type: 'POST',
        dataType: 'json',
        success: function(response) {

            if (response.hasOwnProperty('error')) {
                toastr.error(response.error);
                return;
            }

            if (response.hasOwnProperty('msg'))
                document.getElementById('copy-data-progress').value += '\n   ...' + response.msg;

            if (callback)
                callback(site);
        },
        error: function(jqXHR, status, message) {
            let txt = document.getElementById('copy-data-progress');
            txt.value += '\n' + message;
        }
    });
}

function CopyMongoData() {

    let site = window.location.pathname
        .replace(/^\/+/, '')
        .replace(/\/+$/, '')
        .split('/')[0];

    ShowCopyMessage(site, 1);
    DoCopyAjax(site, 1, CopyDataStep2);
}

function CopyDataStep2(site) {

    ShowCopyMessage(site, 2);
    DoCopyAjax(site, 2, CopyDataStep3);
}

function CopyDataStep3(site) {

    ShowCopyMessage(site, 3);
    DoCopyAjax(site, 3, CopyDataStep4);
}

function CopyDataStep4(site) {

    ShowCopyMessage(site, 4);
    DoCopyAjax(site, 4, CopyDataFinished);
}

function CopyDataFinished(site) {

    ShowCopyMessage(site, 5);
    toastr.success('Finished copying data to .work');
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
// NOTE: these timers are no longer needed because this import method no longer works
//    window.setTimeout(GetCurrentImportedCount, 5000);
//    window.setTimeout(GetCurrentIndexedCount, 5000);

    jQuery(($) => {

        // after a term has been added via AJAX
        $(document).ajaxComplete(() => {
            jQuery('#hide_language.new-language').prop('checked', false);
        });
    });
});
