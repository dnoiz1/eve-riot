<?php

class EvePosTimer extends DataObject
{
    static $db = array(
        'TimerEnds' => 'SS_DateTime',
        'SolarSystem' => 'Int',
        'Planet'    => 'Int',
        'Moon'      => 'Int'
    );
}
