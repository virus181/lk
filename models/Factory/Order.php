<?php
namespace app\models\Factory;

use yii\base\UserException;
use yii\db\ActiveRecord;

class Order extends AbstractFactory
{
    /** @var bool */
    private $withAddress = false;
    /** @var bool */
    private $withDelivery = false;
    /** @var bool */
    private $withProducts = false;
    /** @var bool */
    private $withDimensions = false;
    /** @var bool */
    private $withShop = false;
    /** @var bool */
    private $withWarehouse = false;

    /** @var string */
    protected $tablename = '{{%order}}';

    /**
     * @param bool $withAddress
     * @return Order
     */
    public function setWithAddress(bool $withAddress): Order
    {
        $this->withAddress = $withAddress;
        return $this;
    }

    /**
     * @param bool $withDelivery
     * @return Order
     */
    public function setWithDelivery(bool $withDelivery): Order
    {
        $this->withDelivery = $withDelivery;
        return $this;
    }

    /**
     * @param bool $withDimensions
     * @return Order
     */
    public function setWithDimensions(bool $withDimensions): Order
    {
        $this->withDimensions = $withDimensions;
        return $this;
    }

    /**
     * @param bool $withShop
     * @return Order
     */
    public function setWithShop(bool $withShop): Order
    {
        $this->withShop = $withShop;
        return $this;
    }

    /**
     * @param bool $withWarehouse
     * @return Order
     */
    public function setWithWarehouse(bool $withWarehouse): Order
    {
        $this->withWarehouse = $withWarehouse;
        return $this;
    }

    /**
     * @param bool $withProducts
     * @return Order
     */
    public function setWithProducts(bool $withProducts): Order
    {
        $this->withProducts = $withProducts;
        return $this;
    }

    /**
     * @param string $shopOrderNumber
     * @return Order
     */
    public function setShopOrderNumber(string $shopOrderNumber): Order
    {
        $this->attributes['shop_order_number'] = $shopOrderNumber;
        return $this;
    }

    /**
     * @param int $shopId
     * @return Order
     */
    public function setShopId(int $shopId): Order
    {
        $this->attributes['shop_id'] = $shopId;
        return $this;
    }

    /**
     * @param int $warehouseId
     * @return Order
     */
    public function setWarehouseId(int $warehouseId): Order
    {
        $this->attributes['warehouse_id'] = $warehouseId;
        return $this;
    }

    /**
     * @param string $paymentMethod
     * @return Order
     */
    public function setPaymentMethod(string $paymentMethod): Order
    {
        $this->attributes['payment_method'] =  $paymentMethod;
        return $this;
    }

    /**
     * @param string $status
     * @return Order
     */
    public function setStatus(string $status): Order
    {
        $this->attributes['status'] = (new \app\models\Order())->getWorkflowStatusId($status);
        return $this;
    }

    /**
     * @param bool $isArchive
     * @return Order
     */
    public function setArchive(bool $isArchive): Order
    {
        $this->attributes['is_archive'] = $isArchive ? 1 : 0;
        return $this;
    }

    /**
     * @param bool $isApi
     * @return Order
     */
    public function setApi(bool $isApi): Order
    {
        $this->attributes['is_api'] = $isApi ? 1 : 0;
        return $this;
    }

    /**
     * @param bool $needToSave
     * @return ActiveRecord
     * @throws UserException
     */
    public function create(bool $needToSave): ActiveRecord
    {
        $attributes = $this->getAttributes();

        $this->model = new \app\models\Order();
        $this->model->setAttributes($attributes);

        if ($this->withAddress) {
            $address = (new Address())->create($needToSave);
            $this->model->address = $address;
            $this->model->address_id = $address->id;
            $attributes['address_id'] = $address->id;
        }

        if ($this->withWarehouse) {
            $warehouse = (new Warehouse())->setWithAddress(true)->create($needToSave);
            $this->model->warehouse_id = $warehouse->id;
            $attributes['warehouse_id'] = $warehouse->id;
        }

        if ($this->withShop) {
            $query = (new Shop());
            if ($this->withWarehouse && $needToSave) {
                $query->setWarehouseId($warehouse->id);
            }
            if ($this->withWarehouse && !$needToSave) {
                $query->setWithWarehouse($this->withWarehouse);
            }
            $shop = $query->create($needToSave);
            $this->model->shop_id = $shop->id;
            $attributes['shop_id'] = $shop->id;
        }

        $orderDelivery = (new OrderDelivery())->prepare();

        if (in_array($attributes['status'], \app\models\Order::STATUS_FINISHED)
        || in_array($attributes['status'], \app\models\Order::STATUS_DELIVERING)) {
            $courier = (new Courier())
                ->setWarehouseId($this->model->warehouse_id)
                ->setCarrierKey($orderDelivery->getCarrierKey())
                ->create($needToSave);
            $this->model->courier_id = $courier->id;
            $attributes['courier_id'] = $courier->id;
        }

        if ($needToSave) {
            if (!$id = $this->save($attributes)) {
                throw new UserException();
            }
            $this->model->id = $id;
        }

        if ($this->withProducts) {
            $product = (new Product())
                ->setShopId($this->model->shop_id)
                ->setStatus(\app\models\Product::STATUS_ACTIVE)
                ->create($needToSave);
            (new OrderProduct())
                ->setProductId($product->id)
                ->setOrderId($this->model->id)
                ->create($needToSave);
        }

        if ($this->withDelivery) {
            $orderDelivery->setOrderId($this->model->id)->create($needToSave);
        }

        return $this->model;
    }

    /**
     * @return array
     */
    protected function getAttributes(): array
    {
        $dataProvider = new DataProvider();

        $result = [
            'fio' => !empty($this->attributes['fio']) ? $this->attributes['fio'] : $dataProvider->getFio(),
            'email' => !empty($this->attributes['email']) ? $this->attributes['email'] : $dataProvider->getEmail(),
            'phone' => !empty($this->attributes['phone']) ? $this->attributes['phone'] : $dataProvider->getPhone(),
            'status' => !empty($this->attributes['status']) ? $this->attributes['status'] : $dataProvider->getOrderStatus(),
            'payment_method' => !empty($this->attributes['payment_method']) ? $this->attributes['payment_method'] : $dataProvider->getPaymentMethod(),
            'shop_order_number' => !empty($this->attributes['shop_order_number']) ? $this->attributes['shop_order_number'] : $dataProvider->getShopOrderNumber(),
        ];

        if (!empty($this->attributes['shop_id'])) {
            $result['shop_id'] = $this->attributes['shop_id'];
        }

        if (!empty($this->attributes['warehouse_id'])) {
            $result['warehouse_id'] = $this->attributes['warehouse_id'];
        }

        if (!empty($this->attributes['is_archive'])) {
            $result['is_archive'] = $this->attributes['is_archive'];
        }

        if (!empty($this->attributes['is_api'])) {
            $result['is_api'] = $this->attributes['is_api'];
        }

        if ($this->withDimensions) {
            $result['weight'] = $dataProvider->getRandomNumber(1000, 3000);
            $result['width'] = $dataProvider->getRandomNumber(10, 99);
            $result['height'] = $dataProvider->getRandomNumber(10, 99);
            $result['length'] = $dataProvider->getRandomNumber(10, 99);
        }

        if (!in_array($result['status'], \app\models\Order::STATUS_NOT_DELIVEERY)) {
            $result['provider_number'] = $dataProvider->getRandomNumber(1, 999999);
            $result['dispatch_number'] = $dataProvider->getRandomNumber(1, 999999);
        }

        return $result;
    }
}