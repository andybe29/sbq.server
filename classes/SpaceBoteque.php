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
        SpaceBoteque::INSTANCE_DEV => 'https://lldev.thespacedevs.com/2.3.0',
        SpaceBoteque::INSTANCE_SBQ => 'https://ll.thespacedevs.com/2.3.0'
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
        SpaceBoteque::MISSION_TYPE_EARTH_SCIENCE               => 'Earth Science',
        SpaceBoteque::MISSION_TYPE_PLANETARY_SCIENCE           => 'Planetary Science',
        SpaceBoteque::MISSION_TYPE_ASTROPHYSICS                => 'Astrophysics',
        SpaceBoteque::MISSION_TYPE_HELIOPHYSICS                => 'Heliophysics',
        SpaceBoteque::MISSION_TYPE_HUMAN_EXPLORATION           => 'Human Exploration',
        SpaceBoteque::MISSION_TYPE_ROBOTIC_EXPLORATION         => 'Robotic Exploration',
        SpaceBoteque::MISSION_TYPE_GOVERNMENT                  => 'Government/Top Secret',
        SpaceBoteque::MISSION_TYPE_TOURISM                     => 'Tourism',
        SpaceBoteque::MISSION_TYPE_UNKNOWN                     => 'Unknown',
        SpaceBoteque::MISSION_TYPE_COMMUNICATIONS              => 'Communications',
        SpaceBoteque::MISSION_TYPE_RESUPPLY                    => 'Resupply',
        SpaceBoteque::MISSION_TYPE_SUBORBITAL                  => 'Suborbital',
        SpaceBoteque::MISSION_TYPE_TEST_FLIGHT                 => 'Test Flight',
        SpaceBoteque::MISSION_TYPE_DEDICATED_RIDESHARE         => 'Dedicated Rideshare',
        SpaceBoteque::MISSION_TYPE_NAVIGATION                  => 'Navigation',
        SpaceBoteque::MISSION_TYPE_EMPTY                       => '',
        SpaceBoteque::MISSION_TYPE_TEST_TARGET                 => 'Test Target',
        SpaceBoteque::MISSION_TYPE_LUNAR_EXPLORATION           => 'Lunar Exploration',
        SpaceBoteque::MISSION_TYPE_MATERIALS_SCIENCE           => 'Materials Science',
        SpaceBoteque::MISSION_TYPE_BIOLOGY                     => 'Biology',
        SpaceBoteque::MISSION_TYPE_TECHNOLOGY                  => 'Technology',
        SpaceBoteque::MISSION_TYPE_MISSION_EXTENSION           => 'Mission Extension',
        SpaceBoteque::MISSION_TYPE_SPACE_SITUATIONAL_AWARENESS => 'Space Situational Awareness'
    ];

    public static $sbqMissionTypes = [
        SpaceBoteque::MISSION_TYPE_EARTH_SCIENCE               => 'Earth Science',
        SpaceBoteque::MISSION_TYPE_PLANETARY_SCIENCE           => 'Planetary Science',
        SpaceBoteque::MISSION_TYPE_ASTROPHYSICS                => 'Astrophysics',
        SpaceBoteque::MISSION_TYPE_HELIOPHYSICS                => 'Heliophysics',
        SpaceBoteque::MISSION_TYPE_ROBOTIC_EXPLORATION         => 'Robotic Exploration',
        SpaceBoteque::MISSION_TYPE_LUNAR_EXPLORATION           => 'Lunar Exploration',
        SpaceBoteque::MISSION_TYPE_MATERIALS_SCIENCE           => 'Materials Science',
        SpaceBoteque::MISSION_TYPE_BIOLOGY                     => 'Biology',
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
     * @var int   $proxiesIndex указатель на текущий индекс в SpaceBoteque::$proxies
     * @var int   $proxiesCount кол-во записей в SpaceBoteque::$proxies
     * @var mixed $workingProxy рабочий прокси
     */
    static $proxies      = [];
    static $proxiesIndex = 0;
    static $proxiesCount = 0;
    static $workingProxy = null;

    /**
     * Актуальный URL LL API
     * @return string значение
     */
    public static function llAPI()
    {
        return SpaceBoteque::LL_API_URL[SpaceBoteque::$currentInstance];
    }

    /**
     * Логирование
     */
    public static function log2file($what = null)
    {
        $what = empty($what) ? SpaceBoteque::$error : $what;

        if (empty($what)) return false;

        $flog = SpaceBoteque::$instancePath . '/' . date('Y.m.d') . '.log';
        $what = is_scalar($what) ? $what : json_encode($what, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);

        return error_log(date('H:i:s') . ' : ' . $what . PHP_EOL, 3, $flog);
    }

    /**
     * Загрузка в SpaceBoteque::$proxies адресов прокси-серверов
     */
    public static function proxies()
    {
        SpaceBoteque::$error = null;

        SpaceBoteque::$proxies = [];

        foreach (SpaceBoteque::PROXIES as $url) {
            if (false !== ($data = file($url, FILE_SKIP_EMPTY_LINES))) {
                SpaceBoteque::$proxies = array_merge(SpaceBoteque::$proxies, $data);
            } else {
                SpaceBoteque::$error = new stdClass;
                SpaceBoteque::$error->method  = __METHOD__;
                SpaceBoteque::$error->message = 'Invalid Response';
                SpaceBoteque::$error->value   = $url;
            }
        }

        SpaceBoteque::$proxiesIndex = 0;
        SpaceBoteque::$proxiesCount = count(SpaceBoteque::$proxies);
        SpaceBoteque::$workingProxy = null;

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

        SpaceBoteque::$error = null;

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
        } else if ($proxy) {
            #
        } else {
            # ошибки логировать только в случае запроса НЕ через прокси
            SpaceBoteque::$error = new stdClass;
            SpaceBoteque::$error->method  = __METHOD__;
            SpaceBoteque::$error->message = 'file_get_contents';
            SpaceBoteque::$error->value   = $url;
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
        if (empty($url) or SpaceBoteque::$proxiesIndex >= SpaceBoteque::$proxiesCount) return false;

        # перебор проксей до рабочего

        do {
            $proxy = SpaceBoteque::$proxies[SpaceBoteque::$proxiesIndex];

            $data = SpaceBoteque::requestURL($url, $proxy);

            SpaceBoteque::$workingProxy = (false === $data) ? null : $proxy;

            SpaceBoteque::$proxiesIndex ++;
        } while (SpaceBoteque::$proxiesIndex < SpaceBoteque::$proxiesCount and empty(SpaceBoteque::$workingProxy));

        return $data;
    }

}