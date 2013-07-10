<?php

class EveGroupApplication extends DataObject
{
    private static $db = array(
        'Completed'    => 'Boolean',
        'Approved'     => 'Boolean',
        'Message'      => 'Text',
        'CompletedDate'=> 'SS_DateTime'
    );

    private static $has_one = array(
        'EveManagedGroup'   => 'EveManagedGroup',
        'Member'            => 'Member',
        'CompletedBy'       => 'Member'
    );

    private static $summary_fields = array(
        'Member.FirstName'        => 'Applicant',
        'EveManagedGroup.Title'   => 'Group',
        'CompletedDate'           => 'Completed Date',
        'CompletedBy.FirstName'   => 'By',
        'Approved'                => 'Approved'
    );

    public function Close($status = false)
    {
        $this->Completed     = true;
        $this->Approved      = $status;
        $this->CompletedDate = date('Y-m-d H:i:s');

        if($m = Member::CurrentUser()) {
            $this->CompletedByID = $m->ID;
        }
        $this->write();
    }

    public function Deny()
    {
        $this->Close();
    }

    public function Approve()
    {
        $member = $this->Member();
        $managed_group = $this->EveManagedGroup();

        if($member && $managed_group) {
            $managed_group->addMember($member);
        }

        $this->Close(true);
    }

    function canEdit($member = null)
    {
        return false;
    }

    function canCreate($member = null)
    {
        return false;
    }
}
