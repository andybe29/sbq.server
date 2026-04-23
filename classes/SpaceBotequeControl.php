<?php
/**
 * @author andy.bezbozhny <andy.bezbozhny@gmail.com>
 * SpaceBoteque Ground Control
 */
class SpaceBotequeControl
{
    const COOKIE_SECURE = true;
    const TITLE         = 'SpaceBoteque Ground Control';

    /**
     * разделы Ground Control
     */
    const PAGE_AGENCIES = 'agencies';
    const PAGE_ENTITIES = 'entities';


    static $pages = [
        self::PAGE_AGENCIES => 'Agencies',
        self::PAGE_ENTITIES => 'Entities',
    ];
}