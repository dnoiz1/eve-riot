<?php

class mapRegions extends DataObject
{
    static $db = array(
        'regionID'      => 'Int',
        'regionName'    => 'Varchar(100)',
        'x'             => 'Double',
        'y'             => 'Double',
        'z'             => 'Double',
        'xMin'          => 'Double',
        'xMax'          => 'Double',
        'yMin'          => 'Double',
        'yMax'          => 'Double',
        'zMin'          => 'Double',
        'zMax'          => 'Double',
        'factionID'     => 'Int',
        'radius'        => 'Double'
    );

    static $default_sort = 'regionName ASC';
}
