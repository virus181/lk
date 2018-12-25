// Перерасчет данных после изменения габаритов
window.dimensionsReCalculation = function () {
    var orderId = params['id'],
        deliveryBlock = '#deliveries-for-dimensions',
        calculateBlock = '#calculate-for-dimension',
        serializeForm = $('.dimension form').serialize();
    $(calculateBlock).show();
    $.post('/order/' + orderId + '/calculate', serializeForm).done(function (carriers) {
        $(calculateBlock).hide();
        if (carriers.length) {
            $(deliveryBlock).html(carriers).show();
            window.afterAjaxLoading();
        } else {
            $('.alert-block').show();
        }
    });
};

$(body).on('change', '.dimension-form input', function () {
    window.dimensionsReCalculation();
});

$(body).on('click', '.show-deliveries', function () {
    $('#previous-delivery').remove();
    $('#delivery-calculated-deliveries').removeClass('hidden').fadeIn();
});

// Обновление габаритов и доставки
$(body).on('click', '#update-dimensions', function () {
    var form = $('.dimension form').serialize();
    $.ajax({
        type: 'POST',
        url: 'update-dimensions',
        data: form,
        dataType: "json",
        beforeSend: function () {
            $('.modal-content').addClass('pjax-loading');
        },
        success: function (data) {
            if (data.success) {
                $('#set-next-status').trigger('click');
            }
        }
    });
});

// Применить выбранную доставку
$(body).on('click', '.apply-dimension-delivery', function () {
    var form = $('.dimension form').serialize();
    form += "&uid=" + $(this).attr('uid');
    form += "&OrderDelivery[cost]=185";
    $.ajax({
        type: 'POST',
        url: 'apply-dimension-delivery',
        data: form,
        dataType: "text",
        beforeSend: function () {
            $('.modal-content').addClass('pjax-loading');
        },
        success: function (responseData) {
            $('#order-dimension-delivery').html(responseData);
            $('#deliveries-for-dimensions').addClass('hidden');

            $('.modal-content').removeClass('pjax-loading');
        }
    });
});