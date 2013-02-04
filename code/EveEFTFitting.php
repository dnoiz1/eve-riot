<?php

class EveEFTFitting extends ViewableData
{
    public function __construct($textblock)
    {
        $e = str_replace("\r", "", $textblock);
//        $e = explode("\n", $textblock);
        $e = explode("\n", $e);

        if(preg_match('#\[(.+?),(.+?)\]#', array_shift($e), $match) === 1) {
            $this->hull = $match[1];
        }
        $c = 0;
    	foreach($e as $l) {
    	    if(strlen($l) === 0) {
    	        $c++;
    	        continue;
    	    }
    	    $this->textblock[$c][] = $l;
    	}
    }

    public $hull = false;
    public $textblock = array();
    public $fit = array();
    public $invType = false;
    public $notfound = array();

    function _ship()
    {
	    if(array_key_exists('hull', $this->fit)) return $this->fit['hull'];
        if(!$this->hull) return false;
        $ship = invTypes::get_one('invTypes', sprintf("`typeName` = '%s'", Convert::raw2sql($this->hull)));
        if($ship) {
            $this->fit['hull'] = $ship;
            return $ship;
        }
        return false;
    }

    function ShipName()
    {
        $s = $this->_ship();
        return ($s) ? $s->typeName : false;
    }

    function CanFly()
    {
        $ship = $this->_ship();
        if(!$ship || !$ship->CanUse()) return false;

        $slots = $this->_slots();
        if($slots) foreach($slots as $s) {
            if(!$s->CanUse()) return false;
        }
        return true;
    }

    function ShipID()
    {
        $s = $this->_ship();
        return ($s) ? $s->typeID : 0;
    }

    function _slots($slot = false)
    {
        if(!$slot) {
            $res = new DataObjectSet();
            foreach(array('hi', 'med', 'lo', 'rig', 'sub', 'rig') as $s) {
                $res->merge($this->_slots($s));
            }
            return $res;
        }

        switch($slot) {
            case 'high':
            case 'hi':
                $idx = 2;
                $slot = 'hi';
                break;
            case 'med':
            case 'mid':
                $idx = 1;
                $slot ='mid';
                break;
            case 'lo':
            case 'low':
                $idx = 0;
                $slot = 'lo';
                break;
            case 'rig':
                $idx = 3;
                break;
            case 'sub':
            case 'subsystem':
                $idx = 4;
                $slot = 'sub';
                break;
            case 'drone':
                $idx = 5;
                $slot = 'drone';
                break;
        }

        if(array_key_exists($idx, $this->fit)) return $this->fit[$idx];

        $res = array();

        if(array_key_exists($idx, $this->textblock)) {

            foreach($this->textblock[$idx] as $s) {
                // reset charge type
                $ct = false;

                if(strlen($s) == 0) continue;

                if(($offline = strpos($s, ' /OFFLINE')) !== false) {
                    $s = substr($s, 0, $offline);
                }

                if(strstr($s, ',')) {
                    list($s, $c) = explode(', ', $s);
                    $ct = invTypes::get_one('invTypes', sprintf("typeName = '%s'", Convert::raw2sql($c)));
                }

                if($idx == 5 && preg_match('#^(.*?) x(\d+)$#ims', $s, $m) !== false) {

                    $t = invTypes::get_one('invTypes', sprintf("typeName = '%s'", Convert::raw2sql($m[1])));
                    if($t) {
                        $t->setField('typeName', $s);
                        $t->setField('Qty', (int) $m[2]);
                    }
                } else {
                    $t = invTypes::get_one('invTypes', sprintf("typeName = '%s'", Convert::raw2sql($s)));
                }
                if($t) {
                    if($ct) $t->setField('Charge', $ct);
                    // regular strpos safety need not apply here
                    if($offline) {
                        $t->setField('typeName', sprintf("%s (offline)", $t->typeName));
                        $t->setField('Offline', true);
                    }
                    $res[] = $t;
                } else {
                    $this->notfound[$s] = $s;
//                    var_dump($s);
                }
            }
        }
        return $this->fit[$idx] = new DataObjectSet($res);
    }

    function HighSlots()
    {
        return $this->_slots('hi');
    }

    function MedSlots()
    {
        return $this->_slots('med');
    }

    function LowSlots()
    {
        return $this->_slots('lo');
    }

    function RigSlots()
    {
        return $this->_slots('rig');
    }

    function SubSystems()
    {
        return $this->_slots('sub');
    }

    function Drones()
    {
        return $this->_slots('drone');
    }

    function ShipDNA()
    {
    }

    function NotFound()
    {
        return $this->notfound;
        //(count($this->notfound) > 0) ? new DataObjectSet($this->notfound) : false;
    }
}
