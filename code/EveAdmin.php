<?php

class EveApiAdmin extends ModelAdmin {
    static $managed_models = array('EveApi');
    static $url_segment = 'api-keys';
    static $menu_title = 'API Keys';
}

class EveTimerAdmin extends ModelAdmin
{
    static $managed_models = array('EvePosTimer');
    static $url_segment = 'timers';
    static $menu_title = 'POS Timers';
}

class EveAllianceAdmin extends ModelAdmin
{
    static $managed_models = array('EveAlliance', 'EveCorp');
    static $url_segment = 'alliance';
    static $menu_title = 'Member Alliances';
}

class EveMemberCacheAdmin extends ModelAdmin
{
    static $managed_models = array('EveMemberCharacterCache');
    static $url_segment = 'membercache';
    static $menu_title = 'Member Character Cache';
}

class EveBlacklistAdmin extends ModelAdmin
{
    static $managed_models = array('EveBlacklist');
    static $url_segment = 'blacklist';
    static $menu_title = 'Pilot Blacklist';
}
