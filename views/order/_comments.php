<?php

use app\models\Call;
use app\models\helper\Phone;
use yii\helpers\Html;

/**
 * @var \app\models\Order $order
 * @var \app\models\Message[] $messages
 */

?>
<div class="block row">
    <div class="col-sm-12 delivery-info">
        <h2>Комментарии <i class="fa fa-commenting-o"
                           data-toggle="tooltip"
                           data-placement="left"
                           title="Данные комментарии не передаются в службу доставки"></i></h2>
        <div class="message-block">
            <?php if ($order->comment): ?>
                <p><?= $order->comment; ?></p>
                <hr>
            <?php endif;?>
            <?php if (isset($messages)): ?>
                <?php foreach ($messages as $message): ?>
                    <p><?= $message->message; ?></p>
                    <hr>
                <?php endforeach; ?>
            <?php endif; ?>
            <div class="form-horizontal message-form col-sm-12">
                <div class="form-group">
                    <?= Html::textarea('message', '', ['class' => 'form-control']); ?>
                    <?= Html::hiddenInput('order_id', $order->id); ?>
                </div>
                <br/>
                <div class="form-group">
                    <?= Html::buttonInput(Yii::t('app', 'Save'), ['class' => 'btn btn-sm btn-primary', 'id' => 'add-order-message']) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Добавление комментариев к заказу
Yii::$app->view->registerJs(<<<JS
        $(document).on('focus', '[name="message"]', function() {
            $(this).removeClass('has-error');
            $(this).tooltip('hide');
        });

        $(document).on('click', '#add-order-message', function() {
            var messageForm = $('.message-form');
            $.ajax({
                type: 'POST',
                url: 'save-message',
                data: $('.message-form :input, .message-form textarea').serialize(),
                dataType: "json",
                beforeSend: function () {
                    $('.message-form').addClass('sending');
                },
                success: [
                    function(responseData) {
                        messageForm.removeClass('sending');
                        if (responseData.success == true) {
                            messageForm.before('<p>' + messageForm.find('textarea').val()  + '</p><hr />');
                            messageForm.find('textarea').val('');
                        } else {               
                            $.each(responseData.errorMessage, function( index, value ) {
                              messageForm.find('textarea').addClass('has-error');
                              $('[name="'+index+'"]').tooltip({
                                  placement: 'top',
                                  title: value,
                                  trigger: 'manual'
                              }).tooltip('show');
                            });
                        }
                    }
                ]
            });
        });
JS
);
?>