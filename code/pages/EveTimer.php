<?php

class EveTimerPage extends Page {
    private static $db = array(
        'Regions' => 'MultiValueField',
        'HiddenTypes' => 'MultiValueField'
    );

    function getCMSFields()
    {
        $f = parent::getCMSFields();

        $regions = mapRegions::get('mapRegions', 'regionID < 11000001');
        $regionMap = array();
        foreach($regions as $r) {
            $regionMap[$r->regionID] = $r->regionName;
        }

        $f->findOrMakeTab('Root.TimerOptions', 'Timer Options');
        $f->addFieldToTab('Root.TimerOptions',
            new MultiValueDropDownField('Regions', 'Select Regions you want to display (none to show all)', $regionMap)
        );

        $types = array(
            'CTA'           => 'CTA',
            'Strat-Op'      => 'Strat-Op',
            'Alliance-Op'   => 'Alliance-Op',
            'Major-Op'      => 'Major-Op',
            'Tower'         => 'Tower',
            'Station'       => 'Station',
            'iHub'          => 'iHub',
            'TCU'           => 'TCU',
            'SBU'           => 'SBU',
            'PoCo'          => 'PoCo',
            'Other'         => 'Other',
            'Roam'          => 'Roam'
        );
        $f->addFieldToTab('Root.TimerOptions',
            new MultiValueDropDownField('HiddenTypes', 'Select the Timer Types to Hide', $types)
        );
        return $f;
    }

    function RegionFilter()
    {
        $r = $this->Regions->getvalues();
        if(!$r) return false;

        $systems_in_regions = mapSolarSystems::get_by_region($r);
        $systems = array();

        foreach($systems_in_regions as $s) {
            $systems[]= $s->solarSystemID;
        }
        array_walk($systems, array('Convert', 'raw2sql'));

        return sprintf("TargetSolarSystem IN ('%s')", implode($systems, "','"));
    }

    function TypesFilter()
    {
        $ht = $this->HiddenTypes->getvalues();
        if(!$ht) return false;

        array_walk($ht, array('Convert', 'raw2sql'));

        return sprintf("Type NOT IN ('%s')", implode($ht, "','"));
    }

    function Regions()
    {
        $r = $this->Regions->getvalues();
        if(!$r) return false;

        array_walk($r, array('Convert', 'raw2sql'));

        return mapRegions::get('MapRegions', sprintf("regionID IN ('%s')", implode($r, "','")));
    }

    function NextTimer()
    {
        $filter = 'TimerEnds > NOW() AND Hidden = 0';
        if($rf = $this->RegionFilter()) $filter = sprintf("%s AND %s", $filter, $rf);
        if($tf = $this->TypesFilter()) $filter = sprintf("%s AND %s", $filter, $tf);
        return EveTimer::get_one('EveTimer', $filter);
    }

    function Timers()
    {
        $filter = 'TimerEnds > NOW() AND Hidden = 0';
        if($rf = $this->RegionFilter()) $filter = sprintf("%s AND %s", $filter, $rf);
        if($tf = $this->TypesFilter()) $filter = sprintf("%s AND %s", $filter, $tf);
        return EveTimer::get('EveTimer', $filter);
    }

    function PastTimers()
    {
        $filter = 'TimerEnds BETWEEN NOW() - INTERVAL 2 DAY AND NOW() AND Hidden = 0';
        if($rf = $this->RegionFilter()) $filter = sprintf("%s AND %s", $filter, $rf);
        if($tf = $this->TypesFilter()) $filter = sprintf("%s AND %s", $filter, $tf);
        return EveTimer::get('EveTimer', $filter);
    }
}

class EveTimerPage_Controller extends Page_Controller {
    function init()
    {
        Requirements::JavaScript('eacc/thirdparty/jquery.countdown.min.js');
        Requirements::CustomScript(<<<JS
            $(function(){
                $('.countdown').each(function(k,v){
                    ts = parseInt($(this).text());
                    $(this).countdown({
                        until: new Date(ts * 1000),
                        compact: true,
                        description: ''
                    });
                });
            });
JS
        );
        return parent::init();
    }
}
