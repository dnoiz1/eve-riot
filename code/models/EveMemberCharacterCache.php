<?php

class EveMemberCharacterCache extends DataObject
{
    static $db = array(
        'CharacterID'   => 'Int',
        'CharacterName' =>  'Varchar(255)'
    );

    static $has_one = array(
        'Member'    => 'Member',
        'EveApi'    => 'EveApi'
    );
}
