<?php
namespace app\api\controllers;

use app\api\base\BaseActiveController;
use app\api\Module;
use app\models\Product;
use app\models\User;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\web\Request;
use yii\web\Response;

class ProductController extends BaseActiveController
{
    public $modelClass = 'app\models\OrderDelivery';

    /**
     * @param string $id
     * @param Module $module
     * @param array $config
     */
    public function __construct($id, Module $module, array $config = [])
    {
        Yii::$app->params['environment'] = 'api';
        parent::__construct($id, $module, $config);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'create_v2' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function actions()
    {
        return [
            'index' => [
                'class' => 'yii\rest\IndexAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
            ],
            'view' => [
                'class' => 'yii\rest\ViewAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
            ],
        ];
    }

    /**
     * @version 1
     * @return Product|ActiveDataProvider
     */
    public function actionList()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        /** @var Request $request */
        $request = Yii::$app->request;
        $product = new Product();
        $product->setScenario(Product::SCENARIO_PRODUCT_LIST_API);
        $product->load($request->get(), '');
        $product->validate();

        $perPage = $request->get('per-page') ? $request->get('per-page') : 15;

        if ($product->hasErrors() === false) {
            $query = Product::find()->where(['shop_id' => $product->shop_id]);
            if ($product->min_price) {
                $query->andWhere(['>', 'price', $product->min_price]);
            }
            if ($product->max_price) {
                $query->andWhere(['<', 'price', $product->max_price]);
            }
            if ($product->in_stock == 1) {
                $query->andWhere(['>', 'count', 0]);
            }
            if ($product->in_stock == '0') {
                $query->andWhere(['count' => 0]);
            }
            if ($product->ids) {
                $query->andWhere(['id' => $product->ids]);
            }

            $attributes = array_keys((new Product())->getAttributes());
            $arrayDataProvider = new ActiveDataProvider([
                'query' => $query,
                'sort' => [
                    'attributes' => $attributes,
                ],
                'pagination' => [
                    'pageSize' => $perPage,
                ],
            ]);
            return $arrayDataProvider;
        } else {
            return $product;
        }
    }

    /**
     * @inheritdoc
     */
    protected function verbs()
    {
        $verbs = parent::verbs();
        $verbs['list'] = ['GET'];
        $verbs['create_v2'] = ['POST'];
        return $verbs;
    }

    /**
     * Создание товара
     * @version 2
     */
    public function actionCreate_v2()
    {
        /** @var Request $request */
        $request = Yii::$app->request;

        $product = new Product();
        $product->scenario = Product::SCENARIO_API_CREATE;
        $product->load($request->post(), '');

        /** @var User $user */
        $user = Yii::$app->user->identity;
        $shopIds = $user->getAllowedShopIds();
        if ($shopIds === false || (!empty($shopIds) && !in_array($request->post('shop_id'), $shopIds))) {
            $product->addError('shop_id', 'У вас нет доступа к этому магазину');
        }
        $product->shop_id = $request->post('shop_id');

        if ($product->validate() && $product->hasErrors() === false) {
            $product->save();
        }

        return $product;
    }
}