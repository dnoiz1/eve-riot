<?php
class EveLogisticsOrderHistory extends DataObject
{
    static $db = array(
        'Status' => 'Varchar(255)',
    );

    static $has_one = array(
        'Member' => 'Member',
        'EveLogisticsOrder' => 'EveLogisticsOrder'
    );

    static $default_sort = 'Created DESC';
}
