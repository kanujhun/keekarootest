define([
    'jquery'
], function( $ ) {
    return function(config, node) {
        $(document).ready(function () {
            $(node).on('click', '.bundle-qty-actions button.primary', function (e) {
                $(this).prop('disabled', true).addClass('disabled');
                $(this).find('span').text('Updating...');
                $(node).submit();
            });
        });
    };
});