class DatatablesWebonary {

    static createDataTable(table_id, url, columns, column_defs, order, $button) {

        let layout = {
            topEnd: {
                search: {
                    placeholder: 'Search'
                }
            }
        };

        if ($button)
            layout['topStart'] = $button;

        let options = {
            ajax: url,
            paging: false,
            sScrollY: 'auto',
            scrollY: false,
            sScrollX: '100%',
            scrollX: true,
            ordering: true,
            select: {style: 'single'},
            layout: layout,
            initComplete: function() {
                DatatablesWebonary.setTableHeight(table_id);
            }
        };

        if (columns)
            options['columns'] = columns;

        if (column_defs)
            options['columnDefs'] = column_defs;

        if (order)
            options['order'] = order;

        new DataTable('#' + table_id, options);
    }

    static setTableHeight(table_id) {

        let container = $('#' + table_id).closest('.dt-scroll');
        let tbody = container.find('.dt-scroll-body');
        let offset = 190; // tbody.offset().top + 1;

        let card = tbody.closest('#table-container-div');
        let padding = parseInt(card.css('padding-bottom'));
        if (padding)
            offset += padding;

        let paginate = $('#list-table_paginate');
        if (paginate.length)
            offset += paginate.outerHeight(true) + 2;

        let filter = $('div.list-table-filter');
        if (filter.length) {

            if (!paginate.length) {
                offset += filter.outerHeight(true) + 2;
            }
        }

        let footer = container.find('.dt-scroll-foot');
        if (footer.length)
            offset += footer.outerHeight(true) + 2;

        tbody.css('max-height', 'calc(100vh - ' + (offset + 30).toString() + 'px)');
    }
}
