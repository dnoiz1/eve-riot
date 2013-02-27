<?php
class DustMercTokenPage extends Page
{
    static $has_one = array(
        'DustMercTokenProvider' => 'DustMercTokenProvider'
    );

    function getCMSFields()
    {
        $dmtp = DustMercTokenProvider::get('DustMercTokenProvider')->Map('ID', 'Name');
        $f = parent::getCMSFields();
        $f->findOrMakeTab('Root.Content.DustMerc', 'Dust Merc Provider');
        $f->addfieldToTab('Root.Content.DustMerc', new DropDownField('DustMercTokenProviderID', 'Dust Merc Provider', $dmtp));
        return $f;
    }
}

class DustMercTokenPage_controller extends Page_controller
{
    function CurrentToken()
    {
        $m = Member::currentUser();
        if(!$m) return false;
        return DustMercToken::get_one('DustMercToken', sprintf("MemberID = '%d' DustMercTokenProviderID = '%d' AND Expires < NOW() AND NOT Used", $m->ID, $this->DustMercTokenProvider()->ID));
    }
}
