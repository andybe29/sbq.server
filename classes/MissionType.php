<?php
/**
 * Типы миссий
 * @author andy.bezbozhny <andy.bezbozhny@gmail.com>
 */
class MissionType
{
    /**
     * @const LLAPI_URI URI для получения списка значений
     */
    const LLAPI_URI = '/config/mission_types';

    /**
     * @const Типы миссий (все)
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

    /**
     * @const MISSION_TYPES_ALL все значения (используемые в LL API)
     **/
    const MISSION_TYPES_ALL = [
        self::MISSION_TYPE_EARTH_SCIENCE               => 'Earth Science',
        self::MISSION_TYPE_PLANETARY_SCIENCE           => 'Planetary Science',
        self::MISSION_TYPE_ASTROPHYSICS                => 'Astrophysics',
        self::MISSION_TYPE_HELIOPHYSICS                => 'Heliophysics',
        self::MISSION_TYPE_HUMAN_EXPLORATION           => 'Human Exploration',
        self::MISSION_TYPE_ROBOTIC_EXPLORATION         => 'Robotic Exploration',
        self::MISSION_TYPE_GOVERNMENT                  => 'Government/Top Secret',
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

    /**
     * @const MISSION_TYPES значения, используемые в SpaceBoteque
     **/
    const MISSION_TYPES = [
        # self::MISSION_TYPE_EARTH_SCIENCE               => 'Earth Science',
        self::MISSION_TYPE_PLANETARY_SCIENCE           => 'Planetary Science',
        self::MISSION_TYPE_ASTROPHYSICS                => 'Astrophysics',
        self::MISSION_TYPE_HELIOPHYSICS                => 'Heliophysics',
        self::MISSION_TYPE_ROBOTIC_EXPLORATION         => 'Robotic Exploration',
        self::MISSION_TYPE_LUNAR_EXPLORATION           => 'Lunar Exploration',
        # self::MISSION_TYPE_BIOLOGY                     => 'Biology'
    ];

}