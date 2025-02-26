<?php
/**
 * @author andy.bezbozhny <andy.bezbozhny@gmail.com>
 * константы и статические методы базы данных
 */
class SpaceBotequeDBase
{
    /**
     * наименования таблиц
     * @const TABLE_AGENCIES              агентства
     * @const TABLE_LAUNCH_STATUSES       статусы пусков
     * @const TABLE_LAUNCHES              пуски
     * @const TABLE_LOCATIONS             локации пусковых площадок
     * @const TABLE_MISSIONS              миссии
     * @const TABLE_ORBITS                целевые орбиты
     * @const TABLE_PADS                  пусковые площадки
     * @const TABLE_ROCKET_CONFIGURATIONS конфигурации ракет
     *
     * @const TABLE_MISSIONS2AGENCIES     миссии и агентства
     * @const TABLE_PADS2AGENCIES         пусковые площадки и агентства
     */
    const TABLE_AGENCIES              = 'agencies';
    const TABLE_LAUNCH_STATUSES       = 'launchStatuses';
    const TABLE_LAUNCHES              = 'launches';
    const TABLE_LOCATIONS             = 'locations';
    const TABLE_MISSIONS              = 'missions';
    const TABLE_ORBITS                = 'orbits';
    const TABLE_PADS                  = 'pads';
    const TABLE_ROCKET_CONFIGURATIONS = 'rocketConfigurations';

    const TABLE_MISSIONS2AGENCIES     = 'missions2agencies';
    const TABLE_PADS2AGENCIES         = 'pads2agencies';

    /**
     * Таблицы сущностей
     */
    const TABLES = [
        self::TABLE_AGENCIES,
        self::TABLE_LAUNCH_STATUSES,
        self::TABLE_LAUNCHES,
        self::TABLE_LOCATIONS,
        self::TABLE_MISSIONS,
        self::TABLE_ORBITS,
        self::TABLE_PADS,
        self::TABLE_ROCKET_CONFIGURATIONS
    ];

    /**
     * наименования полей
     */
    const COLUMN_ABBREV      = 'abbrev';
    const COLUMN_AGENCY      = 'agency';        # TABLE_AGENCIES.COLUMN_ID
    const COLUMN_COUNTRYCODE = 'countryCode';
    const COLUMN_DESCRIPTION = 'description';
    const COLUMN_ID          = 'id';
    const COLUMN_INFOURL     = 'infoURL';
    const COLUMN_LATITUDE    = 'latitude';
    const COLUMN_LAUNCH      = 'launch';        # TABLE_LAUNCHES.COLUMN_UUID
    const COLUMN_LONGITUDE   = 'longitude';
    const COLUMN_MISSION     = 'mission';       # TABLE_MISSIONS.COLUMN_ID
    const COLUMN_NAME        = 'name';
    const COLUMN_ORBIT       = 'orbit';         # TABLE_ORBITS.COLUMN_ID
    const COLUMN_PAD         = 'pad';           # TABLE_PADS.COLUMN_ID
    const COLUMN_TYPE        = 'type';          # MissionType::MISSION_TYPES
    const COLUMN_UUID        = 'uuid';
    const COLUMN_WIKIURL     = 'wikiURL';

    const COLUMNS = [
        self::COLUMN_ABBREV,
        self::COLUMN_AGENCY,
        self::COLUMN_COUNTRYCODE,
        self::COLUMN_DESCRIPTION,
        self::COLUMN_ID,
        self::COLUMN_INFOURL,
        self::COLUMN_LATITUDE,
        self::COLUMN_LAUNCH,
        self::COLUMN_LONGITUDE,
        self::COLUMN_MISSION,
        self::COLUMN_NAME,
        self::COLUMN_ORBIT,
        self::COLUMN_TYPE,
        self::COLUMN_UUID,
        self::COLUMN_WIKIURL
    ];

    /**
     * типы полей
     */
    const COLUMN_TYPE_DTIME  = 'datetime';
    const COLUMN_TYPE_FLOAT  = 'float';
    const COLUMN_TYPE_INT    = 'int';
    const COLUMN_TYPE_STRING = 'string';

    const COLUMN_TYPES = [
        self::COLUMN_ABBREV         => self::COLUMN_TYPE_STRING,
        self::COLUMN_AGENCY         => self::COLUMN_TYPE_INT,
        self::COLUMN_COUNTRYCODE    => self::COLUMN_TYPE_STRING,
        self::COLUMN_DESCRIPTION    => self::COLUMN_TYPE_STRING,
        self::COLUMN_ID             => self::COLUMN_TYPE_INT,
        self::COLUMN_INFOURL        => self::COLUMN_TYPE_STRING,
        self::COLUMN_LATITUDE       => self::COLUMN_TYPE_FLOAT,
        self::COLUMN_LAUNCH         => self::COLUMN_TYPE_STRING,
        self::COLUMN_LONGITUDE      => self::COLUMN_TYPE_FLOAT,
        self::COLUMN_MISSION        => self::COLUMN_TYPE_INT,
        self::COLUMN_NAME           => self::COLUMN_TYPE_STRING,
        self::COLUMN_ORBIT          => self::COLUMN_TYPE_INT,
        self::COLUMN_TYPE           => self::COLUMN_TYPE_INT,
        self::COLUMN_UUID           => self::COLUMN_TYPE_STRING,
        self::COLUMN_WIKIURL        => self::COLUMN_TYPE_STRING
    ];

