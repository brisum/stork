(function ($) {
    var $widget = $('#contact-widget'),
        animationCount = 4,
        animationTimeout = 5000;

    if (!$widget.length) {
        return;
    }

    function show() {
        $widget.animate({'bottom': '15px'});
    }

    function activate(method) {
        $widget.find('.method').removeClass('active');
        $widget.find('.message').removeClass('active');

        $widget.find('.method[data-method="' + method + '"]').addClass('active');
        $widget.find('.message[data-method="' + method + '"]').addClass('active');
    }
    
    function animation(method) {
        if (!animationCount) {
            return;
        }

        activate(method);
        animationCount -= 1;
        animationTimeout += 2000;

        if ('feedback' === method) {
            method = 'callback';
        } else if ('callback' === method) {
            method = 'feedback';
        }

        setTimeout(function () {
            animation(method)
        }, animationTimeout);
    }

    $widget.on('mouseover', '.method', function () {
        animationCount = 0;
        activate($(this).attr('data-method'));
    });

    $widget.on('click', '.method', function () {
        var method = $(this).attr('data-method'),
            isOpened = $widget.find('.form-wrapper.active[data-method="' + method + '"]').length;

        $widget.find('.form-wrapper.active').removeClass('active').slideUp();

        if (isOpened) {
            return;
        }

        $widget.find('.form-wrapper[data-method="' + method + '"]').addClass('active').slideDown();
    });

    $widget.on('click', '.form-wrapper .close', function () {
        $(this).closest('.form-wrapper').removeClass('active').slideUp();
    });

    setTimeout(function () {
        animation('feedback');
        show();
    }, 5000);
})(jQuery);
