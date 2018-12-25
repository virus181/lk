<?php
namespace app\delivery\boxberry\Parsel;

use app\delivery\boxberry\BaseBoxberry;

class Lists extends BaseBoxberry
{
    const METHOD_NAME = 'ParselList';

    public $method;

    /**
     * @param string $method
     * @param array $config
     */
    public function __construct(
        string $method = self::METHOD_NAME,
        array $config = []
    ) {
        $this->method = $method;

        parent::__construct($config);
    }

    /**
     * @return string
     */
    public function exec(): string
    {
        return ($this->sendRequest($this->getArr($this), '', 'get', false))->content;
    }
}