    /**
     * таблицы и поля
     */
    const TABLES_COLUMNS = [
        self::TABLE_AGENCIES              => Agency::TABLE_COLUMNS,
#        self::TABLE_LAUNCHES              => Launch::TABLE_COLUMNS,
        self::TABLE_LAUNCH_STATUSES       => LaunchStatus::TABLE_COLUMNS,
        self::TABLE_LOCATIONS             => Location::TABLE_COLUMNS,
        self::TABLE_MISSIONS              => Mission::TABLE_COLUMNS,
        self::TABLE_ORBITS                => Orbit::TABLE_COLUMNS,
        self::TABLE_PADS                  => Pad::TABLE_COLUMNS,
#        self::TABLE_ROCKET_CONFIGURATIONS => RocketConfiguration::TABLE_COLUMNS,

        self::TABLE_MISSIONS2AGENCIES => [
            self::COLUMN_MISSION,
            self::COLUMN_AGENCY
        ],

        self::TABLE_PADS2AGENCIES => [
            self::COLUMN_PAD,
            self::COLUMN_AGENCY
        ]
    ];

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
            case self::COLUMN_ID: {
                $this->$key = (($value = (int)$value) > 0) ? $value : null;
                break;
            }

            case self::COLUMN_UUID: {
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
     * @param string $table  наименование таблицы из self::TABLES
     * @param string $column наименование поля из self::COLUMNS, по которому следует произвести сортировку
     * @param string $dir    направление сортировки
     * @return mixed массив данных либо false в случае фейла
     */
    protected function _all(
        string $table = '',
        string $column = self::COLUMN_ID,
        string $dir = 'ASC'
    )
    {
        SpaceBoteque::$error = null;

        if (self::tableColumning($table, $column)) {
            $dir = in_array($dir = strUtils::str2upper($dir), ['ASC', 'DESC']) ? $dir : 'ASC';
        } else {
            SpaceBoteque::$error = new stdClass;
            SpaceBoteque::$error->method  = __METHOD__;
            SpaceBoteque::$error->message = 'Invalid Params';
            SpaceBoteque::$error->values  = ['table' => $table, 'column' => $column];
        }
        if (SpaceBoteque::$error) return false;

        $this->sql->str = 'SELECT * FROM ' . $table . ' ORDER BY ' . $column . ' ' . $dir;
        $this->sql->execute();

        $data = $this->sql->err ? false : $this->sql->all();
        $this->sql->free();

        return $data ? array_map(['self', 'typeCasting'], $data) : $data;
    }

    /**
     * Чтение отдельной записи
     * @param string $table   наименование таблицы из self::TABLES
     * @param string $column  наименование поля из набора [self::COLUMN_ID, self::COLUMN_UUID]
     * @param mixed  $idValue значение id/uuid записи
     * @return mixed запись либо false в случае фейла
     *
     */
    protected function _read(
        string $table = '',
        string $column = self::COLUMN_ID,
        $idValue = 0
    )
    {
        SpaceBoteque::$error = null;

        if (
            self::tableColumning($table, $column)
            and (
                (self::COLUMN_ID == $column and ($id = (int)$idValue) >= 0)
                or
                (self::COLUMN_UUID == $column and mb_strlen($id = (string)$idValue))
            )
        ) {
            # ok
            $id = (self::COLUMN_UUID == $column) ? $this->sql->varchar($id) : $id;
        } else {
            SpaceBoteque::$error = new stdClass;
            SpaceBoteque::$error->method  = __METHOD__;
            SpaceBoteque::$error->message = 'Invalid Params';
            SpaceBoteque::$error->values  = ['table' => $table, 'column' => $column, 'id' => $idValue];
        }
        if (SpaceBoteque::$error) return false;

        $this->sql->str = 'SELECT * FROM ' . $table . ' WHERE ' . $column . ' = ' . $id;
        $this->sql->execute();

        if ($this->sql->err) {
            # ошибка MySQL
        } else if ($data = $this->sql->rows ? $this->sql->assoc() : []) {
            # ok
            $data = self::typeCasting($data);
        } else {
            SpaceBoteque::$error = new stdClass;
            SpaceBoteque::$error->method  = __METHOD__;
            SpaceBoteque::$error->message = 'Record Not Found';
            SpaceBoteque::$error->values  = ['table' => $table, 'column' => $column, 'id' => $idValue];
        }
        $this->sql->free();

        return ($this->sql->err or SpaceBoteque::$error) ? false : $data;
    }

    /**
     * Создание / Обновление записи
     * @param string $incomeTable наименование таблицы из self::TABLES
     * @param array  $incomeData  массив значений
     * @return boolean результат выполнения операции
     */
    protected function _replace(
        string $incomeTable = '',
        array  $incomeData = []
    )
    {
        SpaceBoteque::$error = null;

        # очистка входящего массива данных от лишних значений, не являющихся полями таблицы
        $data = array_filter($incomeData, function($column) use ($incomeTable) {
            return self::tableColumning($incomeTable, $column);
        }, ARRAY_FILTER_USE_KEY);

        if (empty($data)) {
            SpaceBoteque::$error = new stdClass;
            SpaceBoteque::$error->method  = __METHOD__;
            SpaceBoteque::$error->message = 'array_filter';
            SpaceBoteque::$error->values  = $incomeData;
        }
        if (SpaceBoteque::$error) return false;

        $data = self::typeCasting($data);

        foreach ($data as $column => $value) {
            switch (self::COLUMN_TYPES[$column]) {
                case self::COLUMN_TYPE_DTIME:
                case self::COLUMN_TYPE_STRING: {
                    $data[$column] = empty($value) ? 'NULL' : $this->sql->varchar($value);
                    break;
                }

                case self::COLUMN_TYPE_FLOAT:
                case self::COLUMN_TYPE_INT:
                default: {
                    # ничего не делать
                    break;
                }
            }
        }

        return (false !== $this->sql->replace($incomeTable, $data));
    }

    /**
     * Запись агентств в self::TABLE_MISSIONS2AGENCIES
     * Запись пусковых площадок в self::TABLE_PADS2AGENCIES
     * @param string $incomeTable наименование таблицы (из набора [self::TABLE_MISSIONS2AGENCIES, self::TABLE_PADS2AGENCIES])
     * @param array $incomeData массив id агентств
     * @return boolean результат выполнения операции
     */
    protected function _replaceAgencies(
        string $incomeTable = '',
        array $incomeData = []
    )
    {
        if (empty($this->id) or !in_array($incomeTable, [self::TABLE_MISSIONS2AGENCIES, self::TABLE_PADS2AGENCIES])) return false;

        $columnName = self::TABLES_COLUMNS[$incomeTable][0]; # self::COLUMN_MISSION || self::COLUMN_PAD

        $this->sql->str = 'DELETE FROM ' . $incomeTable . ' WHERE ' . $columnName . ' = ' . $this->id;
        $this->sql->execute();

        $incomeData = array_map('intval', $incomeData);
        $incomeData = array_filter($incomeData, function($agencyId) { return ($agencyId > 0); });

        if (empty($incomeData)) return true;

        $missionPadId = $this->id;
        $values = array_map(function($agencyId) use ($missionPadId) {
            return implode(', ', [$missionPadId, $agencyId]);
        }, $incomeData);

        $this->sql->str   = [];
        $this->sql->str[] = 'INSERT INTO ' . $incomeTable;
        $this->sql->str[] = '(' . $columnName . ', ' . self::COLUMN_AGENCY . ')';
        $this->sql->str[] = 'VALUES (' . implode('), (', $values) . ')';
        return (false !== $this->sql->execute());
    }

    /**
     * Проверка наличия и соттветствия таблиц и полей
     * @param string $table наименование таблицы
     * @param string $column наименование поля
     * @return результат проверки
     */
    public static function tableColumning(string $table, string $column)
    {
        return (
            in_array($table, self::TABLES)
            and
            in_array($column, self::COLUMNS)
            and
            array_key_exists($table, self::TABLES_COLUMNS)
            and
            in_array($column, self::TABLES_COLUMNS[$table])
        );
    }

    /**
     * Приведение типов
     * @param array $data ассоциативный массив данных «ключ => значение»
     * @return array обработанный массив
     */
    public static function typeCasting(array $data = [])
    {
        foreach ($data as $key => $value) {
            switch (self::COLUMN_TYPES[$key]) {
                case self::COLUMN_TYPE_DTIME: {
                    $data[$key] = is_int($value) ? date('Y-m-d H:i:s', $value) : strtotime($value);
                    break;
                }

                case self::COLUMN_TYPE_FLOAT: {
                    $data[$key] = (float)$value;
                    break;
                }

                case self::COLUMN_TYPE_INT: {
                    $data[$key] = (int)$value;
                    break;
                }

                case self::COLUMN_TYPE_STRING:
                default: {
                    $data[$key] = (string)$value;
                    break;
                }
            }
        }

        return $data;
    }
}