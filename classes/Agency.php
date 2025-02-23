<?php
/**
 * @author andy.bezbozhny <andy.bezbozhny@gmail.com>
 */
class Agency
{
    /**
     * @const LLAPI_URI URI для получения списка агентств
     */
    const LLAPI_URI = '/agencies';

    /**
     * @const TABLE название таблицы
     * @const COLUMN_ID          поле для id
     * @const COLUMN_NAME        поле для name
     * @const COLUMN_ABBREV      поле для abbrev
     * @const COLUMN_COUNTRYCODE поле для countryCode
     * @const COLUMN_DESCRIPTION поле для description
     * @const COLUMN_INFOURL     поле для infoURL
     * @const COLUMN_WIKIURL     поле для wikiURL
     */
    const TABLE              = 'agencies';
    const COLUMN_ID          = 'id';
    const COLUMN_NAME        = 'name';
    const COLUMN_ABBREV      = 'abbrev';
    const COLUMN_COUNTRYCODE = 'countryCode';
    const COLUMN_DESCRIPTION = 'description';
    const COLUMN_INFOURL     = 'infoURL';
    const COLUMN_WIKIURL     = 'wikiURL';

    static $columns = [
        self::COLUMN_ID,
        self::COLUMN_NAME,
        self::COLUMN_ABBREV,
        self::COLUMN_COUNTRYCODE,
        self::COLUMN_DESCRIPTION,
        self::COLUMN_INFOURL,
        self::COLUMN_WIKIURL
    ];

    private $sql;

    /**
     * @var int    $id          id агентства
     * @var string $name        наименование
     * @var string $abbrev      краткое наименование
     * @var string $countryCode код страны
     * @var string $description описание
     * @var string $infoURL     ссылка на сайт агентства
     * @var string $wikiURL     ссылка на статью в Wikipedia
     */
    private $id;

    public function __construct($sql)
    {
        $this->sql = $sql;
    }

    public function __get($key)
    {
        return (isset($this->$key) and in_array($key, self::$columns)) ? $this->$key : null;
    }

    public function __isset($key)
    {
        return isset($this->$key);
    }

    public function __set($key, $value)
    {
        $this->$key = (self::COLUMN_ID == $key and ($value = (int)$value) > 0) ? $value : null;
    }

    /**
     * Список всех записей
     * @return mixed массив данных либо false в случае фейла
     */
    public function lista()
    {
        SpaceBoteque::$error = null;

        $this->sql->str = 'SELECT * FROM ' . self::TABLE . ' ORDER BY ' . self::COLUMN_ID;
        $this->sql->execute();

        $data = $this->sql->err ? false : $this->sql->all();
        $this->sql->free();

        return $data ? array_map(['self', '_prepare'], $data) : $data;
    }

    /**
     * Чтение отдельной записи
     * @param int $incomeId id записи
     * @return mixed запись либо false в случае фейла
     *
     */
    public function read(int $incomeId = 0)
    {
        $this->id = null;
        SpaceBoteque::$error = null;

        $id = (0 < $incomeId) ? $incomeId : null;

        if (empty($id)) {
            SpaceBoteque::$error = new stdClass;
            SpaceBoteque::$error->method  = __METHOD__;
            SpaceBoteque::$error->message = 'Empty Value';
            SpaceBoteque::$error->value   = $incomeId;
        }
        if (SpaceBoteque::$error) return false;

        $this->sql->str = 'SELECT * FROM ' . self::TABLE . ' WHERE ' . self::COLUMN_ID . ' = ' . $id;
        $this->sql->execute();

        if ($this->sql->err) {
            # ошибка MySQL
        } else if ($data = $this->sql->rows ? $this->sql->assoc() : []) {

            $data = self::_prepare($data);
            $this->id = $data['id'];

        } else {
            SpaceBoteque::$error = new stdClass;
            SpaceBoteque::$error->method  = __METHOD__;
            SpaceBoteque::$error->message = 'Record Not Found';
            SpaceBoteque::$error->value   = $id;
        }
        $this->sql->free();

        return ($this->sql->err or SpaceBoteque::$error) ? false : $data;
    }

    /**
     * Создание записи
     * @param array $incomeData массив значений
     * @return boolean результат выполнения операции
     */
    public function replace(array $incomeData = [])
    {
        SpaceBoteque::$error = null;

        $data = array_filter($incomeData, function($key) { return in_array($key, self::$columns); }, ARRAY_FILTER_USE_KEY);

        if (empty($data)) {
            SpaceBoteque::$error = new stdClass;
            SpaceBoteque::$error->method  = __METHOD__;
            SpaceBoteque::$error->message = 'array_filter';
            SpaceBoteque::$error->value   = $incomeData;
        }
        if (SpaceBoteque::$error) return false;

        foreach ($data as $key => $value) {
            $data[$key] = (self::COLUMN_ID == $key) ? (int)$value : (mb_strlen($value) ? $this->sql->varchar($value) : 'NULL');
        }

        return (false !== $this->sql->replace(self::TABLE, $data));
    }

    public static function _prepare($data)
    {
        foreach ($data as $key => $value) {
            $data[$key] = (self::COLUMN_ID == $key) ? (int)$value : $value;
        }

        return $data;
    }

}