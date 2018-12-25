<?php
namespace app\models;

use app\models\search\OrderSearch;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%report}}".
 *
 * @property int $id
 * @property string $dispatch_number
 * @property string $carrier_key
 * @property int $report_id
 * @property float $sum
 * @property string $text
 * @property string $type
 * @property string $name
 * @property int $created_at
 * @property int $updated_at
 */
class Report extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%report}}';
    }

    /**
     * @param int $reportId
     * @param string $dispatchNumber
     * @param string $carrierKey
     * @param float $sum
     * @param string $text
     * @param string $type
     * @param string $name
     * @param array $config
     */
    public function __construct(
        int $reportId,
        string $dispatchNumber,
        string $carrierKey,
        float $sum,
        string $text,
        string $type,
        string $name,
        array $config = []
    ) {
        $this->report_id = $reportId;
        $this->dispatch_number = $dispatchNumber;
        $this->sum = $sum;
        $this->carrier_key = $carrierKey;
        $this->text = $text;
        $this->type = $type;
        $this->name = $name;

        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array(
            TimestampBehavior::className(),
        );
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [['text', 'dispatch_number', 'carrier_key', 'type', 'name'], 'string'],
            [['sum'], 'number'],
            [['report_id'], 'integer'],
        ];

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }

    /**
     * @param OrderSearch $data
     * @param bool $withProducts
     * @return array
     */
    public static function getReportData(OrderSearch $data, bool $withProducts = false)
    {
        $orders = Order::find()
            ->joinWith(['delivery'])
            ->where(['!=', 'order.status', (new Order)->getWorkflowStatusId(Order::STATUS_CANCELED)]);

        if ($data->shop_id) {
            $orders->andWhere(['order.shop_id' => $data->shop_id]);
        }
        if ($data->carrier_key) {
            $orders->andWhere(['order_delivery.carrier_key' => $data->carrier_key]);
        }
        if ($data->type) {
            $orders->andWhere(['order_delivery.type' => $data->type]);
        }
        if ($data->date_period) {
            $orders->andWhere(['>', 'order.created_at', $data->date_period[0]]);
            $orders->andWhere(['<', 'order.created_at', $data->date_period[1]]);
        }
        $reportData = $orders->all();

        $result = [];
        $result[] = [
            'ID Fastery',
            'ID ИМ',
            'ID магазина',
            'Название магазина',
            'Адрес склада',
            'Дата создания',
            'Статус заказа',
            'Статус по версии СД',
            'Дата статуса',
            'Стоимость товаров',
            'Количество товаров',
            'Название товара',
            'Количество',
            'Вес',
            'Цена',
            'Оценочная стоимость',
            'Наложенный платеж',
            'Сумма доставки для покпателя',
            'Сумма доставки для ИМ',
            'Фактическая стоимость',
            'СД',
            'Метод доставки',
            'ФИО',
            'Email',
            'Телефон',
            'Адрес доставки',
            'Адрес пункта'
        ];
        /** @var $order Order */
        foreach ($reportData as $order) {
            $deliveryStatus = null;
            if ($order->deliveryStatuses && count($order->deliveryStatuses)) {
                $deliveryStatus = $order->deliveryStatuses[count($order->deliveryStatuses) - 1];
            }


            if ($data->isWithProducts && $order->orderProducts) {
                /** @var $product OrderProduct */
                foreach ($order->orderProducts as $product) {
                    $result[] = [
                        $order->id,
                        $order->shop_order_number,
                        $order->shop_id,
                        $order->shop ? $order->shop->name : null,
                        $order->warehouse ? $order->warehouse->address->full_address : null,
                        date('d.m.Y', $order->created_at),
                        $order->getWorkflowStatusName($order->status),
                        $deliveryStatus ? $deliveryStatus->status : null,
                        $deliveryStatus ? date('d.m.Y', $deliveryStatus->created_at) : null,
                        $order->getCost(),
                        $order->getProductsCount(),
                        $product->name,
                        $product->quantity,
                        $product->weight / 1000,
                        $product->price,
                        $product->accessed_price,
                        $order->getCodCost(),
                        $order->delivery ? $order->delivery->cost : null,
                        $order->delivery ? $order->delivery->original_cost : null,
                        $order->delivery ? $order->getRealDeliveryCost() : null,
                        $order->delivery ? $order->delivery->getDeliveryName() : null,
                        $order->delivery ? $order->delivery->getDeliveryTypeName() : null,
                        $order->fio,
                        $order->email,
                        $order->phone,
                        $order->address ? $order->address->full_address : null,
                        ($order->delivery && $order->delivery->point) ? $order->delivery->point->address : null
                    ];
                }
            } else {
                $result[] = [
                    $order->id,
                    $order->shop_order_number,
                    $order->shop_id,
                    $order->shop ? $order->shop->name : null,
                    $order->warehouse ? $order->warehouse->address->full_address : null,
                    date('d.m.Y', $order->created_at),
                    $order->getWorkflowStatusName($order->status),
                    $deliveryStatus ? $deliveryStatus->status : null,
                    $deliveryStatus ? date('d.m.Y', $deliveryStatus->created_at) : null,
                    $order->getCost(),
                    $order->getProductsCount(),
                    null,
                    null,
                    null,
                    null,
                    null,
                    $order->getCodCost(),
                    $order->delivery ? $order->delivery->cost : null,
                    $order->delivery ? $order->delivery->original_cost : null,
                    $order->delivery ? $order->getRealDeliveryCost() : null,
                    $order->delivery ? $order->delivery->getDeliveryName() : null,
                    $order->delivery ? $order->delivery->getDeliveryTypeName() : null,
                    $order->fio,
                    $order->email,
                    $order->phone,
                    $order->address ? $order->address->full_address : null,
                    ($order->delivery && $order->delivery->point) ? $order->delivery->point->address : null
                ];
            }
        }
        unset($reportData);
        return $result;

    }

    /**
     * @param OrderSearch $data
     * @return array
     */
    public static function getDeviationData(OrderSearch $data)
    {
        $orders = Order::find()
            ->joinWith(['delivery'])
            ->where(['!=', 'order.status', (new Order)->getWorkflowStatusId(Order::STATUS_CANCELED)]);

        if ($data->shop_id) {
            $orders->andWhere(['order.shop_id' => $data->shop_id]);
        }
        if ($data->carrier_key) {
            $orders->andWhere(['order_delivery.carrier_key' => $data->carrier_key]);
        }
        if ($data->type) {
            $orders->andWhere(['order_delivery.type' => $data->type]);
        }
        if ($data->date_period) {
            $orders->andWhere(['>', 'order.created_at', $data->date_period[0]]);
            $orders->andWhere(['<', 'order.created_at', $data->date_period[1]]);
        }
        $reportData = $orders->all();

        $result = [];
        $result[] = [
            'ID Fastery',
            'ID ИМ',
            'ID магазина',
            'Название магазина',
            'Стоимость доставки',
            'Расчетная стоимость доставки',
            'Фактическая стоимость',
            'Процент отклонения'
        ];
        /** @var $order Order */
        foreach ($reportData as $order) {

            $deviation = (($order->delivery->real_cost * Yii::$app->params['fasteryExtraCharge'] - $order->delivery->original_cost) / $order->delivery->original_cost) * 100;
            $result[] = [
                $order->id,
                $order->shop_order_number,
                $order->shop_id,
                $order->shop ? $order->shop->name : null,
                $order->delivery->cost,
                $order->delivery->original_cost,
                str_replace('.', ',', round($order->delivery->real_cost * Yii::$app->params['fasteryExtraCharge'], 2)),
                round($deviation, 2) . '%'
            ];
        }
        unset($reportData);
        return $result;

    }

    /**
     * @param int $order_id
     * @return float
     */
    public static function getCdekRealCost(int $order_id): float
    {
        $query = Order::findBySql('SELECT SUM(sum) as cost 
                FROM (
                  SELECT * FROM (
                    SELECT * 
                      FROM report 
                      WHERE carrier_key = \'cdek\'
                        AND report_id = '.$order_id.'
                      ORDER BY id DESC
                    ) as t GROUP BY `report_id`, `dispatch_number`, `name`
                 ) as t2', [])
            ->asArray()->one();

        return $query['cost'];
    }
}
