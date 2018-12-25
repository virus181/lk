<?php
namespace app\models\Factory;

use yii\base\UserException;
use yii\db\ActiveRecord;

class Product extends AbstractFactory
{
    /** @var string */
    protected $tablename = '{{%product}}';

    /**
     * @param bool $needToSave
     * @return ActiveRecord
     * @throws UserException
     */
    public function create(bool $needToSave): ActiveRecord
    {
        $attributes = $this->getAttributes();

        $this->model = new \app\models\Warehouse();
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
     * @param int $status
     * @return Product
     */
    public function setStatus(int $status): Product
    {
        $this->attributes['status'] = $status;
        return $this;
    }

    /**
     * @param int $shopId
     * @return Product
     */
    public function setShopId(int $shopId): Product
    {
        $this->attributes['shop_id'] = $shopId;
        return $this;
    }

    /**
     * @return array
     */
    protected function getAttributes(): array
    {
        $dataProvider = new DataProvider();
        $price        = $dataProvider->getRandomNumber(100, 2000);
        return [
            'name'              => isset($this->attributes['name']) ? $this->attributes['name'] : $dataProvider->getProductName(),
            'barcode'           => isset($this->attributes['barcode']) ? $this->attributes['barcode'] : $dataProvider->getBarcode(),
            'price'             => isset($this->attributes['price']) ? $this->attributes['price'] : $price,
            'accessed_price'    => isset($this->attributes['accessed_price']) ? $this->attributes['accessed_price'] : $price,
            'weight'            => isset($this->attributes['weight']) ? $this->attributes['weight'] : $dataProvider->getRandomNumber(10, 5000),
            'width'             => isset($this->attributes['width']) ? $this->attributes['width'] : $dataProvider->getRandomNumber(10),
            'height'            => isset($this->attributes['height']) ? $this->attributes['height'] : $dataProvider->getRandomNumber(10),
            'length'            => isset($this->attributes['length']) ? $this->attributes['length'] : $dataProvider->getRandomNumber(10),
            'is_not_reversible' => isset($this->attributes['is_not_reversible']) ? $this->attributes['is_not_reversible'] : $dataProvider->getRandomBoolean(),
            'shop_id'           => isset($this->attributes['shop_id']) ? $this->attributes['shop_id'] : null,
            'count'             => isset($this->attributes['count']) ? $this->attributes['count'] : $dataProvider->getRandomNumber(),
            'status'            => isset($this->attributes['status']) ? $this->attributes['status'] : $dataProvider->getStatus(),
        ];
    }
}