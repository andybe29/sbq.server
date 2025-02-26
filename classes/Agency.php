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

    /**
     * Парсинг ноды из mission.agencies
     * @param array массив ноды
     * @return mixed массив с данными для self::replace либо false
     */
    public static function parseNode(array $node = [])
    {
        if (empty($node)) return false;

        return [
            parent::COLUMN_ID          => $node['id'],
            parent::COLUMN_NAME        => $node['name'],
            parent::COLUMN_ABBREV      => $node['abbrev'],
            parent::COLUMN_COUNTRYCODE => $node['country'][0]['alpha_3_code'],
            parent::COLUMN_DESCRIPTION => $node['description'],
            parent::COLUMN_INFOURL     => (isset($node['info_url']) ? mb_ereg_replace('http:', 'https:', $node['info_url']) : ''),
            parent::COLUMN_WIKIURL     => (isset($node['wiki_url']) ? mb_ereg_replace('http:', 'https:', $node['wiki_url']) : '')
        ];
    }
}