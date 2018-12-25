<?php

$params = array_merge(
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

$dbs = array_merge(
    require(__DIR__ . '/dbs.php'),
    require(__DIR__ . '/dbs-local.php')
);


$db = array_merge(
    require(__DIR__ . '/db.php'),
    require(__DIR__ . '/db-local.php')
);

$config = [
    'id'                  => 'basic-console',
    'basePath'            => dirname(__DIR__),
    'bootstrap'           => [
        'log',
        'queue',
    ],
    'controllerNamespace' => 'app\commands',
    'sourceLanguage'      => 'en-US',
    'language'            => 'ru-RU',
    'controllerMap'       => [
        'migrate-app' => [
            'class'               => 'yii\console\controllers\MigrateController',
            'migrationPath'       => '@yii/migrations',
            'migrationNamespaces' => null,
        ],
//        'migrate' => [
//            'class'               => 'yii\console\controllers\MigrateController',
//            'migrationPath'       => null,
//            'migrationNamespaces' => [
//                'yii\queue\db\migrations',
//            ],
//        ],
    ],
    'components'          => [
        'slack'                    => [
            'httpClient' => ['class' => 'Curl\Curl'],
            'class'      => 'app\components\slack\Client',
            'url'        => 'https://hooks.slack.com/services/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',
            'username'   => 'Fastery',
        ],
        'urlManager'               => [
            'hostInfo' => 'Console',
        ],
        'queue'                    => [
            'class'     => \yii\queue\db\Queue::class,
            'db'        => 'db', // DB connection component or its config
            'tableName' => '{{%queue}}', // Table name
            'ttr' => 5 * 60,
            'channel'   => 'default', // Queue channel key
            'mutex'     => \yii\mutex\MysqlMutex::class, // Mutex used to sync queries
        ],
        'cache'                    => [
            'class' => 'yii\caching\FileCache',
        ],
        'user'                     => [
            'class'         => 'yii\web\User',
            'identityClass' => 'app\models\User',
            'enableSession' => false,
        ],
        'log'                      => [
            'targets' => [
                [
                    'class'      => 'yii\log\FileTarget',
                    'categories' => ['cron'],
                    'levels'     => ['error', 'warning', 'info'],
                    'logFile'    => '@app/runtime/logs/cron.log'
                ],
            ],
        ],
        'db'                       => $db,
        'db_sklad'                 => $dbs,
        'authManager'              => [
            'class' => 'yii\rbac\DbManager',
        ],
        'i18n'                     => [
            'translations' => [
                '*' => [
                    'class'    => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/messages',
                ],
            ],
        ],
        'skladSynchronizerProduct' => [
            'class' => 'app\components\SkladSynchronizerProduct',
        ],
        'workflowSource'           => [
            'class'            => 'raoul2000\workflow\source\file\WorkflowFileSource',
            'definitionLoader' => [
                'class'     => 'raoul2000\workflow\source\file\PhpClassLoader',
                'namespace' => 'app\workflow'
            ],
        ],
        'deliveryErrorFixer'       => [
            'class' => 'app\components\DeliveryErrorFixer',
        ],
        'deliveryStatusUpdater'    => [
            'class' => 'app\components\DeliveryStatusUpdater',
        ],
        'delivery'                 => [
            'class' => 'app\components\Delivery',
        ],
        'call'                     => [
            'class' => 'app\components\Call',
        ],
        'stat'                     => [
            'class' => 'app\components\Stat',
        ],
        'demo'                     => [
            'class' => 'app\components\DemoData',
        ]
    ],
    'params'              => $params,
];

if (YII_ENV_DEV) {
    $config['bootstrap'][]    = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
