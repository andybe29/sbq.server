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

    /**
     * @var int $requestedURLs счётчик успешно выполненных запросов
     */
    static $requestedURLs = 0;

    static $error = null;

    /**
     * Актуальный URL LL API
     * @return string значение
     */
    public static function getCurrentAPIURL()
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

        $flog = self::$instancePath . '/tmp/' . date('Y.m.d') . '.log';
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