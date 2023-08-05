window.onload = function () {
    if (window.jQuery) {
        (function ($) {
            $.fn.button = function (action) {
                if (this.length > 0) {
                    this.each(function () {
                        if (action === 'loading' && $(this).data('loading-text')) {
                            $(this).data('original-text', $(this).html()).html($(this).data('loading-text')).prop('disabled', true);
                        } else if (action === 'reset' && $(this).data('original-text')) {
                            $(this).html($(this).data('original-text')).prop('disabled', false);
                        }
                    });
                }
            };
        }(jQuery));
    }
}
