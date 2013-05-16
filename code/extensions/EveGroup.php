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
        'ParentID' => 'Title'
    );

    static $searchable_fields = array(
        'Title' => 'Title'
    );

    public function UpdateCMSFields(FieldList $f)
    {
        $f->addFieldToTab('Root.Members', new CheckBoxField('ApiManaged', 'Managed By EVE Api?'));
        return $f;
    }
}
