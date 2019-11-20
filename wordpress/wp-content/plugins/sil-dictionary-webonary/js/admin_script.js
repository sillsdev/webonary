
function GetCurrentIndexedCount() {

    var count_span = jQuery('#sil-count-indexed');
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

            var indexed = data['indexed'];
            if (indexed === 0)
                window.location.reload();
console.log(data);
            count_span.html(indexed.toString());
            var percent = Math.ceil((indexed / data['total']) * 100);
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

    var count_span = jQuery('#sil-count-imported');
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

            var imported = data['imported'];
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

document.addEventListener('DOMContentLoaded', function() {

    // start the count updater
    window.setTimeout(GetCurrentImportedCount, 5000);
    window.setTimeout(GetCurrentIndexedCount, 5000);
});
