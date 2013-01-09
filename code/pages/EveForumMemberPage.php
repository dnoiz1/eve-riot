<?php

class EveForumMemberPage extends EveCharacterPage
{
}

class EveForumMemberPage_controller extends EveCharacterPage_controller
{
    function index()
    {
        if(!Permission::check('EVE_ROSTER')) return $this->httpError(403);
        return $this;
    }

    function CharactersFromCache()
    {
        return EveMemberCharacterCache::get('EveMemberCharacterCache');
    }

    function MembersInCorp()
    {
        return Group::get_one('Group', "Code = 'rioters'")->Members();
    }
}
