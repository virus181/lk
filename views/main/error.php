<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

use yii\helpers\Html;

$this->title = $name;
?>
<div class="site-error">
    <h1><span style="font-size: 48px;">Ошибка</span><br/> <?php echo $statusCode;?> - <?php echo $name;?></h1>
    <p>Мы уже пытаемся Вам помочь.<br />
    <?php if (!empty($code)):?>Вы можете отправить нам код ошибки "<strong><?php echo $code;?></strong>", для более оперативного решения проблемы.<?php endif;?>
    </p>
</div>
