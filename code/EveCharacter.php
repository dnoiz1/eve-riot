<?php

require_once('../mysite/thirdparty/ale/factory.php');

class EveCharacter extends ViewableData
{
    public $api = false;
    public $characterID = false;
    public $cache = false;

    function __construct($characterID = null, EveApi $api = null)
    {
        if(!$characterID) new Exception('need a character id bro');
        // use the EveMemberChache first
        if(!$api) {
            if($cache = EveMemberCharacterCache::get_one('EveMemberCharacterCache', sprintf("CharacterID = '%d'", $characterID))) {
                $api = $cache->EveApi();
            }
        }

        // first check if we have an EveAPI
        if($api) {
            if($this->findCharacter($characterID, $api)) return $this;
        } else {
            // look for the charid on the current member
            $m = Member::currentMember();
            if($m) {
                foreach($m->ApiKeys() as $a) {
                    if($this->findCharacter($characterID, $a)) return $this;
                }
            }

            // slow horse mode, should probably avoid this
            /* ye, ye fuck em, YOLO
            $apis = EveApi::get('EveApi');
            foreach($apis as $a) {
                if($this->findCharacter($characterID, $a)) return $this;
            }
            */
        }
        return false;
    }

    function setCharacterID($id = null)
    {
        if(!$id) {
             $id = $this->characterID;
        } else {
            $this->characterID = (int)$id;
        }
        if($this->api) $this->api->ale->setCharacterID($this->characterID);
    }

    function findCharacter($characterID, EveApi $api)
    {
        // find right character.
        foreach($api->Characters() as $c) {
            if($c['characterID'] == $characterID) {
                $this->api = $api;
                $this->setCharacterID($c['characterID']);
                return true;
            }
        }
        return false;
    }

    function forTemplate()
    {
        return $this->Name();
    }

    function _characterSheet()
    {
        $this->setCharacterID();
        $xml = $this->api->ale->char->characterSheet();
//        if(!$this->f) { $this->f = true; var_dump($xml); }
        return $xml;
    }

    function _characterInfo()
    {
        $this->setCharacterID();
        $xml = $this->api->ale->eve->characterInfo();
//        if(!$this->f) { $this->f = true; var_dump($xml); }
        return $xml;
    }

    function _skillInTraining()
    {
        $this->setCharacterID();
        $xml =  $this->api->ale->char->SkillInTraining();
//        if(!$this->f) { $this->f = true; var_dump($xml); }
        return $xml;
    }

    function Name()
    {
        $x = $this->_characterSheet();
        $r = $x->xpath('result/name');
        return (string)$r[0];
    }

    function ID()
    {
        $x = $this->_characterSheet();
        $r = $x->xpath('result/characterID');
        return (string)$r[0];
    }

    function DoB()
    {
        $x = $this->_characterSheet();
        $r = $x->xpath('result/DoB');
        $d = Date::create('Date', (string)$r[0]);
        return $d;
    }

    function Race()
    {
        $x = $this->_characterSheet();
        $r = $x->xpath('result/race');
        return (string)$r[0];
    }

    function Bloodline()
    {
        $x = $this->_characterSheet();
        $r = $x->xpath('result/bloodLine');
        return (string)$r[0];
    }

    function Ancestry()
    {
        $x = $this->_characterSheet();
        $r = $x->xpath('result/ancestry');
        return (string)$r[0];
    }

    function Gender()
    {
        $x = $this->_characterSheet();
        $r = $x->xpath('result/gender');
        return (string)$r[0];
    }

    function Corporation()
    {
        $x = $this->_characterSheet();
        $r = $x->xpath('result/corporationName');
        return (string)$r[0];
    }

    function CorporationID()
    {
        $x = $this->_characterSheet();
        $r = $x->xpath('result/corporationID');
        return (string)$r[0];
    }

    function Alliance()
    {
        $x = $this->_characterSheet();
        $r = $x->xpath('result/allianceName');
        if(!count($r))  return '';
        return (string)$r[0];
    }

    function AllianceID()
    {
        $x = $this->_characterSheet();
        $r = $x->xpath('result/allianceID');
        if(!count($r))  return 0;
        return (string)$r[0];
    }

    function CloneName()
    {
        $x = $this->_characterSheet();
        $r = $x->xpath('result/cloneName');
        return (string)$r[0];
    }

    function CloneSP()
    {
        $x = $this->_characterSheet();
        $r = $x->xpath('result/cloneSkillPoints');
        return Int::create('Int', (string)$r[0]);
    }

    function Wealth()
    {
        $x = $this->_characterSheet();
        $r = $x->xpath('result/balance');
        return (string)$r[0];
    }

    function SecStatus()
    {
        $x = $this->_characterInfo();
        $r = $x->xpath('result/securityStatus');
        return round((string)$r[0], 2);
    }

    function SkillPoints()
    {
        $x = $this->_characterInfo();
        $r = $x->xpath('result/skillPoints');
        return Int::create('Int', (string)$r[0]);
    }

/*  // api key requirements not met derp
    function Training()
    {
        $x = $this->_skillInTraining();
        var_dump($x);
        return;
    }
*/

