<?php
class EveBlacklistedApi extends DataObject {
    static $db = array(
        'KeyID' => 'Int',
        'vCode' => 'Varchar(255)',
        'Valid' =>  'Boolean'
    );

    static $has_one = array(
        'Member' => 'Member'
    );

    static $defaults = array(
        'Valid' => 1
    );

    static $summary_fields = array(
        'Pilot',
        'KeyID',
        'vCode',
        'Valid'
    );

    static $casting = array(
        'Pilot' => 'Varchar(255)'
    );
}

