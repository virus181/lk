<?php
namespace app\models\Factory;

use Yii;
use yii\base\UserException;
use yii\db\ActiveRecord;

class ShopUser extends AbstractFactory
{
    /** @var string */
    protected $tablename = '{{%user_shop}}';

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
     * @param int $shopId
     * @return ShopUser
     */
    public function setShopId(int $shopId): ShopUser
    {
        $this->attributes['shop_id'] = $shopId;
        return $this;
    }

    /**
     * @param int $userId
     * @return ShopUser
     */
    public function setUserId(int $userId): ShopUser
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
            'shop_id' => $this->attributes['shop_id'],
            'user_id' => $this->attributes['user_id'],
        ];

        return $result;
    }
}