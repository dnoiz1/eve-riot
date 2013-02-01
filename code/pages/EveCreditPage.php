<?php
class EveCreditPage extends Page
{
    public function CreditProviders() {
        return EveCreditProvider::get('EveCreditProvider', 'Active = 1');
    }
}

class EveCreditPage_controller extends Page_Controller
{
    function handleAction($request)
    {
        if($action = $request->Param('Action')) {
            $m = Member::CurrentUser();
            if(!$m || !$action) return $this->httpError(404);

            if(!$credit_provider = EveCreditProvider::get_one('EveCreditProvider', sprintf('ID = %d', Convert::raw2sql($action)))) {
                return $this->httpError(404);
            }

            if(!$credit_provider->canView()) return $this->httpError(404);

            if($id = $request->Param('ID')) {
                $member = Member::get_by_id('Member', (int)$id);
                if(!$member) return $this->httpError(404);
                $member_balance = $credit_provider->MemberBalance($member->ID);
                $transaction_history = $credit_provider->MemberTransactionHistory($member->ID);

                return $this->renderWith(array('EveCreditPage_transaction_history', 'Page'), array(
                    'CreditProvider'        => $credit_provider,
                    'Member'                => $member,
                    'MemberBalance'         => $member_balance,
                    'TransactionHistory'    => $transaction_history
                ));
            }

            return $this->renderWith(array('EveCreditPage_manage', 'Page'), array(
                'CreditProvider' => $credit_provider
            ));
        }

        return $this->renderWith(array('EveCreditPage', 'Page'));
    }
}
