$(function () {
    $('.profiles').on('click', '[data-add-nb-paiement]', function (e) {
        e.preventDefault();
        var tr = $(this).parents('.profile');
        $.ajax({
            url: Routing.generate('APP_NB_PAIEMENT', {
                identifier: tr.data('identifier')
            }),
            method: 'GET'
        }).done(function (data) {
            if (!data.success) {
                return;
            }
            tr.replaceWith(data.html);
        })
    })
    $('.profiles').on('click', '[data-reset-nb-paiement]', function (e) {
        e.preventDefault();
        var tr = $(this).parents('.profile');
        $.ajax({
            url: Routing.generate('RESET_NB_PAIEMENT', {
                identifier: tr.data('identifier')
            }),
            method: 'GET'
        }).done(function (data) {
            if (!data.success) {
                return;
            }
            tr.replaceWith(data.html);
        })
    })
})
