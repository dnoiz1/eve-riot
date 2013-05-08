<?php

require_once('../eacc/thirdparty/ale/factory.php');

class EveApi extends DataObject {
    public $ale;

    function __construct($record = null, $isSingleton = false) {
        $this->ale = AleFactory::getEVEOnline();
        return parent::__construct($record, $isSingleton);
    }

    private static $db = array(
        'KeyID' => 'Int',
        'vCode' => 'Varchar(255)',
        'Valid' =>  'Boolean'
    );

    private static $has_one = array(
        'Member' => 'Member'
    );

    static $defaults = array(
        'Valid' => 1
    );

    static $summary_fields = array(
        'Pilot',
        'KeyID',
        'vCode'
    );

    static $casting = array(
        'Pilot' => 'Varchar(255)'
    );

    static $default_sort = "Created ASC";

    function Pilot()
    {
        return ($m = Member::get_by_id('Member', (int)$this->MemberID)) ? $m->FirstName : 'Unassigned';
    }

    function APIErrors()
    {
        if(!$this->Member()) return new ArrayList(array(array('Reason' => 'No Assoc Member')));

        /* // removing leading cause of tech support queries...
        if(time() - strtotime($this->Member()->LastVisited) > (86400 * 30)) {
            return new ArrayList(array(array('Reason' => 'Member has not logged in for one month')));
        }
        */

        $errors = array();
        try {
//            if(!$this->Valid) return new ArrayList(array(array('Reason' => 'Key too old, please create a new one')));

            //check key keys are fresh...
            /*
            if(!$this->ID) {
                if($this->KeyID < (EveApi::get_one("EveApi", '', '', 'ID DESC')->ID - 200)) {
                    return new ArrayList(array(array('Reason' => 'Key too old, please create a new one')));
                }
            }
            */

            $this->ale->setKey($this->KeyID, $this->vCode);
            $info = $this->ale->Account->APIKeyInfo();

            /* else {
                if($this->KeyID < (EveApi::get_one("EveApi", sprintf("KeyID < %d", (int)$this->KeyID), '', 'ID DESC')->ID - 200)) {
                   return new ArrayList(array(array('Reason' => 'Key too old, please create a new one')));
                }
            }*/

            // check  is account
            $info = $info->result->key->attributes();
            //if($info['type'] != 'Account' && $info['type'] != 'Corporation') {
            if($info['type'] != 'Account') {
                $errors[] = array('Reason' => 'Key must be Account');
            }

            // check no expire
            if(strlen($info['expires']) > 1) {
                $errors[] = array('Reason' => 'Key must not Expire');
            }

            // check access mask
            $required = array(
                //'AccountBalance' => 1,
                'CharacterInfo' => 16777216,
                'CharacterSheet' => 8,
                'CharacterInfo' => 8388608,
                'KillLog' => 256,
                //'FacWarStats' => 64
            );

            foreach($required as $k => $v) {
                if(!((int)$info['accessMask'] & $v)) {
                    $errors[] = array('Reason' => 'Missing '.$k);
                }
            }

            $info = $this->ale->Account->Characters();
            $chars = array();
            foreach($info->result->characters as $c) {
                $c = $c->attributes();
                $chars[$c['characterID']] = $c['name'];
            }
            if(EveMemberCharacterCache::get()->filter('CharacterID', array_keys($chars))->exclude('EveApiID', $this->ID)->Count() > 0) {
                $errors[] = array('Reason' => 'Characters on this API already belong to an Account.');
            }

            if(count($errors) == 0) {
                // nuke imposters.
                foreach(Member::get()->filter('FirstName', array_values($chars))->exclude('ID', $this->MemberID) as $imposter) {
                    $imposter->FirstNameCitizen();
                    $imposter->write();
                }
            }

        } catch(Exception $e) {
            $errors[] = array('Reason' => $e->getMessage());
        }
        return new ArrayList($errors);
    }

    function isValid()
    {
        return ($this->ApiErrors()->Count() > 0) ? false : true;
    }

    function hasAccess($mask = 0)
    {
        /* need to call this from isValid, so prob  rework this */
        $isValid = $this->isValid();
        $errors = ($isValid !== true) ?  array() : array();

        try {
            $this->ale->setKey($this->KeyID, $this->vCode);
            $info = $this->ale->Account->APIKeyInfo();
            $info = $info->result->key->attributes();

            if(!((int)$info['accessMask'] & $mask)) {
                $errors[] = array('Reason' => 'Missing ' . $mask);
            }
        } catch (Exception $e) {
            $errors[] = array('Reason' => 'Invalid Key');
        }

        return (count($errors) > 0) ? new ArrayList($errors) : true;
    }

    function Characters()
    {
        if($this->isValid() !== true) return array();
        $chars = array();

        $this->ale->setKey($this->KeyID, $this->vCode);
        try {
            $info = $this->ale->Account->Characters();
        } catch(Exception $e) {
            return $chars;
        }

        foreach($info->result->characters as $c) {
            $chars[] = $c->attributes();
        }

        return $chars;
    }

    function ApiSecurityGroups()
    {
        if($this->isValid() !== true) return new ArrayList();

        $corps = array();
        foreach($this->Characters() as $c) {
            $corps[] = $c['corporationID'];
        }

        $EveCorps = EveCorp::get()->filter('CorpID', $corps);
        $groups = Group::get()->filter(array(
            'ID' => array_keys($EveCorps->Map('GroupID')->toArray())
        ));
        return $groups;
    }

    function onBeforeWrite()
    {
        $this->KeyID = trim($this->KeyID);
        $this->vCode = trim($this->vCode);

        parent::onBeforeWrite();
        if(!$this->ID) {
            if(!$this->isValid()) {
                $this->Valid = false;
            }
        }
    }

    function onAfterWrite()
    {
        parent::onAfterWrite();
        $m = Member::get_by_id('Member', (int)$this->MemberID);
        if($m) {
            foreach($this->Characters() as $c) {
               //incase they have multiple apis for the same account.. people is tards
               if(!EveMemberCharacterCache::get_one('EveMemberCharacterCache', sprintf("CharacterID = %d", $c['characterID']))) {
                   $cache = new EveMemberCharacterCache();
                   $cache->CharacterName = $c['name'];
                   $cache->CharacterID   = $c['characterID'];
                   $cache->CorporationName = $c['corporationName'];
                   $cache->CorporationID   = $c['corporationID'];
                   $cache->MemberID      = $m->ID;
                   $cache->EveApiID      = $this->ID;

                   $cache->write();
               }

               if(strtolower($m->FirstName) == strtolower($c['name']) || $m->CharacterID == $c['characterID'] || $m->CharacterID == 0) {
                   $m->CharacterID = $c['characterID'];
                   $m->FirstName   = $c['name'];
                   $m->write();
               }

               $m->updateGroupsFromAPI();
            }
        }
    }

    function onBeforeDelete()
    {
        parent::onBeforeDelete();
        foreach(EveMemberCharacterCache::get()->Filter(array('EveApiID' => $this->ID)) as $cache) {
            $cache->delete();
        }
    }

    function onAfterDelete()
    {
        parent::onAfterDelete();
        $m = Member::get_by_id('Member', (int)$this->MemberID);
        if($m) $m->updateGroupsFromAPI();
    }
}
