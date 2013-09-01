<?php
class EveGroup extends DataExtension
{
    private static $db = array(
        //'Ticker'    => 'Varchar(5)',
        'ApiManaged'    => 'Boolean'
    );

    private static $defaults = array(
        'ApiManaged'    => 1
    );

    static $default_sort = "ParentID ASC";

    static $summary_fields = array(
        'Title' => 'Title',
//        'Parent.Title' => 'Parent Group'
    );

    static $searchable_fields = array(
        'Title' => 'Title'
    );

    public function UpdateCMSFields(FieldList $f)
    {
        $f->addFieldToTab('Root.Members', new CheckBoxField('ApiManaged', 'Managed By EVE Api?'));
        return $f;
    }

    public function EveManagedGroup()
    {
        return EveManagedGroup::get()->Filter('GroupID', $this->owner->ID)->limit(1)->First();
    }
}
