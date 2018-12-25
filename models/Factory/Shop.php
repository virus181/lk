<?php
namespace app\models\Factory;

use Yii;
use yii\base\UserException;
use yii\db\ActiveRecord;

class Shop extends AbstractFactory
{
    /** @var string */
    protected $tablename = '{{%shop}}';
    /** @var bool */
    private $withWarehouse = false;
    /** @var bool */
    private $fullfilment = false;
    /** @var int */
    private $userId;

    /**
     * @param bool $needToSave
     * @return ActiveRecord
     * @throws UserException
     */
    public function create(bool $needToSave): ActiveRecord
    {
        $attributes = $this->getAttributes();

        $this->model = new \app\models\Shop();
        $this->model->setAttributes($attributes);

        if ($needToSave) {
            if (!$id = $this->save($attributes)) {
                throw new UserException();
            }
            $this->model->id = $id;

            if ($this->userId) {
                (new ShopUser())->setShopId($id)->setUserId($this->userId)->create($needToSave);
            }

            if (!empty($this->attributes['types'])) {
                foreach ($this->attributes['types'] as $type) {
                    (new ShopType([
                        'shop_id' => $this->model->id,
                        'type' => $type
                    ]))->create(true);
                }
            }

            if (!empty($this->attributes['deliveries'])) {
                foreach ($this->attributes['deliveries'] as $deliveryId) {
                    (new ShopDelivery([
                        'shop_id' => $this->model->id,
                        'delivery_id' => $deliveryId
                    ]))->create(true);
                }
            }
        }

        return $this->model;
    }

    /**
     * @param int $userId
     * @return Shop
     */
    public function setUserId(int $userId): Shop
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @param int $status
     * @return Shop
     */
    public function setStatus(int $status): Shop
    {
        $this->attributes['status'] = $status;
        return $this;
    }

    /**
     * @param int $warehouseId
     * @return Shop
     */
    public function setWarehouseId(int $warehouseId): Shop
    {
        $this->attributes['default_warehouse_id'] = $warehouseId;
        return $this;
    }

    /**
     * @param int[] $deliveries
     * @return Shop
     */
    public function setDeliveries(array $deliveries): Shop
    {
        $this->attributes['deliveries'] = $deliveries;
        return $this;
    }

    /**
     * @param string[] $types
     * @return Shop
     */
    public function setTypes(array $types): Shop
    {
        $this->attributes['types'] = $types;
        return $this;
    }

    /**
     * @param int $roundingOff
     * @return Shop
     */
    public function setRoundingOff(int $roundingOff): Shop
    {
        $this->attributes['rounding_off'] = $roundingOff;
        return $this;
    }

    /**
     * @param int $roundingOffPrefix
     * @return Shop
     */
    public function setRoundingOffPrefix(int $roundingOffPrefix): Shop
    {
        $this->attributes['rounding_off_prefix'] = $roundingOffPrefix;
        return $this;
    }

    /**
     * @param int $processDay
     * @return Shop
     */
    public function setProcessDay(int $processDay): Shop
    {
        $this->attributes['process_day'] = $processDay;
        return $this;
    }

    /**
     * @param bool $withWarehouse
     * @return Shop
     */
    public function setWithWarehouse(bool $withWarehouse): Shop
    {
        $this->withWarehouse = $withWarehouse;
        return $this;
    }

    /**
     * @param bool $fullfilment
     * @return Shop
     */
    public function setFullfilment(bool $fullfilment): Shop
    {
        $this->fullfilment = $fullfilment;
        return $this;
    }

    /**
     * @return array
     */
    protected function getAttributes(): array
    {
        $dataProvider = new DataProvider();
        $result = [
            'name' => !empty($this->attributes['name']) ? $this->attributes['name'] : $dataProvider->getSiteName(),
            'phone' => !empty($this->attributes['phone']) ? $this->attributes['phone'] : $dataProvider->getPhone(),
            'url' => !empty($this->attributes['url']) ? $this->attributes['url'] : $dataProvider->getUrl(),
            'status' => !empty($this->attributes['status']) ? $this->attributes['status'] : $dataProvider->getStatus(),
            'rounding_off' => !empty($this->attributes['rounding_off']) ? $this->attributes['rounding_off'] : $dataProvider->getRoundOff(),
            'rounding_off_prefix' => !empty($this->attributes['rounding_off_prefix']) ? $this->attributes['rounding_off_prefix'] : $dataProvider->getRoundOffPrefix(),
            'process_day' => !empty($this->attributes['process_day']) ? $this->attributes['process_day'] : 0,
            'parse_address' => !empty($this->attributes['parse_address']) ? $this->attributes['parse_address'] : 0,
            'default_warehouse_id' => $this->withWarehouse
                ? (new Warehouse())->setWithAddress(true)->setStatus(\app\models\Warehouse::STATUS_ACTIVE)->create(true)->id
                : null,
        ];

        if (!empty($this->attributes['default_warehouse_id'])) {
            $result['default_warehouse_id'] = $this->attributes['default_warehouse_id'];
        }

        if ($this->fullfilment) {
            $result['fulfillment'] = 1;
            $result['additional_id'] = rand(1, 100);
        }

        return $result;
    }
}