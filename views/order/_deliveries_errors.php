<?php

/** @var $errors array */

?>
<p class="info-block lead text-muted text-center mb5">Для расчета доставки необходимо исправить следующие ошибки:</p>
<div class="delivery_errors">
    <?php foreach ($errors as $error) : ?>
        <?php if(is_array($error)):?>
            <?php foreach ($error as $message):?>
                <p><?= $message ?></p>
            <?php endforeach;?>
        <?php else: ?>
            <p><?= $error ?></p>
        <?php endif;?>
    <?php endforeach; ?>
</div>
