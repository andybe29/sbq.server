<?php
class API
{
    /**
     * @const JSON_RPC_VERSION версия JSON-RPC
     */
    const JSON_RPC_VERSION = '2.0';
    /**
     * коды ошибок
     */
    const ERROR_SERVER_ERROR     = -32000;
    const ERROR_DBASE_ERROR      = -32001;

    const ERROR_INVALID_REQUEST  = -32600;
    const ERROR_METHOD_NOT_FOUND = -32601;
    const ERROR_INVALID_PARAMS   = -32602;
    const ERROR_INTERNAL_ERROR   = -32603;
    const ERROR_PARSE_ERROR      = -32700;

    static $errors = [
        self::ERROR_SERVER_ERROR     => 'Server Error',
        self::ERROR_DBASE_ERROR      => 'Database Error',

        self::ERROR_INVALID_REQUEST  => 'Invalid Request',
        self::ERROR_METHOD_NOT_FOUND => 'Method Not Found',
        self::ERROR_INVALID_PARAMS   => 'Invalid Parameters',
        self::ERROR_INTERNAL_ERROR   => 'Internal Error',
        self::ERROR_PARSE_ERROR      => 'Parse Error',
    ];

    /**
     * @var array response шаблон ответа
     */
    static $response = [
        'jsonrpc' => self::JSON_RPC_VERSION,
        'id'      => null
    ];

    private $sql;

    public function __construct($sql)
    {
        $this->sql  = $sql;
    }

    /**
     * космическое агентство
     * @param  array $params массив аргументов:
     *          'id' => id агентства
     * @return array $response массив ответа на базе self::$response
     *      $response['result'] => [
     *          'name'        => наименование
     *          'abbrev'      => аббревиатура
     *          'description' => описание
     *      ]
     */
    public function agency(array $params = [])
    {
        $response = self::$response; # ['jsonrpc' => self::JSON_RPC_VERSION, 'id' => null]

        if (isset($params['id']) and 0 < ($params['id'] = (int)$params['id'])) {
            # OK
        } else {
            $response['error'] = self::ERROR_INVALID_PARAMS;
        }
        if (isset($response['error'])) return self::_checkResponse($response);

        $result = (new Agency($this->sql))->read($params['id']);

        if (false === $result) {
            $response['error'] = $this->sql->err ? self::ERROR_DBASE_ERROR : self::ERROR_SERVER_ERROR;
        }
        if (isset($response['error'])) return self::_checkResponse($response);

        $response['result'] = [
            'name'        => $result['name'],
            'abbrev'      => $result['abbrev'],
            'description' => $result['description']
        ];

        return self::_checkResponse($response);
    }

