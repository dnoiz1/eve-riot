<?php

class EveBlacklist extends DataObject
{
    private static $db = array(
        'Type'      => "Enum('Character, KeyID, EmailAddress, Corporation', 'Character')",
        'Value'     => 'Varchar(255)',
        'Source'    => 'Varchar(255)',
        'Reason'    => 'Text',
    );

    private static $has_one = array(
        'AddedBy'   => 'Member'
    );

    static $summary_fields = array(
        'Type'      => 'Type',
        'Value'     => 'Value',
        'Source'    => 'Source',
        'AddedBy.FirstName'   => 'AddedBy'
    );

    static $searchable_fields = array(
        'Type'      => 'Type',
        'Value'     => 'Value',
        'Source'    => 'Source',
        'AddedBy.FirstName' => 'AddedBy'
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName('AddedByID');
        return $fields;
    }

    function canCreate($member = null)
    {
        return Permission::check('EVE_BLACKLIST_ADMIN');
    }

    function canView($member = null)
    {
        return Permission::check('EVE_BLACKLIST_ADMIN');
    }

    function canEdit($member = null)
    {
        if(!$member) {
            $member = Member::CurrentUser();
            if($member) {
                if($member->ID == $this->AddedByID) {
                    return Permission::check('EVE_BLACKLIST_ADMIN');
                }
            }
        }
        return Permission::check('ADMIN');
    }

    function canDelete($member = null)
    {
        if(!$member) {
            $member = Member::CurrentUser();
            if($member) {
                if($member->ID == $this->AddedByID) {
                    return Permission::check('EVE_BLACKLIST_ADMIN');
                }
            }
        }
        return Permission::check('ADMIN');
    }

    function onBeforeWrite()
    {
        parent::onBeforeWrite();
        $member = Member::CurrentUser();
        if(!$this->ID && $member) {
            $this->AddedByID = $member->ID;
        }
    }

}
