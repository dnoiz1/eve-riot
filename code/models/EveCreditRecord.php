<?php

class EveCreditRecord extends DataObject
{
    static $db = array(
        'Amount'        => 'Decimal(17,2)',
        'RefID'         => 'Double',
        'CharacterID'   => 'Int',
        'Date'          => 'SS_DateTime'
    );

    static $has_one = array(
        'EveCreditProvider' => 'EveCreditProvider'
    );

    static $default_sort = "Date DESC";

    static $summary_fields = array(
        'Date',
        'RefID',
        'CharacterID',
        'Amount'
    );

    function Character()
    {
        return new EveCharacter($this->CharacterID);
    }

    function canView()
    {
        return (Permission::check('ADMIN') || ($this->MemberID === Member::CurrentUser()->ID)) ? true : false;
    }

    function canEdit()
    {
        return false;
    }

    function canDelete()
    {
        return false;
    }

    function canCreate()
    {
        return false;
    }
}
