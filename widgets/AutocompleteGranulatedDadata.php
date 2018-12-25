<?php
namespace app\widgets;

use Yii;
use yii\base\Widget;
use yii\db\BaseActiveRecord;

class AutocompleteGranulatedDadata extends Widget
{
    const ADDRESS_TYPE = 'ADDRESS';

    /** @var string */
    public $token;
    /** @var string */
    public $type;
    /** @var string */
    public $bounds;
    /** @var BaseActiveRecord */
    public $model;
    /** @var string */
    public $modelName = 'Address';
    /** @var string */
    public $constraints;
    /** @var string */
    public $variable;
    /** @var string */
    public $onSelelct;
    /** @var array */
    public $options = ['class' => 'form-control'];
    /** @var string */
    public $attribute;
    /** @var string */
    public $serviceUrl = 'https://suggestions.dadata.ru/suggestions/api/4_1/rs';

    /** @var string */
    public $count = 10;

    public $css = '.suggestions-wrapper {position: absolute;}';
    public $inputId;

    public $afterSelect = <<<JS
JS;



    public function init()
    {
        if (!$this->token) {
            $this->token = Yii::$app->params['dadata.token'];
        }
        if (!$this->type) {
            $this->type = self::ADDRESS_TYPE;
        }
        if (!$this->inputId) {
            $this->inputId = Html::getInputId($this->model, $this->attribute);
        }
        if ($this->onSelelct === null) {

                $this->onSelelct = <<<JS
                    var oldCity = $('[name="$this->modelName[city]"]').val();
                    $('[name="$this->modelName[region]"]').val(suggestion.data.region_with_type);
                    $('[name="$this->modelName[region_fias_id]"]').val(suggestion.data.region_fias_id);
                    $('[name="$this->modelName[city]"]').val(suggestion.data.city_with_type);
                    $('[name="$this->modelName[city_fias_id]"]').val(suggestion.data.city_fias_id);
                    $('[name="$this->modelName[street]"]').val(suggestion.data.street_with_type);
                    $('[name="$this->modelName[street_fias_id]"]').val(suggestion.data.street_fias_id);
                    $('[name="$this->modelName[house]"]').val(suggestion.data.house);
                    $('[name="$this->modelName[flat]"]').val(suggestion.data.flat);
                    $('[name="$this->modelName[housing]"]').val(suggestion.data.block);
                    $('[name="$this->modelName[postcode]"]').val(suggestion.data.postal_code);
                    $('[name="$this->modelName[address_object]"]').val(JSON.stringify(suggestion));
JS;
            
        }
        if ($this->onSelelct === false) {
            $this->onSelelct = <<<JS
JS;
        }

        parent::init();
    }

    public function run()
    {
        if ($this->css) {
            Yii::$app->view->registerCss($this->css);
        }

        Yii::$app->view->registerAssetBundle('mazurva\web\DaDataAsset');

        $callback = <<<JS
            function d (suggestion) {
                $this->onSelelct;
                $this->afterSelect;
            }
JS;

        $additionalOptions = '';
        $variable = '';
        if ($this->bounds) {
            $additionalOptions = 'bounds: "'.$this->bounds.'",' . "\n";
        }
        if ($this->constraints) {
            $variable = 'var $' . $this->variable . ' = ' . $this->constraints .';';
            $additionalOptions .= 'constraints: $'.$this->variable.',' . "\n";
        }
        Yii::$app->view->registerJs(<<<JS
            $variable
            $("#$this->inputId").suggestions({
                serviceUrl: "$this->serviceUrl",
                token: "$this->token",
                type: "$this->type",
                $additionalOptions
                count: "$this->count",
                onSelect: $callback
            });
JS
        );

        return Html::activeTextInput($this->model, $this->attribute, $this->options);
    }

}