<?php

class EvePosTimer extends DataObject
{
    static $db = array(
        'TimerEnds'     => 'SS_DateTime',
        'TargetSolarSystem'   => 'Int',
        'Planet'        => 'Int',
        'Moon'          => 'Int',
        'Friendly'      => "Enum('Yes, No, N/A', 'Yes')",
        'Defended'      => "Enum('Yes, No, N/A', 'No')",
        'Timer'         => "Enum('Shield, Final, None', 'Final')",
        'Owner'         => 'Varchar(100)',
        'Type'          => "Enum('CTA, Tower, Station, iHub, TCU, SBU, PoCo, Other, Roam, Strat-Op', 'Tower')",
        'FormUpSolarSystem' => 'Int',
        'FutherInfo'    => 'Text',
        'Hidden'        => 'Boolean'
    );

    static $summary_fields = array(
//        'TimerEnds',
        'Type',
        'TargetSystemName',
        'Moon',
        'Planet',
        'Timer',
    );

    static $casting = array(
        'TargetSystemName' => 'Varchar(100)'
//        'FormUpSolarSystemName' => 'Varchar(100)',
    );

    static $searchable_fields = array(
/*        'TimerEnds',
        'TargetSolarSystem' */
    );

    static $default_sort = "TimerEnds ASC";

    function getCMSFields()
    {
        $f = parent::getCMSFields();
        $date = new DateTimeField('TimerEnds', 'Timer Ends (EVE Time)');
        $date->getDateField()->setConfig('showcalendar', true);
        $date->getDateField()->setConfig('dateformat', 'dd/MM/YYYY');
        $date->getTimeField()->setConfig('showdropdown', true);
        $f->ReplaceField('TimerEnds', $date);

        $targetSolarSystem = new EveSolarSystemAutoSuggestField('TargetSolarSystem', 'Target Solar System');
        $formUpSolarSystem = new EveSolarSystemAutoSuggestField('FormUpSolarSystem', 'Form Up Solar System');

        $f->replaceField('TargetSolarSystem', $targetSolarSystem);
        $f->replaceField('FormUpSolarSystem', $formUpSolarSystem);
        return $f;
    }

    function scaffoldSearchFields()
    {
        $f = parent::scaffoldSearchFields();
        $date = new DateTimeField('TimerEnds', 'Timer Ends');
        $date->getDateField()->setConfig('showcalendar', true);
        $date->getDateField()->setConfig('dateformat', 'dd/MM/YYYY');
        $date->getTimeField()->setConfig('showdropdown', true);
        $f->ReplaceField('TimerEnds', $date);

        $targetSolarSystem = new EveSolarSystemAutoSuggestField('TargetSolarSystem', 'Target Solar System');
        $f->replaceField('TargetSolarSystem', $targetSolarSystem);
        return $f;
    }

    function TargetRegion()
    {
        if($r = mapSolarSystems::get_one('mapSolarSystems', sprintf("solarSystemID = %d", Convert::raw2sql($this->TargetSolarSystem)))) {
            return $r->Region();
        }
    }

    function TargetSystem()
    {
        return mapSolarSystems::get_one('mapSolarSystems', sprintf("solarSystemID = %d", Convert::raw2sql($this->TargetSolarSystem)));
    }

    function TargetSystemName() {
        return ($ts = $this->TargetSystem()) ? $ts->solarSystemName : '';
    }

    function FormUpSystem()
    {
        return mapSolarSystems::get_one('mapSolarSystems', sprintf("solarSystemID = %d", Convert::raw2sql($this->ForumUpSolarSystem)));
    }

    /* no idea why SS_DateTime doesnt return a
     * SS_DateTime Object all the time?
     */

    function TimerEndsTimeStamp()
    {
        $fuckoff = strtotime($this->TimerEnds);
        return $fuckoff;
    }

    function canView()
    {
        return true;
    }

    function canCreate()
    {
        return $this->canEdit();
    }

    function canDelete()
    {
        return $this->canEdit();
    }

    function canEdit()
    {
        return Permission::check('EVE_TIMERS');
    }

}
