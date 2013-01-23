<?php

class invTypes extends DataObject
{
    static $db = array(
        'typeID' => 'Int',
        'groupID' => 'Int',
        'typeName' => 'Varchar(100)',
        'description' => 'Text',
        'radius' => 'Double',
        'mass' => 'Double',
        'volume' => 'Double',
        'capacity' => 'Double',
        'portionSize' => 'Int',
        'raceID' => 'Int',
        'basePrice' => 'Decimal(19,4)',
        'published' => 'Int',
        'marketGroupID' => 'Int',
        'chanceOfDuplicating' => 'Double',
        'iconID' => 'Int'
    );

    static function meetsPrereqs($TypeIDs, $skillMap)
    {
        if(!$TypeIDs || !skillMap) return false;
        if(is_array($TypeIDs)) {
            array_walk($TypeIDs, array('Convert', 'raw2sql'));
            $filter = sprintf("typeID IN ('%s')", implode($TypeIDs, "','"));
        } else {
            $filter = sprintf("typeID = %d", TypeIDs);
        }
        // phat sql here for prereqs
    }

    /*
     * this is fucked.
     */

    static function SkillGroups()
    {
        return invGroups::get('invGroups', "`categoryID` = '16' AND `published` = '1'", "`groupName`");
    }

    /*
     * this is fucked also.
     */

    public function prereqs()
    {
        $select = "invTypes.typeID, invTypes.typeName AS skill, COALESCE(skillLevel.valueFloat, skillLevel.valueInt) AS requiredLevel";
        $filter = "(attr.attributeID = 182 AND skillLevel.attributeID = 277) OR
                   (attr.attributeID = 183 AND skillLevel.attributeID = 278) OR
                   (attr.attributeID = 184 AND skillLevel.attributeID = 279) OR
                   (attr.attributeID = 1285 AND skillLevel.attributeID = 1286) OR
                   (attr.attributeID = 1289 AND skillLevel.attributeID = 1287) OR
                   (attr.attributeID = 1290 AND skillLevel.attributeID = 1288)";

        $join = "INNER JOIN dgmTypeAttributes AS attr ON attr.typeID = '%d' AND attr.attributeID IN (182,183,184,1285,1289,1290) AND COALESCE(attr.valueFloat, attr.valueInt) = invTypes.typeID
                 INNER JOIN dgmTypeAttributes AS skillLevel ON skillLevel.typeID = '%d' AND skillLevel.attributeID IN (277,278,279,1286,1287,1288)";
        $join = sprintf($join, $this->typeID, $this->typeID);

        $result = DB::Query(sprintf("SELECT %s FROM `invTypes` %s WHERE %s", $select, $join, $filter));

        $ds = new DataObjectSet();

        while($r = $result->record()) {
            $invType = invTypes::get_one('invTypes', sprintf("`typeID` = '%d'", $r['typeID']));
            $invType->setField('requiredLevel', $r['requiredLevel']);
            $ds->push($invType);
        }
        return $ds;
    }

    public function canUse()
    {
        return false;
        $p = $this->prereqs();
        $m = Member::currentUser();
        if(!$p) return true;
        if(!$m) return false;
        foreach($p as $skill) {
            if($m->MainCharacter()) {
                if(!$m->MainCharacter()->HasSkill($skill->typeID)) return false;
            }
        }
        return true;
    }

    public function Group()
    {
        return invGroups::get_one('invGroups', sprintf("groupID = '%d'", $this->groupID));
    }

    public function PriceCheck($useSystem = null)
    {
        /* really should extend RestfulService to create an eve central object, or figureout Ale or something */
        // cached for 12 hours
        $eveCentral = new RestfulService("http://api.eve-central.com", 12 * 3600);

        $params = array('typeid' => $this->typeID);
        if($useSystem) {
            $params['usesystem'] = $useSystem;
        }
        $eveCentral->httpHeader('Accept: application/xml');
        $eveCentral->httpHeader('Content-Type: application/xml');

        try {
            /* pfft GET ignores $data in RestfulService::request() */
            $req = $eveCentral->request('/api/marketstat?' . http_build_query($params));
            if(!$req) return false;
            $median = $req->xpath(sprintf('marketstat/type[@id=%d]/sell/median', $this->typeID));
            $median = (string)$median[0];
            return $median;
        } catch(Exception $e) {
            return false;
        }
    }

    public function realVolume()
    {
        $volume = eveStaticData::packagedSizes(preg_replace('/[^a-zA-Z0-9]/', '', $this->Group()->groupName));
        if(!$volume) $volume = $this->volume;
        return $volume;
    }
}
