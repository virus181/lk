<?php

namespace app\models\sklad;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Connection;

/**
 * This is the model class for table "{{%Market}}".
 *
 * @property int $SN
 * @property string $Name
 * @property string $FullName
 * @property string $WebSite
 * @property string $Details
 *
 * @property Catalog[] $catalogs
 * @property Product[] $products
 */
class Market extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%Market}}';
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
            [['Name'], 'required'],
            [['Name', 'FullName', 'WebSite', 'Details'], 'string'],
            [['Name'], 'unique'],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCatalogs()
    {
        return $this->hasMany(Catalog::className(), ['SNMarket' => 'SN']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(Product::className(), ['SN' => 'SNProduct'])->viaTable('{{%Catalog}}', ['SNMarket' => 'SN']);
    }
}
