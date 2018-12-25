<?php
namespace app\models;

use app\behaviors\LogBehavior;
use app\behaviors\RelationSaveBehavior;
use app\models\queries\RateQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%rate}}".
 *
 * @property int $id
 * @property string $name
 * @property int $shop_id
 * @property string $fias_to
 * @property int $address_id
 * @property string $type
 * @property string $notify_email
 * @property int $min_term
 * @property int $max_term
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Shop $shop
 * @property Address $address
 * @property RateInventory[] $inventories
 */
class Rate extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%rate}}';
    }

    /**
     * @inheritdoc
     * @return RateQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new RateQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array(
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
        );
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [['notify_email', 'type', 'min_term', 'max_term', 'name'], 'required'],
            [['inventories', 'address'], 'safe'],
            [['min_term', 'max_term'], 'integer', 'min' => 0],
            [['type', 'notify_email', 'name'], 'string'],
            [['notify_email'], 'email'],
            [['type'], 'in', 'range' => [OrderDelivery::DELIVERY_TO_DOOR, OrderDelivery::DELIVERY_TO_POINT]],
            [['max_term'], 'validateMaxTerm', 'skipOnEmpty' => true, 'skipOnError' => false],
            [['address_id'], 'exist', 'skipOnError' => true, 'targetClass' => Address::className(), 'targetAttribute' => ['address_id' => 'id']],
        ];

        return $rules;
    }

    /**
     * Проверка максимального срока доставки
     * @param $attribute
     */
    public function validateMaxTerm($attribute)
    {
        if (!empty($this->min_term) && $this->{$attribute} < $this->min_term) {
            $this->addError($attribute, Yii::t('app', 'Max term must be equal or greater than min term'));
        }
    }

    /**
     * @param Address $address
     */
    public function setAddress(Address $address) {
        $this->address = $address;
    }

    /**
     * @param array $inventories
     */
    public function setInventories(array $inventories) {
        $this->inventories = $inventories;
    }

    /**
     * @return array
     */
    public function fields()
    {
        $fields = parent::fields();
        $fields['address'] = 'address';
        return $fields;
    }

    /**
     * @return array
     */
    public function extraFields()
    {
        $fields = parent::extraFields();
        $fields[] = 'address';

        return $fields;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'type' => Yii::t('app', 'Delivery method'),
            'notify_email' => Yii::t('app', 'Notify email'),
            'min_term' => Yii::t('app', 'Min term'),
            'max_term' => Yii::t('app', 'Max term'),
            'name' => Yii::t('app', 'Name'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShop()
    {
        return $this->hasOne(Shop::className(), ['id' => 'shop_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAddress()
    {
        return $this->hasOne(Address::className(), ['id' => 'address_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInventories()
    {
        return $this->hasMany(RateInventory::className(), ['rate_id' => 'id']);
    }

    public function getCityName()
    {
        return $this->address->city;
    }

    public function getPVZName()
    {
        return $this->address->full_address;
    }
}
