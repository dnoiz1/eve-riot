<?php

class EveBlacklist extends DataObject
{
    static $db = array(
        'Type'      => "Enum('Character, KeyID, EmailAddress', 'Character')",
        'Value'     => 'Varchar(255)',
        'Source'    => 'Varchar(255)',
        'Reason'    => 'Text',
        'AddedBy'   => 'Varchar(255)'
    );

    static $summary_fields = array(
        'Type',
        'Value',
        'Source',
        'AddedBy'
    );

    static $searchable_fields = array(
        'Type',
        'Value',
        'Source',
        'AddedBy'
    );

    function canCreate()
    {
        return Permission::check('EVE_BLACKLIST_ADMIN');
    }

    function canView()
    {
        return Permission::check('EVE_BLACKLIST_ADMIN');
    }

    function canEdit()
    {
        return Permission::check('EVE_BLACKLIST_ADMIN');
    }

    function canDelete()
    {
        return Permission::check('EVE_BLACKLIST_ADMIN');
    }
}
