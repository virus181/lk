<?php

namespace app\models;

use app\behaviors\LogBehavior;
use app\models\queries\OrderProductQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%order_product}}".
 *
 * @property int $order_id
 * @property int $product_id
 * @property string $name
 * @property int $quantity
 * @property int $price
 * @property int $accessed_price
 * @property int $weight
 * @property int $width
 * @property int $length
 * @property int $height
 * @property boolean $is_not_reversible
 *
 * @property Order $order
 * @property Product $product
 */
class OrderProduct extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_product}}';
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            LogBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     * @return OrderProductQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new OrderProductQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['price'], 'default', 'value' => 0, 'on' => self::SCENARIO_DEFAULT],
            [['weight'], 'default', 'value' => 10, 'on' => self::SCENARIO_DEFAULT],
            [['accessed_price'], 'default', 'value' => 1, 'on' => self::SCENARIO_DEFAULT],
            [
                'accessed_price',
                'compare',
                'compareValue' => 1,
                'operator' => '>=',
                'message' => Yii::t('app', 'Accessed price must be greater than zero')
            ],
            [['price', 'accessed_price', 'weight', 'width', 'height', 'length'], 'number'],
            [['quantity'], 'default', 'value' => 1],
            [['name', 'order_id', 'product_id', 'quantity', 'price', 'weight'], 'required'],
            [['name'], 'trim'],
            [['is_not_reversible'], 'boolean'],
            [['order_id', 'product_id', 'quantity'], 'integer'],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::className(), 'targetAttribute' => ['order_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['product_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'order_id' => Yii::t('app', 'Order ID'),
            'product_id' => Yii::t('app', 'Product ID'),
            'quantity' => Yii::t('app', 'Quantity'),
            'price' => Yii::t('app', 'Price'),
            'accessed_price' => Yii::t('app', 'Accessed Price'),
            'weight' => Yii::t('app', 'Weight'),
            'name' => Yii::t('app', 'Name'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }

    /**
     * @param $order Order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
    }

    /**
     * @param $product Product
     */
    public function setProduct($product)
    {
        $this->product = $product;
        $this->quantity = $product->quantity;
        $this->weight = $product->weight;
        $this->width = $product->width;
        $this->length = $product->length;
        $this->height = $product->height;
        $this->price = $product->price;
        $this->accessed_price = $product->accessed_price;
        $this->name = $product->name;
    }

    /**
     * @return array
     */
    public function fields()
    {
        $fields = parent::fields();
        if (Yii::$app->params['environment'] == 'api') {

            $fields['width'] = function () {
                return (int) $this->width;
            };

            $fields['length'] = function () {
                return (int) $this->length;
            };

            $fields['height'] = function () {
                return (int) $this->height;
            };

            $fields['is_not_reversible'] = function () {
                return (boolean) $this->is_not_reversible;
            };

            unset($fields['created_at']);
            unset($fields['updated_at']);
            unset($fields['order_id']);
        }
        $fields[] = 'barcode';
        return $fields;
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
//        $data = [];
//        if (!$result = Yii::$app->cache->get(['OrderProduct', $this->attributes['product_id'], $this->attributes['order_id']])) {
//            $oldValues = Log::find()
//                ->andWhere(['model_id' => $this->attributes['product_id']])
//                ->andWhere(['owner_id' => $this->attributes['order_id']])
//                ->andWhere(['model' => 'OrderProduct'])
//                ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC])
//                ->asArray()
//                ->all();
//            $result = [];
//            foreach ($oldValues as $values) {
//                if (isset($result[$values['attribute']])) {
//                    continue;
//                }
//                $result[$values['attribute']] = $values['value'];
//            }
//        }
//
//        foreach ($this->attributes as $key => $value) {
//            if (isset($result[$key]) && $result[$key] != $value) {
//                $data[$key] = [
//                    'new' => $value,
//                    'old' => $result[$key],
//                ];
//            }
//        }
//
//        LogBehavior::setOuterLog(
//            $data,
//            'OrderProduct',
//            $this->attributes['order_id'],
//            $this->attributes['product_id'],
//            empty($result) ? true : false
//        );

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @return string
     */
    public function getBarcode(): string
    {
        return $this->product->barcode ?? '';
    }
}
