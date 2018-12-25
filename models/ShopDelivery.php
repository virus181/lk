<?php

namespace app\models;

use app\models\queries\RegistryQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%shop_delivery}}".
 *
 * @property int $id
 * @property int $shop_id
 * @property int $delivery_id
 * @property int $pickup_type
 * @property string $pickup_time_start
 * @property string $pickup_time_end
 * @property int $created_at
 * @property int $updated_at
 */
class ShopDelivery extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_delivery}}';
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
            [['shop_id', 'delivery_id'], 'integer'],
            [['shop_id', 'delivery_id'], 'required']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                => Yii::t('app', 'ID'),
            'shop_id'           => Yii::t('app', 'Shop id'),
            'carrier_key'       => Yii::t('app', 'Carrier Key'),
            'pickup_type'       => Yii::t('app', 'Pickup type'),
            'pickup_time_start' => Yii::t('app', 'Pickup time start'),
            'pickup_time_end'   => Yii::t('app', 'Pickup time end'),
            'created_at'        => Yii::t('app', 'Created At'),
            'updated_at'        => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array(
            TimestampBehavior::className()
        );
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShop()
    {
        return $this->hasOne(Shop::className(), ['id' => 'shop_id']);
    }
}
