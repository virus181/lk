<?php

namespace app\components;

use Yii;
use yii\base\Component;
use yii\base\ErrorException;
use app\delivery\boxberry\Parsel\Lists;
use app\delivery\boxberry\Parsel\Send;
use yii\log\Logger;

class Delivery extends Component
{
    /**
     * Формирование актов BoxBerry
     */
    public function formBoxberryActs()
    {
        $idList = json_decode((new Lists())->exec(), true);

        try {
            if (!isset($idList['ImIds'])) {
                throw new ErrorException('Список ID пустой');
            }

            (new Send(explode(',', $idList['ImIds'])))->exec();
            Yii::info('Список ID: ' . $idList['ImIds'], 'cron');

        } catch (\Exception $e) {
            Yii::error('При попытке сформировать акт Боксберри произошла ошибка. ' . $e->getMessage(), 'cron');
            Yii::$app->slack->send('Form boxberry act', ':thumbs_up:', [
                [
                    'fallback' => 'Log message',
                    'color' => Yii::$app->slack->getLevelColor(Logger::LEVEL_ERROR),
                    'fields' => [
                        [
                            'title' => 'Application ID',
                            'value' => Yii::$app->id,
                            'short' => true,
                        ],
                        [
                            'title' => 'Error',
                            'value' => $e->getMessage(),
                            'short' => true,
                        ]
                    ],
                ],
            ]);

        }

    }
}