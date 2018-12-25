<?php
namespace app\models\Factory;

use Yii;
use yii\base\UserException;
use yii\db\ActiveRecord;

class OrderDelivery extends AbstractFactory
{
    /** @var string */
    protected $tablename = '{{%order_delivery}}';
    /** @var array */
    private $newAttributes;

    /**
     * @param bool $needToSave
     * @return ActiveRecord
     * @throws UserException
     */
    public function create(bool $needToSave): ActiveRecord
    {
        $attributes = !empty($this->newAttributes) ? $this->newAttributes : $this->getAttributes();

        $this->model = new \app\models\OrderDelivery();
        $this->model->setAttributes($attributes);

        if ($needToSave) {
            if (!$id = $this->save($attributes)) {
                throw new UserException();
            }
            $this->model->id = $id;
        }

        return $this->model;
    }

    /**
     * @param int $orderId
     * @return OrderDelivery
     */
    public function setOrderId(int $orderId): OrderDelivery
    {
        $this->attributes['order_id'] = $orderId;
        $this->newAttributes['order_id'] = $orderId;
        return $this;
    }

    /**
     * @return OrderDelivery
     */
    public function prepare(): OrderDelivery
    {
        $this->newAttributes = $this->getAttributes();
        return $this;
    }

    /**
     * @return null|string
     */
    public function getCarrierKey(): ?string
    {
        return $this->newAttributes['carrier_key'] ?? null;
    }

    /**
     * @return array
     */
    protected function getAttributes(): array
    {
        $dataProvider = new DataProvider();
        $attributes = $dataProvider->getOrderDelivery();

        $minTerm = $dataProvider->getRandomNumber(1, 3);
        $maxTerm = $minTerm + $dataProvider->getRandomNumber(1, 2);

        if (!empty($this->attributes['order_id'])) {
            $attributes['order_id'] = $this->attributes['order_id'];
        }

        $attributes['min_term'] = $minTerm;
        $attributes['max_term'] = $maxTerm;
        $attributes['cost'] = $dataProvider->getRandomNumber(190, 500);
        $attributes['original_cost'] = $dataProvider->getRandomNumber(190, 500);
        $attributes['pickup_date'] = $dataProvider->getTime();
        $attributes['delivery_date'] = $dataProvider->getTime($minTerm);

        return $attributes;
    }
}