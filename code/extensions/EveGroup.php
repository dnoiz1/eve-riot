<?php
class EveGroup extends DataExtension
{
    public function extraStatics($class = null,  $extension = null)
    {
        return array(
            'db' => array(
                'Ticker'    => 'Varchar(5)',
                'ApiManaged'    => 'Boolean'
            ),
            'defaults' => array(
                'ApiManaged'    => 1
            )
        );
    }

    public function UpdateCMSFields(FieldList $f)
    {
        $f->addFieldToTab('Root.Members', new CheckBoxField('ApiManaged', 'Managed By EVE Api?'));
        return $f;
    }
}
