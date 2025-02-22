<?php
/**
 * @author andy.bezbozhny <andy.bezbozhny@gmail.com>
 */
class Pad
{
    /**
     * @const LLAPI_URI URI для получения списка пусковых площадок
     */
    const LLAPI_URI = '/pads';

    /**
     * @const TABLE название таблицы
     * @const COLUMN_ID          поле для id
     * @const COLUMN_NAME        поле для name
     * @const COLUMN_COUNTRYCODE поле для countryCode
     * @const COLUMN_DESCRIPTION поле для description
     * @const COLUMN_LATITUDE    поле для latitude
     * @const COLUMN_LONGITUDE   поле для longitude
     */
    const TABLE              = 'pads';
    const COLUMN_ID          = 'id';
    const COLUMN_NAME        = 'name';
    const COLUMN_COUNTRYCODE = 'countryCode';
    const COLUMN_DESCRIPTION = 'description';
    const COLUMN_LATITUDE    = 'latitude';
    const COLUMN_LONGITUDE   = 'longitude';

    static $columns = [
        self::COLUMN_ID,
        self::COLUMN_NAME,
        self::COLUMN_COUNTRYCODE,
        self::COLUMN_DESCRIPTION,
        self::COLUMN_LATITUDE,
        self::COLUMN_LONGITUDE
    ];

    static $floatColumns = [
        self::COLUMN_LATITUDE,
        self::COLUMN_LONGITUDE
    ];

    private $sql;

    /**
     * @var int    $id          id пусковой площадки
     * @var string $name        наименование
     * @var string $countryCode код страны
     * @var string $description описание
     * @var float  $latitude    широта
     * @var float  $longitude   долгота
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
            if (null == $value or mb_strlen($value) == 0) {
                $data[$key] = 'NULL';
            } else if (in_array($key, self::$floatColumns)) {
                $data[$key] = empty($value) ? 'NULL' : $value;
            } else {
                $data[$key] = (self::COLUMN_ID == $key) ? (int)$value : $this->sql->varchar($value);
            }
        }

        return (false !== $this->sql->replace(self::TABLE, $data));
    }

    public static function _prepare($data)
    {
        foreach ($data as $key => $value) {
            if (in_array($key, self::$floatColumns)) {
                $data[$key] = ($value === null) ? null : (float)$value;
            } else {
                $data[$key] = (self::COLUMN_ID == $key) ? (int)$value : $value;
            }
        }

        return $data;
    }

}