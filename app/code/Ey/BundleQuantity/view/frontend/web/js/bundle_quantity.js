requirejs([
    'jquery'
], function( $ ) {
    $(document).ready(function () {
        var body = $('body');

        $('.bundle-edit').addClass('active');
        body.on('click', '.bundle-edit-link', function (e) {
            e.preventDefault();

            var containerId = 'bundle-edit-qty-wrapper',
                container = "<div class='bundle-container'>" +
                    "<div class='bundle-overlay'></div>" +
                    "<div id='"+containerId+"'>" +
                    "<div class='inner'></div>" +
                    "<div class='bundle-close'><span>X</span></div>" +
                    "</div>" +
                    "</div>";

            if(!body.find('#'+containerId).length){
                body.append(container);
            }
            body.addClass('bundle-loader');
            $.ajax({
                type: 'GET',
                url: $(this).attr('href'),
                dataType: 'html',
                showLoader: true,
                context: body,
                success: function(data) {
                    body.removeClass('bundle-loader').addClass('bundle-finished');
                    $('#' + containerId).find('.inner').html(
                        $(data).find('#bundle-edit-qty-wrapper').html()
                    );
                    body.find('#bundle-edit-qty-wrapper').trigger('contentUpdated');
                    $('.bundle-overlay, .bundle-close').on('click', function(){
                        body.removeClass('bundle-finished');
                        $('#'+containerId).find('.inner').html('');
                    });
                }, error: function(xhr, ajaxOptions, thrownError){
                    body.removeClass('bundle-loader');
                    alert({
                        content: 'Invalid response. Please check the console.'
                    });
                    console.log(xhr.responseText);
                    console.log(thrownError);
                }
            });

            return false;
        });
    });
});