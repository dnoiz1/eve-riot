<?php

require_once('../eacc/thirdparty/ale/factory.php');

class EveAlliance extends DataObject
{
    public $ale;

    private static $db = array(
        'AllianceName'      => 'Varchar(255)',
        'AllianceID'        => 'Int',
        'Ticker'            => 'Varchar(5)',
        'ExecutiveCorpID'   => 'Int',
        'MemberCount'       => 'Int'
    );

    private static $has_many = array(
        'EveCorp'   => 'EveCorp',
    );

    private static $has_one = array(
        'Group'     => 'Group'
    );

    static $summary_fields = array(
        'AllianceName',
        'Ticker',
        'ExecutiveCorpName',
        'MemberCount'
    );

    static $field_labels = array(
        'ExecutiveCorpName' => 'Executive Corp'
    );

    static $casting = array(
        'ExecutiveCorpName' => 'Varchar(100)',
    );

    function getTitle()
    {
        return $this->AllianceName;
    }

    function getCMSFields()
    {
        $f = parent::getCMSFields();
        $f->replaceField('AllianceName', new ReadOnlyField('AllianceName'));
        $f->replaceField('AllianceID', new ReadOnlyField('AllianceID'));
        $f->replaceField('ExecutiveCorpID', new ReadOnlyField('ExecutiveCorpID'));
        $f->replaceField('MemberCount', new ReadOnlyField('MemberCount'));

        return $f;
    }

    function ExecuteCorpName()
    {
        return ($ec = EveCorp::get_one()->filter(array('CorpID' => $this->ExecutiveCorpID))) ? $ec->CorpName : '';
    }

    function InfoFromAPI()
    {
        if(!$this->ale) $this->ale = AleFactory::getEveOnline();

        try {
            $alliance_list = $this->ale->eve->AllianceList();
            $alliance = $alliance_list->xpath(sprintf("/eveapi/result/rowset[@name='alliances']/row[@shortName='%s']", $this->Ticker));

            if(count($alliance) > 0) {
                $alliance = $alliance[0];
                $alliance_info = $alliance->attributes();

                return $alliance_info;
            }

        } catch(Exception $e) {
            //throw $e;
            return false;
        }

        return false;
    }

    function MemberCorps()
    {
        if(!$this->ale) $this->ale = AleFactory::getEveOnline();

        try {
            $alliance_list = $this->ale->eve->AllianceList();
            $membercorps = $alliance_list->xpath(sprintf("/eveapi/result/rowset[@name='alliances']/row[@shortName='%s']/rowset/row", $this->Ticker));

            foreach($membercorps as $k => $m) {
                $m = $m->attributes();
                $membercorps[$k] = $m['corporationID'];
            }

            return $membercorps;
        } catch(Exception $e) {
            //throw $e;
            return false;
        }
        return false;
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        if($this->isChanged('Ticker')) {
            $alliance = $this->InfoFromAPI();

            $this->AllianceName = $alliance['name'];
            $this->Ticker = $alliance['shortName'];
            $this->AllianceID = $alliance['allianceID'];
            $this->ExecutiveCorpID = $alliance['executorCorpID'];
            $this->MemberCount = $alliance['memberCount'];
        }

        //$this->MemberCorps();

        if($group = $this->Group()) {
            $group->Code =  $this->Ticker;
            $group->Title = $this->AllianceName;
            $group->write();

            $this->GroupID = $group->ID;
        }
    }

    function onBeforeDelete()
    {
        parent::onBeforeDelete();
        if($group = $this->Group()) {
            if($group->ID) $group->delete();
        }
        foreach(EveCorp::get()->filter(array('EveAllianceID' => $this->ID)) as $corp) {
            $corp->delete();
        }
    }
}
