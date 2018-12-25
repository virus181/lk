<?php

namespace app\models\sklad;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Connection;

/**
 * This is the model class for table "{{%Catalog}}".
 *
 * @property int $SN
 * @property int $SNMarket
 * @property int $SNProduct
 * @property string $Article
 * @property string $BarCode
 * @property string $Weight
 * @property string $Length
 * @property string $Width
 * @property string $Height
 * @property int $Exist
 * @property int $Price
 * @property int $Ordered
 *
 * @property Market $market
 * @property Product $product
 */
class Catalog extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%Catalog}}';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        /** @var Connection $connection */
        $connection = Yii::$app->get('db_sklad');
        return $connection;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['SNMarket', 'SNProduct', 'Exist', 'Ordered'], 'required'],
            [['SNMarket', 'SNProduct', 'Exist', 'Ordered'], 'integer'],
            [['Article', 'BarCode'], 'string'],
            [['Weight', 'Length', 'Width', 'Height', 'Price'], 'number'],
            [['SNMarket', 'SNProduct'], 'unique', 'targetAttribute' => ['SNMarket', 'SNProduct']],
            [['SNMarket'], 'exist', 'skipOnError' => true, 'targetClass' => Market::className(), 'targetAttribute' => ['SNMarket' => 'SN']],
            [['SNProduct'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['SNProduct' => 'SN']],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMarket()
    {
        return $this->hasOne(Market::className(), ['SN' => 'SNMarket']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['SN' => 'SNProduct']);
    }
}
