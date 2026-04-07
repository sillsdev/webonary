
document.addEventListener('DOMContentLoaded', () => {

    // Make sure sprintf is available
    window.setTimeout(() => {

        if (typeof window.sprintf === 'undefined' && wp.i18n && typeof wp.i18n.sprintf === 'function')
            window.sprintf = wp.i18n.sprintf;

    }, 500);
});

