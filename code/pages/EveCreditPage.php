<?php
class EveCreditPage extends Page
{
    public function CreditProviders() {
        return EveCreditProvider::get('EveCreditProvider');
    }
}

class EveCreditPage_controller extends Page_Controller
{
}
