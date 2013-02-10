<?php

class EveTimeCodePage extends Page {
    static $db = array(
        'AffiliateID'   => 'Int'
    );

    function getCMSFields()
    {
        $f = parent::getCMSFields();
        $f->push(new NumericField('AffiliateID', 'Shattered Crystal AffilitateID'));
        return $f;
    }
}

class EveTimeCodePage_controller extends Page_controller {}

