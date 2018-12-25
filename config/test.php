<?php
return [
    'id'         => 'basic-tests',
    'language'   => 'en-US',
    'components' => [
        'mailer'     => [
            'useFileTransport' => true,
        ],
        'urlManager' => [
            'showScriptName' => true,
        ],
        'db'             => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=fastery_test',
            'username' => 'root',
            'password' => 'root',
            'charset' => 'utf8',
            'enableSchemaCache' => true,
        ]
    ],
];