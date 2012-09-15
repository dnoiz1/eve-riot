<?php

class RiotAdmin extends ModelAdmin {
    static $managed_models = array('FAQ', 'EveApi');
    static $url_segment = 'riot';
    static $menu_title = 'RIOT';
}

class DoctrineAdmin extends ModelAdmin
{
    static $managed_models = array('EveDoctrine', 'EveDoctrineShip');
    static $url_segment = 'doctrines';
    static $menu_title = 'Doctrines';
}
