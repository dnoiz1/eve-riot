<?php

class EveDoctrine extends DataObject
{
    static $db = array(
        'Title' => 'Varchar(255)',
        'Description' => 'HTMLText',
    );

    static $has_many = array(
        'EveDoctrineShip' => 'EveDoctrineShip'
    );

    static $summary_fields = array(
        'Title'
    );

    function getCMSFields()
    {
        $f = parent::getCMSFields();
        return $f;
    }

    function Link($action = false)
    {
        $l = strtolower(str_replace(' ', '-', $this->Title));
        return Controller::CurrentPage()->Link(sprintf('%s/%s', $l, $action));
    }

    function MenuTitle()
    {
        return $this->Title;
    }

    function canView()
    {
        if(Controller::CurrentPage()->ClassName == 'DoctrinePage') {
           return Controller::CurrentPage()->canView();
        }
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
        $groups = array('administrators', 'directors', 'doctrine-editor');

        if($m = Member::CurrentUser()) {
            foreach($groups as $g) {
                if($m->inGroup($g)) return true;
            }
        }
        return false;
    }
}
