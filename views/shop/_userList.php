<?php

use yii\helpers\Html;

/* @var $users \app\models\User[] */
/* @var $user \app\models\User */

?>
<div class="shop-user-list">
    <title><?php echo Yii::t('shop', 'Shop user list');?></title>
    <h1><?php echo Yii::t('shop', 'Shop user list');?></h1>
    <table class="table">
        <thead>
        <tr>
            <td><?php echo Yii::t('shop', 'ID');?></td>
            <td><?php echo Yii::t('shop', 'FIO');?></td>
            <td><?php echo Yii::t('shop', 'E-mail');?></td>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo $user->id; ?></td>
                <td><?php echo Html::a($user->fio, ['user/update', 'id' => $user->id]); ?></td>
                <td><?php echo $user->email; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

