<?php declare(strict_types=1);
namespace app\components\Clients\Accounting\Mocks;

class Registry
{
    public function getLatestRegistry(): array
    {
        return [
            [
                'last_update' => '2018-09-24 21:05:51',
                'reestr'      => [
                    'reest_num'  => '00000003614',
                    'reest_date' => '2018-09-20 17:05:51',
                    'reest_name' => 'Отчет 0004167А_20180920_102658_472',
                    'delivery'   => '7',
                ],
                'plat'        => [
                    'plat_num'    => '0000-003493',
                    'plat_date'   => '2018-09-24 21:05:51',
                    'plat_status' => 1,
                    'plat_summ'   => 8978.36,
                ],
                'orders'      => [
                    [
                        'order_id'         => 123,
                        'order_total_summ' => 5060,
                        'order_agent'      => 85.12,
                        'order_agent_fast' => 124,
                        'order_dost'       => 18.60,
                        'order_dost_fast'  => 18.60,
                        'order_summ'       => 4748.21,
                    ],
                    [
                        'order_id'         => 124,
                        'order_total_summ' => 5060,
                        'order_agent'      => 85.12,
                        'order_agent_fast' => 124,
                        'order_dost'       => 98.90,
                        'order_dost_fast'  => 18.60,
                        'order_summ'       => 4748.21,
                    ]
                ],
            ],
            [
                'last_update' => '2018-09-25 21:05:51',
                'reestr'      => [
                    'reest_num'  => '00000003616',
                    'reest_date' => '2018-09-20 17:05:51',
                    'reest_name' => 'Отчет 0004167А_20180920_102658_473',
                    'delivery'   => '10',
                ],
                'schet'       => [
                    'schet_num'    => '0000-003492',
                    'schet_date'   => '2018-09-24 21:05:51',
                    'schet_status' => 0,
                    'schet_summ'   => 9900.36,
                ],
                'orders'      => [
                    [
                        'order_id'         => 125,
                        'order_total_summ' => 5060,
                        'order_agent'      => 85.12,
                        'order_agent_fast' => 124,
                        'order_dost'       => 18.60,
                        'order_dost_fast'  => 18.60,
                        'order_summ'       => 4748.21,
                    ],
                    [
                        'order_id'         => 126,
                        'order_total_summ' => 5060,
                        'order_agent'      => 85.12,
                        'order_agent_fast' => 124,
                        'order_dost'       => 18.60,
                        'order_dost_fast'  => 18.60,
                        'order_summ'       => 4748.21,
                    ],
                ]
            ],
            [
                'last_update' => '2018-09-26 21:05:51',
                'reestr'      => [
                    'reest_num'  => '00000003616',
                    'reest_date' => '2018-09-20 17:05:51',
                    'reest_name' => 'Отчет 0004167А_20180920_102658_473',
                    'delivery'   => '10',
                ],
                'schet'       => [
                    'schet_num'    => '0000-003491',
                    'schet_date'   => '2018-09-24 21:05:51',
                    'schet_status' => 1,
                    'schet_summ'   => 8978.36,
                ],
                'orders'      => [
                    [
                        'order_id'         => 127,
                        'order_total_summ' => 5060,
                        'order_agent'      => 85.12,
                        'order_agent_fast' => 124,
                        'order_dost'       => 18.60,
                        'order_dost_fast'  => 18.60,
                        'order_summ'       => 4748.21,
                    ],
                    [
                        'order_id'         => 172,
                        'order_total_summ' => 5060,
                        'order_agent'      => 85.12,
                        'order_agent_fast' => 124,
                        'order_dost'       => 18.60,
                        'order_dost_fast'  => 18.60,
                        'order_summ'       => 4748.21,
                    ],
                ]
            ],
        ];
    }
}