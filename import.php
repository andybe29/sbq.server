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

    $requestURL = implode([SpaceBoteque::getCurrentAPIURL(), '/launches/upcoming/']);

    $query = [
        'limit'    => 25,
        'mode'     => 'detailed',
        'offset'   => 0,
        'ordering' => 'net'
    ];

    $unknownMissionTypes = [];

    $agency         = new Agency($sql);

    $launchStatus   = new LaunchStatus($sql);
    $launchStatuses = ($launchStatuses = $launchStatus->all()) ? array_column($launchStatuses, 'id') : [];

    do {
        $url = $requestURL . '?' . http_build_query($query);

        echo $url . PHP_EOL;

        $response = SpaceBoteque::requestURL($url);

        if (empty(SpaceBoteque::$error)) {
            foreach ($response['results'] as $currentLaunchNode) {
                $currentMissionNode = $currentLaunchNode['mission'];

                if (in_array($currentMissionNode['type'], MissionType::MISSION_TYPES)) {
                    # to process
                    $json = json_encode($currentLaunchNode, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
                    $flog = SpaceBoteque::$instancePath . '/tmp/' . $currentLaunchNode['id'] . '.json';
                    error_log($json, 0, $flog);


                } else if (!in_array($currentMissionNode['type'], MissionType::MISSION_TYPES_ALL)) {
                    # unknown Mission Type
                    $unknownMissionTypes[] = $currentMissionNode['type'];
                }
            }
        }

        if (SpaceBoteque::$error) {
            echo json_encode(SpaceBoteque::$error, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE) . PHP_EOL;
        }

        $query['offset'] += $query['limit'];

    } while (empty(SpaceBoteque::$error) and !empty($response['next']));

    if ($unknownMissionTypes) {
        $unknownMissionTypes = array_unique($unknownMissionTypes);
        echo 'Unknown Mission Types: ' . json_encode($unknownMissionTypes, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    }

    endOfScript:

    if (SpaceBoteque::INSTANCE_SBQ == SpaceBoteque::$currentInstance) {
        SpaceBoteque::log2file($fname . ' => completed for ' . gmdate('H:i:s', time() - $begin));
        SpaceBoteque::log2file(SpaceBoteque::$requestedURLs . ' completed');
    }

    flock($fp, LOCK_UN);
    fclose($fp);
    if (file_exists($lock)) unlink($lock);
