<?php

namespace app\models;
use Yii;

/**
 * Helper model".
 */
class Helper
{
    const STATUS_ACTIVE = 10;
    const STATUS_BLOCKED = 0;

    const EMPTY_VALUE = '';

    const MIN_CACHE_VALUE = 3600;
    const DAY_CACHE_VALUE = 86400;

    /**
     * Возвращает сумму прописью
     *
     * @param int $sourceNumber
     * @return string
     */
    public static function numberToString($sourceNumber)
    {
        //Целое значение $sourceNumber вывести прописью по-русски
        //Максимальное значение для аругмента-числа PHP_INT_MAX
        //Максимальное значение для аругмента-строки минус/плюс 999999999999999999999999999999999999
        $smallNumbers = array( //Числа 0..999
            array('ноль'),
            array('', 'один', 'два', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'),
            array('десять', 'одиннадцать', 'двенадцать', 'тринадцать', 'четырнадцать',
                'пятнадцать', 'шестнадцать', 'семнадцать', 'восемнадцать', 'девятнадцать'),
            array('', '', 'двадцать', 'тридцать', 'сорок', 'пятьдесят', 'шестьдесят', 'семьдесят', 'восемьдесят', 'девяносто'),
            array('', 'сто', 'двести', 'триста', 'четыреста', 'пятьсот', 'шестьсот', 'семьсот', 'восемьсот', 'девятьсот'),
            array('', 'одна', 'две')
        );
        $degrees = array(
            array('дофигальон', '', 'а', 'ов'), //обозначение для степеней больше, чем в списке
            array('тысяч', 'а', 'и', ''), //10^3
            array('миллион', '', 'а', 'ов'), //10^6
            array('миллиард', '', 'а', 'ов'), //10^9
            array('триллион', '', 'а', 'ов'), //10^12
            array('квадриллион', '', 'а', 'ов'), //10^15
            array('квинтиллион', '', 'а', 'ов'), //10^18
            array('секстиллион', '', 'а', 'ов'), //10^21
            array('септиллион', '', 'а', 'ов'), //10^24
            array('октиллион', '', 'а', 'ов'), //10^27
            array('нониллион', '', 'а', 'ов'), //10^30
            array('дециллион', '', 'а', 'ов') //10^33
            //досюда написано в Вики по нашей короткой шкале: https://ru.wikipedia.org/wiki/Именные_названия_степеней_тысячи
        );

        if ($sourceNumber == 0) return $smallNumbers[0][0]; //Вернуть ноль
        $sign = '';
        if ($sourceNumber < 0) {
            $sign = 'минус '; //Запомнить знак, если минус
            $sourceNumber = substr($sourceNumber, 1);
        }
        $result = array(); //Массив с результатом

        //Разложение строки на тройки цифр
        $digitGroups = array_reverse(str_split(str_pad($sourceNumber, ceil(strlen($sourceNumber) / 3) * 3, '0', STR_PAD_LEFT), 3));
        foreach ($digitGroups as $key => $value) {
            $result[$key] = array();
            //Преобразование трёхзначного числа прописью по-русски
            foreach ($digit = str_split($value) as $key3 => $value3) {
                if (!$value3) continue;
                else {
                    switch ($key3) {
                        case 0:
                            $result[$key][] = $smallNumbers[4][$value3];
                            break;
                        case 1:
                            if ($value3 == 1) {
                                $result[$key][] = $smallNumbers[2][$digit[2]];
                                break 2;
                            } else $result[$key][] = $smallNumbers[3][$value3];
                            break;
                        case 2:
                            if (($key == 1) && ($value3 <= 2)) $result[$key][] = $smallNumbers[5][$value3];
                            else $result[$key][] = $smallNumbers[1][$value3];
                            break;
                    }
                }
            }
            $value *= 1;
            if (!$degrees[$key]) $degrees[$key] = reset($degrees);

            //Учесть окончание слов для русского языка
            if ($value && $key) {
                $index = 3;
                if (preg_match("/^[1]$|^\\d*[0,2-9][1]$/", $value)) $index = 1; //*1, но не *11
                else if (preg_match("/^[2-4]$|\\d*[0,2-9][2-4]$/", $value)) $index = 2; //*2-*4, но не *12-*14
                $result[$key][] = $degrees[$key][0] . $degrees[$key][$index];
            }
            $result[$key] = implode(' ', $result[$key]);
        }

        return $sign . implode(' ', array_reverse($result));
    }

    /**
     * Правильное склонение числительных
     *
     * @param int $number
     * @param array $endingArray
     * @return string
     */
    public static function getNumEnding($number, $endingArray)
    {
        $number = $number % 100;
        if ($number >= 11 && $number <= 19) {
            $ending = $endingArray[2];
        } else {
            $i = $number % 10;
            switch ($i) {
                case (1):
                    $ending = $endingArray[0];
                    break;
                case (2):
                case (3):
                case (4):
                    $ending = $endingArray[1];
                    break;
                default:
                    $ending = $endingArray[2];
            }
        }
        return $ending;
    }

    /**
     * Получает колонки для ActiveDataProvider исключая не нужные колонки
     *
     * @param array $data
     * @param array $columns
     * @param array $remove
     * @return array
     */
    public static function getDataProviderColumns(array $data, array $columns = [], array $remove = [])
    {
        $result = [];
        foreach ($data as $item) {
            if (!empty($remove) && isset($item['attribute']) && in_array($item['attribute'], $remove)) {
                unset($item['content']);
            }
            $result[] = $item;
        }
        return $result;
    }

    /**
     * Получение доступных методов доставки
     *
     * @return array
     */
    public static function getDeliveryMethods()
    {
        return [
            'courier' => Yii::t('app', 'Courier'),
            'point' => Yii::t('app', 'Point'),
            'mail' => Yii::t('app', 'Mail')
        ];
    }

    /**
     * Получить название типа доставкой
     * @param string $type
     * @return string
     */
    public static function getDeliveryTypeName($type): string
    {
        $names = (new OrderDelivery())->getDeliveryTypes();
        return isset($names[$type]) ? $names[$type] : '';
    }

    /**
     * Получить название метода оплаты
     * @param $payment
     * @return string
     * @internal param string $type
     */
    public static function getPaymentMethodName($payment): string
    {
        $names = (new Order())->getPaymentMethods();
        return isset($names[$payment]) ? $names[$payment] : '';
    }

    /**
     * Возвращает timestamp метку для даты в любом формате
     * @param string $date
     * @return int
     */
    public static function getDateTimestamp(string $date): int
    {
        if (!$date) {
            return strtotime(date('Y-m-d', time()));
        }

        if (is_numeric($date)) {
            return strtotime(date('Y-m-d', (int) $date));
        }

        return strtotime(date('Y-m-d', strtotime($date)));
    }

    /**
     * Получение времени
     * @deprecated
     * @param string $time
     * @return string
     */
    public static function getTime(string $time): string
    {
        $time = is_numeric($time) ? $time . ":00:00" : $time;
        return date("H:i:s", strtotime($time));
    }

    /**
     * @deprecated
     * @param string $phone
     * @return string
     */
    public static function getClearPhone(string $phone): string
    {
        $phone = preg_replace('/\W|_/', "", $phone);
        $first = substr($phone, 0, 1);

        if ($first == 8 || $first == 7) {
            $phone = substr($phone, 1);
        }

        return $phone;
    }

    /**
     * @deprecated
     * @param string $phone
     * @return string
     */
    public static function getNormalizePhone(string $phone): string
    {
        $phone = self::getClearPhone($phone);

        $phoneCode = substr($phone, 0, 3);

        $phoneEndFirst = substr($phone, 3, 3);
        $phoneEndMid = substr($phone, 6, 2);
        $phoneEndLast = substr($phone, 8, 2);

        return '+7' . ' (' . $phoneCode . ') ' . ' ' . $phoneEndFirst . '-' . $phoneEndMid . '-' . $phoneEndLast;
    }
}
