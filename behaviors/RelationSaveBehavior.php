<?php

namespace app\behaviors;

use Closure;
use Yii;
use yii\base\Behavior;
use yii\base\ErrorException;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

class RelationSaveBehavior extends Behavior
{
    const HAS_MANY_TYPE = 'hasMany';
    const HAS_ONE_TYPE = 'hasOne';
    const MANY_MANY_TYPE = 'ManyToMany';

    /** @var  BaseActiveRecord */
    public $owner;

    public $relations = [];

    public $validate = true;

    public $_models;

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'changeRelations',
            ActiveRecord::EVENT_AFTER_UPDATE => 'changeRelations',
            ActiveRecord::EVENT_BEFORE_DELETE => 'changeRelations',
        ];
    }

    /**
     * @throws ErrorException
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function changeRelations()
    {
        if (is_array($ownerPk = $this->owner->getPrimaryKey())) {
            throw new ErrorException('Composite primary keys are not supported');
        }

        foreach ($this->relations as $relationName => $relationConfig) {
            $relation = $this->owner->getRelation($relationName);
            $relationType = self::HAS_MANY_TYPE;
            $saver = null;
            $link = ArrayHelper::getValue($relationConfig, 'link');

            if (is_array($relationConfig)) {
                $relationType = ArrayHelper::getValue($relationConfig, 'type', self::HAS_MANY_TYPE);
                $relationValue = ArrayHelper::getValue($relationConfig, 'value');

                /** @var SaverInterface $saver */
                if (($saverClassName = ArrayHelper::getValue($relationConfig, 'saver')) && !($saver = Yii::createObject($saverClassName)) instanceof SaverInterface) {
                    throw new InvalidConfigException('Saver mus be inplements of SaverInterface');
                }
            } else {
                $relationValue = $relationConfig;
            }

            if ($relationValue === null) {
                throw new InvalidConfigException('Relation value undefined');
            }

            if (($relationModels = ArrayHelper::getValue($this->_models, $relationName)) === null) {
                $relationModels = $this->owner->{$relationValue};
            }

            if ($relationModels !== null) {
                if ($relationModels instanceof BaseActiveRecord) {
                    $relationModels = [$relationModels];
                }
            } else {
                $this->owner->unlinkAll($relationName, true);
                Yii::warning("Empty relation models:\n" . print_r($relationModels, true), __METHOD__);
                continue;
            }


            if ($relationType == self::HAS_MANY_TYPE && $relation->multiple || $relationType == self::HAS_ONE_TYPE) {
                /** @var BaseActiveRecord $relationModel */
                foreach ($relationModels as $relationModel) {
                    if (($transaction = null) && Yii::$app->db->getTransaction() === null) {
                        $transaction = Yii::$app->db->beginTransaction();
                    }
                    try {
                        if ($saver) {
                            $saver->setModel($relationModel);
                            $saver->setOwner($this->owner);
                            $relationModel = $saver->save($this->validate);
                        }

                        if ($link instanceof Closure) {
                            $link($this->owner, $relationModel, $relationName);
                        } else {
                            if ($relationModel->getDirtyAttributes()) {
                                if ($this->validate) {
                                    if ($relationModel->validate() == false) {
                                        Yii::error("Validation error:\n" . print_r($relationModel, true), __METHOD__);
                                    }
                                }

                                if ($relationModel->isNewRecord || $relationModel->dirtyAttributes) {
                                    $relationModel->save($this->validate);
                                }

                                $this->owner->link($relationName, $relationModel);
                            }
                        }

                        if ($transaction && $transaction->isActive) {
                            $transaction->commit();
                        }
                    } catch (Exception $e) {
                        try {
                            if ($transaction && $transaction->isActive) {
                                $transaction->rollBack();
                            }
                        } catch (Exception $e_rollback) {
                            Yii::error($e_rollback, __METHOD__);
                            throw $e_rollback;
                        }
                        throw $e;
                    }
                }
            } else if ($relationType == self::MANY_MANY_TYPE && $relation->multiple && !empty($relation->via)) {
                if (!empty($relationModels)) {
                    $this->owner->unlinkAll($relationName, true);
                }
                /** @var BaseActiveRecord $relationModel */
                foreach ($relationModels as $i => $relationModel) {
                    if (($transaction = null) && Yii::$app->db->getTransaction() === null) {
                        $transaction = Yii::$app->db->beginTransaction();
                    }
                    try {
                        if ($saver) {
                            $saver->setModel($relationModel);
                            $saver->setOwner($this->owner);
                            $relationModel = $saver->save($this->validate);
                        } else {
                            $relationModel->save($this->validate);
                        }

                        if ($relationModel->hasErrors() === false) {
                            if ($link instanceof Closure) {
                                $link($this->owner, $relationModel, $relationName);
                            } else {
                                $this->owner->link($relationName, $relationModel);
                            }
                        } else {
                            Yii::error("Validation error:\n" . print_r($relationModel->errors, true), __METHOD__);
                        }

                        if ($transaction && $transaction->isActive) {
                            $transaction->commit();
                        }
                    } catch (Exception $e) {
                        try {
                            if ($transaction && $transaction->isActive) {
                                $transaction->rollBack();
                            }
                        } catch (Exception $e_rollback) {
                            Yii::error($e_rollback, __METHOD__);
                            throw $e_rollback;
                        }
                        throw $e;
                    }

                    $relationModels[$i] = $relationModel;
                    $this->owner->{$relationName} = $relationModels;
                }
            } else {
                throw new ErrorException('Bad relation');
            }
        }
    }

    public function canSetProperty($name, $checkVars = true)
    {
        return array_key_exists($name, $this->relations) ? true : parent::canSetProperty($name, $checkVars);
    }

    public function canGetProperty($name, $checkVars = true)
    {
        return array_key_exists($name, $this->relations) ? true : parent::canGetProperty($name, $checkVars);
    }

    public function __get($name)
    {
        return ArrayHelper::getValue($this->_models, $name, []);
    }

    public function __set($name, $value)
    {
        $this->_models[$name] = $value;
    }
}