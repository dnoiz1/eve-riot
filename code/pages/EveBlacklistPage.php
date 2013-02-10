<?php

class EveBlacklistPage extends Page
{
    function Blacklist()
    {
        return DataObject::get('EveBlacklist');
    }
}
class EveBlacklistPage_controller extends Page_controller {}
