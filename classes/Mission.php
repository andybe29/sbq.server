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
    const TABLE         = parent::TABLE_MISSIONS;
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
     * Чтение отдельной записи по полю launch
     * @param string $launchUuid uuid пуска
     * @return mixed запись либо false в случае фейла
     *
     */
    public function launch(string $launchUuid = '')
    {
        return $this->_read(self::TABLE, parent::COLUMN_LAUNCH, $launchUuid);
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
     * @param array $incomeData массив id агентств
     * @return boolean результат выполнения операции
     */
    public function replaceAgencies(array $incomeData = [])
    {
        return $this->_replaceAgencies(parent::TABLE_MISSIONS2AGENCIES, $incomeData);
    }

    /**
     * Парсинг ноды mission
     * @param string $uuid uuid пуска
     * @param array  $node массив ноды
     * @return mixed массив с данными для self::replace либо false
     */
    public static function parseNode(string $uuid = '', array $node = [])
    {
        if (empty($uuid) or empty($node)) return false;

        return [
            parent::COLUMN_ID          => $node['id'],
            parent::COLUMN_NAME        => $node['name'],
            parent::COLUMN_TYPE        => array_search($node['type'], MissionType::MISSION_TYPES, true),
            parent::COLUMN_DESCRIPTION => $node['description'],
            parent::COLUMN_LAUNCH      => $uuid,
            parent::COLUMN_ORBIT       => $node['orbit']['id']
        ];
    }
}