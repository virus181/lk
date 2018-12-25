<?php

namespace app\controllers;

use app\models\Common\Products;
use app\models\Files\XML;
use app\models\Product;
use app\models\search\ProductSearch;
use app\models\Shop;
use app\models\traits\FindModelWithCheckAccessTrait;
use app\models\User;
use Yii;
use yii\base\UserException;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

/**
 * ProductController implements the CRUD actions for Product model.
 */
class ProductController extends Controller
{
    use FindModelWithCheckAccessTrait;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function init()
    {
        $this->modelName = Product::className();
        parent::init();
    }

    /**
     * Lists all Product models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel    = new ProductSearch();
        $dataProvider   = $searchModel->search(Yii::$app->request->queryParams);
        $exportProvider = $searchModel->export(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel'    => $searchModel,
            'dataProvider'   => $dataProvider,
            'exportProvider' => $exportProvider,
        ]);
    }

    /**
     * Creates a new Product model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $product = new Product();
        $product->setScenario(Product::SCENARIO_MANUAL_CREATE);
        /** @var User $user */
        $user = Yii::$app->user->identity;

        if (Yii::$app->request->post()) {
            $product->load(Yii::$app->request->post());
            $product->shop_id = Yii::$app->request->post('Product')['shop_id'];
            if ($product->weight !== '') {
                $product->weight = str_replace(',', '.', $product->weight) * 1000;
            }

            if ($product->validate() && $product->save()) {
                return $this->redirect(['index']);
            } else {
                return $this->renderAjax('create', [
                    'model' => $product,
                    'shops' => $user->getAllowedShops(),
                ]);
            }
        } else {
            return $this->renderAjax('create', [
                'model' => $product,
                'shops' => $user->getAllowedShops(),
            ]);
        }
    }

    /**
     * Updates an existing Product model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $id
     * @return mixed
     * @throws ForbiddenHttpException
     */
    public function actionUpdate($id)
    {
        /**  @var Product $product */
        $product         = $this->findModel($id);
        $product->weight = $product->weight / 1000;

        /** @var User $user */
        $user = Yii::$app->user->identity;

        if (Yii::$app->request->post()) {
            $product->load(Yii::$app->request->post());
            $product->shop_id = Yii::$app->request->post('Product')['shop_id'];
            if ($product->weight !== '') {
                $product->weight = str_replace(',', '.', $product->weight) * 1000;
            }

            if ($product->validate()) {
                $product->save();
                return $this->redirect(['index']);
            } else {
                $errorMessage = [];
                foreach ($product->errors as $error) {
                    $errorMessage[] = implode('<br />', $error);
                }
                Yii::$app->session->addFlash('warning', implode('<br />', $errorMessage));
            }
        }

        return $this->renderAjax('update', [
            'disabled' => ($product->status != Product::STATUS_ACTIVE && !Yii::$app->request->post()),
            'model'    => $product,
            'shops'    => $user->getAllowedShops(),
        ]);
    }

    /**
     * Deletes an existing Product model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param integer $id
     * @return mixed
     * @throws ForbiddenHttpException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    /**
     * @return Product[]
     */
    public function actionSearch()
    {
        $term    = Yii::$app->request->get('term');
        $shopId  = Yii::$app->request->get('shop_id');
        $element = Yii::$app->request->get('element');

        $this->findModel($shopId, [], Shop::className());

        $products = Product::find()
            ->select(['*', $element . ' as value', 'IF (additional_id, 1, 0) as storred'])
            ->asArray()
            ->andWhere(['shop_id' => $shopId])
            ->andWhere(['status' => Product::STATUS_ACTIVE])
            ->andFilterWhere(['like', $element, $term])
            ->orderBy([
                'storred' => SORT_DESC,
                'count'   => SORT_DESC
            ])
            ->all();

        Yii::$app->response->format = Response::FORMAT_JSON;

        return $products;
    }

    /**
     * Парсер XML файла и вернем HTML списка товаров
     *
     * @return string
     * @throws UserException
     */
    public function actionParseXml()
    {
        $inputFile = $_FILES['xml'];
        $xml       = new XML($inputFile);
        if (!$xml->validate()) {
            throw new UserException(Yii::t('file', 'Product file is not suitable'));
        }

        $parserData = $xml->parse();
        $products   = (new Products())->getXMLProducts($parserData);

        return $this->renderAjax('_list', [
            'products' => $products,
        ]);
    }
}
