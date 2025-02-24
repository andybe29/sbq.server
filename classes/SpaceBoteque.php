<?php
/**
 * @author andy.bezbozhny <andy.bezbozhny@gmail.com>
 */
class SpaceBoteque
{
    /**
     * @var string $currentInstance значение текущего инстанса
     * @var string $instancePath путь к текущему инстансу
     * значения определяются в config.php
     */
    static $currentInstance;
    static $instancePath;

    /**
     * @const INSTANCE_DEV dev
     * @const INSTANCE_SBQ production
     */
    const INSTANCE_DEV = 'dev';
    const INSTANCE_SBQ = 'sbq';

    /**
     * @const LL_API_URL   API URL в зависимости от инстанса
     * @const LL_API_QUERY параметры запроса (http_build_query)
     */
    const LL_API_URL = [
        self::INSTANCE_DEV => 'https://lldev.thespacedevs.com/2.3.0',
        self::INSTANCE_SBQ => 'https://ll.thespacedevs.com/2.3.0'
    ];
    const LL_API_QUERY = ['limit' => 25, 'offset' => 0];

    /**
     * @var int $requestedURLs счётчик успешно выполненных запросов
     */
    static $requestedURLs = 0;

    static $error = null;

    private $id, $uuid;
    private $sql;

    protected function __construct($sql)
    {
        $this->sql = $sql;
    }

    public function __get($key)
    {
        return isset($this->$key) ? $this->$key : null;
    }

    public function __isset($key)
    {
        return isset($this->$key);
    }

    public function __set($key, $value)
    {
        switch ($key) {
            case SpaceBotequeDBase::COLUMN_ID: {
                $this->$key = (($value = (int)$value) > 0) ? $value : null;
                break;
            }

            case SpaceBotequeDBase::COLUMN_UUID: {
                $this->$key = $value;
            }

            default: {
                $this->$key = null;
                break;
            }
        }
    }

    /**
     * Список всех записей
     * @param string $table  наименование таблицы из SpaceBotequeDBase::TABLES
     * @param string $column наименование поля из SpaceBotequeDBase::COLUMNS, по которому следует произвести сортировку
     * @param string $dir    направление сортировки
     * @return mixed массив данных либо false в случае фейла
     */
    protected function _all(
        string $table = '',
        string $column = SpaceBotequeDBase::COLUMN_ID,
        string $dir = 'ASC'
    )
    {
        self::$error = null;

        if (SpaceBotequeDBase::tableColumning($table, $column)) {
            $dir = in_array($dir = strUtils::str2upper($dir), ['ASC', 'DESC']) ? $dir : 'ASC';
        } else {
            self::$error = new stdClass;
            self::$error->method  = __METHOD__;
            self::$error->message = 'Invalid Params';
            self::$error->values  = ['table' => $table, 'column' => $column];
        }
        if (self::$error) return false;

        $this->sql->str = 'SELECT * FROM ' . $table . ' ORDER BY ' . $column . ' ' . $dir;
        $this->sql->execute();

        $data = $this->sql->err ? false : $this->sql->all();
        $this->sql->free();

        return $data ? array_map(['SpaceBotequeDBase', 'typeCasting'], $data) : $data;
    }

