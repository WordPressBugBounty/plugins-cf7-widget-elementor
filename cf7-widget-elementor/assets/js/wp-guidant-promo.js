(function ($) {
    function dismiss($notice, mode) {
        $notice.fadeOut(200);
        $.post(ajaxurl, {
            action: 'void_cf7_guidant_promo_dismiss',
            mode: mode,
            nonce: $notice.data('nonce')
        });
    }

    // "X" icon: hide for 1 day.
    $(document).on('click', '.void-cf7-guidant-promo__close', function (e) {
        e.preventDefault();
        dismiss($(this).closest('.void-cf7-guidant-promo'), 'day');
    });

    // "Never show again": hide for ever.
    $(document).on('click', '.void-cf7-guidant-promo__never', function (e) {
        e.preventDefault();
        dismiss($(this).closest('.void-cf7-guidant-promo'), 'never');
    });
})(jQuery);
