<?php
namespace app\models\Helper;

class Phone
{
    private const PROVIDER_CODES = [
        'sipout.net'
    ];

    /** @var string */
    private $phone;

    /**
     * @param string $phone
     */
    public function __construct(string $phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return string
     */
    public function getClearPhone(): string
    {
        $phone = preg_replace('/\W|_/', "", $this->phone);
        $first = substr($phone, 0, 1);
        if ($first == 8 || $first == 7) {
            $phone = substr($phone, 1);
        }
        return $phone;
    }

    /**
     * @return string
     */
    public function getHumanView(): string
    {
        $phone = $this->getClearPhone();
        $phoneCode = substr($phone, 0, 3);
        $phoneEndFirst = substr($phone, 3, 3);
        $phoneEndMid = substr($phone, 6, 2);
        $phoneEndLast = substr($phone, 8, 2);
        return '+7' . ' (' . $phoneCode . ') ' . $phoneEndFirst . '-' . $phoneEndMid . '-' . $phoneEndLast;
    }

    /**
     * @return array
     */
    public function getProviderCodes(): array
    {
        return self::PROVIDER_CODES;
    }
}