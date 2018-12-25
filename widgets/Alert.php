<?php
namespace app\widgets;

use Yii;

class Alert extends \yii\bootstrap\Alert
{
    public $alerts = [];

    public $timeout = 10000;

    public function init()
    {
        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->getId();
        }

        foreach (Yii::$app->session->getAllFlashes(true) as $type => $message) {
            if ($type == 'danger') {
                $this->timeout = false;
            }
            $this->options['class'] = 'alert-' . $type;
            $this->alerts[] = $message;
        }

        $this->initOptions();

        if ($this->alerts) {
            echo Html::beginTag('div', $this->options) . "\n";
            echo $this->renderBodyBegin() . "\n";
            foreach ($this->alerts as $alert) {
                if (is_array($alert)) {
                    foreach ($alert as $index => $allertText) {
                        echo $allertText . "<br>";
                    }
                } else {
                    $allertText = $alert;
                    echo $allertText . "<br>";
                }
            }
        }
    }

    public function run()
    {
        if ($this->alerts) {
            echo "\n" . $this->renderBodyEnd();
            echo "\n" . Html::endTag('div');

            $this->registerPlugin('alert');
        }

        if ($this->timeout !== false) {
            $id = $this->id;
            Yii::$app->view->registerJs(<<<JS
                setTimeout(function() {
                    $("#$id").alert('close');
                }, parseInt($this->timeout));
JS
            );
        }

    }
}