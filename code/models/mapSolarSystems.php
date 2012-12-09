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

    static function get_by_region($regions, $name_or_id = 'id')
    {
        if(!$regions) return;

        $field = ($name_or_id == 'id') ? 'regionID' : 'regionName';
        $system_filter = '';

        if(is_array($regions) && count($regions) > 1) {
            array_walk($regions, array('Convert', 'raw2sql'));
            $system_filter = sprintf("%s IN ('%s')", $field, implode($regions, "','"));
        } else {
            if(is_array($regions)) $regions = $regions[0];
            $system_filter = sprintf("%s = '%s'", $field, Convert::raw2sql($regions));
        }

        return mapSolarSystems::get('mapSolarSystems', $system_filter);
    }

    public function Region()
    {
        return mapRegions::get_one('mapRegions', sprintf("regionID = %d", $this->regionID));
    }
}
