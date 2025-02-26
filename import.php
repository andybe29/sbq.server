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

    $agency              = new Agency($sql);
    $launchStatus        = new LaunchStatus($sql);
    $location            = new Location($sql);
    $mission             = new Mission($sql);
    $orbit               = new Orbit($sql);
    $pad                 = new Pad($sql);
    $rocketConfiguration = new RocketConfiguration($sql);

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

                # данные для записи в SpaceBotequeDBase::TABLE_MISSIONS
                $missionData = [
                    SpaceBotequeDBase::COLUMN_ID          => $currentMissionNode['id'],
                    SpaceBotequeDBase::COLUMN_NAME        => $currentMissionNode['name'],
                    SpaceBotequeDBase::COLUMN_TYPE        => array_search($currentMissionNode['type'], MissionType::MISSION_TYPES, true),
                    SpaceBotequeDBase::COLUMN_DESCRIPTION => $currentMissionNode['description'],
                    SpaceBotequeDBase::COLUMN_LAUNCH      => $currentLaunchNode['id'],
                    SpaceBotequeDBase::COLUMN_ORBIT       => $currentMissionNode['orbit']['id']
                ];

                # orbit
                $orbit->replace($currentMissionNode['orbit']);

                # status
                $launchStatus->replace($currentLaunchNode['status']);

                # mission
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

                # данные для записи в SpaceBotequeDBase::TABLE_LAUNCHES
                $launchData = [
                    SpaceBotequeDBase::COLUMN_UUID                => $currentLaunchNode['id'],
                    SpaceBotequeDBase::COLUMN_NAME                => $currentLaunchNode['name'],
                    SpaceBotequeDBase::COLUMN_LAUNCHSTATUS        => $currentLaunchNode['status']['id'],
                    SpaceBotequeDBase::COLUMN_ROCKET              => $currentLaunchNode['rocket']['id'],
                    SpaceBotequeDBase::COLUMN_ROCKETCONFIGURATION => $currentLaunchNode['rocket']['configuration']['id'],
                    SpaceBotequeDBase::COLUMN_PAD                 => $currentLaunchNode['pad']['id'],
                    SpaceBotequeDBase::COLUMN_LOCATION            => $currentLaunchNode['pad']['location']['id'],
                    SpaceBotequeDBase::COLUMN_NET                 => $currentLaunchNode['net'],
                    SpaceBotequeDBase::COLUMN_WINDOWSTART         => $currentLaunchNode['window_start'],
                    SpaceBotequeDBase::COLUMN_WINDOWEND           => $currentLaunchNode['window_end'],
                    SpaceBotequeDBase::COLUMN_UPDATED             => $currentLaunchNode['last_updated']
                ];

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
