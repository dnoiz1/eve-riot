<?php

class EveLogisticsItem extends DataObject
{
    static $db = array(
        'Price' => 'Decimal(15,2)',
        'Name'  => 'Varchar(255)',
        'Qty'   => 'Int'
    );

    static $has_one = array(
        'invTypes' => 'invTypes',
        'EveLogisticsOrder' => 'EveLogisticsOrder'
    );
}
