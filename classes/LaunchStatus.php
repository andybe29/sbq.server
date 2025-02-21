<?php
/**
 * @author andy.bezbozhny <andy.bezbozhny@gmail.com>
 */
class LaunchStatus
{
    /**
     * @const LLAPI_URI URI для получения списка значений статусов пусков
     */
    const LLAPI_URI = '/config/launch_statuses';

    /**
     * @const TABLE название таблицы
     * @const COLUMN_ID          поле для id
     * @const COLUMN_NAME        поле для name
     * @const COLUMN_ABBREV      поле для abbrev
     * @const COLUMN_DESCRIPTION поле для description
     */
    const TABLE              = 'launchStatuses';
    const COLUMN_ID          = 'id';
    const COLUMN_NAME        = 'name';
    const COLUMN_ABBREV      = 'abbrev';
    const COLUMN_DESCRIPTION = 'description';

    static $columns = [
        LaunchStatus::COLUMN_ID,
        LaunchStatus::COLUMN_NAME,
        LaunchStatus::COLUMN_ABBREV,
        LaunchStatus::COLUMN_DESCRIPTION
    ];

    private $sql;

    /**
     * @var int    $id          id статуса
     * @var string $name        наименование
     * @var string $abbrev      краткое наименование
     * @var string $description описание
     */
    private $id;

    public function __construct($sql)
    {
        $this->sql = $sql;
    }

    public function __get($key)
    {
        return (isset($this->$key) and in_array($key, LaunchStatus::$columns)) ? $this->$key : null;
    }

    public function __isset($key)
    {
        return isset($this->$key);
    }

    public function __set($key, $value)
    {
        $this->$key = (LaunchStatus::COLUMN_ID == $key and ($value = (int)$value) > 0) ? $value : null;
    }

    /**
     * Импорт данных
     * @return mixed массив данных либо false в случае фейла
     */
    public function import()
    {
        $URL  = implode([SpaceBoteque::llAPI(), LaunchStatus::LLAPI_URI]);
        $URL .= '?' . http_build_query(SpaceBoteque::LL_API_QUERY);

        $launchStatuses = [];

        do {
            $response = SpaceBoteque::requestURL($URL);

            if (false === $response) {
            } else if (array_key_exists('results', $response) and is_array($response['results'])) {

                $launchStatuses = array_merge($launchStatuses, $response['results']);
                $URL            = array_key_exists('next', $response) ? $response['next'] : null;

            } else {
                SpaceBoteque::$error = new stdClass;
                SpaceBoteque::$error->method  = __METHOD__;
                SpaceBoteque::$error->message = 'Invalid Response for SpaceBoteque::requestURL';
                SpaceBoteque::$error->value   = $response;
            }

            $URL = empty(SpaceBoteque::$error) ? $URL : null;

        } while (!empty($URL));

        return empty(SpaceBoteque::$error) ? $launchStatuses : false;
    }

    /**
     * Создание записи
     * @param array массив значений
     * @return boolean результат выполнения операции
     */
    public function replace(array $incomeData = [])
    {
        SpaceBoteque::$error = null;

        $data = array_filter($incomeData, function($key) { return in_array($key, LaunchStatus::$columns); }, ARRAY_FILTER_USE_KEY);

        if (empty($data)) {
            SpaceBoteque::$error = new stdClass;
            SpaceBoteque::$error->method  = __METHOD__;
            SpaceBoteque::$error->message = 'array_filter';
            SpaceBoteque::$error->value   = $incomeData;
        }
        if (SpaceBoteque::$error) return false;

        foreach ($data as $key => $value) {
            $data[$key] = (LaunchStatus::COLUMN_ID == $key) ? (int)$value : (mb_strlen($value) ? $this->sql->varchar($value) : 'null');
        }

        return (false !== $this->sql->replace(LaunchStatus::TABLE, $data));
    }

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

        $this->sql->str = 'SELECT * FROM ' . LaunchStatus::TABLE . ' WHERE ' . LaunchStatus::COLUMN_ID . ' = ' . $id;
        $this->sql->execute();

        if ($this->sql->err) {
            # ошибка MySQL
        } else if ($data = $this->sql->rows ? $this->sql->assoc() : []) {

            $data = LaunchStatus::_prepare($data);
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

    public function data()
    {
        SpaceBoteque::$error = null;

        $this->sql->str = 'SELECT * FROM ' . LaunchStatus::TABLE . ' ORDER BY ' . LaunchStatus::COLUMN_ID;
        $this->sql->execute();

        $data = $this->sql->err ? false : $this->sql->all();
        $this->sql->free();

        return $data ? array_map(['LaunchStatus', '_prepare'], $data) : $data;
    }

    public static function _prepare($data)
    {
        foreach ($data as $key => $value) {
            $data[$key] = (LaunchStatus::COLUMN_ID == $key) ? (int)$value : $value;
        }

        return $data;
    }

}