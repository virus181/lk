<?php
namespace app\models;

use app\behaviors\LogBehavior;
use app\delivery\DeliveryHelper;
use app\models\queries\RateQuery;
use app\models\queries\ShopQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%rate_inventory}}".
 *
 * @property int $id
 * @property int $rate_id
 * @property int $cost
 * @property float $weight_from
 * @property float $weight_to
 * @property float $price_from
 * @property float $price_to
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Rate $rate
 */
class RateInventory extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%rate_inventory}}';
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
        );
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [['weight_from', 'weight_to', 'price_from', 'price_to', 'cost'], 'required'],
            [['weight_from', 'weight_to', 'price_from', 'price_to', 'cost'], 'number', 'min' => 0],
            [['weight_to'], 'validateWeightTo', 'skipOnEmpty' => true, 'skipOnError' => false],
            [['price_to'], 'validatePriceTo', 'skipOnEmpty' => true, 'skipOnError' => false],
        ];

        return $rules;
    }

    /**
     * @param $attribute
     */
    public function validateWeightTo($attribute)
    {
        if (!empty($this->weight_from) && $this->{$attribute} < $this->weight_from) {
            $this->addError($attribute, Yii::t('app', 'Weight to must be equal or greater than weight from'));
        }
    }

    /**
     * @param $attribute
     */
    public function validatePriceTo($attribute)
    {
        if (!empty($this->price_from) && $this->{$attribute} < $this->price_from) {
            $this->addError($attribute, Yii::t('app', 'Price to must be equal or greater than price from'));
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRate()
    {
        return $this->hasOne(Rate::className(), ['id' => 'rate_id']);
    }
}
