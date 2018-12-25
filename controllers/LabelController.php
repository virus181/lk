<?php
namespace app\controllers;

use app\components\DbDependencyHelper;
use app\components\UserException;
use app\delivery\apiship\Delivery;
use app\models\Helper;
use app\models\Order;
use app\models\search\LabelSearch;
use kartik\mpdf\Pdf;
use Yii;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\Response;

class LabelController extends Controller
{
    public function actionIndex()
    {
        $searchModel = new LabelSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $exportProvider = $searchModel->export(Yii::$app->request->queryParams);

        Yii::$app->getDb()->cache(function ($db) use ($dataProvider) {
            $dataProvider->prepare();
        }, Helper::MIN_CACHE_VALUE, DbDependencyHelper::generateDependency(Order::find()));


        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'exportProvider' => $exportProvider,
            'context' => [
                'downloadUrl' => Url::to(['label/download']),
            ]
        ]);
    }

    /**
     * Получение этикеток
     */
    public function actionDownloadLabel()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $orderIds = Yii::$app->request->post('selection');
        if (!empty($orderIds)) {
            $providerIds = ArrayHelper::getColumn(
                Order::find()->where(['IN', 'id', $orderIds])->asArray()->all(),
                'provider_number'
            );
            $labelUrl = (new Delivery())->getLabelList($providerIds);
            if ($labelUrl) {
                $fileExtensionArray = explode('.', $labelUrl);
                $fileExtension = $fileExtensionArray[count($fileExtensionArray) - 1];
                $fileName = implode('-', $orderIds);
                copy($labelUrl, 'temp/' . $fileName . '.' . $fileExtension);
                return [
                    'url' => 'temp/' . $fileName . '.' . $fileExtension,
                    'success' => true
                ];
            }
        }
        return ['success' => false];
    }

    /**
     * Скачивание этикеток
     * @return mixed
     * @throws UserException
     */
    public function actionDownload()
    {
        $request = Yii::$app->request;

        if (!$request->get('selection')) {
            throw new UserException(400, Yii::t('app', 'Select at least one order from the list'));
        }

        if (!$orders = Order::find()
            ->where(['IN', 'id', $request->get('selection')])
            ->andWhere(['NOT', ['dispatch_number' => null]])
            ->all()
        ) {
            throw new UserException(404, Yii::t('app', 'Selected orders was not found'));
        }

        if (count($orders) != count($request->get('selection'))) {
            throw new UserException(404, Yii::t('app', 'One or more orders have not yet received the SD version number, the system automatically places the numbers, please try again later'));
        }

        $content = $this->renderPartial('_label', [
            'orders' => $orders
        ]);

        $pdf = new Pdf([
            'format' => Pdf::FORMAT_A4,
            'destination' => Pdf::DEST_FILE,
            'orientation' => Pdf::ORIENT_PORTRAIT,
            'marginLeft' => 5,
            'marginRight' => 5,
            'marginTop' => 5,
            'marginBottom' => 5,
            'marginHeader' => 5,
            'marginFooter' => 5,
            'cssFile' => '@app/web/css/label.css',
            'filename' => 'temp/' . sprintf('label_%s_%s.pdf', implode("_", $request->get('selection')), date('d_m_Y', time())),
            'content' => $content
        ]);

        $pdf->render();

        return [
            'url' => 'temp/' . sprintf('label_%s_%s.pdf', implode("_", $request->get('selection')), date('d_m_Y', time())),
            'success' => true
        ];
    }

    /**
     * Скачивание этикетки
     * @param int $id
     * @return mixed
     * @throws UserException
     */
    public function actionPdf(int $id)
    {
        $query = Order::find()->where(['id' => $id]);
        if (ArrayHelper::getValue(Yii::$app->params, 'apiship.sendOrder')) {
            $query->andWhere(['NOT', ['dispatch_number' => null]]);
        }
        if (!$orders = $query->all()) {
            throw new UserException(404, Yii::t('app', 'Not found'));
        }

        $content = $this->renderPartial('_label', [
            'orders' => $orders
        ]);

        $pdf = new Pdf([
            'format' => Pdf::FORMAT_A4,
            'orientation' => Pdf::ORIENT_PORTRAIT,
            'destination' => Pdf::DEST_DOWNLOAD,
            'marginLeft' => 5,
            'marginRight' => 5,
            'marginTop' => 5,
            'marginBottom' => 5,
            'marginHeader' => 5,
            'marginFooter' => 5,
            'cssFile' => '@app/web/css/label.css',
            'filename' => sprintf('label_%d_%s.pdf', $id, date('d_m_Y', time())),
            'content' => $content
        ]);

        return $pdf->render();
    }
}