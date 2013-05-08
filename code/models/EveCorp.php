<?php

require_once('../eacc/thirdparty/ale/factory.php');

class EveCorp extends DataObject
{
    public $ale;

    private static $db = array(
        'CorpName'  => 'Varchar(255)',
        'CorpID'    => 'Int',
        'Ticker'    => 'Varchar(5)',
        'CeoID'     => 'Int',
        'CeoName'   => 'Varchar(255)'
    );

    private static $has_one = array(
        'EveAlliance'   => 'EveAlliance',
        'Group'         => 'Group'
    );

    static $summary_fields = array(
        'CorpName',
        'Ticker',
        'CeoName',
        'EveAllianceName'
    );

    static $field_labels = array(
        'CeoName'     => 'Ceo',
        'EveAllianceName' => 'Alliance'
    );

    static $casting = array(
        'EveAllianceName'   => 'Varchar(255)'
    );

    function getTitle()
    {
        return $this->CorpName;
    }

    function EveAllianceName()
    {
        return ($a = $this->EveAlliance()) ? $a->AllianceName : '';
    }

    function getCMSFields()
    {
        $f = parent::getCMSFields();
        $f->replaceField('CorpName', new ReadOnlyField('CorpName'));
        $f->replaceField('Ticker', new ReadOnlyField('Ticker'));
        $f->replaceField('CeoID', new ReadOnlyField('CeoID'));

        $alliances = EveAlliance::get('EveAlliance')->map('ID', 'AllianceName');
        $f->replaceField('EveAllianceID', new DropDownField('EveAllianceID', 'Alliance', $alliances));
        return $f;
    }

    function InfoFromAPI()
    {
        if(!$this->ale) $this->ale = AleFactory::getEveOnline();

        try {
            $corp = $this->ale->corp->CorporationSheet(array('corporationID' => $this->CorpID));
            $corp = $corp->xpath('/eveapi/result');
            $corp = $corp[0];

            $ret = array(
                'corporationID'     => (string) $corp->corporationID,
                'corporationName'   => (string) $corp->corporationName,
                'ticker'            => (string) $corp->ticker,
                'ceoID'             => (string) $corp->ceoID,
                'ceoName'           => (string) $corp->ceoName,
                'stationID'         => (string) $corp->stationID,
                'stationName'       => (string) $corp->stationName,
                'description'       => (string) $corp->description,
                'url'               => (string) $corp->url,
                'allianceID'        => (string) $corp->allianceID,
                'allianceName'      => (string) $corp->allianceName,
                'taxRate'           => (string) $corp->taxRate,
                'memberCount'       => (string) $corp->memberCount,
                'shares'            => (string) $corp->shares
            );

            return $ret;
        } catch(Exception $e) {
            //throw $e;
            return false;
        }

        return false;
    }

    function onBeforeWrite()
    {
        parent::onBeforeWrite();

        if($this->isChanged('CorpID')) {
            $corp = $this->InfoFromAPI();

            $this->CorpName = $corp['corporationName'];
            $this->CorpID   = $corp['corporationID'];
            $this->Ticker   = $corp['ticker'];
            $this->CeoID    = $corp['ceoID'];
            $this->CeoName  = $corp['ceoName'];
            if($a = EveAlliance::get_one('EveAlliance', sprintf("AllianceID = '%d'", (int)$corp['allianceID']))) {
                $this->EveAllianceID = $a->ID;
            }
        }

        if($group = $this->Group()) {
            if($alliance = $this->EveAlliance()->Group()) {
                $group->ParentID = $alliance->ID;
            }
            $group->Code   = $this->Ticker;
            $group->Title  = $this->CorpName;
            $group->write();
        }

        if($this->GroupID != $group->ID) {
            $this->GroupID = $group->ID;
        }
    }

    function onBeforeDelete()
    {
        parent::onBeforeDelete();
        if($group = $this->Group()) {
            if($group->ID) $group->delete();
        }
    }
}
