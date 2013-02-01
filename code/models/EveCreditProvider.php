<?php

class EveCreditProvider extends DataObject
{
    static $db = array(
        'Name'          => 'Varchar(255)',
        'Type'          => "Enum('Character, Corporation', 'Character')",
        'CharacterID'   => 'Int',
        'Active'        => 'Boolean'
    );

    static $has_one = array(
        'Member' => 'Member',
        'EveApi' => 'EveApi'
    );

    static $has_many = array(
        'EveCreditRecords' => 'EveCreditRecord'
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
                    if($this->hasWalletAccess()) {
                        $f->insertAfter(new LiteralField('', '<h3>Payments to this wallet will be checked every 20 minutes</h3>'), 'CharacterID');
                    } else {
                        $f->insertAfter(new LiteralField('', '<h3>Please confirm the Api Key has Wallet Journal access</h3>'), 'CharacterID');
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

    function hasWalletAccess()
    {
        if(($api = $this->EveApi()) && $this->CharacterID) {
            $mask = ($this->Type == 'Character') ? 2097152 : 1048576;
            return ($api->hasAccess($mask) === true);
        }
        return false;
    }

    function Target()
    {
        if(!$this->hasWalletAccess()) return false;
        $c = new EveCharacter($this->CharacterID, $this->EveApi());
        if($c) {
            return ($this->Type == 'Character') ? $c->Name() : $c->Corporation();
        }
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

    function TransactionHistory($CharacterID = null)
    {
        $filter = '';
        if(is_array($CharacterID) && count($CharacterID) > 1) {
            array_walk($CharacterID, array('Convert', 'raw2sql'));
            $filter = sprintf("CharacterID IN ('%s')", implode($CharacterID, "','"));
        } else {
            if(is_array($CharacterID)) $CharacterID = $CharacterID[0];
            $filter = sprintf("CharacterID = '%s'", Convert::raw2sql($CharacterID));
        }

        $filter = sprintf("%s AND EveCreditProviderID = %d", $filter, $this->ID);

        return EveCreditRecord::get('EveCreditRecord', $filter);
    }

    function MemberTransactionHistory($memberid = null)
    {
        if($memberid) {
            $m = Member::get_by_id('Member', (int)$memberid);
        } else {
            $m = Member::CurrentUser();
        }
        if(!$m) return false;

        $chars = array();

        foreach($m->Characters() as $c) {
            $chars[] = $c['characterID'];
        }

        return $this->TransactionHistory($chars);
    }

    function Balance($CharacterID = null)
    {
        $filter = false;
        if(is_array($CharacterID) && count($CharacterID) > 1) {
            array_walk($CharacterID, array('Convert', 'raw2sql'));
            $filter = sprintf("CharacterID IN ('%s')", implode($CharacterID, "','"));
        } elseif($CharaterID) {
            if(is_array($CharacterID)) $CharacterID = $CharacterID[0];
            $filter = sprintf("CharacterID = '%s'", Convert::raw2sql($CharacterID));
        }
        if($filter) $filter = sprintf('%s AND', $filter);
        $filter = sprintf("%s EveCreditProviderID = %d", $filter,  $this->ID);
        $sql = sprintf("SELECT SUM(Amount) FROM EveCreditRecord WHERE %s", $filter);


        return (float) DB::Query($sql)->value();
    }

    function MemberBalance($memberid = null)
    {
        if($memberid) {
            $m = Member::get_by_id('Member', (int)$memberid);
        } else {
            $m = Member::CurrentUser();
        }
        if(!$m) return false;

        $chars = array();

        foreach($m->Characters() as $c) {
            $chars[] = $c['characterID'];
        }

        return $this->Balance($chars);
    }

    /* return current balances for all members who have deposited */
    function MemberBalances()
    {
        $sql = sprintf("SELECT EveCreditRecord.CharacterID,
                            SUM(EveCreditRecord.Amount) as Balance,
                            Member.NickName as 'Member',
                            Member.ID as 'MemberID'
                        FROM EveCreditRecord
                        JOIN EveMemberCharacterCache ON EveCreditRecord.CharacterID = EveMemberCharacterCache.CharacterID
                        JOIN Member On EveMemberCharacterCache.MemberID = Member.ID
                        WHERE
                        EveCreditRecord.EveCreditProviderID = '%d'
                        GROUP BY
                        EveCreditRecord.CharacterID,
                        Member.ID
                        ORDER BY Member.NickName", $this->ID);
        $result = DB::Query($sql);
        $dos = new DataObjectSet();
        while($row = $result->record()) {
            $dos->push(new ArrayData($row));
        }
        return $dos;
    }

    function NonMemberBalance()
    {
        $sql = sprintf("SELECT SUM(EveCreditRecord.Amount) as Balance
                        FROM EveCreditRecord
                        JOIN EveMemberCharacterCache ON EveCreditRecord.CharacterID = EveMemberCharacterCache.CharacterID
                        JOIN Member On EveMemberCharacterCache.MemberID = Member.ID
                        WHERE
                        EveCreditRecord.EveCreditProviderID = '%d'", $this->ID);
        return $this->Balance() - DB::Query($sql)->value();
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
        /* probably need to sql count instead */
        return (Permission::check('ADMIN') && $this->EveCreditRecords()->Count() == 0) ? true : false;
    }
}
