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
        self::ERROR_SERVER_ERROR     => 'Unknown Server Error',
        self::ERROR_DBASE_ERROR      => 'Unknown Database Error',

        self::ERROR_INVALID_REQUEST  => 'Invalid Request',
        self::ERROR_METHOD_NOT_FOUND => 'Method Not Found',
        self::ERROR_INVALID_PARAMS   => 'Invalid Parameters',
        self::ERROR_INTERNAL_ERROR   => 'Internal Error',
        self::ERROR_PARSE_ERROR      => 'Parsing Error',
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
     * Список типов миссий
     * @param  array $params массив аргументов:
     * @return array $response массив ответа на базе self::$response
     *      $response['result'] => [
     *          [
     *              'id'   => id типа
     *              'type' => значение
     *          ]
     *      ]
     */
    public function missionTypes(array $params = [])
    {
        $response = self::$response; # ['jsonrpc' => self::JSON_RPC_VERSION, 'id' => null]

        $response['result'] = array_map(function($key, $value) {
                return ['id' => $key, 'type' => $value];
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
