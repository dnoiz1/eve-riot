<?php

class EveMemberCharacterCache extends DataObject
{
    static $db = array(
        'CharacterID'   => 'Int',
        'CharacterName' => 'Varchar(255)',
        'APIHash'       => 'Varchar(32)'
    );

    static $has_one = array(
        'Member'    => 'Member',
        'EveApi'    => 'EveApi'
    );

    function Character()
    {
        if($api = $this->EveApi()) {
            return new EveCharacter($this->CharacterID, $api);
        }
        return false;
    }
}
