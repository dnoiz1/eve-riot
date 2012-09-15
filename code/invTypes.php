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

    /*
     * this is fucked.
     */

    static function SkillGroups()
    {
        $groups = invGroups::get('invGroups', "`categoryID` = '16' AND `published` = '1'", "`groupName`");
        return $groups;
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
}
