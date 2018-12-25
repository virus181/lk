<?php
namespace app\api\model\Order;

use Yii;
use yii\base\Model;

class Dimension extends Model
{
    /** @var integer */
    public $width;
    /** @var integer */
    public $length;
    /** @var integer */
    public $height;

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['width', 'length', 'height'], 'required'],
            [['width', 'length', 'height'], 'number'],
            [['width', 'length', 'height'], 'compare', 'compareValue' => 0, 'operator' => '>', 'type' => 'number'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels(): array
    {
        return [
            'length' => Yii::t('order', 'Length'),
            'width' => Yii::t('order', 'Width'),
            'height' => Yii::t('order', 'Height'),
        ];
    }
}