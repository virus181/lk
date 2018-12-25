<?php
namespace app\models\Factory;

use Yii;
use yii\base\UserException;
use yii\db\ActiveRecord;

abstract class AbstractFactory
{
    protected $tablename = '';

    /** @var ActiveRecord */
    protected $model;

    /** @var array */
    protected $attributes;

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    /**
     * @param array $attributes
     * @param bool $isNeedCreatedTime
     * @return int
     */
    public function save(array $attributes, bool $isNeedCreatedTime = true): int
    {
        // Тут нужно сохранить принудительно без валидации
        $data = $attributes;
        if ($isNeedCreatedTime) {
            $data = array_merge($attributes, [
                'created_at' => time(),
                'updated_at' => time(),
            ]);
        }

        $query = Yii::$app->db;
        $result = $query
            ->createCommand()
            ->insert($this->tablename, $data)
            ->execute();

        if ($result > 0) {
            $id = $query->getLastInsertID();
        }

        return $id ?? 0;
    }

    /**
     * @param bool $needToSave
     * @return ActiveRecord
     * @throws UserException
     */
    abstract public function create(bool $needToSave): ActiveRecord;

    /**
     * @return array
     */
    abstract protected function getAttributes(): array;
}