var body = $('body');

body.on('click', '#courier-call', function () {
    if ($('[name="selection[]"]:checked').length > 0) {
        $('#modal-courrier-call').modal('show');
        $.ajax({
            'url': '/order/courier-call',
            data: $('[name="selection[]"]').serialize(),
            dataType: 'html',
            type: 'POST',
            success: function (data) {
                var modal = $('#modal-courrier-call'),
                    page = $(data),
                    header = page.find('h1').text(),
                    title = page.find('title').text();

                page.find('h1').remove();
                modal.find('.modal-title').remove();
                modal.find('.modal-header').append('<h5 class="modal-title lead">' + header + '</h5>');
                modal.find('.modal-body').html(page);

                if (page.find('title').length > 0) {
                    $('head').find('title').html(title);
                    page.find('title').remove();
                }
            },
            error: function (data) {
                $('#modal-courrier-call').find('.modal-body').html('<h2 class="text-center">' + data.responseText + '</h2><br/>');
                console.log(data);
            }
        });
    } else {
        alert('Выберите хотя бы один заказ из списка');
    }
});

body.on('click', '#cancel-orders', function () {
    if ($('[name="selection[]"]:checked').length > 0) {
        if (confirm('Вы действительно хотите отменить выбранные заказы?')) {
            $.ajax({
                'url': '/order/set-cancel-status-to-orders',
                data: $('[name="selection[]"]').serialize(),
                dataType: 'json',
                type: 'POST',
                success: function (data) {
                    window.location.href = data.url;
                },
                error: function (data) {
                    $('#modal-cancel-order').modal('show');
                    $('#modal-cancel-order').find('.modal-body').html('<h2 class="text-center">' + data.responseJSON.message + '</h2><br/>');
                    console.log(data);
                }
            });
        }
    } else {
        alert('Выберите хотя бы один заказ из списка');
    }
});

body.on('click', '.confirm-orders-button', function (e) {
    e.preventDefault();

    $.ajax({
        'url': '/order/is-confirm-allowed',
        data: $('[name="selection[]"]').serialize(),
        dataType: 'json',
        type: 'POST',
        beforeSend: function () {
            $('.container-fluid').addClass('pjax-loading');
        },
        success: function (data) {
            $('.container-fluid').removeClass('pjax-loading');
            if (data.error) {
                swal({
                    confirmButtonColor: "#d9534f",
                    title: "Ошибка",
                    text: data.error.messages.join("\n")
                });
            } else {
                swal({
                    title: data.title,
                    text: data.message,
                    confirmButtonText: 'Подтвердить',
                    cancelButtonText: 'Отменить',
                    confirmButtonColor: "#5cb85c",
                    showCancelButton: true,
                    closeOnConfirm: false,
                    showLoaderOnConfirm: true
                }, function () {
                    $.ajax({
                        'url': '/order/multi-confirm',
                        data: $('[name="selection[]"]').serialize(),
                        dataType: 'json',
                        type: 'POST',
                        success: function (data) {
                            if (data.status) {
                                swal({
                                    title: "Заказы успешно подтверждены."
                                }, function () {
                                    location.reload();
                                });
                            } else {
                                swal({
                                    title: "Воможно некоторые заказы не были обработаны."
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
            }
        },
        error: function (data) {
            // TODO вывести ошибки
        }
    });
});

// Ахивация заказов
body.on('click', '#archive-orders', function (e) {
    e.preventDefault();

    $.ajax({
        'url': '/order/is-archive-allowed',
        data: $('[name="selection[]"]').serialize(),
        dataType: 'json',
        type: 'POST',
        beforeSend: function () {
            $('.container-fluid').addClass('pjax-loading');
        },
        success: function (data) {
            $('.container-fluid').removeClass('pjax-loading');
            if (data.errors) {
                swal({
                    confirmButtonColor: "#d9534f",
                    title: "Ошибка",
                    text: data.errors.join("\n")
                });
            } else {
                swal({
                    title: data.title,
                    text: data.message,
                    confirmButtonText: 'Подтвердить',
                    cancelButtonText: 'Отменить',
                    confirmButtonColor: "#5cb85c",
                    showCancelButton: true,
                    closeOnConfirm: false,
                    showLoaderOnConfirm: true
                }, function () {
                    $.ajax({
                        'url': '/order/multi-archive',
                        data: $('[name="selection[]"]').serialize(),
                        dataType: 'json',
                        type: 'POST',
                        success: function (data) {
                            if (data.status) {
                                swal({
                                    title: "Заказы успешно переведены в архив."
                                }, function () {
                                    location.reload();
                                });
                            } else {
                                swal({
                                    title: "Ошибка. Заказы не переведены в архив."
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
            }
        },
        error: function (data) {
            // TODO вывести ошибки
        }
    });
});

// Разархивироаать заказы
body.on('click', '#un-archive-orders', function (e) {
    e.preventDefault();
    swal({
        title: 'Вывести заказы из архива?',
        confirmButtonText: 'Подтвердить',
        cancelButtonText: 'Отменить',
        confirmButtonColor: "#5cb85c",
        showCancelButton: true,
        closeOnConfirm: false,
        showLoaderOnConfirm: true
    }, function () {
        $.ajax({
            'url': '/order/multi-un-archive',
            data: $('[name="selection[]"]').serialize(),
            dataType: 'json',
            type: 'POST',
            success: function (data) {
                if (data.status) {
                    swal({
                        title: "Заказы успешно выведены их архива."
                    }, function () {
                        location.reload();
                    });
                } else {
                    swal({
                        title: "Ошибка. Заказы не были разархивироанны."
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
