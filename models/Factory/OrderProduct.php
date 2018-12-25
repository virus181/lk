<?php
namespace app\models\Factory;

use Yii;
use yii\base\UserException;
use yii\db\ActiveRecord;

class OrderProduct extends AbstractFactory
{
    /** @var string */
    protected $tablename = '{{%order_product}}';

    /**
     * @param bool $needToSave
     * @return ActiveRecord
     * @throws UserException
     */
    public function create(bool $needToSave): ActiveRecord
    {
        $attributes = $this->getAttributes();

        $this->model = new \app\models\OrderProduct();
        $this->model->setAttributes($attributes);

        if ($needToSave) {
            $this->save($attributes, false);
        }

        return $this->model;
    }

    /**
     * @param int $orderId
     * @return OrderProduct
     */
    public function setOrderId(int $orderId): OrderProduct
    {
        $this->attributes['order_id'] = $orderId;
        return $this;
    }

    /**
     * @param int $productId
     * @return OrderProduct
     */
    public function setProductId(int $productId): OrderProduct
    {
        $this->attributes['product_id'] = $productId;
        return $this;
    }

    /**
     * @return array
     */
    protected function getAttributes(): array
    {
        $dataProvider = new DataProvider();

        $product = null;
        if (!empty($this->attributes['product_id'])) {
            $product = \app\models\Product::findOne($this->attributes['product_id']);
        }

        $attributes = [
            'quantity' => 1,
            'price' => $product ? $product->price : $dataProvider->getRandomNumber(1000, 3000),
            'accessed_price' => $product ? $product->price : $dataProvider->getRandomNumber(1000, 3000),
            'name' => $product ? $product->name : $dataProvider->getProductName(),
            'is_not_reversible' => $product ? $product->is_not_reversible : $dataProvider->getRandomNumber(0, 1),
            'weight' => $product ? $product->weight : $dataProvider->getRandomNumber(10, 99),
            'width' => $product ? $product->width : $dataProvider->getRandomNumber(10, 99),
            'length' => $product ? $product->length : $dataProvider->getRandomNumber(10, 99),
            'height' => $product ? $product->height : $dataProvider->getRandomNumber(10, 99),
        ];

        if (!empty($this->attributes['product_id'])) {
            $attributes['product_id'] = $this->attributes['product_id'];
        }

        if (!empty($this->attributes['order_id'])) {
            $attributes['order_id'] = $this->attributes['order_id'];
        }

        return $attributes;
    }
}