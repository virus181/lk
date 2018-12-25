<?php

namespace app\models\traits;

use app\rbac\ControllerAccessEvent;
use Codeception\Exception\ConfigurationException;
use Yii;
use yii\db\ActiveRecordInterface;
use yii\db\BaseActiveRecord;
use yii\web\NotFoundHttpException;

trait FindModelWithCheckAccessTrait
{
    /** @var BaseActiveRecord */
    public $modelName;

    /**
     * Finds the Order model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @param array $params
     * @param BaseActiveRecord|string $modelName
     * @return ActiveRecordInterface
     * @throws ConfigurationException
     * @throws NotFoundHttpException if the model cannot be found
     * @internal param array $with
     */
    public function findModel($id = null, $params = [], $modelName = '')
    {
        if (!$modelName) {
            if (!$this->modelName) {
                throw new ConfigurationException('You must set $modelName param');
            }
            $modelName = $this->modelName;
        }

        $query = $modelName::find();

        if ($id) {
            $query = $query->andWhere(['id' => $id]);
        }

        if ($params) {
            foreach ($params as $paramKey => $value) {
                $query->{$paramKey}($value);
            }
        }

        if (($model = $query->one()) !== null) {
            $event = new ControllerAccessEvent([
                'action' => Yii::$app->controller->action,
                'model' => $model,
            ]);

            Yii::$app->controller->trigger(ControllerAccessEvent::AFTER_FIND_MODEL, $event);
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist');
        }
    }
}