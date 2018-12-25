<?php declare(strict_types=1);
namespace app\commands;

use app\components\Clients\Accounting;
use app\models\Common\Registry;
use Yii;
use yii\console\Controller;
use yii\db\Query;
use yii\log\Logger;

class RegistryController extends Controller
{
    public function actionGetLatestDocuments()
    {
        $accounterClient = new Accounting();
        $updatedForm = (new Registry())->getRegistryLastUpdatedTime();
        $response = $accounterClient->sendRequest('/buh_fd/hs/GetPayment/download/', [
            'DateFrom' => date('Ymd', $updatedForm),
            'DateTo' => date('Ymd', time() - 86400),
        ]);
        if (!empty($response->getContent())) {
            $responseData = json_decode($response->getContent(), true);
            foreach ($responseData as $data) {
                $registry = (new Accounting\Builder\Registry($data))->build();
                if ($registry->validateAll() && $registry->saveAll()) {

                } else {

                    (new Query())->createCommand()->insert('{{%registry}}', [
                        'status' => 0,
                        'number' => $registry->number,
                        'name' => $registry->name,
                        'delivery_id' => $registry->delivery_id,
                        'created_at' => $registry->created_at,
                        'updated_at' => $registry->updated_at,
                    ])->execute();

                    echo $registry->name . " was not saved \n";
                    Yii::$app->slack->send(sprintf('Registry %s:%s was not saved', $registry->name, $registry->number), ':thumbs_up:', [
                        [
                            'fallback' => sprintf('Registry %s:%s was not saved', $registry->name, $registry->number),
                            'color' => Yii::$app->slack->getLevelColor(Logger::LEVEL_ERROR),
                            'fields' => [
                                [
                                    'title' => 'Errors',
                                    'value' => json_encode($registry->getErrorsAll()),
                                    'short' => false,
                                ],
                            ],
                        ],
                    ]);
                }
            }
        }
    }
}