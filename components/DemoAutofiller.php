<?php
namespace app\components;

use app\models\Order;
use app\models\Shop;
use app\models\ShopType;
use app\models\User;
use app\models\Warehouse;
use Exception;
use Yii;
use yii\base\Component;
use yii\base\InvalidParamException;

class DemoAutofiller extends Component
{
    /**
     * @var User
     */
    public $user;

    public function init()
    {
        if (!($this->user instanceof User)) {
            throw new InvalidParamException('$user must be instance of app\\models\\User');
        }
        parent::init();
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function run(): bool
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            /** @var Warehouse $warehouse */
            $warehouse = (new \app\models\Factory\Warehouse())
                ->setUserId($this->user->id)
                ->setWithAddress(true)
                ->setStatus(Warehouse::STATUS_ACTIVE)
                ->create(true);

            /** @var Shop $shop */
            $shop = (new \app\models\Factory\Shop())
                ->setStatus(Shop::STATUS_ACTIVE)
                ->setUserId($this->user->id)
                ->setWarehouseId($warehouse->id)
                ->setTypes(
                    (new ShopType())->getAvailableDeliveryTypes()
                )->setDeliveries(
                    (new \app\models\Delivery())->getActiveDeliveryServiceIds()
                )->create(true);

            $statuses = [
                Order::STATUS_CREATED,
                Order::STATUS_IN_DELIVERY,
                Order::STATUS_DELIVERED,
                Order::STATUS_CONFIRMED,
                Order::STATUS_READY_DELIVERY,
                Order::STATUS_CREATED,
                Order::STATUS_IN_DELIVERY,
                Order::STATUS_DELIVERED,
                Order::STATUS_CONFIRMED,
                Order::STATUS_READY_DELIVERY,
            ];

            for ($i = 0; $i < 10; $i++) {
                (new \app\models\Factory\Order())
                    ->setStatus($statuses[$i])
                    ->setWithAddress(true)
                    ->setWithDelivery(true)
                    ->setWithProducts(true)
                    ->setShopId($shop->id)
                    ->setWarehouseId($warehouse->id)
                    ->setWithDimensions(true)
                    ->create(true);
            }
            $transaction->commit();
            return true;
        } catch (Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}