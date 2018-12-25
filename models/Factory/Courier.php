<?php
namespace app\models\Factory;

use yii\base\UserException;
use yii\db\ActiveRecord;

class Courier extends AbstractFactory
{
    /** @var string */
    protected $tablename = '{{%courier}}';

    /**
     * @param bool $needToSave
     * @return ActiveRecord
     * @throws UserException
     */
    public function create(bool $needToSave): ActiveRecord
    {
        $attributes = $this->getAttributes();

        $this->model = new \app\models\Address();
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
     * @param string $carrierKey
     * @return Courier
     */
    public function setCarrierKey(string $carrierKey): Courier
    {
        $this->attributes['carrier_key'] = $carrierKey;
        return $this;
    }

    /**
     * @param int $warehouseId
     * @return Courier
     */
    public function setWarehouseId(int $warehouseId): Courier
    {
        $this->attributes['warehouse_id'] = $warehouseId;
        return $this;
    }

    /**
     * @return array
     */
    protected function getAttributes(): array
    {
        $dataProvider = new DataProvider();
        $result = [
            'carrier_key' => !empty($this->attributes['carrier_key']) ? $this->attributes['carrier_key'] : null,
            'warehouse_id' => !empty($this->attributes['warehouse_id']) ? $this->attributes['warehouse_id'] : null,
            'pickup_date' => !empty($this->attributes['pickup_date']) ? $this->attributes['pickup_date'] : $dataProvider->getTime(),
            'courier_call' => 1,
            'class_name_provider' => 'app\delivery\apiship\Delivery',
        ];
        return $result;
    }
}