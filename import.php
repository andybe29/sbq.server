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

    $requestURL = implode([SpaceBoteque::getCurrentAPIURL(), Launch::LLAPI_URI_UPCOMING]);

    $requestQuery = [
        'limit'    => 25,
        'mode'     => 'detailed',
        'offset'   => 0,
        'ordering' => 'net'
    ];

    $unknownMissionTypes = [];

    $agency              = new Agency($sql);
    $launch              = new Launch($sql);
    $launchStatus        = new LaunchStatus($sql);
    $location            = new Location($sql);
    $mission             = new Mission($sql);
    $orbit               = new Orbit($sql);
    $pad                 = new Pad($sql);
    $rocketConfiguration = new RocketConfiguration($sql);

    do {
        $url = $requestURL . '?' . http_build_query($requestQuery);

        if (SpaceBoteque::INSTANCE_DEV == SpaceBoteque::$currentInstance) {
            echo $url . PHP_EOL;
        }

        $response = SpaceBoteque::requestURL($url);

        if (empty(SpaceBoteque::$error)) {

            if (0 == SpaceBoteque::$requestedURLs) {
                SpaceBoteque::log2file($response['count'] . ' launches to process');
            }

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

                # orbit
                $orbit->replace($currentMissionNode['orbit']);

                # status
                $launchStatus->replace($currentLaunchNode['status']);

                # mission
                $missionData = Mission::parseNode($currentLaunchNode['id'], $currentMissionNode);
                $mission->replace($missionData);

                # данные для записи в SpaceBotequeDBase::TABLE_MISSIONS2AGENCIES
                $agencies = [];

                # agencies
                foreach ($currentMissionNode['agencies'] as $currentAgencyNode) {
                    $agencyData = Agency::parseNode($currentAgencyNode);
                    $agency->replace($agencyData);

                    $agencies[] = $currentAgencyNode['id'];
                }

                # missions2agencies
                $mission->id = $missionData['id'];
                $mission->replaceAgencies($agencies);

                # location
                $locationData = SpaceBotequeDBase::parseLocationPadNode($currentLaunchNode['pad']['location']);
                $location->replace($locationData);

                # pad
                $padData = SpaceBotequeDBase::parseLocationPadNode($currentLaunchNode['pad']);
                $pad->replace($padData);

                # данные для записи в SpaceBotequeDBase::TABLE_PADS2AGENCIES
                $agencies = [];

                # agencies
                foreach ($currentLaunchNode['pad']['agencies'] as $currentAgencyNode) {
                    $agencyData = Agency::parseNode($currentAgencyNode);
                    $agency->replace($agencyData);

                    $agencies[] = $currentAgencyNode['id'];
                }

                # pads2agencies
                $pad->id = $currentLaunchNode['pad']['id'];
                $pad->replaceAgencies($agencies);

                # rocketConfigurations
                $rcData = RocketConfiguration::parseNode($currentLaunchNode['rocket']['configuration']);
                $rocketConfiguration->replace($rcData);

                $launchData = Launch::parseNode($currentLaunchNode);
                $launch->replace($launchData);
            }
        }

        if (SpaceBoteque::$error) {
            echo json_encode(SpaceBoteque::$error, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE) . PHP_EOL;
        }

        $requestQuery['offset'] += $requestQuery['limit'];

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
