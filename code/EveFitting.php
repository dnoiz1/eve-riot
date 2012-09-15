<?php

class EveFitting extends File
{
    static $allowed_extensions = array('xml');

    public $xml = false;
    public $index = 0;
    public $fit = array();
    public $invType = false;

    function Init()
    {
        $f = file_get_contents($this->getFullPath());
        if($f) {
            try {
                $this->xml = new SimpleXMLElement($f);
            } catch(Exception $e) {
                return false;
            }
            return true;
        }
        return false;
    }

    function Fittings()
    {
        if(!$this->xml) return array();
        return $this->xml->xpath('fitting');
    }

    function Count()
    {
        if(!$this->xml) return 0;
        return count($this->Fittings());
    }

    function FittingList()
    {
        $fits = array();
        foreach($this->Fittings() as $k => $v) {
            $v = $v->attributes();
            $fits[$k] = $v['name'];
        }
        return $fits;
    }

    function _ship($index = 0)
    {
        $index = ($index) ? $index : $this->index;
        $f =  $this->Fittings();
        if(!$this->xml || !array_key_exists($index, $f)) return false;
        $f = $f[$index]->shipType->attributes();
        $s = (string)$f['value'];
        $ship = invTypes::get_one('invTypes', sprintf("`typeName` = '%s'", Convert::raw2sql($s)));
        return ($ship) ? $ship : false;
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

    function SelectFitting($index = 0)
    {
        $index = (int)$index;
        if(!$this->xml || !array_key_exists($index, $this->FittingList())) return $this->index;
        $this->index = $index;
        $this->fit = array();
        return $this->index;
    }

    function _slots($slot = false)
    {
        if(!$this->xml) return false;

        $filter = false;
        switch($slot) {
            case 'high':
            case 'hi':
                $filter = 'hi';
                break;
            case 'med':
            case 'mid':
                $filter = 'med';
                break;
            case 'lo':
            case 'low':
                $filter = 'low';
                break;
            case 'rig':
                $filter = 'rig';
                break;
            case 'sub':
            case 'subsystem':
                $filter = 'subsystem';
                break;
            case 'drone':
                $filter = 'drone';
                break;
        }
        if($filter) {
            if(array_key_exists($filter, $this->fit)) return $this->fit[$filter];
            $filter = sprintf("hardware[@slot [contains(., '%s')]]", $filter);
        } else {
            $filter = 'hardware';
        }

        $f = $this->Fittings();
        //return $slots = $f[8]->xpath($filter);
        //return $this->index;
        $slots = $f[$this->index]->xpath($filter);
        $res = array();
        foreach($slots as $s) {
            $s = $s[0]->attributes();
            $i = substr($s['slot'], -1);
            $n = trim((string)$s['type']);
            $t = invTypes::get_one('invTypes', sprintf("typeName = '%s'", Convert::raw2xml($n)));
            if($t) {
                if($slot == 'drone') {
                    $t->typeName = sprintf('%dx %s', $s['qty'], $t->typeName);
                    $res[] = $t;
                } else {
                    $res[$i] = $t;
                }
            }
        }
        return $this->fit[$filter] = new DataObjectSet($res);
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
}

