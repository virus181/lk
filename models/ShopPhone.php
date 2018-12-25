<?php

namespace app\models;

use app\models\queries\ShopPhoneQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%shop_phone}}".
 *
 * @property int $id
 * @property int $shop_id
 * @property string $phone
 * @property string $provider_code
 * @property int $created_at
 * @property int $updated_at
 */
class ShopPhone extends ActiveRecord
{
    const SIP_PROVIDER = 'sip';

    const PROVIDER_CODES = [
        self::SIP_PROVIDER
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_phone}}';
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
     * @return ShopPhoneQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ShopPhoneQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['phone'], 'filter', 'filter' => function () {
                return '+7' . $this->getClearPhone();
            }],
            [['shop_id'], 'integer'],
            [['phone', 'provider_code'], 'string'],
            [['phone'], 'unique', 'targetClass' => ShopPhone::className()],
            [['shop_id', 'phone', 'provider_code'], 'required']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'            => Yii::t('shop', 'ID'),
            'shop_id'       => Yii::t('shop', 'Shop id'),
            'phone'         => Yii::t('shop', 'Phone'),
            'provider_code' => Yii::t('shop', 'Provider code'),
            'created_at'    => Yii::t('shop', 'Created At'),
            'updated_at'    => Yii::t('shop', 'Updated At'),
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
     * @return array
     */
    public function getPhoneProviders(): array
    {
        foreach (self::PROVIDER_CODES as $code) {
            $result[$code] = Yii::t('shop', $code);
        }

        return $result ?? [];
    }

    /**
     * @return bool|mixed|string
     */
    public function getClearPhone()
    {
        $phone = preg_replace('/\W|_/', "", $this->phone);

        $first = substr($phone, 0, 1);

        if ($first == 8 || $first == 7) {
            $phone = substr($phone, 1);
        }

        return $phone;
    }
}
