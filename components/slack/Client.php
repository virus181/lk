<?php

namespace app\components\slack;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\Json;
use yii\log\Logger;

class Client extends Component
{
    public $url;
    public $username;
    public $emoji;
    public $defaultText = "Message from Yii application";

    /** @var string|object */
    public $httpClient = '';

    public function init()
    {
        if (!$this->httpClient) {
            throw new InvalidConfigException("Client::httpClient cannot be empty .");
        }

        $this->httpClient = Yii::createObject($this->httpClient);
        if (!method_exists($this->httpClient, 'post')) {
            throw new InvalidConfigException("Client::httpClient post method must exist .");
        }
    }

    public function getLevelColor($level)
    {
        $colors = [
            Logger::LEVEL_ERROR => 'danger',
            Logger::LEVEL_WARNING => 'danger',
            Logger::LEVEL_INFO => 'good',
            Logger::LEVEL_PROFILE => 'warning',
            Logger::LEVEL_TRACE => 'warning',
        ];
        if (!isset($colors[$level])) {
            return 'good';
        }
        return $colors[$level];
    }

    public function send($text = null, $icon = null, $attachments = [])
    {
        $this->httpClient->post($this->url, [
            'payload' => Json::encode($this->getPayload($text, $icon, $attachments)),
        ]);
    }

    protected function getPayload($text = null, $icon = null, $attachments = [])
    {
        if ($text === null) {
            $text = $this->defaultText;
        }

        $payload = [
            'text' => $text,
            'username' => $this->username,
            'fallback' => $attachments[0]['fallback'],
            'color' => $attachments[0]['color'],
            'fields' => $attachments[0]['fields'],
        ];
        if ($icon !== null) {
            $payload['icon_emoji'] = $icon;
        }
        return $payload;
    }

}