    /**
     * предстоящие пуски
     * @param  array $params массив аргументов:
     *          'offset' => индекс первой записи; необязательный аргумент, значение по умолчанию равно 0
     *          'limit'  => кол-во записей на запрос; необязательный аргумент, значение по умолчанию равно 10
     * @return array $response массив ответа на базе self::$response
     *      $response['result'] => [
     *          'count'    => общее количество имеющихся записей о предстоящих пусках
     *          'launches' => массив записей о предстоящих пусках, каждая запись соответствует одному пуску
     *          [
     *              'uuid'    => uuid пуска
     *              'name'    => наименование
     *              'status'  => статус пуска
     *              'location' => космодром
     *              [
     *                  'id'    => id космодрома
     *                  'name'  => наименование
     *              ]
     *              'pad'       => стартовая площадка
     *              [
     *                  'id'    => id стартовой площадки
     *                  'name'  => наименование
     *              ]
     *              'net'       => время пуска
     *              'window'    => пусковое окно
     *              [
     *                  'start' => открытие
     *                  'end'   => закрытие
     *              ]
     *              'updated'   => дата и время последнего обновления
     *          ]
     *      ]
     */
    public function launches(array $params = [])
    {
        $response = self::$response; # ['jsonrpc' => self::JSON_RPC_VERSION, 'id' => null]

        $params = array_map('intval', $params);

        # проверка параметров
        foreach (['offset', 'limit'] as $key) {
            if (isset($response['error'])) continue;

            if (isset($params[$key])) {
                if ($params[$key] >= 0) {
                    # OK
                } else {
                    $response['error'] = self::ERROR_INVALID_PARAMS;
                }
            } else {
                # значения по умолчанию
                $params[$key] = ('limit' == $key) ? SpaceBotequeDBase::LIMIT : 0;
            }
        }
        if (isset($response['error'])) return self::_checkResponse($response);

        $launch = new Launch($this->sql);

        # массив для $response['result']
        $result = [
            'count'    => 0,
            'launches' => []
        ];

        # кол-во предстоящих пусков всего
        $result['count'] = $launch->count();

        if (false === $result['count']) {
            $response['error'] = $this->sql->err ? self::ERROR_DBASE_ERROR : self::ERROR_SERVER_ERROR;
        }
        if (isset($response['error'])) return self::_checkResponse($response);

        if (0 == $result['count']) goto endOfLaunches;

        $launches = $launch->upcoming($params['offset'], $params['limit']);

        if (false === $launches) {
            $response['error'] = $this->sql->err ? self::ERROR_DBASE_ERROR : self::ERROR_SERVER_ERROR;
        }
        if (isset($response['error'])) return self::_checkResponse($response);

        # космодромы
        $locations = (new Location($this->sql))->all();

        if (false === $locations) {
            $response['error'] = $this->sql->err ? self::ERROR_DBASE_ERROR : self::ERROR_SERVER_ERROR;
        }
        if (isset($response['error'])) return self::_checkResponse($response);

        $locations = array_combine(array_column($locations, 'id'), array_column($locations, 'name'));

        # стартовые площадки
        $pads = (new Pad($this->sql))->all();

        if (false === $pads) {
            $response['error'] = $this->sql->err ? self::ERROR_DBASE_ERROR : self::ERROR_SERVER_ERROR;
        }
        if (isset($response['error'])) return self::_checkResponse($response);

        $pads = array_combine(array_column($pads, 'id'), array_column($pads, 'name'));

        # подготовка $result['launches']
        foreach ($launches as $value) {

            $result['launches'][] = [
                'uuid'      => $value['uuid'],
                'name'      => $value['name'],
                'status'    => $value['launchStatus'],
                'location'  => [
                    'id'   => $value['location'],
                    'name' => isset($locations[$value['location']]) ? $locations[$value['location']] : null
                ],
                'pad'       => [
                    'id'   => $value['pad'],
                    'name' => isset($pads[$value['pad']]) ? $pads[$value['pad']] : null
                ],
                'net'       => $value['net'],
                'window'    => [
                    'start' => $value['windowStart'],
                    'end'   => $value['windowEnd']
                ],
                'updated'   => $value['updated']
            ];

        }

        endOfLaunches:
        $response['result'] = $result;
        return self::_checkResponse($response);
    }

    /**
     * статусы пусков
     * @param  array $params массив аргументов: нет
     * @return array $response массив ответа на базе self::$response
     *      $response['result'] => [
     *          [
     *              'id'          => id статуса пуска
     *              'name'        => наименование
     *              'abbrev'      => сокращённое наименование
     *              'description' => описание
     *          ]
     *      ]
     */
    public function launchStatuses()
    {
        $response = self::$response; # ['jsonrpc' => self::JSON_RPC_VERSION, 'id' => null]

        $data = (new LaunchStatus($this->sql))->all();

        if (false === $data) {
            $response['error'] = $this->sql->err ? self::ERROR_DBASE_ERROR : self::ERROR_SERVER_ERROR;
        } else {
            $response['result'] = $data;
        }

        return self::_checkResponse($response);
    }

