<?php
namespace app\api\base;

use app\components\Clients\Analitics;
use app\models\search\SearchModelInterface;
use app\rbac\ControllerAccessEvent;
use Yii;
use yii\rest\ActiveController;

class BaseActiveController extends ActiveController
{

    public $modelClass;
    public $searchModelClass;
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
        'preserveKeys' => false,
    ];

    public function actions()
    {
        $actions = parent::actions();
        if ($this->searchModelClass !== null) {
            $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        }
        return $actions;
    }

    public function prepareDataProvider()
    {
        /** @var SearchModelInterface $searchModel */
        $searchModel = new $this->searchModelClass();
        return $searchModel->search(Yii::$app->request->queryParams);
    }

    public function checkAccess($action, $model = null, $params = [])
    {
        $event = new ControllerAccessEvent([
            'action' => $this->action,
            'model' => $model,
        ]);

        Yii::$app->controller->trigger(ControllerAccessEvent::AFTER_CHECK_ACCESS, $event);
    }

    /**
     * @inheritdoc
     */
    protected function verbs()
    {
        return [
            'create' => ['POST'],
            'update' => ['POST'],
            'view' => ['GET'],
            'index' => ['GET'],
        ];
    }

    /**
     * @param \yii\base\Action $action
     * @param mixed $result
     * @return mixed
     */
    public function afterAction($action, $result)
    {
        Yii::info([
            'method' => $action->id,
            'controller' => $action->controller->id,
            'module' => $action->controller->module->id,
            'RESULT' => (isset($result->errors) && !empty($result->errors))
                ? $result->errors
                : $this->serializeData($result),
        ], 'custom-' . $action->controller->id);
        return parent::afterAction($action, $result);
    }

    /**
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action)
    {
        $client = new Analitics();
        $client->sendRequest([
            'method' => $action->id,
            'controller' => $action->controller->id,
            'module' => $action->controller->module->id,
            'token' => Yii::$app->request->get('access-token', '---'),
            'ip' => Yii::$app->request->getUserIP()
        ], 1, 'api', 0, 'POST');

        // Тут запишем логи
        Yii::info([
            'method' => $action->id,
            'controller' => $action->controller->id,
            'module' => $action->controller->module->id,
            'GET' => Yii::$app->request->get(),
            'POST' => Yii::$app->request->post()
        ], 'custom-' . $action->controller->id);
        return parent::beforeAction($action);
    }
}