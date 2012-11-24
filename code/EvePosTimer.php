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
        'Type'          => "Enum('CTA, Tower, Station, iHub, TCU, SBU, Other, Roam', 'Tower')",
        'FormUpSolarSystem' => 'Int',
        'FutherInfo'    => 'Text'
    );

    static $summary_fields = array(
//        'TimerEnds',
        'Type',
        'TargetSolarSystemName',
        'Moon',
        'Planet',
        'Timer',
    );

    static $casting = array(
        'TargetSolarSystemName' => 'Varchar(100)',
        'FormUpSolarSystemName' => 'Varchar(100)',
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

    function TargetSolarSystemName()
    {
        return mapSolarSystems::get_one('mapSolarSystems', sprintf("solarSystemID = %d", Convert::raw2sql($this->TargetSolarSystem)))->solarSystemName;
    }

    function FormUpSolarSystemName()
    {
        return mapSolarSystems::get_one('mapSolarSystems', sprintf("solarSystemID = %d", Convert::raw2sql($this->ForumUpSolarSystem)))->solarSystemName;
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
        $groups = array('administrators', 'directors', 'officers', 'fleet-commanders');

        if($m = Member::CurrentUser()) {
            foreach($groups as $g) {
                if($m->inGroup($g)) return true;
            }
        }
        return false;
    }

}
