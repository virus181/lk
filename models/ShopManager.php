<?php
namespace app\models;

use app\models\queries\ShopQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%shop_manager}}".
 *
 * @property int $id
 * @property int $shop_id
 * @property int $user_id
 * @property int $created_at
 * @property int $updated_at
 */
class ShopManager extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop_manager}}';
    }

    /**
     * @inheritdoc
     * @return ShopQuery
     */
    public static function find()
    {
        return new ShopQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['shop_id', 'user_id'], 'integer'],
            [['shop_id', 'user_id'], 'required']
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
            'user_id' => Yii::t('app', 'User id'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
