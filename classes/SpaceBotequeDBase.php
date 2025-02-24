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
     * @const TABLE_MISSIONS2AGENCIES     миссии и агентства
     * @const TABLE_ORBITS                целевые орбиты
     * @const TABLE_PADS                  пусковые площадки
     * @const TABLE_PADS2AGENCIES         пусковые площадки и агентства
     * @const TABLE_ROCKET_CONFIGURATIONS конфигурации ракет
     */
    const TABLE_AGENCIES              = 'agencies';
    const TABLE_LAUNCH_STATUSES       = 'launchStatuses';
    const TABLE_LAUNCHES              = 'launches';
    const TABLE_LOCATIONS             = 'locations';
    const TABLE_MISSIONS              = 'missions';
    const TABLE_MISSIONS2AGENCIES     = 'missions2agencies';
    const TABLE_ORBITS                = 'orbits';
    const TABLE_PADS                  = 'pads';
    const TABLE_PADS2AGENCIES         = 'pads2agencies';
    const TABLE_ROCKET_CONFIGURATIONS = 'rocketConfigurations';

    const TABLES = [
        self::TABLE_AGENCIES,
        self::TABLE_LAUNCH_STATUSES,
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
    const COLUMN_DESCRIPTION = 'description';
    const COLUMN_ID          = 'id';
    const COLUMN_NAME        = 'name';
    const COLUMN_UUID        = 'uuid';

    const COLUMNS = [
        self::COLUMN_ABBREV,
        self::COLUMN_DESCRIPTION,
        self::COLUMN_ID,
        self::COLUMN_NAME,
        self::COLUMN_UUID,
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
        self::COLUMN_DESCRIPTION    => self::COLUMN_TYPE_STRING,
        self::COLUMN_ID             => self::COLUMN_TYPE_INT,
        self::COLUMN_NAME           => self::COLUMN_TYPE_STRING,
        self::COLUMN_UUID           => self::COLUMN_TYPE_STRING,
    ];

    /**
     * таблицы и поля
     */
    const TABLES_COLUMNS = [
        self::TABLE_AGENCIES => [
        ],

        self::TABLE_LAUNCHES => [
        ],

        self::TABLE_LAUNCH_STATUSES => [
            self::COLUMN_ID,
            self::COLUMN_NAME,
            self::COLUMN_ABBREV,
            self::COLUMN_DESCRIPTION,
        ],

        self::TABLE_LOCATIONS => [
        ],

        self::TABLE_MISSIONS => [
        ],

        self::TABLE_MISSIONS2AGENCIES => [
        ],

        self::TABLE_ORBITS => [
        ],

        self::TABLE_PADS => [
        ],

        self::TABLE_PADS2AGENCIES => [
        ],

        self::TABLE_ROCKET_CONFIGURATIONS => [
        ]
    ];

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