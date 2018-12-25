var body = $('body');
var form = 'form';
var input = '[class*=product-row] input';
var firstCalc = true;
var tabPanelPointId = 'tab-panel-point';
var mapButtonId = 'shop-on-map';
var colorSuccess = '#5cb85c';
var colorDanger = '#d9534f';
var colorIcon = '#0095b6';

window.afterAjaxLoading = function () {
    $('[data-toggle="tooltip"]').tooltip();
};

window.addProduct = function () {
    var prodContainer = $('.products'),
        lastGrouprId = prodContainer.find('[class*=product-row]:last').prop('id'),
        lastIndex = lastGrouprId.split('-')[1];
    $.get('get-product-row?i=' + lastIndex, function (prodRow) {
        prodContainer.append($(prodRow));
    });
    window.checkConsistentlyProducts();
};

// Проверка товаров на уникальность, для предотврощения задвоения продуктов
window.checkConsistentlyProducts = function () {
    var productCodes = [],
        productRow = $('.product-row'),
        hasError = false;

    productRow.each(function () {
        var productNum = $(this).attr('id').split('-')[1];
        var code = $(this).find('[name="Product[' + productNum + '][barcode]"]').val() + '_' + $(this).find('[name="Product[' + productNum + '][id]"]').val();
        if (productCodes.indexOf(code) < 0) {
            productCodes[productNum] = code;
        } else {
            $('#group-' + productNum)
                .find('[name="Product[' + productNum + '][barcode]"]')
                .parent()
                .addClass('has-error')
                .attr('title', 'Повтор товара');
            hasError = true;
        }
    });
    if (!hasError) {
        productRow.removeClass('error');
    } else {
        $('.product-row .has-error').tooltip({
            'placement': 'bottom'
        });
    }
};

window.calculateTotals = function () {
    var sum = 0,
        accessedSum = 0,
        deliveryCostInput = $('#orderdelivery-cost'),
        paymentInput = $('#order-payment_method'),
        deliverySum = deliveryCostInput.val(),
        paymentMethod = paymentInput.val();

    $('[class*=product-row]').each(function () {
        var quantityInput = $(this).find('input[name*=quantity]'),
            priceInput = $(this).find('input[name*=price]'),
            accessedPriceInput = $(this).find('input[name*=accessed_price]'),
            price = 0,
            accessedPrice = 0,
            quantity = 0;

        if (!isNaN(parseFloat(quantityInput.val()))) {
            quantity = parseFloat(quantityInput.val());
        }

        if (!isNaN(parseFloat(priceInput.val()))) {
            price = parseFloat(priceInput.val());
            sum += price * quantity;
        }

        if (!isNaN(parseFloat(accessedPriceInput.val()))) {
            accessedPrice = parseFloat(accessedPriceInput.val());
            accessedSum += accessedPrice * quantity;
        }
    });

    if (paymentMethod === 'noPay' || paymentMethod === 'productPay') {
        deliverySum = 0;
    }

    var params = {
        'sum': sum,
        'accessed_sum': accessedSum,
        'delivery_sum': deliverySum,
        'payment_method': paymentMethod
    };

    deliveryCostInput.val(deliverySum);

    $.get('calculate-totals?' + $.param(params)).done(function (totals) {
        var html = '';
        $.each(totals, function (index, ttl) {
            html += '<div class="col-sm-3 col-print-3"><label>' + ttl.label + '<div class="products-price">' + ttl.value + '</div></label></div>';
        });
        $('#order-total').html(html);
    });
};

// Получение ссылки для кнопки Позвонить
window.getCallUrl = function () {
    var orderId = 0;
    if (params.id !== undefined) {
        orderId = parseInt(params.id);
    }
    $.ajax({
        'url': '/order/get-call-url',
        data: {
            shopId: $('[name="Order[shop_id]"]').val(),
            orderId: orderId,
            clientPhone: $('[name="Order[phone]"]').val()
        },
        dataType: 'json',
        type: 'GET',
        success: function (data) {
            if (data.url) {
                $('[name="Order[phone]"]').parent().find('a.btn').attr('href', data.url)
            } else {
                $('[name="Order[phone]"]').parent().find('a.btn').removeAttr('href')
            }
        }
    });
};

