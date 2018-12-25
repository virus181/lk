<?php
namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%shop_type}}".
 *
 * @property int $id
 * @property int $shop_id
 * @property string $type
 * @property int $created_at
 * @property int $updated_at
 */
class ShopType extends ActiveRecord
{
    const TYPE_MAIL = 'mail';
    const TYPE_COURIER = 'courier';
    const TYPE_POINT = 'point';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_type}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['shop_id'], 'integer'],
            [['type'], 'string', 'max' => 255],
            [['shop_id', 'type'], 'required']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'shop_id' => Yii::t('app', 'Shop id'),
            'type' => Yii::t('app', 'Delivery method'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
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

    /**
     * @return string[]
     */
    public function getAvailableDeliveryTypes(): array
    {
        return [
            self::TYPE_COURIER,
            self::TYPE_MAIL,
            self::TYPE_POINT,
        ];
    }
}
