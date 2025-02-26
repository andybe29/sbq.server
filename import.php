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

    $agency       = new Agency($sql);
    $launchStatus = new LaunchStatus($sql);
    $mission      = new Mission($sql);
    $orbit        = new Orbit($sql);

    do {
        $url = $requestURL . '?' . http_build_query($query);

        echo $url . PHP_EOL;

        $response = SpaceBoteque::requestURL($url);

        if (empty(SpaceBoteque::$error)) {
            foreach ($response['results'] as $currentLaunchNode) {
                $currentMissionNode = $currentLaunchNode['mission'];

                if (!in_array($currentMissionNode['type'], MissionType::MISSION_TYPES_ALL)) {
                    # unknown Mission Type
                    $unknownMissionTypes[] = $currentMissionNode['type'];
                }

                # если тип миссии не соответствует SpaceBoteque - пропуск хода
                if (!in_array($currentMissionNode['type'], MissionType::MISSION_TYPES)) continue;

                # to process
                $json = json_encode($currentLaunchNode, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
                $flog = SpaceBoteque::$instancePath . '/tmp/' . $currentLaunchNode['id'] . '.json';

                if (file_exists($flog)) {
                    unlink($flog);
                }

                error_log($json, 3, $flog);

                # данные для записи в SpaceBotequeDBase::TABLE_LAUNCHES
                $launchData = [];

                # данные для записи в SpaceBotequeDBase::TABLE_MISSIONS
                $missionData = [
                    'id'          => $currentMissionNode['id'],
                    'name'        => $currentMissionNode['name'],
                    'type'        => array_search($currentMissionNode['type'], MissionType::MISSION_TYPES, true),
                    'description' => $currentMissionNode['description'],
                    'launch'      => $currentLaunchNode['id'],
                    'orbit'       => $currentMissionNode['orbit']['id']
                ];

                # данные для записи в SpaceBotequeDBase::TABLE_MISSIONS2AGENCIES
                $missions2agencies = [];

                # agencies
                foreach ($currentMissionNode['agencies'] as $currentAgencyNode) {
                    $agency->replace(Agency::parse($currentAgencyNode));

                    $missions2agencies[] = $currentAgencyNode['id'];
                }

                # orbit
                $orbit->replace((array)$currentMissionNode['orbit']);

                # status
                $launchStatus->replace((array)$currentLaunchNode['status']);

                # mission
                $mission->replace($missionData);
                $mission->replaceAgencies($missionData['id'], $missions2agencies);
            }
        }

        if (SpaceBoteque::$error) {
            echo json_encode(SpaceBoteque::$error, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE) . PHP_EOL;
        }

        $query['offset'] += $query['limit'];

    } while (false and empty(SpaceBoteque::$error) and !empty($response['next']));

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
