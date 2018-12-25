<?php
namespace app\delivery\own\Calculator;

use yii\httpclient\Client;
use app\models\Order;
use app\models\Rate;
use Yii;

class Calculate
{
    /** @var string */
    public $fiasTo;

    /** @var int */
    public $weight;

    /** @var float */
    public $price;

    /** @var int */
    public $shopId;

    /**
     * @param \app\models\Common\Calculator $calculator
     */
    public function prepare(\app\models\Common\Calculator $calculator)
    {
        $calculator->getToAddress() && $this->fiasTo = $calculator->getToAddress()->getCityFiasId();
        $this->weight = $calculator->weight;
        $this->price = $calculator->cost;
        $this->shopId = $calculator->shop_id;
    }

    /**
     * Каулькулятор стоимости и сроков доставки
     *
     * @param int|bool $cache
     * @return array
     */
    public function calculate($cache = 3600): array
    {
        if (!$this->fiasTo || !$this->shopId) {
            return [];
        }

        if ($cache === false) {
            return $this->exec();
        } else {
            return Yii::$app->cache->getOrSet([
                $this->fiasTo,
                $this->weight,
                $this->price,
                $this->shopId
            ], function () {
                return $this->exec();
            }, $cache);
        }
    }

    /**
     * Выполнить расчет стоимости
     *
     * @return array
     */
    private function exec(): array
    {
        $query = Rate::find()
            ->joinWith(['inventories', 'address'])
            ->where(['<=', 'rate_inventory.weight_from', $this->weight])
            ->andWhere(['>', 'rate_inventory.weight_to', $this->weight])
            ->andWhere(['<=', 'rate_inventory.price_from', $this->price])
            ->andWhere(['>', 'rate_inventory.price_to', $this->price])
            ->andWhere(['shop_id' => $this->shopId])
            ->andWhere(['OR', ['fias_to' => $this->fiasTo], ['fias_to' => null]])
            ->groupBy(['rate_inventory.rate_id']);

        return $query->asArray()->all();
    }

    /**
     * @return string
     */
    public static function className(): string
    {
        return get_called_class();
    }
}