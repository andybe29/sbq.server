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
    const EARTH_SCIENCE               =  1;
    const PLANETARY_SCIENCE           =  2;
    const ASTROPHYSICS                =  3;
    const HELIOPHYSICS                =  4;
    const HUMAN_EXPLORATION           =  5;
    const ROBOTIC_EXPLORATION         =  6;
    const GOVERNMENT                  =  7;
    const TOURISM                     =  8;
    const UNKNOWN                     =  9;
    const COMMUNICATIONS              = 10;
    const RESUPPLY                    = 11;
    const SUBORBITAL                  = 12;
    const TEST_FLIGHT                 = 13;
    const DEDICATED_RIDESHARE         = 14;
    const NAVIGATION                  = 15;
    const EMPTY                       = 16;
    const TEST_TARGET                 = 17;
    const LUNAR_EXPLORATION           = 18;
    const MATERIALS_SCIENCE           = 19;
    const BIOLOGY                     = 20;
    const TECHNOLOGY                  = 21;
    const MISSION_EXTENSION           = 22;
    const SPACE_SITUATIONAL_AWARENESS = 23;

    /**
     * @const MISSION_TYPES_ALL все значения (используемые в LL API)
     **/
    const MISSION_TYPES_ALL = [
        self::EARTH_SCIENCE               => 'Earth Science',
        self::PLANETARY_SCIENCE           => 'Planetary Science',
        self::ASTROPHYSICS                => 'Astrophysics',
        self::HELIOPHYSICS                => 'Heliophysics',
        self::HUMAN_EXPLORATION           => 'Human Exploration',
        self::ROBOTIC_EXPLORATION         => 'Robotic Exploration',
        self::GOVERNMENT                  => 'Government/Top Secret',
        self::TOURISM                     => 'Tourism',
        self::UNKNOWN                     => 'Unknown',
        self::COMMUNICATIONS              => 'Communications',
        self::RESUPPLY                    => 'Resupply',
        self::SUBORBITAL                  => 'Suborbital',
        self::TEST_FLIGHT                 => 'Test Flight',
        self::DEDICATED_RIDESHARE         => 'Dedicated Rideshare',
        self::NAVIGATION                  => 'Navigation',
        self::EMPTY                       => '',
        self::TEST_TARGET                 => 'Test Target',
        self::LUNAR_EXPLORATION           => 'Lunar Exploration',
        self::MATERIALS_SCIENCE           => 'Materials Science',
        self::BIOLOGY                     => 'Biology',
        self::TECHNOLOGY                  => 'Technology',
        self::MISSION_EXTENSION           => 'Mission Extension',
        self::SPACE_SITUATIONAL_AWARENESS => 'Space Situational Awareness'
    ];

    /**
     * @const MISSION_TYPES значения, используемые в SpaceBoteque
     **/
    const MISSION_TYPES = [
        # self::EARTH_SCIENCE               => 'Earth Science',
        self::PLANETARY_SCIENCE           => 'Planetary Science',
        self::ASTROPHYSICS                => 'Astrophysics',
        self::HELIOPHYSICS                => 'Heliophysics',
        self::ROBOTIC_EXPLORATION         => 'Robotic Exploration',
        self::LUNAR_EXPLORATION           => 'Lunar Exploration',
        # self::BIOLOGY                     => 'Biology'
    ];

}