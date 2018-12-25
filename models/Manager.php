<?php
namespace app\models;

use Yii;

/**
 * Manager model".
 */
class Manager
{
    private const GROUP_IDS = [
        '0',
        '1',
        '2'
    ];

    private const MANAGER_LOCATIONS = [
        '1 1',
        '2 1',
        '3 1',
        '4 1',
        '5 1',
    ];

    const QUEUE_ALL = 'all';
    const QUEUE_MANAGER = 'manager';
    const QUEUE_PM = 'pm';
    const QUEUE_CELL_1 = 'cell_1';
    const QUEUE_CELL_2 = 'cell_2';
    const QUEUE_CELL_3 = 'cell_3';
    const QUEUE_CELL_4 = 'cell_4';
    const QUEUE_CELL_5 = 'cell_5';

    private const QUEUES = [
        self::QUEUE_ALL,
        self::QUEUE_MANAGER,
        self::QUEUE_PM,
        self::QUEUE_CELL_1,
        self::QUEUE_CELL_2,
        self::QUEUE_CELL_3,
        self::QUEUE_CELL_4,
        self::QUEUE_CELL_5,
    ];

    /**
     * @return array
     */
    public function getGroupIds(): array
    {
        return self::GROUP_IDS;
    }

    /**
     * @return array
     */
    public function getManagerLocations(): array
    {
        return self::MANAGER_LOCATIONS;
    }

    /**
     * @return array
     */
    public function getQueues(): array
    {
        foreach (self::QUEUES as $queue) {
            $result[$queue] = Yii::t('manager', $queue);
        }

        return $result ?? [];
    }
}
