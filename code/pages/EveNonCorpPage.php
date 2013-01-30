<?php

class EveNonCorpPage extends Page
{
    function ShowInMainMenu()
    {
        if($m = Member::currentMember()) {
            if($m->Groups()->Count() > 0) return false;
        }
        return true;
    }
}

class EveNonCorpPage_controller extends Page_controller
{
}
