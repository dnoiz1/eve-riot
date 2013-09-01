<?php

class EveTimer extends DataObject
{
    private static $db = array(
        'TimerEnds'     => 'SS_DateTime',
        'TargetSolarSystem'   => 'Int',
        'Planet'        => 'Int',
        'Moon'          => 'Int',
        'Friendly'      => "Enum('Yes, No', 'Yes')",
        'Defended'      => "Enum('N/A, Yes, No', 'No')",
        'Timer'         => "Enum('Shield, Final, None', 'Final')",
        'Owner'         => 'Varchar(100)',
        'Type'          => "Enum('CTA, Tower, Station, iHub, TCU, SBU, PoCo, Other, Roam, Strat-Op, Alliance-Op, Major-Op', 'Tower')",
        'Faction'       => "Enum('N/A,  Amarr, Angel, Blood, Caldari, Dark Blood, Domination, Dread Guristas, Gallente, Guristas, Minmatar, Sansha, Shadow, Serpentis, True Sansha', 'N/A')",
        'Size'          => "Enum('N/A, Small, Medium, Large', 'N/A')",
        'FormUpSolarSystem' => 'Int',
        'FurtherInfo'    => 'Text',
        'Hidden'        => 'Boolean'
    );

    private static $has_one = array(
        'AddedBy' => 'Member'
    );

    static $api_access = array(
        'view' => array(
            'TimerEnds',
            'TargetSolarSystem',
            'Planet',
            'Moon',
            'Friendly',
            'Defended',
            'Timer',
            'Owner',
            'Type',
            'Faction',
            'Size',
            'FormUpSolarSystem',
            'FurtherInfo',
            'Hidden'
        ),
        'edit' => array()
    );

//    static $api_access = true;

    static $summary_fields = array(
        'TimerEnds' => 'TimerEnds',
        'Type' => 'Type',
        'TargetSystemName' => 'TargetSystemName',
        'Moon' => 'Moon',
        'Planet' => 'Planet',
        'Timer' => 'Timer',
        'AddedBy.FirstName'   => 'AddedBy'
    );

    static $field_labels = array(
        'TargetSystemName' => 'Solar System',
        'AddedBy'   => 'Added By'
    );

    static $casting = array(
        'TargetSystem' => 'Varchar(100)'
//        'FormUpSolarSystemName' => 'Varchar(100)',
    );

//    static $searchable_fields = array(
//        'TimerEnds',
//        'TargetSolarSystem'
//    );

    static $default_sort = "TimerEnds ASC";

    function getCMSFields()
    {
        $f = parent::getCMSFields();

        $date = DateTimeField::create('TimerEnds', 'Timer Ends (EVE Time)');
        $date->getDateField()->setConfig('showcalendar', true);
        $date->getDateField()->setConfig('dateformat', 'dd/MM/YYYY');
        $date->setTimeField(TimeDropdownField::create('TimerEnds[time]', ''));
        $date->getTimeField()->setConfig('timeformat', 'HH:mm');
        $f->ReplaceField('TimerEnds', $date);

        $targetSolarSystem = new EveSolarSystemAutoSuggestField('TargetSolarSystem', 'Target Solar System');
        $formUpSolarSystem = new EveSolarSystemAutoSuggestField('FormUpSolarSystem', 'Form Up Solar System');

        $f->replaceField('TargetSolarSystem', $targetSolarSystem);
        $f->replaceField('FormUpSolarSystem', $formUpSolarSystem);

        $f->removeByName('AddedByID');

        return $f;
    }

    function scaffoldSearchFields()
    {
        $f = parent::scaffoldSearchFields();
        $targetSolarSystem = new EveSolarSystemAutoSuggestField('TargetSolarSystem', 'Target Solar System');
        $f->replaceField('TargetSolarSystem', $targetSolarSystem);
        return $f;
    }

    function TargetSystem()
    {
        return mapSolarSystems::get_one('mapSolarSystems', sprintf("solarSystemID = %d", Convert::raw2sql($this->TargetSolarSystem)));
    }

    function TargetSystemName()
    {
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

    function canView($member = null)
    {
        return true;
    }

    function canCreate($member = null)
    {
        return Permission::check('EVE_TIMERS');
    }

    function canDelete($member = null)
    {
        return Permission::check('EVE_TIMERS');
    }

    function canEdit($member = null)
    {
        return Permission::check('EVE_TIMERS');
    }

    function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if($m = Member::currentUser()) {
            $this->AddedByID = $m->ID;
        }
    }
}
