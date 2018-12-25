<?php
namespace app\models\Factory;

use Yii;
use yii\base\UserException;
use yii\db\ActiveRecord;

class Warehouse extends AbstractFactory
{
    /** @var string */
    protected $tablename = '{{%warehouse}}';
    /** @var bool */
    private $withAddress = false;
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

        $this->model = new \app\models\Warehouse();
        $this->model->setAttributes($attributes);

        if ($needToSave) {
            if (!$id = $this->save($attributes)) {
                throw new UserException();
            }
            $this->model->id = $id;

            if ($this->userId) {
                (new WarehouseUser())->setWarehouseId($id)->setUserId($this->userId)->create($needToSave);
            }
        }

        return $this->model;
    }

    /**
     * @param int $status
     * @return Warehouse
     */
    public function setStatus(int $status): Warehouse
    {
        $this->attributes['status'] = $status;
        return $this;
    }

    /**
     * @param int $userId
     * @return Warehouse
     */
    public function setUserId(int $userId): Warehouse
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @param bool $withAddress
     * @return Warehouse
     */
    public function setWithAddress(bool $withAddress): Warehouse
    {
        $this->withAddress = $withAddress;
        return $this;
    }

    /**
     * @return array
     */
    protected function getAttributes(): array
    {
        $dataProvider = new DataProvider();
        return [
            'name' => !empty($this->attributes['name']) ? $this->attributes['name'] : $dataProvider->getWarehouseName(),
            'contact_fio' => !empty($this->attributes['contact_fio']) ? $this->attributes['contact_fio'] : $dataProvider->getFio(),
            'contact_phone' => !empty($this->attributes['contact_phone']) ? $this->attributes['contact_phone'] : $dataProvider->getPhone(),
            'address_id' => $this->withAddress
                ? (new Address())
                    ->setFiasId('0c5b2444-70a0-4932-980c-b4dc0d3f02b5')
                    ->create(true)
                    ->id
                : null,
            'status' => isset($this->attributes['status']) ? $this->attributes['status'] : $dataProvider->getStatus(),
        ];
    }
}