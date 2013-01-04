<?php

class EveMember extends DataObjectDecorator
{

    function extraStatics()
    {
        return array(
            'db' => array(
                'CharacterID' => 'Int',
                'JabberUser' => 'Varchar(255)',
                'JabberPasswd' => 'Varchar(255)'
            ),
            'has_many' => array(
                'EveMemberCharacterCache' => 'EveMemberCharacterCache'
            ),
        );
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

        $membergroups = $this->owner->Groups();
        if($membergroups) foreach($membergroups as $g) {
            if(!in_array($g->Code, $groups)) {
                // only work with API groups
                if(!in_array($g->Code, array('rioters', 'officers', 'directors'))) continue;
                // remove from groups
                $this->owner->Groups()->remove($g->ID);
                $this->owner->Groups()->write();
            }
        }

        foreach($groups as $g) {
            if(!$this->owner->inGroup($g)) $this->owner->addToGroupByCode($g);
        }

        ksort($ranks);
        $this->owner->setField('ForumRank', array_shift($ranks));
        $this->owner->write();
    }

    function Characters()
    {
        if($this->owner->chars) return $this->owner->chars;
        $chars = array();
        if($this->ApiKeys()) foreach($this->ApiKeys() as $a) {
            foreach($a->Characters() as $c) {
                $chars[] = $c;
            }
        }
        $this->owner->chars = $chars;
        return $chars;
    }

    function Character($id)
    {
        $chars = $this->Characters();
        foreach($chars as $c) {
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

    function onBeforeWrite()
    {
        $first = false;
        $nickname_as_toon = false;
        foreach($this->Characters() as $c) {
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
        return parent::onBeforeWrite();
    }
}