    function Intelligence()
    {
        $x = $this->_characterSheet();
        $r = $x->xpath('result/attributes/intelligence');
        return (string)$r[0];
    }

    function Perception()
    {
        $x = $this->_characterSheet();
        $r = $x->xpath('result/attributes/perception');
        return (string)$r[0];
    }

    function Charisma()
    {
        $x = $this->_characterSheet();
        $r = $x->xpath('result/attributes/charisma');
        return (string)$r[0];
    }

    function Willpower()
    {
        $x = $this->_characterSheet();
        $r = $x->xpath('result/attributes/willpower');
        return (string)$r[0];
    }

    function Memory()
    {
        $x = $this->_characterSheet();
        $r = $x->xpath('result/attributes/memory');
        return (string)$r[0];
    }

    function IntelligenceAugmentorName()
    {
        $x = $this->_characterSheet();
        $r = $x->xpath('result/attributeEnhancers/intelligenceBonus/augmentatorName');
        return ($r) ? (string)$r[0] : false;
    }

    function IntelligenceAugmentorBonus()
    {
        $x = $this->_characterSheet();
        $r = $x->xpath('result/attributeEnhancers/intelligenceBonus/augmentatorValue');
        return ($r) ? (string)$r[0] : false;
    }

    function PerceptionAugmentorName()
    {
        $x = $this->_characterSheet();
        $r = $x->xpath('result/attributeEnhancers/perceptionBonus/augmentatorName');
        return ($r) ? (string)$r[0] : false;
    }

    function PerceptionAugmentorBonus()
    {
        $x = $this->_characterSheet();
        $r = $x->xpath('result/attributeEnhancers/perceptionBonus/augmentatorValue');
        return ($r) ? (string)$r[0] : false;
    }

    function CharismaAugmentorName()
    {
        $x = $this->_characterSheet();
        $r = $x->xpath('result/attributeEnhancers/charismaBonus/augmentatorName');
        return ($r) ? (string)$r[0] : false;
    }

    function CharismaAugmentorBonus()
    {
        $x = $this->_characterSheet();
        $r = $x->xpath('result/attributeEnhancers/charismaBonus/augmentatorValue');
        return ($r) ? (string)$r[0] : false;
    }

    function WillpowerAugmentorName()
    {
        $x = $this->_characterSheet();
        $r = $x->xpath('result/attributeEnhancers/willpowerBonus/augmentatorName');
        return ($r) ? (string)$r[0] : false;
    }

    function WillpowerAugmentorBonus()
    {
        $x = $this->_characterSheet();
        $r = $x->xpath('result/attributeEnhancers/willpowerBonus/augmentatorValue');
        return ($r) ? (string)$r[0] : false;
    }

    function MemoryAugmentorName()
    {
        $x = $this->_characterSheet();
        $r = $x->xpath('result/attributeEnhancers/memoryBonus/augmentatorName');
        return ($r) ? (string)$r[0] : false;
    }

    function MemoryAugmentorBonus()
    {
        $x = $this->_characterSheet();
        $r = $x->xpath('result/attributeEnhancers/memoryBonus/augmentatorValue');
        return ($r) ? (string)$r[0] : false;
    }

    function Skills()
    {
        $x = $this->_characterSheet();
        $r = $x->xpath('result/rowset[@name="skills"]/row');

        $ids = array();
        foreach($r as $s) {
            $s = $s->attributes();
            $ids[] = (int) $s['typeID'];
        }

        $skills = invTypes::get('invTypes', sprintf("`typeID` IN ('%s')", implode($ids, "','")));

        foreach($r as $s) {
            $s = $s->attributes();
            $t = $skills->find('typeID', $s['typeID']);
            $t->setField('Level', $s['level']);
            $t->setField('SkillPoints', $s['skillpoints']);
        }

        return $skills;
    }

    function SkillsInGroups()
    {
        $x = $this->_characterSheet();

        $groups = invTypes::SkillGroups();
        foreach($groups as $g) {
            foreach($g->invTypes() as $s) {
                $r = $x->xpath(sprintf('result/rowset[@name="skills"]/row[@typeID="%d"]', $s->typeID));
                if($r) {
                    //echo 'lol';
                    $r = $r[0]->attributes();
                    //var_dump($r);
                    $s->setField('SkillPoints', $r['skillpoints']);
                    $s->setField('Level', $r['level']);
                } else {
//                    $s->setField('SkillPoints', 0);
//                    $s->setField('Level', 0);
                    //$g->invTypes()->remove($s);
                }
            }
        }
        return $groups;
    }

    function HasSkill($typeID = false, $level = 0)
    {
        if(!$typeID) return true;
        $x = $this->_characterSheet();
        $r = $x->xpath(sprintf('result/rowset[@name="skills"]/row[@typeID="%d"]', $typeID));
        if($r) {
            $r = $r[0]->attributes();
            if($r['level'] > $level) return true;
        }
        return false;
    }
}
