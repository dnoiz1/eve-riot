<?php

class EveMember extends DataExtension
{

    private static $db = array(
        'CharacterID' => 'Int',
        'JabberUser' => 'Varchar(255)',
        'JabberToken' => 'Varchar(255)',
        'JabberAutoConnect' => 'Boolean',
        'TeamSpeakIdentity' => 'Varchar(255)',
        'HasDonated'    => 'Boolean'
    );

    static $has_many = array(
        'EveMemberCharacterCache' => 'EveMemberCharacterCache'
    );

    static $defaults = array(
        'JabberAutoConnect' => 1
    );

    function FirstNameToJabberUser($suffix = false)
    {
        $nn = strtolower($this->owner->FirstName);
        $nn = trim($nn);
        $nn = str_replace(' ', '_', $nn);
        $nn = preg_replace('/[^a-zA-Z0-9_]/', '', $nn);
        if($suffix) $nn .= $suffix;

        if(Member::get_one('Member', sprintf("JabberUser = '%s'", Convert::raw2sql($nn)))) {
            return $this->FirstNameToJabberUser($suffix++);
        }
        return $nn;
    }

    function AllowedJabber()
    {
        return Permission::checkMember($this->owner->ID, 'JABBER');
    }

    function AllowedTeamspeak()
    {
        return Permission::checkMember($this->owner->ID, 'TEAMSPEAK');
    }

    function ApiKeys()
    {
        return EveApi::get()->filter('MemberID', $this->owner->ID);
    }

    function updateCMSFields(FieldList $f)
    {
        $f->findOrMakeTab('Root.ApiKeys', 'API Keys');
        if($apis = $this->ApiKeys()) foreach($apis as $a) {
            $f->addFieldsToTab('Root.ApiKeys', array(
                new ReadonlyField(sprintf('ApiKey[%d]', $a->ID), $a->KeyID, $a->vCode)
            ));
        }
        return $f;
    }

    function updateGroupsFromAPI()
    {
        $apis = $this->ApiKeys();

        $groups = new ArrayList();
        if($apis) foreach($apis as $a) {
            foreach($a->ApiSecurityGroups() as $g) {
                $groups->push($g);
            }
        }

        // srsly.. not avail
        //$groups = $groups->removeDuplicates();
        $removeGroups = $this->owner->Groups()->exclude('ApiManaged', 0);

        if($groups->Count() > 0) {
            $removeGroups = $removeGroups->exclude(array(
                'ID'  => array_keys($groups->Map())
            ));
        }

        foreach($removeGroups as $rg) {
            $this->owner->Groups()->remove($rg);
        }

        foreach($groups as $g) {
            if(!$this->owner->inGroup($g)) {
                $g->Members()->add($this->owner);
                $g->write();
            }
        }

        $this->owner->write();
    }

    function Characters($nocache = false)
    {
        if($this->owner->chars) return $this->owner->chars;

        if(!$nocache) {
           if($cache = EveMemberCharacterCache::get('EveMemberCharacterCache', sprintf("MemberID = '%d'", $this->owner->ID))) {
                $chars = array();
                foreach($cache as $c) {
                    $chars[] = array(
                        'name'              => $c->CharacterName,
                        'characterID'       => $c->CharacterID,
                        'corporationID'     => $c->CoporationID,
                        'corporationName'   => $c->CoporationName
                    );
                }
                if(count($chars) > 0) $nocache = true;
           }
        }

        if($nocache) {
            $chars = array();
            if($this->ApiKeys()) foreach($this->ApiKeys() as $a) {
                foreach($a->Characters() as $c) {
                    $chars[] = $c;
                }
            }
        }
        $this->owner->chars = $chars;
        return $chars;
    }

    function Character($id)
    {
        $chars = $this->Characters();
        if($chars) foreach($chars as $c) {
            if($c['characterID'] == $id) return $c;
        }
        return false;
    }

    function MainCharacter()
    {
        if(!$this->owner->CharacterID) return false;
        if($this->owner->char) return $this->owner->char;
        return $this->owner->char = new EveCharacter($this->owner->CharacterID);
    }

    function Ticker()
    {
        if($cache = EveMemberCharacterCache::get_one('EveMemberCharacterCache', sprintf("CharacterID = '%d' AND MemberID = '%d'", $this->owner->CharacterID, $this->owner->ID))) {
            if($corp = $cache->EveCorp()) {
                return $corp->Ticker;
            }
        }
        return false;
    }

    function FirstNameCitizen()
    {
        $this->FirstName = sprintf("CoalitionCitizen%d%d", date('U'), rand(1000,9999));
    }

    function onBeforeWrite()
    {
        if($this->owner->isChanged('FirstName')) {
            $first = false;
            $FirstName_as_toon = false;
            $chars = $this->Characters();
            if($chars) {
                    foreach($chars as $c) {
                    if(!$first) $first = $c['name'];
                    if($c['name'] == $this->owner->FirstName) {
                        $this->owner->setField('CharacterID', (int)$c['characterID']);
                        $FirstName_as_toon = true;
                        break;
                    }
                }
                //if($first && !$this->owner->FirstName) $this->owner->FirstName = $first;
                // still doesnt force toon names, but also doesnt fuckup when api does
                //if(!$FirstName_as_toon) $this->owner->FirstName = $this->owner->FirstName;
                if(!$FirstName_as_toon) {
                    $this->owner->FirstName = $first;
                }
                $this->owner->JabberUser = $this->FirstNameToJabberUser();
            }
        }

        if($this->owner->isChanged('NumVisit')) {
            $gen = new RandomGenerator();
            $this->owner->JabberToken = $gen->randomToken('sha1');
        }
        /*
        if($this->owner->isChanged('CharacterID') && $main = $this->MainCharacter()) {
            $this->owner->ForumRank = $main->Rank();
        }
        */
        return parent::onBeforeWrite();
    }
}
