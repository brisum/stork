function windowResize() {
    $(window).trigger('resize');

    setTimeout(function () {
        $(window).trigger('resize');
    }, 500);
}

(function() {
    $(document).foundation();
}());
