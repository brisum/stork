;(function ($) {
    var CommonHeight = function ($context, options) {
        this.$context = $context;
        this.elements = [];
        this.isDisabled = false;

        this.settings = $.extend(
            this.defaultSettings,
            {
                element: '[data-common-height-element]',
                disableWidth: parseInt(this.$context.attr('data-disable-width'))
            },
            options || {}
        );

        var self = this;

        self.init();
        self.onResize();
        $(window).resize(function () {
            self.onResize();
        });
    };

    CommonHeight.prototype.groups = {};

    CommonHeight.prototype.defaultSettings = {
        disableWidth: 450
    };

    CommonHeight.prototype.init = function () {
        var self = this;

        self.$context.find(self.settings.element).each(function (i, element) {
            self.elements.push($(element));
        });

        console.log(self);
    };

    CommonHeight.prototype.onResize = function() {
        var self= this,
            maxHeight = 0,
            height = 0,
            isDisabledWidth = $(window).width() <= self.settings.disableWidth;

        // optimization
        if (isDisabledWidth && self.isDisabled) {
            return;
        }

        $.each(self.elements, function (i, $element) {
            $element.height('auto');
            height = $element.height();
            if (height > maxHeight) {
                maxHeight = height;
            }
        });
        $.each(self.elements, function (i, $element) {
            if (!isDisabledWidth) {
                $element.height(maxHeight);
            }
        });

        // optimization
        self.isDisabled = isDisabledWidth;
    };

    window.bsm = window.bsm || {};
    window.bsm.CommonHeight = CommonHeight;

    $.fn.commonHeight = function(options) {
        return this.each(function() {
            new CommonHeight($(this), options);
        });
    };

    $(document).ready(function () {
        $('[data-common-height]').commonHeight();
    });
}( jQuery ));
