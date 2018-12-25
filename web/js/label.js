$('body').delegate('#download-labels', 'click', function (e) {
    var button = $(this);

    if ($('[name="selection[]"]:checked').length === 0) {
        swal({
            confirmButtonColor: "#d9534f",
            title: "Ошибка",
            text: 'Выберите хотя бы один заказ из списка'
        });
        $('.pjax-loading').removeClass('pjax-loading');
        return;
    }

    $.ajax({
        url: params.downloadurl,
        data: $('[name="selection[]"]').serialize(),
        dataType: 'json',
        type: 'GET',
        beforeSend: function() {
            $('.container-fluid').addClass('pjax-loading');
        },
        error: function(req, status, err) {
            swal({
                confirmButtonColor: "#d9534f",
                title: req.responseJSON.name,
                text: req.responseJSON.message
            });
            $('.pjax-loading').removeClass('pjax-loading');
        },
        success: function(data) {
            if (data.success) {
                window.open(data.url);
            }
            $('.pjax-loading').removeClass('pjax-loading');
        }
    });

    e.preventDefault();
});