<?php
namespace app\widgets;

use Yii;

class ActiveField extends \yii\bootstrap\ActiveField
{
    public $template = "{label}\n{input}\n{hint}";

    public function init()
    {
        $formId = $this->form->id;
        Yii::$app->view->registerJs(<<<JS
                setTimeout(function() {
                    var selector = '[data-toggle=tooltip-error]:visible';
                    $(selector).tooltip({
                        placement: 'bottom',
                        trigger: 'manual'
                    });
                    $(selector).tooltip('show');
                    $('body').find('#$formId input').on('keydown', function() {
                        $(this).parents(selector).tooltip('hide');
                        $(this).tooltip('hide');
                    });
                }, 50);
JS
        );
    }

    public function error($options = [])
    {
        if ($options === false) {
            $this->parts['{error}'] = '';
            return $this;
        }

        $attribute = Html::getAttributeName($this->attribute);
        if ($this->model->hasErrors($attribute)) {
            $this->options['data-toggle'] = 'tooltip-error';
            $this->options['data-title'] = $this->model->getFirstError($attribute);
        }

        $options = array_merge($this->errorOptions, $options);
        $this->parts['{error}'] = Html::error($this->model, $this->attribute, $options);

        return $this;
    }
}