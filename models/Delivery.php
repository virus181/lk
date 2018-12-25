<?php
namespace app\models;

use app\models\queries\RegistryQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%delivery}}".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $status
 * @property string $carrier_key
 */
class Delivery extends ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_BLOCKED = 0;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%delivery}}';
    }

    /**
     * @inheritdoc
     * @return RegistryQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new RegistryQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['carrier_key', 'name', 'logo'], 'string', 'max' => 255],
            [['description'], 'text'],
            [['status'], 'boolean'],
            [['id', 'carrier_key'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array(
            TimestampBehavior::className(),
        );
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'          => Yii::t('app', 'ID'),
            'carrier_key' => Yii::t('app', 'Carrier Key'),
            'name'        => Yii::t('app', 'Name'),
            'logo'        => Yii::t('app', 'Logo'),
            'status'      => Yii::t('app', 'Status'),
            'description' => Yii::t('app', 'Description'),
            'created_at'  => Yii::t('app', 'Created At'),
            'updated_at'  => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return array
     */
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['created_at']);
        unset($fields['updated_at']);
        return $fields;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDeliveryService()
    {
        return $this->hasMany(DeliveryService::className(), ['delivery_id' => 'id']);
    }

    /**
     * @param string $carrierKey
     * @return Delivery|null
     */
    public function getDeliveryByCarrierKey(string $carrierKey): ?Delivery
    {
        return Yii::$app->cache->getOrSet(['delivery', $carrierKey], function () use ($carrierKey) {
            return $this::find()->where(['carrier_key' => $carrierKey])->one();
        }, Helper::DAY_CACHE_VALUE);
    }

    /**
     * @return array
     */
    public function getActiveDeliveryServiceIds(): array
    {
        return Yii::$app->cache->getOrSet('activeDeliveryServiceIds', function () {
            return ArrayHelper::getColumn(
                $this::find()->where(['status' => self::STATUS_ACTIVE])->asArray()->all(),
                'id'
            );
        }, Helper::DAY_CACHE_VALUE);
    }
}
