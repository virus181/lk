<?php

/* @var $products \app\models\OrderProduct[] */
foreach ($products as $i => $orderProduct) {
    echo $this->render('/order/_product', [
        'orderProduct' => $orderProduct,
        'disabledEdit' => false,
        'i'            => $i,
    ]);
}