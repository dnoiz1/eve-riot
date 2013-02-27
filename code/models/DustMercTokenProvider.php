<?php

class DustMercTokenProvider extends DataObject
{
    static $db = array(
        'Name'          => 'Varchar(255)',
        'CharacterID'   => 'Int',
        'Active'        => 'Boolean'
    );

    static $has_one = array(
        'Member'    => 'Member',
        'EveApi'    => 'EveApi',
        'EveCorp'   => 'EveCorp',
        'Group'     => 'Group'
    );

    static $has_many = array(
        'DustMercToken' => 'DustMercToken'
    );

    function getCMSFields()
    {
        $f = parent::getCMSFields();
        $f->removeByName('CharacterID');

        if($m = Member::get_by_id('Member', (int) $this->MemberID)) {
            if($a = $m->ApiKeys()) {
                $f->replaceField('EveApiID', new DropDownField('EveApiID', 'Eve API (Key ID)', $a->map('ID', 'KeyID')));
            } else {
                $f->insertAfter(new LiteralField('', '<h3>Selected Member doesnt have any associated API keys</h3>'), 'MemberID');
            }

            if($k = EveApi::get_by_id('EveApi', $this->EveApiID)) {
                $chars = array();
                foreach($k->Characters() as $c) {
                    $chars[$c['characterID']] = $c['name'];
                }
                $f->insertAfter(new DropdownField('CharacterID', 'Character', $chars, $this->CharacterID), 'EveApiID');

                if(!$this->CharacterID) {
                    $f->insertAfter(new LiteralField('', '<h3>Select a Character and save the record</h3>'), 'CharacterID');
                } else {
                    if($this->hasMailMessagesAccess()) {
                        $f->insertAfter(new LiteralField('', '<h3>Mail to this character will be checked every 20 minutes</h3>'), 'CharacterID');
                    } else {
                        $f->insertAfter(new LiteralField('', '<h3>Please confirm this API has MailMessages Access</h3>'), 'CharacterID');
                    }
                }
           } else {
               $f->insertAfter(new LiteralField('', '<h3>Select an API Key and save the record</h3>'), 'EveApiID');
           }

        } else {
            $f->removeByName('EveApiID');
            $f->insertAfter(new LiteralField('', '<h3>Select a Member and save the record</h3>'), 'MemberID');
        }
        return $f;
    }

    function onBeforeWrite()
    {
        if(!EveApi::get_one('EveApi', sprintf('ID = %d AND MemberID = %d', $this->EveApiID, $this->MemberID))) {
            $this->EveApiID = 0;
            $this->CharacterID = 0;
            return parent::onBeforeWrite();
        }

        if($k = $this->EveApi()) {
            $h = false;
            foreach($k->Characters() as $c) {
                if($c['characterID'] == $this->CharacterID) {
                    $h = true;
                    break;
                }
            }
            if(!$h) $this->CharacterID = 0;
        } else {
            $this->CharacterID = 0;
        }

        return parent::onBeforeWrite();
    }

    function hasMailMessagesAccess()
    {
        if(($api = $this->EveApi()) && $this->CharacterID) {
            return ($api->hasAccess(2048) === true);
        }
        return false;
    }

    function Target()
    {
        if(!$this->hasMailMessagesAccess()) return false;
        $c = new EveCharacter($this->CharacterID, $this->EveApi());
        if($c) return $c->Name();
        return 'This Service is not currently Available, please try again later';
    }

    /* yay muckhole journal walking */
    function APITransactions()
    {
        if(($api = $this->EveApi()) && $this->CharacterID) {
            $a = new EveCharacter($this->CharacterID, $api);
            $ownerid = ($this->Type == 'Character') ? $this->CharacterID : $a->CorporationID();

            $transactions = new DataObjectSet();

            $params = array(
                'rowCount' => 2560
            );

            while(true) {
                if($this->Type == 'Character') {
                    $journal = $api->ale->char->WalletJournal($params);
                    $journal = $journal->xpath('/eveapi/result/rowset/row');
                } else {
                    $journal = $api->ale->corp->WalletJournal($params);
                    $journal = $journal->xpath('/eveapi/result/rowset/row');
                }
                foreach($journal as $j) {
                    $j = $j->attributes();
                    $params['fromID'] = $j['refID'];

                    /* skip anything not an incoming player donation */
                    if($j['refTypeID'] != 10 || $j['ownerID1'] == $ownerid) continue;

                    $transactions->push(new ArrayData(array(
                        'Date'          => $j['date'],
                        'RefID'         => $j['refID'],
                        'CharacterID'   => $j['ownerID1'],
                        'Amount'        => $j['amount']
                    )));
                }

                if(count($journal) < 2560) break;
            }
        }
        return $transactions;
    }

    function canView()
    {
        return Permission::check('ADMIN');
    }

    function canEdit()
    {
        return Permission::check('ADMIN');
    }

    function canCreate()
    {
        return Permission::check('ADMIN');
    }

    function canDelete()
    {
        return (Permission::check('ADMIN') && $this->DustMercToken()->Count() == 0);
    }
}
