<?php
namespace app\controllers;

use app\models\Delivery;
use app\models\OrderDelivery;
use app\models\Points;
use app\models\Report;
use app\models\search\OrderSearch;
use app\models\Shop;
use yii\helpers\ArrayHelper;
use yii\web\Controller;

/**
 * ShopController implements the CRUD actions for Shop model.
 */
class ReportController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * @return string
     */
    public function actionReportByQuery()
    {
        $orderSearch = new OrderSearch();

        if ($orderSearch->load(Yii::$app->request->post())) {

            // Если дата указана сформируем массив периода дат
            if ($orderSearch->date_created) {
                $orderSearch->setDatePeriod($orderSearch->date_created);
            }

            $data = Report::getReportData($orderSearch);

            header("Content-type: text/csv");
            header("Content-Disposition: attachment; filename=report_" . date('d.m.Y', time()) . ".csv");
            header("Pragma: no-cache");
            header("Expires: 0");

            $out = fopen('php://output', 'w');
            fputs($out, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));

            foreach($data as $item)
            {
                fputcsv($out, $item);
            }

            fclose($out);
            die();
        }

        return $this->render('form', [
            'order' => $orderSearch,
            'shops' => ArrayHelper::map(
                Shop::find()
                    ->select(['id', 'name'])
                    ->asArray()
                    ->all(),
                'id',
                'name'
            ),
            'carriers' => ArrayHelper::map(
                Delivery::find()
                    ->select(['carrier_key', 'name'])
                    ->asArray()
                    ->all(),
                'carrier_key',
                'name'
            ),
            'deliveryMethods' => (new OrderDelivery())->getDeliveryTypes(),
            'isAllowProduct' => true
        ]);
    }

    /**
     * @return string
     */
    public function actionDeviationCost()
    {
        $orderSearch = new OrderSearch();

        if ($orderSearch->load(Yii::$app->request->post())) {

            // Если дата указана сформируем массив периода дат
            if ($orderSearch->date_created) {
                $orderSearch->setDatePeriod($orderSearch->date_created);
            }

            $data = Report::getDeviationData($orderSearch);

            header("Content-type: text/csv");
            header("Content-Disposition: attachment; filename=report_" . date('d.m.Y', time()) . ".csv");
            header("Pragma: no-cache");
            header("Expires: 0");

            $out = fopen('php://output', 'w');
            fputs($out, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));

            foreach($data as $item)
            {
                fputcsv($out, $item);
            }

            fclose($out);
            die();
        }

        return $this->render('form', [
            'order' => $orderSearch,
            'shops' => ArrayHelper::map(
                Shop::find()
                    ->select(['id', 'name'])
                    ->asArray()
                    ->all(),
                'id',
                'name'
            ),
            'carriers' => ArrayHelper::map(
                Delivery::find()
                    ->select(['carrier_key', 'name'])
                    ->asArray()
                    ->all(),
                'carrier_key',
                'name'
            ),
            'deliveryMethods' => (new OrderDelivery())->getDeliveryTypes()
        ]);
    }
}
