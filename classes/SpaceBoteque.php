<?php
/**
 * @author andy.bezbozhny <andy.bezbozhny@gmail.com>
 */
class SpaceBoteque
{
    static $currentInstance;

    const INSTANCE_DEV  = 'dev';
    const INSTANCE_SBQ  = 'sbq';

    const LL_API_URL = [
        self::INSTANCE_DEV => 'https://lldev.thespacedevs.com/2.3.0',
        self::INSTANCE_SBQ => 'https://ll.thespacedevs.com/2.3.0'
    ];

    /**
     * URI для получения различных сущностей
     */
    const LL_API_URI_MISSION_TYPE = '/config/mission_types';

    /**
     * Типы миссий (все)
     */
    const MISSION_TYPE_EARTH_SCIENCE               =  1;
    const MISSION_TYPE_PLANETARY_SCIENCE           =  2;
    const MISSION_TYPE_ASTROPHYSICS                =  3;
    const MISSION_TYPE_HELIOPHYSICS                =  4;
    const MISSION_TYPE_HUMAN_EXPLORATION           =  5;
    const MISSION_TYPE_ROBOTIC_EXPLORATION         =  6;
    const MISSION_TYPE_GOVERNMENT                  =  7;
    const MISSION_TYPE_TOURISM                     =  8;
    const MISSION_TYPE_UNKNOWN                     =  9;
    const MISSION_TYPE_COMMUNICATIONS              = 10;
    const MISSION_TYPE_RESUPPLY                    = 11;
    const MISSION_TYPE_SUBORBITAL                  = 12;
    const MISSION_TYPE_TEST_FLIGHT                 = 13;
    const MISSION_TYPE_DEDICATED_RIDESHARE         = 14;
    const MISSION_TYPE_NAVIGATION                  = 15;
    const MISSION_TYPE_EMPTY                       = 16;
    const MISSION_TYPE_TEST_TARGET                 = 17;
    const MISSION_TYPE_LUNAR_EXPLORATION           = 18;
    const MISSION_TYPE_MATERIALS_SCIENCE           = 19;
    const MISSION_TYPE_BIOLOGY                     = 20;
    const MISSION_TYPE_TECHNOLOGY                  = 21;
    const MISSION_TYPE_MISSION_EXTENSION           = 22;
    const MISSION_TYPE_SPACE_SITUATIONAL_AWARENESS = 23;

    public static $allMissionTypes = [
        self::MISSION_TYPE_EARTH_SCIENCE               => 'Earth Science',
        self::MISSION_TYPE_PLANETARY_SCIENCE           => 'Planetary Science',
        self::MISSION_TYPE_ASTROPHYSICS                => 'Astrophysics',
        self::MISSION_TYPE_HELIOPHYSICS                => 'Heliophysics',
        self::MISSION_TYPE_HUMAN_EXPLORATION           => 'Human Exploration',
        self::MISSION_TYPE_ROBOTIC_EXPLORATION         => 'Robotic Exploration',
        self::MISSION_TYPE_GOVERNMENT                  => 'Government / Top Secret',
        self::MISSION_TYPE_TOURISM                     => 'Tourism',
        self::MISSION_TYPE_UNKNOWN                     => 'Unknown',
        self::MISSION_TYPE_COMMUNICATIONS              => 'Communications',
        self::MISSION_TYPE_RESUPPLY                    => 'Resupply',
        self::MISSION_TYPE_SUBORBITAL                  => 'Suborbital',
        self::MISSION_TYPE_TEST_FLIGHT                 => 'Test Flight',
        self::MISSION_TYPE_DEDICATED_RIDESHARE         => 'Dedicated Rideshare',
        self::MISSION_TYPE_NAVIGATION                  => 'Navigation',
        self::MISSION_TYPE_EMPTY                       => '',
        self::MISSION_TYPE_TEST_TARGET                 => 'Test Target',
        self::MISSION_TYPE_LUNAR_EXPLORATION           => 'Lunar Exploration',
        self::MISSION_TYPE_MATERIALS_SCIENCE           => 'Materials Science',
        self::MISSION_TYPE_BIOLOGY                     => 'Biology',
        self::MISSION_TYPE_TECHNOLOGY                  => 'Technology',
        self::MISSION_TYPE_MISSION_EXTENSION           => 'Mission Extension',
        self::MISSION_TYPE_SPACE_SITUATIONAL_AWARENESS => 'Space Situational Awareness'
    ];

    public static $sbqMissionTypes = [
        self::MISSION_TYPE_EARTH_SCIENCE,
        self::MISSION_TYPE_PLANETARY_SCIENCE,
        self::MISSION_TYPE_ASTROPHYSICS,
        self::MISSION_TYPE_HELIOPHYSICS,
        self::MISSION_TYPE_ROBOTIC_EXPLORATION,
        self::MISSION_TYPE_LUNAR_EXPLORATION,
        self::MISSION_TYPE_MATERIALS_SCIENCE,
        self::MISSION_TYPE_BIOLOGY
    ];

    /**
     * Актуальный URL LL API
     * @return string значение
     */
    public static function _llAPI()
    {
        return self::LL_API_URL[self::$currentInstance];
    }

}