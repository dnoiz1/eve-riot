<?php

class mapSolarSystemBridges extends DataObject
{
    static $db = array(
        'Status'     =>  'Varchar(10)',
        'Friendly'          =>  'Varchar(3)',
        'Password'          =>  'Varchar(255)'
    );

    static $has_one = array(
        'fromSolarSystem' => 'mapSolarSystems',
        'fromRegion'      => 'mapSolarSystems',
        'toRegion'        => 'mapSolarSystems',
        'fromCelestial'   => 'mapSolarSystems',
        'toCelestial'     => 'Varchar(10)'
    );
}
