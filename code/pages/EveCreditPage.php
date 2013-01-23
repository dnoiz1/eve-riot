<?php
class EveCreditPage extends Page
{
    public function CreditProviders() {
        return EveCreditProvider::get('EveCreditProvider', 'Active = 1');
    }
}

class EveCreditPage_controller extends Page_Controller
{
    function manage($request)
    {
        $id = (int)$request->Param('ID');

        $m = Member::CurrentUser();
        if(!$m) return $this->httpError(404);
        // change this to some better kind of access control
        if(!$m->inGroup('administrators')) return $this->httpError(403);

        return $this->renderWith(array('EveCreditPage_manage', 'Page'), array(
        ));
    }
}
