
function confirmDeleteApplication(language) {
    return confirm('Are you sure you want to delete the ' + language + ' site application?');
}


function postNewSite() {

    console.log('postNewSite');

    // webonary_ajax_obj is added by WordPress `wp_localize_script()`
    // noinspection JSUnresolvedReference
    const url = webonary_ajax_obj.ajax_url;
    const x_headers = new Headers();
    x_headers.append('X-Requested-With', 'XMLHttpRequest');
    x_headers.append('Content-type', 'application/x-www-form-urlencoded');
    const form_data = new FormData(document.getElementById('wcs2-configuration-form'));


    const request = new Request(url, {
        method: 'POST',
        headers: x_headers,
        body: new URLSearchParams([...form_data])
    });

    fetch(request).then();


}