// Помечаем заказ измененным
var orderChanged = false;
window.markOrderChanged = function () {
    if (!orderChanged && !$('#smb').prop('disabled')) {
        var workflow = '.workflow',
            submit = '#smb';

        $('.workflow .btn').attr('disabled', true);
        $(workflow).attr('title', 'Для изменения статусов, сохраните заказ.');
        $(workflow).tooltip({
            'placement': 'left',
            'trigger': 'custom'
        });
        $(workflow).tooltip('show');
        $(submit).attr('title', 'Заказ был изменен. ' +
            'Не забудьте сохранить изменения.');
        $(submit).tooltip({
            'placement': 'right',
            'trigger': 'custom'
        });
        $(submit).tooltip('show');
        $('a').on('click', function (e) {
            var confirmed = confirm('Заказ был изменен. Не сохраненные изменения будут утеряны. Продолжить?');
            if (!confirmed) {
                e.preventDefault();
            }
        });
        orderChanged = true;
    }
};

window.clearEmptyProductRows = function () {
    $('.products .product-row').each(function () {
        var isEmptyRow = true;
        $(this).find('input').each(function () {
            if ($(this).val() !== '') {
                isEmptyRow = false;
            }
        });
        if (isEmptyRow) {
            $(this).remove();
        }
    });
};

window.parseXML = function () {
    var formData = new FormData();
    var counter = 0;

    $.each($('[name="products-upload"]')[0].files, function (i, file) {
        formData.append('xml', file);
    });

    $('.products .product-row').each(function () {
        counter = parseInt($(this).attr('id').split('-')[1]);
    });
    formData.append('counter', counter);

    $.ajax({
        url: '/product/parse-xml',
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        type: 'POST',
        success: function (data) {
            window.clearEmptyProductRows();
            $('.products').append(data);
            window.recalcTotal();
        },
        error: function (error) {
            swal({
                confirmButtonColor: colorDanger,
                title: "Ошибка",
                text: error.responseJSON.message
            });
        }
    });
};

window.pickupDateChanged = function (e) {
    var orderId = 0;
    if (params.id !== undefined) {
        orderId = parseInt(params.id);
    }
    var deliveryDate = localStorage.getItem('orderId_' + orderId);
    if (deliveryDate === null) {
        deliveryDate = e.currentTarget.value;
    }
    $.ajax({
        'url': '/order/get-delivery-date',
        dataType: 'json',
        data: {
            'pickupDate': e.currentTarget.value,
            'deliveryDate': deliveryDate,
            'minTerm': $('[name="OrderDelivery[min_term]"]').val()
        },
        type: 'GET',
        success: function (json) {
            $('[name="OrderDelivery[delivery_date]"]').val(json.deliveryDate);
        },
        error: function (data) {
            console.log(data);
        }
    });
};

window.deliveryDateChanged = function (e) {
    var orderId = 0;
    if (params.id !== undefined) {
        orderId = parseInt(params.id);
    }
    localStorage.setItem('orderId_' + orderId, e.currentTarget.value);
};

