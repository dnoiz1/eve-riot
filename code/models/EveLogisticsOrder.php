<?php

class EveLogisticsOrder extends DataObject
{
    static $db = array(
        'Status' => "Enum('Pending, Paid, Purchased, Shipped, Complete', 'Pending')",
        'Date'   => 'SS_DateTime'
    );

    static $has_one = array(
        'Member' => 'Member',
        'EveLogisticsPage' => 'EveLogisticsPage'
    );

    static $has_many = array(
        'EveLogisticsItems' => 'EveLogisticsItem',
        'EveLogisticsOrderHistory'  => 'EveLogisticsOrderHistory'
    );

    function Price()
    {
        return DB::Query(sprintf("SELECT SUM(Price) FROM EveLogisticsItem WHERE EveLogisticsOrderID = %d LIMIT 1", $this->ID))->value();
    }

    public function onAfterWrite()
    {
        if($m = Member::CurrentUser()) {
            if($this->Status != $this->changed->Status) {
                $hist = new EveLogisticsOrderHistory();
                $hist->Status = $this->Status;
                $hist->MemberID = $m->ID;
                $hist->EveLogisticsOrderID = $this->ID;
                $hist->write();
            }
        }

        return parent::onAfterWrite();
    }
}
