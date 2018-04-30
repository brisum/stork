(function () {
    if (!$('body').hasClass('page-home')) {
        return;
    }

    var $window = $(window),
        $header = $('#header'),
        $section1 = $('#section-1'),
        rate = 1920 / 1080,
        resizeTimer;

    function resize() {
        var wWidth = $(window).width(),
            wHeight = $(window).height(),
            headerHeight = $header.outerHeight();

        $section1.height(Math.max(500, wHeight - headerHeight));

        var sectionImageRate = $section1.width() / ($section1.height() + headerHeight);
        if (rate <= sectionImageRate) {
            if ($section1.hasClass('portrait')) {
                $section1.removeClass('portrait').addClass('landscape');
            }
        } else {
            if ($section1.hasClass('landscape')) {
                $section1.removeClass('landscape').addClass('portrait');
            }
        }

        console.log(wWidth, wHeight, $header.height(), headerHeight, rate, sectionImageRate);
    }

    $window.resize(function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(resize, 100);
    });
    resize();

}());

// dsd2sdf