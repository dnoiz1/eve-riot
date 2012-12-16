<?php
class EveCreditPage extends Page
{
    public function CreditProviders() {
        return EveCreditProvider::get('EveCreditProvider', 'Active = 1');
    }
}

class EveCreditPage_controller extends Page_Controller
{
}
