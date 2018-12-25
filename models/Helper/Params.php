<?php
namespace app\models\Helper;

class Params
{
    /**
     * Получить отформатированное значение
     * @param string $type
     * @param int $index
     * @param array $data
     * @return float|int|string
     */
    public function getArrayParam(string $type, int $index, array $data)
    {
        switch ($type) {
            case 'string':
                return !empty($data[$index]) ? (string) $data[$index]: '';
            case 'int':
            case 'integer':
                return !empty($data[$index]) ? (int) $data[$index]: 0;
            case 'float':
                return !empty($data[$index]) ? (float) $data[$index]: 0;
            default:
                return null;
        }
    }
}