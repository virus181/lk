var params = $('#data-param-element').data();

function stickyHeader() {
    $('.page-top-sticky').airStickyBlock({
        debug: false,
        stopBlock: '.footer',
        offsetTop: 0
    });
}

stickyHeader();
$(document).on('pjax:success', function () {
    params = $('#data-param-element').data();
    stickyHeader();
});

$(document).on('pjax:beforeSend', function () {
    if ($('.modal.in').length > 0) {
        $('.modal.in').find('.modal-content').addClass('pjax-loading');
    } else {
        $('.container-fluid').addClass('pjax-loading');
    }
});

$(document).on('pjax:complete', function () {
    $('[data-toggle="tooltip"]').tooltip();
    $('.pjax-loading').removeClass('pjax-loading');
    $(".grid-view .table").resizableColumns({
        store: window.store
    });
});

$('body').on('click', 'tr a[data-toggle=modal], a[data-toggle=modal]', function (e) {
    e.stopPropagation();
    var link = $(this),
        obj = $($(this).data('target'));

    obj.find('.modal-body').html('<div class="text-center"><i class="fa fa-circle-o-notch fa-spin fa-3x fa-fw"></i></div>');
    obj.find('.modal-header').find('.modal-title').remove();
    obj.modal('show');

    $.get(link.data('href'), function (data) {
        var page = $(data),
            header = page.find('h1').text(),
            title = page.find('title').text();

        page.find('h1').remove();
        obj.find('.modal-header').append('<h5 class="modal-title lead">' + header + '</h5>');
        obj.find('.modal-body').html(page);

        if (page.find('title').length > 0) {
            $('head').find('title').html(title);
            page.find('title').remove();
        }
    });
    return false;
});


// ------------------------------------------------------------------------------------------------
// Общий функционал для работы с модалками
var modalId = '#modal';

// выбираем целевой элемент
var target = document.getElementById('modal').getElementsByClassName('modal-header')[0];

// создаём экземпляр MutationObserver
var observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
        for (var i = 0; i < mutation.addedNodes.length; i++) {
            if (mutation.addedNodes[i].className !== undefined
                && mutation.addedNodes[i].className.indexOf('modal-title') !== -1
            ) {
                localStorage.setItem("title", document.title);
                document.title = mutation.addedNodes[i].innerText;
            }
        }
    });
});

// конфигурация нашего observer:
var config = { attributes: true, childList: true, characterData: true };

// передаём в качестве аргументов целевой элемент и его конфигурацию
observer.observe(target, config);

$(modalId).on('hidden.bs.modal', function () {
    document.title = localStorage.getItem("title");
});

// ------------------------------------------------------------------------------------------------


window.afterAjaxLoading = function () {
    $('[data-toggle="tooltip"]').tooltip();
};

$(function () {
    $(".menu-tooltip").tooltip();
    $('[data-toggle="tooltip"]').tooltip();

    $(".grid-view .table").resizableColumns({
        store: window.store
    });
});

