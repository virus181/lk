/* $('[data-toggle="tooltip"]').tooltip({
    placement: "bottom",
    trigger: "focus"
}); */

$('input[name="Shop[deliveries][]"]').change(function () {
    if(this.checked) {
        $(this).parents('.quote').removeClass('unchecked-row');
    } else {
        $(this).parents('.quote').addClass('unchecked-row');
    }
});

$('body').delegate('.remove-rate', 'click', function(e) {
    var id = $(this).data('rate-id');
    e.preventDefault();
    swal({
        title: "Вы дейстивтельно хотите удалить СД?",
        text: "Данные будут удалены без вовзратно.",
        confirmButtonText: 'Подтвердить',
        cancelButtonText: 'Отменить',
        confirmButtonColor: "#5cb85c",
        showCancelButton: true,
        closeOnConfirm: false,
        showLoaderOnConfirm: true
    }, function () {
        $.ajax({
            'url': '/rate/delete',
            data: {rateId: id},
            dataType: 'json',
            type: 'POST',
            success: function (data) {
                if (data.success) {
                    swal({
                        title: "Доставка успешно удалена."
                    }, function () {
                        location.reload();
                    });
                } else {
                    swal({
                        title: "Ошибка, доставка не была удалена."
                    }, function () {
                        location.reload();
                    });
                }
            },
            error: function (data) {
                // TODO вывести ошибки
            }
        });
    });
});

$(document).ready(function (jQueryAlias) {
    var rateUrl = '/rate/index';
    $.get(rateUrl + '?shopId=' + params.id, function (data) {
        $('.courier-service').html(data);
    });

    // Автоподстановка адреса
    new autoComplete({
        selector: '#full_address',
        source: function (term, response) {
            try {
                xhr.abort();
            } catch (e) {
            }
            xhr = $.getJSON('/api/address/full', {q: term}, function (data) {
                response(data);
            });
        },
        renderItem: function (item, search) {
            search = search.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
            var re = new RegExp("(" + search.split(' ').join('|') + ")", "gi");

            var city = item['data']['cityWithType'];
            var cityFiasId = item['data']['cityFiasId'];
            var street = item['data']['streetWithType'];
            var streetFiasId = item['data']['streetFiasId'];

            if (!street) {
                street = 'Адрес без улицы';
                streetFiasId = '-';
            }

            if (!item['data']['cityWithType'] && item['data']['settlementWithType']) {
                city = item['data']['settlementWithType'];
                cityFiasId = item['data']['settlementFiasId'];
            } else if (item['data']['cityWithType'] && item['data']['settlementWithType'] && (
                    item['data']['settlementType'] !== 'мкр'
                    && item['data']['settlementType'] !== 'тер'
                    && item['data']['settlementType'] !== 'р-н'
                )) {
                city = item['data']['settlementWithType'];
                cityFiasId = item['data']['settlementFiasId'];
            }

            return '<div class="autocomplete-suggestion" ' +
                'data-region="' + item['data']['regionWithType'] + '"' +
                'data-region-fias-id="' + item['data']['regionFiasId'] + '"' +
                'data-city="' + city + '"' +
                'data-city-fias-id="' + cityFiasId + '"' +
                'data-street="' + street + '"' +
                'data-street-fias-id="' + streetFiasId + '"' +
                'data-house="' + (item['data']['house'] || '') + '"' +
                'data-flat="' + (item['data']['flat'] || '') + '"' +
                'data-housing="' + (item['data']['block'] || '') + '"' +
                'data-postcode="' + (item['data']['postalCode'] || '') + '"' +
                'data-val="' + item['value'] + '">' + item['value'].replace(re, "<b>$1</b>") + '</div>';
        },
        onSelect: function (e, term, item) {
            var cityInput = $('[name="Address[city]"]');
            var oldCity = cityInput.val();
            cityInput.val(item.getAttribute('data-city'));

            $('[name="Address[region]"]').val(item.getAttribute('data-region'));
            $('[name="Address[region_fias_id]"]').val(item.getAttribute('data-region-fias-id'));
            $('[name="Address[city_fias_id]"]').val(item.getAttribute('data-city-fias-id'));
            $('[name="Address[street]"]').val(item.getAttribute('data-street'));
            $('[name="Address[street_fias_id]"]').val(item.getAttribute('data-street-fias-id'));
            $('[name="Address[house]"]').val(item.getAttribute('data-house'));
            $('[name="Address[flat]"]').val(item.getAttribute('data-flat'));
            $('[name="Address[housing]"]').val(item.getAttribute('data-housing'));
            $('[name="Address[postcode]"]').val(item.getAttribute('data-postcode'));

        }
    });
});

window.addPhone = function () {
    var prodContainer = $('.phones'),
        lastGrouprId = prodContainer.find('[class*=phone-row]:last').prop('id'),
        lastIndex = lastGrouprId.split('-')[1];

    $('.action').each(function (index) {
        $(this).html('<button class="btn btn-sm btn-default" type="button"><i class="fa fa-trash"></i></button>');
    });
    $.get('/shop/get-phone-row?i='+lastIndex, function(prodRow) {
        prodContainer.append($(prodRow));
    });
};
$('body').delegate('#addPhone', 'click', function() {
    window.addPhone();
});
$('body').delegate('.action button', 'click', function() {
    var product = $(this).parents('[class*=phone-row]');
    if ($('[class*=phone-row]').length > 1) {
        product.remove();
    }
});