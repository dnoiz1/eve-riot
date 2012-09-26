<?php

class EveDoctrine extends DataObject
{
    static $db = array(
        'Title' => 'Varchar(255)',
        'Description' => 'HTMLText',
    );

    static $many_many = array(
        'EveDoctrineShip' => 'EveDoctrineShip'
    );

    static $summary_fields = array(
        'Title'
    );

    function getCMSFields()
    {
        $f = parent::getCMSFields();

        $st = new ManyManyDataObjectManager(
            $this,
            'EveDoctrineShip',
            'EveDoctrineShip',
            array('Name' => 'Name', 'Description' => 'Description'),
            'getCMSFields_forPopup'
        );

        $st->pageSize = 18;

        $st->setAddTitle('Doctrine Ships');
        $f->addFieldToTab('Root.EveDoctrineShip', $st);

        return $f;
    }

    function getCMSFields_forPopup()
    {
        $f = parent::getCMSFields();
        $f->removeByName('Eve Doctrine Ship');
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

    function EveDoctrineShipsAugmented()
    {
        $ds = $this->EveDoctrineShip();
        foreach($ds as $d) {
            $d->setField('Link', $this->Link($d->ID));
        }
        return $ds;
    }

    function canView()
    {
        if(Controller::CurrentPage()->ClassName == 'DoctrinePage') {
           return Controller::CurrentPage()->canView();
        }
        return $this->canEdit();
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
