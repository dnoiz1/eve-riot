<?php

class EveDoctrineShip extends DataObject
{
    static $db = array(
        'Name'          => 'Varchar(255)',
        'Reimbursment'  => 'Double',
        'TechLevel'     => 'Enum("T1, T2, Faction", "T2")',
        'EFTTextBlock'  => 'Text',
        'Description'   => 'HTMLText',
        'AdminNotes'    => 'Text'
    );

    static $belongs_many_many = array(
        'EveDoctrine' => 'EveDoctrine'
    );

    static $summary_fields = array(
        'Name',
//        'EveDoctrine.Title',
        'TechLevel',
        //'Description',
        'AdminNotes',
        'LastEdited'
    );

    static $searchable_fields = array(
        'Name',
        'Description',
        'TechLevel'
    );

    public $fitting = false;

    function getCMSFields()
    {
        $f = parent::getCMSFields();

        $f->insertAfter(new DateTimeField_readonly('LastEdited', 'Last Updated'), 'Name');

        if($this->Fitting() && $this->Fitting()->NotFound()) {
            $text = implode($this->Fitting()->NotFound(), "\n");
            $f->insertAfter(new LiteralField('', sprintf("<p><h2>Please make sure these modules are using their current names</h2><pre>%s</pre></p>", Convert::raw2xml($text))), 'EFTTextBlock');
        }

        $st = new ManyManyDataObjectManager(
            $this,
            'EveDoctrine',
            'EveDoctrine',
            array(
                'Title' => 'Title'
            ),
            'getCMSFields_forPopup'
        );

        $st->setAddTitle('Doctrine');
        $f->addFieldToTab('Root.EveDoctrine', $st);

        return $f;
    }

    function getCMSFields_forPopup()
    {
        $f = parent::getCMSFields();
        $f->removeByName('Eve Doctrine');
        return $f;
    }

    function EveFitting()
    {

    }

    function Fitting()
    {
        if($this->fitting) return $this->fitting;
        $this->fitting = new EveEFTFitting($this->EFTTextBlock);
        $this->fitting->_slots();
        return $this->fitting;
    }

    function Link($action = false)
    {
        if($hasLink = $this->getField('Link')) return $hasLink;

        $cp = Controller::CurrentPage();
        if($a = $cp->urlParams['Action']) {
            return $cp->Link(sprintf("%s/%d", $a, $this->ID));
        }

        return $cp->Link($action);
    }

    function Doctrine()
    {
        return Controller::CurrentPage()->doctrine;
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
        /*
        $groups = array('administrators', 'directors', 'doctrine-editor');

        if($m = Member::CurrentUser()) {
            foreach($groups as $g) {
                if($m->inGroup($g)) return true;
            }
        }
        return false;
        */
        return Permission::check('EVE_DOCTRINE');
    }
}
