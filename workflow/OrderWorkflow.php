<?php

namespace app\workflow;

use app\models\Order;
use app\models\Shop;
use raoul2000\workflow\source\file\IWorkflowDefinitionProvider;
use Yii;

class OrderWorkflow implements IWorkflowDefinitionProvider
{
    public function getDefinition()
    {
        return [
            'initialStatusId' => Order::STATUS_CREATED,
            'status'          => [
                Order::STATUS_DELIVERY_ERROR       => [
                    'label'      => Yii::t('app', 'Delivery Error'),
                    'transition' => [
                        Order::STATUS_IN_COLLECTING => [
                            'metadata' => [
                                'isSystem' => false,
                                'name'     => Yii::t('app', 'On collecting'),
                                'options'  => ['class' => 'btn-success', 'title' => 'Отправляет заказ на сборку в складскую программу', 'data-toggle' => 'tooltip-status'],
                            ],
                        ],
                        Order::STATUS_CANCELED => [
                            'metadata' => [
                                'isSystem' => false,
                                'name'     => Yii::t('app', 'Cancel'),
                                'options'  => ['class' => 'btn-danger', 'title' => 'Отменяет заказ в системе, а также в службе доставки', 'data-toggle' => 'tooltip-status'],
                            ]
                        ],
                        Order::STATUS_PRESALE       => [
                            'metadata' => [
                                'isSystem' => true,
                                'name'     => Yii::t('app', 'Presale'),
                                'options'  => ['class' => 'btn-success', 'title' => 'Переводит заказ в ожидание поступления товарных позиций', 'data-toggle' => 'tooltip-status'],
                            ]
                        ],
                        Order::STATUS_READY_FOR_DELIVERY => [
                            'metadata' => [
                                'isSystem' => false,
                                'isAllowForOwnOrder' => true,
                                'name' => Yii::t('app', 'Order collected'),
                                'options' => ['class' => 'btn-success', 'title' => 'Перевод заказа в статус “Готов к отгрузке” для вызова курьера', 'data-toggle' => 'tooltip-status'],
                            ]
                        ],
                    ],
                    'metadata'   => [
                        'options' => ['class' => 'btn-danger'],
                    ],
                ],
                Order::STATUS_CREATED              => [
                    'label'      => Yii::t('app', 'Created'),
                    'transition' => [
                        Order::STATUS_DELIVERY_ERROR => [
                            'metadata' => [
                                'isSystem'           => true,
                                'isAllowForOwnOrder' => true,
                                'name'               => Yii::t('app', 'Delivery Error'),
                                'options'            => ['class' => 'hidden'],
                            ],
                        ],
                        Order::STATUS_IN_COLLECTING => [
                            'metadata' => [
                                'isSystem' => false,
                                'isAllowForOwnOrder' => true,
                                'type' => Shop::TYPE_FULFILLMENT,
                                'name' => Yii::t('app', 'On collecting'),
                                'options' => ['class' => 'btn-success', 'title' => 'Отправляет заказ на сборку в складскую программу', 'data-toggle' => 'tooltip-status'],
                            ],
                        ],
                        Order::STATUS_CONFIRMED => [
                            'metadata' => [
                                'isSystem' => false,
                                'isAllowForOwnOrder' => true,
                                'type' => Shop::TYPE_NO_FULFILLMENT,
                                'name' => Yii::t('app', 'On confirmation'),
                                'options' => ['class' => 'btn-success', 'title' => 'Отправляет заказ в СД', 'data-toggle' => 'tooltip-status'],
                            ],
                        ],
                        Order::STATUS_CANCELED => [
                            'metadata' => [
                                'isSystem' => false,
                                'isAllowForOwnOrder' => true,
                                'name' => Yii::t('app', 'Cancel'),
                                'options' => [
                                    'class' => 'btn-danger',
                                    'title' => 'Отменяет заказ в системе, а также в службе доставки',
                                    'data-toggle' => 'tooltip-status'
                                ],
                            ]
                        ],
                        Order::STATUS_PRESALE => [
                            'metadata' => [
                                'isSystem' => true,
                                'name' => Yii::t('app', 'Presale'),
                                'options' => ['class' => 'btn-success', 'title' => 'Переводит заказ в ожидание поступления товарных позиций', 'data-toggle' => 'tooltip-status'],
                            ]
                        ],
                    ],
                    'metadata' => [
                        'options' => ['class' => 'btn-success'],
                    ],
                ],
                Order::STATUS_PRESALE => [
                    'label' => Yii::t('app', 'Presale'),
                    'transition' => [
                        Order::STATUS_DELIVERY_ERROR => [
                            'metadata' => [
                                'isSystem' => true,
                                'name' => Yii::t('app', 'Delivery Error'),
                                'options' => ['class' => 'hidden'],
                            ],
                        ],
                        Order::STATUS_IN_COLLECTING => [
                            'metadata' => [
                                'isSystem' => false,
                                'type' => Shop::TYPE_FULFILLMENT,
                                'name' => Yii::t('app', 'On collecting'),
                                'options' => ['class' => 'btn-success', 'title' => 'Отправляет заказ на сборку в складскую программу', 'data-toggle' => 'tooltip-status'],
                            ],
                        ],
                        Order::STATUS_CANCELED => [
                            'metadata' => [
                                'isSystem' => false,
                                'name' => Yii::t('app', 'Cancel'),
                                'options' => ['class' => 'btn-danger', 'title' => 'Отменяет заказ в системе, а также в службе доставки', 'data-toggle' => 'tooltip-status'],
                            ]
                        ]
                    ],
                    'metadata' => [
                        'options' => ['class' => 'btn-success'],
                    ],
                ],
                Order::STATUS_IN_COLLECTING => [
                    'label' => Yii::t('app', 'In collecting'),
                    'transition' => [
                        Order::STATUS_READY_FOR_DELIVERY => [
                            'metadata' => [
                                'isSystem' => false,
                                'isAllowForOwnOrder' => true,
                                'name' => Yii::t('app', 'Order collected'),
                                'options'            => [
                                    'class' => 'btn-success',
                                    'title' => 'Перевод заказа в статус “Готов к отгрузке” для вызова курьера',
                                    'data-toggle' => 'tooltip-status',
                                    'data-action' => 'set-dimensions'
                                ],

                            ]
                        ],
                        Order::STATUS_DELIVERY_ERROR => [
                            'metadata' => [
                                'isSystem' => true,
                                'isAllowForOwnOrder' => true,
                                'name' => Yii::t('app', 'Delivery Error'),
                                'options' => ['class' => 'hidden'],
                            ],
                        ],
                        Order::STATUS_CANCELED => [
                            'metadata' => [
                                'isSystem' => false,
                                'isAllowForOwnOrder' => true,
                                'name' => Yii::t('app', 'Cancel'),
                                'options' => [
                                    'class' => 'btn-danger',
                                    'title' => 'Отменяет заказ в системе, а также в службе доставки',
                                    'data-toggle' => 'tooltip-status'
                                ],
                            ]
                        ],
                    ],
                ],
                Order::STATUS_CONFIRMED => [
                    'label' => Yii::t('app', 'Confirmed'),
                    'transition' => [
                        Order::STATUS_READY_FOR_DELIVERY => [
                            'metadata' => [
                                'isSystem' => false,
                                'isAllowForOwnOrder' => true,
                                'name' => Yii::t('app', 'Ready for delivery'),
                                'options' => [
                                    'class' => 'btn-success',
                                    'title' => 'Перевод заказа в статус “Готов к отгрузке” для вызова курьера',
                                    'data-toggle' => 'tooltip-status'
                                ],
                            ]
                        ],
                        Order::STATUS_DELIVERY_ERROR => [
                            'metadata' => [
                                'isSystem' => true,
                                'isAllowForOwnOrder' => true,
                                'name' => Yii::t('app', 'Delivery Error'),
                                'options' => ['class' => 'hidden'],
                            ],
                        ],
                        Order::STATUS_CANCELED => [
                            'metadata' => [
                                'isSystem' => false,
                                'isAllowForOwnOrder' => true,
                                'name' => Yii::t('app', 'Cancel'),
                                'options' => [
                                    'class' => 'btn-danger',
                                    'title' => 'Отменяет заказ в системе, а также в службе доставки',
                                    'data-toggle' => 'tooltip-status'
                                ],
                            ]
                        ],
                    ],
                ],
                Order::STATUS_READY_FOR_DELIVERY => [
                    'label' => Yii::t('app', 'Ready for delivery'),
                    'transition' => [
                        Order::STATUS_WAITING_COURIER => [
                            'metadata' => [
                                'isSystem' => true,
                                'isAllowForOwnOrder' => true,
                                'name' => Yii::t('app', 'Call the courier'),
                                'options' => [
                                    'class' => 'btn-success',
                                    'title' => 'Перевод заказа в статус “Готов к отгрузке” для вызова курьера',
                                    'data-toggle' => 'tooltip-status'
                                ],
                            ]
                        ],
                        Order::STATUS_CANCELED => [
                            'metadata' => [
                                'isSystem' => false,
                                'name' => Yii::t('app', 'Cancel'),
                                'options' => ['class' => 'btn-danger', 'title' => 'Отменяет заказ в системе, а также в службе доставки', 'data-toggle' => 'tooltip-status'],
                            ]
                        ],
                    ],
                ],
                Order::STATUS_WAITING_COURIER => [
                    'label' => Yii::t('app', 'Waiting courrier'),
                    'transition' => [
                        Order::STATUS_CANCELED => [
                            'metadata' => [
                                'isSystem' => false,
                                'name' => Yii::t('app', 'Cancel'),
                                'options' => ['class' => 'btn-danger', 'title' => 'Отменяет заказ в системе, а также в службе доставки', 'data-toggle' => 'tooltip-status'],
                            ]
                        ],
                        Order::STATUS_IN_DELIVERY => [
                            'metadata' => [
                                'isSystem' => true,
                                'isAllowForOwnOrder' => true,
                                'name' => Yii::t('app', 'In delivery'),
                                'options' => ['class' => 'btn-success', 'title' => 'Заказ перешел в доставку', 'data-toggle' => 'tooltip-status'],
                            ]
                        ],
                    ],
                ],
                Order::STATUS_IN_DELIVERY => [
                    'label' => Yii::t('app', 'In delivery'),
                    'transition' => [
                        Order::STATUS_CANCELED => [
                            'metadata' => [
                                'isSystem' => false,
                                'name' => Yii::t('app', 'Cancel'),
                                'options' => ['class' => 'btn-danger', 'title' => 'Отменяет заказ в системе, а также в службе доставки', 'data-toggle' => 'tooltip-status'],
                            ]
                        ],
                        Order::STATUS_READY_DELIVERY => [
                            'metadata' => [
                                'isSystem' => true,
                                'name' => Yii::t('app', 'Ready Delivery'),
                                'options' => ['class' => 'btn-success', 'title' => 'Заказ готов к вручению', 'data-toggle' => 'tooltip-status'],
                            ]
                        ],
                        Order::STATUS_DELIVERED => [
                            'metadata' => [
                                'isSystem' => true,
                                'isAllowForOwnOrder' => true,
                                'name' => Yii::t('app', 'Delivered'),
                                'options' => ['class' => 'btn-success', 'title' => 'Заказ доставлен покупателю', 'data-toggle' => 'tooltip-status'],
                            ]
                        ],
                        Order::STATUS_CANCELED_AT_DELIVERY => [
                            'metadata' => [
                                'isSystem' => true,
                                'isAllowForOwnOrder' => true,
                                'name' => Yii::t('app', 'Canceled at delivery'),
                                'options' => ['class' => 'btn-danger', 'title' => 'Отмена при доставке', 'data-toggle' => 'tooltip-status'],
                            ]
                        ],
                        Order::STATUS_PARTIALLY_DELIVERED => [
                            'metadata' => [
                                'isSystem' => true,
                                'name' => Yii::t('app', 'Partially delivered'),
                                'options' => ['class' => 'btn-danger', 'title' => 'Заказ частично доставлен', 'data-toggle' => 'tooltip-status'],
                            ]
                        ],
                        Order::STATUS_ON_RETURN => [
                            'metadata' => [
                                'isSystem' => true,
                                'name' => Yii::t('app', 'On Return'),
                                'options' => ['class' => 'btn-danger', 'title' => 'Заказ возвращается отправителю', 'data-toggle' => 'tooltip-status'],
                            ]
                        ],
                    ],
                ],
                Order::STATUS_READY_DELIVERY => [
                    'label' => Yii::t('app', 'Ready Delivery'),
                    'transition' => [
                        Order::STATUS_DELIVERED => [
                            'metadata' => [
                                'isSystem' => true,
                                'name' => Yii::t('app', 'Delivered'),
                                'options' => ['class' => 'btn-success', 'title' => 'Заказ доставлен покупателю', 'data-toggle' => 'tooltip-status'],
                            ]
                        ],
                        Order::STATUS_CANCELED_AT_DELIVERY => [
                            'metadata' => [
                                'isSystem' => true,
                                'name' => Yii::t('app', 'Canceled at delivery'),
                                'options' => ['class' => 'btn-danger', 'title' => 'Отмена при доставке', 'data-toggle' => 'tooltip-status'],
                            ]
                        ],
                        Order::STATUS_PARTIALLY_DELIVERED => [
                            'metadata' => [
                                'isSystem' => true,
                                'name' => Yii::t('app', 'Partially delivered'),
                                'options' => ['class' => 'btn-danger', 'title' => 'Заказ частично доставлен', 'data-toggle' => 'tooltip-status'],
                            ]
                        ],
                        Order::STATUS_ON_RETURN => [
                            'metadata' => [
                                'isSystem' => true,
                                'name' => Yii::t('app', 'On Return'),
                                'options' => ['class' => 'btn-danger', 'title' => 'Заказ возвращается отправителю', 'data-toggle' => 'tooltip-status'],
                            ]
                        ],
                        // Todo нужно подумать о целесообразности
                        Order::STATUS_IN_DELIVERY => [
                            'metadata' => [
                                'isSystem' => true,
                                'name' => Yii::t('app', 'In delivery'),
                                'options' => ['class' => 'btn-success', 'title' => 'Заказ перешел в доставку', 'data-toggle' => 'tooltip-status'],
                            ]
                        ],
                    ],
                ],
                Order::STATUS_ON_RETURN => [
                    'label' => Yii::t('app', 'On Return'),
                    'transition' => [
                        Order::STATUS_RETURNED => [
                            'metadata' => [
                                'isSystem' => true,
                                'name' => Yii::t('app', 'Returned'),
                                'options' => ['class' => 'btn-danger', 'title' => 'Заказ возвращен отправителю', 'data-toggle' => 'tooltip-status'],
                            ]
                        ],
                        Order::STATUS_DELIVERED => [
                            'metadata' => [
                                'isSystem' => true,
                                'name' => Yii::t('app', 'Delivered'),
                                'options' => ['class' => 'btn-success', 'title' => 'Заказ доставлен покупателю', 'data-toggle' => 'tooltip-status'],
                            ]
                        ],
                        // Todo нужно подумать о целесообразности
                        Order::STATUS_IN_DELIVERY => [
                            'metadata' => [
                                'isSystem' => true,
                                'name' => Yii::t('app', 'In delivery'),
                                'options' => ['class' => 'btn-success', 'title' => 'Заказ перешел в доставку', 'data-toggle' => 'tooltip-status'],
                            ]
                        ],
                    ],
                ],
                Order::STATUS_PARTIALLY_DELIVERED => [
                    'label' => Yii::t('app', 'Partially delivered'),
                    'transition' => [],
                ],
                Order::STATUS_DELIVERED => [
                    'label' => Yii::t('app', 'Delivered'),
                    'transition' => [],
                ],
                Order::STATUS_CANCELED_AT_DELIVERY => [
                    'label' => Yii::t('app', 'Canceled at delivery'),
                    'transition' => [],
                ],
                Order::STATUS_CANCELED => [
                    'label' => Yii::t('app', 'Canceled'),
                    'transition' => [],
                ],
                Order::STATUS_RETURNED => [
                    'label' => Yii::t('app', 'Returned'),
                    'transition' => [],
                ],
            ]
        ];
    }

}