<?php
/**
 * Агенства / Операторы пусков и миссий
 * @author andy.bezbozhny <andy.bezbozhny@gmail.com>
 */
class Agency extends SpaceBotequeDBase
{
    /**
     * @const LLAPI_URI     URI для получения списка значений
     * @const TABLE         наименование таблицы сущности
     * @const TABLE_COLUMNS список полей в таблице сущности
     */
    const LLAPI_URI = '/agencies';
    const TABLE     = parent::TABLE_AGENCIES;
    const TABLE_COLUMNS = [
        parent::COLUMN_ID,
        parent::COLUMN_NAME,
        parent::COLUMN_ABBREV,
        parent::COLUMN_COUNTRYCODE,
        parent::COLUMN_DESCRIPTION,
        parent::COLUMN_INFOURL,
        parent::COLUMN_WIKIURL
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
        return $this->_all(self::TABLE, parent::COLUMN_ID);
    }

    /**
     * Чтение отдельной записи
     * @param int $incomeId id записи
     * @return mixed запись либо false в случае фейла
     *
     */
    public function read(int $incomeId = 0)
    {
        return $this->_read(self::TABLE, parent::COLUMN_ID, $incomeId);
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