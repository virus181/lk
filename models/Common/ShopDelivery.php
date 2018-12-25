<?php
namespace app\models\Common;

use app\models\Delivery;
use app\models\Helper\Date;
use Yii;
use yii\base\Model;
use yii\base\UserException;

class ShopDelivery extends Model
{
    /** @var int */
    private $shopId;

    /** @var string */
    private $carrierKey;

    /** @var \app\models\ShopDelivery */
    private $shopDelivery;

    /**
     * @param int $shopId
     * @param string $carrierKey
     * @param array $config
     */
    public function __construct(
        int $shopId,
        string $carrierKey,
        array $config = []
    ) {
        $this->shopId = $shopId;
        $this->carrierKey = $carrierKey;

        $this->shopDelivery = Yii::$app->cache->getOrSet(
            [$shopId, $carrierKey],
            function () use ($shopId, $carrierKey) {
                $delivery = (new Delivery())->getDeliveryByCarrierKey($carrierKey);
                if (!empty($delivery)) {
                    return \app\models\ShopDelivery::find()->where([
                        'shop_id' => $shopId,
                        'delivery_id' => $delivery->id
                    ])->one();
                }
                return null;
            },
            3600
        );

        parent::__construct($config);
    }

    /**
     * @return string
     * @throws UserException
     */
    public function getPickupTimeStart(): string
    {
        if (!$this->shopDelivery) {
            throw new UserException();
        }
        return (new Date(time()))->setFormat('H:i')->getTime(0, $this->shopDelivery->pickup_time_start);
    }

    /**
     * @return string
     * @throws UserException
     */
    public function getPickupTimeEnd(): string
    {
        if (!$this->shopDelivery) {
            throw new UserException();
        }
        return (new Date(time()))->setFormat('H:i')->getTime(0, $this->shopDelivery->pickup_time_end);
    }
}