<?php
namespace app\controllers;

use Yii;

class Controller extends \yii\web\Controller
{
    /**
     * @param \yii\base\Action $action
     * @param mixed $result
     * @return mixed
     */
    public function afterAction($action, $result)
    {
        return parent::afterAction($action, $result);
    }

    /**
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action)
    {
        // Тут запишем логи
        if (Yii::$app->request->post()) {
            Yii::info([
                'method' => $action->id,
                'controller' => $action->controller->id,
                'GET' => Yii::$app->request->get(),
                'POST' => Yii::$app->request->post()
            ], 'custom-' . $action->controller->id);
        }
        return parent::beforeAction($action);
    }
}
