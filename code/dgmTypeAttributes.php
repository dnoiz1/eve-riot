<?php

class dgmTypeAttributes extends DataObject
{
    static $db = array(
        'typeID' => 'int',
        'attributeID' => 'Int',
        'valueInt'  => 'Int',
        'valueFloat' => 'Double'
    );
}
