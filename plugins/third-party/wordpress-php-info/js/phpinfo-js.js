(function($) {
    $(document).ready( function() {
        var copyPhpInfoBtn = $("#copy-php-info");
        copyPhpInfoBtn.on('click', function(event) {
            var copyTextarea =  $("#php-info-hidden");
            copyTextarea.select();

            try {
                var successful = document.execCommand('copy');
                var msg = successful ? 'successful' : 'unsuccessful';
                console.log('Copying text command was ' + msg);
            } catch (err) {
                console.log('Oops, unable to copy');
            }
        });
    });
})(jQuery);