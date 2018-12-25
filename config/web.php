<?php

use yii\web\Response;
use yii\web\UrlNormalizer;

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
    'id'             => 'basic',
    'basePath'       => dirname(__DIR__),
    'bootstrap'      => [
        'log',
        'queue',
        [
            'class'   => 'yii\filters\ContentNegotiator',
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
                'application/xml'  => Response::FORMAT_XML,
                'text/html'        => Response::FORMAT_HTML,
            ],
        ],
    ],
    'defaultRoute'   => 'main',
    'sourceLanguage' => 'en-US',
    'language'       => 'ru-RU',
    'name'           => 'Fastery.ru',
    'components'     => [
        'slack'                    => [
            'httpClient' => ['class' => 'Curl\Curl'],
            'class'      => 'app\components\slack\Client',
            'url'        => 'https://hooks.slack.com/services/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',
            'username'   => 'Fastery',
        ],
        'domParams'      => [
            'class' => 'app\components\Params'
        ],
        'queue'          => [
            'class'     => \yii\queue\db\Queue::class,
            'db'        => 'db', // DB connection component or its config
            'tableName' => '{{%queue}}', // Table name
            'channel'   => 'default', // Queue channel key
            'mutex'     => \yii\mutex\MysqlMutex::class, // Mutex used to sync queries
        ],
        'assetManager'   => [
            'bundles' => [
                'yii\web\JqueryAsset' => [
                    'sourcePath' => null,   // do not publish the bundle
                    'js'         => [
                        '//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js',
                    ]
                ],
            ],
        ],
        'request'        => [
            'cookieValidationKey' => 'QUgOoYSA-8jqc-AwDtT6z_z0hIPfAdlF',
            'parsers'             => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
        'cache'          => [
            'class' => 'yii\caching\MemCache',
            'useMemcached' => true,
        ],
        'user'           => [
            'identityClass'   => 'app\models\User',
            'enableAutoLogin' => true,
            'loginUrl'        => ['/main/login'],
        ],
        'errorHandler'   => [
            'errorAction' => 'main/error',
        ],
        'stat'                     => [
            'class' => 'app\components\Stat',
        ],
        'mailer'         => [
            'class'     => 'yii\swiftmailer\Mailer',
            'transport' => [
                'class'      => 'Swift_SmtpTransport',
                'host'       => 'smtp.yandex.ru',
                'username'   => 'robot@fastery.ru',
                'password'   => 'haoua9834hfal3478nhv',
                'port'       => '465',
                'encryption' => 'SSL',
            ],
        ],
        'formatter'      => [
            'class'                  => 'yii\i18n\Formatter',
            'nullDisplay'            => '',
            'locale'                 => 'ru-RU',
            'timeZone'               => 'Europe/Moscow',
            'currencyCode'           => 'RUB',
            'numberFormatterSymbols' => [
                NumberFormatter::CURRENCY_SYMBOL => '₽',
            ],
        ],
        'log'            => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets'    => [
                [
                    'class'  => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'maxFileSize' => 102400,
                    'maxLogFiles' => 10,
                ],
                [
                    'class'      => 'yii\log\FileTarget',
                    'logFile'    => '@runtime/logs/info.log',
                    'levels'     => ['info'],
                    'categories' => ['custom*'],
                    'logVars'    => [],
                    'maxFileSize' => 102400,
                    'maxLogFiles' => 10,
                ],
                [
                    'class'      => 'yii\log\FileTarget',
                    'logFile'    => '@runtime/logs/sklad.log',
                    'levels'     => ['info'],
                    'categories' => ['sklad*'],
                    'logVars'    => [],
                    'maxFileSize' => 102400,
                    'maxLogFiles' => 10,
                ],
                [
                    'class'      => 'yii\log\FileTarget',
                    'logFile'    => '@runtime/logs/apiship.log',
                    'levels'     => ['info'],
                    'categories' => ['apiship*'],
                    'logVars'    => [],
                    'maxFileSize' => 102400,
                    'maxLogFiles' => 10,
                ],
            ],
        ],
        'db'             => $db,
        'db_sklad'       => $dbs,
        'urlManager'     => [
            'enablePrettyUrl'     => true,
            'enableStrictParsing' => true,
            'showScriptName'      => false,
            'baseUrl'             => '/',
            'normalizer'          => [
                'class'  => 'yii\web\UrlNormalizer',
                'action' => UrlNormalizer::ACTION_REDIRECT_PERMANENT,
            ],
            'rules'               => [
                '<module:api>/<controller>s/'                                        => '<module>/<controller>/index',
                '<module:api>/<controller>/<action>'                                 => '<module>/<controller>/<action>',
                '<module:api>/v2/<controller>/<action>'                              => '<module>/<controller>/<action>_v2',
                '<module:api>/<controller>/<action:view|update|set-status|set-payment-method>/<id:\d+>' => '<module>/<controller>/<action>',
                '/'                                                                  => 'main/index',
                '/login'                                                             => 'main/login',
                '/forgot-password'                                                   => 'main/forgot-password',
                '/reset-password'                                                    => 'main/reset-password',
                '<action:\w+[^s]$>'                                                  => 'main/<action>',
                '<controller>s/'                                                     => '<controller>/index',
                '<controller>/<id:\d+>/<action>/<status:\w+>'                        => '<controller>/<action>',
                '<controller>/<id:\d+>/<action>'                                     => '<controller>/<action>',
                '<controller>/<id:\d+>'                                              => '<controller>/view',
                '<controller>/<action>'                                              => '<controller>/<action>',
            ],
        ],
        'authManager'    => [
            'class' => 'yii\rbac\DbManager',
            'cache' => [
                'class' => 'yii\caching\FileCache'
            ],
        ],
        'i18n'           => [
            'translations' => [
                '*' => [
                    'class'    => 'yii\i18n\PhpMessageSource',
                    'fileMap'  => [
                        'app'   => 'app.php',
                        'order' => 'order.php',
                    ],
                    'basePath' => '@app/messages',
                ],
            ],
        ],
        'workflowSource' => [
            'class'            => 'raoul2000\workflow\source\file\WorkflowFileSource',
            'definitionLoader' => [
                'class'     => 'raoul2000\workflow\source\file\PhpClassLoader',
                'namespace' => 'app\workflow'
            ],
        ],
    ],
    'modules'        => [
        'api'      => [
            'class' => 'app\api\Module',
        ],
        'admin'    => [
            'class' => 'mdm\admin\Module',
        ],
        'gridview' => [
            'class' => 'kartik\grid\Module',
        ],
    ],
    'as access'      => [
        'class'        => 'app\rbac\AccessControl',
        'allowActions' => [
            'main/login',
            'main/signup',
            'main/forgot-password',
            'main/reset-password',
            'main/doc',
            'gii/*',
            'api/order/tracking',
            'api/address/full',
            'debug/*',
        ]
    ],
    'params'         => $params,
];

if (YII_ENV_DEV) {
    $config['bootstrap'][]      = 'debug';
    $config['modules']['debug'] = [
        'class'      => 'yii\debug\Module',
        'panels'     => [
            'queue' => \yii\queue\debug\Panel::class,
        ],
        'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][]    = 'gii';
    $config['modules']['gii'] = [
        'class'      => 'yii\gii\Module',
        'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