    /**
     * Чтение отдельной записи
     * @param string $table   наименование таблицы из SpaceBotequeDBase::TABLES
     * @param string $column  наименование поля из набора [SpaceBotequeDBase::COLUMN_ID, SpaceBotequeDBase::COLUMN_UUID]
     * @param mixed  $idValue значение id/uuid записи
     * @return mixed запись либо false в случае фейла
     *
     */
    public function _read(
        string $table = '',
        string $column = SpaceBotequeDBase::COLUMN_ID,
        $idValue = 0
    )
    {
        self::$error = null;

        if (
            SpaceBotequeDBase::tableColumning($table, $column)
            and (
                (SpaceBotequeDBase::COLUMN_ID == $column and ($id = (int)$idValue) >= 0)
                or
                (SpaceBotequeDBase::COLUMN_UUID == $column and mb_strlen($id = (string)$idValue))
            )
        ) {
            # ok
            $id = (SpaceBotequeDBase::COLUMN_UUID == $column) ? $this->sql->varchar($id) : $id;
        } else {
            self::$error = new stdClass;
            self::$error->method  = __METHOD__;
            self::$error->message = 'Invalid Params';
            self::$error->values  = ['table' => $table, 'column' => $column, 'id' => $idValue];
        }
        if (self::$error) return false;

        $this->sql->str = 'SELECT * FROM ' . $table . ' WHERE ' . $column . ' = ' . $id;
        $this->sql->execute();

        if ($this->sql->err) {
            # ошибка MySQL
        } else if ($data = $this->sql->rows ? $this->sql->assoc() : []) {
            # ok
            $data = SpaceBotequeDBase::typeCasting($data);
        } else {
            self::$error = new stdClass;
            self::$error->method  = __METHOD__;
            self::$error->message = 'Record Not Found';
            self::$error->values  = ['table' => $table, 'column' => $column, 'id' => $idValue];
        }
        $this->sql->free();

        return ($this->sql->err or self::$error) ? false : $data;
    }

    /**
     * Создание / Обновление записи
     * @param string $incomeTable наименование таблицы из SpaceBotequeDBase::TABLES
     * @param array  $incomeData  массив значений
     * @return boolean результат выполнения операции
     */
    public function _replace(
        string $incomeTable = '',
        array  $incomeData = []
    )
    {
        SpaceBoteque::$error = null;

        $data = array_filter($incomeData, function($column) use ($incomeTable) {
            return SpaceBotequeDBase::tableColumning($incomeTable, $column);
        }, ARRAY_FILTER_USE_KEY);

        if (empty($data)) {
            SpaceBoteque::$error = new stdClass;
            SpaceBoteque::$error->method  = __METHOD__;
            SpaceBoteque::$error->message = 'array_filter';
            SpaceBoteque::$error->values  = $incomeData;
        }
        if (SpaceBoteque::$error) return false;

        $data = SpaceBotequeDBase::typeCasting($data);

        foreach ($data as $column => $value) {
            switch (SpaceBotequeDBase::COLUMN_TYPES[$column]) {
                case SpaceBotequeDBase::COLUMN_TYPE_DTIME:
                case SpaceBotequeDBase::COLUMN_TYPE_STRING: {
                    $data[$column] = $this->sql->varchar($value);
                    break;
                }

                case SpaceBotequeDBase::COLUMN_TYPE_FLOAT:
                case SpaceBotequeDBase::COLUMN_TYPE_INT:
                default: {
                    # ничего не делать
                    break;
                }
            }
        }

        return (false !== $this->sql->replace($incomeTable, $data));
    }

    /**
     * Актуальный URL LL API
     * @return string значение
     */
    public static function llAPI()
    {
        return self::LL_API_URL[self::$currentInstance];
    }

    /**
     * Логирование
     */
    public static function log2file($what = null)
    {
        $what = empty($what) ? self::$error : $what;

        if (empty($what)) return false;

        $flog = self::$instancePath . '/' . date('Y.m.d') . '.log';
        $what = is_scalar($what) ? $what : json_encode($what, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);

        return error_log(date('H:i:s') . ' : ' . $what . PHP_EOL, 3, $flog);
    }

    /**
     * Выполнение запроса
     * @param string  $url   URL
     * @param mixed   $proxy URL прокси-сервера
     * @return mixed содержимое URL либо false в случае фейла
     */
    public static function requestURL(string $url = '', $proxy = null)
    {
        if (empty($url)) return false;

        self::$error = null;

        if ($proxy) {
            $options = [
                'http' => [
                    'proxy'           => 'tcp://' . $proxy,
                    'request_fulluri' => true
                ]
            ];

            $context = stream_context_create($options);
        } else {
            $context = null;
        }

        if (false !== ($data = file_get_contents($url, false, $context))) {
            $data = empty($data) ? [] : json_decode($data, true);

            self::$requestedURLs ++;
        } else if ($proxy) {
            #
        } else {
            # ошибки логировать только в случае запроса НЕ через прокси
            self::$error = new stdClass;
            self::$error->method  = __METHOD__;
            self::$error->message = 'file_get_contents';
            self::$error->value   = $url;
        }

        return $data;
    }

}