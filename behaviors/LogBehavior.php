<?php

namespace app\behaviors;

use app\models\Log;
use app\models\User;
use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;

class LogBehavior extends Behavior
{
    const ORDER_PRODUCT_CLASS = 'OrderProduct';
    const ORDER_DELIVERY_CLASS = 'OrderDelivery';
    const ADDRESS_CLASS = 'Address';
    const CACHE_TIME = 86400;

    const ORDER_DELIVERY_NO_LOGGER_FIELDS = [
        'tariff_id',
        'created_at',
        'updated_at',
        'order_id',
        'charge',
        'name',
        'charge',
        'pickup_types',
        'pickup_type',
        'class_name_provider',
    ];

    public $changedAttributes;

    PRIVATE CONST notLoggerFields = [
        'Order' => [
            'created_at',
            'updated_at',
            'address_id'
        ],
        'OrderDelivery' => self::ORDER_DELIVERY_NO_LOGGER_FIELDS,
        'Shop' => [
            'created_at',
            'updated_at'
        ],
        'Product' => [
            'created_at',
            'updated_at'
        ],
        'User' => [
            'created_at',
            'updated_at'
        ],
        'Warehouse' => [
            'created_at',
            'updated_at'
        ],
        'Address' => [
            'created_at',
            'updated_at'
        ],
        'Rate' => [
            'created_at',
            'updated_at'
        ],
        'RateInventory' => [
            'created_at',
            'updated_at'
        ],
        'OrderProduct' => [
            'created_at',
            'updated_at'
        ],
        'OrderMessage' => [
            'created_at',
            'updated_at'
        ],
    ];

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'setLogInsert',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'setLog',
            ActiveRecord::EVENT_BEFORE_DELETE => 'setLog',
        ];
    }

    /**
     * @param $event
     * @return bool
     */
    public function setLog($event)
    {
        /** @var ActiveRecord $model */
        $model = $this->owner;
        if ($model->isNewRecord) {
            return true;
        }

        $reflection = new \ReflectionClass($this->owner);
        $shortName = $reflection->getShortName();
        $newAttributes = $model->getDirtyAttributes();
        $oldAttributes = $model->oldAttributes;

        $oldOwnerId = 0;
        if ($reflection->getShortName() == self::ADDRESS_CLASS) {
            $oldOwnerId = Yii::$app->cache->getOrSet(
                [self::ADDRESS_CLASS, $oldAttributes['id']],
                function () use ($oldAttributes) {
                    $previousParams = Log::find()->where([
                        'model_id' => $oldAttributes['id'],
                        'model' => LogBehavior::ADDRESS_CLASS
                    ])
                        ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC])
                        ->asArray()
                        ->one();

                    if (empty($previousParams)) {
                        return 0;
                    } else {
                        return $previousParams['owner_id'];
                    }

                }, self::CACHE_TIME
            );
        }

        if ($reflection->getShortName() == self::ORDER_DELIVERY_CLASS && isset($oldAttributes['order_id'])) {
            $oldOwnerId = $oldAttributes['order_id'];
        }

        /** @var User $user */
        $user = Yii::$app->user->identity;
        foreach ($newAttributes as $key => $attribute) {
            $json = false;
            if (is_array($attribute)) {
                $attribute = json_encode($attribute);
                $json = true;
            }

            if (in_array($key, self::notLoggerFields[$reflection->getShortName()])) {
                continue;
            }

            if ($json) {
                $attribute = str_replace(['"', "'", '\\'], '', $attribute);
                $oldAttributes[$key] = str_replace(['"', "'", '\\'], '', $oldAttributes[$key]);
            }

            if (!isset($oldAttributes[$key])) {
                $oldAttributes[$key] = null;
            }

            if ($oldAttributes[$key] != $attribute) {
                $changes[$key] = [
                    'new' => is_array($attribute) ? json_encode($attribute) : (string) $attribute,
                    'old' => is_array($oldAttributes[$key]) ? json_encode($oldAttributes[$key]) : (string) $oldAttributes[$key],
                ];
            }
            $oldAttributes[$key] = $attribute;
        }


        if (!empty($changes)) {
            $log = new Log();
            $log->model = $shortName;
            $log->data = json_encode($changes);
            $log->denorm = json_encode($oldAttributes);
            $log->owner_id = $oldOwnerId ? $oldOwnerId : $this->getOwnerId($shortName, $oldAttributes, $newAttributes);
            $log->user_id = $user ? $user->id : null;
            $log->is_new = 0;
            $log->user_ip = Yii::$app->request->userIP ?? '---';
            $log->model_id = $this->getModelId($shortName, $oldAttributes);


            if ($log->validate()) {
                $log->save();
            }
        } else {

            if ($shortName == 'OrderDelivery' && isset($newAttributes['order_id'])) {
                $log = Log::findOne(['model_id' => $this->getModelId($shortName, $oldAttributes)]);
                $log->owner_id = $newAttributes['order_id'];
                $log->save();
            }

            if ($shortName == 'Order' && isset($newAttributes['address_id'])) {
                $log = Log::findOne(['model_id' => $newAttributes['address_id']]);
                $log->owner_id = $model->id;
                $log->save();

                Yii::$app->cache->set(
                    [self::ADDRESS_CLASS, $newAttributes['address_id']],
                    $model->id,
                    self::CACHE_TIME
                );
            }
        }

        return true;
    }

    /**
     * Добавление логов при создании записи
     * @return bool
     */
    public function setLogInsert()
    {
        /** @var ActiveRecord $model */
        $model = $this->owner;
        $reflection = new \ReflectionClass($this->owner);
        $attributes = $model->attributes;
        $oldAttributes = [];

        /** @var User $user */
        $user = Yii::$app->user->identity;

        if ($reflection->getShortName() == self::ORDER_PRODUCT_CLASS) {
            $oldAttributes = Yii::$app->cache->getOrSet(
                [self::ORDER_PRODUCT_CLASS, $attributes['order_id'], $attributes['product_id']],
                function () use ($attributes) {
                    $previousParams = Log::find()->where([
                        'owner_id' => $attributes['order_id'],
                        'model_id' => $attributes['product_id'],
                        'model' => LogBehavior::ORDER_PRODUCT_CLASS
                    ])
                        ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC])
                        ->asArray()
                        ->one();

                    if (empty($previousParams)) {
                        return [];
                    } else {
                        $s = json_decode($previousParams['denorm'], true);
                        return $s;
                    }

                }, self::CACHE_TIME
            );
        }

        $denormData = $oldAttributes;
        $isNew = true;
        foreach ($attributes as $key => $attribute) {
            if (in_array($key, self::notLoggerFields[$reflection->getShortName()])) {
                continue;
            }

            if ($attribute && !empty($oldAttributes) && isset($oldAttributes[$key])) {
                if ($attribute != $oldAttributes[$key]) {
                    $changes[$key] = [
                        'new' => (string) $attribute,
                        'old' => $oldAttributes[$key]
                    ];
                    if ($oldAttributes[$key]) {
                        $isNew = false;
                    }
                }
            } elseif ($attribute && empty($oldAttributes)) {
                $changes[$key] = [
                    'new' => (string) $attribute,
                    'old' => null,
                ];
            }

            $denormData[$key] = $attribute;
        }

        if (!empty($changes)) {
            $log = new Log();
            $log->model = $reflection->getShortName();
            $log->data = json_encode($changes);
            $log->denorm = json_encode($denormData);
            $log->is_new = $isNew;
            $log->owner_id = $this->getOwnerId($reflection->getShortName(), $attributes);
            $log->user_id = $user ? $user->id : null;
            $log->user_ip = Yii::$app->request->userIP ?? '---';
            $log->model_id = $this->getModelId($reflection->getShortName(), $attributes);

            if ($log->validate()) {
                $log->save();

                if ($reflection->getShortName() == self::ORDER_PRODUCT_CLASS) {
                    Yii::$app->cache->set(
                        [$reflection->getShortName(), $attributes['order_id'], $attributes['product_id']],
                        $denormData,
                        self::CACHE_TIME
                    );
                }
            }
        }

        return true;
    }

    /**
     * @param string $modelName
     * @param array $attributes
     * @return int
     */
    private function getModelId(string $modelName, array $attributes): int
    {
        switch ($modelName) {
            case 'OrderProduct':
                return $attributes['product_id'];
                break;
            default:
                return $attributes['id'];
        }
    }

    /**
     * @param string $modelName
     * @param array $oldAttributes
     * @param array $newAttributes
     * @return int|null
     */
    private function getOwnerId(string $modelName, array $oldAttributes, array $newAttributes = []): ?int
    {
        switch ($modelName) {
            case 'Order':
            case 'Shop':
            case 'Address':
            case 'Product':
            case 'User':
            case 'OrderDelivery':
            case 'Warehouse':
                return $oldAttributes['id'];
                break;
            case 'OrderProduct':
            case 'OrderMessage':
                return $oldAttributes['order_id'] ? $oldAttributes['order_id'] : $newAttributes['order_id'];
                break;
            default:
                return null;
        }
    }

    public static function setOuterLog($data, $model, $owner_id = null, $model_id = null, $isNew = false)
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;

        $log = new Log();
        $log->model = $model;
        $log->data = json_encode($data);
        $log->owner_id = $owner_id;
        $log->user_id = $user->id;
        $log->user_ip = Yii::$app->request->userIP ?? '---';

        if ($log->validate()) {
            $log->save();
        }
    }

    /**
     * @deprecated
     * @param string $attribute
     * @param string $oldValue
     * @param string $newValue
     * @param string $model
     * @param int|null $ownerId
     * @param int|null $modelId
     * @param bool $isNew
     * @return bool
     */
    public static function setSingleLog($attribute, $oldValue, $newValue, $model, $ownerId = null, $modelId = null, $isNew = false)
    {
        if ($newValue == $oldValue) {
            return false;
        }

        $log = new Log();

        /** @var User $user */
        $user = Yii::$app->user->identity;

        $log->model = $model;
        $log->owner_id = $ownerId;
        $log->attribute = null;
        $log->old_value = null;
        $log->value = null;
        $log->is_new = 0;
        $log->data = json_encode([
            $attribute => [
                'new' => $newValue,
                'old' => $oldValue,
            ]
        ]);
        $log->model_id = $modelId ? $modelId : $ownerId;
        $log->user_id = $user ? $user->id : null;
        $log->user_ip = Yii::$app->request->userIP ?? '---';

        if ($log->validate()) {
            $log->save();
            return true;
        }

        return false;
    }
}