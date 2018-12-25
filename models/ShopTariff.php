<?php
namespace app\models;

use app\models\queries\ShopTariffQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%shop_tariff}}".
 *
 * @property int $id
 * @property int $shop_id
 * @property string $code
 * @property boolean $as_vip
 * @property string $work_time
 * @property int $created_at
 * @property int $updated_at
 */
class ShopTariff extends ActiveRecord
{
    const TARIFF_TEST = 'Test';
    const TARIFF_START = 'Start';
    const TARIFF_STANDART = 'Standart';
    const TARIFF_BUSINESS = 'Business';
    const TARIFF_PROFI = 'Profi';
    const TARIFF_VIP = 'Vip';

    const TARIFF_TYPES = [
        self::TARIFF_TEST,
        self::TARIFF_START,
        self::TARIFF_STANDART,
        self::TARIFF_BUSINESS,
        self::TARIFF_PROFI,
        self::TARIFF_VIP,
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_tariff}}';
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
     * @return ShopTariffQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ShopTariffQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['shop_id'], 'integer'],
            [['code'], 'string'],
            [['as_vip'], 'boolean'],
            [['work_time'], 'string'],
            [['shop_id', 'code'], 'required'],
            [['as_vip'], 'safe']
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
            'code' => Yii::t('shop', 'Tariff name'),
            'as_vip' => Yii::t('shop', 'As VIP'),
            'work_time' => Yii::t('shop', 'Work time'),
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

    /**
     * @param array $codes
     * @return array
     */
    public function getWorkTimeByTariffCode(array $codes = []): array
    {
        if (!empty($codes)) {
            foreach ($codes as $code) {
                $result[] = Yii::t('shop', sprintf('Work scheme for %s tariff', $code));
            }
        } else {
            foreach (self::TARIFF_TYPES as $code) {
                $result[] = Yii::t('shop', sprintf('Work scheme for %s tariff', $code));
            }
        }


        return $result ?? [];
    }
}
