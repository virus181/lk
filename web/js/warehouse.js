var body = $('body');

window.updateCoordinates = function () {
    var q = $('[name="Address[full_address]"]').val();
    $.getJSON('/api/address/full', {q: q, limit: 1}, function (data) {
        $('[name="Address[lat]"]').val(data[0].data.geoLat);
        $('[name="Address[lng]"]').val(data[0].data.geoLon);
    });
};

window.autoCompleteAddress = function () {
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
                'data-region="' + item['data']['regionWithType'] + '"' +
                'data-region-fias-id="' + item['data']['regionFiasId'] + '"' +
                'data-city="' + item['data']['cityWithType'] + '"' +
                'data-city-fias-id="' + item['data']['cityFiasId'] + '"' +
                'data-street="' + item['data']['streetWithType'] + '"' +
                'data-street-fias-id="' + item['data']['streetFiasId'] + '"' +
                'data-house="' + (item['data']['house'] || '') + '"' +
                'data-flat="' + (item['data']['flat'] || '') + '"' +
                'data-housing="' + (item['data']['block'] || '') + '"' +
                'data-postcode="' + (item['data']['postalCode'] || '') + '"' +
                'data-lat="' + (item['data']['geoLat'] || '') + '"' +
                'data-lng="' + (item['data']['geoLon'] || '') + '"' +
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
            $('[name="Address[lat]"]').val(item.getAttribute('data-lat'));
            $('[name="Address[lng]"]').val(item.getAttribute('data-lng'));

            window.updateCoordinates();
        }
    });
};

$(document).ready(function () {
    // Автоподстановка адреса
    window.autoCompleteAddress();
});
