<?php

class EveNonCorpPage extends Page
{
    function ShowInMainMenu()
    {
        if($m = Member::currentMember()) {
            if($m->inGroup('rioters')) return false;
        }
        return true;
    }
}

class EveNonCorpPage_controller extends Page_controller
{
}
