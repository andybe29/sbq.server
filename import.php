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
        'format'            => 'json',
        'last_updated__gte' => date('Y-m-d\TH:i:s\Z', time() - 24 * 3600),
        'limit'             => 25,
        'mode'              => 'normal',
        'offset'            => 0,
        'ordering'          => '-last_updated',
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
        $response = SpaceBoteque::requestURL($url = $requestURL . '?' . http_build_query($requestQuery));

        if (SpaceBoteque::$error) {
            echo json_encode(SpaceBoteque::$error, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE) . PHP_EOL;
        } else if (isset($response['results'])) {

            if (1 == SpaceBoteque::$requestedURLs) {
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

        } else if (!isset($response['next'])) {
            $response['next'] = null;
        }

        $requestQuery['offset'] += $requestQuery['limit'];
    } while (empty(SpaceBoteque::$error) and $response['next']);

    if ($unknownMissionTypes) {
        $unknownMissionTypes = array_unique($unknownMissionTypes);
        echo 'Unknown Mission Types: ' . json_encode($unknownMissionTypes, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    }

    endOfScript:

    if (SpaceBoteque::INSTANCE_SBQ == SpaceBoteque::$currentInstance) {
        SpaceBoteque::log2file($fname . ' => completed for ' . gmdate('H:i:s', time() - $begin));
        SpaceBoteque::log2file(SpaceBoteque::$requestedURLs . ' requests completed');
    }

    flock($fp, LOCK_UN);
    fclose($fp);
    if (file_exists($lock)) unlink($lock);
