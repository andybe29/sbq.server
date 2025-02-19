<?php
    error_reporting(E_ALL);

    $begin  = time();

    $fname = basename(__FILE__);

    $lock = __FILE__ . '.lock';
    $fp = fopen($lock, 'w+');

    if (flock($fp, LOCK_EX | LOCK_NB)) {
        fwrite($fp, $fname);
        fflush($fp);
    } else {
        fclose($fp);
        die;
    }

    # импорт
    require 'config.php';

    if (SpaceBoteque::INSTANCE_SBQ == SpaceBoteque::$currentInstance) {
        SpaceBoteque::log2file($fname . ' => has began');
    }

    goto missionType;

    # Загрузка в SpaceBoteque::$proxies адресов прокси-серверов
    if (SpaceBoteque::proxies()) {
        # ok
    } else if (SpaceBoteque::$error) {
        # запись в error.log
        SpaceBoteque::log2file(SpaceBoteque::$serror);
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
        SpaceBoteque::log2file(SpaceBoteque::$error);
    } else if (
        array_key_exists('results', $missionTypes)
        and
        is_array($missionTypes['results'])
    ) {
        $missionTypes = $missionTypes['results'];
        $missionTypes = array_combine(array_column($missionTypes, 'id'), array_column($missionTypes, 'name'));
        ksort($missionTypes);

        if ($diffMissionTypes = array_diff($missionTypes, SpaceBoteque::$allMissionTypes)) {
            # todo Если есть какие-либо различия
            # https://github.com/andybe29/sbq.server/issues/1
        }
    } else {
        SpaceBoteque::$error = new stdClass;
        SpaceBoteque::$error->message = 'Invalid value';
        SpaceBoteque::$error->result  = $missionTypes;
    }
    if (SpaceBoteque::$error) goto endOfScript;

    endOfScript:

    if (SpaceBoteque::INSTANCE_SBQ == SpaceBoteque::$currentInstance) {
        SpaceBoteque::log2file($fname . ' => completed for ' . gmdate('H:i:s', time() - $begin));
    }

    flock($fp, LOCK_UN);
    fclose($fp);
    if (file_exists($lock)) unlink($lock);
