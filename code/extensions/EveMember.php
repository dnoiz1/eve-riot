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
        'EveMemberCharacterCache' => 'EveMemberCharacterCache',
        'EveApi'                  => 'EveApi'
    );

    static $belongs_many_many = array(
        'EveManagedGroup' => 'EveManagedGroup'
    );

    static $defaults = array(
        'JabberAutoConnect' => 1
    );

    function FirstNameToJabberUser($suffix = 0)
    {
        $nn = $this->owner->FirstName;
        $nn = strtolower($nn);
        $nn = trim($nn);
        $nn = str_replace(' ', '_', $nn);
        $nn = preg_replace('/[^a-zA-Z0-9_]/', '', $nn);
        if($suffix > 0) $nn .= $suffix;

        if($m = Member::get()->filter('JabberUser', Convert::raw2sql($nn))->exclude('ID', $this->owner->ID)) {
            if($m->count() > 0) $nn = $this->FirstNameToJabberUser($suffix+1);
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
        return $this->owner->EveApi();
    }

    /*
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
    */

    function updateGroupsFromAPI()
    {
        $apis = $this->ApiKeys();

        $groups = new ArrayList();
        if($apis) foreach($apis as $a) {
            foreach($a->ApiSecurityGroups() as $g) {
                if(!$groups->find('ID', $g->ID)) {
                    $groups->push($g);
                    /*
                    if($g->parentID != 0 && !$groups->find('ID', $g->parentID)) {
                        $groups->push($g->parent());
                    }
                    */
                }
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
           if($cache = $this->owner->EveMemberCharacterCache()) {
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
            if($corp = $cache->EveCorp()) return $corp->Ticker;
        }
        return false;
    }

    function AllianceTicker()
    {
        if($cache = EveMemberCharacterCache::get_one('EveMemberCharacterCache', sprintf("CharacterID = '%d' AND MemberID = '%d'", $this->owner->CharacterID, $this->owner->ID))) {
            if($corp = $cache->EveCorp()) return $corp->EveAllianceTicker();
        }
        return false;
    }

    function TaggedName()
    {
        $name = $this->owner->FirstName;

        if($ticker = $this->Ticker()) {
            $name = sprintf("[%s] %s", $ticker, $name);

            if($alliance_ticker = $this->AllianceTicker()) {
                $name = sprintf("[%s]%s", $alliance_ticker, $name);
            }
        }

        return $name;
    }

    function Standing()
    {
        $standing = 0;

        if($cache = $this->owner->EveMemberCharacterCache()) {
            foreach($cache as $c) {
                if($corp = $c->EveCorp()) {
                    if($alliance = $corp->EveAlliance()) {
                        $standing = ((float)$alliance->Standing > $standing) ? (float)$alliance->Standing : $standing;
                    }
                }
            }
        }

        return $standing;
    }

    function FirstNameCitizen()
    {
        $this->owner->FirstName = sprintf("CoalitionCitizen%d%d", date('U'), rand(1000,9999));
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
                    foreach(Member::get()->filter('FirstName', $first)->exclude('ID', $this->owner->ID) as $imposter) {
                        $imposter->FirstNameCitizen();
                        $imposter->write();
                    }
                }
            }
        }

        if($this->owner->isChanged('FirstName') || ($this->owner->JabberUser == '')) {// == '' && !$this->owner->isChanged('JabberUser'))) {
            $this->owner->JabberUser = $this->FirstNameToJabberUser();
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
