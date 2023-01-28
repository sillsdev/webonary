(function () {
    wp.customize('header_bg_color', function (value) {
        value.bind(function (new_val) {
            document.documentElement.style.setProperty('--webonary-' + value.id, new_val);
        });
    });

    wp.customize('header_text_color', function (value) {
        value.bind(function (new_val) {
            document.documentElement.style.setProperty('--webonary-' + value.id, new_val);
        });
    });

    wp.customize('footer_bg_color', function (value) {
        value.bind(function (new_val) {
            document.documentElement.style.setProperty('--webonary-' + value.id, new_val);
        });
    });

    wp.customize('footer_text_color', function (value) {
        value.bind(function (new_val) {
            document.documentElement.style.setProperty('--webonary-' + value.id, new_val);
        });
    });

    wp.customize('highlight_bg_color', function (value) {
        value.bind(function (new_val) {
            document.documentElement.style.setProperty('--webonary-' + value.id, new_val);
        });
    });

    wp.customize('highlight_text_color', function (value) {
        value.bind(function (new_val) {
            document.documentElement.style.setProperty('--webonary-' + value.id, new_val);
        });
    });
}());
