<?php

namespace app\components;

use app\models\Shop;
use Yii;
use yii\base\Component;
use app\components\Clients;
use app\delivery\boxberry\Parsel\Lists;
use app\delivery\boxberry\parsel\Send;
use yii\helpers\ArrayHelper;
use yii\log\Logger;

class Call extends Component
{
    /**
     * Обновление данных о звонках
     */
    public function updateCallList()
    {
        $client = new Clients\Call();

        $shops = ArrayHelper::map(
            Shop::find()->where(['IS NOT', 'additional_id', null])->andWhere(['status' => Shop::STATUS_ACTIVE])->asArray()->all(),
            'id',
            'additional_id'
        );
        $shops[1] = 1002;

        /** @var \app\models\Call $lastCall */
        $lastCall = \app\models\Call::find()->orderBy(['call_id' => SORT_DESC])->one();

        $params = [
            'what=get_calls_for_shop',
            'shop_id='.implode(',', $shops),
            'start_time='. (($lastCall) ? urlencode($lastCall->ring_time) : urlencode(date(DATE_ATOM, 0)))
        ];

        $data = $client->sendRequest([], 'data.py?' . implode('&', $params), 'GET', false);
        foreach ($data->getData() as $item) {
            $call = new \app\models\Call();

            $shopId = array_search($item['cl_shop_lkid'], $shops);
            if (!$shopId || !isset($shops[$shopId])) {
                continue;
            }

            $call->shop_id = $shopId;
            $call->order_id = $item['cl_order'];
            $call->tag = (string) $item['cl_tag'];
            $call->note = trim((string) $item['cl_note']);
            $call->rec_uid = $item['cl_rec_uid'];
            $call->direction = $item['cl_direction'];
            $call->call_id = $item['cl_id'];
            $call->uid = $item['cl_uid'];
            $call->answer_time = $item['cl_answer_time'];
            $call->close_time = $item['cl_close_time'];
            $call->end_time = $item['cl_end_time'];
            $call->ring_time = $item['cl_ring_time'];
            $call->operator_id = $item['cl_operator'];
            if ((int) $item['cl_webloop']) {
                $call->user_id = (int) $item['cl_webloop'];
            }
            $call->operator_name = $item['cl_operator_name'];
            $call->key = $item['cl_rand'];
            $call->client_phone = $item['cl_client_phone'];
            $call->shop_phone = $item['cl_shop_phone'];
            if ($call->save()) {

            } else {
                print_r($call->errors);
            }

        }
    }
}