function mapInit() {
    var myMap;
    body.delegate('#' + mapButtonId, 'click', function () {
        var tabPanelPoint = $('#' + tabPanelPointId);
        var button = $(this);
        if (!tabPanelPoint.hasClass('btn-primary')) {
            tabPanelPoint.trigger('click');
        }

        var quotes = $('.point-list .quote');
        var pointMap = $('.point-map');
        var pointList = $('.point-list');
        if (!myMap) {

            pointMap.removeClass('hidden');
            pointList.addClass('hidden');
            button.text('Списком');

            myMap = new ymaps.Map('map', {
                center: [quotes.data('lat'), quotes.data('lng')],
                zoom: 9,
                controls: ['zoomControl']
            }, {
                searchControlProvider: 'yandex#search'
            });

            var listButton = new ymaps.control.Button("Показать список");
            myMap.controls.add(listButton, {float: 'left', maxWidth: 150});

            listButton.events.add('click', function (e) {
                $('#' + mapButtonId).trigger('click');
            });

            var cluster = new ymaps.Clusterer({
                preset: 'islands#icon',
                iconColor: colorIcon,
                groupByCoordinates: false,
                clusterDisableClickZoom: false,
                clusterHideIconOnBalloonOpen: false,
                geoObjectHideIconOnBalloonOpen: false,
                gridSize: 80
            });

            var geoObjects = [];
            var i = 0;
            quotes.each(function (index) {
                geoObjects[i] = new ymaps.Placemark(
                    [$(this).data('lat'), $(this).data('lng')],
                    {
                        balloonContentHeader: $(this).data('delivery-name'),
                        balloonContentBody: '<br /><p class="delivery-popup delivery-popup-info"><strong>' + $(this).data('address') + ' </strong>' + $(this).data('phone') + '</p>' +
                            '<p class="delivery-popup delivery-popup-terms"><strong>' + $(this).data('terms') + '</strong>' + $(this).data('pickup') + '</p>' +
                            '<hr /><p class="delivery-popup delivery-popup-price">Стоимость доставки: <strong>' + $(this).data('price') + '</strong></p>' +
                            '<p class="delivery-popup delivery-popup-button">' +
                            '<button class="btn btn-sm btn-warning apply-delivery" ' +
                                    'type="' + $(this).data('type') + '" ' +
                                    'uid="' + $(this).data('uid') + '" ' +
                                    'data-carrier-key="' + $(this).data('carrier-key') + '" ' +
                                    'data-cost="' + $(this).data('cost') + '" ' +
                                    'tariff-id="' + $(this).data('tariff-id') + '" ' +
                                    'carrier-key="' + $(this).data('carrier-key') + '">Выбрать</button>' +
                        '</p>',
                        balloonContentFooter: '',
                        clusterCaption: $(this).data('delivery-name')
                    },
                    {
                        preset: 'islands#icon',
                        iconColor: colorIcon
                    }
                );
                i++;
            });

            cluster.add(geoObjects);
            myMap.geoObjects.add(cluster);

            myMap.setBounds(cluster.getBounds(), {
                checkZoomRange: true
            });
        }
        else {
            pointMap.addClass('hidden');
            pointList.removeClass('hidden');
            button.text('На карте');
            myMap.destroy();
            myMap = null;
        }
    });
}

ymaps.ready(mapInit);

// Расчет стоимости доставки
window.calculateCarriers = function () {
    var orderForm = $('form'),
        carriersSelector = '#deliveries',
        serializeForm = orderForm.serialize();

    orderForm.find('[disabled]').each(function () {
        serializeForm = serializeForm + '&' + $(this).attr('name') + '=' + $(this).val();
    });

    if (firstCalc) {
        serializeForm = serializeForm + '&' + 'Order[partial]=' + params.ispartial;
    }

    if (orderForm.find('input[name*=city]').val() !== '') {
        $(carriersSelector).html('');
        $('.alert-block').hide();
        $('#calculate').show();

        $.post('calculate-carriers', serializeForm).done(function (carriers) {
            if (carriers.length) {
                $('#calculate').hide();
                $(carriersSelector).html(carriers);
                $(carriersSelector).show();
                window.afterAjaxLoading();
            } else {
                $('#calculate').hide();
                $('.alert-block').show();
            }
        });
    }
};

// Перерасчет итогов для заказа
window.recalcTotal = function (force) {
    var quantityS = '.sum-quantity',
        weightS = '.sum-weight',
        priceS = '.sum-price',
        oldSumWeight = !isNaN(parseFloat($(weightS).text())) ? parseFloat($(weightS).text()) : 0,
        oldSumPrice = !isNaN(parseFloat($(priceS).text())) ? parseFloat($(priceS).text()) : 0,
        sumQuantity = 0,
        sumWeight = 0,
        sumPrice = 0;

    $('[class*=product-row]').each(function () {
        var quantityInput = $(this).find('input[name*=quantity]'),
            weightInput = $(this).find('input[name*=weight]'),
            priceInput = $(this).find('input[name*=price]'),
            quantity = 0,
            weight = 0,
            price = 0;

        if (!isNaN(parseFloat(quantityInput.val()))) {
            quantity = parseFloat(quantityInput.val());
            sumQuantity += quantity;
        }

        if (!isNaN(parseFloat(weightInput.val()))) {
            weight = parseFloat(weightInput.val().split(',').join('.')) * 1000;
            sumWeight += weight * quantity;
        }

        if (!isNaN(parseFloat(priceInput.val()))) {
            price = parseFloat(priceInput.val());
            sumPrice += price * quantity;
        }
    });

    $(weightS).text(sumWeight / 1000);
    $(priceS).text(sumPrice);
    $(quantityS).text(sumQuantity);

    if (oldSumPrice !== sumPrice || oldSumWeight !== sumWeight || force) {
        if (!firstCalc) {
            $('.delivery').html('');
            $('#deliveries').removeClass('hidden');
        }
        window.calculateCarriers();
        window.calculateTotals();
        firstCalc = false;
    }
};

