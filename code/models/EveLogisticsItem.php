<?php

class EveLogistsicsItem extends DataObject
{
    static $db = array(
        'Price' => 'Double'
    );

    static $has_one = array(
        'invTypes' => 'invTypes'
    );

    static $belongs_one = array(
        'EveLogisticsOrder' => 'EveLogisticsOrder'
    );
}
