<?php
/**
 * Статусы пусков
 * @author andy.bezbozhny <andy.bezbozhny@gmail.com>
 */
class LaunchStatus extends SpaceBoteque
{
    /**
     * @const LLAPI_URI     URI для получения списка значений
     * @const TABLE         наименование таблицы сущности
     * @const TABLE_COLUMNS список полей в таблице сущности
     */
    const LLAPI_URI     = '/config/launch_statuses';
    const TABLE         = SpaceBotequeDBase::TABLE_LAUNCH_STATUSES;
    const TABLE_COLUMNS = [
        SpaceBotequeDBase::COLUMN_ID,
        SpaceBotequeDBase::COLUMN_NAME,
        SpaceBotequeDBase::COLUMN_ABBREV,
        SpaceBotequeDBase::COLUMN_DESCRIPTION
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
        return $this->_all(self::TABLE, SpaceBotequeDBase::COLUMN_ID);
    }

    /**
     * Чтение отдельной записи
     * @param int $incomeId id записи
     * @return mixed запись либо false в случае фейла
     *
     */
    public function read(int $incomeId = 0)
    {
        return $this->_read(self::TABLE, SpaceBotequeDBase::COLUMN_ID, $incomeId);
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

}