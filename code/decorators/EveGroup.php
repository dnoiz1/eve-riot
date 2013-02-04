<?php
class EveGroup extends DataObjectDecorator
{
    public function extraStatics()
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

    public function UpdateCMSFields($f)
    {
        $f->addFieldToTab('Root.Members', new CheckBoxField('ApiManaged', 'Managed By EVE Api?'));
        return $f;
    }
}
