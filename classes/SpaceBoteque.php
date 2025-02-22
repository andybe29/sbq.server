<?php
/**
 * @author andy.bezbozhny <andy.bezbozhny@gmail.com>
 */
class SpaceBoteque
{
    /**
     * @var string $currentInstance значение текущего инстанса
     */
    static $currentInstance;

    /**
     * @var string $instancePath путь к текущему инстансу
     */
    static $instancePath;

    /**
     * @const INSTANCE_DEV dev
     * @const INSTANCE_SBQ production
     */
    const INSTANCE_DEV  = 'dev';
    const INSTANCE_SBQ  = 'sbq';

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
     * @const URI для получения различных сущностей
     */
    const LAUNCH_STATUSES_LLAPI_URI = LaunchStatus::LLAPI_URI;
    const MISSION_TYPES_LLAPI_URI   = '/config/mission_types';

    /**
     * @const Типы миссий (все)
     */
    const MISSION_TYPE_EARTH_SCIENCE               =  1;
    const MISSION_TYPE_PLANETARY_SCIENCE           =  2;
    const MISSION_TYPE_ASTROPHYSICS                =  3;
    const MISSION_TYPE_HELIOPHYSICS                =  4;
    const MISSION_TYPE_HUMAN_EXPLORATION           =  5;
    const MISSION_TYPE_ROBOTIC_EXPLORATION         =  6;
    const MISSION_TYPE_GOVERNMENT                  =  7;
    const MISSION_TYPE_TOURISM                     =  8;
    const MISSION_TYPE_UNKNOWN                     =  9;
    const MISSION_TYPE_COMMUNICATIONS              = 10;
    const MISSION_TYPE_RESUPPLY                    = 11;
    const MISSION_TYPE_SUBORBITAL                  = 12;
    const MISSION_TYPE_TEST_FLIGHT                 = 13;
    const MISSION_TYPE_DEDICATED_RIDESHARE         = 14;
    const MISSION_TYPE_NAVIGATION                  = 15;
    const MISSION_TYPE_EMPTY                       = 16;
    const MISSION_TYPE_TEST_TARGET                 = 17;
    const MISSION_TYPE_LUNAR_EXPLORATION           = 18;
    const MISSION_TYPE_MATERIALS_SCIENCE           = 19;
    const MISSION_TYPE_BIOLOGY                     = 20;
    const MISSION_TYPE_TECHNOLOGY                  = 21;
    const MISSION_TYPE_MISSION_EXTENSION           = 22;
    const MISSION_TYPE_SPACE_SITUATIONAL_AWARENESS = 23;

    public static $allMissionTypes = [
        self::MISSION_TYPE_EARTH_SCIENCE               => 'Earth Science',
        self::MISSION_TYPE_PLANETARY_SCIENCE           => 'Planetary Science',
        self::MISSION_TYPE_ASTROPHYSICS                => 'Astrophysics',
        self::MISSION_TYPE_HELIOPHYSICS                => 'Heliophysics',
        self::MISSION_TYPE_HUMAN_EXPLORATION           => 'Human Exploration',
        self::MISSION_TYPE_ROBOTIC_EXPLORATION         => 'Robotic Exploration',
        self::MISSION_TYPE_GOVERNMENT                  => 'Government/Top Secret',
        self::MISSION_TYPE_TOURISM                     => 'Tourism',
        self::MISSION_TYPE_UNKNOWN                     => 'Unknown',
        self::MISSION_TYPE_COMMUNICATIONS              => 'Communications',
        self::MISSION_TYPE_RESUPPLY                    => 'Resupply',
        self::MISSION_TYPE_SUBORBITAL                  => 'Suborbital',
        self::MISSION_TYPE_TEST_FLIGHT                 => 'Test Flight',
        self::MISSION_TYPE_DEDICATED_RIDESHARE         => 'Dedicated Rideshare',
        self::MISSION_TYPE_NAVIGATION                  => 'Navigation',
        self::MISSION_TYPE_EMPTY                       => '',
        self::MISSION_TYPE_TEST_TARGET                 => 'Test Target',
        self::MISSION_TYPE_LUNAR_EXPLORATION           => 'Lunar Exploration',
        self::MISSION_TYPE_MATERIALS_SCIENCE           => 'Materials Science',
        self::MISSION_TYPE_BIOLOGY                     => 'Biology',
        self::MISSION_TYPE_TECHNOLOGY                  => 'Technology',
        self::MISSION_TYPE_MISSION_EXTENSION           => 'Mission Extension',
        self::MISSION_TYPE_SPACE_SITUATIONAL_AWARENESS => 'Space Situational Awareness'
    ];

    public static $sbqMissionTypes = [
        self::MISSION_TYPE_EARTH_SCIENCE               => 'Earth Science',
        self::MISSION_TYPE_PLANETARY_SCIENCE           => 'Planetary Science',
        self::MISSION_TYPE_ASTROPHYSICS                => 'Astrophysics',
        self::MISSION_TYPE_HELIOPHYSICS                => 'Heliophysics',
        self::MISSION_TYPE_ROBOTIC_EXPLORATION         => 'Robotic Exploration',
        self::MISSION_TYPE_LUNAR_EXPLORATION           => 'Lunar Exploration',
        self::MISSION_TYPE_MATERIALS_SCIENCE           => 'Materials Science',
        self::MISSION_TYPE_BIOLOGY                     => 'Biology',
    ];

    /**
     * @const PROXIES список URL со списками IP прокси
     **/
    const PROXIES = [
#        'http://api.foxtools.ru/v2/Proxy.txt',
        'https://raw.githubusercontent.com/TheSpeedX/SOCKS-List/master/http.txt'
    ];

    static $error = null;

    /**
     * @var array $proxies      список адресов прокси-серверов
     * @var int   $proxiesIndex указатель на текущий индекс в self::$proxies
     * @var int   $proxiesCount кол-во записей в self::$proxies
     * @var mixed $workingProxy рабочий прокси
     */
    static $proxies      = [];
    static $proxiesIndex = 0;
    static $proxiesCount = 0;
    static $workingProxy = null;

    /**
     * @var int $requestedURLs счётчик успешно выполненных запросов
     */
    static $requestedURLs = 0;

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
     * Загрузка в self::$proxies адресов прокси-серверов
     */
    public static function proxies()
    {
        self::$error = null;

        self::$proxies = [];

        foreach (self::PROXIES as $url) {
            if (false !== ($data = file($url, FILE_SKIP_EMPTY_LINES))) {
                self::$proxies = array_merge(self::$proxies, $data);
            } else {
                self::$error = new stdClass;
                self::$error->method  = __METHOD__;
                self::$error->message = 'Invalid Response';
                self::$error->value   = $url;
            }
        }

        self::$proxiesIndex = 0;
        self::$proxiesCount = count(self::$proxies);
        self::$workingProxy = null;

        return;
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

    /**
     * Выполнение запроса через перебор адресов прокси-серверов
     * @param string  $url   URL
     * @return mixed содержимое URL либо false в случае фейла
     */
    public static function requestURLviaProxies(string $url = '')
    {
        if (empty($url) or self::$proxiesIndex >= self::$proxiesCount) return false;

        # перебор проксей до рабочего

        do {
            $proxy = self::$proxies[self::$proxiesIndex];

            $data = self::requestURL($url, $proxy);

            self::$workingProxy = (false === $data) ? null : $proxy;

            self::$proxiesIndex ++;
        } while (self::$proxiesIndex < self::$proxiesCount and empty(self::$workingProxy));

        return $data;
    }

}