<?php

class Riot101Admin extends ModelAdmin {
    static $managed_models = array('FAQ');
    static $url_segment = 'riot-101';
    static $menu_title = 'RIOT. 101';
}

class EveApiAdmin extends ModelAdmin {
    static $managed_models = array('EveApi');
    static $url_segment = 'api-keys';
    static $menu_title = 'API Keys';
}

class DoctrineAdmin extends ModelAdmin
{
    static $managed_models = array('EveDoctrine', 'EveDoctrineShip');
    static $url_segment = 'doctrines';
    static $menu_title = 'Doctrines';
}

class EveTimerAdmin extends ModelAdmin
{
    static $managed_models = array('EvePosTimer');
    static $url_segment = 'timers';
    static $menu_title = 'POS Timers';
}

class EveCreditAdmin extends ModelAdmin
{
    static $managed_models = array('EveCreditProvider');
    static $url_segment = 'credit-providers';
    static $menu_title = 'Credit Provider';
}

class EveAllianceAdmin extends ModelAdmin
{
    static $managed_models = array('EveAlliance', 'EveCorp');
    static $url_segment = 'alliance';
    static $menu_title = 'Alliance';
}

class EveBlacklistAdmin extends ModelAdmin
{
    static $managed_models = array('EveBlacklist');
    static $url_segment = 'blacklist';
    static $menu_title = 'Pilot Blacklist';
}
