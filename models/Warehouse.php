<?php

namespace app\models;

use app\behaviors\LogBehavior;
use app\behaviors\RelationSaveBehavior;
use app\models\queries\WarehouseQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%warehouse}}".
 *
 * @property int $id
 * @property string $name
 * @property string $contact_fio
 * @property string $contact_phone
 * @property int $address_id
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 *
 * @property array $statuses
 * @property Address $address
 * @property Courier[] $couriers
 */
class Warehouse extends ActiveRecord
{
    const SCENARIO_WAREHOUSE_LIST_API = 'WAREHOUSE_LIST_API';

    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%warehouse}}';
    }

    /**
     * @inheritdoc
     * @return WarehouseQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new WarehouseQuery(get_called_class());
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            LogBehavior::className(),
            [
                'class' => RelationSaveBehavior::className(),
                'relations' => [
                    'address' => [
                        'value' => 'address',
                        'type' => RelationSaveBehavior::HAS_ONE_TYPE,
                    ],
                ]
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [['name', 'contact_fio', 'contact_phone'], 'required', 'on' => self::SCENARIO_DEFAULT],
            [['address_id', 'status'], 'integer'],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['name', 'contact_fio', 'contact_phone'], 'string', 'max' => 255],
            [['address_id'], 'exist', 'skipOnError' => true, 'targetClass' => Address::className(), 'targetAttribute' => ['address_id' => 'id']],
        ];

        /** @var User $user */
        if ($user = Yii::$app->user->identity) {
            $warehouseIds = $user->getAllowedWarehouseIds();

            if ($warehouseIds !== []) {
                $rules[] = [['id'], 'in', 'range' => ($warehouseIds === false ? [] : $warehouseIds)];
            }
        }

        return $rules;
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_WAREHOUSE_LIST_API] = $scenarios[self::SCENARIO_DEFAULT];
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Warehouse Name'),
            'contact_fio' => Yii::t('app', 'Contact Fio'),
            'status' => Yii::t('app', 'Status'),
            'contact_phone' => Yii::t('app', 'Contact Phone'),
            'address' => Yii::t('app', 'Address'),
        ];
    }

    /**
     * @param null $statusCode
     * @return string
     */
    public function getStatusName($statusCode = null)
    {
        return ArrayHelper::getValue($this->statuses, $statusCode ? $statusCode : $this->status);
    }

    public function fields()
    {
        $fields = parent::fields();
        $fields['created_at'] = function () {
            return date('Y-m-d H:i:s', $this->created_at);
        };
        $fields['updated_at'] = function () {
            return date('Y-m-d H:i:s', $this->updated_at);
        };
        $fields['address'] = 'address';

        $fields['contact_phone'] = function () {
            return (new \app\models\Helper\Phone($this->contact_phone))->getHumanView();
        };

        $fields['status'] = function () {
            $statuses = $this->getStatuses();
            return mb_convert_case($statuses[$this->status], MB_CASE_TITLE, "UTF-8");
        };

        return $fields;
    }

    /**
     * @return array
     */
    public function getStatuses()
    {
        return $statuses = [
            self::STATUS_ACTIVE => Yii::t('app', 'active'),
            self::STATUS_DELETED => Yii::t('app', 'deleted'),
        ];
    }

    public function extraFields()
    {
        $fields = parent::extraFields();
        $fields[] = 'address';

        return $fields;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAddress()
    {
        return $this->hasOne(Address::className(), ['id' => 'address_id']);
    }

    public function getCouriers()
    {
        return $this->hasMany(Courier::className(), ['warehouse_id' => 'id']);
    }

    public function setAddress(Address $address) {
        $this->address = $address;
    }

    public function getClearPhone()
    {
        $phone = preg_replace('/\W|_/', "", $this->contact_phone);

        $first = substr($phone, 0, 1);

        if ($first == 8 || $first == 7) {
            $phone = substr($phone, 1);
        }

        return $phone;
    }

    public static function getStatusList(): array
    {
        return $statuses = [
            self::STATUS_ACTIVE => Yii::t('app', 'active'),
            self::STATUS_DELETED => Yii::t('app', 'deleted'),
        ];
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getStatus(): ?string
    {
        return isset($this->getStatuses()[$this->status]) ? $this->getStatuses()[$this->status] : null;
    }

    /**
     * @return string
     */
    public function getWarehouseName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getContactFio(): string
    {
        return $this->contact_fio;
    }

    /**
     * @return string
     */
    public function getContactPhone(): string
    {
        return $this->contact_phone;
    }

    /**
     * @return null|string
     */
    public function getFullAddress(): ?string
    {
        return ($this->address) ? $this->address->full_address : '';
    }

    public function afterSave($insert, $changedAttributes)
    {
        Yii::$app->cache->delete('allowedWarehouseForUser' . Yii::$app->user->id);
        parent::afterSave($insert, $changedAttributes);
    }
}
