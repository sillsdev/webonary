
let previous_count = 0;
let same_count = 0;

function GetCurrentIndexedCount() {

    const count_span = jQuery('#sil-count-indexed');
    if (count_span.length === 0)
        return;

    // webonary_ajax_obj is added by wordpress `wp_localize_script()`
    // noinspection JSUnresolvedVariable
    jQuery.ajax({
        url: webonary_ajax_obj['ajax_url'],
        data: {action: 'getAjaxCurrentIndexedCount'},
        type: 'POST',
        dataType: 'json',
        success: function(data) {

            const indexed = data['indexed'];
            const total = data['total'];

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

    // webonary_ajax_obj is added by wordpress `wp_localize_script()`
    // noinspection JSUnresolvedVariable
    jQuery.ajax({
        url: webonary_ajax_obj['ajax_url'],
        data: {action: 'getAjaxCurrentImportedCount'},
        type: 'POST',
        dataType: 'json',
        success: function(data) {

            const imported = data['imported'];
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

    // webonary_ajax_obj is added by wordpress `wp_localize_script()`
    // noinspection JSUnresolvedVariable
    jQuery.ajax({
        url: webonary_ajax_obj['ajax_url'],
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
