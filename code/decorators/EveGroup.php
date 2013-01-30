<?php
class EveGroup extends DataObjectDecorator
{
    public function extraStatics()
    {
        return array(
            'db' => array(
                'Ticker'    => 'Varchar(5)'
            )
        );
    }
}
