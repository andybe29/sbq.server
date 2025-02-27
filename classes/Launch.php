<?php
/**
 * Пуски
 * @author andy.bezbozhny <andy.bezbozhny@gmail.com>
 */
class Launch extends SpaceBotequeDBase
{
    /**
     * @const LLAPI_URI          URI для получения списка значений
     * @const LLAPI_URI_UPCOMING URI для получения списка предстоящих пусков
     */
    const LLAPI_URI          = '/launches/';
    const LLAPI_URI_UPCOMING = '/launches/upcoming/';

    /**
     * @const TABLE         наименование таблицы сущности
     * @const TABLE_COLUMNS список полей в таблице сущности
     */
    const TABLE         = parent::TABLE_LAUNCHES;
    const TABLE_COLUMNS = [
        parent::COLUMN_UUID,
        parent::COLUMN_NAME,
        parent::COLUMN_LAUNCHSTATUS,
        parent::COLUMN_ROCKET,
        parent::COLUMN_ROCKETCONFIGURATION,
        parent::COLUMN_PAD,
        parent::COLUMN_LOCATION,
        parent::COLUMN_NET,
        parent::COLUMN_WINDOWSTART,
        parent::COLUMN_WINDOWEND,
        parent::COLUMN_UPDATED
    ];

    public function __construct($sql)
    {
        parent::__construct($sql);
    }

    /**
     * Список всех записей
     * @return mixed массив записей либо false в случае фейла
     */
    public function all()
    {
        return $this->_all(self::TABLE, parent::COLUMN_UUID);
    }

    /**
     * Чтение отдельной записи
     * @param int $incomeId id записи
     * @return mixed запись либо false в случае фейла
     *
     */
    public function read(int $incomeId = 0)
    {
        return $this->_read(self::TABLE, parent::COLUMN_UUID, $incomeId);
    }

    /**
     * Создание записи
     * @param array $incomeData массив значений
     * @return boolean результат выполнения операции
     */
    public function replace(array $incomeData = [])
    {
        return $this->_replace(self::TABLE, $incomeData);
    }

    /**
     * Парсинг ноды из launches
     * @param array $node массив ноды
     * @return mixed массив с данными для self::replace либо false
     */
    public static function parseNode(array $node = [])
    {
        if (empty($node)) return false;

        return [
            parent::COLUMN_UUID                => $node['id'],
            parent::COLUMN_NAME                => $node['name'],
            parent::COLUMN_LAUNCHSTATUS        => $node['status']['id'],
            parent::COLUMN_ROCKET              => $node['rocket']['id'],
            parent::COLUMN_ROCKETCONFIGURATION => $node['rocket']['configuration']['id'],
            parent::COLUMN_PAD                 => $node['pad']['id'],
            parent::COLUMN_LOCATION            => $node['pad']['location']['id'],
            parent::COLUMN_NET                 => $node['net'],
            parent::COLUMN_WINDOWSTART         => $node['window_start'],
            parent::COLUMN_WINDOWEND           => $node['window_end'],
            parent::COLUMN_UPDATED             => $node['last_updated']
        ];
    }
}