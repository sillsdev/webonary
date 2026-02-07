
// noinspection JSUnusedGlobalSymbols
class WebonaryAdmin {

    static GetQueryStringPairs() {

        let qs = (window.location.search ? window.location.search.substring(1) : '');
        if (!qs)
            return {};

        let parts = qs.split('&');
        let return_val = {};
        for (let i = 0; i < parts.length; i++) {
            let kvp = parts[i].split('=', 2);
            if (kvp.length === 1) {
                return_val[kvp[0]] = 1;
            }
            else {
                let cleaned = decodeURIComponent(kvp[1].replace(/\+/g, ' '));
                if (kvp[0].endsWith('[]')) {
                    let key = kvp[0].slice(0, -2);
                    if (return_val.hasOwnProperty(key))
                        return_val[key].push(cleaned);
                    else
                        return_val[key] = [cleaned];
                }
                else {
                    return_val[kvp[0]] = cleaned;
                }
            }
        }

        return return_val;
    }

    static CombineQueryStringPairs(pairs) {

        let values = [];
        for (let key in pairs) {
            if (pairs.hasOwnProperty(key))
                values.push(key + '=' + encodeURIComponent(pairs[key].toString()).replace(/%20/g, '+'));
        }

        let return_val = values.join('&');
        if (return_val.length === 0)
            return '';
        return '?' + return_val;
    }

    static ExportReport() {

        let frame = document.getElementById('file-download');
        if (!frame) {
            let e = document.createElement('iframe');
            e.id = 'file-download';
            e.style.display = 'none';
            document.body.append(e);

            frame = document.getElementById('file-download');
        }

        let query_pairs = WebonaryAdmin.GetQueryStringPairs();
        query_pairs.action = 'getReportExcel';
        query_pairs.excel = '1';

        // noinspection JSUnresolvedReference
        frame.src = webonary_ajax_obj.ajax_url + WebonaryAdmin.CombineQueryStringPairs(query_pairs);
    }

    static SelectChanged(select, btn_id) {
        document.getElementById(btn_id).disabled = select.selectedIndex < 1
    }
}
