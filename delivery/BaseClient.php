<?php
namespace app\delivery;

use yii\httpclient\Client;

class BaseClient extends Client
{
    protected $_transport = 'yii\httpclient\CurlTransport';
}