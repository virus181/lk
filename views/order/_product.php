<?php

use app\widgets\AutoComplete;
use app\widgets\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $orderProduct app\models\OrderProduct */
/* @var $disabledEdit boolean */
/* @var $i integer */
/* @var $form yii\widgets\ActiveForm */
?>

<?php
    $product = $orderProduct->product;
    $product->quantity = $orderProduct->quantity;
    $product->width = $orderProduct->width;
    $product->height = $orderProduct->height;
    $product->length = $orderProduct->length;
    $product->weight = $orderProduct->weight;
    $product->price = $orderProduct->price;
    $product->accessed_price = $orderProduct->accessed_price;
    $product->name = $orderProduct->name;
    if ($orderProduct->product_id) {
        $product->id = $orderProduct->product_id;
    }
    $createExp = new JsExpression('function () {
        $(this).data("ui-autocomplete")._renderItem = function (ul, item) {
            var ul_item = $("<li>");
            if (item.count < 1 && item.additional_id) {
                ul_item.addClass("disabled");
            }
            return ul_item
                .append(\'<div class="row">\'
                    + \'<div class="col-sm-2"><span>\' + item.barcode + \'</span></div>\'
                    + \'<div class="col-sm-5"><span>\' + item.name + \'</span></div>\'
                    + \'<div class="col-sm-1"><span>\' + item.weight/1000 + \'</span></div>\'
                    + \'<div class="col-sm-1"><span>\' + item.price + \'</span></div>\'
                    + \'<div class="col-sm-1"><span>\' + item.count + \'</span></div>\'
                    + \'<div class="col-sm-1"><span>\' + ((item.accessed_price) ? item.accessed_price : item.price) + \'</span></div>\'
                    + \'<div class="col-sm-1"><i class="fa fa-cube \'+(item.additional_id ? \'storred\' : \'\') + \'"></i></div>\'
                    + \'<div class="hidden">\' + item.id + \'</div></div></div>\')
                .appendTo(ul);
        };
    }');
    $openExp = new JsExpression('function() {}');

    $sourceExp = new JsExpression('function(request, response) {
        $.ajax({
            url: "' . Url::to(['product/search']) . '",
            dataType: "json",
            data: {
                term: request.term, shop_id: $("#order-shop_id").val(), element: $(this.element).attr("id").split("-")[2]
            },
            success: function(data) {
                response(data);
            }
        });
    }');

    $nameId = Html::getInputId($product, "[$i]name");
    $codeId = Html::getInputId($product, "[$i]barcode");
    $weightId = Html::getInputId($product, "[$i]weight");
    $accessedPriceId = Html::getInputId($product, "[$i]accessed_price");
    $priceId = Html::getInputId($product, "[$i]price");
    $quantityId = Html::getInputId($product, "[$i]quantity");
    $width = Html::getInputId($product, "[$i]width");
    $length = Html::getInputId($product, "[$i]length");
    $height = Html::getInputId($product, "[$i]height");
    $isNotReversible = Html::getInputId($product, "[$i]is_not_reversible");
    $productId = Html::getInputId($product, "[$i]id");
    $selectExp = new JsExpression('function(event, ui) {
            window.markOrderChanged();
            if (ui.item.count < 1 && ui.item.additional_id) {
                alert("Данный товар закончился на складе");
                return false;
            }
            $("#'.$nameId.'").val(ui.item.name);
            $("#'.$codeId.'").val(ui.item.barcode);
            $("#'.$weightId.'").val(ui.item.weight/1000);
            $("#'.$priceId.'").val(ui.item.price);
            $("#'.$width.'").val(ui.item.width);
            $("#'.$length.'").val(ui.item.length);
            $("#'.$height.'").val(ui.item.height);
            $("#'.$quantityId.'").val(1);
            $("#'.$accessedPriceId.'").val(ui.item.accessed_price);
            $("#'.$productId.'").val(ui.item.id);
            $(event.target).parents("[class*=product-row]").find(".fa-cube").removeClass("storred");
            if (ui.item.additional_id) {
                $(event.target).parents("[class*=product-row]").find(".fa-cube").addClass("storred");
            }
            $(event.target).parents("[class*=product-row]").find(".fa-cube")
            $(event.target).parents("[class*=product-row]").find("#' . $quantityId . '").focus().select();
            window.addProduct();
            window.recalcTotal(true);
    }');
?>
<div class="product-row" id="group-<?= $i ?>">
    <div class="row">
        <div class="col-sm-2  col-print-2 <?= isset($product->errors['barcode']) ? 'has-error' : '' ?>">
                <?= AutoComplete::widget([
                    'model' => $product,
                    'attribute' => "[$i]barcode",
                    'options' => ['class' => 'form-control input-sm', 'autocomplete' => 'off', 'disabled' => $disabledEdit],
                    'clientOptions' => [
                        'source' => $sourceExp,
                        'appendTo' => "#group-$i",
                        'create' => $createExp,
                        'open' => $openExp,
                        'select' => $selectExp,
                        'position' => ['of' => "#group-$i"],
                    ],
                ]) ?>
        </div>
        <div class="col-sm-5 col-print-5">
                <?= AutoComplete::widget([
                    'model' => $product,
                    'attribute' => "[$i]name",
                    'options' => ['class' => 'form-control input-sm', 'autocomplete' => 'off', 'disabled' => $disabledEdit],
                    'clientOptions' => [
                        'source' => $sourceExp,
                        'appendTo' => "#group-$i",
                        'create' => $createExp,
                        'open' => $openExp,
                        'select' => $selectExp,
                        'position' => ['of' => "#group-$i"],
                    ],
                ]) ?>
        </div>
        <div class="col-sm-1 col-print-1 <?= isset($product->errors['weight']) ? 'has-error' : '' ?>">
            <?= Html::input(
                'text',
                Html::getInputName($product, "[$i]weight"), ($product->weight ? $product->weight / 1000 : ''),
                [
                    'id' => Html::getInputId($product, "[$i]weight"),
                    'class' => 'form-control input-sm',
                    'autocomplete' => false,
                    'disabled' => $disabledEdit
                ]
            ) ?>
        </div>
        <div class="col-sm-1 col-print-1">
            <?= Html::activeInput('text', $product, "[$i]price", ['class' => 'form-control input-sm', 'autocomplete' => false, 'disabled' => $disabledEdit]) ?>
        </div>
        <div class="col-sm-1 col-print-1">
            <?= Html::activeInput('text', $product, "[$i]quantity", ['class' => 'form-control input-sm', 'autocomplete' => false, 'disabled' => $disabledEdit]) ?>
        </div>
        <div class="col-sm-1 col-print-1">
            <?= Html::activeInput('text', $product, "[$i]accessed_price", ['class' => 'form-control input-sm', 'autocomplete' => false, 'disabled' => $disabledEdit]) ?>
        </div>
        <?= Html::activeInput('hidden', $product, "[$i]id", ['disabled' => $disabledEdit]) ?>
        <div class="col-sm-1  col-print-1">
            <div class="delete-product">
                <i class="fa fa-cube text-success <?= ($product->additional_id && $orderProduct->product->count && ($orderProduct->product->count >= $product->quantity)) ? 'storred' : '' ?>" data-toggle="tooltip"
                   title="<?= $product->additional_id ? 'Товар хранится на складе Fastery' : '' ?>"></i>
                <?php if (!$disabledEdit): ?>
                    <button class="btn btn-sm btn-default" type="button"><i class="fa fa-trash"></i></button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php
$this->registerJs(<<<JS
        $('[data-toggle="tooltip"]').tooltip({
            'container': 'body'
        });
JS
);
?>



