<?php

class EveManagedGroup extends DataObject
{
    private static $db = array(
        'Title'            => 'Varchar(255)',
        'Description'      => 'Text',
//        'MinStanding'      => "Enum('-10, -5, -2.5, 0, 2.5, 5, 10', 10)",
        'MinStanding'      => "Int",
        'RequiresApproval' => 'Boolean'
    );

    private static $has_one = array(
        'Group'     => 'Group',
        'Managers'  => 'Group'
    );

    private static $has_many = array(
        'EveGroupApplication' => 'EveGroupApplication'
    );

    private static $many_many = array(
        'Members' => 'Member'
    );

    static $default_sort = "Title ASC";

    public function getCMSFields()
    {
        $f = parent::getCMSFields();

        $f->replaceField('GroupID', DropDownField::create('GroupID', 'Group', Group::get()->map()));
        $f->replaceField('ManagersID', DropDownField::create('ManagersID', 'Managers', Group::get()->map()));

        return $f;
    }

    public function PendingApplications()
    {
        return $this->EveGroupApplication()->filter('Completed', false);
    }

    public function StandingMembers()
    {
        $group_members = $this->Group()->Members();
        return $this->Members()->subtract($group_members);
    }

    public function AddMember(Member $member)
    {
        $this->Members()->add($member);

        if($group = $this->Group()) {
            if($member->Standing() >= $this->MinStanding) {
                $group->Members()->add($member);
                $group->write();
                return $group->Title;
            }
        }
    }

    public function RemoveMember(Member $member)
    {
        $this->Members()->remove($member);

        if($group = $this->Group()) {
            if($member->inGroup($group->ID)) {
                $group->Members()->remove($member);
                $group->write();
                return $group->Title;
            }
        }
    }

    public function MenuTitle()
    {
        return $this->Title;
    }

    public function Link($action = null)
    {
        if($controller = Controller::Curr()) {
            return $controller->Link(sprintf("/manage/%s/%s", $this->ID, $action));
        }
        return '';
    }

    public function LinkOrCurrent()
    {
        if($controller = Controller::Curr()) {
            if($controller instanceof EveManagedGroupsPage_controller) {
                $current_id = $controller->request->Param('ID');
                return ($current_id == $this->ID);
            }
        }
    }

    public function canManage(Member $member = null)
    {
        if(!$member) {
            $member = Member::CurrentUser();
        }

        return ($member->inGroup($this->ManagersID) || Permission::check('ADMIN'));
    }
}
