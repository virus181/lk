<?php
namespace app\models\search;

use app\models\Call;
use kartik\date\DatePicker;
use kartik\daterange\DateRangePicker;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\rest\ViewAction;
use yii\web\Cookie;

class CommonSearch
{
    /** @var array */
    protected $userColumns;

    /**
     * @param string $column
     * @param array $data
     * @param $model
     * @param bool $isExport
     * @param bool $asLink
     * @param string $class
     * @return array
     */
    public function getColumn(string $column, array $data, $model, $isExport = false, $asLink = false, $class = ''): array
    {
        $columnSearch = [
            'attribute' => $column,
            'label' => $data['name'],
            'content' => function($model) use ($column, $data, $asLink, $class) {
                return $this->getExportColumnContent($column, $data, $model, $asLink, $class);
            }
        ];

        if (!$isExport) {
            $columnSearch['content'] = function ($model) use ($column, $data) {
                return $this->getColumnContent($column, $data, $model, isset($data['asLink']) ? $data['asLink'] : false);
            };
            $columnSearch['filterOptions'] = isset($data['filterOptions']) ? $data['filterOptions'] : [];
            $columnSearch['contentOptions'] = isset($data['contentOptions']) ? $data['contentOptions'] : [];
            $columnSearch['filter'] = $this->getColumnFilter($column, $data, $model, isset($data['list']) ? $data['list'] : null);
            $columnSearch['headerOptions'] = isset($data['headerOptions']) ? $data['headerOptions'] : [];
            $columnSearch['headerOptions']['data-resizable-column-id'] = $column;
        }

        return $columnSearch;
    }


    /**
     * @param string $column
     * @param array $data
     * @param $model
     * @param bool $asLink
     * @param string $class
     * @return null|string
     */
    public function getExportColumnContent(string $column, array $data, $model, $asLink = true, $class = ''): ?string
    {
        if ($class == CallSearch::CLASS_NAME) {
            /** @var CallSearch $model */
            return (string) (new CallSearch())->getExportColumnContent($column, $data, $model);
        }
        if ($class == ProductSearch::CLASS_NAME) {
            /** @var ProductSearch $model */
            return (string) (new ProductSearch())->getExportColumnContent($column, $data, $model);
        }
        if ($class == UserSearch::CLASS_NAME) {
            /** @var UserSearch $model */
            return (string) (new UserSearch())->getExportColumnContent($column, $data, $model);
        }
        if ($class == WarehouseSearch::CLASS_NAME) {
            /** @var WarehouseSearch $model */
            return (string) (new WarehouseSearch())->getExportColumnContent($column, $data, $model);
        }
        if ($class == ShopSearch::CLASS_NAME) {
            /** @var ShopSearch $model */
            return (string) (new ShopSearch())->getExportColumnContent($column, $data, $model);
        }
        if ($class == CourierSearch::CLASS_NAME) {
            /** @var ShopSearch $model */
            return (string) (new CourierSearch())->getExportColumnContent($column, $data, $model);
        }
        if ($class == LabelSearch::CLASS_NAME) {
            /** @var ShopSearch $model */
            return (string) (new LabelSearch())->getExportColumnContent($column, $data, $model);
        }
    }


    /**
     * @param string $column
     * @param array $data
     * @param ActiveRecord $model
     * @param array $dataList
     * @return string|null
     */
    protected function getColumnFilter(string $column, array $data, $model, $dataList): ?string
    {
        if (isset($data['type']) && $data['type'] == 'dateRange') {
            return Html::tag('div',
                DateRangePicker::widget([
                    'name' => $column,
                    'value' => $model->$column,
                    'convertFormat' => false,
                    'useWithAddon' => false,
                    'options' => ['class' => 'form-control input-xs', 'id' => 'order-create-date'],
                    'pluginOptions' => [
                        'locale' => [
                            'format' => 'DD.MM.YYYY',
                            'separator' => ' - ',
                        ]
                    ]
                ])
            );
        }
        if (isset($data['type']) && $data['type'] == 'date') {
            return Html::tag('div',
                DatePicker::widget([
                    'name' => $column,
                    'value' => $model->$column,
                    'language' => 'ru',
                    'pickerButton' => false,
                    'removeButton' => false,
                    'options' => ['class' => 'form-control input-xs', 'id' => 'order-create-date'],
                    'type' => DatePicker::TYPE_INPUT,
                    'pluginOptions' => [
                        'autoclose' => true,
                        'pickTime' => false,
                        'format' => 'dd.mm.yyyy',
                        'todayHighlight' => true,
                    ]
                ])
            );
        }
        if (isset($data['type']) && $data['type'] == 'dropdown') {
            return Html::tag('div', Html::dropDownList(
                $column,
                $model->$column,
                ArrayHelper::merge(
                    ['' => ''],
                    $dataList
                ),
                [
                    'class' => 'form-control input-xs' . (($model->$column === null || $model->$column === '') ? '' : ' selected'),
                ]
            ),
                ['class' => 'select_wrapper']
            );
        }
        return null;
    }


    /**
     * @param string $column
     * @param array $data
     * @param ActiveRecord $model
     * @param bool $asLink
     * @return string
     */
    public function getColumnContent(string $column, array $data, $model, $asLink = true): string
    {
        $fName = $data['function'];
        $text = $model->$fName() ? $model->$fName() : '';
        if ($text && $asLink) {
            $options = [];
            if (isset($data['url']['data'])) {
                foreach ($data['url']['data'] as $k => $datum) {
                    $options[$k] = $model->$datum();
                }
            }
            if (isset($data['url']['options'])) {
                foreach ($data['url']['options'] as $k => $datum) {
                    $options[$k] = $datum;
                }
            }
            if (isset($data['url']['text'])) {
                return Html::a($data['url']['text'], $text, $options);
            } else {
                if (isset($data['url']['subLink'])) {
                    $sLink = sprintf($data['url']['subLink'], $model->{$data['url']['subData']});
                } else {
                    $sLink = '#';
                }
                return Html::a($text, $sLink, $options);
            }

        }
        return $text;
    }


    /**
     * @param array $data
     * @param string $key
     * @return $this
     */
    public function setUserColumns(array $data, string $key)
    {
        $this->userColumns = $data;
        $cookies = Yii::$app->request->cookies;
        $cookies->readOnly = false;
        if ($cookies->has($key)) {
            $cookies->remove($key);
        }

        $nC = new Cookie();
        $nC->name = $key;
        $nC->value = json_encode($data);
        $nC->path = '/';
        Yii::$app->getResponse()->getCookies()->add($nC);

        return $this;
    }

    /**
     * @param array $columns
     * @param string $key
     * @return array
     */
    public function getUserColumns(array $columns, string $key): array
    {
        $cookies = Yii::$app->request->cookies;
        return json_decode($cookies->getValue($key, $this->getDefaultUserColumns($columns)), true);
    }

    /**
     * @param array $columns
     * @return string
     */
    public function getDefaultUserColumns(array $columns): string
    {
        $result = [];
        foreach ($columns as $column => $data) {
            $result[$column] = $data['default'] ? 1 : 0;
        }
        return json_encode($result);
    }
}