<?php

namespace app\models;

use app\models\queries\ShopOptionQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%shop_option}}".
 *
 * @property int $id
 * @property int $shop_id
 * @property string $first_queue
 * @property string $second_queue
 * @property string $work_scheme_url
 * @property boolean $can_change_payment_method
 * @property string $third_queue
 * @property int $created_at
 * @property int $updated_at
 */
class ShopOption extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_option}}';
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
     * @inheritdoc
     * @return ShopOptionQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ShopOptionQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['shop_id'], 'integer'],
            [['first_queue'], 'default', 'value' => 'manager'],
            [['first_queue', 'second_queue', 'third_queue', 'work_scheme_url'], 'string'],
            [['can_change_payment_method'], 'boolean'],
            [['shop_id'], 'required']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('shop', 'ID'),
            'shop_id' => Yii::t('shop', 'Shop id'),
            'first_queue' => Yii::t('shop', 'First queue'),
            'second_queue' => Yii::t('shop', 'Second queue'),
            'third_queue' => Yii::t('shop', 'Third queue'),
            'work_scheme_url' => Yii::t('shop', 'Work scheme url'),
            'can_change_payment_method' => Yii::t('shop', 'Can change payment method'),
            'created_at' => Yii::t('shop', 'Created At'),
            'updated_at' => Yii::t('shop', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShop()
    {
        return $this->hasOne(Shop::className(), ['id' => 'shop_id']);
    }
}
