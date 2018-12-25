<?php
namespace app\models\Factory;

use Yii;
use yii\base\UserException;
use yii\db\ActiveRecord;

class WarehouseUser extends AbstractFactory
{
    /** @var string */
    protected $tablename = '{{%user_warehouse}}';

    /**
     * @param bool $needToSave
     * @return ActiveRecord
     * @throws UserException
     */
    public function create(bool $needToSave): ActiveRecord
    {
        $attributes = $this->getAttributes();

        $this->model = new \app\models\ShopType();
        $this->model->setAttributes($attributes);

        if ($needToSave) {
            if (!$id = $this->save($attributes, false)) {
                throw new UserException();
            }
            $this->model->id = $id;
        }

        return $this->model;
    }

    /**
     * @param int $warehouseId
     * @return WarehouseUser
     */
    public function setWarehouseId(int $warehouseId): WarehouseUser
    {
        $this->attributes['warehouse_id'] = $warehouseId;
        return $this;
    }

    /**
     * @param int $userId
     * @return WarehouseUser
     */
    public function setUserId(int $userId): WarehouseUser
    {
        $this->attributes['user_id'] = $userId;
        return $this;
    }

    /**
     * @return array
     */
    protected function getAttributes(): array
    {
        $result = [
            'warehouse_id' => $this->attributes['warehouse_id'],
            'user_id' => $this->attributes['user_id'],
        ];

        return $result;
    }
}