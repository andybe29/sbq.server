<?php
/**
 * Миссии
 * @author andy.bezbozhny <andy.bezbozhny@gmail.com>
 */
class Mission extends SpaceBotequeDBase
{
    /**
     * @const TABLE         наименование таблицы сущности
     * @const TABLE_COLUMNS список полей в таблице сущности
     */
    const TABLE = parent::TABLE_MISSIONS;
    const TABLE_COLUMNS = [
        parent::COLUMN_ID,
        parent::COLUMN_NAME,
        parent::COLUMN_TYPE,
        parent::COLUMN_DESCRIPTION,
        parent::COLUMN_LAUNCH,
        parent::COLUMN_ORBIT
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

    /**
     * Запись агентств в SpaceBotequeDBase::TABLE_MISSIONS2AGENCIES
     * @param int   $missionId  id миссии
     * @param array $incomeData массив id агентств
     * @return boolean результат выполнения операции
     */
    public function replaceAgencies(int $missionId = 0, array $incomeData = [])
    {
        if ($missionId <= 0) return false;

        $this->sql->str = 'DELETE FROM ' . parent::TABLE_MISSIONS2AGENCIES . ' WHERE ' . parent::COLUMN_MISSION . ' = ' . $missionId;
        $this->sql->execute();

        $incomeData = array_map('intval', $incomeData);
        $incomeData = array_filter($incomeData, function($agencyId) { return ($agencyId > 0); });

        if (empty($incomeData)) return true;

        $values = array_map(function($agencyId) use ($missionId) { return implode(', ', [$missionId, $agencyId]); }, $incomeData);

        $this->sql->str   = [];
        $this->sql->str[] = 'INSERT INTO ' . parent::TABLE_MISSIONS2AGENCIES;
        $this->sql->str[] = '(' . parent::COLUMN_MISSION . ', ' . parent::COLUMN_AGENCY . ')';
        $this->sql->str[] = 'VALUES (' . implode('), (', $values) . ')';
        return (false !== $this->sql->execute());
    }
}