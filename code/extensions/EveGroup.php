<?php
class EveGroup extends DataExtension
{
    private static $db = array(
        'Ticker'    => 'Varchar(5)',
        'ApiManaged'    => 'Boolean'
    );

    private static $defaults = array(
        'ApiManaged'    => 1
    );

    public function UpdateCMSFields(FieldList $f)
    {
        $f->addFieldToTab('Root.Members', new CheckBoxField('ApiManaged', 'Managed By EVE Api?'));
        return $f;
    }
}
