function jsonp(url, callback) {
    var number = document.getElementById('fstr-track-id').value;
    var callbackName = 'jsonp_callback_' + Math.round(100000 * Math.random());
    var script = document.createElement('script');

    window[callbackName] = function(data) {
        delete window[callbackName];
        document.body.removeChild(script);
        callback(data);
    };

    script.src = url + (url.indexOf('?') >= 0 ? '&' : '?') + 'callback=' + callbackName + '&number=' + number;
    document.body.appendChild(script);
}

var block = document.getElementById('fastery');
var form = document.createElement("div");
var input = document.createElement("input");
var bttn = document.createElement("button");
var result = document.createElement("div");
var header = document.createElement("div");
var body = document.createElement("div");
var style = document.createElement('link');

style.rel = 'stylesheet';
style.type = 'text/css';
style.href = 'http://lk.fastery.ru/integration/css/tracking.min.css';
document.body.appendChild(style);

form.className = 'fstr-form';
result.className = 'fstr-result';
header.className = 'fstr-header';
input.className = 'fstr-track-input';
body.className = 'fstr-body';
bttn.className = 'fstr-track-button';

input.setAttribute('id', 'fstr-track-id');
input.setAttribute('placeholder', 'Введите трек номер');
form.appendChild(input);

bttn.innerHTML = 'Отследить';
bttn.onclick = function (e) {
    jsonp('http://lk.fastery.ru/api/order/tracking', function(data) {
        header.innerHTML = '';
        body.innerHTML = '';
        if (data.length == 0) {
            header.innerHTML = 'К сожалению, информации по данному заказу нет.';
            result.appendChild(header);
            return;
        }
        var i = 0;
        var pHeaderOrder = document.createElement("p");
        var pHeaderStatus = document.createElement("p");
        var pHeaderCarrier = document.createElement("p");
        var pHeaderAddress = document.createElement("p");

        pHeaderOrder.innerHTML = 'Заказ <strong>№' + data[i]['order_id'] + '</strong>, создан <strong>' + data[i]['created_at'] + '</strong>';
        header.appendChild(pHeaderOrder);

        pHeaderStatus.innerHTML = 'Статус: <strong>' + data[i]['fastery_status'] + '</strong>';
        header.appendChild(pHeaderStatus);

        pHeaderCarrier.innerHTML = 'Служба доставки: <strong>' + data[i]['carrier_key'] + '</strong>';
        header.appendChild(pHeaderCarrier);

        pHeaderAddress.innerHTML = 'Адрес доставки: <strong>' + data[i]['address'] + '</strong>';
        header.appendChild(pHeaderAddress);

        result.appendChild(header);
        for (i = 0; i < data.length; i++) {
            var p = document.createElement("p");
            p.innerHTML = data[i]['status_date'] + ': ' + data[i]['status'];
            body.appendChild(p);
        }
        result.appendChild(body);
    });
};
form.appendChild(bttn);
block.appendChild(form);
block.appendChild(result);