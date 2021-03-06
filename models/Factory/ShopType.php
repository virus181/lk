<?php
namespace app\models\Factory;

use Yii;
use yii\base\UserException;
use yii\db\ActiveRecord;

class ShopType extends AbstractFactory
{
    /** @var string */
    protected $tablename = '{{%shop_type}}';

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
            if (!$id = $this->save($attributes)) {
                throw new UserException();
            }
            $this->model->id = $id;
        }

        return $this->model;
    }


    /**
     * @return array
     */
    protected function getAttributes(): array
    {
        $result = [
            'shop_id' => $this->attributes['shop_id'],
            'type' => $this->attributes['type'],
        ];

        return $result;
    }
}