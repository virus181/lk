<?php

namespace app\behaviors;

use yii\base\InvalidConfigException;
use yii\base\BaseObject;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;

class ProductSaver extends BaseObject implements SaverInterface
{
    /** @var array|string */
    public $findByAttribute = ['barcode', 'shop_id'];


    /** @var BaseActiveRecord */
    protected $_model;

    /** @var BaseActiveRecord */
    protected $_owner;


    public function __construct(ActiveRecord $model, $config = [])
    {
        $this->_model = $model;
        parent::__construct($config);
    }

    /**
     * @return BaseActiveRecord
     */
    public function getModel()
    {
        return $this->_model;
    }

    /**
     * @param $model BaseActiveRecord
     */
    public function setModel($model)
    {
        $this->_model = $model;
    }

    public function getOwner()
    {
        return $this->_owner;
    }

    public function setOwner($owner)
    {
        $this->_owner = $owner;
    }


    /**
     * @param bool $validate
     * @return ActiveRecord
     * @throws InvalidConfigException
     */
    public function save($validate = false)
    {
        if (!$this->_model instanceof ActiveRecord) {
            throw new InvalidConfigException('$model must be instance of ActiveRecord');
        }

        /** @var ActiveRecord|Object $originalModel */
        $originalModel = $this->_model;
        $originalModel->loadDefaultValues();

        $condition = [];
        if (is_array($this->findByAttribute)) {
            foreach ($this->findByAttribute as $findByAttribute) {
                if ($originalModel->{$findByAttribute} !== null && $originalModel->{$findByAttribute} !== '') {
                    $condition[$findByAttribute] = $originalModel->{$findByAttribute};
                } else {
                    $originalModel->save($validate);
                    return $originalModel;
                }
            }
        } else {
            if ($originalModel->{$this->findByAttribute} !== null && $originalModel->{$findByAttribute} !== '') {
                $condition[$this->findByAttribute] = $originalModel->{$this->findByAttribute};
            } else {
                $originalModel->save($validate);
                return $originalModel;
            }
        }

        if ($originalModel->isNewRecord && ($model = $originalModel::findOne($condition))) {

            foreach ($model->getBehaviors() as $behavior) {
                /** @var $behavior RelationSaveBehavior */
                if ($behavior instanceof RelationSaveBehavior) {
                    foreach ($behavior->relations as $name => $config) {
                        if (is_array($config)) {
                            $valueName = $config['value'];
                        } else {
                            $valueName = $config;
                        }

                        $model->{'set' . ucfirst($valueName)}($originalModel->{$valueName});
                    }
                }
            }

            foreach (get_object_vars($originalModel) as $name => $value) {
                $model->{$name} = $value;
            }

            $model->setAttributes($originalModel->toArray());
            return $model;
        } elseif ($originalModel->isNewRecord) {
            $originalModel->save($validate);
        }


        return $originalModel;
    }
}