<?php

class EveApiAdmin extends ModelAdmin {
    static $managed_models = array('EveApi');
    static $url_segment = 'api-keys';
    static $menu_title = 'API Keys';

    // disable import
    static $model_importers = array();

    // dodgy disable export.
    // not implimented in gridfield yet?
    public function getExportFields() {
        return array('ID');
    }
}

class EveTimerAdmin extends ModelAdmin
{
    static $managed_models = array('EveTimer');
    static $url_segment = 'timers';
    static $menu_title = 'Timers';

    // disable import
    static $model_importers = array();

    // dodgy disable export.
    public function getExportFields() {
        return array('ID');
    }
}

class EveAllianceAdmin extends ModelAdmin
{
    static $managed_models = array('EveAlliance', 'EveCorp');
    static $url_segment = 'alliance';
    static $menu_title = 'Alliances';

    // disable import
    static $model_importers = array();

    // dodgy disable export.
    public function getExportFields() {
        return array('ID');
    }
}

class EveMemberCacheAdmin extends ModelAdmin
{
    static $managed_models = array('EveMemberCharacterCache');
    static $url_segment = 'membercache';
    static $menu_title = 'Member Cache';

    // disable import
    static $model_importers = array();

    // dodgy disable export.
    public function getExportFields() {
        return array('ID');
    }
}

class EveBlacklistAdmin extends ModelAdmin
{
    static $managed_models = array('EveBlacklist');
    static $url_segment = 'blacklist';
    static $menu_title = 'Pilot Blacklist';

    // disable import
    static $model_importers = array();

    // dodgy disable export.
    public function getExportFields() {
        return array('ID');
    }
}

class ManagedGroupAdmin extends ModelAdmin
{
    static $managed_models = array('EveManagedGroup', 'EveGroupApplication');
    static $url_segment    = 'managed-groups';
    static $menu_title     = 'Managed Groups';

    // disable import
    static $model_importers = array();

    // dodgy disable export.
    public function getExportFields() {
        return array('ID');
    }
}
