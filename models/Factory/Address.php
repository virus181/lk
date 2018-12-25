<?php
namespace app\models\Factory;

use yii\base\UserException;
use yii\db\ActiveRecord;

class Address extends AbstractFactory
{
    /** @var string */
    protected $tablename = '{{%address}}';
    /** @var string */
    private $fiasId = '';

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
     * @param string $fiasId
     * @return Address
     */
    public function setFiasId(string $fiasId): Address
    {
        $this->fiasId = $fiasId;
        return $this;
    }

    /**
     * @return array
     */
    protected function getAttributes(): array
    {
        $dataProvider = new DataProvider();
        return !empty($this->attributes) ? $this->attributes : $dataProvider->getAddress($this->fiasId);
    }
}