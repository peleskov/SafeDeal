$(document).ready(() => {
    /* Обновить страницу при закрытии попап */
    $('.js-hidden-refresh').on('hidden.bs.modal', function (e) {
        location.href = location.href;
    })
    /* Обновить страницу при закрытии попап */
    /* Расчет комиссии при создании сделки */
    $('.js-dealprice').on('change keyup', (e) => {
        $('.js-dealfee').text(($(e.target).val().replace(/,/g, '') * $(e.target).data('fee')).toLocaleString());
    });
    /* Расчет комиссии при создании сделки */
    /* Блокировка кнопки submit без согласия */
    $('.js-iagreecheck').on('change', (e) => {
        let form
        if ($(e.target).data('form')) {
            form = $(e.target).data('form')
        } else {
            form = $(e.target).closest('form')
        }
        $(form).find('[type="submit"]').removeAttr('disabled')
        if ($(e.target).data('parent')) {
            $($(e.target).data('parent')).find('.js-iagreecheck').each((i, el) => {
                if (!$(el).is(':checked')) {
                    $(form).find('[type="submit"]').attr('disabled', '')
                }
            })
        } else {
            if ($(e.target).is(':checked')) {
                $(form).find('[type="submit"]').removeAttr('disabled')
            } else {
                $(form).find('[type="submit"]').attr('disabled', '')
            }
        }
    })
    /* Блокировка кнопки submit без согласия */

});

(function () {

    const result = {

        init: function () {

            this.eventSubscription()

        },

        eventSubscription: function () {

            $(document).on('af_complete', $.proxy(this.eventAfComplete, this))
        },

        eventAfComplete: function (event, response) {
            if ('service' in response.data && response.data.service == 'safedeal') {
                this.cleanDOM(response)
                this.offLibraries(response)
                this.getService(response)
            }
        },

        cleanDOM: function (response) {

            $(response.form).find('.is-invalid').removeClass('is-invalid')
            $(response.form).find('.invalid-feedback').remove()
            $(response.form).find('.alert').hide()

        },

        offLibraries: function (response) {

            response.message = '';

        },

        getService: function (response) {
            let modalID, alertClass
            let alert = response.form.find('.alert')

            if ('modalID' in response.data) {
                modalID = response.data.modalID
            }

            if (response.data.result) {
                if ('location' in response.data) {
                    window.location = response.data.location
                }
                alertClass = 'alert-success'
            } else {
                alertClass = 'alert-danger'
                $.each(response.data.errors, (i, msg) => {
                    response.form.find('[name="' + i + '"]')
                        .addClass('is-invalid')
                        .after($('<span class="invalid-feedback">' + msg + '</span>'))
                    if (response.form.attr('id')) {
                        $('body').find('[form="' + response.form.attr('id') + '"][name="' + i + '"]')
                            .addClass('is-invalid')
                            .after($('<span class="invalid-feedback">' + msg + '</span>'))
                    }
                })
            }
            if (modalID) {
                $('.modal').modal('hide')
                if ('hash_link' in response.data && $('.modal').find('#DealLink').length > 0) {
                    $('.modal').find('#DealLink').val(response.data.hash_link)
                }
                $('#' + modalID).modal('show')
            }
            if (alert.length > 0 && response.data.message) {
                alert.show().attr('class', alert.attr('class').replace(/\balert-\w*\b/g, '')).addClass(alertClass).text(response.data.message)
            }
        }
    }

    result.init()

})()