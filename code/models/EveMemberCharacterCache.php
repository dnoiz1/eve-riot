<?php

class EveMemberCharacterCache extends DataObject
{
    static $db = array(
        'CharacterID'       => 'Int',
        'CharacterName'     => 'Varchar(255)',
        'CorporationID'     => 'Int',
        'CorporationName'   => 'Varchar(255)',
        'APIHash'           => 'Varchar(32)'
    );

    static $has_one = array(
        'Member'    => 'Member',
        'EveApi'    => 'EveApi'
    );

    static $summary_fields = array(
        'CharacterID',
        'CharacterName',
        'CorporationID',
        'CorporationName'
    );

    function EveCorp()
    {
        return EveCorp::get_one('EveCorp', sprintf("CorpID = '%d'", $this->CorporationID));
    }

    function Character()
    {
        if($api = $this->EveApi()) {
            return new EveCharacter($this->CharacterID, $api);
        }
        return false;
    }
}
