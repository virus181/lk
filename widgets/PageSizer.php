<?php

namespace app\widgets;

use yii\base\InvalidConfigException;
use yii\bootstrap\Html;
use yii\bootstrap\Widget;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

class PageSizer extends Widget
{
    public $options = ['class' => 'pagination pagination-sm pull-right'];

    public $itemOptions = ['rel' => 'nofollow'];

    public $activeItemClass = 'active';

    public $labelText = 'per page';

    /**
     * @var Pagination
     */
    public $pagination;

    private $currentPageSize;

    private $sizeParam;

    private $pages;

    public function __construct($config = [])
    {
        if (($pagination = ArrayHelper::getValue($config, 'pagination', null)) && !($pagination instanceof Pagination)) {
            throw new InvalidConfigException('The "pagination" property must instace os Pagination.');
        }
        parent::__construct($config);
    }

    public function init()
    {
        parent::init();

        $this->currentPageSize = $this->pagination->getCurrentPageSize();
        $this->pages = $this->pagination->pages;
        $this->sizeParam = $this->pagination->sizeParam;
    }


    public function run()
    {
        $pageCount = $this->pagination->getPageCount();
        if ($pageCount > 1) {
            return $this->renderButtons();
        }

        return '';
    }

    public function renderButtons()
    {
        $li = [];

        foreach ($this->pages as $page) {
            if ($this->currentPageSize == $page) {
                $options = ArrayHelper::merge($this->itemOptions, ['class' => $this->activeItemClass]);
                $li[] = Html::tag('li', '<span>' . $page . '</span>', $options);

            } else {
                $li[] = Html::tag('li', Html::a($page, Url::to('?' . $this->sizeParam . '=' . $page)), $this->itemOptions);
            }
        }

        if ($li) {
            $li[] = Html::tag('li', '<span class="text">' . $this->labelText . '</span>');
            return Html::tag('ul', implode('', $li), $this->options);
        }

        return null;
    }
}