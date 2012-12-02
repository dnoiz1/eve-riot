<?php

class EveLogisticsOrder extends DataObject
{
    static $has_one = array(
        'Member' => 'Member'
    );

    static $has_many = array(
        'EveLogisticsItems' => 'EveLogisticsItem'
    );
}
