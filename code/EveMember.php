<?php

class EveMember extends DataObjectDecorator
{

    function extraStatics()
    {
        return array(
            'db' => array(
                'CharacterID' => 'Int',
                'JabberUser' => 'Varchar(255)',
                'JabberToken' => 'Varchar(255)',
                'JabberAutoConnect' => 'Boolean',
                'TeamSpeakIdentity' => 'Varchar(255)',
                'HasDonated'    => 'Boolean',
            ),
            'has_many' => array(
                'EveMemberCharacterCache' => 'EveMemberCharacterCache'
            ),
            'defaults' => array(
                'JabberAutoConnect' => 1
            )
        );
    }

    function NickNameToJabberUser($suffix = false)
    {
        $nn = strtolower($this->owner->Nickname);
        $nn = trim($nn);
        $nn = str_replace(' ', '_', $nn);
        $nn = preg_replace('/[^a-zA-Z0-9_]/', '', $nn);
        if($suffix) $nn .= $suffix;

        if(Member::get_one('Member', sprintf("JabberUser = '%s' AND ID <> %d", Convert::raw2sql($nn), $this->owner->ID))) {
            return $this->NickNameToJabberUser($suffix++);
        }
        return $nn;
    }

    function AllowedJabber()
    {
        return Permission::check('JABBER');
    }

    function AllowedTeamspeak()
    {
        return Permission::check('TEAMSPEAK');
    }

    function ApiKeys()
    {
        return EveApi::get('EveApi', sprintf('MemberID = %d', $this->owner->ID));
    }

    function updateCMSFields(&$f)
    {
        $f->findOrMakeTab('Root.ApiKeys', 'API Keys');
        if($apis = $this->ApiKeys()) foreach($apis as $a) {
            $f->addFieldsToTab('Root.ApiKeys', array(
                new ReadonlyField(sprintf('ApiKey[%d]', $a->ID), $a->KeyID, $a->vCode)
            ));
        }
    }

    function updateGroupsFromAPI()
    {
        $apis = $this->ApiKeys();

        $groups = array();
        $ranks = array(99 => 'Visitor');

        if($apis) foreach($apis as $a) {
            $a = $a->ApiSecurityGroups();

            if($a['Groups']) foreach($a['Groups'] as $v) {
                if(!in_array($v, $groups)) $groups[] = $v;
            }
            if($a['Rank']) foreach($a['Rank'] as $k => $v) {
                $ranks[$k] = $v;
            }
        }

        $nonApiGroups = Group::get('Group', sprintf("ApiManaged = 0"))->map('ID', 'ID');

        $membergroups = $this->owner->Groups();
        if($membergroups) foreach($membergroups as $g) {
            if(!in_array($g->Code, $groups)) {
                // only work with API groups
                if(in_array($g->ID, $nonApiGroups)) continue;
                // remove from groups
                $this->owner->Groups()->remove($g->ID);
                $this->owner->Groups()->write();
            }
        }

        foreach($groups as $g) {
            if(!$this->owner->inGroup($g)) {
                if($group = Group::get_by_id('Group', (int)$g)) {
                    $group->Members()->add($this->owner);
                }
            }
        }

        ksort($ranks);
        $this->owner->setField('ForumRank', array_shift($ranks));
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

    function onBeforeWrite()
    {
        if($this->owner->isChanged('PublicFieldsRaw')) {
            $first = false;
            $nickname_as_toon = false;
            $chars = $this->Characters();
            if($chars) foreach($chars as $c) {
                if(!$first) $first = $c['name'];
                if($c['name'] == $this->owner->Nickname) {
                    $this->owner->setField('CharacterID', (int)$c['characterID']);
                    $nickname_as_toon = true;
                    break;
                }
            }
            if($first && !$this->owner->FirstName) $this->owner->FirstName = $first;
            // still doesnt force toon names, but also doesnt fuckup when api does
            if(!$nickname_as_toon) $this->owner->Nickname = $this->owner->FirstName;

            $this->owner->JabberUser = $this->NickNameToJabberUser();
        }

        if($this->owner->isChanged('NumVisit')) {
            $gen = new RandomGenerator();
            $this->owner->JabberToken = $gen->generateHash('sha1');
        }

        if($this->owner->isChanged('CharacterID') && $main = $this->MainCharacter()) {
            $this->owner->ForumRank = $main->Rank();
        }

        return parent::onBeforeWrite();
    }
}
