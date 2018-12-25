<?php
namespace app\api\models;

use app\api\models\Suggestion\Data;

class Suggestion
{
    /** @var string */
    public $value;

    /** @var string */
    public $unrestrictedValue;

    /** @var Data */
    public $data;

    /**
     * @param string $value
     * @param string $unrestrictedValue
     * @param Data $data
     */
    public function __construct(string $value, string $unrestrictedValue, Data $data)
    {
        $this->value = $value;
        $this->unrestrictedValue = $unrestrictedValue;
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getUnrestrictedValue(): string
    {
        return $this->unrestrictedValue;
    }

    /**
     * @return Data
     */
    public function getData(): Data
    {
        return $this->data;
    }
}