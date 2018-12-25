window.addInventory = function () {
    var prodContainer = $('.inventories'),
        lastGrouprId = prodContainer.find('[class*=inventory-row]:last').prop('id'),
        lastIndex = lastGrouprId.split('-')[1];

    $('.action').each(function (index) {
        $(this).html('<button class="btn btn-sm btn-default" type="button"><i class="fa fa-trash"></i></button>');
    });
    $.get('/rate/get-inventory-row?i='+lastIndex, function(prodRow) {
        prodContainer.append($(prodRow));
    });
};

$('body').delegate('#addInventory', 'click', function() {
    window.addInventory();
});

$('body').delegate('.action button', 'click', function() {
    var product = $(this).parents('[class*=inventory-row]');
    if ($('[class*=inventory-row]').length > 1) {
        product.remove();
    }
});

$('[data-toggle="tooltip"]').tooltip();
window.autoCompleteAddress();

$(document).on('pjax:complete', function() {
    window.autoCompleteAddress();
});