    /**
     * запускаемая миссия
     * @param  array $params массив аргументов:
     *          'uuid' => UUID пуска
     * @return array $response массив ответа на базе self::$response
     *      $response['result'] => [
     *          [
     *              'name' => наименование
     *              'type' => тип миссии
     *              [
     *                  'id'    => id типа миссии
     *                  'name'  => наименование
     *              ]
     *              'description' => описание
     *              'agencies'    => агентства, к которым относится миссия
     *              [
     *                  'id'          => id агентства
     *                  'name'        => наименование
     *                  'abbrev'      => аббревиатура
     *                  'description' => описание
     *              ]
     *          ]
     *      ]
     */
    public function mission(array $params = [])
    {
        $response = self::$response; # ['jsonrpc' => self::JSON_RPC_VERSION, 'id' => null]

        if (
            isset($params['uuid'])
            and
            preg_match(SpaceBotequeDBase::REGEXP_UUID, $params['uuid'])
        ) {
            # OK
        } else {
            $response['error'] = self::ERROR_INVALID_PARAMS;
        }
        if (isset($response['error'])) return self::_checkResponse($response);

        $mission = new Mission($this->sql);

        $result = $mission->launch($params['uuid']);

        if (false === $result) {
            $response['error'] = $this->sql->err ? self::ERROR_DBASE_ERROR : self::ERROR_SERVER_ERROR;
        }
        if (isset($response['error'])) return self::_checkResponse($response);

        if (empty($result)) goto endOfMission;

        $response['result'] = [
            'name'        => $result['name'],
            'type'        => [
                'id' => $result['type'],
                'name' => isset(MissionType::MISSION_TYPES[$result['type']]) ? MissionType::MISSION_TYPES[$result['type']] : null
            ],
            'description' => $result['description'],
            'agencies'    => []
        ];

        $agencies = $mission->agencies($result['id']);

        if (false === $agencies) {
            $response['error'] = $this->sql->err ? self::ERROR_DBASE_ERROR : self::ERROR_SERVER_ERROR;
        }
        if (isset($response['error'])) return self::_checkResponse($response);

        foreach ($agencies as $value) {
            $response['result']['agencies'][] = [
                'id'          => $value['id'],
                'name'        => $value['name'],
                'abbrev'      => $value['abbrev'],
                'description' => $value['description']
            ];
        }

        endOfMission:
        return self::_checkResponse($response);
    }

    /**
     * типы миссий
     * @param  array $params массив аргументов: нет
     * @return array $response массив ответа на базе self::$response
     *      $response['result'] => [
     *          [
     *              'id'   => id типа миссии
     *              'name' => наименование
     *          ]
     *      ]
     */
    public function missionTypes()
    {
        $response = self::$response; # ['jsonrpc' => self::JSON_RPC_VERSION, 'id' => null]

        $response['result'] = array_map(function($key, $value) {
                return ['id' => $key, 'name' => $value];
            }, array_keys(MissionType::MISSION_TYPES), array_values(MissionType::MISSION_TYPES)
        );

        return self::_checkResponse($response);
    }

    /**
     * проверка входящих данных
     * @param array распарсенный массив входных данных
     * @return boolean результат проверки (если false, то в self::$response готовый ответ с error)
     */
    public static function _checkIncomeData($data = [])
    {
        if (empty($data)) {
            # ошибка парсинга
            self::$response['error'] = self::ERROR_PARSE_ERROR;
        } else if (
            (isset($data['jsonrpc']) and self::JSON_RPC_VERSION == $data['jsonrpc'])
            and
            isset($data['method'])
            and
            (isset($data['id']) and $data['id'] !== null)
        ) {

            if (empty($data)) {
                # ошибка парсинга
                self::$response['error'] = self::ERROR_PARSE_ERROR;
            } else if (isset($data['method'])) {

                # проверка на наличие метода
                if (in_array($data['method'], get_class_methods('API'))) {
                    # OK
                } else {
                    self::$response['error'] = self::ERROR_METHOD_NOT_FOUND;
                }
            }

        } else {
            # неверный запрос
            self::$response['error'] = self::ERROR_INVALID_REQUEST;
        }

        return !isset(self::$response['error']);
    }

    public static function _checkResponse($response)
    {
        if (isset($response['error'])) {
            if (isset($response['result'])) unset($response['result']);

            if (is_numeric($response['error'])) {

                $code = isset(self::$errors[$response['error']]) ? $response['error'] : self::ERROR_SERVER_ERROR;

                $response['error'] = [
                    'code'    => $code,
                    'message' => self::$errors[$code]
                ];
            }
        }

        return $response;
    }

}