$(document).ready(function () {
    body.delegate('.tab-panels a', 'click', function () {
        if ($(this).attr('id') === tabPanelPointId) {
            $('#' + mapButtonId).addClass('btn-primary');
        } else {
            $('#' + mapButtonId).removeClass('btn-primary');
        }
    });

    body.delegate('#order-warehouse_id', 'change', function () {
        window.recalcTotal(true);
    });

    body.delegate('.additional-services', 'change', function () {
        window.recalcTotal(true);
    });

    body.delegate('[class*=products] input', 'change', function () {
        window.recalcTotal();
    });

    body.delegate('.dimension-item input', 'change', function () {
        window.recalcTotal();
    });

    body.delegate('[name="filter"]', 'change', function () {
        window.recalcTotal(true);
    });

    body.delegate('.delete-product button', 'click', function () {
        var product = $(this).parents('[class*=product-row]');
        if ($('[class*=product-row]').length > 1) {
            product.remove();
            window.recalcTotal();
            window.markOrderChanged();
            window.checkConsistentlyProducts();
        }
    });

    body.delegate('#change-delivery', 'click', function () {
        $(this).addClass('hidden');
        $('#deliveries').removeClass('hidden');
    });

    setTimeout(function () {
        window.recalcTotal();
    }, 100);

    body.delegate('.apply-delivery', 'click', function (e) {
        e.preventDefault();

        var form = $('#order-form');
        var disabled = form.find(':input:disabled').removeAttr('disabled');
        var serialized = form.serialize();
        disabled.attr('disabled','disabled');
        var carrierKey = $(this).attr('data-carrier-key');
        var serviceKey = 'no-service';
        var currentPaymentMethod = $('#order-payment_method').val();
        var deliveryPrice = parseInt($(this).attr('data-cost'));

        serialized += "&uid=" + $(this).attr('uid');
        serialized += "&type=" + $(this).attr('type');
        // form += "&tariff_id="+$(this).attr('tariff-id');

        if ($('[name="Order[partial]"]').is(':checked')) {
            serviceKey = 'partial';
        }

        if (currentPaymentMethod === 'noPay' || currentPaymentMethod === 'productPay') {
            deliveryPrice = 0;
        }

        $('#orderdelivery-cost').val(deliveryPrice);

        // Запросим возможные методы оплаты для выбранной СД
        $.ajax({
            type: 'GET',
            url: 'payment-methods-by-carrier-key',
            data: {carrierKey: carrierKey, serviceKey: serviceKey},
            dataType: "json",
            success: function (responseData) {
                var options = '';
                $.each(responseData, function (k, v) {
                    if (k === currentPaymentMethod) {
                        options += '<option selected="selected" value="' + k + '">' + v + '</option>'
                    } else {
                        options += '<option value="' + k + '">' + v + '</option>';
                    }
                });
                $('#order-payment_method').html(options);
            }
        });

        $.ajax({
            type: 'POST',
            url: 'apply-delivery',
            data: serialized,
            dataType: "text",
            beforeSend: function () {
                $('.container-fluid').addClass('pjax-loading');
            },
            success: function (responseData) {
                $('#order-delivery').html(responseData);
                $('#deliveries').addClass('hidden');
                window.markOrderChanged();
                window.calculateTotals();
                $('.container-fluid').removeClass('pjax-loading');
            }
        });
    });

    body.delegate('[name="products-upload"]', 'change', function () {
        window.parseXML();
    });

    body.delegate('#order-payment_method', 'change', function () {
        window.recalcTotal(true);
    });

    body.delegate(input, 'blur', function () {
        $(this).parents('[class*=product-row]').removeClass('active')
    });

    body.delegate('#addProduct', 'click', function () {
        window.addProduct();
    });

    $(form).on('keyup keypress', function (e) {
        var keyCode = e.keyCode || e.which;
        if (keyCode === 13) {
            e.preventDefault();
            return false;
        }
    });

    $(form).on('submit', function (e) {
        var group = '[class*=product-row]';
        if ($(group).length > 1) {
            $(group).each(function () {
                var empty = true;
                $(this).find('input').each(function () {
                    if ($(this).val() !== '') {
                        empty = false;
                    }
                });

                if (empty) {
                    e.preventDefault();
                    $(this).remove();
                    $(this).submit();
                }
            });
        }
    });

    body.delegate('input,select', 'change', function () {
        window.markOrderChanged();
    });

    body.delegate('.sa-button-container .cancel', 'click', function () {
        $('.container-fluid').removeClass('pjax-loading');
    });

    body.delegate('.workflow .btn', 'click', function (e) {
        e.preventDefault();
    });

    body.delegate('#addProductsByExcel', 'click', function (e) {
        $('[name="products-upload"]').trigger('click');
        e.preventDefault();
    });

    // Обрабока звонка
    body.delegate('.can-call', 'click', function () {
        swal({
            title: 'Выполняется звонок',
            html: true,
            text: 'Звонок начался пожалуйста оставайтесь на связи <br /><br /><p class="text-center"><img src="/img/call.gif" /></p>',
            confirmButtonText: 'Закрыть',
            confirmButtonColor: colorSuccess,
            showCancelButton: false,
            closeOnClickOutside: true,
            closeOnConfirm: true
        });
    });

    if (params.isavailablecall !== undefined && params.isavailablecall) {
        body.delegate('[name="Order[shop_id]"]', 'change', function () {
            window.getCallUrl();
        });

        body.delegate('[name="Order[phone]"]', 'change', function () {
            window.getCallUrl();
        });

        window.getCallUrl();
    }


    // Обработаем событие подтверждения заказ, чтобы навесить обраьотку адреса
    body.delegate('#smb', 'click', function (e) {
        e.preventDefault();
        var form = $('#order-form');
        $.ajax({
            'url': '/order/check-address',
            data: {
                addressFull: $('[name="Address[full_address]"]').val(),
                shopId: $('[name="Order[shop_id]"]').val(),
                region: $('[name="Address[region]"]').val(),
                city: $('[name="Address[city]"]').val(),
                street: $('[name="Address[street]"]').val(),
                house: $('[name="Address[house]"]').val(),
                housing: $('[name="Address[housing]"]').val(),
                flat: $('[name="Address[flat]"]').val(),
                postcode: $('[name="Address[postcode]"]').val()
            },
            dataType: 'json',
            type: 'GET',
            beforeSend: function () {
                $('.container-fluid').addClass('pjax-loading');
            },
            success: function (data) {
                var errorMessage = '';
                $.each(data, function (index, value) {
                    errorMessage += value + '<br />';
                });
                if (errorMessage !== '') {
                    swal({
                        title: 'Внимание',
                        html: true,
                        text: errorMessage,
                        confirmButtonText: 'Подтвердить',
                        cancelButtonText: 'Отменить',
                        confirmButtonColor: colorSuccess,
                        showCancelButton: true,
                        closeOnConfirm: false,
                        showLoaderOnConfirm: true
                    }, function () {
                        form.submit();
                    });
                } else {
                    form.submit();
                }
            },
            error: function () {
                // Так как если этот сервис не работает все равно нужно сохранить заказ
                form.submit();
            }
        });
    });

    // Если существует id заказа значит сделаем запрос
    if (params.id !== undefined) {
        $.ajax({
            'url': '/order/calls?orderId=' + params.id,
            dataType: 'html',
            type: 'GET',
            success: function (html) {
                $('#order-left-block').append(html);
            },
            error: function (data) {
                console.log(data);
            }
        });
    }

    // Для заказов без возможности отменить заказ выведем всплывающее окно с сообщением
    $('body').delegate('.is-not-manual', 'click', function (e) {
        e.preventDefault();
        swal({
            confirmButtonColor: colorDanger,
            title: "Ошибка",
            text: 'Вы не можете отменить заказ в этом статусе. Для того чтобы отменить заказ свяжитесь с поддержкой.'
        });
    });

    $(body).on('change', '#orderdelivery-cost', function () {
        window.calculateTotals();
    });

    $(body).on('change', '#orderdelivery-cost', function () {
        window.calculateTotals();
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

            return '<div class="autocomplete-suggestion" ' +
                ' data-region="' + item['data']['regionWithType'] + '"' +
                ' data-region-fias-id="' + item['data']['regionFiasId'] + '"' +
                ' data-city="' + item['data']['cityWithType'] + '"' +
                ' data-city-fias-id="' + item['data']['cityFiasId'] + '"' +
                ' data-street="' + item['data']['streetWithType'] + '"' +
                ' data-street-fias-id="' + item['data']['streetFiasId'] + '"' +
                ' data-house="' + (item['data']['house'] || '') + '"' +
                ' data-flat="' + (item['data']['flat'] || '') + '"' +
                ' data-housing="' + (item['data']['block'] || '') + '"' +
                ' data-postcode="' + (item['data']['postalCode'] || '') + '"' +
                ' data-val="' + item['value'] + '">' + item['value'].replace(re, "<b>$1</b>") + '</div>';
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

            if (oldCity !== item.getAttribute('data-city')) {
                window.recalcTotal(true);
            }
        }
    });

    // Автоподстановка региона
    new autoComplete({
        selector: '#address-region',
        source: function (term, response) {
            try {
                xhr.abort();
            } catch (e) {
            }
            xhr = $.getJSON('/api/address/region', {q: term}, function (data) {
                response(data);
            });
        },
        renderItem: function (item, search) {
            search = search.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
            var re = new RegExp("(" + search.split(' ').join('|') + ")", "gi");

            return '<div class="autocomplete-suggestion" ' +
                ' data-region="' + item['data']['regionWithType'] + '"' +
                ' data-region-fias-id="' + item['data']['regionFiasId'] + '"' +
                ' data-val="' + item['value'] + '">' + item['value'].replace(re, "<b>$1</b>") + '</div>';
        },
        onSelect: function (e, term, item) {
            $('[name="Address[region_fias_id]"]').val(item.getAttribute('data-region-fias-id'));
            $('#full_address').val(makeFullAddress());
        }
    });

    // Автоподстановка города
    new autoComplete({
        selector: '#address-city',
        source: function (term, response) {
            try {
                xhr.abort();
            } catch (e) {
            }
            xhr = $.getJSON('/api/address/city', {q: term, l: $('[name="Address[region]"]').val()}, function (data) {
                response(data);
            });
        },
        renderItem: function (item, search) {
            search = search.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
            var re = new RegExp("(" + search.split(' ').join('|') + ")", "gi");

            return '<div class="autocomplete-suggestion" ' +
                ' data-city="' + item['data']['cityWithType'] + '"' +
                ' data-city-fias-id="' + item['data']['cityFiasId'] + '"' +
                ' data-val="' + city + '">' + item['value'].replace(re, "<b>$1</b>") + '</div>';
        },
        onSelect: function (e, term, item) {
            var cityInput = $('[name="Address[city_fias_id]"]');
            var oldCity = cityInput.val();
            cityInput.val(item.getAttribute('data-city-fias-id'));
            if (oldCity !== item.getAttribute('data-city-fias-id')) {
                window.recalcTotal(true);
            }
            $('#full_address').val(makeFullAddress());
        }
    });

    // Автоподстановка улиц
    new autoComplete({
        selector: '#address-street',
        source: function (term, response) {
            try {
                xhr.abort();
            } catch (e) {
            }
            xhr = $.getJSON('/api/address/street', {q: term, l: $('[name="Address[city]"]').val()}, function (data) {
                response(data);
            });
        },
        renderItem: function (item, search) {
            search = search.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
            var re = new RegExp("(" + search.split(' ').join('|') + ")", "gi");
            return '<div class="autocomplete-suggestion" ' +
                ' data-street="' + item['data']['streetWithType'] + '"' +
                ' data-street-fias-id="' + item['data']['streetFiasId'] + '"' +
                ' data-val="' + item['data']['streetWithType'] + '">' + item['value'].replace(re, "<b>$1</b>") + '</div>';
        },
        onSelect: function (e, term, item) {
            $('[name="Address[street_fias_id]"]').val(item.getAttribute('data-street-fias-id'));
            $('#full_address').val(makeFullAddress());
        }
    });

    // Получение полного адреса
    function makeFullAddress() {
        var fullAddress = [];

        if ($('#address-region').val() !== '') {
            fullAddress.push($('#address-region').val());
        }
        if ($('#address-city').val() !== '') {
            fullAddress.push($('#address-city').val());
        }
        if ($('#address-street').val() !== '') {
            fullAddress.push($('#address-street').val());
        }
        if ($('#address-house').val() !== '') {
            fullAddress.push($('#address-house').val());
        }
        if ($('#address-housing').val() !== '') {
            fullAddress.push($('#address-housing').val());
        }
        if ($('#address-flat').val() !== '') {
            fullAddress.push($('#address-flat').val());
        }
        if ($('#address-postcode').val() !== '') {
            fullAddress.push($('#address-postcode').val());
        }

        return fullAddress.join(', ');
    }

    $(body).on('change', '.detailed-address-input', function () {
        if ($('#order-address_detailed').is(':checked')) {
            $('#full_address').val(makeFullAddress());
        }
    });
});