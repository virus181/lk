<?php
namespace app\delivery\boxberry\Parsel;

use app\delivery\boxberry\BaseBoxberry;

class Send extends BaseBoxberry
{
    /** @var string */
    public $ImIds;
    public $method;

    const METHOD_NAME = 'ParselSend';

    /**
     * @param array $list
     * @param string $method
     * @param array $config
     */
    public function __construct(
        array $list,
        string $method = self::METHOD_NAME,
        array $config = []
    ) {
        $this->method = $method;
        $this->ImIds = implode(',', $list);

        parent::__construct($config);
    }

    /**
     * @return string
     */
    public function exec()
    {
        return ($this->sendRequest($this->getArr($this), '', 'get', false))->content;
    }
}

