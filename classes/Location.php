<?php
/**
 * Локации
 * @author andy.bezbozhny <andy.bezbozhny@gmail.com>
 */
class Location extends SpaceBotequeDBase
{
    /**
     * @const LLAPI_URI     URI для получения списка значений
     * @const TABLE         наименование таблицы сущности
     * @const TABLE_COLUMNS список полей в таблице сущности
     */
    const LLAPI_URI     = '/locations/';
    const TABLE         = parent::TABLE_LOCATIONS;
    const TABLE_COLUMNS = [
        parent::COLUMN_ID,
        parent::COLUMN_NAME,
        parent::COLUMN_COUNTRYCODE,
        parent::COLUMN_DESCRIPTION,
        parent::COLUMN_LATITUDE,
        parent::COLUMN_LONGITUDE
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
     * Список пусковых площадок космодрома
     * @param int $incomeId id записи
     * @return mixed массив записей либо false в случае фейла
     */
    public function pads(int $incomeId = 0)
    {
        if ($incomeId <= 0) return false;

        $this->sql->str   = [];
        $this->sql->str[] = 'SELECT * FROM pads WHERE id IN';
        $this->sql->str[] = '(SELECT pad FROM launches WHERE location = ' . $incomeId . ')';
        $this->sql->execute();

        $data = $this->sql->rows ? $this->sql->all() : ($this->sql->err ? false : []);
        $this->sql->free();

        return $data ? array_map(['parent', 'typeCasting'], $data) : $data;
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