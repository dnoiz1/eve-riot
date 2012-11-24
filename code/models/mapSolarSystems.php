<?php

class mapSolarSystems extends DataObject
{
    static $db = array(
        'regionID'          => 'Int',
        'constellationID'   => 'Int',
        'solarSystemID'     => 'Int',
        'solarSystemName'   => 'Varchar(100)',
        'x'                 => 'Double',
        'y'                 => 'Double',
        'z'                 => 'Double',
        'xMin'              => 'Double',
        'xMax'              => 'Double',
        'yMin'              => 'Double',
        'yMax'              => 'Double',
        'zMin'              => 'Double',
        'zMax'              => 'Double',
        'luminosity'        => 'Double',
        'border'            => 'Int',
        'fringe'            => 'Int',
        'corridor'          => 'Int',
        'hub'               => 'Int',
        'international'     => 'Int',
        'regional'          => 'Int',
        'constellation'     => 'Int',
        'security'          => 'Double',
        'factionID'         => 'Int',
        'radius'            => 'Double',
        'sunTypeID'         => 'Int',
        'securityClass'     => 'Varchar(2)'
    );

    function Region()
    {
        return mapRegions::get_one('mapRegions', sprintf("regionID = %d", $this->regionID));
    }
}
