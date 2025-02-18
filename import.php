<?php
    error_reporting(E_ALL);

    $fname = basename(__FILE__);

    $lock = __DIR__ . '/import.lock';
    $fp = fopen($lock, 'w+');

    if (flock($fp, LOCK_EX | LOCK_NB)) {
        fwrite($fp, $fname);
        fflush($fp);
    } else {
        fclose($fp);
        die;
    }

    echo $fname . ' => has began' . PHP_EOL;

    $begin  = time();

    # импорт
    require 'config.php';

    # Загрузка в SpaceBoteque::$proxies адресов прокси-серверов
    if (SpaceBoteque::proxies()) {
        # ok
    } else {
        # запись в error.log
    }

    if (SpaceBoteque::$error) goto endOfScript;

    missionType:
    # импорт значений MISSION_TYPE

    $missionTypeURL = implode([
        SpaceBoteque::llAPI(),
        SpaceBoteque::LL_API_URI_MISSION_TYPE,
        '?limit=25'
    ]);

    $missionTypes = SpaceBoteque::requestURL($missionTypeURL);

    if (false === $missionTypes) {
        # запись в error.log
    }

    if (SpaceBoteque::$error) goto endOfScript;

    endOfScript:

    echo $fname . ' => completed for ' . gmdate('H:i:s', time() - $begin) . PHP_EOL;

    flock($fp, LOCK_UN);
    fclose($fp);
    if (file_exists($lock)) unlink($lock);
