<?php

class EveDoctrineShip extends DataObject
{
    static $db = array(
        'Name' => 'Varchar(255)',
        'Reimbursment' => 'Double',
        'TechLevel' => 'Enum("T1, T2, Faction", "T2")',
        'EFTTextBlock' => 'Text'
    );

    static $has_one = array(
        'EveDoctrine' => 'EveDoctrine'
    );

    static $summary_fields = array(
        'Name',
        'EveDoctrine.Title',
        'TechLevel',
    );

    static $searchable_fields = array(
        'EveDoctrine.Title',
        'Name',
        'TechLevel'
    );

    public $fitting = false;

    function getCMSFields()
    {
        $f = parent::getCMSFields();

        if($this->Fitting() && $this->Fitting()->NotFound()) {
            $text = implode($this->Fitting()->NotFound(), "\n");
            $f->insertAfter(new LiteralField('', sprintf("<p><h2>Please make sure these modules are using their current names</h2><pre>%s</pre></p>", Convert::raw2xml($text))), 'EFTTextBlock');
        }

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

    function Link()
    {
        return $this->EveDoctrine()->Link($this->ID);
    }

    function canView()
    {
        return $this->EveDoctrine()->canView();
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

    function canCreate()
    {
        return $this->canEdit();
    }

    function canDelete()
    {
        return $this->canEdit();
    }

}
