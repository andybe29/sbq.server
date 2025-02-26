<?php
    mb_internal_encoding('utf-8');
    date_default_timezone_set('UTC');

    spl_autoload_register(
        function ($classname) {
            if (preg_match('/\\\\/', $classname)) {
                $classname = str_replace('\\', DIRECTORY_SEPARATOR, $classname);
            }

            require_once realpath(__DIR__) . '/classes/' . $classname . '.php';
        }
    );

    # доступ к БД
    $dbc = parse_ini_file('.env');
    $sql = new simpleMySQLi($dbc, __DIR__);

    $sql->str = 'SET time_zone = ' . $sql->varchar('+00:00');
    $sql->execute();

    SpaceBoteque::$currentInstance = (false !== stripos(realpath(__DIR__), SpaceBoteque::INSTANCE_DEV)) ? SpaceBoteque::INSTANCE_DEV : SpaceBoteque::INSTANCE_SBQ;

    SpaceBoteque::$instancePath = __DIR__;
