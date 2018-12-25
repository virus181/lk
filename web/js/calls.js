
$.ajax({
    'url': '/call/get-shop-phone-numbers',
    dataType: 'json',
    type: 'GET',
    success: function (data) {
        if (data.length) {
            $('.phones').append('<p class="phone-number-list"><small>Вашему магазину пердоставлен номер:</small> ' + data.join(', ') + '</p>');
        }
    },
    error: function (data) {
        console.log(data);
    }
});