<?php
/**
 * @author andy.bezbozhny <andy.bezbozhny@gmail.com>
 * константы и статические методы для выполнения запросов через прокси-серверы
 */
class SpaceBotequeProxy
{
    /**
     * @const PROXIES список URL со списками IP прокси
     **/
    const PROXIES = [
        'https://raw.githubusercontent.com/TheSpeedX/SOCKS-List/master/http.txt'
    ];

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
     * Загрузка в self::$proxies адресов прокси-серверов
     */
    public static function proxies()
    {
        SpaceBoteque::$error = null;

        self::$proxies = [];

        foreach (self::PROXIES as $url) {
            if (false !== ($data = file($url, FILE_SKIP_EMPTY_LINES))) {
                self::$proxies = array_merge(self::$proxies, $data);
            } else {
                SpaceBoteque::$error = new stdClass;
                SpaceBoteque::$error->method  = __METHOD__;
                SpaceBoteque::$error->message = 'Invalid Response';
                SpaceBoteque::$error->value   = $url;
            }
        }

        self::$proxiesIndex = 0;
        self::$proxiesCount = count(self::$proxies);
        self::$workingProxy = null;

        return;
    }

    /**
     * Выполнение запроса через перебор адресов прокси-серверов
     * @param string  $url URL
     * @return mixed содержимое URL либо false в случае фейла
     */
    public static function requestURLviaProxies(string $url = '')
    {
        if (empty($url) or self::$proxiesIndex >= self::$proxiesCount) return false;

        # перебор проксей до рабочего

        do {
            $proxy = self::$proxies[self::$proxiesIndex];

            $data = SpaceBoteque::requestURL($url, $proxy);

            self::$workingProxy = (false === $data) ? null : $proxy;

            self::$proxiesIndex ++;
        } while (self::$proxiesIndex < self::$proxiesCount and empty(self::$workingProxy));

        return $data;
    }

}