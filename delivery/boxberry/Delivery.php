<?php
namespace app\delivery\boxberry;

use app\delivery\boxberry\parsel\Lists;
use yii\base\Component;

class Delivery extends Component
{
    public function formActs(): bool
    {
        $idList = (new Lists())->exec();
        if (true) {

        }
    }
}