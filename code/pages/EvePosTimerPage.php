<?php

class EvePosTimerPage extends Page {
    static $db = array(
        'Regions' => 'MultiValueField'
    );

    function getCMSFields()
    {
        $f = parent::getCMSFields();

        $regions = mapRegions::get('mapRegions', 'regionID < 11000001');
        $regionMap = array();
        foreach($regions as $r) {
            $regionMap[$r->regionID] = $r->regionName;
        }

        $f->findOrMakeTab('Root.Content.TimerOptions', 'Timer Options');
        $f->addFieldToTab('Root.Content.TimerOptions',
            new MultiValueDropDownField('Regions', 'Select Regions you want to display', $regionMap)
        );
        return $f;
    }

    function RegionFilter()
    {
        $r = $this->Regions->getvalues();
        $systems_in_regions = mapSolarSystems::get_by_region($r);
        $systems = array();

        foreach($systems_in_regions as $s) {
            $systems[]= $s->solarSystemID;
        }
        array_walk($systems, array('Convert', 'raw2sql'));

        return sprintf("TargetSolarSystem IN ('%s')", implode($systems, "','"));
    }

    function Regions()
    {
        $r = $this->Regions->getvalues();
        array_walk($r, array('Convert', 'raw2sql'));

        return mapRegions::get('MapRegions', sprintf("regionID IN ('%s')", implode($r, "','")));
    }

    function NextTimer()
    {
        $filter = 'TimerEnds > NOW() AND Hidden = 0';
        if($rf = $this->RegionFilter()) $filter = sprintf("%s AND %s", $filter, $rf);
        return EvePosTimer::get_one('EvePosTimer', $filter);
    }

    function Timers()
    {
        $filter = 'TimerEnds > NOW() AND Hidden = 0';
        if($rf = $this->RegionFilter()) $filter = sprintf("%s AND %s", $filter, $rf);
        return EvePosTimer::get('EvePosTimer', $filter);
    }

    function PastTimers()
    {
        $filter = 'TimerEnds BETWEEN NOW() - INTERVAL 2 DAY AND NOW() AND Hidden = 0';
        if($rf = $this->RegionFilter()) $filter = sprintf("%s AND %s", $filter, $rf);
        return EvePosTimer::get('EvePosTimer', $filter);
    }
}

class EvePosTimerPage_Controller extends Page_Controller {}
